# 📘 Guide de gestion des abonnements

## Pour les utilisateurs

### 🔍 Voir mon abonnement

1. Connectez-vous à votre compte
2. Cliquez sur votre nom en haut à droite
3. Sélectionnez **"Mon profil"**
4. Faites défiler jusqu'à la section **"Mon abonnement"**

Vous verrez :
- ✅ **Statut** : Actif, Essai, Expiré ou Suspendu (avec un grand badge coloré)
- 📅 **Date de début** : Quand votre abonnement a commencé
- 📅 **Date de fin** : Jusqu'à quand votre abonnement est valide
- 🎁 **Jours offerts** : Si l'administrateur vous a offert des jours gratuits
- ⚠️ **Alertes** : Si votre abonnement est expiré ou suspendu

---

## Pour les administrateurs

### 🎯 Vue d'ensemble des abonnements

Allez sur **Utilisateurs** (`/users`) :

Le tableau affiche :
- Colonne **"Rôle"** : Admin, Utilisateur, Collaborateur ou Client
- Colonne **"Abonnement"** :
  - Badge **cliquable** avec le statut (Actif, Essai, Expiré, Suspendu)
  - Date de fin
  - **Cliquez sur le badge pour gérer l'abonnement !**

Les admins affichent "Illimité" (pas d'abonnement à gérer).

---

### ⚙️ Gérer un abonnement

#### Méthode 1 : Cliquer sur la colonne "Abonnement"
1. Allez sur `/users`
2. **Cliquez directement sur le badge d'abonnement** (Actif, Essai, etc.)
3. Vous arrivez sur la page de gestion

#### Méthode 2 : URL directe
- `/users/subscription?id=X` (remplacez X par l'ID de l'utilisateur)

---

### 🎁 Offrir des jours gratuits

Sur la page de gestion d'abonnement :

1. **Section "Offrir des jours gratuits"**
2. Choisissez la durée :
   - 7 jours (1 semaine)
   - 30 jours (1 mois) ⭐
   - 90 jours (3 mois)
   - 180 jours (6 mois)
   - **365 jours (1 an)** ⭐ (par défaut)
   - 730 jours (2 ans)

3. Indiquez la raison (obligatoire) :
   ```
   Exemples :
   - "Abonnement annuel payé"
   - "Promotion Black Friday"
   - "Client fidèle - 1 mois gratuit"
   - "Compensation pour incident technique"
   ```

4. Cliquez sur **"Offrir ces jours"**

**💡 Note** : Les jours s'ajoutent à la date de fin actuelle !

---

### 🚫 Suspendre un compte

**Quand utiliser ?**
- Mauvais payeur
- Non-respect des conditions
- Compte temporairement inactif

**Comment faire ?**

1. Sur la page de gestion d'abonnement
2. Section **"Suspendre / Réactiver"**
3. Remplissez la raison de suspension (obligatoire)
4. Cliquez sur **"Suspendre le compte"**

**⚠️ Important** :
- ✅ Les données sont **conservées**
- ❌ L'utilisateur **ne peut plus se connecter**
- 📝 La raison est enregistrée dans l'historique

---

### ✅ Réactiver un compte suspendu

1. Sur la page de gestion d'abonnement
2. Section **"Suspendre / Réactiver"**
3. Cliquez sur **"Réactiver le compte"**

Le compte redevient actif immédiatement.

---

### 🔑 Réinitialiser un mot de passe (bonus)

Dans la liste des utilisateurs (`/users`) :

1. Bouton **🔑** (jaune) dans la colonne "Actions"
2. Confirmez l'action
3. Un mot de passe temporaire s'affiche **UNE SEULE FOIS**
4. **Copiez-le immédiatement** et communiquez-le à l'utilisateur
5. L'utilisateur devra le changer à sa prochaine connexion

**🔒 Conformité RGPD** :
- Vous ne voyez JAMAIS le mot de passe actuel
- Le nouveau mot de passe est affiché une seule fois
- Toutes les actions sont auditées

---

## 📊 Codes couleur des statuts

| Statut | Couleur | Signification |
|--------|---------|---------------|
| **Actif** | 🟢 Vert | Abonnement valide |
| **Essai** | 🟡 Jaune | Période d'essai (30j par défaut) |
| **Suspendu** | 🔴 Rouge/Orange | Compte temporairement désactivé |
| **Expiré** | 🔴 Rouge foncé | Abonnement terminé |
| **Illimité** | ⚪ Gris | Compte administrateur |

---

## ❓ Questions fréquentes

### Que se passe-t-il quand un abonnement expire ?
- L'utilisateur ne peut plus se connecter
- Un message d'erreur lui demande de contacter l'administrateur
- Les données sont conservées

### Puis-je modifier l'abonnement d'un admin ?
Non, les administrateurs ont toujours un accès illimité.

### Puis-je offrir plusieurs fois des jours gratuits ?
Oui ! Les jours s'additionnent. Par exemple :
- L'utilisateur a un abonnement jusqu'au 31/12/2025
- Vous offrez 365 jours
- Son nouvel abonnement se termine le 31/12/2026

### La suspension supprime-t-elle les données ?
Non, toutes les données restent intactes. Seule la connexion est bloquée.

### Où voir l'historique des modifications d'abonnement ?
Sur la page de gestion d'abonnement, section "Notes" affiche toutes les actions avec dates et raisons.

---

## 🎯 Exemples d'utilisation

### Cas 1 : Nouveau client - Abonnement 1 an

1. Créez l'utilisateur avec le rôle "User"
2. Cliquez sur le badge "Essai" dans la colonne Abonnement
3. Offrez **365 jours**
4. Raison : "Abonnement annuel payé - Facture #2025-001"
5. ✅ Le client a maintenant 1 an d'accès

### Cas 2 : Client qui ne paie pas

1. Cliquez sur le badge "Actif"
2. Section "Suspendre"
3. Raison : "Facture #2025-002 impayée depuis 30 jours"
4. Suspendre le compte
5. ✅ Le client ne peut plus se connecter
6. Quand il paie → Réactiver le compte

### Cas 3 : Promotion 1 mois gratuit

1. Cliquez sur le badge de l'utilisateur
2. Offrez **30 jours**
3. Raison : "Promotion Black Friday 2025"
4. ✅ Un mois est ajouté à sa date de fin actuelle

---

## 📞 Support

Pour toute question :
- Email : contact@soon22.fr
- Documentation : `/documentation`

---

**Développé par Johnny Girault - Soon22**
