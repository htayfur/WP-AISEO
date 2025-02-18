<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_basic', []);
$defaults = [
    'title_separator' => '-',
    'homepage_title' => '%sitename% %separator% %sitedesc%',
    'post_title' => '%title% %separator% %sitename%',
    'page_title' => '%title% %separator% %sitename%',
    'category_title' => '%category% %separator% %sitename%',
    'tag_title' => '%tag% %separator% %sitename%'
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_basic_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_basic_nonce'], 'wp_ai_seo_basic_settings')) {
    
    // Ayarları güncelle
    $new_options = [];
    foreach ($defaults as $key => $default) {
        if (isset($_POST[$key])) {
            $new_options[$key] = sanitize_text_field($_POST[$key]);
        } else {
            $new_options[$key] = $default;
        }
    }
    
    update_option('wp_ai_seo_basic', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}

// Değişken açıklamaları
$variables = [
    '%sitename%' => __('Site adı', 'wp-ai-seo'),
    '%sitedesc%' => __('Site açıklaması', 'wp-ai-seo'),
    '%title%' => __('Sayfa/yazı başlığı', 'wp-ai-seo'),
    '%category%' => __('Kategori adı', 'wp-ai-seo'),
    '%tag%' => __('Etiket adı', 'wp-ai-seo'),
    '%separator%' => __('Ayraç', 'wp-ai-seo'),
    '%date%' => __('Yayın tarihi', 'wp-ai-seo'),
    '%author%' => __('Yazar adı', 'wp-ai-seo')
];
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_basic_settings', 'wp_ai_seo_basic_nonce'); ?>
    
    <!-- Başlık Ayraç Seçimi -->
    <div class="wp-ai-seo-field">
        <label for="title_separator">
            <?php _e('Başlık Ayracı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Başlık bölümlerini ayırmak için kullanılacak karakter', 'wp-ai-seo'); ?>">?</span>
        </label>
        <select name="title_separator" id="title_separator">
            <option value="-" <?php selected($options['title_separator'], '-'); ?>>-</option>
            <option value="·" <?php selected($options['title_separator'], '·'); ?>>·</option>
            <option value="/" <?php selected($options['title_separator'], '/'); ?>>/</option>
            <option value="|" <?php selected($options['title_separator'], '|'); ?>>|</option>
            <option value="»" <?php selected($options['title_separator'], '»'); ?>>»</option>
        </select>
    </div>

    <!-- Anasayfa Başlığı -->
    <div class="wp-ai-seo-field">
        <label for="homepage_title">
            <?php _e('Anasayfa Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Anasayfada görünecek başlık şablonu', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               name="homepage_title" 
               id="homepage_title" 
               value="<?php echo esc_attr($options['homepage_title']); ?>" 
               class="regular-text">
    </div>

    <!-- Yazı Başlığı -->
    <div class="wp-ai-seo-field">
        <label for="post_title">
            <?php _e('Yazı Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Blog yazılarında görünecek başlık şablonu', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               name="post_title" 
               id="post_title" 
               value="<?php echo esc_attr($options['post_title']); ?>" 
               class="regular-text">
    </div>

    <!-- Sayfa Başlığı -->
    <div class="wp-ai-seo-field">
        <label for="page_title">
            <?php _e('Sayfa Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Sayfalarda görünecek başlık şablonu', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               name="page_title" 
               id="page_title" 
               value="<?php echo esc_attr($options['page_title']); ?>" 
               class="regular-text">
    </div>

    <!-- Kategori Başlığı -->
    <div class="wp-ai-seo-field">
        <label for="category_title">
            <?php _e('Kategori Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Kategori sayfalarında görünecek başlık şablonu', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               name="category_title" 
               id="category_title" 
               value="<?php echo esc_attr($options['category_title']); ?>" 
               class="regular-text">
    </div>

    <!-- Etiket Başlığı -->
    <div class="wp-ai-seo-field">
        <label for="tag_title">
            <?php _e('Etiket Başlığı', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Etiket sayfalarında görünecek başlık şablonu', 'wp-ai-seo'); ?>">?</span>
        </label>
        <input type="text" 
               name="tag_title" 
               id="tag_title" 
               value="<?php echo esc_attr($options['tag_title']); ?>" 
               class="regular-text">
    </div>

    <!-- Kullanılabilir Değişkenler -->
    <div class="wp-ai-seo-field variables-info">
        <h3><?php _e('Kullanılabilir Değişkenler', 'wp-ai-seo'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Değişken', 'wp-ai-seo'); ?></th>
                    <th><?php _e('Açıklama', 'wp-ai-seo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variables as $var => $desc) : ?>
                    <tr>
                        <td><code><?php echo esc_html($var); ?></code></td>
                        <td><?php echo esc_html($desc); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Başlık önizleme
    function updateTitlePreview() {
        var type = $('#title_type').val();
        var template = $('#' + type + '_title').val();
        var separator = $('#title_separator').val();
        
        // Örnek değerler
        var values = {
            '%sitename%': '<?php echo esc_js(get_bloginfo('name')); ?>',
            '%sitedesc%': '<?php echo esc_js(get_bloginfo('description')); ?>',
            '%title%': '<?php _e('Örnek Başlık', 'wp-ai-seo'); ?>',
            '%category%': '<?php _e('Örnek Kategori', 'wp-ai-seo'); ?>',
            '%tag%': '<?php _e('Örnek Etiket', 'wp-ai-seo'); ?>',
            '%separator%': separator
        };
        
        var preview = template;
        for (var key in values) {
            preview = preview.replace(new RegExp(key, 'g'), values[key]);
        }
        
        $('#title_preview').text(preview);
    }
    
    // Event listeners
    $('#title_type, #title_separator, [id$="_title"]').on('change keyup', updateTitlePreview);
    updateTitlePreview();
});
</script>