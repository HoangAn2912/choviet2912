-- =============================================
-- BẢNG CHO HỆ THỐNG LIVESTREAM
-- =============================================

-- Bảng sản phẩm trong livestream
CREATE TABLE IF NOT EXISTS `livestream_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livestream_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0 COMMENT 'Sản phẩm được ghim',
  `pinned_at` datetime DEFAULT NULL,
  `special_price` decimal(10,2) DEFAULT NULL COMMENT 'Giá đặc biệt trong live',
  `stock_quantity` int(11) DEFAULT NULL COMMENT 'Số lượng còn lại',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `livestream_id` (`livestream_id`),
  KEY `product_id` (`product_id`),
  KEY `is_pinned` (`is_pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng giỏ hàng livestream
CREATE TABLE IF NOT EXISTS `livestream_cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`, `livestream_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `livestream_id` (`livestream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng đơn hàng từ livestream
CREATE TABLE IF NOT EXISTS `livestream_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `livestream_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `vnpay_txn_ref` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `livestream_id` (`livestream_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS `livestream_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng tương tác livestream
CREATE TABLE IF NOT EXISTS `livestream_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('like','share','follow','purchase','view') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `livestream_id` (`livestream_id`),
  KEY `user_id` (`user_id`),
  KEY `action_type` (`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng viewers đang xem livestream
CREATE TABLE IF NOT EXISTS `livestream_viewers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livestream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_viewer` (`livestream_id`, `user_id`),
  KEY `livestream_id` (`livestream_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cập nhật bảng livestream hiện có
ALTER TABLE `livestream` 
ADD COLUMN IF NOT EXISTS `room_id` varchar(50) DEFAULT NULL COMMENT 'ID phòng livestream',
ADD COLUMN IF NOT EXISTS `stream_key` varchar(100) DEFAULT NULL COMMENT 'Stream key cho RTMP',
ADD COLUMN IF NOT EXISTS `viewer_count` int(11) DEFAULT 0 COMMENT 'Số lượng người xem',
ADD COLUMN IF NOT EXISTS `total_orders` int(11) DEFAULT 0 COMMENT 'Tổng số đơn hàng',
ADD COLUMN IF NOT EXISTS `total_revenue` decimal(10,2) DEFAULT 0.00 COMMENT 'Tổng doanh thu',
ADD COLUMN IF NOT EXISTS `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Livestream nổi bật';

-- Cập nhật bảng livestream_messages
ALTER TABLE `livestream_messages` 
ADD COLUMN IF NOT EXISTS `message_type` enum('text','product_pin','order_placed') DEFAULT 'text' COMMENT 'Loại tin nhắn',
ADD COLUMN IF NOT EXISTS `product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm nếu liên quan',
ADD COLUMN IF NOT EXISTS `is_system_message` tinyint(1) DEFAULT 0 COMMENT 'Tin nhắn hệ thống';

-- Thêm dữ liệu mẫu
INSERT INTO `livestream_products` (`livestream_id`, `product_id`, `is_pinned`, `special_price`, `stock_quantity`) VALUES
(1, 1, 1, 18500000.00, 5),
(1, 2, 0, 12000000.00, 3),
(2, 3, 1, 25000000.00, 2),
(2, 4, 0, 15000000.00, 4);

-- Thêm dữ liệu mẫu cho livestream
UPDATE `livestream` SET 
`room_id` = 'room_001',
`stream_key` = 'stream_key_001',
`viewer_count` = 1200,
`total_orders` = 15,
`total_revenue` = 250000000.00,
`is_featured` = 1
WHERE `id` = 1;

UPDATE `livestream` SET 
`room_id` = 'room_002',
`stream_key` = 'stream_key_002',
`viewer_count` = 800,
`total_orders` = 8,
`total_revenue` = 180000000.00,
`is_featured` = 0
WHERE `id` = 2;




