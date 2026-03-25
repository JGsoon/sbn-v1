# Scripts Synology pour SBN v1.0

Scripts de webhook pour intégrer Synology Active Backup avec votre plateforme SBN.

## 📋 Contenu

- `backup-webhook.sh` - Script principal qui envoie les webhooks
- `config.sh.example` - Fichier de configuration exemple
- `install.sh` - Script d'installation automatique
- `README.md` - Cette documentation

## 🚀 Installation Rapide

### Étape 1: Télécharger les scripts

Téléchargez tous les fichiers du dossier `synology-scripts/` et transférez-les sur votre NAS Synology via SSH ou le gestionnaire de fichiers.

```bash
# Exemple avec SCP depuis votre ordinateur
scp -r synology-scripts/ admin@votre-nas-ip:/volume1/homes/admin/
```

### Étape 2: Connexion SSH au NAS

```bash
ssh admin@votre-nas-ip
```

### Étape 3: Configurer le token API

1. Connectez-vous à votre interface SBN
2. Allez dans **Paramètres > API > Tokens**
3. Créez un nouveau token API
4. Copiez le token généré

### Étape 4: Configurer le script

```bash
cd /volume1/homes/admin/synology-scripts
cp config.sh.example config.sh
vi config.sh
```

Modifiez les valeurs suivantes:

```bash
SBN_API_URL="https://votre-domaine.fr/api/webhook"
SBN_API_TOKEN="votre-token-api-ici"
SBN_DEBUG="false"
```

### Étape 5: Installer le script

```bash
sudo bash install.sh
```

Le script sera installé dans `/volume1/@appstore/ActiveBackup/scripts/`

### Étape 6: Configurer Synology Active Backup

1. Ouvrez **Active Backup for Business** sur votre NAS
2. Allez dans **Paramètres** > **Notifications**
3. Cliquez sur **Créer** > **Exécuter le script**
4. Configurez comme suit:
   - **Nom**: SBN Webhook
   - **Script**: `/volume1/@appstore/ActiveBackup/scripts/backup-webhook.sh`
   - **Arguments**:
     ```
     %STATUS% %START_TIME% %END_TIME% %BACKUP_PATH% "%ERROR_MESSAGE%" %BACKUP_TYPE%
     ```
   - **Événements**: Cochez tous les événements pertinents
     - ✅ Sauvegarde réussie
     - ✅ Sauvegarde échouée
     - ✅ Sauvegarde avec avertissement

5. Cliquez sur **OK**

## 🧪 Test

### Test manuel du script

```bash
# Test avec une sauvegarde réussie
/volume1/@appstore/ActiveBackup/scripts/backup-webhook.sh \
    success \
    "$(date -Iseconds)" \
    "$(date -Iseconds)" \
    "/volume1/backup/test" \
    "" \
    full

# Test avec une sauvegarde échouée
/volume1/@appstore/ActiveBackup/scripts/backup-webhook.sh \
    failed \
    "$(date -Iseconds)" \
    "$(date -Iseconds)" \
    "" \
    "Erreur de test" \
    full
```

### Vérifier les logs

```bash
# Voir les dernières lignes du log
tail -f /volume1/@appstore/ActiveBackup/scripts/webhook.log

# Voir tout le log
cat /volume1/@appstore/ActiveBackup/scripts/webhook.log
```

## 📊 Paramètres du script

Le script accepte les paramètres suivants (dans cet ordre):

| Paramètre | Description | Valeurs possibles | Obligatoire |
|-----------|-------------|-------------------|-------------|
| STATUS | Statut de la sauvegarde | `success`, `failed`, `warning`, `running` | ✅ |
| START_TIME | Date/heure de début | Format ISO 8601 | ✅ |
| END_TIME | Date/heure de fin | Format ISO 8601 | ✅ |
| BACKUP_PATH | Chemin du backup | Chemin absolu | ❌ |
| ERROR_MESSAGE | Message d'erreur | Texte libre | ❌ |
| BACKUP_TYPE | Type de sauvegarde | `full`, `incremental` | ❌ |

