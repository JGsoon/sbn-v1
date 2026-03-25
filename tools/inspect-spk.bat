@echo off
REM Script pour inspecter un fichier SPK
echo ========================================
echo Inspection du fichier SPK
echo ========================================
echo.

if "%~1"=="" (
    echo Usage: inspect-spk.bat chemin_vers_fichier.spk
    echo.
    echo Exemple: inspect-spk.bat C:\Users\Vous\Downloads\SBN-Package.spk
    pause
    exit /b 1
)

set SPK_FILE=%~1

if not exist "%SPK_FILE%" (
    echo ERREUR: Fichier non trouvé: %SPK_FILE%
    pause
    exit /b 1
)

echo Fichier: %SPK_FILE%
echo Taille:
for %%A in ("%SPK_FILE%") do echo %%~zA bytes
echo.

echo Contenu de l'archive SPK:
echo ----------------------------------------
tar -tf "%SPK_FILE%"
echo ----------------------------------------
echo.

echo Extraction du fichier INFO:
echo ----------------------------------------
tar -xOf "%SPK_FILE%" INFO
echo ----------------------------------------
echo.

echo Verification de package.tgz:
echo ----------------------------------------
tar -tf "%SPK_FILE%" | findstr package.tgz
echo ----------------------------------------
echo.

echo Contenu de package.tgz:
echo ----------------------------------------
tar -xOf "%SPK_FILE%" package.tgz | tar -tzf -
echo ----------------------------------------
echo.

pause
