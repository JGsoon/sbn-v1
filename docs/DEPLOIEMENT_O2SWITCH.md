# Guide de Déploiement sur O2switch

## Prérequis

- Compte d'hébergement O2switch actif
- Accès à cPanel
- FileZilla ou tout autre client FTP/SFTP
- Accès à phpMyAdmin (fourni par O2switch)

---

## Étape 1 : Préparation de la base de données

### 1.1 Créer la base de données MySQL

1. Connectez-vous à **cPanel**
2. Allez dans **Bases de données MySQL**
3. Créez une nouvelle base de données :
   - Nom : `sbn_production` (ou autre nom de votre choix)
4. Créez un utilisateur MySQL :
   - Nom d'utilisateur : `sbn_user`
   - Générez un mot de passe sécurisé
5. Associez l'utilisateur à la base de données avec **TOUS LES PRIVILÈGES**

> **Note** : O2switch ajoute automatiquement un préfixe à vos bases/utilisateurs (ex: `cpanel_user_sbn_production`)

### 1.2 Importer le schéma de base de données

1. Ouvrez **phpMyAdmin** depuis cPanel
2. Sélectionnez votre base de données
3. Cliquez sur l'onglet **Importer**
4. Importez dans cet ordre :
   ```
   database/schema.sql
   database/add_phone_column.sql
   database/add_smtp_config.sql
   database/add_shared_access.sql
   database/fix_api_tokens.sql
   database/add_roles_subscription_sharing.sql
   ```

---

## Étape 2 : Upload des fichiers

### 2.1 Préparer les fichiers en local

Avant l'upload, vérifiez que votre dossier contient :
- ✅ Tous les fichiers de l'application
- ✅ Le fichier `.htaccess`
- ✅ Le fichier `.env.production` (sera renommé après upload)
- ❌ PAS de dossier `vendor` (si vous utilisez Composer)
- ❌ PAS de fichier `.env` avec vos données locales

### 2.2 Structure O2switch

O2switch utilise généralement cette structure :
```
/home/votre_user/
├── public_html/           ← Domaine principal (NE PAS TOUCHER si sites existants)
│   ├── cgi-bin/
│   └── (autres sites)
├── sbn.soon22.fr/         ← Dossier du sous-domaine SBN (DESTINATION)
│   └── (vide pour l'instant)
└── autres-domaines.fr/
```

**IMPORTANT** : Uploadez dans le dossier **`sbn.soon22.fr/`**, PAS dans `public_html/` !

### 2.3 Upload via FTP/SFTP

**Structure finale dans `sbn.soon22.fr/`** :
```
sbn.soon22.fr/             ← Dossier racine du sous-domaine
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Core/
│   └── .htaccess
├── config/
│   ├── config.php
│   ├── helpers.php
│   ├── routes.php
│   └── .htaccess
├── database/
│   ├── schema.sql
│   └── .htaccess
├── public/
│   ├── css/
│   └── js/
├── storage/
│   ├── logs/
│   └── cache/
├── .htaccess              ← Important : à la racine de sbn.soon22.fr/
├── .env.production        ← À renommer en .env après upload
├── index.php
└── (tous les autres fichiers)
```

### 2.4 Paramètres FTP recommandés

**Pour sbn.soon22.fr** :
- **Hôte** : ftp.soon22.fr ou ftp.sbn.soon22.fr
- **Utilisateur** : Votre compte O2switch (généralement fourni par email)
- **Port** : 21 (FTP) ou 22 (SFTP recommandé)
- **Protocole** : **SFTP** (plus sécurisé)
- **Mode de transfert** : Binaire
- **Dossier distant** : `/sbn.soon22.fr/` ou `/home/votre_user/sbn.soon22.fr/`

> **Note** : Les chemins exacts peuvent varier. Vérifiez dans cPanel > "Domaines" ou "Sous-domaines" pour confirmer le chemin.

---

