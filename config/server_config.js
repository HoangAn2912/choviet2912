// ========================================
// CẤU HÌNH MÔI TRƯỜNG CHO NODE.JS SERVER
// ========================================
// 🔴 Đổi NODE_ENV để chuyển môi trường:
// - development (local)
// - production (hosting)

const ENV = process.env.NODE_ENV || 'development';

const config = {
  development: {
    // LOCAL (XAMPP)
    hostname: 'localhost',
    port: 8080,
    basePath: '',
    wsPort: 3000,
    wsSecret: '',
    projectRoot: 'D:\\xampp\\htdocs',
    chatPath: 'D:\\xampp\\htdocs\\chat'
  },
  
  production: {
    // HOSTING - ⚠️ Cập nhật khi deploy
    hostname: 'yourdomain.com',
    port: 8080,
    basePath: '',
    wsPort: 3000,
    wsSecret: '',  // Nên thêm secret cho production
    projectRoot: '/home/username/public_html',
    chatPath: '/home/username/public_html/chat'
  }
};

module.exports = config[ENV] || config.development;



