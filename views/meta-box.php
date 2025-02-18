<?php
/**
 * SEO meta kutusu şablonu
 * 
 * @var array $meta Mevcut meta verileri
 * @var WP_Post $post Mevcut yazı/sayfa
 */

if (!defined('ABSPATH')) {
    exit;
}

$meta = wp_parse_args($meta, [
    'meta_title' => '',
    'meta_description' => '',
    'focus_keywords' => '',
    'canonical_url' => '',
    'robots_meta' => ''
]);

// Varsayılan robots meta seçenekleri
$robots_options = [
    'index,follow' => __('İndeksle ve Takip Et', 'wp-ai-seo'),
    'noindex,follow' => __('İndeksleme, Takip Et', 'wp-ai-seo'),
    'index,nofollow' => __('İndeksle, Takip Etme', 'wp-ai-seo'),
    'noindex,nofollow' => __('İndeksleme, Takip Etme', 'wp-ai-seo')
];
?>

<div class="wp-ai-seo-meta-box">
    <!-- Meta başlık -->
    <div class="wp-ai-seo-field">
        <label for="wp_ai_seo_meta_title">
            <?php _e('SEO Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Arama sonuçlarında görünecek başlık', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               id="wp_ai_seo_meta_title" 
               name="wp_ai_seo_meta_title" 
               value="<?php echo esc_attr($meta['meta_title']); ?>" 
               class="widefat"
               maxlength="60">
        <div class="wp-ai-seo-counter">
            <span class="wp-ai-seo-counter-current">0</span>/60
        </div>
        <p class="wp-ai-seo-preview-title"></p>
    </div>

    <!-- Meta açıklama -->
    <div class="wp-ai-seo-field">
        <label for="wp_ai_seo_meta_description">
            <?php _e('Meta Açıklama', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Arama sonuçlarında görünecek açıklama', 'wp-ai-seo'); ?>">?</span>
        </label>
        <textarea id="wp_ai_seo_meta_description" 
                  name="wp_ai_seo_meta_description" 
                  class="widefat" 
                  rows="3" 
                  maxlength="160"><?php echo esc_textarea($meta['meta_description']); ?></textarea>
        <div class="wp-ai-seo-counter">
            <span class="wp-ai-seo-counter-current">0</span>/160
        </div>
        <p class="wp-ai-seo-preview-description"></p>
    </div>

    <!-- Odak anahtar kelimeleri -->
    <div class="wp-ai-seo-field">
        <label for="wp_ai_seo_focus_keywords">
            <?php _e('Odak Anahtar Kelimeler', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Virgülle ayırarak birden fazla anahtar kelime ekleyebilirsiniz', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               id="wp_ai_seo_focus_keywords" 
               name="wp_ai_seo_focus_keywords" 
               value="<?php echo esc_attr($meta['focus_keywords']); ?>" 
               class="widefat">
    </div>

    <!-- Canonical URL -->
    <div class="wp-ai-seo-field">
        <label for="wp_ai_seo_canonical_url">
            <?php _e('Canonical URL', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Bu içeriğin asıl/kaynak URL adresi', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="url" 
               id="wp_ai_seo_canonical_url" 
               name="wp_ai_seo_canonical_url" 
               value="<?php echo esc_url($meta['canonical_url']); ?>" 
               class="widefat">
    </div>

    <!-- Robots meta -->
    <div class="wp-ai-seo-field">
        <label for="wp_ai_seo_robots_meta">
            <?php _e('Robots Meta', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Arama motorları için yönergeler', 'wp-ai-seo'); ?>">?</span>
        </label>
        <select id="wp_ai_seo_robots_meta" 
                name="wp_ai_seo_robots_meta" 
                class="widefat">
            <?php foreach ($robots_options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" 
                    <?php selected($meta['robots_meta'], $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<style>
.wp-ai-seo-meta-box {
    padding: 10px;
}

.wp-ai-seo-field {
    margin-bottom: 20px;
}

.wp-ai-seo-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.wp-ai-seo-info {
    display: inline-block;
    width: 16px;
    height: 16px;
    line-height: 16px;
    text-align: center;
    background: #e5e5e5;
    border-radius: 50%;
    color: #666;
    font-size: 11px;
    cursor: help;
    margin-left: 5px;
}

.wp-ai-seo-counter {
    text-align: right;
    color: #666;
    font-size: 12px;
    margin-top: 5px;
}

.wp-ai-seo-preview-title,
.wp-ai-seo-preview-description {
    margin: 10px 0 0;
    padding: 10px;
    background: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: Arial, sans-serif;
    font-size: 13px;
    color: #444;
}

.wp-ai-seo-preview-title {
    color: #1a0dab;
    font-size: 18px;
    text-decoration: none;
    cursor: pointer;
}

.wp-ai-seo-preview-title:hover {
    text-decoration: underline;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Karakter sayacı fonksiyonu
    function updateCounter(input) {
        var $input = $(input);
        var $counter = $input.siblings('.wp-ai-seo-counter').find('.wp-ai-seo-counter-current');
        var count = $input.val().length;
        var max = $input.attr('maxlength');
        
        $counter.text(count);
        
        if (count > max * 0.9) {
            $counter.css('color', '#dc3232');
        } else if (count > max * 0.7) {
            $counter.css('color', '#ffb900');
        } else {
            $counter.css('color', '#666');
        }
    }

    // Önizleme güncelleme fonksiyonu
    function updatePreview() {
        var title = $('#wp_ai_seo_meta_title').val() || '<?php echo esc_js(get_the_title($post->ID)); ?>';
        var desc = $('#wp_ai_seo_meta_description').val() || '<?php echo esc_js(wp_trim_words($post->post_content, 30)); ?>';
        
        $('.wp-ai-seo-preview-title').text(title);
        $('.wp-ai-seo-preview-description').text(desc);
    }

    // Event listeners
    $('#wp_ai_seo_meta_title, #wp_ai_seo_meta_description').on('input', function() {
        updateCounter(this);
        updatePreview();
    }).trigger('input');
});
</script>