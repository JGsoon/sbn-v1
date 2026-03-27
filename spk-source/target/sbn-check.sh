#!/bin/bash

CONFIG="/var/packages/SBNBackupNotifier/target/etc/config.json"
API_KEY=$(grep "api_key" "$CONFIG" | cut -d'"' -f4)
SERVER_URL=$(grep "server_url" "$CONFIG" | cut -d'"' -f4)

# Lire les logs Hyper Backup depuis le log systeme Synology (2 dernieres heures)
TWO_HOURS_AGO=$(date -d '2 hours ago' '+%Y-%m-%d %H:%M' 2>/dev/null || date -u '+%Y-%m-%d %H:%M')
LOGS=$(synolog --get cat /var/log/synolog/synobackup.log 2>/dev/null | tail -50)

# Si pas de log Hyper Backup, tenter Active Backup
if [ -z "$LOGS" ]; then
    LOGS=$(cat /var/log/synolog/activebackup.log 2>/dev/null | tail -50)
fi

# Recuperer les infos NAS
HOSTNAME=$(hostname)
SERIAL=$(cat /proc/sys/kernel/syno_serial 2>/dev/null || echo "unknown")
DSM_VERSION=$(cat /etc.defaults/VERSION 2>/dev/null | grep productversion | cut -d'"' -f2)

# Envoyer le heartbeat
curl -s -X POST "$SERVER_URL/api/heartbeat" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $API_KEY" \
    -d "{
        \"serial\": \"$SERIAL\",
        \"hostname\": \"$HOSTNAME\",
        \"dsm_version\": \"$DSM_VERSION\",
        \"logs\": $(echo "$LOGS" | python3 -c 'import sys,json; print(json.dumps(sys.stdin.read()))' 2>/dev/null || echo '""'),
        \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"
    }"
