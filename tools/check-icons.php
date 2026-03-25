<?php
$icon72 = __DIR__ . '/../storage/spk-template/PACKAGE_ICON.PNG';
$icon256 = __DIR__ . '/../storage/spk-template/PACKAGE_ICON_256.PNG';

echo "Vérification des icônes:\n\n";

if (file_exists($icon72)) {
    $info = getimagesize($icon72);
    echo "PACKAGE_ICON.PNG:\n";
    echo "  Largeur: {$info[0]}px\n";
    echo "  Hauteur: {$info[1]}px\n";
    echo "  Type: {$info['mime']}\n";
    echo "  Taille: " . filesize($icon72) . " bytes\n";
    echo "  " . ($info[0] >= 64 && $info[1] >= 64 ? "✅ Taille OK (min 64x64)" : "❌ Trop petit") . "\n\n";
} else {
    echo "❌ PACKAGE_ICON.PNG non trouvé\n\n";
}

if (file_exists($icon256)) {
    $info = getimagesize($icon256);
    echo "PACKAGE_ICON_256.PNG:\n";
    echo "  Largeur: {$info[0]}px\n";
    echo "  Hauteur: {$info[1]}px\n";
    echo "  Type: {$info['mime']}\n";
    echo "  Taille: " . filesize($icon256) . " bytes\n\n";
} else {
    echo "❌ PACKAGE_ICON_256.PNG non trouvé\n\n";
}
