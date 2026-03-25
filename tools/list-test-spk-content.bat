@echo off
for %%f in ("%TEMP%\TEST-SBN-*.spk") do (
    echo Fichier: %%f
    echo Taille: %%~zf bytes
    echo.
    echo Contenu:
    tar -tf "%%f"
)
