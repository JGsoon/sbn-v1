# Changelog - Nouvelles fonctionnalités SBN v1.0

## Date : 14 novembre 2025

### 🎯 Corrections et améliorations

#### 1. **Correction du dropdown de navigation** ✅
- **Problème** : Le dropdown s'ouvrait mais se fermait immédiatement, empêchant la sélection
- **Solution** : Correction du gestionnaire d'événements JavaScript pour stopper la propagation correctement
- **Fichier modifié** : `public/js/main.js:61-68`

---

### 🔐 Système de rôles et permissions

#### 2. **Nouveaux rôles utilisateurs** ✅
4 types de rôles sont maintenant disponibles :

- **Admin** : Accès complet, peut gérer les utilisateurs et les abonnements
- **User** : Peut créer ses propres tableaux (tokens API) et les partager
- **Collaborator** : Accès à tous les tableaux partagés avec possibilité de modification
- **Client** : Accès limité en lecture seule à leur(s) tableau(x)

**Exemple d'utilisation** :
- Michel (User) gère 10 sociétés
- Son collaborateur (Collaborator) peut accéder et modifier tous les tableaux
- Les clients de Michel (Client) ne voient que leur tableau en lecture seule

**Fichiers concernés** :
- `database/add_roles_subscription_sharing.sql`
- `app/Models/User.php`
- `app/Controllers/UserController.php`
- `app/Views/users/*.php`

---

### 💳 Gestion des abonnements

#### 3. **Système d'abonnement complet** ✅

**Statuts d'abonnement** :
- `active` : Abonnement actif
- `trial` : Période d'essai (30 jours par défaut)
- `expired` : Abonnement expiré
- `suspended` : Compte suspendu (mauvais payeur)

**Fonctionnalités admin** :
- ✅ Offrir des jours/mois/années gratuits
- ✅ Suspendre un compte (conserve les données)
- ✅ Réactiver un compte suspendu
- ✅ Voir l'historique des modifications d'abonnement

**Accès** : `/users/subscription?id=X` (admin uniquement)

**Fichiers concernés** :
- `app/Models/User.php:309-569`
- `app/Controllers/UserController.php:367-440`
- `app/Views/users/subscription.php`

---

### 🔑 Réinitialisation de mot de passe (Admin)

#### 4. **Réinitialisation sécurisée et conforme RGPD** ✅

**Fonctionnement** :
1. L'admin clique sur le bouton "Réinitialiser le mot de passe"
2. Un mot de passe temporaire sécurisé (16 caractères) est généré
3. Le mot de passe est affiché UNE SEULE FOIS à l'admin
4. L'admin ne peut JAMAIS voir le mot de passe en clair après l'affichage initial
5. L'action est tracée dans la table `password_reset_history`

**Conformité RGPD** :
- ✅ L'admin ne voit jamais le mot de passe actuel
- ✅ Le mot de passe temporaire n'est affiché qu'une fois
- ✅ Toutes les actions sont auditées
- ✅ L'IP et la date sont enregistrées

**Fichiers concernés** :
- `app/Models/User.php:430-467`
- `app/Controllers/UserController.php:442-488`
- `app/Views/users/index.php:13-25`
- `database/add_roles_subscription_sharing.sql:59-74`

---

### 🔄 Système de partage

#### 5. **Partage granulaire de tableaux** ✅

**Niveaux d'accès** :
- `read` : Lecture seule
- `write` : Lecture et modification

**Fonctionnalités** :
- Partager une société spécifique avec un utilisateur
- Révoquer un partage
- Voir les partages actifs

**Modèle** : `app/Models/SharedAccess.php`

**Fichiers concernés** :
- `database/add_roles_subscription_sharing.sql:27-47`
- `app/Models/SharedAccess.php`
- `app/Models/User.php:490-569`

---

### 🖥️ Gestion des NAS Synology

#### 6. **Table et modèle NAS** ✅

**Informations stockées** :
- Nom du NAS
- QuickConnect ID (lien direct vers le NAS)
- Adresse IP
- Modèle et version DSM
- Statut actif/inactif
- Dernière connexion

