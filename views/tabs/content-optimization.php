<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_content', []);
$defaults = [
    'min_word_count' => 300,
    'max_heading_length' => 60,
    'enable_auto_linking' => 1,
    'max_auto_links' => 3,
    'image_alt_required' => 1,
    'readability_check' => 1,
    'keyword_density' => [
        'min' => 0.5,
        'max' => 2.5
    ]
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_content_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_content_nonce'], 'wp_ai_seo_content_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'min_word_count' => intval($_POST['min_word_count']),
        'max_heading_length' => intval($_POST['max_heading_length']),
        'enable_auto_linking' => isset($_POST['enable_auto_linking']) ? 1 : 0,
        'max_auto_links' => intval($_POST['max_auto_links']),
        'image_alt_required' => isset($_POST['image_alt_required']) ? 1 : 0,
        'readability_check' => isset($_POST['readability_check']) ? 1 : 0,
        'keyword_density' => [
            'min' => floatval($_POST['keyword_density_min']),
            'max' => floatval($_POST['keyword_density_max'])
        ]
    ];
    
    update_option('wp_ai_seo_content', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_content_settings', 'wp_ai_seo_content_nonce'); ?>
    
    <!-- İçerik Gereksinimleri -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('İçerik Gereksinimleri', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('İçerik kalitesi için minimum gereksinimler', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label for="min_word_count">
                <?php _e('Minimum Kelime Sayısı', 'wp-ai-seo'); ?>
                <span class="wp-ai-seo-info" title="<?php esc_attr_e('İçeriğin minimum kelime sayısı', 'wp-ai-seo'); ?>">?</span>
            </label>
            <input type="number" 
                   name="min_word_count" 
                   id="min_word_count" 
                   value="<?php echo esc_attr($options['min_word_count']); ?>" 
                   min="100" 
                   max="1000" 
                   step="50">
            <p class="description">
                <?php _e('SEO için önerilen minimum kelime sayısı 300\'dür.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="max_heading_length">
                <?php _e('Maksimum Başlık Uzunluğu', 'wp-ai-seo'); ?>
            </label>
            <input type="number" 
                   name="max_heading_length" 
                   id="max_heading_length" 
                   value="<?php echo esc_attr($options['max_heading_length']); ?>" 
                   min="40" 
                   max="80" 
                   step="5">
            <p class="description">
                <?php _e('SEO için önerilen maksimum başlık uzunluğu 60 karakterdir.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Anahtar Kelime Yoğunluğu -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Anahtar Kelime Yoğunluğu', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('İçerikteki anahtar kelime kullanım oranı', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="keyword_density_min">
                <?php _e('Minimum Yoğunluk (%)', 'wp-ai-seo'); ?>
            </label>
            <input type="number" 
                   name="keyword_density_min" 
                   id="keyword_density_min" 
                   value="<?php echo esc_attr($options['keyword_density']['min']); ?>" 
                   min="0.1" 
                   max="5" 
                   step="0.1">
        </div>

        <div class="wp-ai-seo-field">
            <label for="keyword_density_max">
                <?php _e('Maksimum Yoğunluk (%)', 'wp-ai-seo'); ?>
            </label>
            <input type="number" 
                   name="keyword_density_max" 
                   id="keyword_density_max" 
                   value="<?php echo esc_attr($options['keyword_density']['max']); ?>" 
                   min="0.1" 
                   max="5" 
                   step="0.1">
            <p class="description">
                <?php _e('Önerilen anahtar kelime yoğunluğu %0.5 ile %2.5 arasındadır.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Otomatik Bağlantı Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Otomatik Bağlantı Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('İç bağlantılar için otomatik ayarlar', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_auto_linking" 
                       value="1" 
                       <?php checked($options['enable_auto_linking'], 1); ?>>
                <?php _e('Otomatik İç Bağlantı Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Anahtar kelimeleri otomatik olarak ilgili içeriklere bağlar.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="max_auto_links">
                <?php _e('Maksimum Otomatik Bağlantı Sayısı', 'wp-ai-seo'); ?>
            </label>
            <input type="number" 
                   name="max_auto_links" 
                   id="max_auto_links" 
                   value="<?php echo esc_attr($options['max_auto_links']); ?>" 
                   min="1" 
                   max="10">
            <p class="description">
                <?php _e('Her içerik için eklenecek maksimum otomatik bağlantı sayısı.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Görsel Optimizasyonu -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Görsel Optimizasyonu', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Görseller için SEO ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="image_alt_required" 
                       value="1" 
                       <?php checked($options['image_alt_required'], 1); ?>>
                <?php _e('Alt Etiketi Zorunlu', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Görseller için alt etiketi girilmesini zorunlu tutar.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Okunabilirlik Kontrolü -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Okunabilirlik Kontrolü', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('İçerik okunabilirlik analizi', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="readability_check" 
                       value="1" 
                       <?php checked($options['readability_check'], 1); ?>>
                <?php _e('Okunabilirlik Analizi Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('İçeriklerin okunabilirlik seviyesini Flesch-Kincaid metriği ile analiz eder.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Anahtar kelime yoğunluğu kontrolü
    $('#keyword_density_min, #keyword_density_max').on('change', function() {
        var min = parseFloat($('#keyword_density_min').val());
        var max = parseFloat($('#keyword_density_max').val());
        
        if (min >= max) {
            alert('<?php echo esc_js(__('Minimum yoğunluk, maksimum yoğunluktan küçük olmalıdır.', 'wp-ai-seo')); ?>');
            $(this).val(this.defaultValue);
        }
    });
});
</script>