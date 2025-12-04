#!/bin/bash

# Script cập nhật và deploy code tự động
# Sử dụng: ./deploy.sh

echo "🚀 Bắt đầu quá trình deploy..."
# 2. Sync files sang thư mục web
echo "📂 Đang đồng bộ file sang /var/www/choviet.site..."
rsync -av --exclude 'node_modules' --exclude 'vendor' --exclude '.git' --exclude 'deploy.sh' /root/deployweb/choviet2912/ /var/www/choviet.site/

# 3. Cập nhật quyền (đề phòng file mới)
echo "🔒 Cập nhật quyền truy cập..."
chown -R www-data:www-data /var/www/choviet.site
chmod -R 755 /var/www/choviet.site
chmod -R 777 /var/www/choviet.site/img /var/www/choviet.site/chat /var/www/choviet.site/logs

# 4. Cài đặt dependencies nếu có thay đổi
echo "📦 Kiểm tra dependencies..."
cd /var/www/choviet.site
# Composer
if [ -f "composer.json" ]; then
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader
fi
# NPM
if [ -f "package.json" ]; then
    npm install --production
fi

# 5. Restart Node.js server
echo "🔄 Restarting Node.js server..."
pm2 restart choviet-server

echo "✅ Deploy hoàn tất! Website đã được cập nhật."