**Modèle** : `app/Models/NasDevice.php`

**Fichiers concernés** :
- `database/add_roles_subscription_sharing.sql:49-58`
- `app/Models/NasDevice.php`

---

### 📊 Nouveau Dashboard

#### 7. **Dashboard restructuré** ✅

**Affichage par client** :
```
Client : [Nom du client] [Niveau d'accès]
  ├── NAS : [Nom du NAS] - QuickConnect: [lien]
  │   ├── Statistiques : X réussies, Y échecs
  │   ├── Équipement 1 : [Nom] - [État avec couleur] - [Date] - [Taille]
  │   ├── Équipement 2 : [Nom] - [État avec couleur] - [Date] - [Taille]
  │   └── ...
  └── NAS 2 ...
```

**Codes couleur des états** :
- 🟢 Vert : Sauvegarde réussie
- 🔴 Rouge : Échec
- 🟡 Jaune : En cours
- ⚪ Gris : Aucune sauvegarde

**Informations affichées** :
- Nom de l'équipement et hostname
- État de la dernière sauvegarde
- Date (format relatif : "Il y a 2h" ou date complète)
- Taille de la sauvegarde
- Nombre de sauvegardes réussies/échouées

**Fichiers concernés** :
- `app/Controllers/DashboardController.php`
- `app/Views/dashboard/index.php` (nouvelle version)
- `app/Views/dashboard/index_old.php` (ancienne version sauvegardée)

---

## 📋 Migration de la base de données

Pour appliquer toutes ces modifications, exécutez :

```bash
mysql -u sbn_dev -psbn_dev_2024 sbn_dev < database/add_roles_subscription_sharing.sql
```

Ou via XAMPP :
```bash
C:\xampp\mysql\bin\mysql.exe -u sbn_dev -psbn_dev_2024 sbn_dev < C:\xampp\htdocs\sbn-v1\database\add_roles_subscription_sharing.sql
```

**Note** : Le script a déjà été exécuté automatiquement lors de l'implémentation.

---

## 🎨 Améliorations visuelles

- Interface moderne avec dégradés de couleurs
- Badges et statuts visuels
- Cards avec ombres et effets
- Design responsive
- Animations et transitions fluides

---

## 🔒 Sécurité et RGPD

- ✅ Vérification des permissions sur toutes les actions
- ✅ Audit trail complet (table `audit_logs`)
- ✅ Historique des réinitialisations de mots de passe
- ✅ Isolation des données par société (multi-tenant)
- ✅ Conformité RGPD pour la gestion des mots de passe
- ✅ Protection CSRF sur tous les formulaires

---

## 📝 Utilisation

### Pour les administrateurs

1. **Gérer les utilisateurs** : `/users`
   - Créer des utilisateurs avec différents rôles
   - Gérer les abonnements
   - Réinitialiser les mots de passe

2. **Gérer les abonnements** : `/users/subscription?id=X`
   - Offrir des jours gratuits
   - Suspendre/réactiver un compte

3. **Réinitialiser un mot de passe** :
   - Bouton dans la liste des utilisateurs
   - Le mot de passe temporaire s'affiche une seule fois

### Pour les utilisateurs

1. **Dashboard** : `/dashboard`
   - Vue par client/NAS/équipement
   - États des sauvegardes en temps réel
   - Statistiques globales

2. **Création de tokens API** : `/settings/api`
   - Chaque token correspond à une société/client
   - Les tokens peuvent être partagés avec des collaborateurs ou clients

---

## 🚀 Prochaines étapes suggérées

- [ ] Créer une interface de gestion des partages
- [ ] Ajouter des notifications par email pour les échecs de sauvegarde
- [ ] Implémenter un système de rapports PDF
- [ ] Ajouter un calendrier de sauvegardes
- [ ] Créer des alertes personnalisées

---

## 📞 Support

Pour toute question ou problème, contactez :
- Email : contact@soon22.fr
- GitHub : https://github.com/soon22

---

**Développé par Johnny Girault - Soon22**
