# Créer un SPK avec les outils officiels Synology

## Le problème

Les SPK ne sont PAS de simples archives TAR. Synology utilise un format propriétaire créé par `PkgCreate.py` qui ajoute des métadonnées/signatures spéciales.

## Solution : Utiliser l'environnement de développement Synology

### Option 1 : Avec WSL (Windows Subsystem for Linux)

Si vous avez WSL installé sur Windows :

1. **Ouvrir WSL (Ubuntu)**
   ```bash
   wsl
   ```

2. **Installer les outils nécessaires**
   ```bash
   sudo apt update
   sudo apt install git python3 python3-pip
   ```

3. **Cloner le toolkit Synology**
   ```bash
   cd ~
   git clone https://github.com/SynologyOpenSource/pkgscripts-ng.git
   ```

4. **Créer la structure du package**
   ```bash
   mkdir -p ~/sbn-package/SBNBackupNotifier
   cd ~/sbn-package/SBNBackupNotifier
   ```

5. **Copier nos fichiers**
   - INFO
   - scripts/installer
   - scripts/start-stop-status
   - package/webhook.sh
   - package/config.sh
   - package/README.md
   - PACKAGE_ICON.PNG
   - PACKAGE_ICON_256.PNG

6. **Créer le SPK**
   ```bash
   cd ~/pkgscripts-ng
   ./PkgCreate.py -v 7.0 -p noarch -c SBNBackupNotifier
   ```

### Option 2 : Avec Docker (Plus simple)

1. **Créer un Dockerfile pour builder le SPK**
   ```dockerfile
   FROM ubuntu:22.04

   RUN apt-get update && apt-get install -y \
       git python3 python3-pip tar gzip

   WORKDIR /build

   # Cloner les outils
   RUN git clone https://github.com/SynologyOpenSource/pkgscripts-ng.git

   # Copier notre package
   COPY ./spk-source /build/source

   CMD ["/build/pkgscripts-ng/PkgCreate.py", "-c", "/build/source"]
   ```

2. **Builder et exécuter**
   ```bash
   docker build -t spk-builder .
   docker run -v C:\xampp\htdocs\sbn-v1\storage\spk-template:/build/source spk-builder
   ```

### Option 3 : En PHP directement (Analyser un SPK existant)

On peut télécharger un SPK officiel Synology, l'analyser byte par byte, et reproduire le format exact.

## Prochaines étapes

Quelle option préférez-vous ?
1. WSL (si déjà installé)
2. Docker (nécessite Docker Desktop)
3. Analyse d'un SPK existant et reproduction en PHP
