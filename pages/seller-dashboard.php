<?php
require_once '../config.php';

// Pastikan user sudah login dan rolenya 'seller' (atau 'admin' untuk testing)
if(!isLoggedIn() || ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin')) {
    redirect('../index.php', 'Akses ditolak. Halaman ini hanya untuk Penjual.', 'error');
}

$page_title = 'Dashboard Penjual';
$seller_id = $_SESSION['user_id'];

// Handle Delete Product (Soft Delete / Sembunyikan)
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $prod_id = $_GET['id'];
    // Pastikan produk ini benar-benar milik seller yang login
    $stmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE product_id = ? AND seller_id = ?");
    if($stmt->execute([$prod_id, $seller_id])) {
        $_SESSION['success_message'] = 'Produk berhasil dinonaktifkan';
        header('Location: seller-dashboard.php');
        exit();
    }
}

// ==========================================
// AMBIL DATA DARI DATABASE (DENGAN ERROR HANDLING)
// ==========================================
try {
    // 1. Hitung Total Produk
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $total_products = $stmt->fetchColumn();

    // 2. Hitung Produk Aktif (Jika kolom is_available ada)
    // Jika error, berarti kolom is_available tidak ada, kita pakai total saja
    $active_products = $total_products; 
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND is_available = 1");
        $stmt->execute([$seller_id]);
        $active_products = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Abaikan jika kolom is_available tidak ada
    }

    // 3. Ambil Rata-rata Rating (Jika tabel ratings ada)
    $avg_rating = '0.0';
    try {
        $stmt = $pdo->prepare("
            SELECT AVG(r.rating_value) 
            FROM ratings r 
            JOIN products p ON r.product_id = p.product_id 
            WHERE p.seller_id = ?
        ");
        $stmt->execute([$seller_id]);
        $rating_res = $stmt->fetchColumn();
        if($rating_res) $avg_rating = number_format($rating_res, 1);
    } catch (PDOException $e) {
        // Abaikan jika tabel ratings belum dibuat
    }

    // 4. Ambil Daftar Produk Milik Seller
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM products p 
        WHERE p.seller_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$seller_id]);
    $my_products = $stmt->fetchAll();

} catch (PDOException $e) {
    // Jika query utama gagal, tampilkan pesan error
    die("Terjadi kesalahan pada database: " . $e->getMessage() . "<br>Pastikan tabel 'products' sudah dibuat dengan benar.");
}

include '../header.php';
?>

