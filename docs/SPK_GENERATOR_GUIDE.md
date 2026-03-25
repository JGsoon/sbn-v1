# 📦 Guide - Générateur de Packages SPK Personnalisés

## 🎯 Vue d'ensemble

Le système SBN génère automatiquement des packages Synology (.spk) **personnalisés** pour chaque client. Chaque package contient le token API unique du client, éliminant ainsi toute configuration manuelle.

---

## ✨ Fonctionnalités

### Pour l'utilisateur final

1. **Création simple** : Un clic pour créer un token API
2. **Téléchargement instantané** : Un clic pour télécharger son package personnalisé
3. **Installation automatique** : Upload du .spk dans le Centre de paquets Synology
4. **Zéro configuration** : Tout est pré-configuré avec son token unique

### Pour l'administrateur

- Génération automatique à la volée
- Aucune maintenance manuelle
- Logs d'audit complets
- Tokens révocables à tout moment

---

## 🔧 Architecture Technique

### Composants

```
app/
├── Controllers/
│   ├── ApiController.php              ← Endpoint webhook (/api/webhook)
│   └── ApiTokenController.php         ← Générateur SPK
│       ├── create()                   ← Créer un token API
│       ├── downloadSpk()              ← Télécharger le .spk
│       ├── generateSpkPackage()       ← Génération du package
│       └── download()                 ← Télécharger config.sh seul
│
├── Views/
│   └── settings/
│       └── api.php                    ← Interface utilisateur
│
storage/
└── spk-template/                      ← Template du package
    ├── INFO                           ← Métadonnées Synology
    ├── scripts/
    │   ├── installer                  ← Installation/désinstallation
    │   └── start-stop-status          ← Gestion du service
    └── package/
        ├── webhook.sh                 ← Script de notification
        ├── config.sh.template         ← Configuration (avec placeholders)
        └── README.md                  ← Guide utilisateur
```

### Routes

```php
'settings/api' => 'ApiTokenController@index'                    // Liste des tokens
'settings/api/create' => 'ApiTokenController@create'            // Créer un token
'settings/api/download-spk' => 'ApiTokenController@downloadSpk' // Télécharger .spk
'settings/api/download' => 'ApiTokenController@download'        // Télécharger config.sh
'api/webhook' => 'ApiController@webhook'                        // Endpoint webhook
```

---

## 🔄 Workflow Complet

### 1. Création du token API

```
Utilisateur → Paramètres → Tokens API
         ↓
Remplit le formulaire (nom du token)
         ↓
POST /settings/api/create
         ↓
ApiTokenController génère un token sécurisé :
- Préfixe : sbn_
- Longueur : 128 caractères (64 bytes)
- Cryptographiquement sécurisé (random_bytes)
         ↓
Enregistré dans DB (table: api_tokens)
         ↓
Affiché UNE SEULE FOIS dans une modal
```

### 2. Téléchargement du package SPK

```
Utilisateur → Clique "Package .SPK"
         ↓
GET /settings/api/download-spk?token_id=123
         ↓
ApiTokenController@downloadSpk():
         ↓
1. Récupère le token depuis la DB
2. Appelle generateSpkPackage()
         ↓
generateSpkPackage():
         ↓
a) Crée un dossier temporaire
b) Copie les fichiers du template
c) Remplace les placeholders dans config.sh.template :
   - {{API_URL}}        → https://sbn.soon22.fr/api/webhook
   - {{API_TOKEN}}      → Token unique du client
   - {{COMPANY_NAME}}   → Nom de l'entreprise
   - {{USER_EMAIL}}     → Email du client
d) Crée package.tgz (archive du dossier package/)
e) Crée le .spk final (archive contenant INFO, scripts/, package.tgz)
f) Nettoie les fichiers temporaires
         ↓
3. Force le téléchargement du fichier
4. Supprime le fichier temporaire
5. Log l'événement dans audit_logs
```

### 3. Installation sur Synology

```
Client → Centre de paquets
       ↓
Upload manuel du fichier .spk
       ↓
Synology exécute scripts/installer (preinst, postinst)
       ↓
Package installé dans : /var/packages/SBNBackupNotifier/target/
       ↓
Fichiers extraits :
├── config.sh      ← Configuration personnalisée (avec token)
├── webhook.sh     ← Script de notification
├── logs/          ← Dossier de logs
└── README.md      ← Instructions
```

### 4. Configuration Active Backup

