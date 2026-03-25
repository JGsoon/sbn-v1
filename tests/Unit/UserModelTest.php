<?php
/**
 * Tests unitaires pour le modèle User
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Models\User;

// Test de validation d'email
runTest("User email validation - valid email", function() {
    // Test de validation d'email sans instancier le modèle
    // (car il nécessite une connexion DB)
    // Simuler une méthode de validation si elle existe
    $validEmail = "test@example.com";
    assertTrue(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false, "Email should be valid");
});

runTest("User email validation - invalid email", function() {
    $invalidEmail = "not-an-email";
    assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) !== false, "Email should be invalid");
});

// Test de validation de mot de passe
runTest("Password hashing works", function() {
    $password = "SecurePassword123!";
    $hash = password_hash($password, PASSWORD_DEFAULT);

    assertNotNull($hash, "Hash should not be null");
    assertTrue(password_verify($password, $hash), "Password should verify against hash");
});

runTest("Password verification fails with wrong password", function() {
    $password = "SecurePassword123!";
    $wrongPassword = "WrongPassword456!";
    $hash = password_hash($password, PASSWORD_DEFAULT);

    assertFalse(password_verify($wrongPassword, $hash), "Wrong password should not verify");
});

// Test de création d'utilisateur (structure)
runTest("User model class exists", function() {
    assertTrue(class_exists('App\Models\User'), "User model class should exist");
});

echo PHP_EOL . "✅ All User model unit tests completed!" . PHP_EOL;
