// Cấu hình môi trường cho Node.js WebSocket Server
// File cấu hình thực tế cho dự án chạy ở thư mục gốc htdocs

module.exports = {
  // Hostname cho web server
  hostname: 'localhost',
  
  // Port cho web server  
  port: 8080,
  
  // Base path cho project ở thư mục gốc (để rỗng)
  basePath: '',
  
  // Port cho WebSocket server
  wsPort: 3000,
  
  // Secret để ký HMAC token cho WebSocket (để trống để tắt xác thực)
  wsSecret: '',
  
  // Đường dẫn tuyệt đối đến thư mục project ở gốc
  projectRoot: 'D:\\xampp\\htdocs',
  
  // Đường dẫn đến thư mục chat ở gốc
  chatPath: 'D:\\xampp\\htdocs\\chat'
};



