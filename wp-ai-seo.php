<?php
/**
 * Plugin Name: WP AI-SEO
 * Plugin URI: https://htayfur.com/wp-ai-seo
 * Description: Yapay zeka destekli WordPress SEO eklentisi
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Hakan Tayfur
 * Author URI: https://htayfur.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-ai-seo
 * Domain Path: /languages
 */

// Doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit('Doğrudan erişim engellendi!');
}

// Composer autoloader
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

// Plugin sınıflarını otomatik yükle
spl_autoload_register(function($class) {
    // Plugin namespace kontrolü
    $namespace = 'WP_AI_SEO\\';
    if (strpos($class, $namespace) !== 0) {
        return;
    }

    // Namespace'i kaldır ve dosya yolunu oluştur
    $class = str_replace($namespace, '', $class);
    $class = str_replace('\\', '/', $class);
    
    $path = dirname(__FILE__) . '/includes/' . $class . '.php';
    
    // Dosyayı güvenli bir şekilde yükle
    if (file_exists($path)) {
        require_once $path;
    }
});

// Güvenlik kontrolleri
if (!function_exists('add_action')) {
    exit('WordPress yüklü değil!');
}

if (version_compare(PHP_VERSION, '7.4', '<')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
        'WP AI-SEO eklentisi PHP 7.4 veya üzeri gerektirir. ' .
        'Lütfen hosting firmanızla iletişime geçin.',
        'Plugin Aktivasyon Hatası',
        ['back_link' => true]
    );
}

// Gerekli PHP eklentilerini kontrol et
$required_extensions = ['mbstring', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                'WP AI-SEO eklentisi için %s PHP eklentisi gerekli. ' .
                'Lütfen hosting firmanızla iletişime geçin.',
                $ext
            ),
            'Plugin Aktivasyon Hatası',
            ['back_link' => true]
        );
    }
}

// WordPress versiyonunu kontrol et
global $wp_version;
if (version_compare($wp_version, '6.0', '<')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
        'WP AI-SEO eklentisi WordPress 6.0 veya üzeri gerektirir. ' .
        'Lütfen WordPress\'inizi güncelleyin.',
        'Plugin Aktivasyon Hatası',
        ['back_link' => true]
    );
}

// Güvenlik sabitleri
define('WP_AI_SEO_MIN_PHP_VERSION', '7.4');
define('WP_AI_SEO_MIN_WP_VERSION', '6.0');
define('WP_AI_SEO_PLUGIN_FILE', __FILE__);
define('WP_AI_SEO_PLUGIN_BASE', plugin_basename(__FILE__));
define('WP_AI_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_AI_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Eklentiyi başlat
function wp_ai_seo_init() {
    // Plugin sınıfını yükle
    $plugin = WP_AI_SEO\Plugin::instance();

    // Güvenlik kontrollerini etkinleştir
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        $security = new WP_AI_SEO\Security();
        
        // XSS koruması
        add_filter('the_content', [$security, 'sanitize_input']);
        add_filter('the_title', [$security, 'sanitize_input']);
        add_filter('comment_text', [$security, 'sanitize_input']);
        
        // CSRF koruması
        if (is_admin()) {
            add_action('admin_init', [$security, 'verify_nonce_token']);
        }
        
        // Brute force koruması
        add_filter('authenticate', [$security, 'check_failed_login'], 30, 3);
    }

    // Log temizleme cron işi
    if (!wp_next_scheduled('wp_ai_seo_cleanup_logs')) {
        wp_schedule_event(time(), 'daily', 'wp_ai_seo_cleanup_logs');
    }
    add_action('wp_ai_seo_cleanup_logs', [$security, 'cleanup_security_logs']);

    return $plugin;
}

// Eklentiyi başlat
add_action('plugins_loaded', 'wp_ai_seo_init');

// Aktivasyon kancası
register_activation_hook(__FILE__, function() {
    // Gerekli dizinleri oluştur
    $upload_dir = wp_upload_dir();
    $dirs = [
        WP_AI_SEO_PLUGIN_DIR . 'logs',
        $upload_dir['basedir'] . '/wp-ai-seo'
    ];

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        // .htaccess dosyası oluştur
        if (!file_exists($dir . '/.htaccess')) {
            file_put_contents($dir . '/.htaccess', 'Deny from all');
        }

        // index.php dosyası oluştur
        if (!file_exists($dir . '/index.php')) {
            file_put_contents($dir . '/index.php', '<?php // Silence is golden');
        }
    }

    // Varsayılan ayarları kaydet
    $default_settings = [
        'enable_automatic_updates' => 1,
        'uninstall_data' => 0,
        'enable_admin_bar' => 1,
        'dashboard_widget' => 1,
        'email_notifications' => 1,
        'notification_email' => get_option('admin_email'),
        'notification_frequency' => 'weekly',
        'enable_debug' => 0
    ];

    add_option('wp_ai_seo_settings', $default_settings);

    // Aktivasyon işlemini logla
    WP_AI_SEO\Security::instance()->log_security_event(
        'activation',
        'Plugin activated',
        ['version' => WP_AI_SEO_VERSION]
    );
});

// Deaktivasyon kancası
register_deactivation_hook(__FILE__, function() {
    // Zamanlanmış görevleri temizle
    wp_clear_scheduled_hook('wp_ai_seo_cleanup_logs');

    // Deaktivasyon işlemini logla
    WP_AI_SEO\Security::instance()->log_security_event(
        'deactivation',
        'Plugin deactivated',
        ['version' => WP_AI_SEO_VERSION]
    );
});

// Kaldırma kancası
register_uninstall_hook(__FILE__, function() {
    // Ayarlar ve verileri temizle
    if (get_option('wp_ai_seo_settings')['uninstall_data']) {
        global $wpdb;
        
        // Veritabanı tablolarını temizle
        $tables = [
            $wpdb->prefix . 'wp_ai_seo_logs',
            $wpdb->prefix . 'wp_ai_seo_redirects'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        // Ayarları temizle
        delete_option('wp_ai_seo_settings');
        delete_option('wp_ai_seo_security_logs');

        // Post meta verilerini temizle
        $wpdb->query(
            "DELETE FROM $wpdb->postmeta 
             WHERE meta_key LIKE '_wp_ai_seo_%'"
        );

        // Dosyaları temizle
        $upload_dir = wp_upload_dir();
        $dirs = [
            WP_AI_SEO_PLUGIN_DIR . 'logs',
            $upload_dir['basedir'] . '/wp-ai-seo'
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $file) {
                    if ($file->isDir()) {
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }

                rmdir($dir);
            }
        }
    }
});