<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_social', []);
$defaults = [
    'enable_social_meta' => 1,
    'facebook' => [
        'app_id' => '',
        'admin_id' => '',
        'default_image' => '',
        'default_card_type' => 'summary_large_image'
    ],
    'twitter' => [
        'card_type' => 'summary_large_image',
        'username' => '',
        'default_image' => ''
    ],
    'linkedin' => [
        'company_id' => '',
        'default_image' => ''
    ],
    'pinterest' => [
        'verify_meta' => '',
        'default_image' => ''
    ],
    'social_templates' => [
        'post_title' => '%title% | %sitename%',
        'post_description' => '%excerpt%',
        'post_image' => '%featured_image%'
    ]
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_social_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_social_nonce'], 'wp_ai_seo_social_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'enable_social_meta' => isset($_POST['enable_social_meta']) ? 1 : 0,
        'facebook' => [
            'app_id' => sanitize_text_field($_POST['facebook_app_id']),
            'admin_id' => sanitize_text_field($_POST['facebook_admin_id']),
            'default_image' => esc_url_raw($_POST['facebook_default_image']),
            'default_card_type' => sanitize_text_field($_POST['facebook_card_type'])
        ],
        'twitter' => [
            'card_type' => sanitize_text_field($_POST['twitter_card_type']),
            'username' => sanitize_text_field($_POST['twitter_username']),
            'default_image' => esc_url_raw($_POST['twitter_default_image'])
        ],
        'linkedin' => [
            'company_id' => sanitize_text_field($_POST['linkedin_company_id']),
            'default_image' => esc_url_raw($_POST['linkedin_default_image'])
        ],
        'pinterest' => [
            'verify_meta' => sanitize_text_field($_POST['pinterest_verify_meta']),
            'default_image' => esc_url_raw($_POST['pinterest_default_image'])
        ],
        'social_templates' => [
            'post_title' => sanitize_text_field($_POST['social_title_template']),
            'post_description' => sanitize_text_field($_POST['social_description_template']),
            'post_image' => sanitize_text_field($_POST['social_image_template'])
        ]
    ];
    
    update_option('wp_ai_seo_social', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}

