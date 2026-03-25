# Test de la génération SPK via l'interface web

## 📋 Procédure de test

### 1. Redémarrer Apache
Dans XAMPP Control Panel :
- Cliquer sur "Stop" pour Apache
- Attendre 2 secondes
- Cliquer sur "Start"

### 2. Accéder à l'interface
- Ouvrir : http://localhost/sbn-v1
- Se connecter avec vos identifiants

### 3. Générer un SPK de test
- Aller dans : **Paramètres → Tokens API**
- Créer un nouveau token (si besoin)
- Cliquer sur le bouton **"📦 Télécharger le package Synology"**
- Le fichier sera téléchargé : `SBN-Backup-Notifier-[nom-du-token].spk`

### 4. Vérifier le contenu du SPK

Utiliser le script d'inspection :
```bash
cd C:\xampp\htdocs\sbn-v1\tools
php read-tar-content.php "C:\Users\Vous\Downloads\SBN-Backup-Notifier-xxx.spk"
```

**Vérifier que le SPK contient :**
```
INFO
PACKAGE_ICON.PNG
PACKAGE_ICON_256.PNG
conf/
conf/privilege         ← IMPORTANT : doit être présent
scripts/
scripts/installer
scripts/start-stop-status
package.tgz
```

### 5. Vérifier le fichier INFO

```bash
php tools/check-info-content.php
```

**Doit afficher :**
```
✅ os_min_ver est correct: 7.0-40000
✅ startable="no" est présent
```

### 6. Installer sur le NAS Synology

- Ouvrir le **Centre de paquets** sur votre DS1522+
- **Installation manuelle**
- Sélectionner le fichier SPK téléchargé
- **L'installation doit réussir** ✅

### 7. Vérifier l'installation

Sur le NAS, vérifier que :
- Le package apparaît dans le Centre de paquets
- Statut : Installé
- Aucune erreur dans les logs

## ⚠️ En cas de problème

### Erreur "Format de fichier non valide"
→ Le fichier INFO ne contient pas `os_min_ver="7.0-40000"`
→ Vérifier : `storage/spk-template/INFO`

### Erreur "Privilèges root"
→ Le fichier `conf/privilege` est manquant
→ Vérifier : `storage/spk-template/conf/privilege`

### Le SPK ne se télécharge pas
→ Vérifier les logs Apache : `C:\xampp\apache\logs\error.log`
→ Vérifier que TarCreator.php n'a pas d'erreur

### Erreur PHP
→ Redémarrer Apache
→ Vérifier les logs : `storage/logs/`

## ✅ Test réussi si :

- [x] Le SPK se télécharge sans erreur
- [x] Le fichier contient `conf/privilege`
- [x] Le fichier INFO contient `os_min_ver="7.0-40000"`
- [x] L'installation sur le NAS réussit
- [x] Le package apparaît dans le Centre de paquets

## 📤 Déploiement sur O2switch

Une fois le test local réussi, déployer ces fichiers :

```
app/Controllers/ApiTokenController.php
app/Helpers/TarCreator.php
storage/spk-template/INFO
storage/spk-template/conf/privilege         (nouveau dossier/fichier)
storage/spk-template/PACKAGE_ICON.PNG
storage/spk-template/PACKAGE_ICON_256.PNG
```

### Via FTP :
1. Connecter à O2switch
2. Aller dans `/sbn/` (ou le dossier de production)
3. Uploader les fichiers en respectant l'arborescence
4. **IMPORTANT** : Créer le dossier `storage/spk-template/conf/` si nécessaire
5. Tester sur la production : https://sbn.soon22.fr