## 🔧 Configuration avancée

### Mode debug

Pour activer les logs détaillés:

```bash
vi /volume1/@appstore/ActiveBackup/scripts/config.sh
```

Changez:
```bash
SBN_DEBUG="true"
```

### Variables Synology Active Backup

Synology Active Backup fournit les variables suivantes que vous pouvez utiliser:

- `%STATUS%` - Statut de la sauvegarde
- `%START_TIME%` - Heure de début
- `%END_TIME%` - Heure de fin
- `%BACKUP_PATH%` - Chemin de la sauvegarde
- `%ERROR_MESSAGE%` - Message d'erreur (si échec)
- `%BACKUP_TYPE%` - Type de sauvegarde
- `%DEVICE_NAME%` - Nom de l'appareil sauvegardé
- `%JOB_NAME%` - Nom de la tâche de sauvegarde

## 🔐 Sécurité

### Token API

- **Ne partagez JAMAIS votre token API**
- Le token permet d'écrire dans votre base de données
- Changez-le régulièrement depuis l'interface SBN
- Si compromis, révoquez-le immédiatement

### Isolation des données

- Chaque token est lié à une société (`company_id`)
- Les données sont automatiquement isolées
- Un NAS ne peut PAS écrire dans une autre société
- Les permissions sont vérifiées côté serveur

### Fichiers de configuration

Le fichier `config.sh` contient des informations sensibles:

```bash
# Vérifier les permissions
ls -la /volume1/@appstore/ActiveBackup/scripts/config.sh
# Doit être: -rw------- (600)

# Corriger si nécessaire
chmod 600 /volume1/@appstore/ActiveBackup/scripts/config.sh
```

## 📝 Format JSON des données envoyées

Le script envoie un JSON au format suivant:

```json
{
    "device_name": "NAS-PROD-01",
    "device_ip": "192.168.1.100",
    "device_os": "DSM 7.2",
    "status": "success",
    "start_time": "2025-01-15T10:00:00+01:00",
    "end_time": "2025-01-15T10:30:00+01:00",
    "size_bytes": 5368709120,
    "error_message": "",
    "backup_type": "full",
    "destination_path": "/volume1/ActiveBackup/PC/DESKTOP-01"
}
```

## 🐛 Dépannage

### Le webhook ne fonctionne pas

1. **Vérifier la configuration**:
   ```bash
   cat /volume1/@appstore/ActiveBackup/scripts/config.sh
   ```

2. **Tester manuellement**:
   ```bash
   /volume1/@appstore/ActiveBackup/scripts/backup-webhook.sh success "$(date -Iseconds)" "$(date -Iseconds)"
   ```

3. **Vérifier les logs**:
   ```bash
   tail -50 /volume1/@appstore/ActiveBackup/scripts/webhook.log
   ```

4. **Activer le debug**:
   ```bash
   vi /volume1/@appstore/ActiveBackup/scripts/config.sh
   # Changer SBN_DEBUG="true"
   ```

### Erreur 401 Unauthorized

- Vérifiez que votre token API est correct
- Vérifiez que le token est actif dans l'interface SBN
- Le token a peut-être expiré

### Erreur 400 Bad Request

- Vérifiez le format des données envoyées
- Activez le mode debug pour voir le JSON envoyé
- Vérifiez que tous les champs requis sont présents

### Erreur de connexion

- Vérifiez que l'URL de l'API est correcte
- Vérifiez que votre NAS peut accéder à Internet
- Testez avec `curl`:
  ```bash
  curl -I https://votre-domaine.fr/api/webhook
  ```

## 📞 Support

Pour obtenir de l'aide:

1. Consultez la documentation SBN
2. Vérifiez les logs du webhook
3. Contactez votre administrateur SBN

## 📄 Licence

SBN v1.0 - Développé par Soon22
