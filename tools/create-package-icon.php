<?php
/**
 * Créer une icône simple pour le package SPK
 */

// Créer une image 72x72 (taille standard Synology)
$icon72 = imagecreatetruecolor(72, 72);

// Couleurs
$blue = imagecolorallocate($icon72, 41, 128, 185);      // Bleu principal
$white = imagecolorallocate($icon72, 255, 255, 255);    // Blanc
$darkBlue = imagecolorallocate($icon72, 31, 97, 141);   // Bleu foncé

// Remplir le fond en bleu
imagefilledrectangle($icon72, 0, 0, 72, 72, $blue);

// Dessiner un cercle blanc au centre (représentant notification)
imagefilledellipse($icon72, 36, 36, 40, 40, $white);
imagefilledellipse($icon72, 36, 36, 34, 34, $blue);

// Dessiner les lettres "SBN"
$font = 5; // Police système grande
imagestring($icon72, $font, 19, 30, "SBN", $white);

// Sauvegarder l'icône 72x72
$outputPath = __DIR__ . '/../storage/spk-template/PACKAGE_ICON.PNG';
imagepng($icon72, $outputPath);
imagedestroy($icon72);

echo "✅ Icône 72x72 créée: $outputPath\n";

// Créer aussi une version 256x256 (optionnelle mais recommandée)
$icon256 = imagecreatetruecolor(256, 256);

// Couleurs pour la grande icône
$blue256 = imagecolorallocate($icon256, 41, 128, 185);
$white256 = imagecolorallocate($icon256, 255, 255, 255);
$darkBlue256 = imagecolorallocate($icon256, 31, 97, 141);

// Remplir le fond
imagefilledrectangle($icon256, 0, 0, 256, 256, $blue256);

// Dessiner un cercle de notification
imagefilledellipse($icon256, 128, 128, 140, 140, $white256);
imagefilledellipse($icon256, 128, 128, 120, 120, $blue256);

// Pour le texte "SBN" on va faire plus grand
// Dessiner "SBN" pixel par pixel en plus grand
$fontSize = 5;
$text = "SBN";

// Utiliser une police plus grande pour la version 256
imagestring($icon256, $fontSize, 95, 118, $text, $white256);

// Ajouter un effet de bordure arrondie
imagerectangle($icon256, 0, 0, 255, 255, $darkBlue256);

// Sauvegarder l'icône 256x256
$outputPath256 = __DIR__ . '/../storage/spk-template/PACKAGE_ICON_256.PNG';
imagepng($icon256, $outputPath256);
imagedestroy($icon256);

echo "✅ Icône 256x256 créée: $outputPath256\n";
echo "\nLes icônes ont été créées avec succès!\n";
