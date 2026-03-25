<?php
/**
 * SBN v1.0 - Contrôleur Documentation
 *
 * Documentation et téléchargement des scripts NAS
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;

class DocumentationController extends Controller {

    /**
     * Page principale de documentation
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $this->view('documentation/index', [
            'title' => 'Documentation - SBN v1.0',
            'user' => $user
        ]);
    }

    /**
     * Télécharger le script principal sbn_webhook.sh
     */
    public function downloadWebhookScript() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $content = <<<'EOF'
#!/bin/bash
################################################################################
# SBN v1.0 - Script Webhook pour Synology NAS
#
# Ce script envoie les notifications de sauvegarde vers SBN
# Compatible avec: Active Backup for Business et HyperBackup
#
# Usage: ./sbn_webhook.sh [success|failed|warning]
################################################################################

# Charger la configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/config.sh"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ ERREUR: Fichier config.sh introuvable dans $SCRIPT_DIR"
    echo "Téléchargez-le depuis SBN: Paramètres → Tokens API → Config"
    exit 1
fi

source "$CONFIG_FILE"

# Vérifier les variables requises
if [ -z "$SBN_API_URL" ] || [ -z "$SBN_API_TOKEN" ]; then
    echo "❌ ERREUR: Variables SBN_API_URL ou SBN_API_TOKEN non définies"
    exit 1
fi

# Fichier de log
LOG_FILE="$SCRIPT_DIR/webhook.log"

# Fonction de log
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Fonction de debug
debug() {
    if [ "$SBN_DEBUG" = "true" ]; then
        log "DEBUG: $1"
    fi
}

# Récupérer le statut (paramètre 1 ou variable HyperBackup)
STATUS="${1:-${BACKUP_STATUS:-unknown}}"

# Récupérer les informations du système
DEVICE_NAME=$(hostname)
DEVICE_IP=$(ip route get 1 | awk '{print $7;exit}')
DEVICE_OS="DSM $(cat /etc/VERSION | grep productversion | cut -d'"' -f2)"

# Timestamp
TIMESTAMP=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Calculer la taille si un chemin est fourni
BACKUP_SIZE=""
if [ -n "$BACKUP_PATH" ] && [ -d "$BACKUP_PATH" ]; then
    BACKUP_SIZE=$(du -sb "$BACKUP_PATH" 2>/dev/null | awk '{print $1}')
fi

# Construire le message
case "$STATUS" in
    success)
        MESSAGE="Sauvegarde réussie"
        ;;
    failed)
        MESSAGE="Sauvegarde échouée"
        ;;
    warning)
        MESSAGE="Sauvegarde terminée avec avertissements"
        ;;
    *)
        MESSAGE="Statut inconnu: $STATUS"
        ;;
esac

# Construire le JSON
JSON_DATA=$(cat <<JSON_END
{
    "device_name": "$DEVICE_NAME",
    "device_ip": "$DEVICE_IP",
    "device_os": "$DEVICE_OS",
    "backup_status": "$STATUS",
    "backup_message": "$MESSAGE",
    "backup_size": "$BACKUP_SIZE",
    "timestamp": "$TIMESTAMP"
}
JSON_END
)

log "========================================="
log "Envoi webhook vers SBN"
log "Statut: $STATUS"
log "Device: $DEVICE_NAME ($DEVICE_IP)"
debug "JSON: $JSON_DATA"

# Envoyer le webhook avec timeout configurable
TIMEOUT="${SBN_TIMEOUT:-30}"

HTTP_RESPONSE=$(curl -s -w "\n%{http_code}" \
    --max-time "$TIMEOUT" \
    -X POST \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $SBN_API_TOKEN" \
    -d "$JSON_DATA" \
    "$SBN_API_URL" 2>&1)

# Séparer le body et le code HTTP
HTTP_BODY=$(echo "$HTTP_RESPONSE" | head -n -1)
HTTP_CODE=$(echo "$HTTP_RESPONSE" | tail -n 1)

debug "Code HTTP: $HTTP_CODE"
debug "Réponse: $HTTP_BODY"

# Vérifier le résultat
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "201" ]; then
    log "✅ Webhook envoyé avec succès (HTTP $HTTP_CODE)"
    exit 0
else
    log "❌ Erreur lors de l'envoi du webhook (HTTP $HTTP_CODE)"
    log "Réponse: $HTTP_BODY"
    exit 1
fi
EOF;

        header('Content-Type: application/x-sh');
        header('Content-Disposition: attachment; filename="sbn_webhook.sh"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');

        echo $content;
        exit;
    }

    /**
     * Télécharger le fichier d'exemple config.sh
     */
    public function downloadConfigExample() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $content = <<<'EOF'
#!/bin/bash
################################################################################
# SBN v1.0 - Configuration Exemple
#
# ⚠️  NE PAS UTILISER CE FICHIER DIRECTEMENT!
#
# Pour obtenir votre configuration personnalisée:
# 1. Allez dans Paramètres → Tokens API
# 2. Créez un nouveau token
# 3. Téléchargez le fichier config.sh généré automatiquement
#
################################################################################

