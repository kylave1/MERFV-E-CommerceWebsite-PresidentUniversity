<?php
require_once '../config.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Akses ditolak', 'error');
}

$page_title = 'Laporan Pengguna';

// Handle Aksi (Misal: Tandai Selesai)
if(isset($_GET['action']) && $_GET['action'] == 'resolve' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved' WHERE report_id = ?");
    $stmt->execute([$_GET['id']]);
    redirect('reports.php', 'Laporan telah diselesaikan');
}

$reports = $pdo->query("
    SELECT r.*, u.full_name as reporter_name 
    FROM reports r 
    JOIN users u ON r.user_id = u.user_id 
    ORDER BY r.created_at DESC
")->fetchAll();

include 'admin-header.php';
?>

<div class="admin-header">
    <h1>Laporan Masuk</h1>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelapor</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $report): ?>
            <tr>
                <td>#<?php echo $report['report_id']; ?></td>
                <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                <td><?php echo htmlspecialchars($report['reason']); ?></td>
                <td>
                    <span class="badge badge-<?php echo $report['status'] == 'pending' ? 'warning' : 'success'; ?>">
                        <?php echo ucfirst($report['status']); ?>
                    </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($report['created_at'])); ?></td>
                <td>
                    <?php if($report['status'] == 'pending'): ?>
                        <a href="?action=resolve&id=<?php echo $report['report_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Tandai laporan ini sebagai selesai?')">Selesaikan</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'admin-footer.php'; ?>