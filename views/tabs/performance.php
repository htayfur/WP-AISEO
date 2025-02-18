<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_performance', []);
$defaults = [
    'enable_pagespeed' => 1,
    'pagespeed_api_key' => '',
    'enable_mobile_check' => 1,
    'enable_security' => 1,
    'bad_bot_list' => [
        'semrush',
        'ahrefs',
        'mj12bot',
        'dotbot',
        'rogerbot'
    ],
    'enable_db_cleanup' => 1,
    'cleanup_schedule' => 'weekly',
    'cleanup_items' => [
        'revisions' => 1,
        'auto_drafts' => 1,
        'trash_posts' => 1,
        'spam_comments' => 1,
        'trash_comments' => 1,
        'expired_transients' => 1,
        'unused_tags' => 1,
        'unused_meta' => 1
    ]
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_performance_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_performance_nonce'], 'wp_ai_seo_performance_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'enable_pagespeed' => isset($_POST['enable_pagespeed']) ? 1 : 0,
        'pagespeed_api_key' => sanitize_text_field($_POST['pagespeed_api_key']),
        'enable_mobile_check' => isset($_POST['enable_mobile_check']) ? 1 : 0,
        'enable_security' => isset($_POST['enable_security']) ? 1 : 0,
        'bad_bot_list' => array_map('sanitize_text_field', explode("\n", $_POST['bad_bot_list'])),
        'enable_db_cleanup' => isset($_POST['enable_db_cleanup']) ? 1 : 0,
        'cleanup_schedule' => sanitize_text_field($_POST['cleanup_schedule']),
        'cleanup_items' => [
            'revisions' => isset($_POST['cleanup_items']['revisions']) ? 1 : 0,
            'auto_drafts' => isset($_POST['cleanup_items']['auto_drafts']) ? 1 : 0,
            'trash_posts' => isset($_POST['cleanup_items']['trash_posts']) ? 1 : 0,
            'spam_comments' => isset($_POST['cleanup_items']['spam_comments']) ? 1 : 0,
            'trash_comments' => isset($_POST['cleanup_items']['trash_comments']) ? 1 : 0,
            'expired_transients' => isset($_POST['cleanup_items']['expired_transients']) ? 1 : 0,
            'unused_tags' => isset($_POST['cleanup_items']['unused_tags']) ? 1 : 0,
            'unused_meta' => isset($_POST['cleanup_items']['unused_meta']) ? 1 : 0
        ]
    ];
    
    update_option('wp_ai_seo_performance', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_performance_settings', 'wp_ai_seo_performance_nonce'); ?>
    
    <!-- PageSpeed Insights Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('PageSpeed Insights Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Google PageSpeed Insights API ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_pagespeed" 
                       value="1" 
                       <?php checked($options['enable_pagespeed'], 1); ?>>
                <?php _e('PageSpeed Analizi Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Google PageSpeed Insights ile sayfa hız analizini etkinleştirir.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="pagespeed_api_key">
                <?php _e('PageSpeed API Anahtarı', 'wp-ai-seo'); ?>
                <span class="wp-ai-seo-info" title="<?php esc_attr_e('Google Cloud Console\'dan alınan API anahtarı', 'wp-ai-seo'); ?>">?</span>
            </label>
            <input type="text" 
                   name="pagespeed_api_key" 
                   id="pagespeed_api_key" 
                   value="<?php echo esc_attr($options['pagespeed_api_key']); ?>" 
                   class="regular-text">
            <p class="description">
                <?php 
                printf(
                    __('API anahtarını %sGoogle Cloud Console%s\'dan alabilirsiniz.', 'wp-ai-seo'),
                    '<a href="https://console.cloud.google.com/apis/credentials" target="_blank">',
                    '</a>'
                ); 
                ?>
            </p>
        </div>
    </div>

    <!-- Mobil Uyumluluk Kontrolü -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Mobil Uyumluluk Kontrolü', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Mobil uyumluluk test ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_mobile_check" 
                       value="1" 
                       <?php checked($options['enable_mobile_check'], 1); ?>>
                <?php _e('Mobil Uyumluluk Testi Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('İçeriklerin mobil uyumluluğunu otomatik olarak test eder.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Güvenlik Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Güvenlik Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Bot koruma ve güvenlik ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_security" 
                       value="1" 
                       <?php checked($options['enable_security'], 1); ?>>
                <?php _e('Bot Koruması Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Zararlı botları engeller ve spam içerikleri tespit eder.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="bad_bot_list">
                <?php _e('Engellenecek Bot Listesi', 'wp-ai-seo'); ?>
                <span class="wp-ai-seo-info" title="<?php esc_attr_e('Her satıra bir bot adı yazın', 'wp-ai-seo'); ?>">?</span>
            </label>
            <textarea name="bad_bot_list" 
                      id="bad_bot_list" 
                      rows="5" 
                      class="large-text code"><?php echo esc_textarea(implode("\n", $options['bad_bot_list'])); ?></textarea>
        </div>
    </div>

    <!-- Veritabanı Temizliği -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Veritabanı Temizliği', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Otomatik veritabanı temizleme ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_db_cleanup" 
                       value="1" 
                       <?php checked($options['enable_db_cleanup'], 1); ?>>
                <?php _e('Otomatik Temizlik Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Veritabanını belirli aralıklarla otomatik olarak temizler.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="cleanup_schedule"><?php _e('Temizlik Sıklığı', 'wp-ai-seo'); ?></label>
            <select name="cleanup_schedule" id="cleanup_schedule">
                <option value="daily" <?php selected($options['cleanup_schedule'], 'daily'); ?>>
                    <?php _e('Günlük', 'wp-ai-seo'); ?>
                </option>
                <option value="weekly" <?php selected($options['cleanup_schedule'], 'weekly'); ?>>
                    <?php _e('Haftalık', 'wp-ai-seo'); ?>
                </option>
                <option value="monthly" <?php selected($options['cleanup_schedule'], 'monthly'); ?>>
                    <?php _e('Aylık', 'wp-ai-seo'); ?>
                </option>
            </select>
        </div>

        <div class="wp-ai-seo-field cleanup-items">
            <label><?php _e('Temizlenecek Öğeler', 'wp-ai-seo'); ?></label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[revisions]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['revisions'], 1); ?>>
                <?php _e('Yazı revizyonları', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[auto_drafts]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['auto_drafts'], 1); ?>>
                <?php _e('Otomatik taslaklar', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[trash_posts]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['trash_posts'], 1); ?>>
                <?php _e('Çöp kutusundaki yazılar', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[spam_comments]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['spam_comments'], 1); ?>>
                <?php _e('Spam yorumlar', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[trash_comments]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['trash_comments'], 1); ?>>
                <?php _e('Çöp kutusundaki yorumlar', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[expired_transients]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['expired_transients'], 1); ?>>
                <?php _e('Süresi dolmuş geçici veriler', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[unused_tags]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['unused_tags'], 1); ?>>
                <?php _e('Kullanılmayan etiketler', 'wp-ai-seo'); ?>
            </label>
            
            <label class="wp-ai-seo-checkbox">
                <input type="checkbox" 
                       name="cleanup_items[unused_meta]" 
                       value="1" 
                       <?php checked($options['cleanup_items']['unused_meta'], 1); ?>>
                <?php _e('Kullanılmayan meta veriler', 'wp-ai-seo'); ?>
            </label>
        </div>
    </div>

    <?php 
    // Mevcut temizlik istatistikleri
    global $wpdb;
    $stats = [
        'revisions' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'"),
        'auto_drafts' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft'"),
        'trash_posts' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash'"),
        'spam_comments' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'"),
        'trash_comments' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash'"),
        'expired_transients' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()"),
        'unused_tags' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->terms t LEFT JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id WHERE tt.taxonomy = 'post_tag' AND tt.count = 0"),
        'unused_meta' => $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key NOT IN ('_edit_lock', '_edit_last') AND post_id NOT IN (SELECT ID FROM $wpdb->posts)")
    ];
    ?>

    <div class="wp-ai-seo-card">
        <h3><?php _e('Temizlik İstatistikleri', 'wp-ai-seo'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Öğe', 'wp-ai-seo'); ?></th>
                    <th><?php _e('Sayı', 'wp-ai-seo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Yazı revizyonları', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['revisions']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Otomatik taslaklar', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['auto_drafts']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Çöp kutusundaki yazılar', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['trash_posts']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Spam yorumlar', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['spam_comments']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Çöp kutusundaki yorumlar', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['trash_comments']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Süresi dolmuş geçici veriler', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['expired_transients']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Kullanılmayan etiketler', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['unused_tags']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Kullanılmayan meta veriler', 'wp-ai-seo'); ?></td>
                    <td><?php echo number_format_i18n($stats['unused_meta']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="wp-ai-seo-card">
        <button type="button" 
                class="button button-primary" 
                id="wp-ai-seo-cleanup-now">
            <?php _e('Şimdi Temizle', 'wp-ai-seo'); ?>
        </button>
        <span class="spinner"></span>
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Manuel temizlik işlemi
    $('#wp-ai-seo-cleanup-now').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        
        if ($button.hasClass('disabled')) {
            return;
        }
        
        if (!confirm('<?php echo esc_js(__('Seçili öğeleri temizlemek istediğinizden emin misiniz?', 'wp-ai-seo')); ?>')) {
            return;
        }
        
        $button.addClass('disabled');
        $spinner.addClass('is-active');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_ai_seo_cleanup',
                nonce: $('#wp_ai_seo_performance_nonce').val(),
                items: $('input[name^="cleanup_items"]:checked').serialize()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('Temizlik işlemi sırasında bir hata oluştu.', 'wp-ai-seo')); ?>');
            },
            complete: function() {
                $button.removeClass('disabled');
                $spinner.removeClass('is-active');
            }
        });
    });
});
</script>