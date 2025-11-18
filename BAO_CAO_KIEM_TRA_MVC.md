# BÁO CÁO KIỂM TRA TUÂN THỦ MÔ HÌNH MVC

## 📋 TỔNG QUAN

Dự án **Chợ Việt** có cấu trúc thư mục theo mô hình MVC (Model-View-Controller) nhưng **KHÔNG TUÂN THỦ ĐẦY ĐỦ** các nguyên tắc của mô hình này.

---

## ❌ CÁC VI PHẠM NGHIÊM TRỌNG

### 1. **VIEW TRUY VẤN DATABASE TRỰC TIẾP**

#### 🔴 Vi phạm trong `view/header.php` (dòng 28-51)
```php
// ❌ SAI: View truy vấn database trực tiếp
require_once __DIR__ . '/../model/mConnect.php';
$headerConn = new Connect();
$headerDb = $headerConn->connect();
$header_sql = "SELECT account_type FROM users WHERE id = ?";
$header_stmt = $headerDb->prepare($header_sql);
// ... query trực tiếp
```

**Vấn đề:**
- View không được phép truy cập database trực tiếp
- Logic nghiệp vụ nằm trong View
- Vi phạm nguyên tắc Separation of Concerns

**Giải pháp:**
- Di chuyển logic này vào Controller hoặc Model
- Controller gọi Model để lấy `account_type`
- Truyền dữ liệu vào View qua biến

---

#### 🔴 Vi phạm trong `view/index.php` (dòng 14-36)
```php
// ❌ SAI: View có function truy vấn database
include_once("model/mConnect.php");
$con = new connect();
$mysqli = $con->connect();

function getBanners() {
    global $mysqli;
    $sql = "SELECT * FROM banners WHERE status = 'active' ORDER BY display_order ASC";
    if ($result = $mysqli->query($sql)) {
        // ... query trực tiếp
    }
}
$banners = getBanners();
```

**Vấn đề:**
- View tự tạo connection và query
- Function nghiệp vụ nằm trong View

**Giải pháp:**
- Tạo Model `mBanner.php` với method `getActiveBanners()`
- Controller gọi Model và truyền `$banners` vào View

---

### 2. **INDEX.PHP CHỨA QUÁ NHIỀU LOGIC ROUTING VÀ BUSINESS LOGIC**

#### 🔴 Vi phạm trong `index.php` (dòng 66-334)
```php
// ❌ SAI: Routing logic và business logic lẫn lộn
if (isset($_GET['action']) && $_GET['action'] == 'capNhatTrangThai') {
    include_once("controller/cPost.php");
    $ctrl = new cPost();
    $ctrl->capNhatTrangThaiBan();
    exit;
}
// ... hàng trăm dòng if-else
```

**Vấn đề:**
- File `index.php` quá lớn (334 dòng)
- Routing logic và business logic không tách biệt
- Khó bảo trì và mở rộng
- Nhiều đoạn code lặp lại (kiểm tra account_type ở nhiều nơi)

**Giải pháp:**
- Tạo Router class riêng
- Hoặc sử dụng Front Controller pattern
- Tách routing logic ra file riêng

---

#### 🔴 Vi phạm: Kiểm tra quyền trực tiếp trong `index.php`
```php
// ❌ SAI: Business logic trong entry point
require_once("model/mConnect.php");
$conn = new Connect();
$db = $conn->connect();
$check_sql = "SELECT account_type FROM users WHERE id = ?";
// ... query để kiểm tra quyền
```

**Vấn đề:**
- Logic kiểm tra quyền lặp lại nhiều lần
- Không có middleware/authorization layer

**Giải pháp:**
- Tạo Authorization middleware
- Controller xử lý authorization
- Hoặc tạo helper function `checkBusinessAccount()`

---

### 3. **VIEW GỌI MODEL TRỰC TIẾP**

#### 🔴 Vi phạm trong `index.php` (dòng 164-166)
```php
// ❌ SAI: Entry point gọi Model trực tiếp
include_once("model/mProfile.php");
$profileModel = new mProfile();
$userId = $profileModel->getUserByUsername($_GET['username']);
```

