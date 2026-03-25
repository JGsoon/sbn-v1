# 🛠️ Outils de Développement et Debug

Ce dossier contient des outils de développement, debug et vérification qui **NE DOIVENT PAS** être accessibles en production.

## 🔒 Sécurité

- Ce dossier est protégé par `.htaccess` pour bloquer l'accès web
- Accès autorisé uniquement en local (127.0.0.1 / ::1)
- En production, vérifier que l'accès est bien bloqué

## 📋 Fichiers disponibles

### `check-install.php`
Vérifie que l'installation est complète et fonctionnelle :
- Permissions des dossiers
- Configuration PHP
- Connexion base de données
- Extensions PHP requises

**Usage :** `php tools/check-install.php` ou accès via navigateur en local

### `test-db.php`
Test de connexion et requêtes à la base de données :
- Vérification de la connexion
- Test des tables principales
- Affichage des données de test

**Usage :** `php tools/test-db.php` ou accès via navigateur en local

### `debug-login.php`
Outil de debug pour le système d'authentification :
- Affiche les informations de session
- Vérifie les credentials
- Debug des problèmes de login

**Usage :** Accès via navigateur en local uniquement

### `check-production-ready.php`
Vérifie que l'application est prête pour la production :
- Vérification des variables d'environnement
- Sécurité (debug mode désactivé, etc.)
- Permissions et configurations
- Checklist de déploiement

**Usage :** `php tools/check-production-ready.php` avant chaque déploiement

## ⚠️ Attention

**NE JAMAIS** :
- Rendre ces fichiers accessibles en production
- Commiter des credentials ou données sensibles dans ces fichiers
- Utiliser ces outils sur un serveur de production sans supervision

## 🚀 Utilisation recommandée

1. **Avant déploiement** : Lancer `check-production-ready.php`
2. **Après installation** : Lancer `check-install.php`
3. **En cas de problème** : Utiliser les outils de debug appropriés
4. **Tests réguliers** : Vérifier la connexion DB avec `test-db.php`
