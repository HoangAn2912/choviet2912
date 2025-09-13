@echo off
echo Downloading Composer...
powershell -Command "Invoke-WebRequest -Uri https://getcomposer.org/Composer-Setup.exe -OutFile composer-setup.exe"
echo Installing Composer...
start /wait composer-setup.exe
echo Cleaning up...
del composer-setup.exe
echo Installation completed!
pause

