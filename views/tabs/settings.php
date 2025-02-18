<?php
if (!defined('ABSPATH')) {
    exit;
}

// Ayarları al
$options = get_option('wp_ai_seo_settings', []);
$defaults = [
    'enable_automatic_updates' => 1,
    'uninstall_data' => 0,
    'enable_admin_bar' => 1,
    'dashboard_widget' => 1,
    'email_notifications' => 1,
    'notification_email' => get_option('admin_email'),
    'notification_frequency' => 'weekly',
    'excluded_post_types' => [],
    'excluded_taxonomies' => [],
    'enable_debug' => 0,
    'custom_capabilities' => 0,
    'roles' => [
        'administrator' => [
            'manage_seo' => 1,
            'edit_seo' => 1,
            'view_seo_stats' => 1
        ],
        'editor' => [
            'manage_seo' => 0,
            'edit_seo' => 1,
            'view_seo_stats' => 1
        ],
        'author' => [
            'manage_seo' => 0,
            'edit_seo' => 1,
            'view_seo_stats' => 1
        ]
    ]
];

$options = wp_parse_args($options, $defaults);

// Form gönderildi mi kontrol et
if (isset($_POST['wp_ai_seo_settings_nonce']) && 
    wp_verify_nonce($_POST['wp_ai_seo_settings_nonce'], 'wp_ai_seo_settings')) {
    
    // Ayarları güncelle
    $new_options = [
        'enable_automatic_updates' => isset($_POST['enable_automatic_updates']) ? 1 : 0,
        'uninstall_data' => isset($_POST['uninstall_data']) ? 1 : 0,
        'enable_admin_bar' => isset($_POST['enable_admin_bar']) ? 1 : 0,
        'dashboard_widget' => isset($_POST['dashboard_widget']) ? 1 : 0,
        'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
        'notification_email' => sanitize_email($_POST['notification_email']),
        'notification_frequency' => sanitize_text_field($_POST['notification_frequency']),
        'excluded_post_types' => isset($_POST['excluded_post_types']) ? 
            array_map('sanitize_text_field', $_POST['excluded_post_types']) : [],
        'excluded_taxonomies' => isset($_POST['excluded_taxonomies']) ? 
            array_map('sanitize_text_field', $_POST['excluded_taxonomies']) : [],
        'enable_debug' => isset($_POST['enable_debug']) ? 1 : 0,
        'custom_capabilities' => isset($_POST['custom_capabilities']) ? 1 : 0,
        'roles' => []
    ];

    // Rol ayarlarını güncelle
    if (isset($_POST['roles'])) {
        foreach ($_POST['roles'] as $role => $capabilities) {
            $new_options['roles'][$role] = [
                'manage_seo' => isset($capabilities['manage_seo']) ? 1 : 0,
                'edit_seo' => isset($capabilities['edit_seo']) ? 1 : 0,
                'view_seo_stats' => isset($capabilities['view_seo_stats']) ? 1 : 0
            ];
        }
    }
    
    update_option('wp_ai_seo_settings', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>' . 
         esc_html__('Ayarlar başarıyla kaydedildi.', 'wp-ai-seo') . 
         '</p></div>';
}

// Post tiplerini al
$post_types = get_post_types(['public' => true], 'objects');
unset($post_types['attachment']);

// Taxonomileri al
$taxonomies = get_taxonomies(['public' => true], 'objects');

// Kullanıcı rollerini al
$roles = wp_roles()->roles;
?>

<form method="post" action="" class="wp-ai-seo-form">
    <?php wp_nonce_field('wp_ai_seo_settings', 'wp_ai_seo_settings_nonce'); ?>
    
    <!-- Genel Ayarlar -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Genel Ayarlar', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Genel eklenti ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_automatic_updates" 
                       value="1" 
                       <?php checked($options['enable_automatic_updates'], 1); ?>>
                <?php _e('Otomatik Güncellemeleri Etkinleştir', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="uninstall_data" 
                       value="1" 
                       <?php checked($options['uninstall_data'], 1); ?>>
                <?php _e('Kaldırıldığında Tüm Verileri Sil', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Eklenti kaldırıldığında tüm ayarları ve verileri siler.', 'wp-ai-seo'); ?>
            </p>
        </div>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_admin_bar" 
                       value="1" 
                       <?php checked($options['enable_admin_bar'], 1); ?>>
                <?php _e('Admin Çubuğunda Göster', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="dashboard_widget" 
                       value="1" 
                       <?php checked($options['dashboard_widget'], 1); ?>>
                <?php _e('Dashboard Widget\'ı Göster', 'wp-ai-seo'); ?>
            </label>
        </div>
    </div>

    <!-- Bildirim Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Bildirim Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('E-posta bildirim ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="email_notifications" 
                       value="1" 
                       <?php checked($options['email_notifications'], 1); ?>>
                <?php _e('E-posta Bildirimleri', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="wp-ai-seo-field">
            <label for="notification_email"><?php _e('Bildirim E-postası', 'wp-ai-seo'); ?></label>
            <input type="email" 
                   name="notification_email" 
                   id="notification_email" 
                   value="<?php echo esc_attr($options['notification_email']); ?>" 
                   class="regular-text">
        </div>

        <div class="wp-ai-seo-field">
            <label for="notification_frequency"><?php _e('Bildirim Sıklığı', 'wp-ai-seo'); ?></label>
            <select name="notification_frequency" id="notification_frequency">
                <option value="daily" <?php selected($options['notification_frequency'], 'daily'); ?>>
                    <?php _e('Günlük', 'wp-ai-seo'); ?>
                </option>
                <option value="weekly" <?php selected($options['notification_frequency'], 'weekly'); ?>>
                    <?php _e('Haftalık', 'wp-ai-seo'); ?>
                </option>
                <option value="monthly" <?php selected($options['notification_frequency'], 'monthly'); ?>>
                    <?php _e('Aylık', 'wp-ai-seo'); ?>
                </option>
            </select>
        </div>
    </div>

    <!-- İçerik Ayarları -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('İçerik Ayarları', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('SEO analizi yapılmayacak içerik türleri', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label><?php _e('Hariç Tutulan Yazı Tipleri', 'wp-ai-seo'); ?></label>
            <?php foreach ($post_types as $post_type) : ?>
                <label class="wp-ai-seo-checkbox">
                    <input type="checkbox" 
                           name="excluded_post_types[]" 
                           value="<?php echo esc_attr($post_type->name); ?>"
                           <?php checked(in_array($post_type->name, $options['excluded_post_types'])); ?>>
                    <?php echo esc_html($post_type->label); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="wp-ai-seo-field">
            <label><?php _e('Hariç Tutulan Taksonomiler', 'wp-ai-seo'); ?></label>
            <?php foreach ($taxonomies as $taxonomy) : ?>
                <label class="wp-ai-seo-checkbox">
                    <input type="checkbox" 
                           name="excluded_taxonomies[]" 
                           value="<?php echo esc_attr($taxonomy->name); ?>"
                           <?php checked(in_array($taxonomy->name, $options['excluded_taxonomies'])); ?>>
                    <?php echo esc_html($taxonomy->label); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kullanıcı Rolleri -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Kullanıcı Rolleri', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Her rol için SEO yetkilerini ayarlayın', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="custom_capabilities" 
                       value="1" 
                       <?php checked($options['custom_capabilities'], 1); ?>>
                <?php _e('Özel Yetkiler Kullan', 'wp-ai-seo'); ?>
            </label>
        </div>

        <div class="wp-ai-seo-roles-grid" style="display: <?php echo $options['custom_capabilities'] ? 'block' : 'none'; ?>;">
            <?php foreach ($roles as $role_id => $role) : ?>
                <div class="wp-ai-seo-role-card">
                    <h4><?php echo translate_user_role($role['name']); ?></h4>
                    
                    <label class="wp-ai-seo-checkbox">
                        <input type="checkbox" 
                               name="roles[<?php echo esc_attr($role_id); ?>][manage_seo]" 
                               value="1" 
                               <?php checked($options['roles'][$role_id]['manage_seo'] ?? 0, 1); ?>>
                        <?php _e('SEO Yönetimi', 'wp-ai-seo'); ?>
                    </label>
                    
                    <label class="wp-ai-seo-checkbox">
                        <input type="checkbox" 
                               name="roles[<?php echo esc_attr($role_id); ?>][edit_seo]" 
                               value="1" 
                               <?php checked($options['roles'][$role_id]['edit_seo'] ?? 0, 1); ?>>
                        <?php _e('SEO Düzenleme', 'wp-ai-seo'); ?>
                    </label>
                    
                    <label class="wp-ai-seo-checkbox">
                        <input type="checkbox" 
                               name="roles[<?php echo esc_attr($role_id); ?>][view_seo_stats]" 
                               value="1" 
                               <?php checked($options['roles'][$role_id]['view_seo_stats'] ?? 0, 1); ?>>
                        <?php _e('SEO İstatistikleri', 'wp-ai-seo'); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Gelişmiş Ayarlar -->
    <div class="wp-ai-seo-card">
        <h3>
            <?php _e('Gelişmiş Ayarlar', 'wp-ai-seo'); ?>
            <span class="wp-ai-seo-info" title="<?php esc_attr_e('Gelişmiş eklenti ayarları', 'wp-ai-seo'); ?>">?</span>
        </h3>
        
        <div class="wp-ai-seo-field">
            <label>
                <input type="checkbox" 
                       name="enable_debug" 
                       value="1" 
                       <?php checked($options['enable_debug'], 1); ?>>
                <?php _e('Debug Modu', 'wp-ai-seo'); ?>
            </label>
            <p class="description">
                <?php _e('Hata ayıklama modunu etkinleştirir. Sadece geliştirme amaçlı kullanın.', 'wp-ai-seo'); ?>
            </p>
        </div>
    </div>

    <?php submit_button(__('Ayarları Kaydet', 'wp-ai-seo')); ?>
</form>

<script>
jQuery(document).ready(function($) {
    // Özel yetkileri göster/gizle
    $('input[name="custom_capabilities"]').on('change', function() {
        $('.wp-ai-seo-roles-grid').toggle(this.checked);
    });
});
</script>