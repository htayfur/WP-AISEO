<?php
namespace WP_AI_SEO;

class Plugin {
    /**
     * Plugin sınıfı örneği
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Admin sınıfı örneği
     *
     * @var Admin\Admin|null
     */
    private $admin = null;

    /**
     * Frontend sınıfı örneği
     *
     * @var Frontend\Frontend|null
     */
    private $frontend = null;

    /**
     * Security sınıfı örneği
     *
     * @var Security|null
     */
    private $security = null;

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->init_hooks();
    }

    /**
     * Plugin sınıfı örneğini döndür
     *
     * @return Plugin
     */
    public static function instance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sabitleri tanımla
     */
    private function define_constants(): void {
        define('WP_AI_SEO_VERSION', '1.0.0');
        define('WP_AI_SEO_FILE', __FILE__);
        define('WP_AI_SEO_PATH', plugin_dir_path(dirname(__FILE__)));
        define('WP_AI_SEO_URL', plugin_dir_url(dirname(__FILE__)));
        define('WP_AI_SEO_ASSETS', WP_AI_SEO_URL . 'assets/');
    }

    /**
     * Hook'ları başlat
     */
    private function init_hooks(): void {
        // Plugin aktivasyon/deaktivasyon
        register_activation_hook(WP_AI_SEO_FILE, [Installer::class, 'activate']);
        register_deactivation_hook(WP_AI_SEO_FILE, [Installer::class, 'deactivate']);
        register_uninstall_hook(WP_AI_SEO_FILE, [Installer::class, 'uninstall']);

        // Admin ve Frontend sınıflarını yükle
        if (is_admin()) {
            $this->admin = Admin\Admin::instance();
        } else {
            $this->frontend = Frontend\Frontend::instance();
        }

        // Security sınıfını her durumda yükle
        $this->security = Security::instance();

        // Dil dosyalarını yükle
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Eklenti yükleme kontrolü
        add_action('plugins_loaded', [$this, 'check_environment']);
    }

    /**
     * Dil dosyalarını yükle
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'wp-ai-seo',
            false,
            dirname(plugin_basename(WP_AI_SEO_FILE)) . '/languages/'
        );
    }

    /**
     * Sistem gereksinimlerini kontrol et
     */
    public function check_environment(): void {
        // PHP versiyon kontrolü
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' .
                     sprintf(
                         __('WP AI-SEO eklentisi PHP 7.4 veya üzeri gerektirir. Şu anki versiyon: %s', 'wp-ai-seo'),
                         PHP_VERSION
                     ) .
                     '</p></div>';
            });
            return;
        }

        // WordPress versiyon kontrolü
        if (version_compare($GLOBALS['wp_version'], '6.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' .
                     sprintf(
                         __('WP AI-SEO eklentisi WordPress 6.0 veya üzeri gerektirir. Şu anki versiyon: %s', 'wp-ai-seo'),
                         $GLOBALS['wp_version']
                     ) .
                     '</p></div>';
            });
            return;
        }

        // Gerekli PHP eklentileri kontrolü
        $required_extensions = ['mbstring', 'json', 'curl'];
        $missing_extensions = [];

        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }

        if (!empty($missing_extensions)) {
            add_action('admin_notices', function() use ($missing_extensions) {
                echo '<div class="notice notice-error"><p>' .
                     sprintf(
                         __('WP AI-SEO eklentisi için gerekli PHP eklentileri eksik: %s', 'wp-ai-seo'),
                         implode(', ', $missing_extensions)
                     ) .
                     '</p></div>';
            });
            return;
        }

        // Yazma izinleri kontrolü
        $writable_paths = [
            WP_AI_SEO_PATH . 'logs',
            wp_upload_dir()['basedir'] . '/wp-ai-seo'
        ];

        foreach ($writable_paths as $path) {
            if (!file_exists($path)) {
                wp_mkdir_p($path);
            }

            if (!is_writable($path)) {
                add_action('admin_notices', function() use ($path) {
                    echo '<div class="notice notice-error"><p>' .
                         sprintf(
                             __('WP AI-SEO eklentisi için %s dizinine yazma izni gerekiyor.', 'wp-ai-seo'),
                             $path
                         ) .
                         '</p></div>';
                });
                return;
            }
        }
    }

    /**
     * Admin sınıfı örneğini döndür
     *
     * @return Admin\Admin|null
     */
    public function get_admin() {
        return $this->admin;
    }

    /**
     * Frontend sınıfı örneğini döndür
     *
     * @return Frontend\Frontend|null
     */
    public function get_frontend() {
        return $this->frontend;
    }

    /**
     * Security sınıfı örneğini döndür
     *
     * @return Security|null
     */
    public function get_security() {
        return $this->security;
    }
}