**Vấn đề:**
- Entry point không nên gọi Model trực tiếp
- Nên đi qua Controller

**Giải pháp:**
- Gọi Controller thay vì Model
- Controller sẽ gọi Model

---

### 4. **CONTROLLER CÓ LOGIC NGHIỆP VỤ PHỨC TẠP**

#### ⚠️ Vi phạm trong `controller/cCategory.php` (dòng 58-83)
```php
// ⚠️ Controller có logic xử lý API trực tiếp
if (isset($_GET['action'])) {
    $controller = new cCategory();
    switch ($_GET['action']) {
        case 'getProductsByCategory':
            $controller->getProductsByCategory();
            break;
        // ... xử lý trực tiếp trong Controller file
    }
}
```

**Vấn đề:**
- Controller file có code xử lý request ở cuối file
- Nên tách ra thành method riêng hoặc route riêng

---

### 5. **THIẾU ROUTER VÀ FRONT CONTROLLER**

**Vấn đề:**
- Không có Router class riêng
- Tất cả routing logic nằm trong `index.php`
- Khó quản lý routes
- Khó thêm middleware

**Giải pháp:**
- Tạo `Router.php` class
- Hoặc sử dụng routing library (ví dụ: FastRoute)

---

## ✅ ĐIỂM TỐT

1. **Cấu trúc thư mục đúng:**
   - Có thư mục `controller/`, `model/`, `view/`
   - File naming convention rõ ràng (cCategory, mCategory)

2. **Một số Controller tuân thủ MVC:**
   - `controller/cPost.php` - gọi Model đúng cách
   - `controller/cCategory.php` - có sử dụng Model

3. **Model layer tương đối tốt:**
   - Model có class riêng
   - Có sử dụng prepared statements

---

## 📊 ĐÁNH GIÁ TỔNG THỂ

| Tiêu chí | Điểm | Ghi chú |
|----------|------|---------|
| Cấu trúc thư mục | 8/10 | Đúng cấu trúc MVC |
| Separation of Concerns | 4/10 | View có logic nghiệp vụ |
| Controller pattern | 5/10 | Controller có nhưng routing lộn xộn |
| Model pattern | 7/10 | Model tương đối tốt |
| Routing | 3/10 | Không có Router riêng |
| **TỔNG ĐIỂM** | **5.4/10** | **CẦN CẢI THIỆN** |

---

## 🔧 ĐỀ XUẤT SỬA CHỮA

### Ưu tiên CAO:

1. **Di chuyển database queries từ View sang Model/Controller**
   - `view/header.php`: Tạo method trong Model để lấy account_type
   - `view/index.php`: Tạo Model `mBanner.php`

2. **Tạo Router class**
   - Tách routing logic từ `index.php`
   - Tạo file `core/Router.php`

3. **Tạo Authorization helper**
   - Tạo `helpers/Authorization.php`
   - Tránh lặp lại code kiểm tra quyền

### Ưu tiên TRUNG BÌNH:

4. **Refactor index.php**
   - Giảm số dòng code
   - Tách logic thành các method

5. **Tạo Front Controller**
   - Xử lý tất cả requests qua một điểm vào

### Ưu tiên THẤP:

6. **Cải thiện error handling**
7. **Thêm logging system**
8. **Tạo base Controller class**

---

## 📝 KẾT LUẬN

Dự án **KHÔNG TUÂN THỦ ĐẦY ĐỦ** mô hình MVC. Các vi phạm chính:

1. ❌ View truy vấn database trực tiếp
2. ❌ Entry point chứa quá nhiều logic
3. ❌ Thiếu Router và Front Controller
4. ❌ Logic nghiệp vụ lặp lại nhiều nơi

**Đánh giá:** ⚠️ **CẦN REFACTOR** để tuân thủ đúng mô hình MVC.

**Khuyến nghị:** Ưu tiên sửa các vi phạm nghiêm trọng (View truy vấn DB) trước, sau đó cải thiện routing và architecture.

















