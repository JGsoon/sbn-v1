<?php
/**
 * Lire le contenu d'un fichier TAR
 */

if ($argc < 2) {
    echo "Usage: php read-tar-content.php fichier.spk\n";
    exit(1);
}

$tarFile = $argv[1];

if (!file_exists($tarFile)) {
    echo "Fichier non trouvé: $tarFile\n";
    exit(1);
}

echo "Fichier: $tarFile\n";
echo "Taille: " . number_format(filesize($tarFile)) . " bytes\n\n";

echo "Contenu de l'archive:\n";
echo "----------------------------------------\n";

$fp = fopen($tarFile, 'rb');
$fileList = [];

while (!feof($fp)) {
    $header = fread($fp, 512);
    if (strlen($header) < 512) {
        break;
    }

    // Vérifier si c'est un bloc vide (fin d'archive)
    if (trim($header) === '') {
        break;
    }

    // Extraire le nom du fichier (100 premiers bytes)
    $filename = trim(substr($header, 0, 100));
    if (empty($filename)) {
        break;
    }

    // Extraire la taille du fichier (12 bytes à partir de l'offset 124)
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);

    // Type de fichier (offset 156)
    $type = substr($header, 156, 1);
    $typeStr = ($type === '5') ? 'DIR' : 'FILE';

    $fileList[] = [
        'name' => $filename,
        'size' => $size,
        'type' => $typeStr
    ];

    echo sprintf("%-50s %8s %10s\n", $filename, $typeStr, number_format($size));

    // Passer le contenu du fichier
    if ($size > 0) {
        $blocks = ceil($size / 512);
        fseek($fp, $blocks * 512, SEEK_CUR);
    }
}

fclose($fp);

echo "----------------------------------------\n";
echo "Total: " . count($fileList) . " entrées\n";
