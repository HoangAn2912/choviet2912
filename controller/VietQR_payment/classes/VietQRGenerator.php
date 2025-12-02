<?php
/**
 * Class tạo mã QR VietQR - Enhanced version
 * Sử dụng API của VietQR để generate QR code với nhiều tùy chọn
 */

class VietQRGenerator {
    private $apiUrl = 'https://img.vietqr.io/image/';
    private $bankCode;
    private $accountNumber;
    private $accountName;
    
    // Danh sách các ngân hàng hỗ trợ
    private $supportedBanks = [
        'VCB' => 'Vietcombank',
        'TCB' => 'Techcombank', 
        'VTB' => 'VietinBank',
        'BIDV' => 'BIDV',
        'ACB' => 'ACB',
        'MB' => 'MB Bank',
        'SHB' => 'SHB',
        'VPB' => 'VPBank',
        'TPB' => 'TPBank',
        'STB' => 'Sacombank'
    ];
    
    public function __construct($bankCode = 'VCB', $accountNumber = '1026479899', $accountName = 'NGUYEN VAN A') {
        $this->bankCode = strtoupper($bankCode);
        $this->accountNumber = $accountNumber;
        $this->accountName = strtoupper($this->removeVietnameseAccents($accountName));
    }
    
    /**
     * Tạo URL QR code cơ bản
     */
    public function generateQRUrl($amount, $description = '', $transactionId = '') {
        if (!$this->validateAmount($amount)) {
            throw new Exception('Số tiền không hợp lệ');
        }
        
        if (!$this->validateBankCode($this->bankCode)) {
            throw new Exception('Mã ngân hàng không được hỗ trợ');
        }
        
        // Format description với transaction ID
        $addInfo = $this->formatDescription($description, $transactionId);
        
        // Tạo URL theo format VietQR
        $qrUrl = $this->apiUrl . $this->bankCode . '/' . $this->accountNumber . '/' . $amount . '.png';
        
        // Thêm parameters
        $params = [
            'addInfo' => $addInfo,
            'accountName' => $this->accountName
        ];
        
        $qrUrl .= '?' . http_build_query($params);
        
        return $qrUrl;
    }
    
    /**
     * Tạo QR code với template đẹp hơn
     */
    public function generateQRUrlWithTemplate($amount, $description = '', $transactionId = '', $template = 'compact') {
        if (!$this->validateAmount($amount)) {
            throw new Exception('Số tiền không hợp lệ');
        }
        
        $addInfo = $this->formatDescription($description, $transactionId);
        
        // Sử dụng template
        $qrUrl = $this->apiUrl . $this->bankCode . '/' . $this->accountNumber . '/' . $amount . '.png';
        
        $params = [
            'addInfo' => $addInfo,
            'accountName' => $this->accountName,
            'template' => $template
        ];
        
        $qrUrl .= '?' . http_build_query($params);
        
        return $qrUrl;
    }
    
    /**
     * Tạo QR code với logo ngân hàng
     */
    public function generateQRUrlWithLogo($amount, $description = '', $transactionId = '') {
        return $this->generateQRUrlWithTemplate($amount, $description, $transactionId, 'compact2');
    }
    
    /**
     * Tạo QR code kích thước tùy chỉnh
     */
    public function generateQRUrlCustomSize($amount, $description = '', $transactionId = '', $width = 300, $height = 300) {
        $baseUrl = $this->generateQRUrl($amount, $description, $transactionId);
        
        // Thêm kích thước
        $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
        $baseUrl .= $separator . 'width=' . $width . '&height=' . $height;
        
        return $baseUrl;
    }
    
    /**
     * Lấy thông tin chi tiết giao dịch
     */
    public function getTransactionDetails($amount, $description = '', $transactionId = '') {
        $addInfo = $this->formatDescription($description, $transactionId);
        
        return [
            'bank_code' => $this->bankCode,
            'bank_name' => $this->supportedBanks[$this->bankCode] ?? 'Unknown Bank',
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0, ',', '.') . ' VND',
            'description' => $addInfo,
            'qr_url' => $this->generateQRUrl($amount, $description, $transactionId)
        ];
    }
    
    /**
     * Validate số tiền
     */
    public function validateAmount($amount) {
        return is_numeric($amount) && $amount > 0 && $amount <= 500000000; // Max 500M VND
    }
    
    /**
     * Validate mã ngân hàng
     */
    public function validateBankCode($bankCode) {
        return array_key_exists(strtoupper($bankCode), $this->supportedBanks);
    }
    
    /**
     * Format description với transaction ID
     */
    private function formatDescription($description, $transactionId) {
        $addInfo = $transactionId;
        
        if (!empty($description)) {
            // Loại bỏ ký tự đặc biệt và giới hạn độ dài
            $cleanDescription = $this->cleanDescription($description);
            $addInfo = $transactionId . ' ' . $cleanDescription;
        }
        
        // Giới hạn độ dài tối đa 25 ký tự (theo quy định VietQR)
        return substr($addInfo, 0, 25);
    }
    
    /**
     * Làm sạch description
     */
    private function cleanDescription($description) {
        // Loại bỏ dấu tiếng Việt
        $clean = $this->removeVietnameseAccents($description);
        
        // Chỉ giữ lại chữ cái, số và khoảng trắng
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', '', $clean);
        
        // Loại bỏ khoảng trắng thừa
        $clean = preg_replace('/\s+/', ' ', trim($clean));
        
        return strtoupper($clean);
    }
    
    /**
     * Loại bỏ dấu tiếng Việt
     */
    private function removeVietnameseAccents($str) {
        $accents = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ'
        ];
        
        $replacements = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];
        
        return str_replace($accents, $replacements, $str);
    }
    
    /**
     * Lấy danh sách ngân hàng hỗ trợ
     */
    public function getSupportedBanks() {
        return $this->supportedBanks;
    }
    
    /**
     * Test kết nối API VietQR
     */
    public function testConnection($amount = 10000) {
        try {
            $testUrl = $this->generateQRUrl($amount, 'Test connection', 'TEST_' . time());
            
            // Kiểm tra URL có accessible không
            $headers = @get_headers($testUrl);
            
            if ($headers && strpos($headers[0], '200') !== false) {
                return [
                    'success' => true,
                    'message' => 'Kết nối VietQR API thành công',
                    'test_url' => $testUrl
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể kết nối đến VietQR API',
                    'test_url' => $testUrl
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi test connection: ' . $e->getMessage()
            ];
        }
    }
}
?>
