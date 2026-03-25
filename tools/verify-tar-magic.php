<?php
/**
 * Vérifier le magic bytes du header TAR
 */

// Trouver le fichier SPK le plus récent
$files = glob(__DIR__ . '/SPK-CORRECT-PERMS-*.spk');
if (!empty($files)) {
    // Trier par date de modification
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $spkFile = $files[0];
} else {
    $spkFile = __DIR__ . '/SPK-CORRECT-PERMS.spk';
}

if (!file_exists($spkFile)) {
    echo "❌ Fichier SPK non trouvé\n";
    exit(1);
}

echo "Analyse du header TAR:\n";
echo "========================================\n\n";

$fp = fopen($spkFile, 'rb');
$header = fread($fp, 512);
fclose($fp);

echo "Premiers 512 bytes (header du premier fichier):\n\n";

// Nom du fichier (0-99)
$filename = substr($header, 0, 100);
echo "Nom (0-99): " . trim($filename) . "\n\n";

// Mode (100-107)
$mode = substr($header, 100, 8);
echo "Mode (100-107): \"" . $mode . "\" (hex: " . bin2hex($mode) . ")\n\n";

// Magic (257-262) - devrait être "ustar\0"
$magic = substr($header, 257, 6);
echo "Magic (257-262): \"" . addcslashes($magic, "\0..\37") . "\" (hex: " . bin2hex($magic) . ")\n";
echo "Attendu: \"ustar\\0\" (hex: 7573746172" . "00)\n";
if (bin2hex($magic) === '75737461720000') {
    echo "✅ Magic correct\n\n";
} else if (bin2hex($magic) === '757374617200') {
    echo "⚠️ Magic = 'ustar\\0' mais il manque peut-être le padding\n\n";
} else {
    echo "❌ Magic INCORRECT\n\n";
}

// Version (263-264) - devrait être "00"
$version = substr($header, 263, 2);
echo "Version (263-264): \"" . $version . "\" (hex: " . bin2hex($version) . ")\n";
echo "Attendu: \"00\" (hex: 3030)\n";
if (bin2hex($version) === '3030') {
    echo "✅ Version correcte\n\n";
} else {
    echo "❌ Version INCORRECTE\n\n";
}

// Checksum (148-155)
$checksum = substr($header, 148, 8);
echo "Checksum (148-155): \"" . trim($checksum) . "\" (hex: " . bin2hex($checksum) . ")\n\n";

// Calculer le checksum réel
$testHeader = $header;
// Remplacer le checksum par des espaces pour le calcul
for ($i = 148; $i < 156; $i++) {
    $testHeader[$i] = ' ';
}
$calculatedChecksum = 0;
for ($i = 0; $i < 512; $i++) {
    $calculatedChecksum += ord($testHeader[$i]);
}

echo "Checksum calculé: $calculatedChecksum\n";
echo "Checksum dans header: " . octdec(trim($checksum)) . "\n";

if ($calculatedChecksum === octdec(trim($checksum))) {
    echo "✅ Checksum correct\n\n";
} else {
    echo "❌ Checksum INCORRECT\n\n";
}

// Type de fichier (156)
$type = $header[156];
echo "Type (156): \"" . ($type === "\0" ? '\\0' : $type) . "\" (hex: " . bin2hex($type) . ")\n";
echo "  0 ou \\0 = fichier normal\n";
echo "  5 = répertoire\n\n";

echo "========================================\n";
echo "DIAGNOSTIC:\n\n";

// Le format ustar complet devrait avoir:
// - offset 257-262: "ustar\0" (6 bytes avec null terminator)
// - offset 263-264: "00" (version)
// - offset 265-296: uname (32 bytes)
// - offset 297-328: gname (32 bytes)

$uname = substr($header, 265, 32);
$gname = substr($header, 297, 32);

echo "Uname (265-296): \"" . trim($uname) . "\" (hex: " . bin2hex(substr($uname, 0, 8)) . "...)\n";
echo "Gname (297-328): \"" . trim($gname) . "\" (hex: " . bin2hex(substr($gname, 0, 8)) . "...)\n\n";

if (trim($uname) === '' && trim($gname) === '') {
    echo "⚠️ ATTENTION: Les champs uname et gname sont vides\n";
    echo "   Certaines implémentations TAR les requièrent.\n";
}