<style>
    /* ==== STYLING DASHBOARD SELLER ==== */
    body {
        background-color: #f8f9fa; /* Warna background abu sangat muda */
    }

    .seller-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        display: flex;
        gap: 25px;
        align-items: flex-start;
    }

    /* --- SIDEBAR KIRI --- */
    .seller-sidebar {
        width: 260px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        padding: 25px 0;
        flex-shrink: 0;
        border: 1px solid #eee;
    }

    .seller-profile {
        text-align: center;
        padding: 0 20px 25px;
        border-bottom: 1px solid #f0f0f0;
        margin-bottom: 15px;
    }

    .seller-profile img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 3px solid #f8f4f0;
    }

    .seller-profile h4 {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: #333;
    }

    .seller-profile small {
        color: #888;
        font-size: 13px;
    }

    .seller-menu {
        display: flex;
        flex-direction: column;
    }

    .seller-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 25px;
        color: #555;
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        transition: 0.2s;
        border-left: 3px solid transparent;
    }

    .seller-menu a:hover, .seller-menu a.active {
        background: #fcfaf8;
        color: #4C3C27;
        border-left-color: #4C3C27;
    }

    /* --- KONTEN KANAN --- */
    .seller-content {
        flex: 1;
        min-width: 0; /* Mencegah tabel meluber */
    }

    .page-title {
        font-size: 26px;
        font-weight: 700;
        color: #222;
        margin: 0 0 25px 0;
    }

    /* Kartu Statistik */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.produk { background: #e3f2fd; color: #1976d2; }
    .stat-icon.aktif { background: #e8f5e9; color: #388e3c; }
    .stat-icon.rating { background: #fff8e1; color: #fbc02d; }

    .stat-info h3 { margin: 0 0 5px 0; font-size: 22px; font-weight: 700; color: #333; }
    .stat-info p { margin: 0; color: #777; font-size: 13px; }

    /* Tabel Produk */
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #eee;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eee;
    }

    .table-header h3 { margin: 0; font-size: 18px; font-weight: 600; color: #333; }

    .btn-add {
        background: #4C3C27;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
    }
    .btn-add:hover { background: #300C0C; }

    .table-responsive { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px 25px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #fafafa; font-weight: 600; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { font-size: 14px; color: #333; vertical-align: middle; }

    .product-info { display: flex; align-items: center; gap: 15px; }
    .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
    .product-name { font-weight: 600; color: #222; margin-bottom: 3px; display: block; }
    .product-date { font-size: 12px; color: #888; }

    .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
    .badge-active { background: #e8f5e9; color: #2e7d32; }
    .badge-inactive { background: #ffebee; color: #c62828; }

    .action-btns { display: flex; gap: 8px; }
    .action-btn {
        width: 35px; height: 35px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px; color: #fff; text-decoration: none; transition: 0.2s; border: none; cursor: pointer;
    }
    .btn-edit { background: #fbc02d; }
    .btn-edit:hover { background: #f9a825; }
    .btn-delete { background: #ef5350; }
    .btn-delete:hover { background: #d32f2f; }

    /* Responsive */
    @media (max-width: 992px) {
        .seller-wrapper { flex-direction: column; }
        .seller-sidebar { width: 100%; }
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="seller-wrapper">
    
    <!-- SIDEBAR KIRI -->
    <div class="seller-sidebar">
        <div class="seller-profile">
            <!-- Menggunakan fungsi getUserAvatar atau default jika tidak ada -->
            <img src="<?php echo function_exists('getUserAvatar') ? getUserAvatar($_SESSION['user_id']) : '../assets/images/default-avatar.png'; ?>" alt="Profile">
            <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
            <small>Seller Terverifikasi</small>
        </div>
        <div class="seller-menu">
            <a href="seller-dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="upload-product.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
            <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profil Toko</a>
            <a href="../auth/logout.php" style="color: #d32f2f; margin-top: 10px;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
    
    <!-- KONTEN KANAN -->
    <div class="seller-content">
        <h1 class="page-title">Dashboard Penjual</h1>
        
        <!-- TAMPILKAN PESAN SUKSES JIKA ADA -->
        <?php if(isset($_SESSION['success_message'])): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2e7d32;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <!-- STATISTIK -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon produk"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_products); ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon aktif"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($active_products); ?></h3>
                    <p>Produk Aktif</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon rating"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3><?php echo $avg_rating; ?> <span style="font-size: 14px; color: #888;">/ 5.0</span></h3>
                    <p>Rating Toko</p>
                </div>
            </div>
        </div>
        
        <!-- TABEL PRODUK -->
        <div class="table-card">
            <div class="table-header">
                <h3>Daftar Produk Anda</h3>
                <a href="upload-product.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Baru</a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($my_products)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 50px; color: #888;">
                                    <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 15px; color: #ddd; display: block;"></i>
                                    <p style="margin: 0; font-size: 16px;">Anda belum memiliki produk.</p>
                                    <p style="margin: 5px 0 0 0; font-size: 14px;">Silakan klik tombol "Tambah Baru" untuk mulai berjualan.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($my_products as $prod): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <!-- Cek apakah gambarnya ada, jika tidak pakai placeholder -->
                                        <img src="<?php echo (file_exists('../' . UPLOAD_PATH . $prod['image']) && $prod['image'] != '') ? '../' . UPLOAD_PATH . $prod['image'] : '../assets/images/default-product.jpg'; ?>" class="product-img" alt="img">
                                        <div>
                                            <span class="product-name"><?php echo htmlspecialchars($prod['name']); ?></span>
                                            <span class="product-date">Diunggah: <?php echo date('d M Y', strtotime($prod['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="text-transform: capitalize; background: #f0f0f0; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php echo $prod['category']; ?></span></td>
                                <td>
                                    <strong style="color: #4C3C27;"><?php echo formatCurrency($prod['price']); ?></strong>
                                    <?php if(!empty($prod['discounted_price']) && $prod['discounted_price'] > 0): ?>
                                        <br><small style="color: #d32f2f; text-decoration: line-through;"><?php echo formatCurrency($prod['discounted_price']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($prod['is_available']) && $prod['is_available'] == 1): ?>
                                        <span class="badge badge-active">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <!-- Pastikan file edit-product.php untuk seller sudah ada -->
                                        <a href="edit-product.php?id=<?php echo $prod['product_id']; ?>" class="action-btn btn-edit" title="Edit Produk"><i class="fas fa-edit"></i></a>
                                        
                                        <?php if(!isset($prod['is_available']) || $prod['is_available'] == 1): ?>
                                            <a href="?action=delete&id=<?php echo $prod['product_id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus/menonaktifkan produk ini?')" title="Nonaktifkan Produk"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<?php include '../footer.php'; ?>