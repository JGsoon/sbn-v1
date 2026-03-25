<?php
/**
 * Analyser un vrai SPK Synology pour comprendre la structure exacte
 */

echo "========================================\n";
echo "Analyse de structure SPK\n";
echo "========================================\n\n";

echo "Pour diagnostiquer le problème, nous devons comparer avec un vrai package Synology.\n\n";

echo "INSTRUCTIONS:\n";
echo "1. Téléchargez un package officiel depuis Synology (par exemple un package simple)\n";
echo "2. Copiez-le dans: C:\\xampp\\htdocs\\sbn-v1\\tools\\REFERENCE.spk\n";
echo "3. Relancez ce script\n\n";

$referenceFile = __DIR__ . '/REFERENCE.spk';

if (!file_exists($referenceFile)) {
    echo "❌ Fichier REFERENCE.spk non trouvé.\n";
    echo "\nVous pouvez télécharger un package de test depuis:\n";
    echo "- Package Center de votre NAS\n";
    echo "- https://archive.synology.com/download/Package/\n\n";
    exit(1);
}

echo "✅ Fichier de référence trouvé!\n\n";

// Analyser le SPK de référence
echo "Analyse du SPK de référence:\n";
echo "========================================\n";

$fp = fopen($referenceFile, 'rb');
$fileNum = 0;

while (!feof($fp)) {
    $header = fread($fp, 512);
    if (strlen($header) < 512) break;
    if (trim($header) === '') break;

    $filename = trim(substr($header, 0, 100));
    if (empty($filename)) break;

    // Extraire tous les champs importants du header
    $mode = trim(substr($header, 100, 8));
    $uid = trim(substr($header, 108, 8));
    $gid = trim(substr($header, 116, 8));
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);
    $mtime = trim(substr($header, 136, 12));
    $checksum = trim(substr($header, 148, 8));
    $type = substr($header, 156, 1);
    $linkname = trim(substr($header, 157, 100));
    $magic = substr($header, 257, 6);
    $version = substr($header, 263, 2);
    $uname = trim(substr($header, 265, 32));
    $gname = trim(substr($header, 297, 32));

    $fileNum++;
    echo "$fileNum. $filename\n";
    echo "   Type: " . ($type === '5' ? 'DIR' : ($type === '0' || $type === "\0" ? 'FILE' : "TYPE=$type")) . "\n";
    echo "   Mode: $mode\n";
    echo "   Size: $size bytes\n";
    echo "   Magic: " . bin2hex($magic) . " (" . addcslashes($magic, "\0..\37") . ")\n";
    echo "   Version: " . bin2hex($version) . "\n";
    if (!empty($uname)) echo "   Owner: $uname\n";
    if (!empty($gname)) echo "   Group: $gname\n";
    echo "\n";

    if ($size > 0) {
        $blocks = ceil($size / 512);
        fseek($fp, $blocks * 512, SEEK_CUR);
    }
}

fclose($fp);

echo "========================================\n";
echo "Total: $fileNum entrées\n\n";

// Maintenant analyser notre SPK
echo "Analyse de notre SPK:\n";
echo "========================================\n";

$ourFile = __DIR__ . '/MINIMAL-PHAR-20260323145430.spk';
if (!file_exists($ourFile)) {
    // Trouver le dernier SPK créé
    $files = glob(__DIR__ . '/MINIMAL-PHAR-*.spk');
    if (!empty($files)) {
        $ourFile = end($files);
    }
}

if (!file_exists($ourFile)) {
    echo "❌ Aucun SPK de test trouvé\n";
    exit(1);
}

echo "Fichier: " . basename($ourFile) . "\n\n";

$fp = fopen($ourFile, 'rb');
$fileNum = 0;

while (!feof($fp)) {
    $header = fread($fp, 512);
    if (strlen($header) < 512) break;
    if (trim($header) === '') break;

    $filename = trim(substr($header, 0, 100));
    if (empty($filename)) break;

    $mode = trim(substr($header, 100, 8));
    $uid = trim(substr($header, 108, 8));
    $gid = trim(substr($header, 116, 8));
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);
    $type = substr($header, 156, 1);
    $magic = substr($header, 257, 6);
    $version = substr($header, 263, 2);
    $uname = trim(substr($header, 265, 32));
    $gname = trim(substr($header, 297, 32));

    $fileNum++;
    echo "$fileNum. $filename\n";
    echo "   Type: " . ($type === '5' ? 'DIR' : ($type === '0' || $type === "\0" ? 'FILE' : "TYPE=$type")) . "\n";
    echo "   Mode: $mode\n";
    echo "   Size: $size bytes\n";
    echo "   Magic: " . bin2hex($magic) . " (" . addcslashes($magic, "\0..\37") . ")\n";
    echo "   Version: " . bin2hex($version) . "\n";
    if (!empty($uname)) echo "   Owner: $uname\n";
    if (!empty($gname)) echo "   Group: $gname\n";
    echo "\n";

    if ($size > 0) {
        $blocks = ceil($size / 512);
        fseek($fp, $blocks * 512, SEEK_CUR);
    }
}

fclose($fp);

echo "========================================\n";
echo "Total: $fileNum entrées\n";
