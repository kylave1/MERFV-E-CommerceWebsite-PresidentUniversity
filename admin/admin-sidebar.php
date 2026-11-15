<?php

?>
<!-- Admin Side -->
<div class="admin-sidebar">
    
    <!-- ========================================== -->
    <!-- HEADER BRAND BARU (ANTI CSS NYANGKUT)      -->
    <!-- ========================================== -->
    <div class="custom-brand-area">
        
        <!-- Gambar Logo -->
        <img src="../assets/images/logo.png" alt="MERF Logo" class="custom-brand-img">
        
        <!-- Teks MERFv -->
        <span class="custom-brand-text">MERFv</span>
    </div>
    
    <!-- ========================================== -->
    <!-- MENU NAVIGASI                              -->
    <!-- ========================================== -->
    <nav class="admin-nav">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Pengguna</span>
        </a>
        
        <a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Produk</span>
        </a>
        
        <a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fab fa-whatsapp" style="color: #25D366;"></i>
            <span>Log Klik WA</span>
        </a>
        
        <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-flag"></i>
            <span>Laporan</span>
        </a>

        <a href="comments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i>
            <span>Komentar</span>
        </a>
        
        <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Pengaturan</span>
        </a>
        
        <div class="admin-nav-divider"></div>
        
        <a href="../index.php">
            <i class="fas fa-home"></i>
            <span>Kembali ke Site</span>
        </a>
    </nav>
</div>

<!-- Mobile Menu Toggle untuk Admin -->
<div class="admin-mobile-toggle">
    <i class="fas fa-bars"></i>
</div>

<script>
// Toggle sidebar di mobile
document.querySelector('.admin-mobile-toggle')?.addEventListener('click', function() {
    document.querySelector('.admin-sidebar').classList.toggle('active');
});
</script>

<style>
/* ========================================== */
/* CSS BARU: MEMAKSA TAMPILAN LOGO & TEKS     */
/* ========================================== */

.custom-brand-area {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 15px !important;
    padding: 25px 20px !important;
    border-bottom: 1px solid rgba(255,255,255,0.1) !important;
    background: transparent !important;
}

.custom-brand-img {
    height: 50px !important;
    width: auto !important;
    background: transparent !important;
    border-radius: 0 !important; /* Menghilangkan bulatan jika ada */
    border: none !important;
    box-shadow: none !important;
}

.custom-brand-text {
    color: #ffffff !important; /* PAKSA WARNA PUTIH */
    font-size: 26px !important;
    font-weight: 800 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important; /* HAPUS KOTAK ABU-ABU */
    border: none !important;
    box-shadow: none !important;
    display: inline-block !important;
    letter-spacing: 1px !important;
}

/* Menghancurkan efek pseudo-element (kalau ada) yang bikin kotak aneh */
.custom-brand-area::before,
.custom-brand-area::after,
.custom-brand-text::before,
.custom-brand-text::after {
    display: none !important;
    content: none !important;
}

/* Style Mobile Toggle */
.admin-mobile-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: #4C3C27;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 12px;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .admin-mobile-toggle {
        display: flex;
    }
}
</style>