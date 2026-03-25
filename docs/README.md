# SBN v1.0 - Synology Backup Notifier

**Plateforme de monitoring de sauvegardes Active Backup pour Synology**

## Description

SBN v1.0 est une application web sécurisée permettant de surveiller et gérer les sauvegardes effectuées via Active Backup for Business de Synology. Conçue pour les environnements multi-tenants, elle offre une vue centralisée des sauvegardes de votre entreprise.

## Fonctionnalités

### Système d'authentification robuste
- Connexion sécurisée avec bcrypt (cost 12)
- Protection contre les attaques par force brute (verrouillage après 5 tentatives)
- Session sécurisée avec tokens CSRF
- Fonction "Se souvenir de moi"
- Réinitialisation de mot de passe sécurisée

### Gestion multi-tenant
- Isolation complète des données par société
- Gestion des utilisateurs et des rôles (admin/user)
- Permissions granulaires par société

### Monitoring des sauvegardes
- Tableau de bord avec statistiques en temps réel
- Historique complet des sauvegardes
- Graphiques et visualisations
- Alertes et notifications

### Conformité RGPD
- Export des données personnelles (Article 20)
- Droit à l'oubli / Suppression des données (Article 17)
- Journalisation complète des accès (audit trail)
- Consentement explicite lors de l'inscription

### Sécurité avancée
- Headers de sécurité (CSP, X-Frame-Options, HSTS)
- Protection XSS, CSRF, injection SQL
- Validation et sanitisation des données
- Journalisation des tentatives de connexion
- Chiffrement des données sensibles

## Prérequis techniques

### Serveur (o2switch compatible)
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Apache avec mod_rewrite
- Extension PDO MySQL
- Extension OpenSSL

### En développement
- XAMPP, WAMP, MAMP ou équivalent
- Git (optionnel)

## Installation

### 1. Cloner ou télécharger le projet

```bash
git clone https://github.com/votre-repo/sbn-v1.git
cd sbn-v1
```

### 2. Configuration de la base de données

1. Créer une base de données MySQL :
```sql
CREATE DATABASE sbn_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sbn_dev'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON sbn_dev.* TO 'sbn_dev'@'localhost';
FLUSH PRIVILEGES;
```

2. Importer le schéma :
```bash
mysql -u sbn_dev -p sbn_dev < database/schema.sql
```

### 3. Configuration de l'environnement

1. Copier le fichier `.env.exemple` vers `.env` :
```bash
cp .env.exemple .env
```

2. Modifier `.env` avec vos paramètres :
```ini
DB_HOST=localhost
DB_NAME=sbn_dev
DB_USER=sbn_dev
DB_PASS=votre_mot_de_passe

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/sbn-v1
```

### 4. Permissions (Linux/Mac)

```bash
chmod -R 755 storage/
chmod -R 755 storage/logs/
chmod -R 755 storage/cache/
```

### 5. Configuration Apache (.htaccess)

Le fichier `.htaccess` est déjà configuré. Assurez-vous que `mod_rewrite` est activé :

```bash
# Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2

# XAMPP
# mod_rewrite est généralement activé par défaut
```

### 6. Accéder à l'application

Ouvrez votre navigateur et allez à :
```
http://localhost/sbn-v1
```

## Compte de test

Par défaut, un compte administrateur est créé :

- **Email** : admin@soon22.fr
- **Mot de passe** : Admin123!

**⚠️ IMPORTANT** : Modifiez ces identifiants en production !

## Structure du projet

```
sbn-v1/
├── app/
│   ├── Controllers/     # Contrôleurs MVC
│   ├── Models/          # Modèles de données
│   ├── Views/           # Vues (templates)
│   └── Core/            # Classes de base
├── config/              # Configuration
├── database/            # Scripts SQL
├── public/              # Fichiers publics (CSS, JS, images)
├── storage/             # Logs et cache
├── .env                 # Configuration environnement (ne pas commit)
├── .htaccess           # Configuration Apache
└── index.php           # Point d'entrée

```

## Routes principales

### Publiques
- `/login` - Connexion
- `/register` - Inscription
- `/forgot-password` - Mot de passe oublié

### Protégées (authentification requise)
- `/dashboard` - Tableau de bord
- `/backups` - Liste des sauvegardes
- `/companies` - Gestion des sociétés
- `/settings` - Paramètres utilisateur
- `/gdpr/export` - Export données RGPD
- `/gdpr/delete` - Suppression compte

### Admin uniquement
- `/users` - Gestion des utilisateurs

## Déploiement en production (o2switch)

### 1. Préparation

1. Modifier `.env` :
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sbn.soon22.fr
```

2. Activer HTTPS dans `.htaccess` (décommenter les lignes HTTPS)

### 2. Upload via FTP

Transférer tous les fichiers sauf :
- `.git/`
- `README.md`
- `.env` (recréer sur le serveur)

### 3. Configuration cPanel

1. Créer la base de données MySQL via cPanel
2. Importer `database/schema.sql`
3. Créer le fichier `.env` avec les bonnes informations

### 4. Sécurité post-déploiement

1. Supprimer le compte admin de test
2. Vérifier les permissions des dossiers
3. Activer les sauvegardes automatiques
4. Configurer le monitoring

## Configuration Synology

### Webhooks Active Backup

Pour recevoir les notifications de sauvegarde :

1. Dans Active Backup for Business > Paramètres > Notifications
2. Ajouter un webhook :
```
URL: https://sbn.soon22.fr/api/webhook
Token: [votre_token_api]
```

## Sécurité

### Bonnes pratiques

1. **Mots de passe** : Minimum 8 caractères, complexes
2. **Sessions** : Timeout après 2h d'inactivité
3. **HTTPS** : Obligatoire en production
4. **Backups** : Sauvegardes quotidiennes de la base
5. **Logs** : Surveillance régulière des fichiers de log

### Journalisation

Les logs sont stockés dans `storage/logs/` :
- `database_YYYY-MM-DD.log` - Logs BDD
- `php_errors.log` - Erreurs PHP

## Support et contribution

- **Auteur** : Soon22
- **Version** : 1.0.0
- **Licence** : MIT
- **Contact** : contact@soon22.fr
- **Site web** : https://soon22.fr

## Roadmap v1.1

- [ ] API REST complète
- [ ] Notifications email automatiques
- [ ] Export PDF des rapports
- [ ] Multi-langue (EN/FR)
- [ ] Application mobile
- [ ] Intégration Slack/Teams

## Changelog

### v1.0.0 (2024-11-11)
- Version initiale
- Système d'authentification complet
- Dashboard avec statistiques
- Conformité RGPD
- Multi-tenant
- Sécurité renforcée

## Licence

MIT License - Libre d'utilisation pour vos projets.

---

**Développé avec ❤️ par Soon22**
