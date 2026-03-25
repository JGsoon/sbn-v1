# SBN Backup Notifier - Package Synology

## Installation automatique réussie !

Ce package a été installé dans : `/var/packages/SBNBackupNotifier/target/`

## Configuration des webhooks Active Backup

### Étape 1 : Localiser le script webhook

Le script webhook est installé ici :
```
/var/packages/SBNBackupNotifier/target/webhook.sh
```

### Étape 2 : Configurer Active Backup for Business

1. Ouvrez **Active Backup for Business**
2. Allez dans **Paramètres** > **Notifications**
3. Section **Webhook** :
   - Cochez "Activer le webhook"
   - URL : Laissez vide (le script local n'utilise pas d'URL)
   - Script personnalisé : Cochez cette option
   - Chemin du script : `/var/packages/SBNBackupNotifier/target/webhook.sh`

### Étape 3 : Tester

Lancez une sauvegarde test pour vérifier que les notifications sont envoyées à votre plateforme SBN.

## Fichiers de configuration

- Configuration : `/var/packages/SBNBackupNotifier/target/config.sh`
- Logs : `/var/packages/SBNBackupNotifier/target/logs/`

## Votre configuration

Votre token API est pré-configuré dans ce package.
Le webhook enverra automatiquement les notifications de sauvegarde à votre compte SBN.

## Support

- Documentation : https://votre-domaine.com/documentation
- Email : contact@soon22.fr

---

**Développé par Soon22 - Johnny Girault**
