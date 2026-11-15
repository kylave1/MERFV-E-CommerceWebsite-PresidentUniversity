<?php
require_once 'config.php';
$page_title = 'Home';
$page_scripts = ['assets/js/home.js'];

// Get featured products
$featured_products = [];
$stmt = $pdo->prepare("SELECT p.*, u.full_name, u.profile_pic 
                       FROM products p 
                       JOIN users u ON p.seller_id = u.user_id 
                       WHERE p.is_available = 1 
                       ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get recent blogs
$recent_blogs = [];
$stmt = $pdo->prepare("SELECT b.*, u.full_name, u.profile_pic 
                       FROM blogs b 
                       JOIN users u ON b.user_id = u.user_id 
                       WHERE b.is_published = 1 
                       ORDER BY b.created_at DESC LIMIT 3");
$stmt->execute();
$recent_blogs = $stmt->fetchAll();

// Get stats
$stats = [
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE is_available = 1")->fetchColumn(),
    'total_sellers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'active'")->fetchColumn(),
    'total_transactions' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
    'today_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn()
];

include 'header.php';
?>

<!-- ==========================================
     HERO SECTION - ELEGANT EDITION
     ========================================== -->
<section class="hero-section" style="position: relative; background: linear-gradient(135deg, #2C2416 0%, #4C3C27 100%); padding: 100px 20px; overflow: hidden; text-align: center;">
    
    <!-- Ornamen Background -->
    <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(201,181,156,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -50px; left: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(201,181,156,0.1) 0%, transparent 70%); border-radius: 50%;"></div>

    <div class="container" style="position: relative; z-index: 2; max-width: 900px; margin: 0 auto;">
        
        <!-- Badge Atas -->
        <span style="display: inline-block; padding: 8px 20px; background: rgba(201, 181, 156, 0.15); border: 1px solid rgba(201, 181, 156, 0.3); border-radius: 50px; color: #C9B59C; font-size: 14px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 25px; backdrop-filter: blur(10px);">
            President University's #1 Marketplace
        </span>

        <!-- Judul Utama -->
        <h1 style="font-family: 'Poppins', sans-serif; font-size: clamp(36px, 5vw, 56px); font-weight: 800; color: #ffffff; line-height: 1.2; margin-bottom: 20px; letter-spacing: -1px;">
            Find Everything Students Need on <span style="color: #C9B59C; position: relative; display: inline-block;">One Platform</span>
        </h1>

        <!-- Subjudul -->
        <p style="font-size: clamp(16px, 2vw, 18px); color: #B8B8B8; line-height: 1.6; max-width: 700px; margin: 0 auto 40px auto; font-weight: 400;">
            Discover food, preloved items, services, and urgent assistance crafted specifically for the student community.
        </p>

        <!-- Tambahan Kalimat Elegan -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; animation: fadeInUp 1s ease;">
            <div style="height: 1px; width: 40px; background: linear-gradient(to right, transparent, #C9B59C);"></div>
            <p style="font-size: 20px; font-style: italic; color: #C9B59C; font-family: 'Georgia', serif; margin: 0; letter-spacing: 1px;">
                "MERFV will be there for you."
            </p>
            <div style="height: 1px; width: 40px; background: linear-gradient(to left, transparent, #C9B59C);"></div>
        </div>

    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo number_format($stats['total_products']); ?></h3>
                    <p class="stat-label">Active Products</p>
                    <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> +12% this week</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-store"></i></div>
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo number_format($stats['total_sellers']); ?></h3>
                    <p class="stat-label">Trusted Sellers</p>
                    <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> +5 new sellers</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo number_format($stats['total_transactions']); ?></h3>
                    <p class="stat-label">Successful Transactions</p>
                    <span class="stat-trend"><i class="fas fa-check-circle"></i> 100% secure</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo number_format($stats['total_users']); ?></h3>
                    <p class="stat-label">Active Users</p>
                    <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> +<?php echo $stats['today_users'] ?? 0; ?> today</span>
                </div>
            </div>
        </div>
    </div>
</section>

<style> 
/* ===== CSS UNTUK STATS SECTION ===== */
.stats-section { padding: 30px 0 50px; background: linear-gradient(180deg, #FFFEFC 0%, #F9F7F4 100%); }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
.stat-card { background: white; border-radius: 20px; padding: 30px 25px; display: flex; align-items: center; gap: 20px; border: 1px solid #E8E3D9; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); position: relative; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.02); }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, #4C3C27, #C9B59C); opacity: 0; transition: opacity 0.4s ease; }
.stat-card:hover { transform: translateY(-8px); border-color: #C9B59C; box-shadow: 0 20px 35px rgba(76, 60, 39, 0.1); }
.stat-card:hover::before { opacity: 1; }
.stat-icon { width: 70px; height: 70px; border-radius: 18px; background: linear-gradient(145deg, #F5F3EE, #EAE7E0); display: flex; align-items: center; justify-content: center; font-size: 32px; color: #4C3C27; transition: all 0.4s ease; flex-shrink: 0; }
.stat-card:hover .stat-icon { background: linear-gradient(135deg, #4C3C27, #300C0C); color: white; transform: scale(1.05) rotate(5deg); }
.stat-content { flex: 1; display: flex; flex-direction: column; }
.stat-number { font-size: 36px; font-weight: 800; color: #2C2416; line-height: 1.1; margin-bottom: 6px; font-family: 'Montserrat', 'Poppins', sans-serif; }
.stat-label { font-size: 15px; font-weight: 600; color: #6D6D6D; margin-bottom: 8px; letter-spacing: 0.3px; }
.stat-trend { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 5px 12px; border-radius: 30px; background: #F5F3EE; color: #4A4A4A; width: fit-content; }
.stat-trend.positive { background: rgba(40, 167, 69, 0.1); color: #28A745; }
.stat-trend i { font-size: 11px; }
.stat-trend .fa-check-circle { color: #28A745; }
.stat-card:nth-child(1) .stat-icon { color: #4C3C27; } .stat-card:nth-child(1):hover .stat-icon { background: linear-gradient(135deg, #4C3C27, #2C2416); }
.stat-card:nth-child(2) .stat-icon { color: #C9B59C; } .stat-card:nth-child(2):hover .stat-icon { background: linear-gradient(135deg, #C9B59C, #8B7356); }
.stat-card:nth-child(3) .stat-icon { color: #17A2B8; } .stat-card:nth-child(3):hover .stat-icon { background: linear-gradient(135deg, #17A2B8, #0F6674); }
.stat-card:nth-child(4) .stat-icon { color: #28A745; } .stat-card:nth-child(4):hover .stat-icon { background: linear-gradient(135deg, #28A745, #1E7A34); }
.stat-card { animation: fadeInUp 0.6s ease backwards; }
.stat-card:nth-child(1) { animation-delay: 0.1s; } .stat-card:nth-child(2) { animation-delay: 0.2s; } .stat-card:nth-child(3) { animation-delay: 0.3s; } .stat-card:nth-child(4) { animation-delay: 0.4s; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; } .stat-card { padding: 25px 20px; } .stat-number { font-size: 32px; } }
@media (max-width: 768px) { .stats-section { padding: 20px 0 40px; } .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; } .stat-card { padding: 20px 18px; flex-direction: column; text-align: center; } .stat-icon { width: 60px; height: 60px; font-size: 28px; margin-bottom: 10px; } .stat-content { align-items: center; } .stat-number { font-size: 28px; } .stat-label { font-size: 14px; } .stat-trend { font-size: 11px; padding: 4px 10px; } }
@media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; gap: 15px; } .stat-card { flex-direction: row; text-align: left; padding: 18px 20px; } .stat-icon { margin-bottom: 0; width: 55px; height: 55px; font-size: 24px; } .stat-content { align-items: flex-start; } .stat-number { font-size: 26px; } }
</style> 

<!-- Categories Section -->
<section class="popular-categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Popular Categories</h2>
            <a href="all-categories.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="categories-grid">
            <a href="<?php echo SITE_URL; ?>pages/food.php" class="category-card">
                <div class="category-icon"><i class="fas fa-utensils"></i></div>
                <div class="category-content"><h3>Food</h3><p>200+ products</p></div>
                <div class="category-arrow"><i class="fas fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo SITE_URL; ?>pages/preloved.php" class="category-card">
                <div class="category-icon"><i class="fas fa-tshirt"></i></div>
                <div class="category-content"><h3>Preloved</h3><p>150+ products</p></div>
                <div class="category-arrow"><i class="fas fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo SITE_URL; ?>pages/service.php" class="category-card">
                <div class="category-icon"><i class="fas fa-hands-helping"></i></div>
                <div class="category-content"><h3>Services</h3><p>80+ services</p></div>
                <div class="category-arrow"><i class="fas fa-chevron-right"></i></div>
            </a>
            <a href="<?php echo SITE_URL; ?>pages/urgent.php" class="category-card">
                <div class="category-icon"><i class="fas fa-clock"></i></div>
                <div class="category-content"><h3>Urgent Needs</h3><p>Quick assistance</p></div>
                <div class="category-arrow"><i class="fas fa-chevron-right"></i></div>
            </a>
        </div>
    </div>
</section>

<style>
/* ===== POPULAR CATEGORIES ===== */
.popular-categories { padding: 50px 0; background: #F9F7F4; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.section-title { font-size: 28px; font-weight: 700; color: #2C2416; position: relative; padding-left: 16px; }
.section-title::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(to bottom, #4C3C27, #C9B59C); border-radius: 2px; }
.view-all-link { display: flex; align-items: center; gap: 8px; color: #4C3C27; text-decoration: none; font-weight: 600; font-size: 15px; padding: 10px 20px; border-radius: 40px; transition: all 0.3s; }
.view-all-link:hover { background: white; gap: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.categories-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.category-card { display: flex; align-items: center; gap: 16px; padding: 20px; background: white; border-radius: 16px; border: 1px solid #E8E3D9; text-decoration: none; transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); position: relative; overflow: hidden; }
.category-card:hover { transform: translateY(-5px); border-color: #4C3C27; box-shadow: 0 12px 28px rgba(76, 60, 39, 0.1); }
.category-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #4C3C27, #300C0C); opacity: 0; transition: opacity 0.3s; z-index: 1; }
.category-card:hover::before { opacity: 0.03; }
.category-icon { width: 55px; height: 55px; border-radius: 14px; background: #F5F3EE; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #4C3C27; transition: all 0.3s; z-index: 2; }
.category-card:hover .category-icon { background: #4C3C27; color: white; transform: scale(1.05); }
.category-content { flex: 1; z-index: 2; }
.category-content h3 { font-size: 18px; font-weight: 700; color: #2C2416; margin-bottom: 6px; transition: color 0.3s; }
.category-card:hover .category-content h3 { color: #4C3C27; }
.category-content p { font-size: 14px; color: #6D6D6D; transition: color 0.3s; }
.category-card:hover .category-content p { color: #4C3C27; }
.category-arrow { width: 32px; height: 32px; border-radius: 50%; background: #F5F3EE; display: flex; align-items: center; justify-content: center; color: #4C3C27; font-size: 14px; transition: all 0.3s; z-index: 2; }
.category-card:hover .category-arrow { background: #4C3C27; color: white; transform: translateX(5px); }
.category-card:nth-child(1):hover .category-icon { background: #FF6B6B; }
.category-card:nth-child(2):hover .category-icon { background: #4C6EF5; }
.category-card:nth-child(3):hover .category-icon { background: #20C997; }
.category-card:nth-child(4):hover .category-icon { background: #FF8787; }
@media (max-width: 992px) { .categories-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; } .section-title { font-size: 24px; } }
@media (max-width: 768px) { .popular-categories { padding: 40px 0; } .section-header { flex-direction: column; align-items: flex-start; gap: 15px; } .categories-grid { gap: 15px; } .category-card { padding: 16px; } .category-icon { width: 48px; height: 48px; font-size: 22px; } .category-content h3 { font-size: 16px; } .category-content p { font-size: 13px; } }
@media (max-width: 576px) { .categories-grid { grid-template-columns: 1fr; gap: 12px; } .view-all-link { align-self: flex-start; } }
</style>

<!-- Featured Products -->
<section class="featured-section" style="padding: 50px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Products</h2>
            <a href="<?php echo SITE_URL; ?>pages/food.php" class="btn-outline btn-sm">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
            <?php 
            if(!empty($featured_products)):
                foreach($featured_products as $product): 
            ?>
            <div class="product-card" style="background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div class="product-image" style="position: relative;">
                    <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="<?php echo SITE_URL . UPLOAD_PATH . $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 180px; object-fit: cover;">
                    </a>
                </div>
                <div class="product-content" style="padding: 15px;">
                    <h3 class="product-title" style="font-size: 16px; font-weight: 700; margin-bottom: 10px;">
                        <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $product['product_id']; ?>" style="color: #333; text-decoration: none;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <div class="product-price" style="font-size: 18px; font-weight: 800; color: #4C3C27;">
                        <?php echo formatCurrency($product['discounted_price'] ?? $product['price']); ?>
                    </div>
                </div>
            </div>
            <?php 
                endforeach; 
            else:
                echo '<p style="text-align:center; width:100%; grid-column: 1/-1; color:#888;">Belum ada produk unggulan.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<!-- ==========================================
     BLOG SECTION (RECENT MOMENTS) - FIXED CSS
     ========================================== -->
<?php if(!empty($recent_blogs)): ?>
<section class="blog-section-home">
    <div class="container">
        <div class="blog-header-wrapper">
            <h2 class="section-title">Recent Moments</h2>
            <a href="<?php echo SITE_URL; ?>pages/blog.php" class="btn-view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="blog-moment-grid-home">
            <?php foreach($recent_blogs as $blog): ?>
            <div class="moment-card">
                <div class="moment-header">
                    <div class="author-avatar">
                        <img src="<?php echo getUserAvatar($blog['user_id']); ?>" alt="Author">
                    </div>
                    <div class="author-info">
                        <div class="author-name"><?php echo htmlspecialchars($blog['full_name']); ?></div>
                        <div class="post-meta">
                            <span class="post-category">Blog</span>
                            <span class="post-time"><?php echo timeAgo($blog['created_at']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="moment-content">
                    <h3 class="moment-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                    <p class="moment-text"><?php echo htmlspecialchars(substr($blog['content'], 0, 100)); ?>...</p>
                </div>
                <div class="moment-footer">
                    <div class="post-stats">
                        <span class="stat-item"><i class="fas fa-eye"></i> <?php echo $blog['views'] ?? 0; ?></span>
                    </div>
                    <a href="<?php echo SITE_URL; ?>pages/blog-detail.php?id=<?php echo $blog['blog_id']; ?>" class="btn-read-more">Read <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
/* ===== BLOG SECTION HOMEPAGE - MOMENT CARDS ===== */
.blog-section-home { padding: 60px 0; background: #F9F7F4; margin-top: 20px; }
.blog-header-wrapper { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.btn-view-all { display: flex; align-items: center; gap: 10px; color: #4C3C27; font-weight: 600; text-decoration: none; padding: 10px 20px; border-radius: 30px; transition: all 0.3s; }
.btn-view-all:hover { background: rgba(76, 60, 39, 0.05); gap: 15px; }
.btn-view-all i { font-size: 14px; }

/* BLOG GRID - 3 KOLOM */
.blog-moment-grid-home { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }

/* MOMENT CARD */
.moment-card { background: white; border-radius: 20px; padding: 25px; border: 1px solid #E8E3D9; transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; position: relative; overflow: hidden; }
.moment-card:hover { transform: translateY(-6px); border-color: #C9B59C; box-shadow: 0 15px 35px rgba(76, 60, 39, 0.08); }
.moment-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #4C3C27, #C9B59C); opacity: 0; transition: opacity 0.3s; }
.moment-card:hover::before { opacity: 1; }

/* HEADER CARD (FIX GAMBAR RUSAK) */
.moment-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
.author-avatar { width: 50px; height: 50px; border-radius: 50%; overflow: hidden; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex-shrink: 0; }
.author-avatar img { width: 100%; height: 100%; object-fit: cover; }
.author-info { flex: 1; }
.author-name { font-size: 16px; font-weight: 700; color: #2C2416; margin-bottom: 6px; }
.post-meta { display: flex; align-items: center; gap: 8px; font-size: 13px; }
.post-category { background: #F0EDE5; padding: 4px 12px; border-radius: 20px; color: #4C3C27; font-weight: 600; font-size: 12px; }
.post-time { color: #8E8E8E; }

/* CONTENT CARD */
.moment-content { flex: 1; margin-bottom: 20px; }
.moment-title { font-size: 18px; font-weight: 700; color: #2C2416; margin-bottom: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.moment-text { font-size: 14px; line-height: 1.6; color: #5C5C5C; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

/* FOOTER CARD */
.moment-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 18px; border-top: 1px solid #F0EDE5; }
.post-stats { display: flex; gap: 16px; }
.stat-item { display: flex; align-items: center; gap: 6px; color: #8E8E8E; font-size: 13px; }
.stat-item i { font-size: 14px; color: #4C3C27; }
.btn-read-more { display: flex; align-items: center; gap: 6px; color: #4C3C27; font-size: 13px; font-weight: 600; text-decoration: none; padding: 8px 16px; border-radius: 25px; background: #F5F3EE; transition: all 0.3s; }
.btn-read-more:hover { background: #4C3C27; color: white; gap: 10px; }

/* RESPONSIVE BLOG */
@media (max-width: 992px) { .blog-moment-grid-home { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { 
    .blog-section-home { padding: 40px 0; } 
    .section-title { font-size: 24px; } 
    .btn-view-all { display: none; } 
    .blog-moment-grid-home { grid-template-columns: 1fr; gap: 20px; } 
    .moment-card { padding: 20px; } 
}
</style>
<?php endif; ?>

<!-- Services Section -->
<section class="jasa-home-section">
    <div class="container">
        <div class="jasa-home-header">
            <div>
                <h2 class="section-title">Services & Offers</h2>
                <p class="section-subtitle">Discover various services and skills from trusted students</p>
            </div>
            <a href="<?php echo SITE_URL; ?>pages/service.php" class="btn-view-all-small">
                See Others <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="jasa-home-grid">
            <?php
            $stmt = $pdo->prepare("
                SELECT p.*, u.full_name, u.profile_pic, u.phone as seller_phone, u.rating as seller_rating,
                (SELECT COUNT(*) FROM orders WHERE product_id = p.product_id AND status = 'delivered') as terjual
                FROM products p
                JOIN users u ON p.seller_id = u.user_id
                WHERE p.category = 'service' 
                AND p.is_available = 1 
                AND u.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT 4
            ");
            $stmt->execute();
            $jasa_products = $stmt->fetchAll();
            
            if(!empty($jasa_products)):
                foreach($jasa_products as $jasa): 
                $phone = $jasa['seller_phone'] ?? '';
                $wa_number = preg_replace('/[^0-9]/', '', $phone);
                if(substr($wa_number, 0, 1) == '0') $wa_number = '62' . substr($wa_number, 1);
                if(substr($wa_number, 0, 2) != '62' && strlen($wa_number) > 0) $wa_number = '62' . $wa_number;
                
                $message = "Hello%20" . urlencode($jasa['full_name']) . "%2C%0A%0A";
                $message .= "I%20am%20interested%20in%20the%20service%20*" . urlencode($jasa['name']) . "*%20that%20you%20offer.%0A%0A";
                $message .= "Is%20it%20still%20available%3F";
                $wa_link = "https://wa.me/$wa_number?text=$message";
                ?>
                <div class="jasa-home-card">
                    <div class="jasa-home-category"><?php echo ucfirst($jasa['subcategory'] ?? 'Service'); ?></div>
                    <div class="jasa-home-content">
                        <h3 class="jasa-home-title"><?php echo htmlspecialchars($jasa['name']); ?></h3>
                        <div class="jasa-home-seller">
                            <span class="seller-name"><?php echo htmlspecialchars($jasa['full_name']); ?></span>
                            <span class="seller-rating"><i class="fas fa-star"></i> <?php echo number_format($jasa['seller_rating'] ?? 0, 1); ?></span>
                        </div>
                        <div class="jasa-home-desc">
                            <p><?php echo htmlspecialchars(substr($jasa['description'], 0, 100)); ?>...</p>
                        </div>
                        <div class="jasa-home-footer">
                            <div class="price-section">
                                <span class="jasa-home-price"><?php echo formatCurrency($jasa['discounted_price'] ?? $jasa['price']); ?></span>
                                <span class="jasa-sold">Sold <?php echo $jasa['terjual'] ?? 0; ?></span>
                            </div>
                            <a href="<?php echo $wa_link; ?>" target="_blank" class="btn-hire-wa">
                                <i class="fab fa-whatsapp"></i> Hire
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>