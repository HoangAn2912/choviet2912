# Hướng dẫn Cập nhật và Deploy Code

Tài liệu này hướng dẫn cách lấy code mới nhất từ GitHub và cập nhật lên server `choviet.site`.

## Thông tin Truy cập Server (SSH)

Sử dụng thông tin sau để truy cập vào server:

- **IP**: `103.90.226.19`
- **Username**: `root`
- **Password**: `enaGzxJ6KJmLRBh5DN1J`
- **Port**: `22`

### Cách kết nối

Mở terminal (hoặc PowerShell trên Windows) và chạy lệnh:

```bash
ssh root@103.90.226.19
```

Nhập mật khẩu khi được hỏi.

### Kết nối bằng VSCode (Khuyên dùng)

Để sửa code trực tiếp trên server dễ dàng hơn:

1. Cài đặt Extension **Remote - SSH** trong VSCode.
2. Click vào biểu tượng **><** màu xanh ở góc dưới cùng bên trái VSCode.
3. Chọn **Connect to Host...** -> **Add New SSH Host...**
4. Nhập lệnh: `ssh root@103.90.226.19` và nhấn Enter.
5. Chọn file config để lưu (thường là cái đầu tiên).
6. Click **Connect** (hoặc lặp lại bước 2 và chọn IP vừa thêm).
7. Chọn **Linux** -> **Continue**.
8. Nhập mật khẩu: `enaGzxJ6KJmLRBh5DN1J`
9. Sau khi kết nối, vào menu **File** -> **Open Folder** -> Nhập `/var/www/choviet.site` để mở code web.

---

## Quy trình Cập nhật

Đã tạo sẵn một script tự động (`deploy.sh`) để đơn giản hóa quá trình này. Bạn chỉ cần thực hiện 1 lệnh duy nhất.

### Cách thực hiện

1. **Mở Terminal** trên server (hoặc SSH vào server).
2. **Di chuyển vào thư mục source code**:
   ```bash
   cd /root/deployweb/choviet2912
   ```
3. **Chạy script deploy**:
   ```bash
   ./deploy.sh
   ```

### Script sẽ tự động 

1. `git pull`: Lấy code mới nhất từ nhánh `main` trên GitHub.
2. `rsync`: Copy các file thay đổi sang thư mục web `/var/www/choviet.site`.
3. `chmod/chown`: Cập nhật lại quyền truy cập file để đảm bảo web server đọc được.
4. `composer/npm`: Cài đặt thêm thư viện mới nếu có.
5. `pm2 restart`: Khởi động lại Node.js server để áp dụng thay đổi cho Chat/Livestream.

---

## Xử lý sự cố

Nếu gặp lỗi "Permission denied" khi chạy script:
```bash
chmod +x ./deploy.sh
```

Nếu muốn cập nhật thủ công (không dùng script):
```bash
# 1. Pull code
git pull origin main

# 2. Copy sang thư mục web
rsync -av --exclude 'node_modules' --exclude 'vendor' ./ /var/www/choviet.site/

# 3. Restart server chat (nếu sửa file js/server.js)
pm2 restart choviet-server
```
