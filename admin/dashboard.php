<?php
ob_start();
session_start();

require_once dirname(__DIR__) . '/config.php';

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin'){
    header('Location: ../index.php');
    exit();
}

$page_title = 'Admin Dashboard';

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'reported_items' => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
    'today_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'active_sellers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'active'")->fetchColumn()
];

// Get recent activities (HANYA AMBIL 5 DATA AGAR TIDAK KEPANJANGAN)
$stmt = $pdo->query("
    (SELECT 'user' as type, full_name as title, created_at as date FROM users ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'product' as type, name as title, created_at as date FROM products ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'order' as type, CONCAT('Order #', order_code) as title, created_at as date FROM orders ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'report' as type, CONCAT('Report #', report_id) as title, created_at as date FROM reports ORDER BY created_at DESC LIMIT 2)
    ORDER BY date DESC LIMIT 5
");
$recent_activities = $stmt->fetchAll();

// Get chart data (last 7 days agar grafik lebih rapi)
$chart_data = [];
for($i = 7; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $chart_data[] = [
        'date' => date('d M', strtotime($date)), // Format tanggal lebih pendek
        'orders' => $stmt->fetchColumn()
    ];
}

// Get user distribution
$customer_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$seller_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MERF Marketplace</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* CSS TAMBAHAN UNTUK MERAPIKAN DASHBOARD */
        .chart-wrapper {
            position: relative;
            height: 300px; /* Kunci tinggi grafik agar tidak melar */
            width: 100%;
        }
        .scrollable-activities {
            max-height: 350px; /* Batasi tinggi list aktivitas */
            overflow-y: auto;  /* Tambahkan scrollbar jika lebih */
            padding-right: 5px;
        }
        /* Scrollbar custom agar lebih cantik */
        .scrollable-activities::-webkit-scrollbar {
            width: 6px;
        }
        .scrollable-activities::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 10px;
        }
        @media (max-width: 992px) {
            .dashboard-bottom-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'admin-sidebar.php'; ?>
        
        <!-- Admin Content -->
        <div class="admin-content">
            <!-- Page Header -->
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-actions">
                    <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    <a href="../auth/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="stat-icon users"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Pengguna</p>
                        <small>+<?php echo $stats['today_users']; ?> hari ini</small>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="stat-icon products"><i class="fas fa-box"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Total Produk</p>
                        <small><?php echo $stats['active_sellers']; ?> seller aktif</small>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Pesanan</p>
                        <small><?php echo $stats['pending_orders']; ?> pending</small>
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="stat-icon reports"><i class="fas fa-flag"></i></div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['reported_items']); ?></h3>
                        <p>Laporan Baru</p>
                        <small>Perlu ditinjau</small>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="admin-charts-section" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="admin-chart-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 15px;"><i class="fas fa-chart-line" style="color: #4C3C27; margin-right: 10px;"></i> Statistik Pesanan (7 Hari Terakhir)</h3>
                    <div class="chart-wrapper">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
                
                <div class="admin-chart-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 15px;"><i class="fas fa-chart-pie" style="color: #4C3C27; margin-right: 10px;"></i> Distribusi Pengguna</h3>
                    <div class="chart-wrapper">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Grid (Activities & Quick Actions) -->
            <div class="dashboard-bottom-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
                
                <!-- Recent Activities -->
                <div class="admin-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3><i class="fas fa-history" style="color: #4C3C27;"></i> Aktivitas Terbaru</h3>
                    </div>
                    <div class="admin-card-body scrollable-activities">
                        <div class="activities-list">
                            <?php if(empty($recent_activities)): ?>
                                <p style="text-align: center; color: #888; padding: 20px;">Belum ada aktivitas</p>
                            <?php else: ?>
                                <?php foreach($recent_activities as $activity): ?>
                                <div class="activity-item" style="display: flex; align-items: start; gap: 15px; padding: 15px 0; border-bottom: 1px solid #eee;">
                                    <div class="activity-icon" style="background: #f4f4f4; padding: 10px; border-radius: 50%;">
                                        <?php switch($activity['type']):
                                            case 'user': echo '<i class="fas fa-user-plus" style="color: #28A745;"></i>'; break;
                                            case 'product': echo '<i class="fas fa-box" style="color: #4C3C27;"></i>'; break;
                                            case 'order': echo '<i class="fas fa-shopping-bag" style="color: #17A2B8;"></i>'; break;
                                            case 'report': echo '<i class="fas fa-flag" style="color: #FFC107;"></i>'; break;
                                        endswitch; ?>
                                    </div>
                                    <div class="activity-content">
                                        <p style="margin: 0; font-size: 14px;"><strong><?php echo htmlspecialchars($activity['title']); ?></strong> telah ditambahkan</p>
                                        <small style="color: #888;"><?php echo timeAgo($activity['date']); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="admin-card" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div class="admin-card-header" style="margin-bottom: 15px;">
                        <h3><i class="fas fa-bolt" style="color: #4C3C27;"></i> Quick Actions</h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="quick-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <a href="users.php" class="quick-action" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; color: #333; text-align: center; transition: 0.2s;">
                                <i class="fas fa-users" style="font-size: 20px; color: #4C3C27; margin-bottom: 5px;"></i>
                                <span style="font-size: 12px; font-weight: 500;">Kelola User</span>
                            </a>
                            <a href="products.php" class="quick-action" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; color: #333; text-align: center; transition: 0.2s;">
                                <i class="fas fa-box" style="font-size: 20px; color: #4C3C27; margin-bottom: 5px;"></i>
                                <span style="font-size: 12px; font-weight: 500;">Kelola Produk</span>
                            </a>
                            <a href="orders.php" class="quick-action" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; color: #333; text-align: center; transition: 0.2s;">
                                <i class="fab fa-whatsapp" style="font-size: 20px; color: #25D366; margin-bottom: 5px;"></i>
                                <span style="font-size: 12px; font-weight: 500;">Log WA</span>
                            </a>
                            <a href="settings.php" class="quick-action" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; color: #333; text-align: center; transition: 0.2s;">
                                <i class="fas fa-cog" style="font-size: 20px; color: #4C3C27; margin-bottom: 5px;"></i>
                                <span style="font-size: 12px; font-weight: 500;">Pengaturan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Info (Diperkecil) -->
            <div class="admin-card" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div class="admin-card-header" style="margin-bottom: 15px;">
                    <h3 style="font-size: 16px;"><i class="fas fa-info-circle" style="color: #4C3C27;"></i> Informasi Sistem</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center;">
                        <div>
                            <small style="color: #888; display: block; margin-bottom: 2px;">PHP Version</small>
                            <strong style="font-size: 14px;"><?php echo phpversion(); ?></strong>
                        </div>
                        <div>
                            <small style="color: #888; display: block; margin-bottom: 2px;">Database</small>
                            <strong style="font-size: 14px;">MySQL</strong>
                        </div>
                        <div>
                            <small style="color: #888; display: block; margin-bottom: 2px;">Server</small>
                            <strong style="font-size: 14px;"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></strong>
                        </div>
                        <div>
                            <small style="color: #888; display: block; margin-bottom: 2px;">Waktu Server</small>
                            <strong style="font-size: 14px;"><?php echo date('d M Y H:i'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Setup default font
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#666';

    // Orders Chart
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($chart_data, 'date')); ?>,
            datasets: [{
                label: 'Pesanan Masuk',
                data: <?php echo json_encode(array_column($chart_data, 'orders')); ?>,
                borderColor: '#4C3C27',
                backgroundColor: 'rgba(76, 60, 39, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#4C3C27',
                pointRadius: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Users Chart
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    new Chart(usersCtx, {
        type: 'doughnut',
        data: {
            labels: ['Customer', 'Seller', 'Admin'],
            datasets: [{
                data: [<?php echo $customer_count; ?>, <?php echo $seller_count; ?>, <?php echo $admin_count; ?>],
                backgroundColor: ['#C9B59C', '#4C3C27', '#300C0C'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, boxWidth: 8 }
                }
            }
        }
    });
    </script>
</body>
</html>