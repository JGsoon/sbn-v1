# 📦 Guide d'installation - Package Synology (.spk)

## ✨ Solution recommandée - Installation en 3 clics !

Le package .spk est la méthode la plus simple et professionnelle pour installer SBN Backup Notifier sur votre NAS Synology.

### Avantages du package .spk

✅ Installation automatique via interface graphique
✅ Configuration pré-incluse avec votre token API
✅ Désinstallation propre en 1 clic
✅ Mises à jour faciles (à venir)
✅ Pas besoin de SSH ou ligne de commande
✅ Compatible avec tous les modèles Synology (DSM 6.0+)

---

## 📥 Étape 1 : Télécharger le package .spk

### Depuis votre interface SBN :

1. Connectez-vous à votre compte SBN
2. Allez dans **Paramètres** → **Tokens API** (`/settings/api`)
3. Si vous n'avez pas encore de token, cliquez sur **"Créer un nouveau token"**
4. Une fois le token créé, dans la liste des tokens, cliquez sur le bouton : **📦 Package .SPK**

Le fichier téléchargé sera nommé : `SBN-Backup-Notifier-[NomDeVotreToken].spk`

---

## 🚀 Étape 2 : Installer le package sur votre NAS

### Via l'interface DSM :

1. Connectez-vous à votre NAS Synology (DSM)
2. Ouvrez le **"Centre de paquets"**
3. Cliquez sur **"Installation manuelle"** (bouton en haut à droite)
4. Cliquez sur **"Parcourir"**
5. Sélectionnez le fichier `.spk` que vous venez de télécharger
6. Suivez l'assistant d'installation :
   - Acceptez les autorisations
   - Confirmez l'installation
7. ✅ Installation terminée !

---

## ⚙️ Étape 3 : Configurer Active Backup for Business

Maintenant que le package est installé, configurons les webhooks :

### 1. Ouvrir Active Backup for Business

- Dans DSM, ouvrez l'application **"Active Backup for Business"**

### 2. Accéder aux paramètres de notification

- Allez dans **Paramètres** (icône engrenage)
- Section **"Notifications"**
- Onglet **"Webhook"**

### 3. Configurer le webhook

Activez et configurez comme suit :

```
☑️ Activer le webhook

Type de webhook :
○ URL (laissez décoché)
◉ Script personnalisé (cochez cette option)

Chemin du script :
/var/packages/SBNBackupNotifier/target/webhook.sh

☑️ Activer pour les tâches réussies
☑️ Activer pour les tâches échouées
```

### 4. Sauvegarder

Cliquez sur **"Appliquer"** ou **"OK"**

---

## ✅ Étape 4 : Tester l'installation

### Test simple :

1. Lancez une sauvegarde manuelle dans Active Backup
2. Une fois terminée, vérifiez dans SBN :
   - Allez sur le **Dashboard** de SBN
   - Vous devriez voir la sauvegarde apparaître

### Vérifier les logs (optionnel) :

Si vous avez accès SSH à votre NAS :

```bash
# Voir les logs du webhook
cat /var/packages/SBNBackupNotifier/target/logs/webhook-$(date +%Y%m%d).log
```

---

## 📂 Fichiers installés

Le package installe les fichiers suivants sur votre NAS :

```
/var/packages/SBNBackupNotifier/
├── target/
│   ├── webhook.sh          # Script appelé par Active Backup
│   ├── config.sh           # Configuration avec votre token API
│   ├── README.md           # Documentation
│   └── logs/               # Logs des webhooks
│       └── webhook-YYYYMMDD.log
```

---

## 🔧 Désinstallation

Si vous souhaitez désinstaller le package :

1. Ouvrez le **"Centre de paquets"**
2. Trouvez **"SBN Backup Notifier"**
3. Cliquez sur **"Désinstaller"**
4. Confirmez

⚠️ **Note** : Les logs seront conservés lors de la désinstallation. Si vous souhaitez tout supprimer, connectez-vous en SSH et supprimez manuellement `/var/packages/SBNBackupNotifier/`

---

## 🆚 Comparaison des méthodes d'installation

| Fonctionnalité | Package .SPK | Installation manuelle (scripts) |
|----------------|--------------|--------------------------------|
| Difficulté | ⭐ Facile | ⭐⭐⭐ Avancé |
| Installation | Interface graphique | SSH + ligne de commande |
| Configuration | Automatique | Manuelle |
| Désinstallation | 1 clic | Manuelle |
| Mises à jour | Automatique (futur) | Manuelle |
| SSH requis | ❌ Non | ✅ Oui |

**Recommandation** : Utilisez toujours le package .SPK sauf si vous avez des besoins très spécifiques.

---

## ❓ FAQ

### Le package fonctionne sur quel modèle de NAS ?

Le package est compatible avec **tous les modèles Synology** utilisant DSM 6.0 ou supérieur. L'architecture est `noarch` (indépendante du processeur).

### Puis-je installer plusieurs packages pour différents clients ?

Non, un seul package .spk peut être installé par NAS. Cependant, le package supporte plusieurs configurations. Si vous gérez plusieurs clients, utilisez un token API par client et modifiez le fichier `config.sh` en conséquence.

### Que faire si Active Backup n'appelle pas le webhook ?

Vérifiez :
1. Le chemin du script est correct : `/var/packages/SBNBackupNotifier/target/webhook.sh`
2. Les autorisations : `chmod +x /var/packages/SBNBackupNotifier/target/webhook.sh`
3. Les logs : `cat /var/packages/SBNBackupNotifier/target/logs/webhook-*.log`

### Comment mettre à jour vers une nouvelle version ?

Téléchargez le nouveau package .spk et installez-le via "Installation manuelle". Le package existant sera mis à jour automatiquement.

### Le token API est-il stocké en sécurité ?

Oui, le fichier `config.sh` contenant le token est stocké avec les permissions `644` (lecture seule pour les utilisateurs non-privilégiés) et accessible uniquement via SSH.

---

## 📞 Support

### Besoin d'aide ?

- **Documentation complète** : `/documentation` dans SBN
- **Email** : contact@soon22.fr
- **GitHub** : https://github.com/soon22

### Signaler un problème

Si vous rencontrez un problème :
1. Vérifiez les logs : `/var/packages/SBNBackupNotifier/target/logs/`
2. Notez le message d'erreur exact
3. Contactez le support avec ces informations

---

## 🎉 C'est terminé !

Votre NAS Synology est maintenant connecté à SBN et enverra automatiquement les notifications de sauvegarde après chaque backup.

**Prochaines étapes** :
- Configurez vos autres NAS/clients
- Personnalisez les alertes dans SBN
- Partagez l'accès avec vos collaborateurs ou clients

---

**Développé par Soon22 - Johnny Girault**
