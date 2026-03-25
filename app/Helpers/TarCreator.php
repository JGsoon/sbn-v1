<?php
/**
 * Créateur de fichiers TAR manuel pour compatibilité Synology
 * Basé sur le format POSIX tar ustar
 */

namespace App\Helpers;

class TarCreator {
    private $archive = '';

    /**
     * Ajouter un fichier à l'archive
     */
    public function addFile($filename, $content, $mode = 0644) {
        $this->archive .= $this->createTarHeader($filename, strlen($content), $mode, 0); // 0 = fichier normal
        $this->archive .= $content;

        // Padding pour aligner sur 512 bytes
        $remainder = strlen($content) % 512;
        if ($remainder > 0) {
            $this->archive .= str_repeat("\0", 512 - $remainder);
        }
    }

    /**
     * Ajouter un dossier à l'archive
     */
    public function addDirectory($dirname) {
        // S'assurer que le nom se termine par /
        if (substr($dirname, -1) !== '/') {
            $dirname .= '/';
        }

        $this->archive .= $this->createTarHeader($dirname, 0, 0755, 5); // 5 = répertoire
    }

    /**
     * Créer un header tar au format ustar
     */
    private function createTarHeader($filename, $filesize, $mode, $type) {
        // Initialiser avec des zéros
        $header = str_repeat("\0", 512);

        // Nom du fichier (100 bytes)
        $header = $this->writeString($header, 0, $filename, 100);

        // Mode fichier (8 bytes) - en octal
        $header = $this->writeOctal($header, 100, $mode, 8);

        // UID (8 bytes) - propriétaire
        $header = $this->writeOctal($header, 108, 0, 8);

        // GID (8 bytes) - groupe
        $header = $this->writeOctal($header, 116, 0, 8);

        // Taille du fichier (12 bytes) - en octal
        $header = $this->writeOctal($header, 124, $filesize, 12);

        // Temps de modification (12 bytes) - en octal
        $header = $this->writeOctal($header, 136, time(), 12);

        // Checksum (8 bytes) - calculé plus tard
        $header = $this->writeString($header, 148, '        ', 8);

        // Type de fichier (1 byte)
        // 0 ou \0 = fichier normal
        // 5 = répertoire
        $header = $this->writeString($header, 156, (string)$type, 1);

        // Format ustar (6 bytes)
        $header = $this->writeString($header, 257, 'ustar', 6);

        // Version ustar (2 bytes)
        $header = $this->writeString($header, 263, '00', 2);

        // Uname - nom du propriétaire (32 bytes)
        $header = $this->writeString($header, 265, 'root', 32);

        // Gname - nom du groupe (32 bytes)
        $header = $this->writeString($header, 297, 'root', 32);

        // Calculer et écrire le checksum
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord($header[$i]);
        }
        $header = $this->writeOctal($header, 148, $checksum, 7);
        // Null terminator pour checksum (position 155)
        $header = substr_replace($header, "\0", 155, 1);

        return $header;
    }

    /**
     * Écrire une chaîne dans le buffer à une position donnée
     */
    private function writeString($buffer, $offset, $string, $length) {
        for ($i = 0; $i < $length && $i < strlen($string); $i++) {
            $buffer[$offset + $i] = $string[$i];
        }
        return $buffer;
    }

    /**
     * Écrire un nombre en octal dans le buffer
     */
    private function writeOctal($buffer, $offset, $value, $length) {
        $octal = sprintf('%0' . ($length - 1) . 'o', $value);
        return $this->writeString($buffer, $offset, $octal, $length - 1);
    }

    /**
     * Finaliser l'archive (ajouter deux blocs de zéros à la fin)
     */
    public function finalize() {
        $this->archive .= str_repeat("\0", 1024);
    }

    /**
     * Obtenir le contenu de l'archive
     */
    public function getArchive() {
        return $this->archive;
    }

    /**
     * Sauvegarder l'archive dans un fichier
     */
    public function save($filename) {
        $this->finalize();
        return file_put_contents($filename, $this->archive);
    }
}
