# Audit de Sécurité - Isolation Multi-Tenant SBN v1.0

## 🔒 Principe fondamental

**RÈGLE D'OR**: Chaque requête SQL DOIT filtrer par `company_id` pour garantir l'isolation totale des données entre sociétés.

## ✅ Contrôles de sécurité implémentés

### 1. API Webhook (`ApiController.php`)

#### ✅ Vérifications effectuées:
- **Token API validation**: Le token est vérifié ET associé à un `company_id`
- **Isolation automatique**: Toutes les données créées incluent le `company_id` du token
- **Audit logging**: Toutes les actions sont loggées avec IP

```php
// Vérification du token
$stmt = $this->db->prepare("
    SELECT id, company_id, name, is_active
    FROM api_tokens
    WHERE token = ? AND is_active = 1
");

// Création avec company_id
$stmt->execute([$company_id, $deviceId, $data]);
```

#### 🛡️ Protection:
- Un NAS ne peut PAS écrire dans une autre société
- Le `company_id` est déterminé par le token API
- Impossible de spoffer le `company_id`

---

### 2. Gestion des Tokens API (`ApiTokenController.php`)

#### ✅ Vérifications effectuées:
- **Création**: Le token est automatiquement lié au `company_id` de l'utilisateur connecté
- **Liste**: Seuls les tokens de la société de l'utilisateur sont visibles
- **Révocation/Suppression**: Double vérification `token_id` + `company_id`

```php
// Exemple de requête sécurisée
$stmt = $this->db->prepare("
    DELETE FROM api_tokens
    WHERE id = ? AND company_id = ?
");
$stmt->execute([$tokenId, $user['company_id']]);
```

#### 🛡️ Protection:
- Un utilisateur ne peut PAS voir/modifier les tokens d'une autre société
- Impossible d'énumérer les tokens via l'ID

---

### 3. Sauvegardes (`BackupController.php`)

#### ✅ Vérifications effectuées:
```php
// Liste des sauvegardes
$stmt = $this->db->prepare("
    SELECT b.*, d.name as device_name
    FROM backups b
    LEFT JOIN backup_devices d ON b.device_id = d.id
    WHERE b.company_id = ?  // ← CRITIQUE
    ORDER BY b.start_time DESC
");
$stmt->execute([$user['company_id']]);
```

#### 🛡️ Protection:
- Un utilisateur voit UNIQUEMENT les sauvegardes de sa société
- Impossible d'accéder aux sauvegardes d'autres sociétés via ID

---

### 4. Sociétés (`CompanyController.php`)

#### ✅ Vérifications effectuées:
```php
// Admin voit toutes les sociétés
if ($user['role'] === 'admin') {
    $companies = $this->companyModel->findAll('name ASC');
} else {
    // User normal voit SEULEMENT sa société
    $companies = [$this->companyModel->findById($user['company_id'])];
}
```

#### 🛡️ Protection:
- Utilisateur normal: accès UNIQUEMENT à sa société
- Admin: peut voir toutes les sociétés (pour support)

---

### 5. Authentification (`AuthController.php`)

#### ✅ Vérifications effectuées:
- Le `company_id` est stocké dans la session après login
- Toutes les actions utilisent `$user['company_id']` depuis la session
- Session sécurisée avec `httponly` et `secure` cookies

```php
$_SESSION['company_id'] = $user['company_id'];
```

---

## 🔍 Checklist de sécurité par table

| Table | Filtre company_id | Vérifié | Commentaire |
|-------|-------------------|---------|-------------|
| `backups` | ✅ OUI | ✅ | Filtré dans BackupController |
| `backup_devices` | ✅ OUI | ✅ | Filtré dans ApiController |
| `notifications` | ✅ OUI | ✅ | Créées avec company_id |
| `api_tokens` | ✅ OUI | ✅ | Filtré dans ApiTokenController |
| `users` | ✅ OUI | ⚠️ | À implémenter dans UserController |
| `companies` | ⚠️ PARTIEL | ⚠️ | Admin peut voir toutes (normal) |
| `audit_logs` | ❌ NON | ⚠️ | Logs globaux (OK pour admin) |
| `login_attempts` | ❌ NON | ✅ | Sécurité globale (normal) |

---

## ⚠️ Points d'attention

### 1. Contrôleurs à implémenter/vérifier

#### UserController.php
**STATUS**: ⚠️ À implémenter

Doit inclure:
```php
// Liste des utilisateurs
$stmt = $this->db->prepare("
    SELECT * FROM users
    WHERE company_id = ?  // ← AJOUTER IMPÉRATIVEMENT
    ORDER BY created_at DESC
");
$stmt->execute([$user['company_id']]);
```

