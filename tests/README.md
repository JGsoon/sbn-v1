# 🧪 Tests SBN

Suite de tests pour garantir la stabilité de l'application lors de l'ajout de nouvelles fonctionnalités.

## 📁 Structure

```
tests/
├── bootstrap.php          # Configuration et helpers de test
├── .env.test             # Variables d'environnement pour les tests
├── run-all-tests.php     # Lance tous les tests
├── Unit/                 # Tests unitaires (modèles, fonctions)
├── Integration/          # Tests d'intégration (contrôleurs, workflows)
└── E2E/                  # Tests end-to-end (scénarios utilisateur)
```

## 🚀 Lancer les tests

### Tous les tests
```bash
php tests/run-all-tests.php
```

### Tests unitaires uniquement
```bash
php tests/Unit/UserModelTest.php
```

### Tests d'intégration
```bash
php tests/Integration/AuthTest.php
```

### Tests E2E
```bash
php tests/E2E/CompanyFlowTest.php
```

## ✅ Types de tests

### Tests Unitaires (`Unit/`)
Testent des composants isolés (modèles, helpers, fonctions)
- Rapides
- Pas de dépendances externes
- Testent la logique métier

**Exemple:**
- Validation d'email
- Hashing de mot de passe
- Méthodes de modèles

### Tests d'Intégration (`Integration/`)
Testent les interactions entre composants
- Contrôleurs + Modèles
- Workflows d'authentification
- Validation de formulaires

**Exemple:**
- Processus de login complet
- Création d'une entreprise
- Gestion de session

### Tests E2E (`E2E/`)
Testent des scénarios utilisateur complets
- Simulent un parcours utilisateur
- Testent plusieurs fonctionnalités ensemble

**Exemple:**
- Inscription → Login → Création entreprise → Modification → Suppression

## 📝 Écrire un nouveau test

### 1. Créer le fichier
```php
<?php
// tests/Unit/MyNewTest.php
require_once __DIR__ . '/../bootstrap.php';

runTest("Description du test", function() {
    // Votre logique de test
    assertTrue($condition, "Message d'assertion");
});
```

### 2. Utiliser les assertions

```php
// Vérifier une égalité
assertEqual($expected, $actual, "Should be equal");

// Vérifier vrai/faux
assertTrue($condition, "Should be true");
assertFalse($condition, "Should be false");

// Vérifier non null
assertNotNull($value, "Should not be null");
```

### 3. Tester avec la base de données

Pour les tests nécessitant la DB, utilisez `.env.test` avec une base de données séparée :

```php
// La connexion utilise automatiquement DB_NAME=sbn_test
require_once __DIR__ . '/../bootstrap.php';
```

## 🎯 Bonnes pratiques

1. **Un test = Une fonctionnalité**
   - Chaque test doit tester une seule chose
   - Nom de test descriptif

2. **Tests indépendants**
   - Chaque test doit pouvoir s'exécuter seul
   - Pas de dépendances entre tests

3. **Nettoyage**
   - Utiliser une DB de test séparée
   - Rollback ou nettoyage après chaque test

4. **Avant chaque commit**
   - Lancer `php tests/run-all-tests.php`
   - S'assurer que tous les tests passent

5. **Lors d'un bug**
   - Écrire un test qui reproduit le bug
   - Corriger le code
   - Le test doit passer

## 🔧 Configuration

### Base de données de test

1. Créer une base de données séparée:
```sql
CREATE DATABASE sbn_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importer le schéma:
```bash
mysql -u root sbn_test < database/schema.sql
```

3. Configurer `.env.test` avec les bonnes informations

## 📊 Intégration Continue (CI)

Ces tests peuvent être intégrés dans un pipeline CI/CD :

```yaml
# Exemple pour GitHub Actions
- name: Run tests
  run: php tests/run-all-tests.php
```

## ⚠️ Troubleshooting

### Tests échouent en local mais pas en prod
- Vérifier `.env.test` vs `.env`
- Vérifier les différences de configuration PHP
- Vérifier les versions de dépendances

### Tests de DB échouent
- Vérifier que `sbn_test` existe
- Vérifier les permissions MySQL
- Vérifier que le schéma est à jour

### Erreurs de session
- En CLI, certains tests de session sont simulés
- Pour tester réellement, utiliser un navigateur headless (Selenium, Puppeteer)

## 🚀 Prochaines étapes

- [ ] Ajouter PHPUnit pour tests plus avancés
- [ ] Ajouter couverture de code (code coverage)
- [ ] Ajouter tests de régression automatiques
- [ ] Ajouter tests de performance
- [ ] Intégrer dans CI/CD
