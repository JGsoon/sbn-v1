<?php
/**
 * Convertir tous les fichiers template en fins de ligne Unix (LF)
 */

$files = [
    __DIR__ . '/../storage/spk-template/INFO',
    __DIR__ . '/../storage/spk-template/scripts/installer',
    __DIR__ . '/../storage/spk-template/scripts/start-stop-status',
    __DIR__ . '/../storage/spk-template/package/webhook.sh',
    __DIR__ . '/../storage/spk-template/package/config.sh.template',
    __DIR__ . '/../storage/spk-template/package/README.md',
];

echo "========================================\n";
echo "Conversion des fins de ligne\n";
echo "========================================\n\n";

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "❌ Fichier non trouvé: " . basename($file) . "\n";
        continue;
    }

    $content = file_get_contents($file);
    $originalSize = strlen($content);

    // Détecter le type de fin de ligne
    $hasWindows = strpos($content, "\r\n") !== false;
    $hasMac = strpos($content, "\r") !== false && !$hasWindows;

    // Convertir en Unix (LF)
    $content = str_replace("\r\n", "\n", $content);
    $content = str_replace("\r", "\n", $content);

    $newSize = strlen($content);
    file_put_contents($file, $content);

    $status = $hasWindows ? "Windows → Unix" : ($hasMac ? "Mac → Unix" : "déjà Unix");
    $diff = $originalSize - $newSize;

    echo sprintf("✅ %-30s %s (%d → %d bytes, -%d)\n",
        basename($file),
        $status,
        $originalSize,
        $newSize,
        $diff
    );
}

echo "\n✅ Tous les fichiers ont été convertis en fins de ligne Unix (LF)\n";
echo "   Redémarrez Apache et téléchargez un nouveau SPK.\n";
