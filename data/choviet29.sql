-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 11, 2025 lúc 09:43 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `choviet29`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream`
--

CREATE TABLE `livestream` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'chua_bat_dau' COMMENT 'chua_bat_dau, dang_dien_ra, da_ket_thuc',
  `image` varchar(255) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream`
--

INSERT INTO `livestream` (`id`, `user_id`, `title`, `description`, `start_time`, `end_time`, `status`, `image`, `created_date`) VALUES
(1, 2, 'Livestream bán điện thoại', 'Livestream bán các loại điện thoại giá rẻ', '2025-01-05 20:00:00', '2025-01-05 22:00:00', 'chua_bat_dau', 'livestream1.jpg', '2025-01-01 10:00:00'),
(2, 3, 'Livestream laptop gaming', 'Livestream giới thiệu laptop gaming mới nhất', '2025-01-06 19:00:00', '2025-01-06 21:00:00', 'chua_bat_dau', 'livestream2.jpg', '2025-01-02 14:30:00'),
(3, 1, 'Livestream admin', 'Livestream hướng dẫn sử dụng website', '2025-01-07 18:00:00', '2025-01-07 20:00:00', 'chua_bat_dau', 'livestream3.jpg', '2025-01-03 09:15:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_cart`
--

CREATE TABLE `livestream_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_cart`
--

INSERT INTO `livestream_cart` (`id`, `user_id`, `product_id`, `livestream_id`, `quantity`, `created_date`) VALUES
(1, 2, 1, 1, 1, '2025-01-01 20:30:00'),
(2, 3, 2, 2, 1, '2025-01-02 19:15:00'),
(3, 1, 3, 1, 2, '2025-01-03 20:45:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_messages`
--

CREATE TABLE `livestream_messages` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_messages`
--

INSERT INTO `livestream_messages` (`id`, `livestream_id`, `user_id`, `content`, `created_time`) VALUES
(1, 1, 2, 'Chào mọi người!', '2025-01-01 20:00:00'),
(2, 1, 3, 'Sản phẩm này giá bao nhiêu?', '2025-01-01 20:05:00'),
(3, 2, 3, 'Laptop này có card đồ họa gì?', '2025-01-02 19:10:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_packages`
--

CREATE TABLE `livestream_packages` (
  `id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL COMMENT 'Thời hạn tính bằng ngày',
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_packages`
--

INSERT INTO `livestream_packages` (`id`, `package_name`, `description`, `price`, `duration_days`, `status`) VALUES
(1, 'Gói cơ bản', 'Gói livestream cơ bản 30 ngày', 500000.00, 30, 1),
(2, 'Gói nâng cao', 'Gói livestream nâng cao 60 ngày', 800000.00, 60, 1),
(3, 'Gói VIP', 'Gói livestream VIP 90 ngày', 1200000.00, 90, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_payment_history`
--

CREATE TABLE `livestream_payment_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_payment_history`
--

INSERT INTO `livestream_payment_history` (`id`, `user_id`, `package_id`, `amount`, `payment_date`) VALUES
(1, 2, 1, 500000.00, '2025-01-01 10:00:00'),
(2, 3, 2, 800000.00, '2025-01-02 14:30:00'),
(3, 1, 3, 1200000.00, '2025-01-03 09:15:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_products`
--

CREATE TABLE `livestream_products` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_sequence` int(11) NOT NULL DEFAULT 0,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_products`
--

INSERT INTO `livestream_products` (`id`, `livestream_id`, `product_id`, `order_sequence`, `created_date`) VALUES
(1, 1, 1, 1, '2025-01-01 10:00:00'),
(2, 2, 2, 1, '2025-01-02 14:30:00'),
(3, 1, 3, 2, '2025-01-03 09:15:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_registrations`
--

CREATE TABLE `livestream_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
  `expiry_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active, expired, cancelled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_registrations`
--

INSERT INTO `livestream_registrations` (`id`, `user_id`, `package_id`, `registration_date`, `expiry_date`, `status`) VALUES
(1, 2, 1, '2025-01-01 10:00:00', '2025-01-31 10:00:00', 'active'),
(2, 3, 2, '2025-01-02 14:30:00', '2025-03-03 14:30:00', 'active'),
(3, 1, 3, '2025-01-03 09:15:00', '2025-04-03 09:15:00', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_date` date DEFAULT curdate(),
  `created_time` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `product_id`, `content`, `created_date`, `created_time`, `is_read`) VALUES
(1, 2, 3, 1, 'Sản phẩm này còn không bạn?', '2025-01-01', '2025-01-01 15:30:00', 0),
(2, 3, 2, 1, 'Còn bạn ơi, bạn có muốn xem hàng không?', '2025-01-01', '2025-01-01 15:35:00', 1),
(3, 2, 3, 2, 'Laptop này có thể giảm giá không?', '2025-01-02', '2025-01-02 10:20:00', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `otp_verification`
--

INSERT INTO `otp_verification` (`id`, `email`, `phone`, `otp`, `created_at`, `expires_at`, `verified`) VALUES
(1, 'user1@test.com', '0987654321', '123456', '2025-01-01 03:00:00', '2025-01-01 03:05:00', 1),
(2, 'hoangan2711.npha@gmail.com', NULL, '752349', '2025-09-04 21:05:32', '2025-09-04 16:15:32', 1),
(3, 'hoangan2912.npha@gmail.com', NULL, '076373', '2025-09-05 06:16:24', '2025-09-05 01:26:24', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `parent_categories`
--

CREATE TABLE `parent_categories` (
  `parent_category_id` int(11) NOT NULL,
  `parent_category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `parent_categories`
--

INSERT INTO `parent_categories` (`parent_category_id`, `parent_category_name`) VALUES
(1, 'Điện tử'),
(2, 'Thời trang'),
(3, 'Nhà cửa & Đời sống'),
(4, 'Xe cộ'),
(5, 'Giải trí & Thể thao'),
(6, 'Sách & Văn phòng phẩm'),
(7, 'Mẹ & Bé'),
(8, 'Thú cưng'),
(9, 'Đồ công nghiệp & Văn phòng'),
(10, 'Đồ thủ công & Nghệ thuật'),
(11, 'Sưu tầm & Cổ vật'),
(12, 'Khác');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posting_fee_history`
--

CREATE TABLE `posting_fee_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `posting_fee_history`
--

INSERT INTO `posting_fee_history` (`id`, `product_id`, `user_id`, `amount`, `created_date`) VALUES
(1, 1, 2, 50000.00, '2025-01-01'),
(2, 2, 3, 75000.00, '2025-01-02'),
(3, 3, 2, 30000.00, '2025-01-03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'cho_duyet',
  `sale_status` varchar(50) NOT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  `updated_date` datetime DEFAULT current_timestamp(),
  `note` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `image`, `status`, `sale_status`, `created_date`, `updated_date`, `note`) VALUES
(1, 2, 1, 'iPhone 14 Pro Max', 'Điện thoại iPhone 14 Pro Max 128GB màu tím, còn bảo hành 6 tháng', 25000000.00, 'iphone14.jpg', 'da_duyet', 'con_hang', '2025-01-01 10:00:00', '2025-09-05 14:11:37', 'Hàng chính hãng');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `parent_category_id`) VALUES
(1, 'Điện thoại', 1),
(2, 'Laptop', 1),
(3, 'Máy tính bảng', 1),
(4, 'Máy ảnh & Máy quay', 1),
(5, 'Âm thanh (loa, tai nghe, micro)', 1),
(6, 'Thiết bị đeo thông minh', 1),
(7, 'Linh kiện điện tử', 1),
(8, 'Quần áo nam', 2),
(9, 'Quần áo nữ', 2),
(10, 'Giày dép', 2),
(11, 'Túi xách & Ví', 2),
(12, 'Trang sức & Phụ kiện', 2),
(13, 'Đồng hồ', 2),
(14, 'Đồ vintage & second-hand', 2),
(15, 'Nội thất', 3),
(16, 'Đồ gia dụng', 3),
(17, 'Trang trí nhà cửa', 3),
(18, 'Dụng cụ bếp', 3),
(19, 'Đồ cũ sưu tầm trong gia đình', 3),
(20, 'Xe máy', 4),
(21, 'Ô tô', 4),
(22, 'Xe đạp', 4),
(23, 'Xe điện', 4),
(24, 'Phụ tùng xe', 4),
(25, 'Đồ bảo hộ & Phụ kiện xe', 4),
(26, 'Nhạc cụ', 5),
(27, 'Thiết bị chơi game', 5),
(28, 'Đồ thể thao', 5),
(29, 'Đồ dã ngoại', 5),
(30, 'Bộ sưu tập', 5),
(31, 'Sách giáo khoa', 6),
(32, 'Sách tham khảo', 6),
(33, 'Tiểu thuyết', 6),
(34, 'Truyện tranh', 6),
(35, 'Văn phòng phẩm', 6),
(36, 'Đồ lưu niệm học tập', 6),
(37, 'Quần áo trẻ em', 7),
(38, 'Đồ chơi trẻ em', 7),
(39, 'Xe đẩy & Ghế ăn', 7),
(40, 'Sữa & đồ ăn cho bé', 7),
(41, 'Đồ sơ sinh', 7),
(42, 'Phụ kiện cho mẹ', 7),
(43, 'Thức ăn', 8),
(44, 'Chuồng & Lồng', 8),
(45, 'Đồ chơi thú cưng', 8),
(46, 'Phụ kiện thú cưng', 8),
(47, 'Thuốc & sản phẩm chăm sóc', 8),
(48, 'Máy in & Máy photocopy', 9),
(49, 'Máy chiếu', 9),
(50, 'Thiết bị văn phòng', 9),
(51, 'Công cụ & Máy móc cũ', 9),
(52, 'Thiết bị điện công nghiệp', 9),
(53, 'Đồ gốm sứ', 10),
(54, 'Đồ mỹ nghệ', 10),
(55, 'Tranh vẽ & Tượng', 10),
(56, 'Đồ handmade', 10),
(57, 'Vật liệu thủ công', 10),
(58, 'Đồ cổ', 11),
(59, 'Tiền xu & Tem', 11),
(60, 'Đồ sưu tầm hiếm', 11),
(61, 'Đồ cổ trang trí', 11),
(62, 'Khác', 12);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_history`
--

CREATE TABLE `promotion_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `promotion_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promotion_history`
--

INSERT INTO `promotion_history` (`id`, `product_id`, `user_id`, `amount`, `promotion_time`) VALUES
(1, 1, 2, 200000.00, '2025-01-01 12:00:00'),
(2, 2, 3, 500000.00, '2025-01-02 15:30:00'),
(3, 3, 2, 100000.00, '2025-01-03 10:15:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewed_user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` varchar(1000) DEFAULT NULL,
  `created_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `reviewer_id`, `reviewed_user_id`, `product_id`, `rating`, `comment`, `created_date`) VALUES
(1, 2, 3, 1, 5, 'Người bán rất nhiệt tình, sản phẩm đúng như mô tả', '2025-01-01'),
(2, 3, 2, 2, 4, 'Sản phẩm tốt, giao hàng nhanh', '2025-01-02'),
(3, 1, 2, 3, 3, 'Sản phẩm ổn, giá hơi cao', '2025-01-03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(2, 'user'),
(3, 'moderator');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transfer_accounts`
--

CREATE TABLE `transfer_accounts` (
  `id` int(11) NOT NULL,
  `account_number` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transfer_accounts`
--

INSERT INTO `transfer_accounts` (`id`, `account_number`, `user_id`, `balance`) VALUES
(1, 1000, 3, 0),
(2, 1001, 4, 900000),
(3, 1002, 5, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transfer_history`
--

CREATE TABLE `transfer_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transfer_content` varchar(255) NOT NULL,
  `transfer_image` varchar(255) NOT NULL,
  `transfer_status` varchar(255) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transfer_history`
--

INSERT INTO `transfer_history` (`history_id`, `user_id`, `transfer_content`, `transfer_image`, `transfer_status`, `created_date`) VALUES
(1, 2, 'Chuyển tiền mua iPhone 14 Pro Max', 'transfer1.jpg', 'da_duyet', '2025-01-01 16:00:00'),
(2, 3, 'Chuyển tiền mua Laptop Dell', 'transfer2.jpg', 'cho_duyet', '2025-01-02 11:30:00'),
(3, 1, 'Nạp tiền vào tài khoản', 'transfer3.jpg', 'da_duyet', '2025-01-03 08:45:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `account_type` varchar(20) NOT NULL DEFAULT 'ca_nhan' COMMENT 'ca_nhan, doanh_nghiep',
  `avatar` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `created_date` date DEFAULT current_timestamp(),
  `updated_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role_id`, `account_type`, `avatar`, `birth_date`, `created_date`, `updated_date`, `is_active`, `is_verified`) VALUES
(1, 'admin01', 'admin@choviet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'Hà Nội', 1, 'ca_nhan', 'avatar1.jpg', '1990-01-01', '2025-01-01', '2025-09-05 14:11:36', 1, 1),
(2, 'user01', 'user1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'TP.HCM', 2, 'ca_nhan', 'avatar2.jpg', '1995-05-15', '2025-01-02', '2025-09-05 14:11:36', 1, 1),
(3, 'admin', 'test1757019822@example.com', '126cfbcd4d16ae6d25c9bfcae76d8ee4', NULL, NULL, 2, 'ca_nhan', NULL, NULL, '2025-09-05', '2025-09-05 11:14:15', 1, 1),
(4, 'hoangan', 'hoangan2711.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', NULL, NULL, 2, 'ca_nhan', NULL, NULL, '2025-09-05', '2025-09-05 04:05:49', 1, 1),
(5, 'hoangan2', 'hoangan2912.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', NULL, NULL, 2, 'ca_nhan', NULL, NULL, '2025-09-05', '2025-09-05 13:16:45', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vnpay_transactions`
--

CREATE TABLE `vnpay_transactions` (
  `id` int(11) NOT NULL,
  `txn_ref` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `vnpay_response_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vnpay_transactions`
--

INSERT INTO `vnpay_transactions` (`id`, `txn_ref`, `user_id`, `amount`, `status`, `vnpay_response_code`, `created_at`, `updated_at`) VALUES
(1, '4_1757046815413', 4, 50000.00, 'success', '00', '2025-09-05 04:33:35', '2025-09-05 04:38:05'),
(2, '4_1757047116195', 4, 50000.00, 'success', '00', '2025-09-05 04:38:36', '2025-09-05 04:39:06'),
(3, 'TXN003', 1, 1000000.00, 'failed', '07', '2025-01-03 01:50:00', '2025-09-05 07:11:37'),
(4, '4_1757056335728', 4, 50000.00, 'success', '00', '2025-09-05 07:12:15', '2025-09-05 07:12:55'),
(6, '4_1757056623295', 4, 50000.00, 'success', '00', '2025-09-05 07:17:03', '2025-09-05 07:17:30'),
(7, '4_1757056687312', 4, 50000.00, 'success', '00', '2025-09-05 07:18:07', '2025-09-05 07:18:31'),
(8, '4_1757056781164', 4, 500000.00, 'success', '00', '2025-09-05 07:19:41', '2025-09-05 07:20:08'),
(9, '4_1757057109349', 4, 50000.00, 'pending', NULL, '2025-09-05 07:25:09', '2025-09-05 07:25:09'),
(10, '4_1757057121755', 4, 50000.00, 'success', '00', '2025-09-05 07:25:21', '2025-09-05 07:25:51'),
(11, '4_1757057297391', 4, 50000.00, 'success', '00', '2025-09-05 07:28:17', '2025-09-05 07:28:44'),
(12, '4_1757057460205', 4, 50000.00, 'success', '00', '2025-09-05 07:31:00', '2025-09-05 07:31:23'),
(13, '4_1757073840123', 4, 50000.00, 'pending', NULL, '2025-09-05 12:04:00', '2025-09-05 12:04:00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `livestream`
--
ALTER TABLE `livestream`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `livestream_cart`
--
ALTER TABLE `livestream_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `livestream_id` (`livestream_id`);

--
-- Chỉ mục cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `livestream_packages`
--
ALTER TABLE `livestream_packages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Chỉ mục cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `livestream_registrations`
--
ALTER TABLE `livestream_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `phone` (`phone`);

--
-- Chỉ mục cho bảng `parent_categories`
--
ALTER TABLE `parent_categories`
  ADD PRIMARY KEY (`parent_category_id`);

--
-- Chỉ mục cho bảng `posting_fee_history`
--
ALTER TABLE `posting_fee_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Chỉ mục cho bảng `promotion_history`
--
ALTER TABLE `promotion_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewed_user_id` (`reviewed_user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `transfer_accounts`
--
ALTER TABLE `transfer_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `transfer_history`
--
ALTER TABLE `transfer_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_email` (`email`),
  ADD UNIQUE KEY `idx_phone` (`phone`),
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `vnpay_transactions`
--
ALTER TABLE `vnpay_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `txn_ref` (`txn_ref`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_txn_ref` (`txn_ref`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `livestream`
--
ALTER TABLE `livestream`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_cart`
--
ALTER TABLE `livestream_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_packages`
--
ALTER TABLE `livestream_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_registrations`
--
ALTER TABLE `livestream_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `parent_categories`
--
ALTER TABLE `parent_categories`
  MODIFY `parent_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `posting_fee_history`
--
ALTER TABLE `posting_fee_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT cho bảng `promotion_history`
--
ALTER TABLE `promotion_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `transfer_accounts`
--
ALTER TABLE `transfer_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `transfer_history`
--
ALTER TABLE `transfer_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `vnpay_transactions`
--
ALTER TABLE `vnpay_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `livestream`
--
ALTER TABLE `livestream`
  ADD CONSTRAINT `livestream_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `livestream_cart`
--
ALTER TABLE `livestream_cart`
  ADD CONSTRAINT `livestream_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `livestream_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `livestream_cart_ibfk_3` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`);

--
-- Các ràng buộc cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  ADD CONSTRAINT `livestream_messages_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`),
  ADD CONSTRAINT `livestream_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  ADD CONSTRAINT `livestream_payment_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `livestream_payment_history_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `livestream_packages` (`id`);

--
-- Các ràng buộc cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  ADD CONSTRAINT `livestream_products_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`),
  ADD CONSTRAINT `livestream_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `livestream_registrations`
--
ALTER TABLE `livestream_registrations`
  ADD CONSTRAINT `livestream_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `livestream_registrations_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `livestream_packages` (`id`);

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `posting_fee_history`
--
ALTER TABLE `posting_fee_history`
  ADD CONSTRAINT `posting_fee_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `posting_fee_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

--
-- Các ràng buộc cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `parent_categories` (`parent_category_id`);

--
-- Các ràng buộc cho bảng `promotion_history`
--
ALTER TABLE `promotion_history`
  ADD CONSTRAINT `promotion_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `promotion_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewed_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `transfer_accounts`
--
ALTER TABLE `transfer_accounts`
  ADD CONSTRAINT `transfer_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `transfer_history`
--
ALTER TABLE `transfer_history`
  ADD CONSTRAINT `transfer_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
