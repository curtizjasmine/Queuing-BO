@echo off
title WebSocket Server - Velez Ventures
color 0A

echo =======================================
echo    STARTING WEBSOCKET SERVER...
echo    (Keep this window OPEN)
echo =======================================

:: 1. Go to your project folder
cd /d E:\newxamp\htdocs\opd\

:: 2. Run the PHP file using the PHP Executable
:: I added "E:\newxamp\php\php.exe" before your file path
E:\newxamp\php\php.exe bin\server.php

:: 3. If it crashes or stops, pause so you can read the error
pause