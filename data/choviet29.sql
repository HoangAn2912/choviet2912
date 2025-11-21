-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 21, 2025 lúc 01:24 PM
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
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `image_url`, `button_text`, `button_link`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(5, 'Test Banner ABBC', 'không vấn đề', 'https://cdn.pixabay.com/photo/2015/10/29/14/38/web-1012467_1280.jpg', 'Xem thêm', 'https:\\\\choviet29.page.gd', 4, 'active', '2025-09-13 02:55:36', '2025-09-13 02:55:36'),
(7, 'Banner test', 'okokokk', 'https://st.depositphotos.com/17620692/61554/v/1600/depositphotos_615540384-stock-illustration-sale-website-banner-background-design.jpg', 'Button', 'https:\\\\choviet29.page.gd', 2, 'active', '2025-09-13 04:09:18', '2025-09-13 04:09:18'),
(8, 'Siêu Sales sập sàn 90%', 'Hàng mới 100%', 'https://st.depositphotos.com/17620692/61619/v/1600/depositphotos_616194878-stock-illustration-modern-blue-green-pink-orange.jpg', 'Mua ưu đãi ngay', 'https:\\\\choviet29.page.gd', 1, 'inactive', '2025-09-13 04:52:08', '2025-11-06 04:50:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'ID sản phẩm',
  `order_id` int(11) DEFAULT NULL COMMENT 'ID đơn hàng (nếu có)',
  `change_type` enum('sale','return','restock','adjustment','initial') NOT NULL COMMENT 'Loại biến động: sale=bán, return=trả hàng, restock=nhập thêm, adjustment=điều chỉnh, initial=khởi tạo',
  `quantity_change` int(11) NOT NULL COMMENT 'Số lượng thay đổi (âm = giảm, dương = tăng)',
  `old_quantity` int(11) NOT NULL COMMENT 'Số lượng trước khi thay đổi',
  `new_quantity` int(11) NOT NULL COMMENT 'Số lượng sau khi thay đổi',
  `note` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_by` int(11) DEFAULT NULL COMMENT 'Người thực hiện',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lịch sử biến động tồn kho';

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
(3, 1, 'Livestream admin', 'Livestream hướng dẫn sử dụng website', '2025-01-07 18:00:00', '2025-01-07 20:00:00', 'chua_bat_dau', 'livestream3.jpg', '2025-01-03 09:15:00'),
(4, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 10:42:00', '2025-09-18 11:42:00', 'chua_bat_dau', 'default-live.jpg', '2025-09-18 10:43:00'),
(5, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 10:57:00', '2025-09-18 00:57:00', 'chua_bat_dau', 'default-live.jpg', '2025-09-18 10:58:12'),
(6, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 11:05:00', '2025-09-18 00:05:00', 'dang_live', 'default-live.jpg', '2025-09-18 11:06:34'),
(7, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-20 00:44:00', '2025-09-20 01:44:00', 'da_ket_thuc', 'default-live.jpg', '2025-09-20 00:44:51'),
(8, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-20 01:02:00', '2025-09-20 01:03:00', 'da_ket_thuc', 'default-live.jpg', '2025-09-20 01:03:56'),
(9, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'chua_bat_dau', 'livestream_68cd9fcf9d65d.jpg', '2025-09-20 01:24:15'),
(10, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cd9fdf67f30.jpg', '2025-09-20 01:24:31'),
(11, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cd9feaa7634.jpg', '2025-09-20 01:24:42'),
(12, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cda03a0f50c.jpg', '2025-09-20 01:26:02'),
(14, 5, 'An bán táo', 'An bán táo', '2025-09-20 01:41:00', '2025-09-20 02:41:00', 'da_ket_thuc', 'livestream_68cda3fa4dc1f.jpg', '2025-09-20 01:42:02'),
(15, 5, 'Tạo live', 'live', '2025-09-20 02:23:00', '2025-09-20 04:23:00', 'dang_live', 'livestream_68cdadeab6b74.jpg', '2025-09-20 02:24:26'),
(16, 5, 'sdfasdfas', 'sadfasdf', '2025-09-20 02:39:00', '2025-09-02 02:38:00', 'da_ket_thuc', 'livestream_68cdb14c245d6.jpg', '2025-09-20 02:38:52'),
(17, 5, 'Bán điện thoại giá rẻ abc', 'Bán điện thoại giá rẻ abc', '2025-09-20 09:13:00', '2025-09-20 10:13:00', 'da_ket_thuc', 'livestream_68ce0df3f2d69.jpg', '2025-09-20 09:14:11'),
(18, 4, 'Bán điện thoại giá rẻá', 'Bán điện thoại giá rẻá', '2025-09-26 15:51:00', '2025-09-27 15:52:00', 'chua_bat_dau', 'livestream_68d6543cbf971.jpg', '2025-09-26 15:52:12'),
(19, 5, 'Bán điện thoại giá rẻ abc', 'Bán điện thoại giá rẻ abc', '2025-09-26 16:02:00', '2025-09-26 16:03:00', 'dang_live', 'livestream_68d656c30eb1e.jpg', '2025-09-26 16:02:59'),
(20, 5, 'Bán điện thoại giá rẻ nà ní', 'Bán điện thoại giá rẻ nà ní', '2025-09-17 23:15:00', '2025-09-18 23:16:00', 'dang_live', 'livestream_68d6bc4c694f6.jpg', '2025-09-26 23:16:12'),
(21, 5, 'hoang an live', 'hoang an live', '2025-09-27 07:17:00', '2025-09-27 08:17:00', 'dang_live', 'livestream_68d72d2fc390f.jpg', '2025-09-27 07:17:51'),
(22, 4, 'Bán điện thoại giá rẻ', 'anh em', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'dang_live', 'livestream_6919e730d4dff.jpg', '2025-11-16 22:01:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_cart_items`
--

CREATE TABLE `livestream_cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_cart_items`
--

INSERT INTO `livestream_cart_items` (`id`, `user_id`, `livestream_id`, `product_id`, `quantity`, `price`, `added_at`, `created_at`) VALUES
(1, 4, 10, 8, 1, 25000000.00, '2025-09-20 01:50:45', '2025-09-26 17:34:22'),
(13, 4, 19, 5, 1, 190000.00, '2025-09-26 18:03:31', '2025-09-26 18:03:31'),
(32, 4, 20, 5, 3, 190000.00, '2025-09-27 01:03:53', '2025-09-27 01:03:53'),
(33, 4, 20, 6, 1, 0.00, '2025-09-27 01:08:39', '2025-09-27 01:08:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_interactions`
--

CREATE TABLE `livestream_interactions` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('like','share','follow','purchase','view') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_interactions`
--

INSERT INTO `livestream_interactions` (`id`, `livestream_id`, `user_id`, `action_type`, `created_at`) VALUES
(1, 22, 5, 'like', '2025-11-16 22:37:33'),
(2, 22, 5, 'like', '2025-11-16 22:37:35'),
(3, 22, 5, 'like', '2025-11-16 22:37:35'),
(4, 22, 5, 'like', '2025-11-16 22:37:36'),
(5, 22, 5, 'like', '2025-11-16 22:37:37'),
(6, 22, 5, 'like', '2025-11-16 22:37:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_messages`
--

CREATE TABLE `livestream_messages` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_time` datetime NOT NULL DEFAULT current_timestamp(),
  `message_type` enum('text','product_pin','order_placed') DEFAULT 'text' COMMENT 'Loại tin nhắn',
  `product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm nếu liên quan',
  `is_system_message` tinyint(1) DEFAULT 0 COMMENT 'Tin nhắn hệ thống'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_messages`
--

INSERT INTO `livestream_messages` (`id`, `livestream_id`, `user_id`, `content`, `created_time`, `message_type`, `product_id`, `is_system_message`) VALUES
(1, 1, 2, 'Chào mọi người!', '2025-01-01 20:00:00', 'text', NULL, 0),
(2, 1, 3, 'Sản phẩm này giá bao nhiêu?', '2025-01-01 20:05:00', 'text', NULL, 0),
(3, 2, 3, 'Laptop này có card đồ họa gì?', '2025-01-02 19:10:00', 'text', NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_orders`
--

CREATE TABLE `livestream_orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `vnpay_txn_ref` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivery_name` varchar(255) DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `delivery_province` varchar(255) DEFAULT NULL,
  `delivery_district` varchar(255) DEFAULT NULL,
  `delivery_ward` varchar(255) DEFAULT NULL,
  `delivery_street` varchar(255) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_orders`
--

INSERT INTO `livestream_orders` (`id`, `order_code`, `user_id`, `livestream_id`, `total_amount`, `status`, `payment_method`, `vnpay_txn_ref`, `created_at`, `updated_at`, `delivery_name`, `delivery_phone`, `delivery_province`, `delivery_district`, `delivery_ward`, `delivery_street`, `delivery_address`) VALUES
(18, 'LIVE202509275579', 4, 20, 190000.00, 'confirmed', 'wallet', NULL, '2025-09-27 00:27:59', '2025-09-27 00:27:59', 'hoangandeptraisomot', '0934838366', '22', '195', '6793', '1233', '1233, Phường Cẩm Trung, Thành phố Cẩm Phả, Tỉnh Quảng Ninh'),
(19, 'LIVE202509278840', 4, 21, 190000.00, 'cancelled', 'wallet', NULL, '2025-09-27 07:19:54', '2025-09-27 07:20:14', 'hoangandeptraisomot', '0934838366', '11', '95', '3148', '123', '123, Phường Sông Đà, Thị xã Mường Lay, Tỉnh Điện Biên'),
(20, 'LIVE202511168090', 5, 22, 12500000.00, 'delivered', 'wallet', NULL, '2025-11-16 22:49:38', '2025-11-17 12:10:21', 'hoangan2', '0934838366', '11', '96', '3163', '123', '123, Xã Mường Toong, Huyện Mường Nhé, Tỉnh Điện Biên');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_order_items`
--

CREATE TABLE `livestream_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_order_items`
--

INSERT INTO `livestream_order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 5, 1, 190000.00, '2025-09-26 16:26:16'),
(2, 2, 6, 1, 190000.00, '2025-09-26 16:26:57'),
(3, 3, 6, 1, 190000.00, '2025-09-26 17:36:53'),
(4, 4, 6, 1, 190000.00, '2025-09-26 17:38:29'),
(5, 5, 6, 1, 190000.00, '2025-09-26 17:45:57'),
(6, 6, 5, 1, 190000.00, '2025-09-26 23:18:36'),
(7, 7, 5, 1, 190000.00, '2025-09-26 23:49:50'),
(8, 8, 5, 1, 190000.00, '2025-09-26 23:50:58'),
(9, 9, 5, 2, 190000.00, '2025-09-26 23:52:06'),
(10, 10, 5, 1, 190000.00, '2025-09-26 23:55:30'),
(11, 11, 5, 1, 190000.00, '2025-09-26 23:57:00'),
(12, 12, 5, 2, 190000.00, '2025-09-27 00:00:16'),
(13, 13, 5, 1, 190000.00, '2025-09-27 00:03:26'),
(14, 14, 5, 1, 190000.00, '2025-09-27 00:07:54'),
(15, 15, 5, 1, 190000.00, '2025-09-27 00:09:38'),
(16, 16, 5, 1, 190000.00, '2025-09-27 00:10:05'),
(17, 17, 5, 1, 190000.00, '2025-09-27 00:15:25'),
(18, 18, 5, 1, 190000.00, '2025-09-27 00:27:59'),
(19, 19, 6, 1, 190000.00, '2025-09-27 07:19:54'),
(20, 20, 4, 1, 12500000.00, '2025-11-16 22:49:38');

--
-- Bẫy `livestream_order_items`
--
DELIMITER $$
CREATE TRIGGER `after_livestream_order_insert` AFTER INSERT ON `livestream_order_items` FOR EACH ROW BEGIN
    DECLARE v_track_inventory TINYINT;
    
    -- Kiểm tra sản phẩm có track inventory không
    SELECT track_inventory INTO v_track_inventory
    FROM products
    WHERE id = NEW.product_id;
    
    -- Nếu có track inventory, trừ tồn kho
    IF v_track_inventory = 1 THEN
        CALL update_product_stock(
            NEW.product_id,
            -NEW.quantity,  -- Trừ tồn kho
            'sale',
            CONCAT('Bán hàng qua livestream - Order #', NEW.order_id),
            NULL,  -- System auto
            NEW.order_id
        );
    END IF;
END
$$
DELIMITER ;

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
(1, 'Gói Ngày', 'Livestream trong 1 ngày. Phù hợp để test hoặc bán hàng ngắn hạn.', 190000.00, 1, 1),
(2, 'Gói Tuần', 'Livestream trong 7 ngày. Tiết kiệm hơn so với gói ngày.', 890000.00, 7, 1),
(3, 'Gói Tháng VIP', 'Livestream KHÔNG GIỚI HẠN số lần và thời lượng trong 30 ngày. Tối ưu chi phí cho doanh nghiệp.', 2990000.00, 30, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_payment_history`
--

CREATE TABLE `livestream_payment_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `registration_id` int(11) DEFAULT NULL COMMENT 'ID đăng ký gói',
  `package_id` int(11) NOT NULL COMMENT 'ID gói livestream',
  `amount` decimal(10,2) NOT NULL COMMENT 'Số tiền thanh toán',
  `payment_method` varchar(50) NOT NULL COMMENT 'Phương thức thanh toán: vnpay, wallet',
  `payment_status` enum('pending','success','failed') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái thanh toán',
  `vnpay_txn_ref` varchar(100) DEFAULT NULL COMMENT 'Mã giao dịch VNPay',
  `payment_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Ngày thanh toán',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lịch sử thanh toán gói livestream';

--
-- Đang đổ dữ liệu cho bảng `livestream_payment_history`
--

INSERT INTO `livestream_payment_history` (`id`, `user_id`, `registration_id`, `package_id`, `amount`, `payment_method`, `payment_status`, `vnpay_txn_ref`, `payment_date`, `created_at`) VALUES
(1, 4, 1, 1, 190000.00, 'wallet', 'success', NULL, '2025-10-28 23:29:21', '2025-10-28 16:29:21'),
(2, 4, 2, 1, 190000.00, 'wallet', 'success', NULL, '2025-10-28 23:29:53', '2025-10-28 16:29:53'),
(3, 5, 3, 1, 190000.00, 'wallet', 'success', NULL, '2025-11-05 18:16:53', '2025-11-05 11:16:53'),
(4, 5, 4, 1, 190000.00, 'wallet', 'success', NULL, '2025-11-05 19:15:12', '2025-11-05 12:15:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_products`
--

CREATE TABLE `livestream_products` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_sequence` int(11) NOT NULL DEFAULT 0,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) DEFAULT 0 COMMENT 'Sản phẩm được ghim',
  `pinned_at` datetime DEFAULT NULL,
  `special_price` decimal(10,2) DEFAULT NULL COMMENT 'Giá đặc biệt trong live',
  `stock_quantity` int(11) DEFAULT NULL COMMENT 'Số lượng còn lại',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_products`
--

INSERT INTO `livestream_products` (`id`, `livestream_id`, `product_id`, `order_sequence`, `created_date`, `is_pinned`, `pinned_at`, `special_price`, `stock_quantity`, `created_at`) VALUES
(1, 1, 1, 1, '2025-01-01 10:00:00', 0, NULL, NULL, NULL, '2025-09-18 10:50:00'),
(2, 2, 2, 1, '2025-01-02 14:30:00', 0, NULL, NULL, NULL, '2025-09-18 10:50:00'),
(3, 1, 3, 2, '2025-01-03 09:15:00', 0, NULL, NULL, NULL, '2025-09-18 10:50:00'),
(6, 9, 5, 0, '2025-09-20 01:24:15', 0, NULL, NULL, NULL, '2025-09-20 01:24:15'),
(7, 9, 8, 0, '2025-09-20 01:24:15', 0, NULL, NULL, NULL, '2025-09-20 01:24:15'),
(8, 10, 5, 0, '2025-09-20 01:24:31', 0, NULL, NULL, NULL, '2025-09-20 01:24:31'),
(9, 10, 8, 0, '2025-09-20 01:24:31', 0, NULL, NULL, NULL, '2025-09-20 01:24:31'),
(10, 11, 5, 0, '2025-09-20 01:24:42', 0, NULL, NULL, NULL, '2025-09-20 01:24:42'),
(11, 11, 8, 0, '2025-09-20 01:24:42', 1, '2025-09-20 01:46:06', NULL, NULL, '2025-09-20 01:24:42'),
(12, 12, 5, 0, '2025-09-20 01:26:02', 1, '2025-09-20 01:33:24', NULL, NULL, '2025-09-20 01:26:02'),
(13, 12, 8, 0, '2025-09-20 01:26:02', 0, NULL, NULL, NULL, '2025-09-20 01:26:02'),
(14, 14, 5, 0, '2025-09-20 01:42:02', 0, NULL, NULL, NULL, '2025-09-20 01:42:02'),
(15, 14, 8, 0, '2025-09-20 01:42:02', 1, '2025-09-20 01:42:24', NULL, NULL, '2025-09-20 01:42:02'),
(16, 14, 6, 0, '2025-09-20 01:42:02', 0, NULL, NULL, NULL, '2025-09-20 01:42:02'),
(17, 15, 8, 0, '2025-09-20 02:24:26', 1, '2025-09-20 02:24:52', NULL, NULL, '2025-09-20 02:24:26'),
(18, 16, 8, 0, '2025-09-20 02:38:52', 0, NULL, NULL, NULL, '2025-09-20 02:38:52'),
(19, 16, 5, 0, '2025-09-20 02:38:52', 0, NULL, NULL, NULL, '2025-09-20 02:38:52'),
(20, 17, 5, 0, '2025-09-20 09:14:12', 1, '2025-09-20 09:14:48', NULL, NULL, '2025-09-20 09:14:12'),
(21, 17, 8, 0, '2025-09-20 09:14:12', 0, NULL, NULL, NULL, '2025-09-20 09:14:12'),
(22, 19, 6, 0, '2025-09-26 16:02:59', 0, NULL, NULL, NULL, '2025-09-26 16:02:59'),
(23, 19, 5, 0, '2025-09-26 16:02:59', 0, NULL, NULL, NULL, '2025-09-26 16:02:59'),
(24, 19, 8, 0, '2025-09-26 18:33:22', 1, '2025-09-26 19:10:14', 189000.00, 0, '2025-09-26 18:33:22'),
(25, 20, 8, 0, '2025-09-26 23:16:12', 0, NULL, NULL, 1, '2025-09-26 23:16:12'),
(26, 20, 6, 0, '2025-09-26 23:17:34', 1, '2025-09-27 01:11:42', 0.00, 0, '2025-09-26 23:17:34'),
(27, 20, 5, 0, '2025-09-26 23:18:12', 0, NULL, 190000.00, 0, '2025-09-26 23:18:12'),
(28, 21, 6, 0, '2025-09-27 07:17:52', 1, '2025-09-27 07:19:17', NULL, 1, '2025-09-27 07:17:52'),
(29, 21, 5, 0, '2025-09-27 07:17:52', 0, NULL, NULL, 1, '2025-09-27 07:17:52'),
(30, 21, 8, 0, '2025-09-27 07:19:01', 0, NULL, 123000.00, 0, '2025-09-27 07:19:01'),
(31, 22, 4, 0, '2025-11-16 22:01:04', 0, NULL, NULL, 1, '2025-11-16 22:01:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_registrations`
--

CREATE TABLE `livestream_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `package_id` int(11) NOT NULL COMMENT 'ID gói livestream',
  `registration_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Ngày đăng ký',
  `expiry_date` datetime NOT NULL COMMENT 'Ngày hết hạn',
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái: active=đang dùng, expired=hết hạn, cancelled=đã hủy',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Phương thức thanh toán',
  `vnpay_txn_ref` varchar(100) DEFAULT NULL COMMENT 'Mã giao dịch VNPay (nếu có)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Quản lý đăng ký gói livestream của người dùng';

--
-- Đang đổ dữ liệu cho bảng `livestream_registrations`
--

INSERT INTO `livestream_registrations` (`id`, `user_id`, `package_id`, `registration_date`, `expiry_date`, `status`, `payment_method`, `vnpay_txn_ref`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '2025-10-28 23:29:21', '2025-10-29 23:29:21', 'cancelled', 'wallet', NULL, '2025-10-28 16:29:21', '2025-10-28 16:29:53'),
(2, 4, 1, '2025-10-28 23:29:53', '2025-10-29 23:29:53', 'active', 'wallet', NULL, '2025-10-28 16:29:53', '2025-10-28 16:29:53'),
(3, 5, 1, '2025-11-05 18:16:53', '2025-11-06 18:16:53', 'cancelled', 'wallet', NULL, '2025-11-05 11:16:53', '2025-11-05 12:15:12'),
(4, 5, 1, '2025-11-05 19:15:12', '2025-11-06 19:15:12', 'active', 'wallet', NULL, '2025-11-05 12:15:12', '2025-11-05 12:15:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `livestream_viewers`
--

CREATE TABLE `livestream_viewers` (
  `id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream_viewers`
--

INSERT INTO `livestream_viewers` (`id`, `livestream_id`, `user_id`, `joined_at`, `last_activity`) VALUES
(1, 19, 5, '2025-09-26 19:39:30', '2025-09-26 19:43:35'),
(2, 19, 0, '2025-09-26 19:43:35', '2025-09-26 19:43:35'),
(3, 22, 5, '2025-11-16 22:14:31', '2025-11-16 22:37:33');

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
(3, 2, 3, 2, 'Laptop này có thể giảm giá không?', '2025-01-02', '2025-01-02 10:20:00', 0),
(5, 1, 5, 6, 'Sản phẩm này còn không?', '2025-11-06', '2025-11-06 13:54:02', 0),
(7, 4, 5, 6, 'Giá có thương lượng không?', '2025-11-10', '2025-11-10 19:29:06', 0),
(9, 4, 5, 5, 'Còn bạn.', '2025-11-10', '2025-11-10 20:17:43', 0),
(10, 4, 5, 5, '<div class=\"product-card-message\" style=\"border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #fff; max-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);\">\n                      <div style=\"display: flex; gap: 12px;\">\n                        <img src=\"img/68c45ff293915.jpg\" alt=\"Áo Polo Nam Revvour Floral Luxe\" \n                             style=\"width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink: 0;\">\n                        <div style=\"flex: 1; min-width: 0;\">\n                          <h6 style=\"margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;\">Áo Polo Nam Revvour Floral Luxe</h6>\n                          <p style=\"margin: 0 0 8px 0; color: #dc3545; font-weight: bold; font-size: 16px;\">\n                            190.000 đ\n                          </p>\n                          <a href=\"index.php?detail&id=5\" \n                             style=\"display: inline-block; font-size: 12px; color: #007bff; text-decoration: none; font-weight: 500;\">\n                            Xem chi tiết →\n                          </a>\n                        </div>\n                      </div>\n                    </div>', '2025-11-10', '2025-11-10 20:20:02', 0),
(11, 4, 5, 5, 'Cho tôi xin địa chỉ được không?', '2025-11-10', '2025-11-10 20:20:21', 0),
(12, 4, 5, 5, '<div class=\"product-card-message\" style=\"border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #fff; max-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);\">\n                      <div style=\"display: flex; gap: 12px;\">\n                        <img src=\"img/68c45ff293915.jpg\" alt=\"Áo Polo Nam Revvour Floral Luxe\" \n                             style=\"width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink: 0;\">\n                        <div style=\"flex: 1; min-width: 0;\">\n                          <h6 style=\"margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;\">Áo Polo Nam Revvour Floral Luxe</h6>\n                          <p style=\"margin: 0 0 8px 0; color: #dc3545; font-weight: bold; font-size: 16px;\">\n                            190.000 đ\n                          </p>\n                          <a href=\"index.php?detail&id=5\" \n                             style=\"display: inline-block; font-size: 12px; color: #007bff; text-decoration: none; font-weight: 500;\">\n                            Xem chi tiết →\n                          </a>\n                        </div>\n                      </div>\n                    </div>', '2025-11-10', '2025-11-10 20:24:25', 0),
(13, 4, 5, 5, 'Sản phẩm này còn không?', '2025-11-10', '2025-11-10 20:24:33', 0);

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
  `parent_category_name` varchar(100) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `parent_categories`
--

INSERT INTO `parent_categories` (`parent_category_id`, `parent_category_name`, `is_hidden`) VALUES
(1, 'Điện tử', 0),
(2, 'Thời trang', 0),
(3, 'Nhà cửa & Đời sống', 0),
(4, 'Xe cộ', 0),
(5, 'Giải trí & Thể thao', 0),
(6, 'Sách & Văn phòng phẩm', 0),
(7, 'Mẹ & Bé', 0),
(8, 'Thú cưng', 0),
(9, 'Đồ công nghiệp & Văn phòng', 0),
(10, 'Đồ thủ công & Nghệ thuật', 0),
(11, 'Sưu tầm & Cổ vật', 0),
(12, 'Khác', 0);

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
(3, 3, 4, 30000.00, '2025-01-03');

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
  `note` varchar(255) NOT NULL,
  `stock_quantity` int(11) DEFAULT NULL COMMENT 'Số lượng tồn kho (NULL = không giới hạn, cho sản phẩm C2C)',
  `is_livestream_product` tinyint(1) DEFAULT 0 COMMENT '1 = Sản phẩm livestream (có quản lý kho), 0 = Sản phẩm C2C thường',
  `low_stock_alert` int(11) DEFAULT 5 COMMENT 'Ngưỡng cảnh báo hết hàng',
  `track_inventory` tinyint(1) DEFAULT 0 COMMENT '1 = Có theo dõi tồn kho, 0 = Không'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `image`, `status`, `sale_status`, `created_date`, `updated_date`, `note`, `stock_quantity`, `is_livestream_product`, `low_stock_alert`, `track_inventory`) VALUES
(1, 2, 1, 'iPhone 14 Pro Max', 'Điện thoại iPhone 14 Pro Max 128GB màu tím, còn bảo hành 6 tháng', 25000000.00, 'iphone14.jpg', 'Đã duyệt', 'Đã ẩn', '2025-01-01 10:00:00', '2025-11-21 14:19:21', '..........', NULL, 0, 5, 0),
(4, 4, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-11-21 14:14:18', 'okkokoo', NULL, 0, 5, 0),
(5, 5, 8, 'Áo Polo Nam Revvour Floral Luxe', 'Hàng tôi vừa mới mua 290.000đ nhưng không vừa size nên tôi muốn pass lại, áo size L. ai cần liên hệ tôi', 190000.00, '68c45ff293915.jpg,68c45ff293a4a.jpg,68c45ff293b49.jpg,68c45ff293f36.jpg', 'Đã duyệt', 'Đã ẩn', '2025-09-13 01:01:22', '2025-11-21 14:11:10', '', NULL, 0, 5, 0),
(6, 5, 8, 'Áo Polo Nam Revvour Floral Luxe', 'Hàng tôi vừa mới mua 290.000đ nhưng không vừa size nên tôi muốn pass lại, áo size L. ai cần liên hệ tôi', 190000.00, '68c46043f1705.jpg,68c46043f1836.jpg,68c46043f1945.jpg,68c46043f1a15.jpg', 'Đã duyệt', 'Đang bán', '2025-09-13 01:02:43', '2025-09-13 01:02:43', '', NULL, 0, 5, 0),
(7, 4, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-09-11 14:50:57', '', NULL, 0, 5, 0),
(8, 5, 1, 'iPhone 14 Pro Max', 'Điện thoại iPhone 14 Pro Max 128GB màu tím, còn bảo hành 6 tháng', 25000000.00, 'iphone14.jpg', 'Đã duyệt', 'Đang bán', '2025-01-01 10:00:00', '2025-09-05 14:11:37', 'Hàng chính hãng', NULL, 0, 5, 0),
(9, 2, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-09-11 14:50:57', '', NULL, 0, 5, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `parent_category_id`, `is_hidden`) VALUES
(1, 'Điện thoại', 1, 0),
(2, 'Laptop', 1, 0),
(3, 'Máy tính bảng', 1, 0),
(4, 'Máy ảnh & Máy quay', 1, 0),
(5, 'Âm thanh (loa, tai nghe, micro)', 1, 0),
(6, 'Thiết bị đeo thông minh', 1, 0),
(7, 'Linh kiện điện tử', 1, 0),
(8, 'Quần áo nam', 2, 0),
(9, 'Quần áo nữ', 2, 0),
(10, 'Giày dép', 2, 0),
(11, 'Túi xách & Ví', 2, 0),
(12, 'Trang sức & Phụ kiện', 2, 0),
(13, 'Đồng hồ', 2, 0),
(14, 'Đồ vintage & second-hand', 2, 0),
(15, 'Nội thất', 3, 0),
(16, 'Đồ gia dụng', 3, 0),
(17, 'Trang trí nhà cửa', 3, 0),
(18, 'Dụng cụ bếp', 3, 0),
(19, 'Đồ cũ sưu tầm trong gia đình', 3, 0),
(20, 'Xe máy', 4, 0),
(21, 'Ô tô', 4, 0),
(22, 'Xe đạp', 4, 0),
(23, 'Xe điện', 4, 0),
(24, 'Phụ tùng xe', 4, 0),
(25, 'Đồ bảo hộ & Phụ kiện xe', 4, 0),
(26, 'Nhạc cụ', 5, 0),
(27, 'Thiết bị chơi game', 5, 0),
(28, 'Đồ thể thao', 5, 0),
(29, 'Đồ dã ngoại', 5, 0),
(30, 'Bộ sưu tập', 5, 0),
(31, 'Sách giáo khoa', 6, 0),
(32, 'Sách tham khảo', 6, 0),
(33, 'Tiểu thuyết', 6, 0),
(34, 'Truyện tranh', 6, 0),
(35, 'Văn phòng phẩm', 6, 0),
(36, 'Đồ lưu niệm học tập', 6, 0),
(37, 'Quần áo trẻ em', 7, 0),
(38, 'Đồ chơi trẻ em', 7, 0),
(39, 'Xe đẩy & Ghế ăn', 7, 0),
(40, 'Sữa & đồ ăn cho bé', 7, 0),
(41, 'Đồ sơ sinh', 7, 0),
(42, 'Phụ kiện cho mẹ', 7, 0),
(43, 'Thức ăn', 8, 0),
(44, 'Chuồng & Lồng', 8, 0),
(45, 'Đồ chơi thú cưng', 8, 0),
(46, 'Phụ kiện thú cưng', 8, 0),
(47, 'Thuốc & sản phẩm chăm sóc', 8, 0),
(48, 'Máy in & Máy photocopy', 9, 0),
(49, 'Máy chiếu', 9, 0),
(50, 'Thiết bị văn phòng', 9, 0),
(51, 'Công cụ & Máy móc cũ', 9, 0),
(52, 'Thiết bị điện công nghiệp', 9, 0),
(53, 'Đồ gốm sứ', 10, 0),
(54, 'Đồ mỹ nghệ', 10, 0),
(55, 'Tranh vẽ & Tượng', 10, 0),
(56, 'Đồ handmade', 10, 0),
(57, 'Vật liệu thủ công', 10, 0),
(58, 'Đồ cổ', 11, 0),
(59, 'Tiền xu & Tem', 11, 0),
(60, 'Đồ sưu tầm hiếm', 11, 0),
(61, 'Đồ cổ trang trí', 11, 0),
(62, 'Khác', 12, 0);

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
  `created_date` date DEFAULT curdate(),
  `livestream_order_id` int(11) DEFAULT NULL COMMENT 'ID đơn hàng livestream (nếu có)',
  `c2c_order_id` int(11) DEFAULT NULL COMMENT 'ID đơn hàng C2C (nếu có)',
  `order_type` enum('livestream','c2c','direct') DEFAULT 'direct' COMMENT 'Loại đơn hàng: livestream, c2c, hoặc direct (review trực tiếp không qua đơn)',
  `is_verified_purchase` tinyint(1) DEFAULT 0 COMMENT '1 = Đã xác thực mua hàng, 0 = Chưa xác thực'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `reviewer_id`, `reviewed_user_id`, `product_id`, `rating`, `comment`, `created_date`, `livestream_order_id`, `c2c_order_id`, `order_type`, `is_verified_purchase`) VALUES
(1, 2, 3, 1, 5, 'Người bán rất nhiệt tình, sản phẩm đúng như mô tả', '2025-01-01', NULL, NULL, 'direct', 0),
(2, 3, 2, 2, 4, 'Sản phẩm tốt, giao hàng nhanh', '2025-01-02', NULL, NULL, 'direct', 0),
(3, 1, 2, 3, 3, 'Sản phẩm ổn, giá hơi cao', '2025-01-03', NULL, NULL, 'direct', 0);

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
(3, 'moderator'),
(4, 'adcontent'),
(5, 'adbusiness');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer') DEFAULT 'deposit',
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(20) DEFAULT 'vietqr',
  `bank_code` varchar(10) DEFAULT 'VCB',
  `bank_account` varchar(20) DEFAULT '1026479899',
  `qr_code_url` text DEFAULT NULL,
  `callback_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `account_id`, `amount`, `transaction_type`, `status`, `payment_method`, `bank_code`, `bank_account`, `qr_code_url`, `callback_data`, `created_at`, `updated_at`, `notes`) VALUES
(1, 'TXN001', 1, 101, 150000.00, 'deposit', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn001', '{\"message\":\"Deposit success\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(2, 'TXN002', 2, 102, 50000.00, 'withdrawal', 'pending', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn002', '{\"message\":\"Waiting for confirmation\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(3, 'TXN003', 3, 103, 200000.00, 'transfer', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn003', '{\"message\":\"Transfer completed\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(4, 'TXN004', 1, 101, 75000.00, 'deposit', 'failed', 'vietqr', 'VCB', '1026479899', NULL, '{\"error\":\"Invalid QR code\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(5, 'TXN005', 4, 104, 1000000.00, 'deposit', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn005', '{\"message\":\"OK\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(6, 'TXN006', 5, 105, 300000.00, 'transfer', 'cancelled', 'vietqr', 'VCB', '1026479899', NULL, '{\"reason\":\"User cancelled\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(7, 'TXN007', 2, 102, 20000.00, 'withdrawal', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn007', '{\"message\":\"Withdrawal done\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(8, 'TXN008', 3, 103, 99999.00, 'deposit', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn008', '{\"message\":\"Success\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(9, 'TXN009', 6, 106, 450000.00, 'transfer', 'pending', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn009', '{\"status\":\"Waiting\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL),
(10, 'TXN010', 7, 107, 125000.00, 'deposit', 'completed', 'vietqr', 'VCB', '1026479899', 'https://example.com/qr/txn010', '{\"message\":\"Completed\"}', '2025-11-16 08:15:26', '2025-11-16 15:21:49', NULL);

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
(1, 1000, 3, 1000000),
(2, 1001, 4, 430000),
(3, 1002, 5, 587500000);

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
  `business_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Tài khoản doanh nghiệp đã xác minh',
  `avatar` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `created_date` date DEFAULT current_timestamp(),
  `updated_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `balance` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role_id`, `account_type`, `business_verified`, `avatar`, `birth_date`, `created_date`, `updated_date`, `is_active`, `is_verified`, `balance`) VALUES
(1, 'Hoàng Nam ', 'admin@choviet.com', 'e10adc3949ba59abbe56e057f20f883e', '0934838364', '58 đường số 15 phường Linh Chiểu, Tp Thủ Đức, 12', 1, 'ca_nhan', 0, 'avatar1.jpg', '1990-01-01', '2025-01-01', '2025-11-18 12:32:43', 1, 1, 0.00),
(2, 'user01', 'admincontent@choviet.com', '202cb962ac59075b964b07152d234b70', '0987654321', 'TP.HCM', 4, 'ca_nhan', 0, 'avatar2.jpg', '1995-05-15', '2025-01-02', '2025-11-05 17:15:51', 1, 1, 0.00),
(3, 'admin', 'adminbusiness@choviet.com', '202cb962ac59075b964b07152d234b70', NULL, NULL, 5, 'ca_nhan', 0, NULL, NULL, '2025-09-05', '2025-11-05 17:16:29', 1, 1, 0.00),
(4, 'hoangandeptraiso234', 'hoangan2711.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', '0934838366', '58 đường số 15 phường Linh Chiểu, Tp Thủ Đức, 12', 2, 'doanh_nghiep', 0, '1757577336_68c28078bebc4.jpg', '2003-11-27', '2025-09-05', '2025-11-17 11:13:51', 1, 1, 810000.00),
(5, 'hoangan2', 'hoangan2912.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', NULL, NULL, 2, 'doanh_nghiep', 0, NULL, NULL, '2025-09-05', '2025-11-21 10:43:45', 1, 1, 0.00);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_inventory_report`
-- (See below for the actual view)
--
CREATE TABLE `v_inventory_report` (
`product_id` int(11)
,`product_name` varchar(255)
,`price` decimal(10,2)
,`stock_quantity` int(11)
,`low_stock_alert` int(11)
,`is_livestream_product` tinyint(1)
,`track_inventory` tinyint(1)
,`seller_id` int(11)
,`seller_name` varchar(50)
,`stock_status` varchar(14)
,`total_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_seller_ratings`
-- (See below for the actual view)
--
CREATE TABLE `v_seller_ratings` (
`seller_id` int(11)
,`seller_name` varchar(50)
,`total_reviews` bigint(21)
,`avg_rating` decimal(14,4)
,`five_star_count` decimal(22,0)
,`four_star_count` decimal(22,0)
,`three_star_count` decimal(22,0)
,`two_star_count` decimal(22,0)
,`one_star_count` decimal(22,0)
,`verified_reviews_count` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_inventory_report`
--
DROP TABLE IF EXISTS `v_inventory_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventory_report`  AS SELECT `p`.`id` AS `product_id`, `p`.`title` AS `product_name`, `p`.`price` AS `price`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`low_stock_alert` AS `low_stock_alert`, `p`.`is_livestream_product` AS `is_livestream_product`, `p`.`track_inventory` AS `track_inventory`, `p`.`user_id` AS `seller_id`, `u`.`username` AS `seller_name`, CASE WHEN `p`.`stock_quantity` is null THEN 'Không giới hạn' WHEN `p`.`stock_quantity` = 0 THEN 'Hết hàng' WHEN `p`.`stock_quantity` <= `p`.`low_stock_alert` THEN 'Sắp hết' ELSE 'Còn hàng' END AS `stock_status`, coalesce((select sum(`livestream_order_items`.`quantity`) from `livestream_order_items` where `livestream_order_items`.`product_id` = `p`.`id`),0) AS `total_sold` FROM (`products` `p` join `users` `u` on(`p`.`user_id` = `u`.`id`)) WHERE `p`.`is_livestream_product` = 1 ORDER BY `p`.`stock_quantity` ASC, `p`.`title` ASC ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_seller_ratings`
--
DROP TABLE IF EXISTS `v_seller_ratings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_seller_ratings`  AS SELECT `u`.`id` AS `seller_id`, `u`.`username` AS `seller_name`, count(`r`.`id`) AS `total_reviews`, coalesce(avg(`r`.`rating`),0) AS `avg_rating`, sum(case when `r`.`rating` = 5 then 1 else 0 end) AS `five_star_count`, sum(case when `r`.`rating` = 4 then 1 else 0 end) AS `four_star_count`, sum(case when `r`.`rating` = 3 then 1 else 0 end) AS `three_star_count`, sum(case when `r`.`rating` = 2 then 1 else 0 end) AS `two_star_count`, sum(case when `r`.`rating` = 1 then 1 else 0 end) AS `one_star_count`, sum(case when `r`.`is_verified_purchase` = 1 then 1 else 0 end) AS `verified_reviews_count` FROM (`users` `u` left join `reviews` `r` on(`u`.`id` = `r`.`reviewed_user_id`)) GROUP BY `u`.`id`, `u`.`username` ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_change_type` (`change_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_inventory_user` (`created_by`);

--
-- Chỉ mục cho bảng `livestream`
--
ALTER TABLE `livestream`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `livestream_cart_items`
--
ALTER TABLE `livestream_cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`livestream_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `fk_livestream_cart_items_product_1` (`product_id`);

--
-- Chỉ mục cho bảng `livestream_interactions`
--
ALTER TABLE `livestream_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action_type` (`action_type`);

--
-- Chỉ mục cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `livestream_orders`
--
ALTER TABLE `livestream_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `status` (`status`);

--
-- Chỉ mục cho bảng `livestream_order_items`
--
ALTER TABLE `livestream_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `livestream_packages`
--
ALTER TABLE `livestream_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_price` (`price`);

--
-- Chỉ mục cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_package_id` (`package_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_registration_id` (`registration_id`);

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
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_package_id` (`package_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Chỉ mục cho bảng `livestream_viewers`
--
ALTER TABLE `livestream_viewers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_viewer` (`livestream_id`,`user_id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_stock_quantity` (`stock_quantity`),
  ADD KEY `idx_is_livestream_product` (`is_livestream_product`);

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
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_livestream_order_id` (`livestream_order_id`),
  ADD KEY `idx_c2c_order_id` (`c2c_order_id`),
  ADD KEY `idx_order_type` (`order_type`),
  ADD KEY `idx_verified_purchase` (`is_verified_purchase`);

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
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `livestream`
--
ALTER TABLE `livestream`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `livestream_cart_items`
--
ALTER TABLE `livestream_cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `livestream_interactions`
--
ALTER TABLE `livestream_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_orders`
--
ALTER TABLE `livestream_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `livestream_order_items`
--
ALTER TABLE `livestream_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `livestream_packages`
--
ALTER TABLE `livestream_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `livestream_registrations`
--
ALTER TABLE `livestream_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `livestream_viewers`
--
ALTER TABLE `livestream_viewers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream`
--
ALTER TABLE `livestream`
  ADD CONSTRAINT `livestream_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `livestream_cart_items`
--
ALTER TABLE `livestream_cart_items`
  ADD CONSTRAINT `fk_livestream_cart_items_livestream_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_cart_items_product_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_cart_items_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_cart_items_user_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream_interactions`
--
ALTER TABLE `livestream_interactions`
  ADD CONSTRAINT `fk_livestream_interactions_livestream` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_interactions_livestream_2` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_interactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_interactions_user_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  ADD CONSTRAINT `livestream_messages_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`),
  ADD CONSTRAINT `livestream_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `livestream_orders`
--
ALTER TABLE `livestream_orders`
  ADD CONSTRAINT `fk_livestream_orders_livestream` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_orders_livestream_3` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_orders_user_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream_order_items`
--
ALTER TABLE `livestream_order_items`
  ADD CONSTRAINT `fk_livestream_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `livestream_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_order_items_order_4` FOREIGN KEY (`order_id`) REFERENCES `livestream_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_order_items_product_4` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream_payment_history`
--
ALTER TABLE `livestream_payment_history`
  ADD CONSTRAINT `fk_livestream_payment_package` FOREIGN KEY (`package_id`) REFERENCES `livestream_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_payment_registration` FOREIGN KEY (`registration_id`) REFERENCES `livestream_registrations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_livestream_reg_package` FOREIGN KEY (`package_id`) REFERENCES `livestream_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `livestream_viewers`
--
ALTER TABLE `livestream_viewers`
  ADD CONSTRAINT `fk_livestream_viewers_livestream` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_viewers_livestream_5` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_viewers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_livestream_viewers_user_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
