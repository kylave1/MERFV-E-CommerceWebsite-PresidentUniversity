<?php
require_once '../config.php';

// Cek Login Admin
if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Edit Produk';
$product_id = $_GET['id'] ?? 0;

// Ambil data produk berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    redirect('products.php', 'Produk tidak ditemukan', 'error');
}

// Handle Update Data
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $discounted_price = !empty($_POST['discounted_price']) ? $_POST['discounted_price'] : null;
    $category = $_POST['category'];
    $stock = (int)$_POST['stock'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Logika Upload Gambar (Opsional)
    $image_name = $product['image']; // Default pakai gambar lama
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            // Buat nama file unik
            $new_image_name = 'product_' . time() . '.' . $filetype;
            $upload_path = '../assets/images/uploads/' . $new_image_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Hapus gambar lama jika ada dan bukan default
                if($product['image'] && file_exists('../assets/images/uploads/' . $product['image'])) {
                    unlink('../assets/images/uploads/' . $product['image']);
                }
                $image_name = $new_image_name;
            }
        }
    }

    // Update database
    $update = $pdo->prepare("
        UPDATE products 
        SET name = ?, description = ?, price = ?, discounted_price = ?, category = ?, stock = ?, is_available = ?, image = ? 
        WHERE product_id = ?
    ");
    
    if($update->execute([$name, $description, $price, $discounted_price, $category, $stock, $is_available, $image_name, $product_id])) {
        redirect('products.php', 'Produk berhasil diperbarui');
    }
}

include 'admin-header.php';
?>

<!-- Page Header -->
<div class="admin-header">
    <h1>Edit Produk: <?php echo htmlspecialchars($product['name']); ?></h1>
    <div class="admin-actions">
        <a href="products.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Form Edit Produk -->
<div class="admin-form" style="max-width: 800px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nama Produk</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Deskripsi Produk</label>
            <textarea name="description" class="form-control" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Harga Normal (Rp)</label>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Harga Diskon (Opsional)</label>
                <input type="number" name="discounted_price" value="<?php echo $product['discounted_price']; ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Kategori</label>
                <select name="category" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="food" <?php echo $product['category'] == 'food' ? 'selected' : ''; ?>>Food</option>
                    <option value="preloved" <?php echo $product['category'] == 'preloved' ? 'selected' : ''; ?>>Preloved</option>
                    <option value="service" <?php echo $product['category'] == 'service' ? 'selected' : ''; ?>>Service</option>
                    <option value="urgent" <?php echo $product['category'] == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Stok</label>
                <input type="number" name="stock" value="<?php echo $product['stock'] ?? 1; ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Gambar Produk</label>
            <?php if($product['image']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo UPLOAD_PATH . $product['image']; ?>" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <small style="color: #666; display: block; margin-top: 5px;">Biarkan kosong jika tidak ingin mengubah gambar.</small>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="is_available" value="1" <?php echo $product['is_available'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                Tampilkan Produk (Tersedia)
            </label>
        </div>
        
        <div style="border-top: 1px solid #eee; padding-top: 20px;">
            <button type="submit" class="btn btn-primary" style="background: #4C3C27; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include 'admin-footer.php'; ?>