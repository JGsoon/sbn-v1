<?php
/**
 * SBN v1.0 - Configuration des routes
 *
 * @package SBN
 * @version 1.0.0
 */

return [
    // Routes publiques
    '' => ['controller' => 'AuthController', 'action' => 'login'],
    'login' => ['controller' => 'AuthController', 'action' => 'login'],
    'register' => ['controller' => 'AuthController', 'action' => 'register'],
    'forgot-password' => ['controller' => 'AuthController', 'action' => 'forgotPassword'],
    'reset-password' => ['controller' => 'AuthController', 'action' => 'resetPassword'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout'],

    // Routes protégées - Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'auth' => true],

    // Routes protégées - Sauvegardes
    'backups' => ['controller' => 'BackupController', 'action' => 'index', 'auth' => true],
    'backups/detail' => ['controller' => 'BackupController', 'action' => 'detail', 'auth' => true],
    'backups/history' => ['controller' => 'BackupController', 'action' => 'history', 'auth' => true],

    // Routes protégées - Documentation
    'documentation' => ['controller' => 'DocumentationController', 'action' => 'index', 'auth' => true],
    'documentation/download-webhook' => ['controller' => 'DocumentationController', 'action' => 'downloadWebhookScript', 'auth' => true],
    'documentation/download-config' => ['controller' => 'DocumentationController', 'action' => 'downloadConfigExample', 'auth' => true],
    'documentation/download-install' => ['controller' => 'DocumentationController', 'action' => 'downloadInstallScript', 'auth' => true],

    // Routes protégées - Sociétés
    'companies' => ['controller' => 'CompanyController', 'action' => 'index', 'auth' => true],
    'companies/add' => ['controller' => 'CompanyController', 'action' => 'add', 'auth' => true],
    'companies/edit' => ['controller' => 'CompanyController', 'action' => 'edit', 'auth' => true],
    'companies/delete' => ['controller' => 'CompanyController', 'action' => 'delete', 'auth' => true],

    // Routes protégées - Utilisateurs (Admin uniquement)
    'users' => ['controller' => 'UserController', 'action' => 'index', 'auth' => true, 'role' => 'admin'],
    'users/add' => ['controller' => 'UserController', 'action' => 'add', 'auth' => true, 'role' => 'admin'],
    'users/edit' => ['controller' => 'UserController', 'action' => 'edit', 'auth' => true, 'role' => 'admin'],
    'users/delete' => ['controller' => 'UserController', 'action' => 'delete', 'auth' => true, 'role' => 'admin'],
    'users/subscription' => ['controller' => 'UserController', 'action' => 'subscription', 'auth' => true, 'role' => 'admin'],
    'users/resetPassword' => ['controller' => 'UserController', 'action' => 'resetPassword', 'auth' => true, 'role' => 'admin'],

    // Routes protégées - Paramètres
    'settings' => ['controller' => 'SettingsController', 'action' => 'index', 'auth' => true],
    'settings/profile' => ['controller' => 'SettingsController', 'action' => 'profile', 'auth' => true],
    'settings/security' => ['controller' => 'SettingsController', 'action' => 'security', 'auth' => true],
    'settings/notifications' => ['controller' => 'SettingsController', 'action' => 'notifications', 'auth' => true],
    'settings/smtp' => ['controller' => 'SmtpConfigController', 'action' => 'index', 'auth' => true, 'role' => 'admin'],
    'settings/smtp/test' => ['controller' => 'SmtpConfigController', 'action' => 'test', 'auth' => true, 'role' => 'admin'],
    'settings/api' => ['controller' => 'ApiTokenController', 'action' => 'index', 'auth' => true],
    'settings/api/create' => ['controller' => 'ApiTokenController', 'action' => 'create', 'auth' => true],
    'settings/api/download' => ['controller' => 'ApiTokenController', 'action' => 'download', 'auth' => true],
    'settings/api/download-spk' => ['controller' => 'ApiTokenController', 'action' => 'downloadSpk', 'auth' => true],
    'settings/api/clear-token' => ['controller' => 'ApiTokenController', 'action' => 'clearToken', 'auth' => true],
    'settings/api/revoke' => ['controller' => 'ApiTokenController', 'action' => 'revoke', 'auth' => true],
    'settings/api/delete' => ['controller' => 'ApiTokenController', 'action' => 'delete', 'auth' => true],

    // Routes protégées - Partage
    'share' => ['controller' => 'ShareController', 'action' => 'index', 'auth' => true],
    'share/create' => ['controller' => 'ShareController', 'action' => 'create', 'auth' => true],
    'share/revoke' => ['controller' => 'ShareController', 'action' => 'revoke', 'auth' => true],
    'share/delete' => ['controller' => 'ShareController', 'action' => 'delete', 'auth' => true],

    // Routes RGPD
    'privacy' => ['controller' => 'LegalController', 'action' => 'privacy'],
    'terms' => ['controller' => 'LegalController', 'action' => 'terms'],
    'gdpr/export' => ['controller' => 'GdprController', 'action' => 'export', 'auth' => true],
    'gdpr/delete' => ['controller' => 'GdprController', 'action' => 'delete', 'auth' => true],

    // API Routes
    'api/backup/status' => ['controller' => 'ApiController', 'action' => 'backupStatus', 'auth' => true],
    'api/webhook' => ['controller' => 'ApiController', 'action' => 'webhook'],
];
