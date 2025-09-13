@echo off
REM Script khởi động WebSocket server cho Windows hosting
REM Sử dụng: start_websocket_server.bat

echo 🚀 Đang khởi động WebSocket Chat Server...

REM Kiểm tra Node.js
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js chưa được cài đặt!
    echo 📥 Tải và cài đặt Node.js từ: https://nodejs.org/
    pause
    exit /b 1
)

REM Kiểm tra npm
npm --version >nul 2>&1
if errorlevel 1 (
    echo ❌ npm chưa được cài đặt!
    pause
    exit /b 1
)

REM Cài đặt dependencies nếu chưa có
if not exist "node_modules" (
    echo 📦 Đang cài đặt dependencies...
    npm install
)

REM Tạo thư mục chat nếu chưa có
if not exist "chat" (
    echo 📁 Tạo thư mục chat...
    mkdir chat
)

REM Set environment variables cho production
set NODE_ENV=production
set HOSTING=true

REM Lấy thông tin domain từ environment hoặc sử dụng giá trị mặc định
if "%HOSTNAME%"=="" set HOSTNAME=your-domain.com
if "%PORT%"=="" set PORT=80
if "%WS_PORT%"=="" set WS_PORT=3000
if "%BASE_PATH%"=="" set BASE_PATH=
if "%PROJECT_ROOT%"=="" set PROJECT_ROOT=%CD%
if "%CHAT_PATH%"=="" set CHAT_PATH=%CD%\chat

echo 🔧 Cấu hình:
echo    Hostname: %HOSTNAME%
echo    Port: %PORT%
echo    WebSocket Port: %WS_PORT%
echo    Base Path: %BASE_PATH%
echo    Project Root: %PROJECT_ROOT%
echo    Chat Path: %CHAT_PATH%

REM Kiểm tra PM2
pm2 --version >nul 2>&1
if not errorlevel 1 (
    echo 🔄 Sử dụng PM2 để quản lý process...
    
    REM Dừng process cũ nếu có
    pm2 stop websocket-chat 2>nul
    pm2 delete websocket-chat 2>nul
    
    REM Khởi động với PM2
    pm2 start js/server.js --name "websocket-chat" --env production
    pm2 save
    
    echo ✅ WebSocket server đã khởi động với PM2!
    echo 📊 Xem logs: pm2 logs websocket-chat
    echo 🔄 Restart: pm2 restart websocket-chat
    echo ⏹️ Stop: pm2 stop websocket-chat
    pause
) else (
    echo ⚠️ PM2 chưa được cài đặt. Chạy trực tiếp...
    echo 💡 Cài đặt PM2: npm install -g pm2
    
    REM Chạy trực tiếp
    node js/server.js
    pause
)