```
Client → Active Backup for Business
       ↓
Paramètres → Notifications → Webhook
       ↓
Active le webhook personnalisé :
- Script : /var/packages/SBNBackupNotifier/target/webhook.sh
       ↓
Lors de chaque sauvegarde :
       ↓
Active Backup exécute webhook.sh avec variables d'environnement :
- ABB_TASK_NAME
- ABB_DEVICE_NAME
- ABB_STATUS
- ABB_BACKUP_SIZE
- etc.
       ↓
webhook.sh :
1. Charge config.sh (contient API_TOKEN et API_URL)
2. Crée un payload JSON avec les données
3. Envoie une requête POST à l'API
       ↓
POST https://sbn.soon22.fr/api/webhook
Headers:
  - X-API-Token: sbn_...
  - Content-Type: application/json
Body: { device_name, status, backup_size, ... }
```

### 5. Réception dans SBN

```
Requête arrive sur /api/webhook
       ↓
ApiController@webhook():
       ↓
1. Vérifie la méthode POST
2. Récupère le header X-API-Token
3. Valide le token dans la DB (table: api_tokens)
4. Parse le JSON
5. Valide les champs requis
6. Trouve ou crée le device (table: backup_devices)
7. Enregistre le backup (table: backups)
8. Crée une notification (table: notifications)
9. Retourne une réponse JSON
       ↓
Client voit la notification sur son dashboard !
```

---

## 📋 Structure du package .spk

Un fichier `.spk` est une archive tar contenant :

```
SBN-Backup-Notifier.spk
├── INFO                      ← Métadonnées (nom, version, arch, etc.)
├── scripts/
│   ├── installer             ← Gestion installation/désinstallation
│   └── start-stop-status     ← Gestion démarrage/arrêt (optionnel)
└── package.tgz               ← Archive contenant les fichiers du package
    └── (extrait dans /var/packages/SBNBackupNotifier/target/)
        ├── config.sh         ← Configuration personnalisée
        ├── webhook.sh        ← Script de notification
        └── README.md         ← Guide utilisateur
```

### Fichier INFO

```ini
package="SBNBackupNotifier"
version="1.0.0"
displayname="SBN Backup Notifier"
description="Moniteur de sauvegardes Active Backup avec notifications"
maintainer="Soon22"
maintainer_url="https://soon22.fr"
arch="noarch"
firmware="6.0-0000"
os_min_ver="6.0-0000"
```

### Fichier config.sh (personnalisé)

```bash
#!/bin/bash
# Configuration générée automatiquement

API_URL="https://sbn.soon22.fr/api/webhook"
API_TOKEN="sbn_abc123...xyz789"  # Token unique du client
COMPANY_NAME="Entreprise X"
USER_EMAIL="client@example.com"
LOG_RETENTION_DAYS=30
PACKAGE_VERSION="1.0.0"
```

### Script webhook.sh

Le script :
1. Charge `config.sh`
2. Récupère les variables d'environnement d'Active Backup
3. Construit un payload JSON
4. Envoie à l'API avec `curl`
5. Log le résultat

---

## 🔐 Sécurité

### Génération des tokens

```php
private function generateSecureToken($length = 64) {
    $randomBytes = random_bytes($length);
    $token = bin2hex($randomBytes);
    return 'sbn_' . $token;
}
```

- **random_bytes()** : Génération cryptographiquement sécurisée
- **128 caractères** : 2^512 combinaisons possibles
- **Préfixe** : Identification facile (sbn_)

### Validation API

Le webhook valide :
- ✅ Méthode POST uniquement
- ✅ Token présent dans les headers
- ✅ Token actif dans la base de données
- ✅ Token appartient à une société existante
- ✅ JSON valide
- ✅ Champs requis présents

### Logs d'audit

Chaque action est loguée :
- Création de token
- Téléchargement de package
- Révocation de token
- Suppression de token
- Webhook reçu (success/fail)

---

## 📊 Base de données

### Table: api_tokens

```sql
CREATE TABLE api_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    last_used_at DATETIME,
    created_at DATETIME,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Table: backup_devices

```sql
CREATE TABLE backup_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    os_version VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    last_seen DATETIME,
    created_at DATETIME,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

### Table: backups

```sql
CREATE TABLE backups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    device_id INT NOT NULL,
    task_name VARCHAR(255),
    status ENUM('success', 'failed', 'warning', 'running'),
    start_time DATETIME,
    end_time DATETIME,
    duration INT,
    backup_size BIGINT,
    backup_type VARCHAR(50),
    error_message TEXT,
    created_at DATETIME,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (device_id) REFERENCES backup_devices(id)
);
```

