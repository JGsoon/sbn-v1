<?php
/**
 * Télécharger un petit package Synology officiel pour référence
 */

echo "========================================\n";
echo "Téléchargement d'un package de référence\n";
echo "========================================\n\n";

// Essayer de télécharger un petit package depuis l'archive Synology
// Utilisons un package simple et petit
$packageUrl = "https://archive.synology.com/download/Package/spk/Perl/5.28.1-0011/noarch/Perl-5.28.1-0011-noarch.spk";
$outputFile = __DIR__ . '/REFERENCE-Perl.spk';

echo "Téléchargement depuis archive.synology.com...\n";
echo "URL: $packageUrl\n\n";

$ch = curl_init($packageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200 || empty($data)) {
    echo "❌ Échec du téléchargement (HTTP $httpCode)\n";
    if ($error) echo "   Erreur: $error\n";
    echo "\nVous pouvez télécharger manuellement un package depuis:\n";
    echo "https://archive.synology.com/download/Package/spk/\n\n";
    echo "Puis copiez-le dans: $outputFile\n";
    exit(1);
}

file_put_contents($outputFile, $data);

echo "✅ Package téléchargé: " . basename($outputFile) . "\n";
echo "   Taille: " . number_format(filesize($outputFile)) . " bytes\n\n";

// Analyser la structure
echo "========================================\n";
echo "Structure du package officiel Synology:\n";
echo "========================================\n\n";

$fp = fopen($outputFile, 'rb');
$num = 0;
$maxFiles = 10; // Limiter l'affichage

while (!feof($fp) && $num < $maxFiles) {
    $header = fread($fp, 512);
    if (strlen($header) < 512) break;
    if (trim($header) === '') break;

    $filename = trim(substr($header, 0, 100));
    if (empty($filename)) break;

    // Extraire les champs du header
    $modeOctal = substr($header, 100, 8);
    $modeDec = octdec(trim($modeOctal));
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);
    $type = substr($header, 156, 1);
    $magic = substr($header, 257, 6);
    $version = substr($header, 263, 2);

    $typeStr = ($type === '5') ? 'DIR' : ($type === '0' || $type === "\0" ? 'FILE' : "TYPE=" . ord($type));
    $isExec = ($modeDec & 0111) !== 0;

    $num++;
    echo "$num. $filename\n";
    echo "   Type: $typeStr\n";
    echo "   Mode: " . sprintf("0%o", $modeDec) . ($isExec ? " ✅ EXEC" : "") . "\n";
    echo "   Size: " . number_format($size) . " bytes\n";
    echo "   Magic: \"" . trim($magic) . "\" (hex: " . bin2hex($magic) . ")\n";
    echo "   Version: \"" . $version . "\" (hex: " . bin2hex($version) . ")\n";
    echo "\n";

    if ($size > 0) {
        $blocks = ceil($size / 512);
        fseek($fp, $blocks * 512, SEEK_CUR);
    }
}

fclose($fp);

if ($num >= $maxFiles) {
    echo "... (affichage limité aux $maxFiles premières entrées)\n\n";
}

echo "========================================\n\n";

echo "Maintenant comparons avec notre SPK:\n\n";

// Comparer avec notre SPK
$ourFile = __DIR__ . '/SPK-CORRECT-PERMS-20260323145918.spk';
if (!file_exists($ourFile)) {
    $files = glob(__DIR__ . '/SPK-CORRECT-PERMS-*.spk');
    if (!empty($files)) {
        $ourFile = end($files);
    }
}

if (!file_exists($ourFile)) {
    echo "❌ Notre SPK de test non trouvé\n";
    exit(0);
}

echo "========================================\n";
echo "Structure de NOTRE SPK:\n";
echo "========================================\n\n";

$fp = fopen($ourFile, 'rb');
$num = 0;

while (!feof($fp) && $num < $maxFiles) {
    $header = fread($fp, 512);
    if (strlen($header) < 512) break;
    if (trim($header) === '') break;

    $filename = trim(substr($header, 0, 100));
    if (empty($filename)) break;

    $modeOctal = substr($header, 100, 8);
    $modeDec = octdec(trim($modeOctal));
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);
    $type = substr($header, 156, 1);
    $magic = substr($header, 257, 6);
    $version = substr($header, 263, 2);

    $typeStr = ($type === '5') ? 'DIR' : ($type === '0' || $type === "\0" ? 'FILE' : "TYPE=" . ord($type));
    $isExec = ($modeDec & 0111) !== 0;

    $num++;
    echo "$num. $filename\n";
    echo "   Type: $typeStr\n";
    echo "   Mode: " . sprintf("0%o", $modeDec) . ($isExec ? " ✅ EXEC" : "") . "\n";
    echo "   Size: " . number_format($size) . " bytes\n";
    echo "   Magic: \"" . trim($magic) . "\" (hex: " . bin2hex($magic) . ")\n";
    echo "   Version: \"" . $version . "\" (hex: " . bin2hex($version) . ")\n";
    echo "\n";

    if ($size > 0) {
        $blocks = ceil($size / 512);
        fseek($fp, $blocks * 512, SEEK_CUR);
    }
}

fclose($fp);

echo "========================================\n\n";
echo "COMPARAISON:\n";
echo "Recherchez les différences dans les champs Magic, Version, Mode, etc.\n";
