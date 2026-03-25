# Guide d'Upload pour sbn.soon22.fr

## Structure O2switch actuelle

```
/home/votre_user/
├── public_html/           ← Contient déjà des sites (NE PAS TOUCHER)
│   ├── cgi-bin/
│   └── (autres sites existants)
└── sbn.soon22.fr/         ← DESTINATION (actuellement vide)
```

---

## Étapes d'upload

### 1️⃣ Connexion FTP/SFTP

**Paramètres FileZilla (ou autre client FTP)** :

```
Hôte     : ftp.soon22.fr (ou ftp.sbn.soon22.fr)
Protocole: SFTP (SSH File Transfer Protocol) - Recommandé
Port     : 22 (SFTP) ou 21 (FTP)
Utilisateur: [votre compte O2switch]
Mot de passe: [votre mot de passe O2switch]
```

**Après connexion** : Naviguez vers le dossier `/sbn.soon22.fr/`

---

### 2️⃣ Upload des fichiers

**Depuis votre local** : `C:\xampp\htdocs\sbn-v1\`

**Vers O2switch** : `/sbn.soon22.fr/`

**Sélectionnez TOUS les fichiers et dossiers** :
```
✅ app/
✅ config/
✅ database/
✅ public/
✅ storage/
✅ synology-scripts/
✅ .htaccess          ← IMPORTANT : fichiers cachés
✅ .env.production    ← À renommer après
✅ .gitignore
✅ index.php
✅ check-production-ready.php
✅ DEPLOIEMENT_O2SWITCH.md
✅ README.md
✅ tous les autres fichiers...
```

**À NE PAS uploader** :
```
❌ .env (fichier local avec données XAMPP)
❌ .git/ (si vous avez un dépôt Git local)
❌ vendor/ (si présent)
❌ node_modules/ (si présent)
❌ *.code-workspace
❌ sbn-v1.code-workspace
```

**Mode de transfert** : Automatique ou Binaire

---

### 3️⃣ Vérifier l'upload

Après upload, la structure dans `/sbn.soon22.fr/` doit être :

```
/sbn.soon22.fr/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Core/
│   └── .htaccess
├── config/
│   ├── autoload.php
│   ├── config.php
│   ├── database.php
│   ├── helpers.php
│   ├── routes.php
│   └── .htaccess
├── database/
│   ├── schema.sql
│   ├── add_phone_column.sql
│   ├── add_smtp_config.sql
│   ├── add_shared_access.sql
│   ├── fix_api_tokens.sql
│   ├── add_roles_subscription_sharing.sql
│   └── .htaccess
├── public/
│   ├── css/
│   │   ├── main.css
│   │   ├── modern.css
│   │   ├── dashboard.css
│   │   └── auth.css
│   └── js/
│       └── main.js
├── storage/
│   ├── cache/
│   ├── logs/
│   └── .htaccess
├── synology-scripts/
│   └── ...
├── .htaccess              ← À la racine !
├── .env.production        ← Renommer en .env
├── .gitignore
├── index.php              ← Point d'entrée
├── check-production-ready.php
├── DEPLOIEMENT_O2SWITCH.md
├── GUIDE_UPLOAD_SBN_SOON22.md
├── RESUME_MIGRATION.md
├── README.md
└── ...
```

---

### 4️⃣ Configuration post-upload

#### Via le Gestionnaire de fichiers cPanel

1. **Renommer .env.production**
   - Faites un clic droit sur `.env.production`
   - Choisir "Renommer"
   - Nouveau nom : `.env`

2. **Éditer le fichier .env**
   - Clic droit sur `.env` → "Modifier"
   - Remplissez les informations :

```env
# Base de données
DB_HOST=localhost
DB_NAME=o2switch_votre_prefix_sbn
DB_USER=o2switch_votre_prefix_user
DB_PASS=mot_de_passe_mysql_fort

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sbn.soon22.fr

