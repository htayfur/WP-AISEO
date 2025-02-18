<?php
if (!defined('ABSPATH')) {
    exit;
}

// Mevcut sekmeyi al
$current_tab = str_replace('wp-ai-seo-', '', $_GET['page']);
if ($current_tab === 'wp-ai-seo') {
    $current_tab = 'dashboard';
}

// Sekmeler
$tabs = [
    'dashboard' => [
        'title' => __('Genel Bakış', 'wp-ai-seo'),
        'icon' => 'dashicons-chart-area'
    ],
    'basic-seo' => [
        'title' => __('Temel SEO', 'wp-ai-seo'),
        'icon' => 'dashicons-admin-settings'
    ],
    'technical-seo' => [
        'title' => __('Teknik SEO', 'wp-ai-seo'),
        'icon' => 'dashicons-admin-tools'
    ],
    'content-optimization' => [
        'title' => __('İçerik Optimizasyonu', 'wp-ai-seo'),
        'icon' => 'dashicons-editor-paste-text'
    ],
    'advanced-seo' => [
        'title' => __('Gelişmiş SEO', 'wp-ai-seo'),
        'icon' => 'dashicons-performance'
    ],
    'social-seo' => [
        'title' => __('Sosyal Medya', 'wp-ai-seo'),
        'icon' => 'dashicons-share'
    ],
    'performance' => [
        'title' => __('Performans', 'wp-ai-seo'),
        'icon' => 'dashicons-dashboard'
    ],
    'settings' => [
        'title' => __('Ayarlar', 'wp-ai-seo'),
        'icon' => 'dashicons-admin-generic'
    ]
];
?>

<div class="wrap wp-ai-seo-wrap">
    <div class="wp-ai-seo-header">
        <h1>
            <?php echo esc_html($tabs[$current_tab]['title']); ?>
            <span class="dashicons <?php echo esc_attr($tabs[$current_tab]['icon']); ?>"></span>
        </h1>
    </div>

    <div class="wp-ai-seo-content">
        <div class="wp-ai-seo-settings">
            <ul class="wp-ai-seo-settings-nav">
                <?php foreach ($tabs as $tab_id => $tab) : ?>
                    <li>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-ai-seo-' . $tab_id)); ?>" 
                           class="<?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
                            <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span>
                            <?php echo esc_html($tab['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="wp-ai-seo-settings-content">
                <?php
                // İlgili sekme içeriğini yükle
                $template = WP_AI_SEO_PATH . 'views/tabs/' . $current_tab . '.php';
                if (file_exists($template)) {
                    require_once $template;
                } else {
                    echo '<div class="notice notice-error"><p>' . 
                         esc_html__('Görünüm dosyası bulunamadı.', 'wp-ai-seo') . 
                         '</p></div>';
                }
                ?>
            </div>
        </div>

        <?php if ($current_tab === 'dashboard') : ?>
            <div class="wp-ai-seo-dashboard-widgets">
                <div class="wp-ai-seo-widget">
                    <h3><?php _e('SEO Sağlık Puanı', 'wp-ai-seo'); ?></h3>
                    <div class="wp-ai-seo-health-score" style="background-color: #46b450;">
                        85
                    </div>
                    <p class="description">
                        <?php _e('Sitenizin genel SEO performans puanı', 'wp-ai-seo'); ?>
                    </p>
                </div>

                <div class="wp-ai-seo-widget">
                    <h3><?php _e('Hızlı İstatistikler', 'wp-ai-seo'); ?></h3>
                    <ul>
                        <li>
                            <strong><?php _e('İndekslenen Sayfalar:', 'wp-ai-seo'); ?></strong>
                            <span class="count">0</span>
                        </li>
                        <li>
                            <strong><?php _e('404 Hataları:', 'wp-ai-seo'); ?></strong>
                            <span class="count">0</span>
                        </li>
                        <li>
                            <strong><?php _e('Eksik Meta Açıklamalar:', 'wp-ai-seo'); ?></strong>
                            <span class="count">0</span>
                        </li>
                        <li>
                            <strong><?php _e('Optimize Edilmemiş Resimler:', 'wp-ai-seo'); ?></strong>
                            <span class="count">0</span>
                        </li>
                    </ul>
                </div>

                <div class="wp-ai-seo-widget">
                    <h3><?php _e('Son SEO Sorunları', 'wp-ai-seo'); ?></h3>
                    <div class="wp-ai-seo-issues-list">
                        <p class="wp-ai-seo-no-issues">
                            <?php _e('Şu anda aktif SEO sorunu bulunmuyor.', 'wp-ai-seo'); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>