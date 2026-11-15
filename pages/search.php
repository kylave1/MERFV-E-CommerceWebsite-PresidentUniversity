<?php
require_once '../config.php';

$page_title = 'Hasil Pencarian';

// Tangkap kata kunci dari URL
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

// Jika ada kata kunci, cari di database
if(!empty($query)) {
    try {
        // Menggunakan LIKE untuk mencari nama atau deskripsi produk
        $stmt = $pdo->prepare("
            SELECT p.*, u.full_name 
            FROM products p 
            JOIN users u ON p.seller_id = u.user_id 
            WHERE p.is_available = 1 
            AND (p.name LIKE ? OR p.description LIKE ?) 
            ORDER BY p.created_at DESC
        ");
        
        $keyword = "%" . $query . "%";
        $stmt->execute([$keyword, $keyword]);
        $results = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Search Error: " . $e->getMessage());
    }
}

include '../header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px; min-height: 60vh;">
    
    <!-- Header Hasil Pencarian -->
    <div style="margin-bottom: 30px; border-bottom: 2px solid #F0EDE5; padding-bottom: 15px;">
        <h1 style="font-size: 32px; color: #2C2416; margin-bottom: 10px;">Hasil Pencarian</h1>
        
        <?php if(!empty($query)): ?>
            <p style="color: #6D6D6D; font-size: 16px;">
                Menampilkan hasil untuk: <strong style="color: #4C3C27;">"<?php echo htmlspecialchars($query); ?>"</strong> 
                (<?php echo count($results); ?> produk ditemukan)
            </p>
        <?php else: ?>
            <p style="color: #6D6D6D; font-size: 16px;">Silakan masukkan kata kunci pencarian pada kolom di beranda.</p>
        <?php endif; ?>
    </div>

    <!-- Grid Produk -->
    <?php if(!empty($query) && count($results) > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
            
            <?php foreach($results as $product): ?>
                <!-- Card Produk -->
                <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.03)';">
                    
                    <!-- Perhatikan tambahan ../ agar gambar terbaca dari folder pages/ -->
                    <img src="../<?php echo UPLOAD_PATH . $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    
                    <div style="padding: 15px; flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </span>
                        
                        <h3 style="font-size: 16px; font-weight: 700; color: #333; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>

                        <div style="font-size: 18px; font-weight: 800; color: #4C3C27; margin-top: auto;">
                            <?php echo formatCurrency($product['discounted_price'] ?? $product['price']); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            
        </div>
        
    <?php elseif(!empty($query) && count($results) == 0): ?>
        
        <!-- Tampilan Jika Produk Tidak Ditemukan -->
        <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 16px; border: 1px dashed #ddd;">
            <i class="fas fa-search-minus" style="font-size: 60px; color: #ccc; margin-bottom: 15px;"></i>
            <h2 style="font-size: 22px; color: #333; margin-bottom: 10px;">Produk Tidak Ditemukan</h2>
            <p style="color: #777; margin-bottom: 20px;">Maaf, kami tidak dapat menemukan produk dengan kata kunci "<?php echo htmlspecialchars($query); ?>".</p>
            <a href="<?php echo SITE_URL; ?>index.php" style="display: inline-block; padding: 12px 25px; background: #4C3C27; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Kembali ke Beranda</a>
        </div>

    <?php endif; ?>
    
</div>

<?php include '../footer.php'; ?>