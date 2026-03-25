<?php
/**
 * Créer un SPK avec les BONNES permissions
 * PharData ne préserve pas chmod(), il faut utiliser une autre méthode
 */

$outputFile = __DIR__ . '/SPK-CORRECT-PERMS-' . date('YmdHis') . '.spk';

echo "========================================\n";
echo "SPK avec permissions correctes\n";
echo "========================================\n\n";

try {
    $tempDir = sys_get_temp_dir() . '/spk-perms-' . uniqid();
    mkdir($tempDir, 0777, true);
    mkdir($tempDir . '/scripts', 0777, true);

    // INFO - Lire depuis le template
    $templateDir = __DIR__ . '/../storage/spk-template';
    $info = file_get_contents($templateDir . '/INFO');
    // Forcer les fins de ligne Unix
    $info = str_replace("\r\n", "\n", str_replace("\r", "\n", $info));
    file_put_contents($tempDir . '/INFO', $info);

    // Installer
    $installer = "#!/bin/bash\n";
    $installer .= "case \$1 in\n";
    $installer .= "    preinst) exit 0 ;;\n";
    $installer .= "    postinst) exit 0 ;;\n";
    $installer .= "    preuninst) exit 0 ;;\n";
    $installer .= "    postuninst) exit 0 ;;\n";
    $installer .= "esac\n";
    $installer .= "exit 0\n";
    file_put_contents($tempDir . '/scripts/installer', $installer);

    // Start-stop
    $startstop = "#!/bin/bash\n";
    $startstop .= "case \$1 in\n";
    $startstop .= "    start) exit 0 ;;\n";
    $startstop .= "    stop) exit 0 ;;\n";
    $startstop .= "    status) exit 0 ;;\n";
    $startstop .= "esac\n";
    $startstop .= "exit 0\n";
    file_put_contents($tempDir . '/scripts/start-stop-status', $startstop);

    // Package.tgz vide
    file_put_contents($tempDir . '/package.tgz', gzencode(""));

    echo "✅ Fichiers créés\n\n";

    // MÉTHODE ALTERNATIVE: Utiliser notre TarCreator avec les bonnes permissions
    require_once __DIR__ . '/../app/Helpers/TarCreator.php';

    $tar = new \App\Helpers\TarCreator();

    echo "Création du SPK avec TarCreator:\n";
    echo "  1. INFO (0644)\n";
    $tar->addFile('INFO', $info, 0644);

    echo "  2. scripts/ (DIR)\n";
    $tar->addDirectory('scripts');

    echo "  3. scripts/installer (0755 - EXÉCUTABLE)\n";
    $tar->addFile('scripts/installer', $installer, 0755);

    echo "  4. scripts/start-stop-status (0755 - EXÉCUTABLE)\n";
    $tar->addFile('scripts/start-stop-status', $startstop, 0755);

    echo "  5. package.tgz (0644)\n";
    $tar->addFile('package.tgz', gzencode(""), 0644);

    $tar->save($outputFile);

    echo "\n✅ SPK créé: $outputFile\n";
    echo "   Taille: " . filesize($outputFile) . " bytes\n\n";

    // Nettoyer
    array_map('unlink', glob($tempDir . '/scripts/*'));
    rmdir($tempDir . '/scripts');
    array_map('unlink', glob($tempDir . '/*'));
    rmdir($tempDir);

    // Vérifier les permissions dans l'archive
    echo "Vérification des permissions:\n";
    echo "========================================\n";

    $fp = fopen($outputFile, 'rb');
    $num = 0;

    while (!feof($fp)) {
        $header = fread($fp, 512);
        if (strlen($header) < 512) break;
        if (trim($header) === '') break;

        $filename = trim(substr($header, 0, 100));
        if (empty($filename)) break;

        $modeOctal = trim(substr($header, 100, 8));
        $modeDec = octdec($modeOctal);
        $sizeOctal = trim(substr($header, 124, 12));
        $size = octdec($sizeOctal);
        $type = substr($header, 156, 1);
        $typeStr = ($type === '5') ? 'DIR' : 'FILE';

        $num++;

        // Formater les permissions en style Unix (rwxr-xr-x)
        $perms = sprintf("%o", $modeDec);
        $isExecutable = ($modeDec & 0111) !== 0;
        $status = $isExecutable ? "✅ EXEC" : "";

        echo sprintf("%d. %-40s %4s  0%s  %s\n",
            $num, $filename, $typeStr, $perms, $status);

        if ($size > 0) {
            $blocks = ceil($size / 512);
            fseek($fp, $blocks * 512, SEEK_CUR);
        }
    }

    fclose($fp);

    echo "========================================\n\n";
    echo "🧪 TESTEZ CE FICHIER SUR VOTRE DS1522+\n";
    echo "   Les scripts ont maintenant les permissions d'exécution (0755)\n\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
