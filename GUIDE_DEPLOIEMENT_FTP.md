# 📤 Guide de Déploiement FTP vers O2switch

## 🎯 Objectif

Déployer le dossier `sbn-v1-production` vers https://sbn.proute.fr via FTP

---

## ✅ Préparation (déjà fait)

Le script a préparé le dossier de production :
- ✅ `.htaccess` restauré pour production (HTTPS forcé)
- ✅ Nouveau guide SPK copié
- ✅ Fichiers de développement exclus

---

## 📋 Checklist AVANT upload

### 1. Vérifier le fichier `.env` dans `sbn-v1-production`

Ouvrez : `C:\xampp\htdocs\sbn-v1-production\.env`

**Vérifiez que ces lignes sont correctes :**

```env
# DOIT être en production
APP_ENV=production
APP_DEBUG=false

# Credentials O2switch (déjà OK normalement)
DB_HOST=localhost
DB_NAME=rodz1475_sbn_dev
DB_USER=rodz1475_sbnjgdev
DB_PASS=sn6Y9RHa!9QPmLS@

# Email (déjà OK)
MAIL_HOST=sbn.proute.fr
MAIL_PORT=465
MAIL_USERNAME=alerts@sbn.proute.fr
MAIL_PASSWORD=.prouteolF!okw
```

⚠️ **IMPORTANT** : `APP_DEBUG` doit être `false` en production !

---

## 🚀 Déploiement FTP

### Étape 1 : Connexion FTP

**Informations de connexion** (celles que vous avez utilisées avant) :

- **Hôte** : `ftp.proute.fr` ou `ftp.sbn.proute.fr`
- **Utilisateur** : Votre compte O2switch
- **Port** : 21 (FTP) ou 22 (SFTP)
- **Protocole** : SFTP (recommandé, plus sécurisé)
- **Dossier distant** : `/sbn.proute.fr/` ou `/home/votre_user/sbn.proute.fr/`

### Étape 2 : Upload des fichiers

#### Option A : Upload complet (recommandé si peu de fichiers ont changé)

