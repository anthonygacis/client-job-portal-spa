@echo on
set "TOKEN=get from the owner"
set "USERNAME=anthonygacis"
set "REPO=client-payroll-system-spa"
git pull https://anthonygacis:%TOKEN%@github.com/%USERNAME%/%REPO%.git main
rem set PHP_LOCATION=C:\xampp\php\php.exe
rem %PHP_LOCATION% artisan migrate
pause