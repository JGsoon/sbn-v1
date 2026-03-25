#!/bin/bash
###############################################################################
# SBN Backup Notifier - Webhook Script
#
# Ce script est appelé automatiquement par Active Backup for Business
# après chaque sauvegarde pour envoyer les notifications à la plateforme SBN.
#
# Installation : Ce fichier est installé par le package .spk
# Configuration : /var/packages/SBNBackupNotifier/target/config.sh
###############################################################################

# Charger la configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/config.sh"

if [ -f "$CONFIG_FILE" ]; then
    source "$CONFIG_FILE"
else
    echo "ERREUR: Fichier de configuration non trouvé: $CONFIG_FILE" >&2
    exit 1
fi

# Vérifier que le token API est configuré
if [ -z "$API_TOKEN" ]; then
    echo "ERREUR: API_TOKEN n'est pas configuré dans $CONFIG_FILE" >&2
    exit 1
fi

# Vérifier que l'URL API est configurée
if [ -z "$API_URL" ]; then
    echo "ERREUR: API_URL n'est pas configuré dans $CONFIG_FILE" >&2
    exit 1
fi

###############################################################################
# Variables d'environnement fournies par Active Backup
###############################################################################

TASK_NAME="${ABB_TASK_NAME}"
DEVICE_NAME="${ABB_DEVICE_NAME}"
START_TIME="${ABB_START_TIME}"
END_TIME="${ABB_END_TIME}"
STATUS="${ABB_STATUS}"
ERROR_MESSAGE="${ABB_ERROR_MESSAGE}"
BACKUP_SIZE="${ABB_BACKUP_SIZE}"
DURATION="${ABB_DURATION}"

###############################################################################
# Création du log
###############################################################################

LOG_DIR="${SCRIPT_DIR}/logs"
mkdir -p "$LOG_DIR"
LOG_FILE="${LOG_DIR}/webhook-$(date +%Y%m%d).log"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log_message "=== Nouveau webhook reçu ==="
log_message "Tâche: $TASK_NAME"
log_message "Appareil: $DEVICE_NAME"
log_message "Statut: $STATUS"
log_message "Taille: $BACKUP_SIZE octets"
log_message "Durée: $DURATION secondes"

###############################################################################
# Préparation des données JSON
###############################################################################

# Échapper les caractères spéciaux pour JSON
escape_json() {
    echo "$1" | sed 's/"/\\"/g' | sed "s/'/\\'/g"
}

TASK_NAME_ESCAPED=$(escape_json "$TASK_NAME")
DEVICE_NAME_ESCAPED=$(escape_json "$DEVICE_NAME")
ERROR_MESSAGE_ESCAPED=$(escape_json "$ERROR_MESSAGE")

# Créer le payload JSON
JSON_PAYLOAD=$(cat <<EOF
{
    "task_name": "$TASK_NAME_ESCAPED",
    "device_name": "$DEVICE_NAME_ESCAPED",
    "start_time": "$START_TIME",
    "end_time": "$END_TIME",
    "status": "$STATUS",
    "error_message": "$ERROR_MESSAGE_ESCAPED",
    "backup_size": $BACKUP_SIZE,
    "duration": $DURATION,
    "nas_info": {
        "hostname": "$(hostname)",
        "model": "$(cat /proc/sys/kernel/syno_hw_version 2>/dev/null || echo 'unknown')",
        "dsm_version": "$(cat /etc/VERSION | grep productversion | cut -d'=' -f2 | tr -d '"' 2>/dev/null || echo 'unknown')"
    }
}
EOF
)

log_message "Payload JSON créé"

###############################################################################
# Envoi de la notification à l'API SBN
###############################################################################

RESPONSE=$(curl -s -w "\n%{http_code}" -X POST \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $API_TOKEN" \
    -H "X-Client-Type: synology-spk" \
    -H "X-Client-Version: 1.0.0" \
    --data "$JSON_PAYLOAD" \
    --max-time 30 \
    "$API_URL" 2>&1)

HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
RESPONSE_BODY=$(echo "$RESPONSE" | head -n -1)

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "201" ]; then
    log_message "✓ Notification envoyée avec succès (HTTP $HTTP_CODE)"
    log_message "Réponse: $RESPONSE_BODY"
    exit 0
else
    log_message "✗ Erreur lors de l'envoi (HTTP $HTTP_CODE)"
    log_message "Réponse: $RESPONSE_BODY"
    exit 1
fi
