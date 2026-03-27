@echo off
cd /d %~dp0

del /q ..\SBNBackupNotifier.spk 2>nul
del /q package.tgz 2>nul

cd target
tar czf ..\package.tgz *
cd ..

tar cf ..\SBNBackupNotifier.spk INFO PACKAGE_ICON.PNG PACKAGE_ICON_256.PNG package.tgz scripts conf

del /q package.tgz

echo Build termine: SBNBackupNotifier.spk
