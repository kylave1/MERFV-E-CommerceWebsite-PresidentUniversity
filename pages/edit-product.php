<?php
require_once '../config.php';

// Cek Login
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Silakan login terlebih dahulu';
    header('Location: ../auth/signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Ambil ID Produk dari URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($product_id === 0) {
    header('Location: seller-dashboard.php');
    exit();
}

// Ambil data produk dari database
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Cek apakah produk ada
if(!$product) {
    $_SESSION['error_message'] = 'Produk tidak ditemukan.';
    header('Location: seller-dashboard.php');
    exit();
}

// KEAMANAN: Pastikan yang mengedit adalah pemilik produk atau Admin
if($product['seller_id'] != $user_id && $user_role != 'admin') {
    $_SESSION['error_message'] = 'Anda tidak memiliki akses untuk mengedit produk ini.';
    header('Location: seller-dashboard.php');
    exit();
}

$page_title = 'Edit Produk - ' . htmlspecialchars($product['name']);
$errors = [];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'] ?? '';
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $discounted_price = !empty($_POST['discounted_price']) ? floatval($_POST['discounted_price']) : null;
    $stock = intval($_POST['stock'] ?? 1);
    $location = trim($_POST['location']);
    
    // Validasi
    if(empty($name)) $errors[] = 'Nama produk harus diisi';
    if(empty($category)) $errors[] = 'Kategori harus dipilih';
    if(empty($description)) $errors[] = 'Deskripsi produk harus diisi';
    if($price <= 0) $errors[] = 'Harga harus lebih dari 0';
    if(empty($location)) $errors[] = 'Lokasi harus diisi';
    
    // Handle Upload Gambar Baru (Opsional)
    $image_name = $product['image']; // Default pakai gambar lama
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            if($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../' . UPLOAD_PATH . $new_filename;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Hapus gambar lama jika ada (kecuali default)
                    if(!empty($product['image']) && file_exists('../' . UPLOAD_PATH . $product['image'])) {
                        unlink('../' . UPLOAD_PATH . $product['image']);
                    }
                    $image_name = $new_filename;
                } else {
                    $errors[] = 'Gagal upload gambar baru.';
                }
            } else {
                $errors[] = 'Ukuran gambar maksimal 5MB';
            }
        } else {
            $errors[] = 'Format gambar harus JPG, PNG, GIF, atau WebP';
        }
    }
    
    // Jika tidak ada error, Update Database
    if(empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, category = ?, subcategory = ?, description = ?, 
                    price = ?, discounted_price = ?, quantity = ?, location = ?, image = ? 
                WHERE product_id = ?
            ");
            
            if($stmt->execute([
                $name, $category, $subcategory, $description, 
                $price, $discounted_price, $stock, $location, $image_name, $product_id
            ])) {
                $_SESSION['success_message'] = 'Produk berhasil diperbarui!';
                header('Location: seller-dashboard.php');
                exit();
            }
        } catch(PDOException $e) {
            error_log("Edit Product Error: " . $e->getMessage());
            $errors[] = 'Gagal menyimpan perubahan. Silakan coba lagi.';
        }
    }
}

include '../header.php';
?>

