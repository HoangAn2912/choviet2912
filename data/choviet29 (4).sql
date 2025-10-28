-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 01, 2025 lúc 07:07 AM
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
(2, 'Giảm giá lên đến 50%', 'Cơ hội vàng để sở hữu những sản phẩm yêu thích với giá ưu đãi', '/placeholder.svg?height=430&width=800', 'Xem ưu đãi', '#sale', 2, 'active', '2025-09-13 01:45:26', '2025-09-13 01:45:26'),
(3, 'Bộ sưu tập mùa hè 2024', 'Những thiết kế mới nhất cho mùa hè sôi động', '/placeholder.svg?height=430&width=800', 'Khám phá', '#summer-collection', 3, 'active', '2025-09-13 01:45:26', '2025-09-13 01:45:26'),
(5, 'Test Banner ABBC', 'không vấn đề', 'https://cdn.pixabay.com/photo/2015/10/29/14/38/web-1012467_1280.jpg', 'Xem thêm', 'https:\\\\choviet29.page.gd', 4, 'active', '2025-09-13 02:55:36', '2025-09-13 02:55:36'),
(7, 'Banner test', 'okokokk', 'https://st.depositphotos.com/17620692/61554/v/1600/depositphotos_615540384-stock-illustration-sale-website-banner-background-design.jpg', 'Button', 'https:\\\\choviet29.page.gd', 2, 'active', '2025-09-13 04:09:18', '2025-09-13 04:09:18'),
(8, 'Siêu Sales sập sàn 90%', 'Hàng mới 100%', 'https://st.depositphotos.com/17620692/61619/v/1600/depositphotos_616194878-stock-illustration-modern-blue-green-pink-orange.jpg', 'Mua ưu đãi ngay', 'https:\\\\choviet29.page.gd', 1, 'active', '2025-09-13 04:52:08', '2025-09-13 04:52:08');

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
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `room_id` varchar(50) DEFAULT NULL COMMENT 'ID phòng livestream',
  `stream_key` varchar(100) DEFAULT NULL COMMENT 'Stream key cho RTMP',
  `viewer_count` int(11) DEFAULT 0 COMMENT 'Số lượng người xem',
  `total_orders` int(11) DEFAULT 0 COMMENT 'Tổng số đơn hàng',
  `total_revenue` decimal(10,2) DEFAULT 0.00 COMMENT 'Tổng doanh thu',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Livestream nổi bật'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `livestream`
--