---

## 🧪 Tests

Pour tester le système complet :

### 1. Test de génération de token

```bash
# Via interface ou curl
curl -X POST http://localhost/sbn-v1/settings/api/create \
  -H "Cookie: session=..." \
  -d "csrf_token=xxx&token_name=TEST-NAS-01"
```

### 2. Test de téléchargement SPK

```bash
curl -X GET "http://localhost/sbn-v1/settings/api/download-spk?token_id=1" \
  -H "Cookie: session=..." \
  -o test-package.spk
```

### 3. Test du webhook (simuler Active Backup)

```bash
curl -X POST http://localhost/sbn-v1/api/webhook \
  -H "X-API-Token: sbn_your_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "device_name": "NAS-TEST-01",
    "status": "success",
    "task_name": "Backup Daily",
    "start_time": "2026-03-23 10:00:00",
    "end_time": "2026-03-23 10:30:00",
    "duration": 1800,
    "backup_size": 1073741824,
    "backup_type": "full"
  }'
```

Réponse attendue :
```json
{
  "success": true,
  "message": "Backup data received successfully",
  "backup_id": 42
}
```

---

## 🐛 Dépannage

### Problème : Le .spk ne se génère pas

**Vérifier :**
1. Extension PHP `phar` activée
2. Permissions d'écriture sur `/tmp/` ou `sys_get_temp_dir()`
3. Template existe dans `storage/spk-template/`
4. Logs dans `storage/logs/php_errors.log`

### Problème : Le webhook ne reçoit rien

**Vérifier :**
1. Token actif dans la base de données
2. URL correcte : `https://sbn.soon22.fr/api/webhook`
3. Header `X-API-Token` présent
4. Logs Active Backup sur le NAS
5. Logs SBN dans `storage/logs/`

### Problème : Erreur 401 Unauthorized

**Causes possibles :**
- Token révoqué ou supprimé
- Token mal copié (espaces, retours à la ligne)
- Header manquant dans la requête

---

## 📈 Améliorations futures

### Court terme

- [ ] Vérification automatique des mises à jour du package
- [ ] Statistiques d'utilisation des tokens
- [ ] Export des logs webhook en CSV
- [ ] Notifications email lors de l'utilisation d'un nouveau token

### Moyen terme

- [ ] Support multi-versions de packages (DSM 6 vs DSM 7)
- [ ] Intégration avec d'autres solutions de backup (Veeam, etc.)
- [ ] Dashboard en temps réel avec WebSockets
- [ ] API REST complète pour gestion programmatique

### Long terme

- [ ] Marketplace Synology officiel
- [ ] Support d'autres plateformes NAS (QNAP, TrueNAS)
- [ ] Application mobile (iOS/Android)
- [ ] Intégrations tierces (Slack, Teams, Discord)

---

## 📚 Ressources

### Documentation Synology

- [Package Center Developer Guide](https://help.synology.com/developer-guide/)
- [DSM 7.0 Developer Guide](https://help.synology.com/developer-guide/synology_package/index.html)
- [Active Backup API](https://www.synology.com/support/download)

### Documentation SBN

- `docs/GUIDE_INSTALLATION_SPK.md` - Guide d'installation du package
- `docs/GUIDE_UPLOAD_SBN_SOON22.md` - Guide de déploiement
- `docs/PROJECT_SUMMARY.md` - Vue d'ensemble du projet

### Code source

- `app/Controllers/ApiTokenController.php:342-500` - Générateur SPK
- `app/Controllers/ApiController.php:25-134` - Endpoint webhook
- `storage/spk-template/` - Template du package

---

## ✅ Checklist de déploiement

Avant de mettre en production :

- [ ] Tests de génération de .spk sur plusieurs navigateurs
- [ ] Test d'installation sur DSM 6.x et DSM 7.x
- [ ] Validation du webhook avec vraies données Active Backup
- [ ] Vérification des logs (aucune erreur)
- [ ] Test de révocation de token (webhook doit échouer)
- [ ] Test de suppression de token (webhook doit échouer)
- [ ] Documentation à jour
- [ ] Backup de la base de données
- [ ] Monitoring en place (Sentry, logs, etc.)

---

**Développé avec ❤️ par Soon22 - Johnny Girault**

Version 1.0.0 - Mars 2026
