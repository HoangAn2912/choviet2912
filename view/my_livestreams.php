<?php
include_once("view/header.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?login');
    exit;
}

include_once("model/mLivestream.php");
$mLivestream = new mLivestream();
$livestreams = $mLivestream->getLivestreamsByUserId($_SESSION['user_id']);
?>

<style>
.my-livestreams-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-title {
    text-align: center;
    margin-bottom: 40px;
    color: #333;
}

.livestream-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.livestream-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.livestream-card:hover {
    transform: translateY(-5px);
}

.livestream-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.livestream-content {
    padding: 20px;
}

.livestream-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.livestream-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.livestream-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 12px;
    color: #888;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 11px;
}

.status-chua_bat_dau {
    background: #fff3cd;
    color: #856404;
}

.status-dang_live {
    background: #d4edda;
    color: #155724;
}

.status-da_ket_thuc {
    background: #f8d7da;
    color: #721c24;
}

.livestream-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    flex: 1;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.create-livestream-btn {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 15px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.create-livestream-btn:hover {
    background: #0056b3;
    color: white;
    transform: translateY(-2px);
}
</style>

<div class="my-livestreams-container">
    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-video mr-2"></i>Livestream của tôi</h1>
            <p>Quản lý các livestream bạn đã tạo</p>
        </div>

        <?php if (empty($livestreams)): ?>
            <div class="empty-state">
                <i class="fas fa-video"></i>
                <h3>Chưa có livestream nào</h3>
                <p>Bạn chưa tạo livestream nào. Hãy tạo livestream đầu tiên của bạn!</p>
                <a href="index.php?create-livestream" class="create-livestream-btn">
                    <i class="fas fa-plus mr-2"></i>Tạo livestream mới
                </a>
            </div>
        <?php else: ?>
            <div class="livestream-grid">
                <?php foreach ($livestreams as $livestream): ?>
                    <div class="livestream-card">
                        <img src="img/<?= htmlspecialchars($livestream['image'] ?? 'default-live.jpg') ?>" 
                             alt="<?= htmlspecialchars($livestream['title']) ?>" 
                             class="livestream-image">
                        
                        <div class="livestream-content">
                            <h3 class="livestream-title"><?= htmlspecialchars($livestream['title']) ?></h3>
                            <p class="livestream-description"><?= htmlspecialchars($livestream['description']) ?></p>
                            
                            <div class="livestream-meta">
                                <span><i class="fas fa-calendar mr-1"></i><?= date('d/m/Y H:i', strtotime($livestream['created_date'])) ?></span>
                                <span class="status-badge status-<?= $livestream['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $livestream['status'])) ?>
                                </span>
                            </div>
                            
                            <div class="livestream-actions">
                                <a href="index.php?streamer&id=<?= $livestream['id'] ?>" 
                                   class="btn-action btn-primary">
                                    <i class="fas fa-cog mr-1"></i>Quản lý
                                </a>
                                
                                <?php if ($livestream['status'] == 'chua_bat_dau'): ?>
                                    <a href="index.php?livestream&id=<?= $livestream['id'] ?>" 
                                       class="btn-action btn-success">
                                        <i class="fas fa-play mr-1"></i>Xem
                                    </a>
                                <?php elseif ($livestream['status'] == 'dang_live'): ?>
                                    <a href="index.php?watch&id=<?= $livestream['id'] ?>" 
                                       class="btn-action btn-danger">
                                        <i class="fas fa-play mr-1"></i>Xem Live
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?livestream&id=<?= $livestream['id'] ?>" 
                                       class="btn-action btn-primary">
                                        <i class="fas fa-eye mr-1"></i>Xem lại
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="index.php?create-livestream" class="create-livestream-btn">
                    <i class="fas fa-plus mr-2"></i>Tạo livestream mới
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once("view/footer.php"); ?>