INSERT INTO `livestream` (`id`, `user_id`, `title`, `description`, `start_time`, `end_time`, `status`, `image`, `created_date`, `room_id`, `stream_key`, `viewer_count`, `total_orders`, `total_revenue`, `is_featured`) VALUES
(1, 2, 'Livestream bán điện thoại', 'Livestream bán các loại điện thoại giá rẻ', '2025-01-05 20:00:00', '2025-01-05 22:00:00', 'chua_bat_dau', 'livestream1.jpg', '2025-01-01 10:00:00', NULL, NULL, 0, 0, 0.00, 0),
(2, 3, 'Livestream laptop gaming', 'Livestream giới thiệu laptop gaming mới nhất', '2025-01-06 19:00:00', '2025-01-06 21:00:00', 'chua_bat_dau', 'livestream2.jpg', '2025-01-02 14:30:00', NULL, NULL, 0, 0, 0.00, 0),
(3, 1, 'Livestream admin', 'Livestream hướng dẫn sử dụng website', '2025-01-07 18:00:00', '2025-01-07 20:00:00', 'chua_bat_dau', 'livestream3.jpg', '2025-01-03 09:15:00', NULL, NULL, 0, 0, 0.00, 0),
(4, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 10:42:00', '2025-09-18 11:42:00', 'chua_bat_dau', 'default-live.jpg', '2025-09-18 10:43:00', 'room_1758166980_5', 'stream_68cb7fc435256', 0, 0, 0.00, 0),
(5, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 10:57:00', '2025-09-18 00:57:00', 'chua_bat_dau', 'default-live.jpg', '2025-09-18 10:58:12', 'room_1758167892_5', 'stream_68cb8354e5434', 0, 0, 0.00, 0),
(6, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-18 11:05:00', '2025-09-18 00:05:00', 'dang_live', 'default-live.jpg', '2025-09-18 11:06:34', 'room_1758168394_5', 'stream_68cb854a7cdd6', 0, 0, 0.00, 0),
(7, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-20 00:44:00', '2025-09-20 01:44:00', 'da_ket_thuc', 'default-live.jpg', '2025-09-20 00:44:51', 'room_1758303891_5', 'stream_68cd96932b18a', 0, 0, 0.00, 0),
(8, 5, 'Bán điện thoại giá rẻ', 'Bán điện thoại giá rẻ', '2025-09-20 01:02:00', '2025-09-20 01:03:00', 'da_ket_thuc', 'default-live.jpg', '2025-09-20 01:03:56', 'room_1758305036_5', 'stream_68cd9b0c08b10', 0, 0, 0.00, 0),
(9, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'chua_bat_dau', 'livestream_68cd9fcf9d65d.jpg', '2025-09-20 01:24:15', NULL, NULL, 0, 0, 0.00, 0),
(10, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cd9fdf67f30.jpg', '2025-09-20 01:24:31', NULL, NULL, 0, 0, 0.00, 0),
(11, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cd9feaa7634.jpg', '2025-09-20 01:24:42', NULL, NULL, 0, 0, 0.00, 0),
(12, 5, 'Bán điện thoại giá rẻ á nha', 'Bán điện thoại giá rẻ á nha', '2025-09-20 01:23:00', '2025-09-20 02:23:00', 'da_ket_thuc', 'livestream_68cda03a0f50c.jpg', '2025-09-20 01:26:02', NULL, NULL, 0, 0, 0.00, 0),
(14, 5, 'An bán táo', 'An bán táo', '2025-09-20 01:41:00', '2025-09-20 02:41:00', 'da_ket_thuc', 'livestream_68cda3fa4dc1f.jpg', '2025-09-20 01:42:02', NULL, NULL, 0, 0, 0.00, 0),
(15, 5, 'Tạo live', 'live', '2025-09-20 02:23:00', '2025-09-20 04:23:00', 'dang_live', 'livestream_68cdadeab6b74.jpg', '2025-09-20 02:24:26', NULL, NULL, 0, 0, 0.00, 0),
(16, 5, 'sdfasdfas', 'sadfasdf', '2025-09-20 02:39:00', '2025-09-02 02:38:00', 'da_ket_thuc', 'livestream_68cdb14c245d6.jpg', '2025-09-20 02:38:52', NULL, NULL, 0, 0, 0.00, 0),
(17, 5, 'Bán điện thoại giá rẻ abc', 'Bán điện thoại giá rẻ abc', '2025-09-20 09:13:00', '2025-09-20 10:13:00', 'da_ket_thuc', 'livestream_68ce0df3f2d69.jpg', '2025-09-20 09:14:11', NULL, NULL, 0, 0, 0.00, 0),
(18, 4, 'Bán điện thoại giá rẻá', 'Bán điện thoại giá rẻá', '2025-09-26 15:51:00', '2025-09-27 15:52:00', 'chua_bat_dau', 'livestream_68d6543cbf971.jpg', '2025-09-26 15:52:12', NULL, NULL, 0, 0, 0.00, 0),
(19, 5, 'Bán điện thoại giá rẻ abc', 'Bán điện thoại giá rẻ abc', '2025-09-26 16:02:00', '2025-09-26 16:03:00', 'dang_live', 'livestream_68d656c30eb1e.jpg', '2025-09-26 16:02:59', NULL, NULL, 5, 0, 0.00, 0),
(20, 5, 'Bán điện thoại giá rẻ nà ní', 'Bán điện thoại giá rẻ nà ní', '2025-09-17 23:15:00', '2025-09-18 23:16:00', 'dang_live', 'livestream_68d6bc4c694f6.jpg', '2025-09-26 23:16:12', NULL, NULL, 0, 0, 0.00, 0),
(21, 5, 'hoang an live', 'hoang an live', '2025-09-27 07:17:00', '2025-09-27 08:17:00', 'dang_live', 'livestream_68d72d2fc390f.jpg', '2025-09-27 07:17:51', NULL, NULL, 0, 0, 0.00, 0);

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
(19, 'LIVE202509278840', 4, 21, 190000.00, 'cancelled', 'wallet', NULL, '2025-09-27 07:19:54', '2025-09-27 07:20:14', 'hoangandeptraisomot', '0934838366', '11', '95', '3148', '123', '123, Phường Sông Đà, Thị xã Mường Lay, Tỉnh Điện Biên');

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
(19, 19, 6, 1, 190000.00, '2025-09-27 07:19:54');

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
(30, 21, 8, 0, '2025-09-27 07:19:01', 0, NULL, 123000.00, 0, '2025-09-27 07:19:01');

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
(2, 19, 0, '2025-09-26 19:43:35', '2025-09-26 19:43:35');

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
(4, 5, 4, 4, 'chat_4_5.json', '2025-09-11', '2025-09-11 15:03:40', 0);

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
  `note` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `image`, `status`, `sale_status`, `created_date`, `updated_date`, `note`) VALUES
(1, 2, 1, 'iPhone 14 Pro Max', 'Điện thoại iPhone 14 Pro Max 128GB màu tím, còn bảo hành 6 tháng', 25000000.00, 'iphone14.jpg', 'Đã duyệt', 'Đang bán', '2025-01-01 10:00:00', '2025-09-05 14:11:37', 'Hàng chính hãng'),
(4, 4, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-09-11 14:50:57', ''),
(5, 5, 8, 'Áo Polo Nam Revvour Floral Luxe', 'Hàng tôi vừa mới mua 290.000đ nhưng không vừa size nên tôi muốn pass lại, áo size L. ai cần liên hệ tôi', 190000.00, '68c45ff293915.jpg,68c45ff293a4a.jpg,68c45ff293b49.jpg,68c45ff293f36.jpg', 'Đã duyệt', 'Đang bán', '2025-09-13 01:01:22', '2025-09-13 01:01:22', ''),
(6, 5, 8, 'Áo Polo Nam Revvour Floral Luxe', 'Hàng tôi vừa mới mua 290.000đ nhưng không vừa size nên tôi muốn pass lại, áo size L. ai cần liên hệ tôi', 190000.00, '68c46043f1705.jpg,68c46043f1836.jpg,68c46043f1945.jpg,68c46043f1a15.jpg', 'Đã duyệt', 'Đang bán', '2025-09-13 01:02:43', '2025-09-13 01:02:43', ''),
(7, 4, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-09-11 14:50:57', ''),
(8, 5, 1, 'iPhone 14 Pro Max', 'Điện thoại iPhone 14 Pro Max 128GB màu tím, còn bảo hành 6 tháng', 25000000.00, 'iphone14.jpg', 'Đã duyệt', 'Đang bán', '2025-01-01 10:00:00', '2025-09-05 14:11:37', 'Hàng chính hãng'),
(9, 2, 1, 'Cần bán 15 thường 128gb ngoại hình 99%', 'Vừa mua nhưng vì có vấn đề gia đình nên tôi cần bán nó để xoay sở...\r\n15 thường blue\r\n128gb\r\nmã mỹ\r\npin 100%\r\nngoại hình 99%\r\nphụ kiện đầy đủ...\r\nanh chị có thể tham khảo sơ qua, nếu có nhu cầu thì liên hệ em ạ', 12500000.00, '68c27f614ad28.jpg,68c27f614ae24.jpg,68c27f614aee5.jpg,68c27f614afbf.jpg', 'Đã duyệt', 'Đang bán', '2025-09-11 09:50:57', '2025-09-11 14:50:57', '');

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
(2, 1001, 4, 1000000),
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
  `is_verified` tinyint(1) DEFAULT 0,
  `balance` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role_id`, `account_type`, `avatar`, `birth_date`, `created_date`, `updated_date`, `is_active`, `is_verified`, `balance`) VALUES
(1, 'admin01', 'admin@choviet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'Hà Nội', 1, 'ca_nhan', 'avatar1.jpg', '1990-01-01', '2025-01-01', '2025-09-05 14:11:36', 1, 1, 0.00),
(2, 'user01', 'user1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'TP.HCM', 2, 'ca_nhan', 'avatar2.jpg', '1995-05-15', '2025-01-02', '2025-09-05 14:11:36', 1, 1, 0.00),
(3, 'admin', 'test1757019822@example.com', '787a1458649a2df9166ebabf580ac665', NULL, NULL, 2, 'ca_nhan', NULL, NULL, '2025-09-05', '2025-09-26 19:33:37', 1, 1, 0.00),
(4, 'hoangandeptraisomot', 'hoangan2711.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', '0934838366', '58 đường số 15 phường Linh Chiểu, Tp Thủ Đức, 12', 2, 'ca_nhan', '1757577336_68c28078bebc4.jpg', '2003-11-27', '2025-09-05', '2025-09-26 23:58:10', 1, 1, 810000.00),
(5, 'hoangan2', 'hoangan2912.npha@gmail.com', '787a1458649a2df9166ebabf580ac665', NULL, NULL, 2, 'ca_nhan', NULL, NULL, '2025-09-05', '2025-09-05 13:16:45', 1, 1, 0.00);

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
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_created_at` (`created_at`);

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
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `livestream_id` (`livestream_id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `livestream`
--
ALTER TABLE `livestream`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `livestream_cart_items`
--
ALTER TABLE `livestream_cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `livestream_interactions`
--
ALTER TABLE `livestream_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `livestream_messages`
--
ALTER TABLE `livestream_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_orders`
--
ALTER TABLE `livestream_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `livestream_order_items`
--
ALTER TABLE `livestream_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `livestream_packages`
--
ALTER TABLE `livestream_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `livestream_viewers`
--
ALTER TABLE `livestream_viewers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Các ràng buộc cho bảng `livestream_products`
--
ALTER TABLE `livestream_products`
  ADD CONSTRAINT `livestream_products_ibfk_1` FOREIGN KEY (`livestream_id`) REFERENCES `livestream` (`id`),
  ADD CONSTRAINT `livestream_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

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

--
-- Các ràng buộc cho bảng `vnpay_transactions`
--
ALTER TABLE `vnpay_transactions`
  ADD CONSTRAINT `fk_vnpay_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vnpay_transactions_user_6` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
