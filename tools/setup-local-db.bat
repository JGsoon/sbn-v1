@echo off
REM Script de configuration rapide de la base de données locale
echo ========================================
echo Configuration Base de Donnees SBN v1.0
echo ========================================
echo.

REM Emplacement de MySQL
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe

echo 1. Creation de la base de donnees...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS sbn_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if %errorlevel% neq 0 (
    echo ERREUR: Impossible de creer la base de donnees
    pause
    exit /b 1
)
echo    [OK] Base de donnees creee

echo.
echo 2. Import du schema principal...
"%MYSQL_PATH%" -u root sbn_dev < ../database/schema.sql
if %errorlevel% neq 0 (
    echo ERREUR: Import schema.sql echoue
    pause
    exit /b 1
)
echo    [OK] Schema importe

echo.
echo 3. Import des migrations...
"%MYSQL_PATH%" -u root sbn_dev < ../database/add_phone_column.sql
"%MYSQL_PATH%" -u root sbn_dev < ../database/add_smtp_config.sql
"%MYSQL_PATH%" -u root sbn_dev < ../database/add_shared_access.sql
"%MYSQL_PATH%" -u root sbn_dev < ../database/fix_api_tokens.sql
"%MYSQL_PATH%" -u root sbn_dev < ../database/add_roles_subscription_sharing.sql
echo    [OK] Migrations importees

echo.
echo ========================================
echo Configuration terminee avec succes!
echo ========================================
echo.
echo Vous pouvez maintenant acceder a:
echo http://localhost/sbn-v1
echo.
pause
