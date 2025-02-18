<?php
if (!defined('ABSPATH')) {
    exit;
}

// SEO istatistiklerini al
$stats = [
    'indexed_pages' => 0,
    'not_found_errors' => 0,
    'missing_meta' => 0,
    'unoptimized_images' => 0
];

// Son SEO sorunları
$recent_issues = [];

// Site sağlık puanı
$health_score = 85;
?>

<div class="wp-ai-seo-dashboard-grid">
    <!-- SEO Sağlık Puanı -->
    <div class="wp-ai-seo-dashboard-box health-score">
        <h3><?php _e('SEO Sağlık Puanı', 'wp-ai-seo'); ?></h3>
        <div class="score-circle" data-score="<?php echo esc_attr($health_score); ?>">
            <div class="score-number"><?php echo esc_html($health_score); ?></div>
            <div class="score-label"><?php _e('puan', 'wp-ai-seo'); ?></div>
        </div>
        <div class="score-details">
            <?php if ($health_score >= 80) : ?>
                <p class="score-good">
                    <?php _e('Siteniz iyi durumda! Yüksek performansı korumaya devam edin.', 'wp-ai-seo'); ?>
                </p>
            <?php elseif ($health_score >= 60) : ?>
                <p class="score-ok">
                    <?php _e('Bazı iyileştirmeler yaparak puanınızı yükseltebilirsiniz.', 'wp-ai-seo'); ?>
                </p>
            <?php else : ?>
                <p class="score-bad">
                    <?php _e('Sitenizin SEO puanı düşük. Acil iyileştirmeler gerekiyor.', 'wp-ai-seo'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hızlı İstatistikler -->
    <div class="wp-ai-seo-dashboard-box stats">
        <h3><?php _e('Hızlı İstatistikler', 'wp-ai-seo'); ?></h3>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="dashicons dashicons-admin-site"></span>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html($stats['indexed_pages']); ?></div>
                    <div class="stat-label"><?php _e('İndekslenen Sayfalar', 'wp-ai-seo'); ?></div>
                </div>
            </div>
            <div class="stat-item">
                <span class="dashicons dashicons-warning"></span>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html($stats['not_found_errors']); ?></div>
                    <div class="stat-label"><?php _e('404 Hataları', 'wp-ai-seo'); ?></div>
                </div>
            </div>
            <div class="stat-item">
                <span class="dashicons dashicons-editor-help"></span>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html($stats['missing_meta']); ?></div>
                    <div class="stat-label"><?php _e('Eksik Meta Açıklamalar', 'wp-ai-seo'); ?></div>
                </div>
            </div>
            <div class="stat-item">
                <span class="dashicons dashicons-format-image"></span>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html($stats['unoptimized_images']); ?></div>
                    <div class="stat-label"><?php _e('Optimize Edilmemiş Resimler', 'wp-ai-seo'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Son SEO Sorunları -->
    <div class="wp-ai-seo-dashboard-box issues">
        <h3><?php _e('Son SEO Sorunları', 'wp-ai-seo'); ?></h3>
        <?php if (empty($recent_issues)) : ?>
            <p class="no-issues">
                <?php _e('Şu anda aktif SEO sorunu bulunmuyor.', 'wp-ai-seo'); ?>
            </p>
        <?php else : ?>
            <ul class="issues-list">
                <?php foreach ($recent_issues as $issue) : ?>
                    <li class="issue-item <?php echo esc_attr($issue['severity']); ?>">
                        <span class="dashicons <?php echo esc_attr($issue['icon']); ?>"></span>
                        <div class="issue-content">
                            <div class="issue-title"><?php echo esc_html($issue['title']); ?></div>
                            <div class="issue-description"><?php echo esc_html($issue['description']); ?></div>
                        </div>
                        <?php if (!empty($issue['action_url'])) : ?>
                            <a href="<?php echo esc_url($issue['action_url']); ?>" class="button">
                                <?php _e('Düzelt', 'wp-ai-seo'); ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- SEO Kontrol Listesi -->
    <div class="wp-ai-seo-dashboard-box checklist">
        <h3><?php _e('SEO Kontrol Listesi', 'wp-ai-seo'); ?></h3>
        <div class="checklist-items">
            <div class="checklist-item completed">
                <span class="dashicons dashicons-yes-alt"></span>
                <div class="checklist-content">
                    <?php _e('Site başlığı ve açıklaması ayarlandı', 'wp-ai-seo'); ?>
                </div>
            </div>
            <div class="checklist-item">
                <span class="dashicons dashicons-marker"></span>
                <div class="checklist-content">
                    <?php _e('XML site haritası oluşturuldu', 'wp-ai-seo'); ?>
                </div>
            </div>
            <div class="checklist-item">
                <span class="dashicons dashicons-marker"></span>
                <div class="checklist-content">
                    <?php _e('Robots.txt dosyası optimize edildi', 'wp-ai-seo'); ?>
                </div>
            </div>
            <div class="checklist-item">
                <span class="dashicons dashicons-marker"></span>
                <div class="checklist-content">
                    <?php _e('Sosyal medya meta etiketleri ayarlandı', 'wp-ai-seo'); ?>
                </div>
            </div>
        </div>
    </div>
</div>