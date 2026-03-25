<?php
$spkFile = __DIR__ . '/SPK-CORRECT-PERMS-20260323151314.spk';
if (!file_exists($spkFile)) {
    $files = glob(__DIR__ . '/SPK-CORRECT-PERMS-*.spk');
    if (!empty($files)) {
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $spkFile = $files[0];
    }
}

echo "Extraction du fichier INFO du SPK:\n";
echo "========================================\n\n";

$fp = fopen($spkFile, 'rb');

// Lire le header du premier fichier (INFO)
$header = fread($fp, 512);

$filename = trim(substr($header, 0, 100));
$sizeOctal = trim(substr($header, 124, 12));
$size = octdec($sizeOctal);

echo "Fichier: $filename\n";
echo "Taille: $size bytes\n\n";

// Lire le contenu
$content = fread($fp, $size);

fclose($fp);

echo "Contenu du fichier INFO:\n";
echo "========================================\n";
echo $content;
echo "\n========================================\n\n";

// Vérifier os_min_ver
if (strpos($content, 'os_min_ver="7.0-40000"') !== false) {
    echo "✅ os_min_ver est correct: 7.0-40000\n";
} else if (strpos($content, 'os_min_ver="6.0-0000"') !== false) {
    echo "❌ os_min_ver est INCORRECT: 6.0-0000 (devrait être 7.0-40000)\n";
} else {
    echo "⚠️ os_min_ver non trouvé\n";
}

if (strpos($content, 'startable="no"') !== false) {
    echo "✅ startable=\"no\" est présent\n";
}
