#!/bin/bash
################################################################################
# SBN v1.0 - Script de webhook pour Synology Active Backup
#
# Ce script est appelé par Synology Active Backup après chaque sauvegarde
# et envoie les informations à votre instance SBN via webhook sécurisé
#
# @package SBN
# @version 1.0.0
# @author Soon22
################################################################################

# Charger la configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/config.sh"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "ERREUR: Fichier de configuration non trouvé: $CONFIG_FILE"
    echo "Copiez config.sh.example vers config.sh et configurez-le."
    exit 1
fi

source "$CONFIG_FILE"

# Vérifier les variables requises
if [ -z "$SBN_API_URL" ] || [ -z "$SBN_API_TOKEN" ]; then
    echo "ERREUR: SBN_API_URL et SBN_API_TOKEN doivent être définis dans config.sh"
    exit 1
fi

################################################################################
# Fonctions utilitaires
################################################################################

# Logger un message
log() {
    local level="$1"
    local message="$2"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [$level] $message" >> "${SCRIPT_DIR}/webhook.log"
    if [ "$SBN_DEBUG" = "true" ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] [$level] $message"
    fi
}

# Obtenir la taille d'un répertoire en octets
get_directory_size() {
    local path="$1"
    if [ -d "$path" ]; then
        du -sb "$path" 2>/dev/null | cut -f1
    else
        echo "0"
    fi
}

# Obtenir le nom de l'hôte/device
get_device_name() {
    hostname 2>/dev/null || echo "unknown"
}

# Obtenir l'adresse IP
get_device_ip() {
    ip route get 1.1.1.1 2>/dev/null | awk '{print $7; exit}' || echo "unknown"
}

# Obtenir la version de l'OS
get_os_version() {
    if [ -f /etc/synoinfo.conf ]; then
        grep "productversion" /etc/synoinfo.conf | cut -d'"' -f2
    else
        uname -r
    fi
}

# Envoyer le webhook
send_webhook() {
    local json_data="$1"
    local response
    local http_code

    log "INFO" "Envoi du webhook vers: $SBN_API_URL"

    if [ "$SBN_DEBUG" = "true" ]; then
        log "DEBUG" "Données JSON: $json_data"
    fi

    # Envoyer la requête HTTP POST
    response=$(curl -s -w "\n%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -H "X-API-Token: $SBN_API_TOKEN" \
        -d "$json_data" \
        --max-time 30 \
        "$SBN_API_URL" 2>&1)

    http_code=$(echo "$response" | tail -n1)
    response_body=$(echo "$response" | head -n-1)

    log "INFO" "Code HTTP: $http_code"

    if [ "$http_code" -eq 201 ] || [ "$http_code" -eq 200 ]; then
        log "SUCCESS" "Webhook envoyé avec succès"
        if [ "$SBN_DEBUG" = "true" ]; then
            log "DEBUG" "Réponse: $response_body"
        fi
        return 0
    else
        log "ERROR" "Échec de l'envoi du webhook (HTTP $http_code)"
        log "ERROR" "Réponse: $response_body"
        return 1
    fi
}

# Construire le JSON des données de sauvegarde
build_json_data() {
    local status="$1"
    local start_time="$2"
    local end_time="$3"
    local size_bytes="$4"
    local error_message="$5"
    local backup_type="${6:-full}"
    local destination_path="$7"

    local device_name=$(get_device_name)
    local device_ip=$(get_device_ip)
    local os_version=$(get_os_version)

    # Échapper les guillemets dans les chaînes
    error_message=$(echo "$error_message" | sed 's/"/\\"/g')
    destination_path=$(echo "$destination_path" | sed 's/"/\\"/g')

    # Construire le JSON
    cat <<EOF
{
    "device_name": "$device_name",
    "device_ip": "$device_ip",
    "device_os": "DSM $os_version",
    "status": "$status",
    "start_time": "$start_time",
    "end_time": "$end_time",
    "size_bytes": $size_bytes,
    "error_message": "$error_message",
    "backup_type": "$backup_type",
    "destination_path": "$destination_path"
}
EOF
}

################################################################################
# Script principal
################################################################################

main() {
    log "INFO" "============================================"
    log "INFO" "Début de l'exécution du script webhook SBN"
    log "INFO" "============================================"

    # Paramètres du script (fournis par Synology Active Backup ou en ligne de commande)
    local STATUS="${1:-success}"           # success, failed, warning, running
    local START_TIME="${2:-$(date -Iseconds)}"
    local END_TIME="${3:-$(date -Iseconds)}"
    local BACKUP_PATH="${4:-}"
    local ERROR_MSG="${5:-}"
    local BACKUP_TYPE="${6:-full}"

    # Calculer la taille si un chemin est fourni
    local SIZE_BYTES=0
    if [ -n "$BACKUP_PATH" ] && [ -d "$BACKUP_PATH" ]; then
        log "INFO" "Calcul de la taille du backup: $BACKUP_PATH"
        SIZE_BYTES=$(get_directory_size "$BACKUP_PATH")
        log "INFO" "Taille calculée: $SIZE_BYTES octets"
    fi

    # Construire les données JSON
    log "INFO" "Construction des données de sauvegarde"
    JSON_DATA=$(build_json_data "$STATUS" "$START_TIME" "$END_TIME" "$SIZE_BYTES" "$ERROR_MSG" "$BACKUP_TYPE" "$BACKUP_PATH")

    # Envoyer le webhook
    if send_webhook "$JSON_DATA"; then
        log "SUCCESS" "Script terminé avec succès"
        exit 0
    else
        log "ERROR" "Script terminé avec erreur"
        exit 1
    fi
}

# Exécuter le script principal
main "$@"
