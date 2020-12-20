%~d0
cd %~dp0
@echo off
REM phpunit must be installed. See more : https://phpunit.de/getting-started/phpunit-5.html
REM bat file can run via CMD, open WIN+R CMD and RUN this file "startTest.bat"
REM Manual for *.phar installations
REM Using *.bat
REM Using CMD
REM https://stackoverflow.com/questions/22297546/how-to-run-phar-from-anywhere-on-windows
REM OPTIONAL: phpunit -c phpunit.xml --bootstrap bootstrap_autoload.php .
@echo on
phpunit --bootstrap bootstrap_autoload.php .