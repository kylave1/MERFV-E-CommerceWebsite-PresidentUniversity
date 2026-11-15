<?php
require_once '../config.php';

// Check if user is admin
if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Pengaturan Website';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach($_POST as $key => $value) {
        if(strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8);
            $setting_value = trim($value);
            
            $stmt = $pdo->prepare("
                INSERT INTO site_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            
            $stmt->execute([$setting_key, $setting_value, $setting_value]);
        }
    }
    
    // Handle checkbox yang tidak terkirim jika tidak dicentang
    $checkboxes = ['enable_registration', 'require_student_id', 'auto_verify_students', 'auto_approve_products', 'maintenance_mode'];
    foreach($checkboxes as $cb) {
        if(!isset($_POST['setting_' . $cb])) {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = 'false' WHERE setting_key = ?");
            $stmt->execute([$cb]);
        }
    }
    
    redirect('settings.php', 'Pengaturan berhasil disimpan');
}

// Get current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// PANGGIL HEADER ADMIN
include 'admin-header.php';
?>

<!-- Page Header -->
<div class="admin-header">
    <h1>Pengaturan Website</h1>
</div>

<!-- Settings Form -->
<div class="admin-form-container" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px;">
    <form method="POST">
        
        <!-- General Settings -->
        <div class="form-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <h3 style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Pengaturan Umum</h3>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Nama Website</label>
                <input type="text" name="setting_site_name" 
                       value="<?php echo htmlspecialchars($settings['site_name'] ?? 'MERF Marketplace'); ?>" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Deskripsi Website</label>
                <textarea name="setting_site_description" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical;"><?php 
                    echo htmlspecialchars($settings['site_description'] ?? 'Platform E-Commerce untuk Mahasiswa President University'); 
                ?></textarea>
            </div>
            
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Email Admin</label>
                    <input type="email" name="setting_admin_email" 
                           value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@merf.com'); ?>" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Currency</label>
                    <input type="text" name="setting_currency" 
                           value="<?php echo htmlspecialchars($settings['currency'] ?? 'IDR'); ?>" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
            </div>
        </div>
        
        <!-- Registration Settings -->
        <div class="form-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <h3 style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Pengaturan Pendaftaran</h3>
            
            <div class="form-group" style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="setting_enable_registration" value="true" id="enable_reg" 
                       <?php echo ($settings['enable_registration'] ?? 'true') == 'true' ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                <label for="enable_reg" style="font-weight: 500; color: #444; cursor: pointer;">Aktifkan Pendaftaran Pengguna Baru</label>
            </div>
            
            <div class="form-group" style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="setting_require_student_id" value="true" id="req_nim"
                       <?php echo ($settings['require_student_id'] ?? 'false') == 'true' ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                <label for="req_nim" style="font-weight: 500; color: #444; cursor: pointer;">Wajibkan NIM untuk Mahasiswa</label>
            </div>
            
            <div class="form-group" style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="setting_auto_verify_students" value="true" id="auto_ver"
                       <?php echo ($settings['auto_verify_students'] ?? 'true') == 'true' ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                <label for="auto_ver" style="font-weight: 500; color: #444; cursor: pointer;">Verifikasi Otomatis untuk Mahasiswa PU</label>
            </div>
        </div>
        
        <!-- Product Settings -->
        <div class="form-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <h3 style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Pengaturan Produk</h3>
            
            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Harga Minimum Produk</label>
                    <input type="number" name="setting_min_product_price" 
                           value="<?php echo htmlspecialchars($settings['min_product_price'] ?? '1000'); ?>" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Maksimal Gambar per Produk</label>
                    <input type="number" name="setting_max_product_images" 
                           value="<?php echo htmlspecialchars($settings['max_product_images'] ?? '5'); ?>" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Ukuran Maksimal Upload (MB)</label>
                <input type="number" name="setting_max_upload_size" 
                       value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '5'); ?>" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="setting_auto_approve_products" value="true" id="auto_app"
                       <?php echo ($settings['auto_approve_products'] ?? 'true') == 'true' ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                <label for="auto_app" style="font-weight: 500; color: #444; cursor: pointer;">Setujui Produk Otomatis</label>
            </div>
        </div>
        
        <!-- Maintenance Mode -->
        <div class="form-section" style="margin-bottom: 30px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Mode Maintenance</h3>
            
            <div class="form-group" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="setting_maintenance_mode" value="true" id="main_mode"
                       <?php echo ($settings['maintenance_mode'] ?? 'false') == 'true' ? 'checked' : ''; ?>
                       onchange="toggleMaintenanceMessage(this)" style="width: 18px; height: 18px; cursor: pointer;">
                <label for="main_mode" style="font-weight: 500; color: #444; cursor: pointer;">Aktifkan Mode Maintenance</label>
            </div>
            
            <div class="form-group" id="maintenanceMessageGroup" 
                 style="<?php echo ($settings['maintenance_mode'] ?? 'false') == 'true' ? '' : 'display: none;'; ?>">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #444;">Pesan Maintenance</label>
                <textarea name="setting_maintenance_message" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical;"><?php 
                    echo htmlspecialchars($settings['maintenance_message'] ?? 'Website sedang dalam perawatan. Silakan kembali beberapa saat lagi.'); 
                ?></textarea>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div style="margin-top: 20px;">
            <button type="submit" style="background: #4C3C27; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: 600; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-save"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
function toggleMaintenanceMessage(checkbox) {
    const messageGroup = document.getElementById('maintenanceMessageGroup');
    if(checkbox.checked) {
        messageGroup.style.display = 'block';
    } else {
        messageGroup.style.display = 'none';
    }
}
</script>

<?php 
// PANGGIL FOOTER ADMIN
include 'admin-footer.php'; 
?>