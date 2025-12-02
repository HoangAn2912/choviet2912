@echo off
echo 🚀 Khởi động Unified Server (Chat + Livestream)...
echo.

REM Kiểm tra Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [Loi] Node.js chua duoc cai dat hoac chua co trong PATH
    echo Vui lòng cài đặt Node.js từ https://nodejs.org/
    pause
    exit /b 1
)

echo [OK] Node.js da san sang
echo.

REM Dừng server cũ nếu đang chạy
echo 🔄 Dừng server cũ...
taskkill /f /im node.exe >nul 2>&1

REM Khởi động server mới
echo 🚀 Khởi động Unified Server...
echo 📡 Server sẽ chạy trên: http://localhost:8080
echo 🎥 Livestream WebSocket: ws://localhost:8080
echo 💬 Chat WebSocket: ws://localhost:8080
echo.

node js/server.js

pause