## Étape 3 : Configuration de l'application

### 3.1 Configurer le fichier .env

Via le **Gestionnaire de fichiers** de cPanel :

1. Naviguez vers le dossier `sbn.soon22.fr/`
2. Renommez `.env.production` en `.env`
3. Éditez le fichier `.env` et remplissez :

```env
# Base de données (informations depuis cPanel > Bases de données MySQL)
DB_HOST=localhost
DB_NAME=votre_prefix_sbn_production
DB_USER=votre_prefix_sbn_user
DB_PASS=votre_mot_de_passe_mysql

# Application
APP_ENV=production
APP_DEBUG=false
# Laisser vide pour auto-détection, ou spécifier :
APP_URL=https://sbn.soon22.fr

# Email (SMTP O2switch)
MAIL_HOST=mail.soon22.fr
MAIL_PORT=587
MAIL_USERNAME=noreply@soon22.fr
MAIL_PASSWORD=mot_de_passe_email
MAIL_FROM=noreply@soon22.fr
MAIL_FROM_NAME="SBN Notifications"
```

> **Note O2switch** : Le préfixe MySQL dépend de votre compte (ex: `o2switch_user_sbn_prod`)

### 3.2 Vérifier le .htaccess

Le `.htaccess` est déjà configuré pour O2switch et **fonctionne automatiquement** pour `sbn.soon22.fr`.

**RIEN à modifier** : Le système détecte automatiquement qu'il est à la racine du sous-domaine.

> ✅ Le `RewriteBase` n'est pas nécessaire car l'application est à la racine de `sbn.soon22.fr/`

### 3.3 Activer HTTPS (recommandé)

1. Dans cPanel, allez dans **SSL/TLS Status**
2. Activez **Let's Encrypt SSL** (gratuit)
3. Une fois activé, éditez `.htaccess` et décommentez :
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## Étape 4 : Permissions des dossiers

Via le **Gestionnaire de fichiers** de cPanel, définissez les permissions :

```
storage/          → 755 ou 775
storage/logs/     → 755 ou 775
storage/cache/    → 755 ou 775
config/           → 755
public/           → 755
.env              → 644 (lecture seule)
.htaccess         → 644
```

**Comment changer les permissions** :
1. Faites un clic droit sur le dossier/fichier
2. Choisissez **Modifier les permissions**
3. Entrez la valeur (755, 644, etc.)

---

## Étape 5 : Configuration PHP (optionnel)

### 5.1 Version PHP

1. Dans cPanel, allez dans **Sélecteur PHP**
2. Choisissez **PHP 7.4** ou supérieur (8.0/8.1 recommandé)

### 5.2 Extensions PHP requises

Vérifiez que ces extensions sont activées (généralement déjà activées sur O2switch) :
- ✅ mysqli
- ✅ pdo_mysql
- ✅ session
- ✅ json
- ✅ openssl
- ✅ mbstring
- ✅ curl

---

## Étape 6 : Vérification et tests

### 6.1 Test d'accès

Accédez à votre application :
```
https://sbn.soon22.fr
```

Vous devriez voir la page de connexion de SBN v1.0.

**En cas d'erreur** :
- Vérifiez que le sous-domaine `sbn.soon22.fr` est bien configuré dans cPanel
- Vérifiez que les fichiers sont bien dans `sbn.soon22.fr/` et non dans un sous-dossier
- Consultez `storage/logs/php_errors.log` via le Gestionnaire de fichiers

### 6.2 Créer le premier compte administrateur

1. Accédez à `https://sbn.soon22.fr/register`
2. Créez le compte administrateur
3. Connectez-vous avec ce compte

> **Note** : Le premier compte créé devient automatiquement administrateur

### 6.3 Vérifier les logs

Si problème, vérifiez les logs :
- `storage/logs/php_errors.log`
- `storage/logs/app.log`

Via le **Gestionnaire de fichiers** de cPanel.

---