**Actions requises**:
- [ ] Filtrer liste des utilisateurs par company_id
- [ ] Vérifier company_id lors de l'édition
- [ ] Vérifier company_id lors de la suppression
- [ ] Empêcher création d'utilisateurs pour autre société

#### CompanyController.php
**STATUS**: ⚠️ À sécuriser

Doit inclure:
```php
// Édition de société
if ($user['role'] !== 'admin') {
    // User normal peut SEULEMENT éditer SA société
    if ($companyId != $user['company_id']) {
        die('Unauthorized');
    }
}
```

---

## 🔐 Système de partage/invitation (À implémenter)

### Concept
Permettre à un utilisateur d'inviter quelqu'un à voir UNE PARTIE de ses données.

### Approche sécurisée recommandée

#### Table: `shared_access`
```sql
CREATE TABLE shared_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_company_id INT NOT NULL,  -- Société qui partage
    shared_with_email VARCHAR(255) NOT NULL,  -- Email invité
    shared_with_user_id INT NULL,  -- User ID si inscrit
    scope JSON NOT NULL,  -- Définit ce qui est partagé
    is_active BOOLEAN DEFAULT 1,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (owner_company_id) REFERENCES companies(id),
    FOREIGN KEY (shared_with_user_id) REFERENCES users(id)
);
```

#### Scope JSON exemple:
```json
{
    "type": "devices",
    "device_ids": [1, 5, 12],  // IDs spécifiques des devices partagés
    "permissions": ["read"],    // Permissions: read, write, delete
    "filters": {
        "status": ["success", "failed"]  // Filtres optionnels
    }
}
```

#### Vérification dans les requêtes:
```php
// Vérifier si l'utilisateur a accès direct ou partagé
$stmt = $this->db->prepare("
    SELECT b.*
    FROM backups b
    WHERE (
        b.company_id = ?  -- Accès direct
        OR b.device_id IN (
            SELECT JSON_EXTRACT(scope, '$.device_ids[*]')
            FROM shared_access
            WHERE shared_with_user_id = ?
              AND is_active = 1
              AND (expires_at IS NULL OR expires_at > NOW())
        )  -- Accès partagé
    )
    ORDER BY b.start_time DESC
");
$stmt->execute([$user['company_id'], $user['id']]);
```

---

## 🧪 Tests de sécurité recommandés

### 1. Test d'isolation de base
```bash
# En tant qu'utilisateur de company_id=1
# Tenter d'accéder aux données de company_id=2
curl -X GET "https://app.com/backups/detail?id=123" \
  -H "Cookie: session_id=..."

# DOIT retourner: 404 ou empty (pas 403 pour éviter enumeration)
```

### 2. Test de token API
```bash
# Avec token de company_id=1
# Tenter d'envoyer des données en spécifiant company_id=2
curl -X POST "https://app.com/api/webhook" \
  -H "X-API-Token: sbn_..." \
  -d '{"device_name":"hack","company_id":2}'

# DOIT: Ignorer le company_id dans le POST, utiliser celui du token
```

### 3. Test d'énumération
```bash
# Tenter d'énumérer les IDs
for i in {1..100}; do
    curl -s "https://app.com/backups/detail?id=$i"
done

# DOIT: Retourner toujours la même erreur (pas de timing attack)
```

---

## 📋 Checklist de déploiement

Avant mise en production:

- [ ] Audit de TOUS les controllers pour vérification company_id
- [ ] Tests d'isolation entre sociétés
- [ ] Vérification des logs d'audit
- [ ] Test de performance avec isolation
- [ ] Documentation des règles de partage
- [ ] Formation des admins sur la sécurité multi-tenant
- [ ] Plan de réponse en cas de fuite de données
- [ ] Backup et recovery tests

---

## 🚨 Procédure en cas de fuite détectée

1. **Isolation immédiate**
   - Mettre l'application en mode maintenance
   - Invalider tous les tokens API
   - Forcer re-authentification de tous les utilisateurs

2. **Investigation**
   - Analyser les logs d'audit
   - Identifier la requête fautive
   - Déterminer l'étendue de la fuite

3. **Notification**
   - Informer les sociétés affectées
   - Respecter les obligations RGPD
   - Documenter l'incident

4. **Correction**
   - Patch immédiat
   - Tests de sécurité approfondis
   - Déploiement en urgence

5. **Prévention**
   - Analyser la cause racine
   - Ajouter des tests automatisés
   - Réviser les procédures de développement

---

## 📞 Contact Sécurité

En cas de découverte de vulnérabilité:
- Email: security@soon22.fr
- Ne PAS divulguer publiquement
- Délai de réponse: 24h maximum

---

**Dernière mise à jour**: 2025-01-15
**Prochaine révision**: 2025-02-15
**Responsable**: Soon22 Security Team
