@echo off
cd /d %~dp0

:: Nettoyage
if exist package.tgz del package.tgz
if exist *.spk del *.spk

:: Créer package.tgz (vide, juste les scripts)
cd scripts
tar -czf ..\package.tgz start-stop-status
cd ..

:: Créer le SPK
tar -cf SynoBackupNotifier.spk INFO PACKAGE_ICON.PNG package.tgz scripts\start-stop-status

echo.
echo SPK cree : SynoBackupNotifier.spk
pause