# Email
MAIL_HOST=mail.soon22.fr
MAIL_PORT=587
MAIL_USERNAME=noreply@soon22.fr
MAIL_PASSWORD=mot_de_passe_email
MAIL_FROM=noreply@soon22.fr
```

3. **Vérifier les permissions**
   - `storage/logs/` → 755
   - `storage/cache/` → 755
   - `.env` → 644 (lecture seule)

---

### 5️⃣ Configuration de la base de données

#### Dans cPanel > Bases de données MySQL

1. **Créer la base de données**
   - Nom : `sbn_production` (avec votre préfixe)
   - Exemple final : `o2switch_user_sbn_production`

2. **Créer l'utilisateur**
   - Nom : `sbn_user`
   - Générer un mot de passe sécurisé
   - Exemple final : `o2switch_user_sbn_user`

3. **Associer utilisateur à la base**
   - Sélectionner l'utilisateur et la base
   - Cocher **TOUS LES PRIVILÈGES**

#### Dans phpMyAdmin

4. **Importer les fichiers SQL** (dans l'ordre) :
   ```
   1. database/schema.sql
   2. database/add_phone_column.sql
   3. database/add_smtp_config.sql
   4. database/add_shared_access.sql
   5. database/fix_api_tokens.sql
   6. database/add_roles_subscription_sharing.sql
   ```

---

### 6️⃣ Vérifier le sous-domaine

#### Dans cPanel > Domaines ou Sous-domaines

Vérifiez que `sbn.soon22.fr` pointe vers `/sbn.soon22.fr/`

Si non configuré :
1. Allez dans **Sous-domaines**
2. Créer un sous-domaine :
   - Sous-domaine : `sbn`
   - Domaine : `soon22.fr`
   - Racine du document : `/home/votre_user/sbn.soon22.fr`

---

### 7️⃣ Activer SSL (HTTPS)

#### Dans cPanel > SSL/TLS Status

1. Rechercher `sbn.soon22.fr`
2. Activer **AutoSSL** (Let's Encrypt gratuit)
3. Attendre 2-5 minutes

Une fois activé, décommentez dans `.htaccess` :
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

### 8️⃣ Test final

1. **Accéder à l'application** :
   ```
   https://sbn.soon22.fr
   ```

2. **Page de connexion visible** ? ✅ Succès !

3. **Créer le compte admin** :
   ```
   https://sbn.soon22.fr/register
   ```

4. **Tester la sécurité** (doivent retourner 403/404) :
   ```
   https://sbn.soon22.fr/.env          ← Interdit
   https://sbn.soon22.fr/config/       ← Interdit
   https://sbn.soon22.fr/database/     ← Interdit
   https://sbn.soon22.fr/storage/      ← Interdit
   ```

---

## Résolution de problèmes

### Erreur 500 - Internal Server Error

**Causes** :
- Permissions incorrectes sur `storage/`
- Erreur de syntaxe dans `.htaccess`
- Erreur PHP

**Solutions** :
1. Vérifier `storage/logs/php_errors.log`
2. Vérifier permissions : `storage/` → 755
3. Temporairement, dans `.env` : `APP_DEBUG=true` pour voir l'erreur

---

### Page blanche

**Causes** :
- Problème de connexion base de données
- Erreur PHP non affichée

**Solutions** :
1. Vérifier les identifiants dans `.env`
2. Vérifier que la base de données existe
3. Activer temporairement `APP_DEBUG=true`

---

### CSS/JS ne se chargent pas

**Causes** :
- Problème de chemin
- Règles `.htaccess` trop restrictives

**Solutions** :
1. Vérifier que `APP_URL` est correct dans `.env`
2. Ou laisser `APP_URL` vide pour auto-détection
3. Vérifier que `public/css/` et `public/js/` existent

---

### Sous-domaine ne résout pas

**Causes** :
- DNS pas encore propagé
- Sous-domaine mal configuré

**Solutions** :
1. Attendre 5-10 minutes (propagation DNS)
2. Vérifier dans cPanel > Sous-domaines
3. Vérifier le chemin de la racine du document

---

## Checklist complète

- [ ] Connexion FTP/SFTP OK
- [ ] Upload dans `/sbn.soon22.fr/` (pas `public_html/`)
- [ ] Tous les fichiers uploadés (sauf `.env` local)
- [ ] `.env.production` renommé en `.env`
- [ ] `.env` configuré avec identifiants O2switch
- [ ] Base de données créée
- [ ] Utilisateur MySQL créé et associé
- [ ] Fichiers SQL importés (6 fichiers)
- [ ] Permissions définies (755/644)
- [ ] Sous-domaine `sbn.soon22.fr` configuré
- [ ] SSL Let's Encrypt activé
- [ ] Test d'accès : `https://sbn.soon22.fr` ✅
- [ ] Compte admin créé
- [ ] Test sécurité (`.env` non accessible) ✅
- [ ] SMTP configuré dans l'interface

---

## Assistance

- **Documentation complète** : `DEPLOIEMENT_O2SWITCH.md`
- **Support O2switch** : https://faq.o2switch.fr
- **Développeur** : Johnny Girault - https://soon22.fr

---

**Durée estimée du déploiement** : 20-30 minutes

**Bon déploiement !** 🚀
