<?php
/**
 * SPK ultra minimal - juste INFO et scripts
 * Pas de package.tgz, pas d'icônes
 */

$outputFile = __DIR__ . '/ULTRA-MINIMAL-' . date('YmdHis') . '.spk';

echo "========================================\n";
echo "Création d'un SPK ULTRA minimal\n";
echo "========================================\n\n";

try {
    $tempDir = sys_get_temp_dir() . '/ultra-minimal-' . uniqid();
    mkdir($tempDir, 0777, true);
    mkdir($tempDir . '/scripts', 0777, true);

    // INFO minimal
    $info = <<<'EOF'
package="TestMinimal"
version="1.0.0"
displayname="Test Minimal"
description="Package de test ultra minimal"
maintainer="Test"
arch="noarch"
os_min_ver="6.0-0000"
EOF;
    file_put_contents($tempDir . '/INFO', $info);

    // Script installer minimal
    $installer = <<<'EOF'
#!/bin/bash
case $1 in
    preinst)
        exit 0
        ;;
    postinst)
        exit 0
        ;;
    preuninst)
        exit 0
        ;;
    postuninst)
        exit 0
        ;;
esac
exit 0
EOF;
    file_put_contents($tempDir . '/scripts/installer', $installer);
    chmod($tempDir . '/scripts/installer', 0755);

    // Script start-stop-status minimal
    $startstop = <<<'EOF'
#!/bin/bash
case $1 in
    start)
        exit 0
        ;;
    stop)
        exit 0
        ;;
    status)
        exit 0
        ;;
esac
exit 0
EOF;
    file_put_contents($tempDir . '/scripts/start-stop-status', $startstop);
    chmod($tempDir . '/scripts/start-stop-status', 0755);

    // Créer un package.tgz vide (juste pour la forme)
    file_put_contents($tempDir . '/package.tgz', gzencode(""));

    echo "✅ Fichiers créés\n\n";

    // Créer le SPK avec PharData EN AJOUTANT LE DOSSIER SCRIPTS EXPLICITEMENT
    $spk = new PharData($outputFile);

    echo "Ajout des fichiers dans l'ordre:\n";

    echo "  1. INFO\n";
    $spk->addFile($tempDir . '/INFO', 'INFO');

    echo "  2. scripts/ (dossier)\n";
    // Ajouter explicitement le dossier scripts
    $spk->addEmptyDir('scripts');

    echo "  3. scripts/installer\n";
    $spk->addFile($tempDir . '/scripts/installer', 'scripts/installer');

    echo "  4. scripts/start-stop-status\n";
    $spk->addFile($tempDir . '/scripts/start-stop-status', 'scripts/start-stop-status');

    echo "  5. package.tgz\n";
    $spk->addFile($tempDir . '/package.tgz', 'package.tgz');

    unset($spk);

    echo "\n✅ SPK créé: $outputFile\n";
    echo "   Taille: " . filesize($outputFile) . " bytes\n\n";

    // Nettoyer
    array_map('unlink', glob($tempDir . '/scripts/*'));
    rmdir($tempDir . '/scripts');
    array_map('unlink', glob($tempDir . '/*'));
    rmdir($tempDir);

    // Analyser la structure
    echo "Structure du SPK:\n";
    echo "========================================\n";

    $fp = fopen($outputFile, 'rb');
    $num = 0;

    while (!feof($fp)) {
        $header = fread($fp, 512);
        if (strlen($header) < 512) break;
        if (trim($header) === '') break;

        $filename = trim(substr($header, 0, 100));
        if (empty($filename)) break;

        $sizeOctal = trim(substr($header, 124, 12));
        $size = octdec($sizeOctal);
        $type = substr($header, 156, 1);
        $typeStr = ($type === '5') ? 'DIR' : 'FILE';
        $mode = substr($header, 100, 8);

        $num++;
        echo sprintf("%d. %-40s %4s %8s bytes  mode=%s\n",
            $num, $filename, $typeStr, number_format($size), trim($mode));

        if ($size > 0) {
            $blocks = ceil($size / 512);
            fseek($fp, $blocks * 512, SEEK_CUR);
        }
    }

    fclose($fp);

    echo "========================================\n\n";
    echo "🧪 TESTEZ CE FICHIER SUR VOTRE NAS\n\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
