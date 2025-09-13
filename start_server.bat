@echo off
echo ========================================
echo    KHOI DONG SERVER CHO CHO VIET 29
echo ========================================

REM Chuyển đến thư mục dự án
cd /d "D:\xampp\htdocs\choviet29"

REM Hiển thị thư mục hiện tại
echo Thư mục hiện tại: %CD%

REM Kiểm tra xem thư mục chat có tồn tại không
if not exist "chat" (
    echo Tao thu muc chat...
    mkdir chat
)

REM Khởi động server Node.js
echo Khoi dong server Node.js...
echo Luu y: Server se luu file JSON tai: %CD%\chat\
node js/server.js

pause
