<?php
require_once '../config.php';

// Check if user is admin
if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Kelola Produk';

// Handle actions
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;

if($action == 'delete' && $product_id) {
    $stmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE product_id = ?");
    if($stmt->execute([$product_id])) redirect('products.php', 'Produk berhasil dihapus');
}

if($action == 'restore' && $product_id) {
    $stmt = $pdo->prepare("UPDATE products SET is_available = 1 WHERE product_id = ?");
    if($stmt->execute([$product_id])) redirect('products.php', 'Produk berhasil dipulihkan');
}

if($action == 'feature' && $product_id) {
    $stmt = $pdo->prepare("UPDATE products SET is_featured = 1 WHERE product_id = ?");
    if($stmt->execute([$product_id])) redirect('products.php', 'Produk berhasil ditandai sebagai unggulan');
}

if($action == 'unfeature' && $product_id) {
    $stmt = $pdo->prepare("UPDATE products SET is_featured = 0 WHERE product_id = ?");
    if($stmt->execute([$product_id])) redirect('products.php', 'Produk berhasil dihapus dari unggulan');
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$seller_id = $_GET['seller_id'] ?? ''; // Parameter baru untuk filter seller
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Ambil daftar semua seller untuk dropdown filter
$sellers = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'seller' ORDER BY full_name ASC")->fetchAll();

// Build Base Query
$base_sql = " FROM products p JOIN users u ON p.seller_id = u.user_id WHERE 1=1";
$params = [];

if($search) {
    $base_sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($category) {
    $base_sql .= " AND p.category = ?";
    $params[] = $category;
}

if($status === 'active') {
    $base_sql .= " AND p.is_available = 1";
} elseif($status === 'inactive') {
    $base_sql .= " AND p.is_available = 0";
}

// Filter berdasarkan Penjual/Seller yang dipilih
if($seller_id) {
    $base_sql .= " AND p.seller_id = ?";
    $params[] = $seller_id;
}

// 1. QUERY MENGHITUNG TOTAL DATA
$count_sql = "SELECT COUNT(*) " . $base_sql;
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();

// 2. QUERY MENGAMBIL DATA PRODUK
$sql = "
    SELECT p.*, u.full_name, u.email,
    (SELECT COUNT(*) FROM ratings WHERE product_id = p.product_id) as rating_count,
    (SELECT AVG(rating_value) FROM ratings WHERE product_id = p.product_id) as avg_rating
    " . $base_sql . " 
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// PANGGIL HEADER ADMIN
include 'admin-header.php';
?>

<!-- Page Header -->
<div class="admin-header">
    <h1>Kelola Produk</h1>
    <div class="admin-actions">
        <a href="../pages/upload-product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>

<!-- Filters -->
<div class="admin-filters">
    <form method="GET" class="filter-form">
        <div class="filter-row" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Cari produk..." 
                   value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="flex: 1; min-width: 200px;">
            
            <!-- DROPDOWN FILTER SELLER -->
            <select name="seller_id" class="form-control" style="flex: 1; min-width: 150px;">
                <option value="">Semua Penjual</option>
                <?php foreach($sellers as $seller): ?>
                    <option value="<?php echo $seller['user_id']; ?>" <?php echo $seller_id == $seller['user_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($seller['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category" class="form-control" style="flex: 1; min-width: 120px;">
                <option value="">Semua Kategori</option>
                <option value="food" <?php echo $category == 'food' ? 'selected' : ''; ?>>Food</option>
                <option value="preloved" <?php echo $category == 'preloved' ? 'selected' : ''; ?>>Preloved</option>
                <option value="service" <?php echo $category == 'service' ? 'selected' : ''; ?>>Service</option>
                <option value="urgent" <?php echo $category == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
            </select>
            
            <select name="status" class="form-control" style="flex: 1; min-width: 120px;">
                <option value="">Semua Status</option>
                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Aktif</option>
                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
            </select>
            
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fas fa-filter"></i> Filter
            </button>
            
            <a href="products.php" class="btn btn-outline" style="white-space: nowrap;">Reset</a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produk</th>
                <th>Penjual</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($products)): ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada produk ditemukan</td>
            </tr>
            <?php else: ?>
            <?php foreach($products as $product): ?>
            <tr>
                <td>#<?php echo $product['product_id']; ?></td>
                <td>
                    <div class="product-cell">
                        <img src="../<?php echo UPLOAD_PATH . $product['image']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <div>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <small><?php echo htmlspecialchars(substr($product['description'], 0, 30)); ?>...</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        <div>
                            <strong><?php echo htmlspecialchars($product['full_name']); ?></strong>
                            <small><?php echo htmlspecialchars($product['email']); ?></small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-category-<?php echo $product['category']; ?>">
                        <?php echo ucfirst($product['category']); ?>
                    </span>
                </td>
                <td>
                    <strong><?php echo formatCurrency($product['price']); ?></strong>
                    <?php if($product['discounted_price']): ?>
                    <br>
                    <small style="color: #dc3545;">
                        <?php echo formatCurrency($product['discounted_price']); ?>
                    </small>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="rating-display">
                        <div class="stars">
                            <?php
                            $rating = $product['avg_rating'] ?? 0;
                            for($i = 1; $i <= 5; $i++):
                                if($i <= floor($rating)) echo '<i class="fas fa-star"></i>';
                                elseif($i - 0.5 <= $rating) echo '<i class="fas fa-star-half-alt"></i>';
                                else echo '<i class="far fa-star"></i>';
                            endfor;
                            ?>
                        </div>
                        <small>(<?php echo $product['rating_count']; ?>)</small>
                    </div>
                </td>
                <td>
                    <?php if($product['is_available']): ?>
                    <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                    <span class="badge badge-danger">Nonaktif</span>
                    <?php endif; ?>
                    
                    <?php if($product['is_featured']): ?>
                    <span class="badge badge-warning">Unggulan</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="../pages/product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn-action btn-view" title="Lihat"><i class="fas fa-eye"></i></a>
                        
                        <a href="edit-product.php?id=<?php echo $product['product_id']; ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                        
                        <?php if($product['is_featured']): ?>
                        <a href="?id=<?php echo $product['product_id']; ?>&action=unfeature" class="btn-action btn-warning" title="Hapus Unggulan"><i class="fas fa-star"></i></a>
                        <?php else: ?>
                        <a href="?id=<?php echo $product['product_id']; ?>&action=feature" class="btn-action btn-warning" title="Jadikan Unggulan"><i class="far fa-star"></i></a>
                        <?php endif; ?>
                        
                        <?php if($product['is_available']): ?>
                        <a href="?id=<?php echo $product['product_id']; ?>&action=delete" class="btn-action btn-danger" title="Nonaktifkan" onclick="return confirm('Yakin ingin menonaktifkan produk ini?')"><i class="fas fa-ban"></i></a>
                        <?php else: ?>
                        <a href="?id=<?php echo $product['product_id']; ?>&action=restore" class="btn-action btn-success" title="Aktifkan"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if($total_products > $limit): ?>
<div class="admin-pagination">
    <?php
    $total_pages = ceil($total_products / $limit);
    $query_params = $_GET;
    unset($query_params['page']);
    $query_string = http_build_query($query_params);
    ?>
    
    <?php if($page > 1): ?>
    <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>" class="pagination-item"><i class="fas fa-chevron-left"></i> Sebelumnya</a>
    <?php endif; ?>
    
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $start_page + 4);
    for($i = $start_page; $i <= $end_page; $i++):
    ?>
    <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if($page < $total_pages): ?>
    <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="pagination-item">Berikutnya <i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
/* Tambahan style agar filter sejajar dan rapi */
.filter-row {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}
.admin-filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.product-cell { display: flex; align-items: center; gap: 10px; }
.product-cell img { width: 50px; height: 50px; border-radius: 5px; object-fit: cover; }
.badge-category-food { background: #28a745; color: white; }
.badge-category-preloved { background: #17a2b8; color: white; }
.badge-category-service { background: #ffc107; color: #212529; }
.badge-category-urgent { background: #dc3545; color: white; }
.rating-display { display: flex; align-items: center; gap: 5px; }
.rating-display .stars { color: #ffc107; font-size: 12px; }
</style>

<?php include 'admin-footer.php'; ?>