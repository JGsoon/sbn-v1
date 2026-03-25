@echo off
echo ========================================
echo Inspection du SPK de test
echo ========================================
echo.

set TEMP_DIR=%TEMP%

echo Recherche du fichier TEST-SBN...
dir /b "%TEMP_DIR%\TEST-SBN-*.spk" > nul 2>&1

if errorlevel 1 (
    echo Aucun fichier TEST-SBN trouve dans %TEMP_DIR%
    pause
    exit /b 1
)

for %%f in ("%TEMP_DIR%\TEST-SBN-*.spk") do (
    echo Fichier trouve: %%f
    echo Taille: %%~zf bytes
    echo.
    echo Contenu de l'archive SPK:
    echo ----------------------------------------
    tar -tf "%%f"
    echo ----------------------------------------
    echo.
    set SPK_FILE=%%f
    goto :found
)

:found
echo.
echo Pour tester l'installation, copiez ce fichier sur votre NAS
echo et installez-le via le Centre de paquets ^> Installation manuelle
echo.
pause
