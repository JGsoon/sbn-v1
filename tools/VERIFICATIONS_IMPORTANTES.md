# Vérifications importantes pour installer des SPK tiers

## ⚠️ VÉRIFICATION CRITIQUE

Avant de pouvoir installer un SPK personnalisé sur Synology, vous DEVEZ activer l'installation de sources tierces.

## Sur votre DS1522+ (DSM 7.x)

### Méthode 1 : Via le Centre de paquets

1. Ouvrez **Centre de paquets**
2. Cliquez sur **Paramètres** (icône engrenage en haut à droite)
3. Allez dans l'onglet **Général**
4. Dans la section **Niveau de confiance** :
   - Cochez **"N'importe quel éditeur"**
   OU
   - Cochez **"Éditeurs Synology Inc. et éditeurs de confiance"**
5. Cliquez sur **OK**

### Méthode 2 : Activer le mode développeur

Pour DSM 7.0+, vous devez peut-être aussi activer le mode développeur :

1. **Panneau de configuration** → **Tâche planifiée**
2. Créer une nouvelle tâche planifiée → **Tâche définie par l'utilisateur**
3. Général :
   - Nom : Enable Any Publisher
   - Utilisateur : root
4. Tâche :
   - Commande utilisateur :
   ```bash
   synopkg trust --add any
   ```
5. Exécuter une fois, puis supprimer la tâche

### Vérification

Pour vérifier que c'est activé, essayez d'installer un package tiers connu qui fonctionne, par exemple depuis SynoCommunity.

## Autres causes possibles

Si "N'importe quel éditeur" est déjà activé et que ça ne fonctionne toujours pas :

### 1. Format du package

Le SPK n'est peut-être pas juste un TAR, mais nécessite un format spécial créé par PkgCreate.py.

### 2. Champs INFO manquants

Vérifiez que tous les champs obligatoires sont présents dans le fichier INFO.

### 3. Scripts invalides

Les scripts bash doivent être valides et sans erreur de syntaxe.

### 4. DSM version

Certains packages ne fonctionnent que sur des versions spécifiques de DSM.

## Si rien ne fonctionne

Il est possible que Synology DSM 7 ait changé le format des SPK d'une manière qui nécessite l'utilisation de leurs outils officiels (PkgCreate.py avec Docker).

### Alternative : Utiliser l'environnement officiel

Synology fournit un toolkit Docker pour créer des packages :

```bash
# Télécharger le toolkit
docker pull synology/toolkit

# Créer le package
docker run -it --rm \
  -v /path/to/source:/source \
  synology/toolkit
```

Mais cela nécessite Linux/Docker.

## Références

- [Synology Developer Guide](https://global.download.synology.com/download/Document/Software/DeveloperGuide/Os/DSM/All/enu/DSM_Developer_Guide_7_enu.pdf)
- [Pack Stage Documentation](https://help.synology.com/developer-guide/toolkit/pack_stage.html)
- [SynoForum - Invalid File Format Issue](https://www.synoforum.com/threads/need-help-with-synology-package-development-invalid-file-format-issue.13477/)