## Étape 7 : Configuration Email (SMTP)

### 7.1 Créer un compte email

Dans cPanel > **Comptes de messagerie** :
1. Créez un compte : `noreply@votre-domaine.fr`
2. Notez les paramètres SMTP

### 7.2 Configurer dans l'application

Dans l'interface SBN :
1. Allez dans **Paramètres** > **Configuration SMTP**
2. Entrez les informations du compte email
3. Testez l'envoi

---

## Étape 8 : Sécurité post-déploiement

### 8.1 Vérifier les fichiers sensibles

Testez que ces URLs retournent une erreur 403/404 :
```
https://sbn.soon22.fr/.env
https://sbn.soon22.fr/config/
https://sbn.soon22.fr/database/
https://sbn.soon22.fr/storage/
https://sbn.soon22.fr/app/
```

Si ces URLs sont accessibles, vérifiez que les fichiers `.htaccess` sont bien présents dans chaque dossier.

### 8.2 Changer les mots de passe

- ✅ Mot de passe base de données (si test)
- ✅ Mot de passe compte admin
- ✅ Mot de passe FTP

### 8.3 Activer le mode production

Vérifiez dans `.env` :
```env
APP_ENV=production
APP_DEBUG=false
```

---

## Mise à jour via GitHub (future)

### Structure prévue

Une fois le dépôt GitHub configuré :

```bash
# Sur O2switch, via SSH (si disponible)
cd /home/votre_user/www/
git pull origin main
```

Ou via **Git Version Control** dans cPanel.

### Fichiers à exclure du Git

Créez un `.gitignore` :
```
.env
storage/logs/*.log
storage/cache/*
!storage/logs/.gitkeep
!storage/cache/.gitkeep
vendor/
```

---

## Résolution des problèmes courants

### Erreur 500 - Internal Server Error

**Causes possibles** :
1. Erreur dans `.htaccess`
   - Solution : Vérifiez la syntaxe, commentez les règles une par une
2. Permissions incorrectes
   - Solution : `storage/` doit être en 755 ou 775
3. Erreur PHP
   - Solution : Vérifiez `storage/logs/php_errors.log`

### Page blanche

**Causes possibles** :
1. `APP_DEBUG=false` cache les erreurs
   - Solution temporaire : Passez en `APP_DEBUG=true` pour voir l'erreur
2. Problème de base de données
   - Solution : Vérifiez les identifiants dans `.env`

### CSS/JS ne se chargent pas

**Causes possibles** :
1. Chemin `APP_URL` incorrect
   - Solution : Vérifiez ou laissez vide pour auto-détection
2. Règles `.htaccess` bloquent `/public/`
   - Solution : Vérifiez la règle `RewriteCond %{REQUEST_URI} !^/public/`

### Connexion base de données échoue

**Causes possibles** :
1. Identifiants incorrects
   - Solution : Vérifiez les infos dans cPanel > Bases de données MySQL
2. Utilisateur non associé à la base
   - Solution : Associez l'utilisateur avec tous les privilèges

---

## Checklist finale

Avant de mettre en production :

- [ ] Base de données créée et importée
- [ ] Fichiers uploadés via FTP
- [ ] `.env` configuré avec les bonnes informations
- [ ] Permissions des dossiers correctes (755/644)
- [ ] SSL activé (HTTPS)
- [ ] Compte administrateur créé
- [ ] SMTP configuré et testé
- [ ] Fichiers sensibles protégés (.env, config/, etc.)
- [ ] Mode production activé (`APP_DEBUG=false`)
- [ ] Tests de connexion et navigation OK

---

## Support O2switch

- **Documentation** : https://faq.o2switch.fr
- **Support** : Via ticket depuis l'espace client
- **Forum** : https://forum.o2switch.fr

---

## Contact

Pour toute question concernant l'application SBN :
- Développeur : Johnny Girault
- Site : https://soon22.fr

---

**Bon déploiement !** 🚀
