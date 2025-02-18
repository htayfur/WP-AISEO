<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_advanced', []);
$defaults = [
    'enable_breadcrumbs' => 1,
    'enable_schema' => 1,
    'schema_type' => 'Organization',
    'organization_name' => '',
    'organization_logo' => '',
    'social_profiles' => [],
    'enable_local_seo' => 0,
    'business_name' => '',
    'business_type' => '',
    'business_address' => [
        'street' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'country' => ''
    ],
    'business_phone' => '',
    'business_email' => '',
    'business_hours' => [],
    'enable_woocommerce_schema' => 1,
    'product_schema' => [
        'show_price' => 1,
        'show_rating' => 1,
        'show_availability' => 1
    ]
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_advanced_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_advanced_nonce'], 'wp_ai_seo_advanced_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'enable_breadcrumbs' => isset($_POST['enable_breadcrumbs']) ? 1 : 0,
        'enable_schema' => isset($_POST['enable_schema']) ? 1 : 0,
        'schema_type' => sanitize_text_field($_POST['schema_type']),
        'organization_name' => sanitize_text_field($_POST['organization_name']),
        'organization_logo' => esc_url_raw($_POST['organization_logo']),
        'social_profiles' => array_map('esc_url_raw', (array) $_POST['social_profiles']),
        'enable_local_seo' => isset($_POST['enable_local_seo']) ? 1 : 0,
        'business_name' => sanitize_text_field($_POST['business_name']),
        'business_type' => sanitize_text_field($_POST['business_type']),
        'business_address' => [
            'street' => sanitize_text_field($_POST['business_address']['street']),
            'city' => sanitize_text_field($_POST['business_address']['city']),
            'state' => sanitize_text_field($_POST['business_address']['state']),
            'zip' => sanitize_text_field($_POST['business_address']['zip']),
            'country' => sanitize_text_field($_POST['business_address']['country'])
        ],
        'business_phone' => sanitize_text_field($_POST['business_phone']),
        'business_email' => sanitize_email($_POST['business_email']),
        'business_hours' => array_map('sanitize_text_field', (array) $_POST['business_hours']),
        'enable_woocommerce_schema' => isset($_POST['enable_woocommerce_schema']) ? 1 : 0,
        'product_schema' => [
            'show_price' => isset($_POST['product_schema']['show_price']) ? 1 : 0,
            'show_rating' => isset($_POST['product_schema']['show_rating']) ? 1 : 0,
            'show_availability' => isset($_POST['product_schema']['show_availability']) ? 1 : 0
        ]
    ];
    
    update_option('wp_ai_seo_advanced', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_advanced_settings', 'wp_ai_seo_advanced_nonce'); ?>
    
    <!-- Breadcrumbs Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Breadcrumbs Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Sayfa içi navigasyon ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_breadcrumbs" 
                       value="1" 
                       <?php checked($options['enable_breadcrumbs'], 1); ?>>
                <?php _e('Breadcrumbs Aktif', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Sayfalarda breadcrumbs navigasyonunu göster.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Schema.org Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Schema.org Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Yapılandırılmış veri ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_schema" 
                       value="1" 
                       <?php checked($options['enable_schema'], 1); ?>>
                <?php _e('Schema.org Aktif', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="wp-ai-seo-field">
            <label for="schema_type"><?php _e('Kuruluş Tipi', 'wp-ai-seo'); ?></label>
            <select name="schema_type" id="schema_type">
                <option value="Organization" <?php selected($options['schema_type'], 'Organization'); ?>>
                    <?php _e('Organizasyon', 'wp-ai-seo'); ?>
                </option>
                <option value="Corporation" <?php selected($options['schema_type'], 'Corporation'); ?>>
                    <?php _e('Şirket', 'wp-ai-seo'); ?>
                </option>
                <option value="LocalBusiness" <?php selected($options['schema_type'], 'LocalBusiness'); ?>>
                    <?php _e('Yerel İşletme', 'wp-ai-seo'); ?>
                </option>
            </select>
        </div>

        <div class="wp-ai-seo-field">
            <label for="organization_name"><?php _e('Kuruluş Adı', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="organization_name" 
                   id="organization_name" 
                   value="<?php echo esc_attr($options['organization_name']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="organization_logo"><?php _e('Kuruluş Logosu', 'wp-ai-seo'); ?></label>
            <input type="url" 
                   name="organization_logo" 
                   id="organization_logo" 
                   value="<?php echo esc_url($options['organization_logo']); ?>" 
                   class="regular-text">
            <button type="button" class="button wp-ai-seo-media-upload" data-target="organization_logo">
                <?php _e('Medya Yükle', 'wp-ai-seo'); ?>
            </button>
        </div>
    </div>

    <!-- Yerel SEO Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Yerel SEO Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Yerel işletme SEO ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_local_seo" 
                       value="1" 
                       <?php checked($options['enable_local_seo'], 1); ?>>
                <?php _e('Yerel SEO Aktif', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="local-seo-fields" style="display: <?php echo $options['enable_local_seo'] ? 'block' : 'none'; ?>;">
            <div class="wp-ai-seo-field">
                <label for="business_name"><?php _e('İşletme Adı', 'wp-ai-seo'); ?></label>
                <input type="text" 
                       name="business_name" 
                       id="business_name" 
                       value="<?php echo esc_attr($options['business_name']); ?>" 
                       class="regular-text">
            </div>

            <div class="wp-ai-seo-field">
                <label for="business_type"><?php _e('İşletme Tipi', 'wp-ai-seo'); ?></label>
                <input type="text" 
                       name="business_type" 
                       id="business_type" 
                       value="<?php echo esc_attr($options['business_type']); ?>" 
                       class="regular-text">
            </div>

            <div class="wp-ai-seo-field address-fields">
                <label><?php _e('İşletme Adresi', 'wp-ai-seo'); ?></label>
                <input type="text" 
                       name="business_address[street]" 
                       placeholder="<?php esc_attr_e('Sokak Adresi', 'wp-ai-seo'); ?>" 
                       value="<?php echo esc_attr($options['business_address']['street']); ?>" 
                       class="regular-text">
                <input type="text" 
                       name="business_address[city]" 
                       placeholder="<?php esc_attr_e('Şehir', 'wp-ai-seo'); ?>" 
                       value="<?php echo esc_attr($options['business_address']['city']); ?>" 
                       class="regular-text">
                <input type="text" 
                       name="business_address[state]" 
                       placeholder="<?php esc_attr_e('Eyalet/Bölge', 'wp-ai-seo'); ?>" 
                       value="<?php echo esc_attr($options['business_address']['state']); ?>" 
                       class="regular-text">
                <input type="text" 
                       name="business_address[zip]" 
                       placeholder="<?php esc_attr_e('Posta Kodu', 'wp-ai-seo'); ?>" 
                       value="<?php echo esc_attr($options['business_address']['zip']); ?>" 
                       class="regular-text">
                <input type="text" 
                       name="business_address[country]" 
                       placeholder="<?php esc_attr_e('Ülke', 'wp-ai-seo'); ?>" 
                       value="<?php echo esc_attr($options['business_address']['country']); ?>" 
                       class="regular-text">
            </div>

            <div class="wp-ai-seo-field">
                <label for="business_phone"><?php _e('İşletme Telefonu', 'wp-ai-seo'); ?></label>
                <input type="tel" 
                       name="business_phone" 
                       id="business_phone" 
                       value="<?php echo esc_attr($options['business_phone']); ?>" 
                       class="regular-text">
            </div>

            <div class="wp-ai-seo-field">
                <label for="business_email"><?php _e('İşletme E-posta', 'wp-ai-seo'); ?></label>
                <input type="email" 
                       name="business_email" 
                       id="business_email" 
                       value="<?php echo esc_attr($options['business_email']); ?>" 
                       class="regular-text">
            </div>
        </div>
    </div>

    <!-- WooCommerce SEO Ayarları -->
    <?php if (class_exists('WooCommerce')) : ?>
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('WooCommerce SEO Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('WooCommerce ürün şema ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_woocommerce_schema" 
                       value="1" 
                       <?php checked($options['enable_woocommerce_schema'], 1); ?>>
                <?php _e('WooCommerce Schema Aktif', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="woocommerce-schema-fields" style="display: <?php echo $options['enable_woocommerce_schema'] ? 'block' : 'none'; ?>;">
            <div class="wp-ai-seo-field">
                <label>
                    <input type="checkbox" 
                           name="product_schema[show_price]" 
                           value="1" 
                           <?php checked($options['product_schema']['show_price'], 1); ?>>
                    <?php _e('Fiyat Bilgisini Göster', 'wp-ai-seo'); ?>
                </label>
            </div>

            <div class="wp-ai-seo-field">
                <label>
                    <input type="checkbox" 
                           name="product_schema[show_rating]" 
                           value="1" 
                           <?php checked($options['product_schema']['show_rating'], 1); ?>>
                    <?php _e('Değerlendirme Bilgisini Göster', 'wp-ai-seo'); ?>
                </label>
            </div>

            <div class="wp-ai-seo-field">
                <label>
                    <input type="checkbox" 
                           name="product_schema[show_availability]" 
                           value="1" 
                           <?php checked($options['product_schema']['show_availability'], 1); ?>>
                    <?php _e('Stok Durumunu Göster', 'wp-ai-seo'); ?>
                </label>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Yerel SEO alanlarını göster/gizle
    $('input[name="enable_local_seo"]').on('change', function() {
        $('.local-seo-fields').toggle(this.checked);
    });

    // WooCommerce şema alanlarını göster/gizle
    $('input[name="enable_woocommerce_schema"]').on('change', function() {
        $('.woocommerce-schema-fields').toggle(this.checked);
    });

    // Medya yükleyici
    $('.wp-ai-seo-media-upload').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = $('#' + button.data('target'));
        
        var mediaUploader = wp.media({
            title: '<?php _e('Logo Seç', 'wp-ai-seo'); ?>',
            button: {
                text: '<?php _e('Seç', 'wp-ai-seo'); ?>'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            targetInput.val(attachment.url);
        });

        mediaUploader.open();
    });
});
</script>