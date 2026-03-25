/**
 * SBN v1.0 - JavaScript principal
 * @package SBN
 * @version 1.0.0
 */

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {

    // Auto-fermeture des alertes après 10 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 10000);
    });

    // Gestion du dropdown utilisateur
    initDropdowns();
});

// Dropdown menu cliquable
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');

        if (toggle) {
            // Toggle au clic
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Fermer tous les autres dropdowns
                dropdowns.forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('active');
                    }
                });

                // Toggle le dropdown actuel
                dropdown.classList.toggle('active');
            });
        }
    });

    // Fermer le dropdown en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });

    // Empêcher la fermeture quand on clique dans le menu
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(e) {
            // Toujours stopper la propagation pour empêcher le document.click de fermer le dropdown
            e.stopPropagation();
            // Si c'est un lien, la navigation se fera normalement malgré le stopPropagation
        });
    });
}

// Confirmation avant suppression
function confirmDelete(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?');
}

// Format de la taille de fichier
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Octets';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Octets', 'Ko', 'Mo', 'Go', 'To'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Format de durée
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    const parts = [];

    if (hours > 0) {
        parts.push(hours + 'h');
    }
    if (minutes > 0) {
        parts.push(minutes + 'min');
    }
    if (secs > 0 || parts.length === 0) {
        parts.push(secs + 's');
    }

    return parts.join(' ');
}

// Copier dans le presse-papier
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copié dans le presse-papier', 'success');
        }).catch(err => {
            console.error('Erreur de copie:', err);
        });
    } else {
        // Fallback pour navigateurs anciens
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showNotification('Copié dans le presse-papier', 'success');
        } catch (err) {
            console.error('Erreur de copie:', err);
        }
        document.body.removeChild(textarea);
    }
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alert, mainContent.firstChild);
    } else {
        document.body.insertBefore(alert, document.body.firstChild);
    }

    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 10000); // ← 10000 = 10 secondes
}
