# Fichiers à déployer sur O2switch (Production)

## 📦 Liste des fichiers modifiés/créés

### 1. Contrôleur principal
```
app/Controllers/ApiTokenController.php
```
**Modifications** :
- Ajout lecture fichier conf/privilege
- Ajout du dossier conf/ dans le SPK
- Ajout du fichier conf/privilege dans le SPK

### 2. Helper de création TAR
```
app/Helpers/TarCreator.php
```
**Modifications** :
- Ajout des champs uname="root" et gname="root" dans les headers TAR

### 3. Template SPK - Fichier INFO
```
storage/spk-template/INFO
```
**Contenu mis à jour** :
- `os_min_ver="7.0-40000"` (au lieu de 6.0-0000)
- `startable="no"` ajouté
- Suppression de `firmware`, `silent_install`, etc.

### 4. Template SPK - Fichier privilege (NOUVEAU)
```
storage/spk-template/conf/privilege
```
**Nouveau fichier JSON** :
```json
{
    "defaults": {
        "run-as": "package"
    }
}
```

### 5. Icônes du package
```
storage/spk-template/PACKAGE_ICON.PNG
storage/spk-template/PACKAGE_ICON_256.PNG
```
**Nouveaux fichiers** : Icônes générées avec le logo "SBN"

## 📂 Structure complète à déployer

```
sbn-v1/
├── app/
│   ├── Controllers/
│   │   └── ApiTokenController.php          ← MODIFIÉ
│   └── Helpers/
│       └── TarCreator.php                   ← MODIFIÉ
└── storage/
    └── spk-template/
        ├── INFO                              ← MODIFIÉ
        ├── PACKAGE_ICON.PNG                  ← NOUVEAU
        ├── PACKAGE_ICON_256.PNG              ← NOUVEAU
        ├── conf/                             ← NOUVEAU DOSSIER
        │   └── privilege                     ← NOUVEAU
        ├── package/
        │   ├── webhook.sh
        │   ├── config.sh.template
        │   └── README.md
        └── scripts/
            ├── installer
            └── start-stop-status
```

## 🚀 Procédure de déploiement

### Option 1 : Via FTP (FileZilla)

1. **Connecter à O2switch**
   - Host : ftp.your-domain.com
   - Port : 21
   - Protocole : FTP

2. **Naviguer vers le dossier de production**
   - Exemple : `/sbn/` ou `/public_html/sbn/`

3. **Créer le dossier conf/ (si nécessaire)**
   ```
   storage/spk-template/conf/
   ```

4. **Uploader les fichiers un par un** :
   - Mode : Binaire (important pour les PNG)
   - Écraser les fichiers existants

5. **Vérifier les permissions** :
   - Tous les fichiers : 644
   - Tous les dossiers : 755

### Option 2 : Via rsync/scp (si SSH disponible)

```bash
# Depuis votre machine locale
scp -r C:\xampp\htdocs\sbn-v1\app\Controllers\ApiTokenController.php user@host:/path/to/sbn/app/Controllers/
scp -r C:\xampp\htdocs\sbn-v1\app\Helpers\TarCreator.php user@host:/path/to/sbn/app/Helpers/
scp -r C:\xampp\htdocs\sbn-v1\storage\spk-template/* user@host:/path/to/sbn/storage/spk-template/
```

### Option 3 : Via Git (recommandé si Git est configuré)

```bash
# Dans le dossier local
git add .
git commit -m "Fix: SPK generation for DSM 7 - Add conf/privilege and update os_min_ver"
git push origin main

# Sur le serveur O2switch (via SSH)
cd /path/to/sbn
git pull origin main
```

## ✅ Checklist de déploiement

- [ ] **Backup** : Sauvegarder les fichiers actuels de production
- [ ] Créer le dossier `storage/spk-template/conf/` sur le serveur
- [ ] Uploader `ApiTokenController.php`
- [ ] Uploader `TarCreator.php`
- [ ] Uploader `INFO`
- [ ] Uploader `conf/privilege`
- [ ] Uploader `PACKAGE_ICON.PNG`
- [ ] Uploader `PACKAGE_ICON_256.PNG`
- [ ] Vérifier les permissions (644 pour fichiers, 755 pour dossiers)
- [ ] Tester sur production : https://sbn.soon22.fr
- [ ] Créer un token de test
- [ ] Télécharger un SPK de test
- [ ] Installer le SPK sur le DS1522+
- [ ] ✅ Confirmer que l'installation fonctionne

## 🧪 Test en production

1. Aller sur : https://sbn.soon22.fr
2. Se connecter
3. Paramètres → Tokens API
4. Créer un nouveau token (ou utiliser existant)
5. Cliquer sur "📦 Télécharger le package Synology"
6. Installer le SPK sur le NAS
7. **Vérifier que l'installation réussit** ✅

## ⚠️ Attention

- Les fichiers PNG doivent être uploadés en **mode binaire** (pas ASCII)
- Le fichier `conf/privilege` doit avoir des fins de ligne Unix (LF)
- Vérifier que le dossier `conf/` existe avant d'uploader le fichier `privilege`

## 🔄 Rollback en cas de problème

Si un problème survient en production :

1. Restaurer les fichiers depuis le backup
2. Redémarrer Apache/PHP-FPM si nécessaire
3. Vérifier les logs : `/var/log/apache2/error.log`

## 📞 Support

En cas de problème :
- Consulter `SOLUTION_SPK_DSM7.md` pour les détails techniques
- Vérifier les logs dans `storage/logs/`
- Tester en local d'abord avant de déployer

---

**Prêt pour le déploiement** : Oui ✅
**Testé en local** : Oui ✅
**Installation validée sur DS1522+** : Oui ✅
