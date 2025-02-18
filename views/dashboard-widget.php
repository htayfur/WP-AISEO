<?php
if (!defined('ABSPATH')) {
    exit;
}

// SEO istatistiklerini al
$stats = [
    'total_posts' => wp_count_posts()->publish,
    'total_pages' => wp_count_posts('page')->publish,
    'indexed_count' => 0, // Google Search Console API ile alınacak
    'not_indexed' => 0,
    'missing_meta' => 0,
    'low_content' => 0
];

global $wpdb;

// Meta açıklaması eksik olan içerikler
$stats['missing_meta'] = $wpdb->get_var(
    "SELECT COUNT(*) FROM $wpdb->posts p 
     LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id AND m.meta_key = '_wp_ai_seo_meta_description' 
     WHERE p.post_type IN ('post', 'page') 
     AND p.post_status = 'publish' 
     AND m.meta_value IS NULL"
);

// Minimum kelime sayısının altındaki içerikler
$min_words = get_option('wp_ai_seo_content')['min_word_count'] ?? 300;
$stats['low_content'] = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $wpdb->posts 
         WHERE post_type IN ('post', 'page') 
         AND post_status = 'publish' 
         AND LENGTH(post_content) - LENGTH(REPLACE(post_content, ' ', '')) + 1 < %d",
        $min_words
    )
);

// Genel SEO puanı
$total_posts = $stats['total_posts'] + $stats['total_pages'];
if ($total_posts > 0) {
    $scores = $wpdb->get_results(
        "SELECT meta_value FROM $wpdb->postmeta 
         WHERE meta_key = '_wp_ai_seo_score' 
         AND meta_value != ''"
    );

    $total_score = 0;
    $score_count = count($scores);
    
    foreach ($scores as $score) {
        $total_score += intval($score->meta_value);
    }
    
    $avg_score = $score_count > 0 ? round($total_score / $score_count) : 0;
} else {
    $avg_score = 0;
}

// Son SEO sorunları
$issues = [];

if ($stats['missing_meta'] > 0) {
    $issues[] = [
        'type' => 'warning',
        'message' => sprintf(
            __('%d içerikte meta açıklama eksik.', 'wp-ai-seo'),
            $stats['missing_meta']
        ),
        'action_url' => admin_url('admin.php?page=wp-ai-seo&missing_meta=1')
    ];
}

if ($stats['low_content'] > 0) {
    $issues[] = [
        'type' => 'warning',
        'message' => sprintf(
            __('%d içerik minimum kelime sayısının altında.', 'wp-ai-seo'),
            $stats['low_content']
        ),
        'action_url' => admin_url('admin.php?page=wp-ai-seo&low_content=1')
    ];
}
?>

<div class="wp-ai-seo-dashboard-widget">
    <!-- Genel SEO Puanı -->
    <div class="score-section">
        <div class="seo-score <?php echo $avg_score >= 80 ? 'good' : ($avg_score >= 50 ? 'ok' : 'bad'); ?>">
            <?php echo esc_html($avg_score); ?>%
        </div>
        <h4><?php _e('Genel SEO Puanı', 'wp-ai-seo'); ?></h4>
    </div>

    <!-- İçerik İstatistikleri -->
    <div class="stats-section">
        <ul>
            <li>
                <span class="dashicons dashicons-admin-post"></span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo number_format_i18n($stats['total_posts']); ?></span>
                    <span class="stat-label"><?php _e('Yazı', 'wp-ai-seo'); ?></span>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-admin-page"></span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo number_format_i18n($stats['total_pages']); ?></span>
                    <span class="stat-label"><?php _e('Sayfa', 'wp-ai-seo'); ?></span>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-warning"></span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo number_format_i18n($stats['missing_meta']); ?></span>
                    <span class="stat-label"><?php _e('Meta Eksik', 'wp-ai-seo'); ?></span>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-editor-help"></span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo number_format_i18n($stats['low_content']); ?></span>
                    <span class="stat-label"><?php _e('İçerik Az', 'wp-ai-seo'); ?></span>
                </div>
            </li>
        </ul>
    </div>

    <!-- SEO Sorunları -->
    <?php if (!empty($issues)) : ?>
        <div class="issues-section">
            <h4><?php _e('SEO Sorunları', 'wp-ai-seo'); ?></h4>
            <ul>
                <?php foreach ($issues as $issue) : ?>
                    <li class="<?php echo esc_attr($issue['type']); ?>">
                        <span class="dashicons dashicons-<?php echo $issue['type'] === 'error' ? 'warning' : 'flag'; ?>"></span>
                        <div class="issue-content">
                            <p><?php echo esc_html($issue['message']); ?></p>
                            <?php if (!empty($issue['action_url'])) : ?>
                                <a href="<?php echo esc_url($issue['action_url']); ?>" class="button button-small">
                                    <?php _e('Düzelt', 'wp-ai-seo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- SEO Özeti -->
    <div class="summary-section">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-ai-seo')); ?>" class="button button-primary">
            <?php _e('Detaylı SEO Raporu', 'wp-ai-seo'); ?>
        </a>
    </div>
</div>

<style>
.wp-ai-seo-dashboard-widget {
    padding: 12px;
}

.wp-ai-seo-dashboard-widget .score-section {
    text-align: center;
    margin-bottom: 20px;
}

.wp-ai-seo-dashboard-widget .seo-score {
    display: inline-block;
    width: 80px;
    height: 80px;
    line-height: 80px;
    border-radius: 50%;
    font-size: 24px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 10px;
}

.wp-ai-seo-dashboard-widget .seo-score.good {
    background-color: #46b450;
}

.wp-ai-seo-dashboard-widget .seo-score.ok {
    background-color: #ffb900;
}

.wp-ai-seo-dashboard-widget .seo-score.bad {
    background-color: #dc3232;
}

.wp-ai-seo-dashboard-widget .stats-section ul {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.wp-ai-seo-dashboard-widget .stats-section li {
    display: flex;
    align-items: center;
    margin: 0;
}

.wp-ai-seo-dashboard-widget .stats-section .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    margin-right: 10px;
    color: #646970;
}

.wp-ai-seo-dashboard-widget .stat-content {
    display: flex;
    flex-direction: column;
}

.wp-ai-seo-dashboard-widget .stat-value {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
}

.wp-ai-seo-dashboard-widget .stat-label {
    font-size: 12px;
    color: #646970;
}

.wp-ai-seo-dashboard-widget .issues-section {
    margin: 20px 0;
}

.wp-ai-seo-dashboard-widget .issues-section h4 {
    margin: 0 0 10px;
}

.wp-ai-seo-dashboard-widget .issues-section ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.wp-ai-seo-dashboard-widget .issues-section li {
    display: flex;
    align-items: flex-start;
    margin: 0 0 10px;
    padding: 10px;
    background: #fff;
    border: 1px solid #dcdcde;
    border-left-width: 4px;
}

.wp-ai-seo-dashboard-widget .issues-section li.warning {
    border-left-color: #ffb900;
}

.wp-ai-seo-dashboard-widget .issues-section li.error {
    border-left-color: #dc3232;
}

.wp-ai-seo-dashboard-widget .issues-section .dashicons {
    margin-right: 10px;
    color: #646970;
}

.wp-ai-seo-dashboard-widget .issue-content {
    flex: 1;
}

.wp-ai-seo-dashboard-widget .issue-content p {
    margin: 0 0 5px;
}

.wp-ai-seo-dashboard-widget .summary-section {
    margin-top: 20px;
    text-align: center;
}
</style>