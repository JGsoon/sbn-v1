#!/bin/bash
################################################################################
# SBN v1.0 - Script d'installation pour Synology NAS
#
# Ce script configure automatiquement le webhook SBN sur votre NAS Synology
#
# @package SBN
# @version 1.0.0
################################################################################

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSTALL_DIR="/volume1/@appstore/ActiveBackup/scripts"

echo "════════════════════════════════════════════════════════════════"
echo "  SBN v1.0 - Installation du script webhook Synology"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Vérifier si l'utilisateur est root
if [ "$EUID" -ne 0 ]; then
    echo "⚠️  ATTENTION: Ce script doit être exécuté en tant que root"
    echo "   Utilisez: sudo bash install.sh"
    exit 1
fi

# Vérifier si Active Backup est installé
if [ ! -d "/volume1/@appstore/ActiveBackup" ]; then
    echo "⚠️  ATTENTION: Synology Active Backup n'est pas installé"
    echo "   Installez Active Backup depuis le Package Center avant de continuer"
    exit 1
fi

# Créer le répertoire d'installation si nécessaire
echo "📁 Création du répertoire d'installation..."
mkdir -p "$INSTALL_DIR"

# Copier les fichiers
echo "📋 Copie des fichiers..."
cp "$SCRIPT_DIR/backup-webhook.sh" "$INSTALL_DIR/"
chmod +x "$INSTALL_DIR/backup-webhook.sh"

# Créer le fichier de configuration
if [ -f "$SCRIPT_DIR/config.sh" ]; then
    echo "⚙️  Copie de la configuration existante..."
    cp "$SCRIPT_DIR/config.sh" "$INSTALL_DIR/"
else
    echo "⚙️  Création du fichier de configuration..."
    cp "$SCRIPT_DIR/config.sh.example" "$INSTALL_DIR/config.sh"
    echo ""
    echo "⚠️  IMPORTANT: Vous devez configurer le fichier:"
    echo "   $INSTALL_DIR/config.sh"
    echo ""
    echo "   Modifiez les valeurs suivantes:"
    echo "   - SBN_API_URL: URL de votre instance SBN"
    echo "   - SBN_API_TOKEN: Votre token API"
    echo ""
fi

chmod 600 "$INSTALL_DIR/config.sh"

# Créer le fichier de log
touch "$INSTALL_DIR/webhook.log"
chmod 644 "$INSTALL_DIR/webhook.log"

echo ""
echo "✅ Installation terminée avec succès!"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Prochaines étapes:"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "1. Configurez le fichier de configuration:"
echo "   vi $INSTALL_DIR/config.sh"
echo ""
echo "2. Configurez Synology Active Backup pour appeler le script:"
echo "   - Ouvrez Active Backup for Business"
echo "   - Allez dans Paramètres > Notifications"
echo "   - Ajoutez une notification de type 'Script'"
echo "   - Script: $INSTALL_DIR/backup-webhook.sh"
echo "   - Arguments: %STATUS% %START_TIME% %END_TIME% %BACKUP_PATH% \"%ERROR_MESSAGE%\" %BACKUP_TYPE%"
echo ""
echo "3. Testez le script manuellement:"
echo "   $INSTALL_DIR/backup-webhook.sh success \"$(date -Iseconds)\" \"$(date -Iseconds)\" \"/volume1/backup\" \"\" full"
echo ""
echo "4. Vérifiez les logs:"
echo "   tail -f $INSTALL_DIR/webhook.log"
echo ""
echo "════════════════════════════════════════════════════════════════"
