<?php
/**
 * Plugin Name: WP AI-SEO
 * Plugin URI: https://htayfur.com/wp-ai-seo
 * Description: Yapay zeka destekli WordPress SEO eklentisi
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Hakan Tayfur & AI
 * Author URI: https://htayfur.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-ai-seo
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('WP_AI_SEO_VERSION', '1.0.0');

// Plugin path
define('WP_AI_SEO_PATH', plugin_dir_path(__FILE__));

// Plugin URL
define('WP_AI_SEO_URL', plugin_dir_url(__FILE__));

// Autoloader için spl_autoload_register
spl_autoload_register(function ($class) {
    // Plugin sınıf prefix'i
    $prefix = 'WP_AI_SEO\\';
    $base_dir = WP_AI_SEO_PATH . 'includes/';

    // Prefix kontrolü
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Sınıf dosya yolu
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Dosya varsa yükle
    if (file_exists($file)) {
        require $file;
    }
});

// Plugin aktivasyon hook'u
register_activation_hook(__FILE__, function() {
    // Gerekli tabloları oluştur
    require_once WP_AI_SEO_PATH . 'includes/Installer.php';
    WP_AI_SEO\Installer::activate();
});

// Plugin deaktivasyon hook'u
register_deactivation_hook(__FILE__, function() {
    // Temizlik işlemleri
    require_once WP_AI_SEO_PATH . 'includes/Installer.php';
    WP_AI_SEO\Installer::deactivate();
});

// Plugin başlatma
add_action('plugins_loaded', function() {
    // Ana sınıfı yükle ve başlat
    require_once WP_AI_SEO_PATH . 'includes/Plugin.php';
    WP_AI_SEO\Plugin::instance();
});