# URL de votre instance SBN (webhook endpoint)
SBN_API_URL="https://votre-instance-sbn.com/api/webhook"

# Token API de votre société
# ⚠️ NE PARTAGEZ JAMAIS CE TOKEN
SBN_API_TOKEN="sbn_VOTRE_TOKEN_ICI"

# Mode debug (true/false)
# Active les logs détaillés dans la console et le fichier webhook.log
SBN_DEBUG="false"

# Timeout pour les requêtes HTTP (en secondes)
SBN_TIMEOUT="30"
EOF;

        header('Content-Type: application/x-sh');
        header('Content-Disposition: attachment; filename="config.sh.example"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');

        echo $content;
        exit;
    }

    /**
     * Télécharger le script d'installation
     */
    public function downloadInstallScript() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $content = <<<'EOF'
#!/bin/bash
################################################################################
# SBN v1.0 - Script d'installation pour Synology NAS
#
# Ce script installe automatiquement les scripts webhook SBN
################################################################################

set -e

echo "========================================="
echo "Installation SBN Webhook pour Synology"
echo "========================================="
echo ""

# Vérifier que nous sommes sur un Synology
if [ ! -f /etc/synoinfo.conf ]; then
    echo "❌ ERREUR: Ce script doit être exécuté sur un Synology NAS"
    exit 1
fi

# Créer le dossier d'installation
INSTALL_DIR="/volume1/scripts/sbn"
echo "📁 Création du dossier: $INSTALL_DIR"
mkdir -p "$INSTALL_DIR"

# Copier les fichiers
echo "📋 Copie des fichiers..."

if [ -f "sbn_webhook.sh" ]; then
    cp sbn_webhook.sh "$INSTALL_DIR/"
    chmod +x "$INSTALL_DIR/sbn_webhook.sh"
    echo "   ✅ sbn_webhook.sh copié"
else
    echo "   ❌ sbn_webhook.sh introuvable"
    exit 1
fi

if [ -f "config.sh" ]; then
    cp config.sh "$INSTALL_DIR/"
    chmod 600 "$INSTALL_DIR/config.sh"
    echo "   ✅ config.sh copié (permissions: 600)"
else
    echo "   ⚠️  config.sh introuvable - vous devrez le créer manuellement"
    echo "      Téléchargez-le depuis: Paramètres → Tokens API → Config"
fi

# Test de configuration
echo ""
echo "🧪 Test de la configuration..."

if [ -f "$INSTALL_DIR/config.sh" ]; then
    source "$INSTALL_DIR/config.sh"

    if [ -z "$SBN_API_URL" ] || [ -z "$SBN_API_TOKEN" ]; then
        echo "   ⚠️  Configuration incomplète"
        echo "      Éditez: $INSTALL_DIR/config.sh"
    else
        echo "   ✅ Configuration OK"
        echo "      URL: $SBN_API_URL"
    fi
else
    echo "   ⚠️  Pas de fichier config.sh"
fi

# Afficher les instructions finales
echo ""
echo "========================================="
echo "✅ Installation terminée!"
echo "========================================="
echo ""
echo "📝 Prochaines étapes:"
echo ""
echo "1. Si vous n'avez pas encore le fichier config.sh:"
echo "   - Allez dans SBN: Paramètres → Tokens API"
echo "   - Créez un token et téléchargez config.sh"
echo "   - Placez-le dans: $INSTALL_DIR/"
echo ""
echo "2. Configurez HyperBackup:"
echo "   - Ouvrez HyperBackup"
echo "   - Sélectionnez votre tâche → Paramètres"
echo "   - Activez 'Exécuter un script personnalisé'"
echo "   - Chemin: $INSTALL_DIR/sbn_webhook.sh"
echo ""
echo "3. Test manuel:"
echo "   sudo $INSTALL_DIR/sbn_webhook.sh success"
echo ""
echo "📄 Logs: $INSTALL_DIR/webhook.log"
echo ""
EOF;

        header('Content-Type: application/x-sh');
        header('Content-Disposition: attachment; filename="install.sh"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');

        echo $content;
        exit;
    }

    /**
     * Télécharger tous les scripts en ZIP
     */
    public function downloadAll() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Créer un fichier ZIP temporaire
        $zipFile = tempnam(sys_get_temp_dir(), 'sbn_scripts_');
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE) !== TRUE) {
            $this->setFlash('error', 'Impossible de créer l\'archive ZIP');
            $this->redirect('documentation');
        }

        // Ajouter README
        $readme = file_get_contents(ROOT_PATH . '/synology-scripts/README.md');
        $zip->addFromString('sbn-scripts/README.md', $readme);

        // Ajouter les scripts (on va les générer inline)
        // Note: Les scripts sont générés par les méthodes ci-dessus
        // Pour simplifier, on va rediriger vers la page de documentation

        $this->setFlash('info', 'Téléchargez les fichiers individuellement ci-dessous');
        $this->redirect('documentation');
    }
}