<style>
    /* ===== STYLING EDIT PRODUCT ===== */
    .edit-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .edit-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        border: 1px solid #E8E3D9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }

    /* HEADER DI POJOK KIRI */
    .edit-header {
        text-align: left;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #F0EDE5;
    }

    .edit-header h1 {
        font-size: 32px;
        font-weight: 700;
        color: #4C3C27;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .edit-header p {
        color: #6D6D6D;
        font-size: 16px;
        margin: 0;
    }

    /* ERROR BOX */
    .error-box {
        background: #FEF2F2;
        border-left: 4px solid #DC3545;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 30px;
    }

    .error-box ul {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .error-box li {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #DC3545;
        font-size: 14px;
        margin-bottom: 8px;
    }

    /* FORM SECTIONS */
    .form-section {
        margin-bottom: 40px;
        padding-bottom: 40px;
        border-bottom: 1px solid #F0EDE5;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    .section-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #4C3C27;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: #2C2416;
        margin: 0;
    }

    /* FORM GRID */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    label {
        font-size: 14px;
        font-weight: 600;
        color: #2C2416;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .required {
        color: #DC3545;
        font-size: 16px;
    }

    input, select, textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid #E8E3D9;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        background: white;
        box-sizing: border-box;
    }

    input:focus, select:focus, textarea:focus {
        border-color: #C9B59C;
        outline: none;
        box-shadow: 0 0 0 4px rgba(201,181,156,0.1);
    }

    /* PRICE INPUT */
    .price-input {
        display: flex;
        align-items: center;
        border: 1.5px solid #E8E3D9;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .price-input:focus-within {
        border-color: #C9B59C;
        box-shadow: 0 0 0 4px rgba(201,181,156,0.1);
    }

    .currency {
        padding: 14px 16px;
        background: #F5F3EE;
        color: #4C3C27;
        font-weight: 600;
        border-right: 1.5px solid #E8E3D9;
    }

    .price-input input {
        border: none;
        border-radius: 0;
        padding: 14px 16px;
    }

    .price-input input:focus {
        box-shadow: none;
    }

    .help-text {
        font-size: 12px;
        color: #999;
        margin-top: 4px;
    }

    /* IMAGE UPLOAD */
    .current-image-preview {
        margin-bottom: 15px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eee;
        display: inline-block;
    }
    
    .current-image-preview img {
        max-width: 200px;
        max-height: 200px;
        object-fit: cover;
        display: block;
    }

    .image-upload-area {
        border: 2px dashed #E8E3D9;
        border-radius: 16px;
        padding: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #F9F7F4;
        min-height: 150px;
    }

    .image-upload-area:hover, .image-upload-area.dragover {
        border-color: #4C3C27;
        background: #F5F3EE;
    }

    .upload-prompt { text-align: center; }
    .upload-prompt i { font-size: 36px; color: #C9B59C; margin-bottom: 10px; }
    .upload-prompt h4 { font-size: 16px; font-weight: 600; color: #2C2416; margin-bottom: 5px; }
    .upload-prompt p { font-size: 13px; color: #888; margin: 0; }

    .image-upload-area input[type="file"] { display: none; }

    .image-preview {
        margin-top: 15px;
    }

    .preview-item {
        position: relative;
        display: inline-block;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #C9B59C;
    }

    .preview-item img { max-width: 200px; max-height: 200px; object-fit: cover; display: block; }

    .remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(0,0,0,0.7);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* FORM ACTIONS */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-submit {
        flex: 1;
        background: linear-gradient(135deg, #4C3C27, #300C0C);
        color: white;
        border: none;
        padding: 16px 30px;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(76,60,39,0.2);
    }

    .btn-cancel {
        padding: 16px 30px;
        border: 1.5px solid #E8E3D9;
        border-radius: 40px;
        color: #6D6D6D;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-cancel:hover {
        background: #F5F3EE;
        border-color: #C9B59C;
        color: #2C2416;
    }

    @media (max-width: 768px) {
        .edit-card { padding: 30px 20px; }
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width { grid-column: span 1; }
        .form-actions { flex-direction: column; }
    }
</style>

<div class="edit-container">
    <div class="edit-card">
        
        <!-- HEADER -->
        <div class="edit-header">
            <h1>Edit Produk</h1>
            <p>Perbarui informasi produk Anda di MERF Marketplace</p>
        </div>
        
        <?php if(!empty($errors)): ?>
        <div class="error-box">
            <ul>
                <?php foreach($errors as $error): ?>
                <li><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            
            <!-- BAGIAN 1: INFORMASI DASAR -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-number">1</div>
                    <h3>Informasi Dasar</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Nama Produk/Jasa <span class="required">*</span></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori <span class="required">*</span></label>
                        <select name="category" id="categorySelect" required>
                            <option value="" disabled>Pilih Kategori</option>
                            <option value="food" <?php echo $product['category'] == 'food' ? 'selected' : ''; ?>>🍱 Makanan & Minuman</option>
                            <option value="preloved" <?php echo $product['category'] == 'preloved' ? 'selected' : ''; ?>>👕 Barang Preloved</option>
                            <option value="service" <?php echo $product['category'] == 'service' ? 'selected' : ''; ?>>💼 Jasa & Layanan</option>
                            <option value="urgent" <?php echo $product['category'] == 'urgent' ? 'selected' : ''; ?>>⚡ Kebutuhan Mendesak</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subkategori (opsional)</label>
                        <select name="subcategory" id="subcategorySelect">
                            <option value="<?php echo htmlspecialchars($product['subcategory'] ?? ''); ?>" selected>
                                <?php echo htmlspecialchars(ucfirst($product['subcategory'] ?? 'Pilih Subkategori')); ?>
                            </option>
                            <!-- Opsi akan diisi oleh JS -->
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Deskripsi <span class="required">*</span></label>
                        <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- BAGIAN 2: HARGA & STOK -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-number">2</div>
                    <h3>Harga & Stok</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Harga <span class="required">*</span></label>
                        <div class="price-input">
                            <span class="currency">Rp</span>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" min="0" step="1000" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga Diskon</label>
                        <div class="price-input">
                            <span class="currency">Rp</span>
                            <input type="number" name="discounted_price" value="<?php echo htmlspecialchars($product['discounted_price'] ?? ''); ?>" min="0" step="1000">
                        </div>
                        <small class="help-text">Kosongkan jika tidak ada diskon</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stock" value="<?php echo htmlspecialchars($product['quantity'] ?? 1); ?>" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi <span class="required">*</span></label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($product['location'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            
            <!-- BAGIAN 3: GAMBAR PRODUK -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-number">3</div>
                    <h3>Gambar Produk</h3>
                </div>
                
                <div class="form-group full-width">
                    <!-- Tampilkan Gambar Saat Ini -->
                    <?php if(!empty($product['image'])): ?>
                    <div>
                        <label style="margin-bottom: 10px;">Gambar Saat Ini:</label>
                        <div class="current-image-preview">
                            <img src="../<?php echo UPLOAD_PATH . $product['image']; ?>" alt="Current Image">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Area Upload Gambar Baru -->
                    <label style="margin-top: 10px;">Ganti Gambar (Opsional):</label>
                    <div class="image-upload-area" id="imageUploadArea">
                        <div class="upload-prompt">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h4>Upload Gambar Baru</h4>
                            <p>Klik atau drag & drop gambar di sini</p>
                            <span class="upload-hint" style="font-size: 12px; color: #999;">Format: JPG, PNG, GIF, WebP (Maks. 5MB)</span>
                        </div>
                        <input type="file" name="image" id="imageInput" accept="image/*">
                    </div>
                    <div class="image-preview" id="imagePreview"></div>
                </div>
            </div>
            
            <!-- TOMBOL AKSI -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="seller-dashboard.php" class="btn-cancel">Batal</a>
            </div>
            
        </form>
    </div>
</div>

<script>
// Data Subkategori
const subcategories = {
    food: [
        { value: 'dimsum', label: '🥟 Dimsum' },
        { value: 'makanan', label: '🍚 Makanan Berat' },
        { value: 'minuman', label: '🥤 Minuman' },
        { value: 'snack', label: '🍪 Snack' },
        { value: 'kue', label: '🍰 Kue & Dessert' },
        { value: 'dessert', label: '🍨 Dessert' }
    ],
    preloved: [
        { value: 'clothes', label: '👕 Pakaian' },
        { value: 'skincare', label: '🧴 Skincare' },
        { value: 'electronics', label: '📱 Elektronik' },
        { value: 'books', label: '📚 Buku' },
        { value: 'accessories', label: '🕶️ Aksesoris' },
        { value: 'shoes', label: '👟 Sepatu' },
        { value: 'bags', label: '👜 Tas' }
    ],
    service: [
        { value: 'courses', label: '📖 Kursus' },
        { value: 'jastip', label: '🛵 Jastip' },
        { value: 'repair', label: '🔧 Perbaikan' },
        { value: 'beauty', label: '💄 Kecantikan' },
        { value: 'cleaning', label: '🧹 Cleaning Service' },
        { value: 'design', label: '🎨 Design' },
        { value: 'laundry', label: '🧺 Laundry' }
    ],
    urgent: [
        { value: 'food', label: '🍱 Makanan' },
        { value: 'items', label: '📦 Barang' },
        { value: 'services', label: '⚡ Jasa' },
        { value: 'transportation', label: '🚗 Transportasi' }
    ]
};

const categorySelect = document.getElementById('categorySelect');
const subSelect = document.getElementById('subcategorySelect');
const currentSubcategory = "<?php echo $product['subcategory'] ?? ''; ?>";

// Fungsi update subkategori
function updateSubcategories() {
    const category = categorySelect.value;
    subSelect.innerHTML = '<option value="">Pilih Subkategori</option>';
    
    if(category && subcategories[category]) {
        subcategories[category].forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.value;
            option.textContent = sub.label;
            // Set selected jika cocok dengan data dari database
            if(sub.value === currentSubcategory) {
                option.selected = true;
            }
            subSelect.appendChild(option);
        });
    }
}

// Jalankan saat halaman dimuat
window.addEventListener('load', updateSubcategories);

// Jalankan saat kategori diubah
categorySelect.addEventListener('change', updateSubcategories);

// Logic Preview Gambar Baru
const uploadArea = document.getElementById('imageUploadArea');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');

uploadArea?.addEventListener('click', function() { imageInput.click(); });
uploadArea?.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
uploadArea?.addEventListener('dragleave', function() { this.classList.remove('dragover'); });
uploadArea?.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    if(e.dataTransfer.files.length) {
        imageInput.files = e.dataTransfer.files;
        handleImagePreview(e.dataTransfer.files[0]);
    }
});

imageInput?.addEventListener('change', function() {
    if(this.files.length) handleImagePreview(this.files[0]);
});

function handleImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.innerHTML = `
            <div class="preview-item">
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage()"><i class="fas fa-times"></i></button>
            </div>
        `;
        uploadArea.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    imageInput.value = '';
    imagePreview.innerHTML = '';
    uploadArea.style.display = 'flex';
}
</script>

<?php include '../footer.php'; ?>