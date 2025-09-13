#!/bin/bash

# Script khởi động WebSocket server cho hosting
# Sử dụng: chmod +x start_websocket_server.sh && ./start_websocket_server.sh

echo "🚀 Đang khởi động WebSocket Chat Server..."

# Kiểm tra Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js chưa được cài đặt!"
    echo "📥 Cài đặt Node.js:"
    echo "curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -"
    echo "sudo apt-get install -y nodejs"
    exit 1
fi

# Kiểm tra npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm chưa được cài đặt!"
    exit 1
fi

# Cài đặt dependencies nếu chưa có
if [ ! -d "node_modules" ]; then
    echo "📦 Đang cài đặt dependencies..."
    npm install
fi

# Tạo thư mục chat nếu chưa có
if [ ! -d "chat" ]; then
    echo "📁 Tạo thư mục chat..."
    mkdir -p chat
    chmod 755 chat
fi

# Set environment variables cho production
export NODE_ENV=production
export HOSTING=true

# Lấy thông tin domain từ environment hoặc sử dụng giá trị mặc định
export HOSTNAME=${HOSTNAME:-"your-domain.com"}
export PORT=${PORT:-80}
export WS_PORT=${WS_PORT:-3000}
export BASE_PATH=${BASE_PATH:-""}
export PROJECT_ROOT=${PROJECT_ROOT:-$(pwd)}
export CHAT_PATH=${CHAT_PATH:-"$(pwd)/chat"}

echo "🔧 Cấu hình:"
echo "   Hostname: $HOSTNAME"
echo "   Port: $PORT"
echo "   WebSocket Port: $WS_PORT"
echo "   Base Path: $BASE_PATH"
echo "   Project Root: $PROJECT_ROOT"
echo "   Chat Path: $CHAT_PATH"

# Kiểm tra PM2
if command -v pm2 &> /dev/null; then
    echo "🔄 Sử dụng PM2 để quản lý process..."
    
    # Dừng process cũ nếu có
    pm2 stop websocket-chat 2>/dev/null || true
    pm2 delete websocket-chat 2>/dev/null || true
    
    # Khởi động với PM2
    pm2 start js/server.js --name "websocket-chat" --env production
    pm2 save
    
    echo "✅ WebSocket server đã khởi động với PM2!"
    echo "📊 Xem logs: pm2 logs websocket-chat"
    echo "🔄 Restart: pm2 restart websocket-chat"
    echo "⏹️ Stop: pm2 stop websocket-chat"
else
    echo "⚠️ PM2 chưa được cài đặt. Chạy trực tiếp..."
    echo "💡 Cài đặt PM2: npm install -g pm2"
    
    # Chạy trực tiếp
    node js/server.js
fi