// Kullanılabilir değişkenler
$variables = [
    '%title%' => __('Yazı başlığı', 'wp-ai-seo'),
    '%sitename%' => __('Site adı', 'wp-ai-seo'),
    '%excerpt%' => __('Yazı özeti', 'wp-ai-seo'),
    '%featured_image%' => __('Öne çıkarılan görsel', 'wp-ai-seo'),
    '%author%' => __('Yazar adı', 'wp-ai-seo'),
    '%category%' => __('Birincil kategori', 'wp-ai-seo')
];
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_social_settings', 'wp_ai_seo_social_nonce'); ?>
    
    <!-- Genel Sosyal Medya Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Genel Sosyal Medya Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Sosyal medya meta etiketleri için genel ayarlar', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_social_meta" 
                       value="1" 
                       <?php checked($options['enable_social_meta'], 1); ?>>
                <?php _e('Sosyal Medya Meta Etiketlerini Aktifleştir', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Open Graph ve Twitter Card meta etiketlerini ekler.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <!-- Facebook Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Facebook Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Facebook Open Graph ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="facebook_app_id"><?php _e('Facebook Uygulama ID', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="facebook_app_id" 
                   id="facebook_app_id" 
                   value="<?php echo esc_attr($options['facebook']['app_id']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="facebook_admin_id"><?php _e('Facebook Admin ID', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="facebook_admin_id" 
                   id="facebook_admin_id" 
                   value="<?php echo esc_attr($options['facebook']['admin_id']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="facebook_default_image"><?php _e('Varsayılan Facebook Görseli', 'wp-ai-seo'); ?></label>
            <input type="url" 
                   name="facebook_default_image" 
                   id="facebook_default_image" 
                   value="<?php echo esc_url($options['facebook']['default_image']); ?>" 
                   class="regular-text">
            <button type="button" class="button wp-ai-seo-media-upload" data-target="facebook_default_image">
                <?php _e('Medya Yükle', 'wp-ai-seo'); ?>
            </button>
        </div>
    </div>

    <!-- Twitter Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Twitter Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Twitter Card ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="twitter_card_type"><?php _e('Twitter Card Tipi', 'wp-ai-seo'); ?></label>
            <select name="twitter_card_type" id="twitter_card_type">
                <option value="summary" <?php selected($options['twitter']['card_type'], 'summary'); ?>>
                    <?php _e('Özet', 'wp-ai-seo'); ?>
                </option>
                <option value="summary_large_image" <?php selected($options['twitter']['card_type'], 'summary_large_image'); ?>>
                    <?php _e('Büyük Görsel', 'wp-ai-seo'); ?>
                </option>
            </select>
        </div>

        <div class="wp-ai-seo-field">
            <label for="twitter_username"><?php _e('Twitter Kullanıcı Adı', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="twitter_username" 
                   id="twitter_username" 
                   value="<?php echo esc_attr($options['twitter']['username']); ?>" 
                   class="regular-text">
            <p class="description">
                <?php _e('@ işareti olmadan kullanıcı adınızı girin.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label for="twitter_default_image"><?php _e('Varsayılan Twitter Görseli', 'wp-ai-seo'); ?></label>
            <input type="url" 
                   name="twitter_default_image" 
                   id="twitter_default_image" 
                   value="<?php echo esc_url($options['twitter']['default_image']); ?>" 
                   class="regular-text">
            <button type="button" class="button wp-ai-seo-media-upload" data-target="twitter_default_image">
                <?php _e('Medya Yükle', 'wp-ai-seo'); ?>
            </button>
        </div>
    </div>

    <!-- LinkedIn Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('LinkedIn Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('LinkedIn paylaşım ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="linkedin_company_id"><?php _e('LinkedIn Şirket ID', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="linkedin_company_id" 
                   id="linkedin_company_id" 
                   value="<?php echo esc_attr($options['linkedin']['company_id']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="linkedin_default_image"><?php _e('Varsayılan LinkedIn Görseli', 'wp-ai-seo'); ?></label>
            <input type="url" 
                   name="linkedin_default_image" 
                   id="linkedin_default_image" 
                   value="<?php echo esc_url($options['linkedin']['default_image']); ?>" 
                   class="regular-text">
            <button type="button" class="button wp-ai-seo-media-upload" data-target="linkedin_default_image">
                <?php _e('Medya Yükle', 'wp-ai-seo'); ?>
            </button>
        </div>
    </div>

    <!-- Pinterest Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Pinterest Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Pinterest paylaşım ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="pinterest_verify_meta"><?php _e('Pinterest Doğrulama Meta', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="pinterest_verify_meta" 
                   id="pinterest_verify_meta" 
                   value="<?php echo esc_attr($options['pinterest']['verify_meta']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="pinterest_default_image"><?php _e('Varsayılan Pinterest Görseli', 'wp-ai-seo'); ?></label>
            <input type="url" 
                   name="pinterest_default_image" 
                   id="pinterest_default_image" 
                   value="<?php echo esc_url($options['pinterest']['default_image']); ?>" 
                   class="regular-text">
            <button type="button" class="button wp-ai-seo-media-upload" data-target="pinterest_default_image">
                <?php _e('Medya Yükle', 'wp-ai-seo'); ?>
            </button>
        </div>
    </div>

    <!-- Sosyal Medya Şablonları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Sosyal Medya Şablonları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Sosyal medya paylaşımları için şablonlar', 'wp-ai-seo'); ?>">?</span>
        </h3>

        <div class="wp-ai-seo-field">
            <label for="social_title_template"><?php _e('Başlık Şablonu', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="social_title_template" 
                   id="social_title_template" 
                   value="<?php echo esc_attr($options['social_templates']['post_title']); ?>" 
                   class="large-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="social_description_template"><?php _e('Açıklama Şablonu', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="social_description_template" 
                   id="social_description_template" 
                   value="<?php echo esc_attr($options['social_templates']['post_description']); ?>" 
                   class="large-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="social_image_template"><?php _e('Görsel Şablonu', 'wp-ai-seo'); ?></label>
            <input type="text" 
                   name="social_image_template" 
                   id="social_image_template" 
                   value="<?php echo esc_attr($options['social_templates']['post_image']); ?>" 
                   class="large-text">
        </div>

        <!-- Kullanılabilir Değişkenler -->
        <div class="wp-ai-seo-field variables-info">
            <h4><?php _e('Kullanılabilir Değişkenler', 'wp-ai-seo'); ?></h4>
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
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Medya yükleyici
    $('.wp-ai-seo-media-upload').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = $('#' + button.data('target'));
        
        var mediaUploader = wp.media({
            title: '<?php _e('Görsel Seç', 'wp-ai-seo'); ?>',
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