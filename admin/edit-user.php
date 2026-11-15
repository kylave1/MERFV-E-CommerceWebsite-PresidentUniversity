<?php
require_once '../config.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Edit Pengguna';
$user_id = $_GET['id'] ?? 0;

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user) {
    redirect('users.php', 'Pengguna tidak ditemukan', 'error');
}

// Handle Update
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    
    $update = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, role=?, status=? WHERE user_id=?");
    if($update->execute([$full_name, $email, $phone, $role, $status, $user_id])) {
        redirect('users.php', 'Data pengguna berhasil diperbarui');
    }
}

// Panggil Header Admin
include 'admin-header.php';
?>

<!-- Page Header -->
<div class="admin-header">
    <h1>Edit Pengguna: <?php echo htmlspecialchars($user['full_name']); ?></h1>
    <div class="admin-actions">
        <a href="users.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Form Edit -->
<div class="admin-form" style="max-width: 800px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <form method="POST">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nama Lengkap</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nomor Telepon</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        
        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Role</label>
                <select name="role" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="seller" <?php echo $user['role'] == 'seller' ? 'selected' : ''; ?>>Seller</option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Status</label>
                <select name="status" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                    <option value="banned" <?php echo $user['status'] == 'banned' ? 'selected' : ''; ?>>Diblokir (Banned)</option>
                </select>
            </div>
        </div>
        
        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <button type="submit" class="btn btn-primary" style="background: #4C3C27; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php 
// Panggil Footer Admin
include 'admin-footer.php'; 
?>