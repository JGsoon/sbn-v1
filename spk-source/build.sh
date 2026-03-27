#!/bin/bash

# Nettoyage
rm -f ../SBNBackupNotifier.spk

# Creer package.tgz (contenu de target/)
cd target
tar czf ../package.tgz *
cd ..

# Creer le SPK
tar cf ../SBNBackupNotifier.spk \
    INFO \
    PACKAGE_ICON.PNG \
    PACKAGE_ICON_256.PNG \
    package.tgz \
    scripts \
    conf \
    WIZARD_UIFILES

# Nettoyage
rm -f package.tgz

echo "Build terminé: SBNBackupNotifier.spk"
