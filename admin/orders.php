<?php
require_once '../config.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Log Klik WhatsApp';

// Mengambil data (Asumsi: klik WA disimpan di tabel orders dengan status 'wa_click')
// Jika tabelmu berbeda, sesuaikan query di bawah ini
try {
    $stmt = $pdo->query("
        SELECT o.*, u.full_name as customer_name, p.name as product_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        LEFT JOIN products p ON o.product_id = p.product_id
        ORDER BY o.created_at DESC
    ");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback jika tabel orders tidak punya product_id
    $stmt = $pdo->query("
        SELECT o.*, u.full_name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.created_at DESC
    ");
    $logs = $stmt->fetchAll();
}

include 'admin-header.php';
?>

<div class="admin-header">
    <h1>Log Klik WhatsApp</h1>
    <div class="admin-actions">
        <span style="font-size: 14px; color: #666;">
            <i class="fas fa-info-circle"></i> Data calon pembeli yang menghubungi penjual via WA
        </span>
    </div>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pengunjung / Pembeli</th>
                <th>Produk yang Dilihat</th>
                <th>Waktu Klik WA</th>
                <th>Status Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($logs)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 30px;">
                        <div style="text-align: center; color: #666;">
                            <i class="fab fa-whatsapp" style="font-size: 40px; margin-bottom: 10px; color: #25D366;"></i>
                            <p>Belum ada pengunjung yang mengklik tombol WhatsApp.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td>#<?php echo $log['order_id'] ?? $log['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($log['customer_name'] ?? 'Guest'); ?></strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($log['product_name'] ?? '-'); ?>
                    </td>
                    <td>
                        <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                    </td>
                    <td>
                        <!-- Kamu bisa ubah dropdown ini untuk tracking manual -->
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="log_id" value="<?php echo $log['order_id'] ?? $log['id']; ?>">
                            <select name="status" class="form-control" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="baru" <?php echo ($log['status'] ?? '') == 'baru' ? 'selected' : ''; ?>>Baru (Belum dihubungi)</option>
                                <option value="dihubungi" <?php echo ($log['status'] ?? '') == 'dihubungi' ? 'selected' : ''; ?>>Sudah Dihubungi</option>
                                <option value="deal" <?php echo ($log['status'] ?? '') == 'deal' ? 'selected' : ''; ?>>Deal / Terjual</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary" style="background: #4C3C27; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Simpan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'admin-footer.php'; ?>