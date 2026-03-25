<div class="page-header">
    <h1><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h1>
    <p>Modifier les informations de <?= htmlspecialchars($targetUser['name']) ?></p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/users/edit?id=<?= $targetUser['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Nom complet *
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control <?= isset($_SESSION['errors']['name']) ? 'is-invalid' : '' ?>"
                        placeholder="Jean Dupont"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['name'] ?? $targetUser['name']) ?>"
                    >
                    <?php if (isset($_SESSION['errors']['name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['name']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= isset($_SESSION['errors']['email']) ? 'is-invalid' : '' ?>"
                        placeholder="jean.dupont@exemple.fr"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['email'] ?? $targetUser['email']) ?>"
                    >
                    <?php if (isset($_SESSION['errors']['email'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['email']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Téléphone
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control"
                        placeholder="+33 6 12 34 56 78"
                        value="<?= htmlspecialchars($_SESSION['old']['phone'] ?? $targetUser['phone']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-shield-alt"></i> Rôle *
                    </label>
                    <select
                        id="role"
                        name="role"
                        class="form-control <?= isset($_SESSION['errors']['role']) ? 'is-invalid' : '' ?>"
                        required
                    >
                        <option value="user" <?= ($targetUser['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>
                            Utilisateur (Peut créer ses propres tableaux)
                        </option>
                        <option value="collaborator" <?= ($targetUser['role'] ?? '') === 'collaborator' ? 'selected' : '' ?>>
                            Collaborateur (Accès à tous les tableaux partagés avec modification)
                        </option>
                        <option value="client" <?= ($targetUser['role'] ?? '') === 'client' ? 'selected' : '' ?>>
                            Client (Accès limité en lecture seule)
                        </option>
                        <option value="admin" <?= ($targetUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                            Administrateur (Accès complet)
                        </option>
                    </select>
                    <?php if (isset($_SESSION['errors']['role'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['role']) ?>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">
                        Choisissez le niveau d'accès approprié pour cet utilisateur
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        <?= ($targetUser['is_active'] ?? 1) ? 'checked' : '' ?>
                        style="width: 20px; height: 20px; cursor: pointer;"
                    >
                    <span style="font-weight: var(--font-weight-medium);">
                        <i class="fas fa-check-circle"></i> Compte actif
                    </span>
                </label>
                <small class="text-muted">
                    Si désactivé, l'utilisateur ne pourra plus se connecter
                </small>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border-color);">

            <h3 style="margin-bottom: 1rem; font-size: 1.125rem; color: var(--text-primary);">
                <i class="fas fa-lock"></i> Changer le mot de passe (optionnel)
            </h3>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Laissez vide si vous ne souhaitez pas changer le mot de passe</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Nouveau mot de passe
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>"
                        placeholder="Minimum 8 caractères"
                    >
                    <?php if (isset($_SESSION['errors']['password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['password']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password_confirm">
                        <i class="fas fa-lock"></i> Confirmer le mot de passe
                    </label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control <?= isset($_SESSION['errors']['password_confirm']) ? 'is-invalid' : '' ?>"
                        placeholder="Répétez le mot de passe"
                    >
                    <?php if (isset($_SESSION['errors']['password_confirm'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['password_confirm']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Nettoyer les erreurs et anciennes valeurs
unset($_SESSION['errors'], $_SESSION['old']);
?>

<style>
.page-header {
    margin-bottom: var(--spacing-2xl);
}

.page-header h1 {
    font-size: 2rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: var(--spacing-sm);
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.page-header p {
    color: var(--text-secondary);
    font-size: 1.125rem;
}
</style>
