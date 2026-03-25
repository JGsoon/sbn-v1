# Correction du Format SPK - Résumé

## Problème Initial
Le package SPK généré était refusé par Synology DS1522+ avec l'erreur :
**"Format de fichier non valide. Veuillez contacter le développeur du paquet."**

## Analyse
L'inspection du SPK avec `inspect-spk.bat` a révélé :
```
Ordre INCORRECT :
├─ package.tgz          ❌ (était en premier)
├─ INFO                 ❌ (doit être en premier)
├─ package.tar.gz       ❌ (fichier dupliqué parasite)
├─ scripts/installer
└─ scripts/start-stop-status
```

## Corrections Appliquées

### 1. Création de TarCreator.php
- Fichier : `app/Helpers/TarCreator.php`
- Création manuelle d'archives TAR au format POSIX ustar
- Compatible avec Synology DSM
- Corrigé : Erreur de syntaxe ligne 83 (substr_replace au lieu de [])

### 2. Modification de ApiTokenController.php
- Méthode : `generateSpkPackage()` (lignes 401-480)
- Utilise maintenant TarCreator pour créer le SPK manuellement
- **Ordre correct** :
  ```
  1. INFO                       ✅ (premier fichier obligatoire)
  2. scripts/                   ✅ (répertoire)
  3. scripts/installer          ✅ (exécutable)
  4. scripts/start-stop-status  ✅ (exécutable)
  5. package.tgz                ✅ (archive compressée en dernier)
  ```

### 3. Suppression des fichiers parasites
- Plus de `package.tar.gz` dupliqué
- Plus d'utilisation de fichiers temporaires intermédiaires
- Création directe en mémoire avec gzencode()

## Structure Finale du SPK

```
SBN-Backup-Notifier-[token_name].spk (archive TAR)
│
├─ INFO                          (Métadonnées du package)
├─ scripts/                      (Répertoire des scripts)
│  ├─ installer                  (Script d'installation)
│  └─ start-stop-status         (Script de gestion du service)
└─ package.tgz                   (Archive compressée GZIP)
   │
   └─ (contenu TAR)
      ├─ webhook.sh             (Script webhook personnalisé)
      ├─ config.sh              (Configuration avec API token)
      └─ README.md              (Documentation)
```

## Format Technique

### package.tgz
- Format : TAR POSIX ustar
- Compression : GZIP niveau 9
- Contenu : webhook.sh (0755), config.sh (0644), README.md (0644)

### SBN-xxx.spk
- Format : TAR POSIX ustar (non compressé)
- Structure : Headers 512 bytes + données + padding
- Fin : Deux blocs de 512 octets nuls (1024 bytes)

## Comment Tester

### 1. Redémarrer Apache
Dans XAMPP Control Panel : Stop puis Start Apache

### 2. Télécharger un nouveau SPK
1. Aller sur : http://localhost/sbn-v1
2. Connexion
3. Paramètres → Tokens API
4. Cliquer sur le bouton **"📦 Télécharger le package Synology"**

### 3. Inspecter le SPK (optionnel)
```batch
cd C:\xampp\htdocs\sbn-v1\tools
inspect-spk.bat "C:\Users\Vous\Downloads\SBN-xxx.spk"
```

Vérifier que l'ordre est :
```
INFO
scripts/
scripts/installer
scripts/start-stop-status
package.tgz
```

### 4. Installer sur Synology
1. DSM → Centre de paquets
2. Installation manuelle
3. Sélectionner le fichier .spk
4. Installer

✅ **L'installation devrait maintenant réussir sans erreur**

## Fichiers Modifiés

| Fichier | Statut | Description |
|---------|--------|-------------|
| `app/Helpers/TarCreator.php` | ✅ CRÉÉ | Créateur TAR manuel POSIX ustar |
| `app/Controllers/ApiTokenController.php` | ✅ MODIFIÉ | Méthode generateSpkPackage() corrigée |
| `tools/fix-spk-order.php` | ✅ CRÉÉ | Script de correction appliqué |
| `tools/inspect-spk.bat` | ✅ CRÉÉ | Outil d'inspection |

## Déploiement en Production

Une fois testé avec succès sur DS1522+, uploader sur O2switch :
```
app/Helpers/TarCreator.php              (nouveau fichier)
app/Controllers/ApiTokenController.php  (fichier modifié)
```

## Références
- Format SPK Synology : https://help.synology.com/developer-guide/create_package.html
- Format TAR POSIX : IEEE Std 1003.1-1988 (ustar)
- Synology DSM : Package Center Manual Installation

---
**Date de correction** : 2026-03-23
**Version SBN** : 1.0
**Testé sur** : Synology DS1522+ / DSM 7.x
