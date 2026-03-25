# Guide d'installation SBN v1.0

## Installation locale (XAMPP)

### 1. Prérequis

- XAMPP 8.0+ installé
- Navigateur web moderne
- Accès administrateur

### 2. Étapes d'installation

#### A. Déplacer les fichiers

Copier le dossier `sbn-v1` dans :
```
C:\xampp\htdocs\sbn-v1
```

#### B. Démarrer les services

1. Ouvrir XAMPP Control Panel
2. Démarrer Apache
3. Démarrer MySQL

#### C. Créer la base de données

1. Aller sur http://localhost/phpmyadmin
2. Créer une nouvelle base :
   - Nom : `sbn_dev`
   - Collation : `utf8mb4_unicode_ci`

3. Importer le schéma :
   - Onglet "Importer"
   - Choisir le fichier `database/schema.sql`
   - Cliquer sur "Exécuter"

#### D. Configurer l'application

1. Copier `.env.exemple` vers `.env`
2. Vérifier les paramètres dans `.env` :
```ini
DB_HOST=localhost
DB_NAME=sbn_dev
DB_USER=root
DB_PASS=
APP_URL=http://localhost/sbn-v1
```

#### E. Tester l'installation

1. Ouvrir http://localhost/sbn-v1
2. Vous devriez voir la page de connexion

#### F. Se connecter

Utilisez le compte de test :
- Email : `admin@proute.fr`
- Mot de passe : `pouet`

## Installation en production (o2switch)

### 1. Prérequis

- Compte d'hébergement ovh actif
- Accès cPanel
- Domaine configuré : sbn.proute.fr

### 2. Étapes d'installation

#### A. Créer la base de données

1. Se connecter à cPanel
2. Aller dans "MySQL Databases"
3. Créer une base :
   - Nom : `votre_user_sbn`
   - Créer un utilisateur
   - Attribuer tous les privilèges

#### B. Importer le schéma

1. Aller dans phpMyAdmin
2. Sélectionner la base créée
3. Importer `database/schema.sql`

#### C. Transférer les fichiers

Via FileZilla ou cPanel File Manager :

1. Se connecter en FTP
2. Aller dans `public_html/` (ou créer un sous-dossier)
3. Transférer tous les fichiers **SAUF** :
   - `.git/`
   - `.env` (à recréer)
   - `README.md`

#### D. Configurer l'environnement

1. Créer un fichier `.env` sur le serveur
2. Le remplir avec vos paramètres :

```ini
# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=votre_user_sbn
DB_USER=votre_user_sbn
DB_PASS=votre_mot_de_passe

# Application
APP_NAME="SBN v1.0"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sbn.proute.fr

# Email (à configurer avec vos paramètres o2switch)
MAIL_HOST=mail.proute.fr
MAIL_PORT=587
MAIL_USERNAME=noreply@sbn.proute.fr
MAIL_PASSWORD=votre_password
MAIL_FROM=noreply@sbn.proute.fr
MAIL_FROM_NAME="SBN Notifications"

# Sécurité
SESSION_LIFETIME=120
CSRF_TOKEN_NAME=_csrf_token

# API
API_RATE_LIMIT=100
```

#### E. Configurer les permissions

Via File Manager ou FTP :
```
storage/           755
storage/logs/      755
storage/cache/     755
```

#### F. Activer HTTPS

1. Éditer `.htaccess`
2. Décommenter les lignes HTTPS :
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### G. Sécuriser l'installation

1. Supprimer le compte admin de test :
   - Se connecter au dashboard
   - Créer un nouveau compte admin
   - Supprimer `admin@proute.fr`

2. Vérifier les headers de sécurité dans `.htaccess`

3. Activer le cookie secure :
```ini
php_value session.cookie_secure 1
```

#### H. Tester

1. Aller sur https://sbn.proute.fr
2. Vérifier la connexion HTTPS (cadenas)
3. Se connecter et tester les fonctionnalités

### 3. Configuration email o2switch

Dans cPanel :

1. Créer l'adresse email `noreply@sbn.proute.fr`
2. Noter les paramètres SMTP
3. Les ajouter dans `.env`

### 4. Sauvegarde

Configurer les sauvegardes automatiques :

1. Dans cPanel > Backup
2. Activer les sauvegardes quotidiennes
3. Sauvegarder :
   - Base de données
   - Fichiers du site

### 5. Monitoring

1. Surveiller les logs dans `storage/logs/`
2. Configurer les alertes cPanel
3. Vérifier régulièrement les sauvegardes

## Dépannage

### Erreur "Page non trouvée"

**Cause** : mod_rewrite non activé

**Solution XAMPP** :
1. Ouvrir `C:\xampp\apache\conf\httpd.conf`
2. Décommenter : `LoadModule rewrite_module modules/mod_rewrite.so`
3. Chercher `AllowOverride None` et le remplacer par `AllowOverride All`
4. Redémarrer Apache

**Solution o2switch** :
- mod_rewrite est activé par défaut

### Erreur de connexion à la base

**Vérifications** :
1. Paramètres dans `.env` corrects
2. Base de données créée
3. Utilisateur a les permissions
4. Services MySQL démarrés

### Erreur 500

**En développement** :
1. Activer le debug dans `.env` : `APP_DEBUG=true`
2. Consulter les logs dans `storage/logs/`

**En production** :
1. Vérifier les permissions des dossiers
2. Consulter les logs d'erreur
3. Vérifier le fichier `.env`

### Problèmes de session

**Solutions** :
1. Vérifier les permissions de `storage/`
2. Augmenter `session.gc_maxlifetime`
3. Vérifier que les cookies sont autorisés

## Support

Pour toute question :
- Email : contact@proute.fr
- Documentation : README.md
- Site web : https://proute.fr

---

**SBN v1.0** - Installation facile et sécurisée
