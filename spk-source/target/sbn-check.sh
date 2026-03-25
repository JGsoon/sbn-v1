#!/bin/bash

CONFIG="/var/packages/SBNBackupNotifier/target/etc/config.json"
API_KEY=$(grep "api_key" "$CONFIG" | cut -d'"' -f4)
SERVER_URL=$(grep "server_url" "$CONFIG" | cut -d'"' -f4)

# Récupérer les logs Hyper Backup des dernières 2 heures
LOGS=$(synologtool --get-log 2>/dev/null | grep -i "backup" | head -20)

# Envoyer le heartbeat avec les infos backup
curl -s -X POST "$SERVER_URL/api/heartbeat" \
    -H "Content-Type: application/json" \
    -d "{\"api_key\":\"$API_KEY\",\"hostname\":\"$(hostname)\",\"logs\":\"$LOGS\",\"timestamp\":\"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"}"
