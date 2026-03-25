# 🚀 Démarrage rapide - SBN v1.0

## Installation en 5 minutes

### 1. Créer la base de données

Ouvrez phpMyAdmin (http://localhost/phpmyadmin) et exécutez :

```sql
CREATE DATABASE sbn_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Importer le schéma

Dans phpMyAdmin :
1. Sélectionnez la base `sbn_dev`
2. Cliquez sur "Importer"
3. Choisissez `database/schema.sql`
4. Cliquez sur "Exécuter"

### 3. Configurer l'environnement

Le fichier `.env` est déjà configuré pour XAMPP par défaut.

Si besoin, modifiez-le :
```ini
DB_HOST=localhost
DB_NAME=sbn_dev
DB_USER=root
DB_PASS=
```

### 4. Vérifier l'installation

Ouvrez : http://localhost/sbn-v1/check-install.php

Tous les tests doivent être verts ✅

### 5. Se connecter

Ouvrez : http://localhost/sbn-v1

**Compte de test :**
- Email : `admin@soon22.fr`
- Mot de passe : `Admin123!`

---

## Problèmes courants

### ❌ "Page non trouvée" (404)

**Solution** : Activer mod_rewrite dans XAMPP

1. Ouvrir `C:\xampp\apache\conf\httpd.conf`
2. Chercher et décommenter :
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Chercher `AllowOverride None` et le remplacer par `AllowOverride All`
4. Redémarrer Apache

### ❌ "Erreur de connexion base de données"

**Solutions** :
1. Vérifier que MySQL est démarré dans XAMPP
2. Vérifier que la base `sbn_dev` existe
3. Vérifier les identifiants dans `.env`

### ❌ "Erreur 500"

**Solution** : Vérifier les permissions

1. Le dossier `storage/` doit être accessible en écriture
2. Créer les dossiers si manquants :
   ```
   storage/logs/
   storage/cache/
   ```

---

## Structure de l'application

```
sbn-v1/
├── app/
│   ├── Controllers/     ← Logique métier
│   ├── Models/          ← Accès données
│   ├── Views/           ← Templates HTML
│   └── Core/            ← Classes de base
├── config/              ← Configuration
├── database/            ← Schéma SQL
├── public/              ← CSS, JS, images
├── storage/             ← Logs et cache
└── index.php           ← Point d'entrée
```

---

## Prochaines étapes

### Créer un nouveau compte

1. Cliquez sur "Créer un compte"
2. Remplissez le formulaire
3. Vous serez admin de votre société

### Personnaliser

1. Modifier le logo dans `public/assets/images/`
2. Adapter les couleurs dans `public/css/main.css`
3. Configurer l'email dans `.env`

### Ajouter des sauvegardes

Les sauvegardes peuvent être ajoutées :
1. Via l'API webhook Synology
2. Manuellement dans le dashboard
3. Via import CSV (à venir)

---

## Routes disponibles

### Publiques
- `/` ou `/login` - Connexion
- `/register` - Inscription
- `/forgot-password` - Mot de passe oublié

### Authentifiées
- `/dashboard` - Tableau de bord
- `/backups` - Gestion sauvegardes
- `/companies` - Gestion sociétés
- `/settings` - Paramètres
- `/logout` - Déconnexion

### RGPD
- `/gdpr/export` - Exporter mes données
- `/gdpr/delete` - Supprimer mon compte
- `/privacy` - Politique de confidentialité
- `/terms` - Conditions d'utilisation

---

## Sécurité

✅ **Activé par défaut :**
- Protection CSRF
- Hashage bcrypt (cost 12)
- Sessions sécurisées
- Headers de sécurité
- Protection injection SQL
- Validation des entrées
- Journalisation complète

⚠️ **À faire en production :**
1. Modifier `APP_ENV=production` dans `.env`
2. Désactiver `APP_DEBUG=false`
3. Activer HTTPS
4. Supprimer `check-install.php`
5. Changer le compte admin par défaut

---

## Support

📧 Email : contact@soon22.fr
🌐 Site : https://soon22.fr
📚 Docs complètes : README.md

---

**SBN v1.0** - Développé avec ❤️ par Soon22
