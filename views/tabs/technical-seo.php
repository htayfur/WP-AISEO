<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_technical', []);
$defaults = [
    'enable_sitemap' => 1,
    'enable_html_sitemap' => 1,
    'sitemap_include' => ['post', 'page'],
    'sitemap_exclude' => [],
    'robots_settings' => [
        'noindex' => [],
        'nofollow' => []
    ],
    'redirects' => []
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_technical_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_technical_nonce'], 'wp_ai_seo_technical_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'enable_sitemap' => isset($_POST['enable_sitemap']) ? 1 : 0,
        'enable_html_sitemap' => isset($_POST['enable_html_sitemap']) ? 1 : 0,
        'sitemap_include' => isset($_POST['sitemap_include']) ? array_map('sanitize_text_field', $_POST['sitemap_include']) : [],
        'sitemap_exclude' => isset($_POST['sitemap_exclude']) ? array_map('sanitize_text_field', $_POST['sitemap_exclude']) : [],
        'robots_settings' => [
            'noindex' => isset($_POST['noindex']) ? array_map('sanitize_text_field', $_POST['noindex']) : [],
            'nofollow' => isset($_POST['nofollow']) ? array_map('sanitize_text_field', $_POST['nofollow']) : []
        ]
    ];
    
    update_option('wp_ai_seo_technical', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}

// Kullanılabilir post tipleri
$post_types = get_post_types(['public' => true], 'objects');
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_technical_settings', 'wp_ai_seo_technical_nonce'); ?>
    
    <!-- Site Haritası Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Site Haritası Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('XML ve HTML site haritası ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_sitemap" 
                       value="1" 
                       <?php checked($options['enable_sitemap'], 1); ?>>
                <?php _e('XML Site Haritası Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('XML site haritası otomatik olarak oluşturulur ve arama motorlarına gönderilir.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_html_sitemap" 
                       value="1" 
                       <?php checked($options['enable_html_sitemap'], 1); ?>>
                <?php _e('HTML Site Haritası Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Ziyaretçiler için kategorize edilmiş HTML site haritası oluşturur.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label><?php _e('Site Haritasına Dahil Edilecek İçerikler', 'wp-ai-seo'); ?></label>
            <?php foreach ($post_types as $post_type) : ?>
                <label class="wp-ai-seo-checkbox">
                    <input type="checkbox" 
                           name="sitemap_include[]" 
                           value="<?php echo esc_attr($post_type->name); ?>"
                           <?php checked(in_array($post_type->name, $options['sitemap_include'])); ?>>
                    <?php echo esc_html($post_type->label); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Robots.txt Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Robots.txt Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Arama motoru robotları için yönergeler', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label><?php _e('İndekslenmeyecek İçerikler (noindex)', 'wp-ai-seo'); ?></label>
            <?php foreach ($post_types as $post_type) : ?>
                <label class="wp-ai-seo-checkbox">
                    <input type="checkbox" 
                           name="noindex[]" 
                           value="<?php echo esc_attr($post_type->name); ?>"
                           <?php checked(in_array($post_type->name, $options['robots_settings']['noindex'])); ?>>
                    <?php echo esc_html($post_type->label); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="wp-ai-seo-field">
            <label><?php _e('Takip Edilmeyecek İçerikler (nofollow)', 'wp-ai-seo'); ?></label>
            <?php foreach ($post_types as $post_type) : ?>
                <label class="wp-ai-seo-checkbox">
                    <input type="checkbox" 
                           name="nofollow[]" 
                           value="<?php echo esc_attr($post_type->name); ?>"
                           <?php checked(in_array($post_type->name, $options['robots_settings']['nofollow'])); ?>>
                    <?php echo esc_html($post_type->label); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="wp-ai-seo-field">
            <label for="custom_robots"><?php _e('Özel Robots.txt Kuralları', 'wp-ai-seo'); ?></label>
            <textarea name="custom_robots" 
                      id="custom_robots" 
                      rows="10" 
                      class="large-text code"><?php echo esc_textarea($options['custom_robots'] ?? ''); ?></textarea>
            <p class="description">
                <?php _e('Her satıra bir kural yazın. Örnek:', 'wp-ai-seo'); ?>
                <br>
                <code>User-agent: *</code>
                <br>
                <code>Disallow: /wp-admin/</code>
            </p>
        </div>
    </div>

    <!-- Yönlendirme Yönetimi -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Yönlendirme Yönetimi', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('301, 302 ve 307 yönlendirmelerini yönetin', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div id="redirect-list">
            <?php if (!empty($options['redirects'])) : 
                foreach ($options['redirects'] as $index => $redirect) : ?>
                <div class="redirect-item">
                    <select name="redirects[<?php echo $index; ?>][type]">
                        <option value="301" <?php selected($redirect['type'], '301'); ?>>301 - Kalıcı</option>
                        <option value="302" <?php selected($redirect['type'], '302'); ?>>302 - Geçici</option>
                        <option value="307" <?php selected($redirect['type'], '307'); ?>>307 - Geçici</option>
                    </select>
                    <input type="text" 
                           name="redirects[<?php echo $index; ?>][source]" 
                           value="<?php echo esc_attr($redirect['source']); ?>" 
                           placeholder="/eski-url" 
                           class="regular-text">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                    <input type="text" 
                           name="redirects[<?php echo $index; ?>][target]" 
                           value="<?php echo esc_attr($redirect['target']); ?>" 
                           placeholder="/yeni-url" 
                           class="regular-text">
                    <button type="button" class="button remove-redirect">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
                <?php endforeach;
            endif; ?>
        </div>

        <button type="button" class="button" id="add-redirect">
            <?php _e('Yönlendirme Ekle', 'wp-ai-seo'); ?>
        </button>
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Yönlendirme ekle/kaldır işlevselliği
    var redirectTemplate = `
        <div class="redirect-item">
            <select name="redirects[{{index}}][type]">
                <option value="301">301 - Kalıcı</option>
                <option value="302">302 - Geçici</option>
                <option value="307">307 - Geçici</option>
            </select>
            <input type="text" 
                   name="redirects[{{index}}][source]" 
                   placeholder="/eski-url" 
                   class="regular-text">
            <span class="dashicons dashicons-arrow-right-alt"></span>
            <input type="text" 
                   name="redirects[{{index}}][target]" 
                   placeholder="/yeni-url" 
                   class="regular-text">
            <button type="button" class="button remove-redirect">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
    `;

    $('#add-redirect').on('click', function() {
        var index = $('.redirect-item').length;
        var newItem = redirectTemplate.replace(/{{index}}/g, index);
        $('#redirect-list').append(newItem);
    });

    $(document).on('click', '.remove-redirect', function() {
        $(this).closest('.redirect-item').remove();
    });
});
</script>