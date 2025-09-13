<?php
header("Content-Type: application/json");

$from = isset($_GET['from']) ? intval($_GET['from']) : 0;
$to = isset($_GET['to']) ? intval($_GET['to']) : 0;

$min = min($from, $to);
$max = max($from, $to);

// ✅ THÊM đường dẫn thư mục chat
$chatDir = __DIR__ . "/../chat";
$fileName = "$chatDir/chat_{$min}_{$max}.json";

if (!file_exists($fileName)) {
    echo json_encode([]);
    exit;
}

$data = json_decode(file_get_contents($fileName), true);
if (!is_array($data)) {
    echo json_encode([]);
    exit;
}

// Chuẩn hóa dữ liệu: chuyển noi_dung -> content nếu cần
foreach ($data as &$row) {
    if (!isset($row['content']) && isset($row['noi_dung'])) {
        $row['content'] = $row['noi_dung'];
        unset($row['noi_dung']);
    }
}
unset($row);

echo json_encode($data);
?>