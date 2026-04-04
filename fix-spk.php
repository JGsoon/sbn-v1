<?php
// C:\xampp\htdocs\sbn-v1\fix-spk.php

$base = 'C:/xampp/htdocs/sbn-v1/spk-source';

file_put_contents("$base/target/sbn-check.sh", '#!/bin/bash

CONFIG="/var/packages/SBNBackupNotifier/target/etc/config.json"
LOG_FILE="/var/packages/SBNBackupNotifier/target/var/sbn.log"
API_KEY=$(grep "api_key" "$CONFIG" | cut -d\'"\' -f4)
SERVER_URL=$(grep "server_url" "$CONFIG" | cut -d\'"\' -f4)

mkdir -p "$(dirname "$LOG_FILE")"

log() { echo "[$(date +\'%Y-%m-%d %H:%M:%S\')] $1" >> "$LOG_FILE"; }

log "=== SBN Check Start ==="

HOSTNAME=$(hostname)
SERIAL=$(cat /proc/sys/kernel/syno_serial 2>/dev/null || echo "unknown")
DSM_VERSION=$(cat /etc.defaults/VERSION 2>/dev/null | grep productversion | cut -d\'"\' -f2)

HB_LOGS=""
if [ -f /var/log/synolog/synobackup.log ]; then
    HB_LOGS=$(tail -100 /var/log/synolog/synobackup.log 2>/dev/null)
fi

AB_LOGS=""
if [ -f /var/log/synolog/activebackup.log ]; then
    AB_LOGS=$(tail -100 /var/log/synolog/activebackup.log 2>/dev/null)
fi

ALL_LOGS="${HB_LOGS}
${AB_LOGS}"

LOGS_JSON=$(echo "$ALL_LOGS" | python3 -c "import sys,json; print(json.dumps(sys.stdin.read()))" 2>/dev/null || echo "\"\"")

RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$SERVER_URL/api/heartbeat" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $API_KEY" \
    -d "{
        \"serial\": \"$SERIAL\",
        \"hostname\": \"$HOSTNAME\",
        \"dsm_version\": \"$DSM_VERSION\",
        \"logs\": $LOGS_JSON,
        \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"
    }" 2>&1)

HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | head -n -1)

log "HTTP $HTTP_CODE - $BODY"
log "=== SBN Check End ==="

tail -500 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
');

echo "OK - sbn-check.sh corrige\n";
