<?php
/**
 * Controller quản lý đơn hàng C2C
 */

require_once __DIR__ . '/../model/mC2COrder.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/RateLimiter.php';

class cC2COrder {
    private $model;

    public function __construct() {
        $this->model = new mC2COrder();
    }

    /**
     * Tạo đơn hàng từ sản phẩm
     */
    public function createOrder() {
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrfToken)) {
            header("Location: index.php?toast=" . urlencode("❌ CSRF token không hợp lệ!") . "&type=error");
            exit;
        }

        // Rate limiting - 10 đơn / 1 giờ
        RateLimiter::middleware('c2c_order_create', 10, 3600);

        $user_id = $_SESSION['user_id'] ?? 0;
        if ($user_id == 0) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $data = [
            'buyer_id' => $user_id,
            'product_id' => intval($_POST['product_id'] ?? 0),
            'quantity' => intval($_POST['quantity'] ?? 1),
            'price' => floatval($_POST['price'] ?? 0),
            'delivery_method' => Security::sanitize($_POST['delivery_method'] ?? 'ship'),
            'delivery_name' => Security::sanitize($_POST['delivery_name'] ?? ''),
            'delivery_phone' => Security::sanitize($_POST['delivery_phone'] ?? ''),
            'delivery_address' => Security::sanitize($_POST['delivery_address'] ?? ''),
            'delivery_province' => Security::sanitize($_POST['delivery_province'] ?? ''),
            'delivery_district' => Security::sanitize($_POST['delivery_district'] ?? ''),
            'delivery_ward' => Security::sanitize($_POST['delivery_ward'] ?? ''),
            'payment_method' => Security::sanitize($_POST['payment_method'] ?? 'cash'),
            'buyer_note' => Security::sanitize($_POST['buyer_note'] ?? '')
        ];

        $result = $this->model->createOrder($data);

        if ($result['success']) {
            header("Location: index.php?c2c-order-detail&id=" . $result['order_id'] . "&toast=" . urlencode("✅ " . $result['message']) . "&type=success");
        } else {
            header("Location: index.php?detail&id=" . $data['product_id'] . "&toast=" . urlencode("❌ " . $result['message']) . "&type=error");
        }
        exit;
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function showOrderDetail() {
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($user_id == 0) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $order_id = intval($_GET['id'] ?? 0);
        if ($order_id == 0) {
            header("Location: index.php");
            exit;
        }

        // Kiểm tra quyền truy cập
        if (!$this->model->canAccessOrder($order_id, $user_id)) {
            header("Location: index.php?toast=" . urlencode("❌ Bạn không có quyền xem đơn hàng này!") . "&type=error");
            exit;
        }

        $order = $this->model->getOrderById($order_id);
        $messages = $this->model->getOrderMessages($order_id);

        // Đánh dấu tin nhắn đã đọc
        $this->model->markMessagesAsRead($order_id, $user_id);

        // Xác định role (buyer/seller)
        $is_buyer = ($order['buyer_id'] == $user_id);
        $is_seller = ($order['seller_id'] == $user_id);

        include_once __DIR__ . '/../view/c2c_order_detail.php';
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus() {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrfToken)) {
            header("Location: index.php?toast=" . urlencode("❌ CSRF token không hợp lệ!") . "&type=error");
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? 0;
        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = Security::sanitize($_POST['status'] ?? '');
        $note = Security::sanitize($_POST['note'] ?? '');

        $result = $this->model->updateOrderStatus($order_id, $new_status, $user_id, $note);

        if ($result['success']) {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("✅ " . $result['message']) . "&type=success");
        } else {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("❌ " . $result['message']) . "&type=error");
        }
        exit;
    }

    /**
     * Gửi tin nhắn / đề nghị giá
     */
    public function sendMessage() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $order_id = intval($_POST['order_id'] ?? 0);
        $message = Security::sanitize($_POST['message'] ?? '');
        $message_type = Security::sanitize($_POST['message_type'] ?? 'text');
        $offer_price = isset($_POST['offer_price']) ? floatval($_POST['offer_price']) : null;

        if ($message_type == 'offer' && $offer_price) {
            $result = $this->model->sendPriceOffer($order_id, $user_id, $offer_price, $message);
        } else {
            $result = $this->model->addOrderMessage($order_id, $user_id, $message, $message_type);
        }

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Chấp nhận giá đề nghị
     */
    public function acceptOffer() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $order_id = intval($_POST['order_id'] ?? 0);
        $offer_price = floatval($_POST['offer_price'] ?? 0);

        $result = $this->model->acceptOffer($order_id, $user_id, $offer_price);

        if ($result['success']) {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("✅ " . $result['message']) . "&type=success");
        } else {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("❌ " . $result['message']) . "&type=error");
        }
        exit;
    }

    /**
     * Hiển thị danh sách đơn hàng của tôi
     */
    public function myOrders() {
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($user_id == 0) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $role = $_GET['role'] ?? 'buyer'; // buyer hoặc seller
        $status = $_GET['status'] ?? null;

        if ($role == 'buyer') {
            $orders = $this->model->getBuyerOrders($user_id, $status);
            $counts = $this->model->countOrders($user_id, 'buyer');
        } else {
            $orders = $this->model->getSellerOrders($user_id, $status);
            $counts = $this->model->countOrders($user_id, 'seller');
        }

        include_once __DIR__ . '/../view/c2c_my_orders.php';
    }

    /**
     * Giữ tiền ký quỹ
     */
    public function holdEscrow() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $order_id = intval($_POST['order_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);

        $result = $this->model->holdEscrow($order_id, $amount, $user_id);

        if ($result['success']) {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("✅ " . $result['message']) . "&type=success");
        } else {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("❌ " . $result['message']) . "&type=error");
        }
        exit;
    }

    /**
     * Xác nhận hoàn tất và chuyển tiền
     */
    public function completeOrder() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $order_id = intval($_POST['order_id'] ?? 0);

        $order = $this->model->getOrderById($order_id);
        
        // Chỉ buyer mới có thể xác nhận hoàn tất
        if ($order['buyer_id'] != $user_id) {
            header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("❌ Bạn không có quyền!") . "&type=error");
            exit;
        }

        // Cập nhật trạng thái
        $this->model->updateOrderStatus($order_id, 'completed', $user_id, 'Người mua xác nhận đã nhận hàng');

        // Nếu có escrow, chuyển tiền cho seller
        if ($order['escrow_held']) {
            $this->model->releaseEscrowToSeller($order_id, $order['seller_id']);
        }

        header("Location: index.php?c2c-order-detail&id={$order_id}&toast=" . urlencode("✅ Đơn hàng đã hoàn tất!") . "&type=success");
        exit;
    }
}
?>