1. Connectez-vous au dossier `/sbn.proute.fr/` sur le serveur
2. **Uploadez ces fichiers/dossiers depuis `sbn-v1-production`** :
   - `.htaccess` ⚠️ **IMPORTANT** (remplace l'ancien)
   - `docs/` (contient le nouveau guide SPK)

3. **Vérifiez que ces dossiers sont déjà présents** (ne pas supprimer) :
   - `app/`
   - `config/`
   - `database/`
   - `public/`
   - `storage/`
   - `synology-scripts/`
   - `index.php`
   - `.env`

#### Option B : Upload sélectif (plus rapide)

Si vous voulez gagner du temps, uploadez **uniquement** :

1. **`.htaccess`** (depuis `sbn-v1-production/.htaccess`)
   - ⚠️ Remplace l'ancien (pour forcer HTTPS et CSP)

2. **`docs/SPK_GENERATOR_GUIDE.md`** (nouveau fichier)
   - Créez le dossier `docs/` s'il n'existe pas
   - Uploadez le fichier dedans

### Étape 3 : Nettoyage du serveur (IMPORTANT)

**Supprimez ces fichiers/dossiers du serveur** (s'ils existent) :

Via FTP ou Gestionnaire de fichiers cPanel :

```
❌ À SUPPRIMER du serveur :
   - tools/ (outils de debug locaux)
   - tests/ (tests automatisés)
   - SESSION_SUMMARY.md
   - SYNC_REPORT.md
   - .env.exemple
   - .env.production
   - sbn-v1.rar
   - sbn-v1.zip
   - .git/
   - .gitignore
```

**Ces fichiers ne doivent PAS être sur le serveur de production !**

### Étape 4 : Vérifier les permissions

Via le Gestionnaire de fichiers cPanel ou FTP :

```
Dossiers → 755 :
- storage/
- storage/logs/
- storage/cache/
- config/
- public/

Fichiers → 644 :
- .env
- .htaccess
- index.php
```

---

## ✅ Tests après déploiement

### 1. Test HTTPS
```
https://sbn.proute.fr
```
Doit :
- ✅ Afficher le site avec le cadenas vert
- ✅ Rediriger automatiquement de HTTP → HTTPS

### 2. Test du nouveau guide SPK
```
https://sbn.proute.fr (connecté)
→ Documentation
→ Vérifiez qu'il y a un lien vers le guide SPK
```

### 3. Test de génération SPK
1. Connectez-vous à https://sbn.proute.fr
2. Paramètres → Tokens API
3. Créez un token
4. Cliquez "Package .SPK"
5. Le fichier doit se télécharger !

### 4. Test des fichiers sensibles (sécurité)

Ces URLs doivent retourner une **erreur 403** (accès interdit) :

```
https://sbn.proute.fr/.env
https://sbn.proute.fr/config/
https://sbn.proute.fr/database/
https://sbn.proute.fr/storage/
https://sbn.proute.fr/app/
https://sbn.proute.fr/tools/  (si oublié de supprimer)
```

Si vous voyez le contenu → **PROBLÈME DE SÉCURITÉ** !

---

## 🐛 Dépannage

### Problème : Page blanche après upload

**Cause** : `.htaccess` ou erreur PHP

**Solution** :
1. Vérifiez `storage/logs/php_errors.log`
2. Vérifiez que `APP_DEBUG=false` dans `.env`
3. Consultez les logs d'erreur cPanel

### Problème : CSS ne charge pas

**Cause** : CSP trop strict ou permissions

**Solution** :
1. Vérifiez que `.htaccess` est bien uploadé
2. Forcez le cache (Ctrl+Shift+R)
3. Vérifiez les permissions de `public/`

### Problème : Erreur 500

**Cause** : `.htaccess` ou permissions

**Solution** :
1. Vérifiez la syntaxe de `.htaccess`
2. Vérifiez les permissions (755 pour dossiers, 644 pour fichiers)
3. Consultez `storage/logs/php_errors.log`

### Problème : Package SPK ne se génère pas

**Cause** : Extension PHP `phar` ou permissions

**Solution** :
1. Vérifiez que l'extension `phar` est activée dans cPanel
2. Vérifiez les permissions sur `/tmp/`
3. Vérifiez `storage/logs/php_errors.log`

---

## 📊 Résumé des fichiers à uploader

### Obligatoires (si changés)
- ✅ `.htaccess` (mis à jour pour production)
- ✅ `docs/SPK_GENERATOR_GUIDE.md` (nouveau)

### Déjà présents (ne pas toucher)
- ✅ `.env` (avec credentials production)
- ✅ `app/` (tous les contrôleurs, modèles, vues)
- ✅ `config/` (configuration)
- ✅ `database/` (schémas SQL)
- ✅ `docs/` (autres guides)
- ✅ `public/` (CSS, JS, images)
- ✅ `storage/` (cache, logs, spk-template)
- ✅ `synology-scripts/`
- ✅ `index.php`

### À NE PAS uploader (exclus)
- ❌ `tools/` (debug local)
- ❌ `tests/` (tests automatisés)
- ❌ `.git/` (historique Git)
- ❌ `.gitignore`
- ❌ `.env.exemple`
- ❌ Fichiers `.md` de dev (SESSION_SUMMARY, SYNC_REPORT)
- ❌ Archives (.rar, .zip)

---

## 🎯 Checklist finale

Avant de considérer le déploiement terminé :

- [ ] `.htaccess` uploadé et remplace l'ancien
- [ ] `docs/SPK_GENERATOR_GUIDE.md` uploadé
- [ ] Fichiers de dev supprimés du serveur (tools/, tests/)
- [ ] `.env` vérifié (APP_DEBUG=false)
- [ ] Permissions vérifiées (755/644)
- [ ] HTTPS fonctionne (https://sbn.proute.fr)
- [ ] Site affiche correctement
- [ ] Génération de SPK testée et fonctionne
- [ ] Fichiers sensibles protégés (test 403)
- [ ] Aucune erreur dans storage/logs/

---

## 📞 Support

Si vous rencontrez un problème :
1. Consultez les logs : `storage/logs/php_errors.log`
2. Vérifiez les logs cPanel
3. Testez en local d'abord (http://localhost/sbn-v1)

---

**Bon déploiement ! 🚀**
