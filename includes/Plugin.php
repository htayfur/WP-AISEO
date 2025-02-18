<?php
namespace WP_AI_SEO;

/**
 * Ana Plugin sınıfı
 */
class Plugin {
    /**
     * Plugin örneği
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Plugin modülleri
     *
     * @var array
     */
    private $modules = [];

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_modules();
    }

    /**
     * Plugin örneğini döndür
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
     * Temel hook'ları başlat
     */
    private function init_hooks(): void {
        // Admin menüsünü ekle
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Admin assets'lerini yükle
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Ayarlar bağlantısını ekle
        add_filter('plugin_action_links_' . plugin_basename(WP_AI_SEO_PATH . 'wp-ai-seo.php'), 
            [$this, 'add_settings_link']
        );
    }

    /**
     * Modülleri yükle
     */
    private function load_modules(): void {
        // SEO Temel modülü
        $this->modules['basic_seo'] = new Modules\BasicSeo();
        
        // Teknik SEO modülü
        $this->modules['technical_seo'] = new Modules\TechnicalSeo();
        
        // İçerik optimizasyonu modülü
        $this->modules['content_optimization'] = new Modules\ContentOptimization();
        
        // Gelişmiş SEO modülü
        $this->modules['advanced_seo'] = new Modules\AdvancedSeo();
        
        // Sosyal medya optimizasyonu modülü
        $this->modules['social_seo'] = new Modules\SocialSeo();
        
        // Performans ve güvenlik modülü
        $this->modules['performance'] = new Modules\Performance();
    }

    /**
     * Admin menüsünü ekle
     */
    public function add_admin_menu(): void {
        add_menu_page(
            __('WP AI-SEO', 'wp-ai-seo'),
            __('WP AI-SEO', 'wp-ai-seo'),
            'manage_options',
            'wp-ai-seo',
            [$this, 'render_admin_page'],
            'dashicons-chart-line',
            80
        );

        // Alt menüler
        $this->add_submenu_pages();
    }

    /**
     * Alt menüleri ekle
     */
    private function add_submenu_pages(): void {
        $submenus = [
            'basic-seo' => __('Temel SEO', 'wp-ai-seo'),
            'technical-seo' => __('Teknik SEO', 'wp-ai-seo'),
            'content-optimization' => __('İçerik Optimizasyonu', 'wp-ai-seo'),
            'advanced-seo' => __('Gelişmiş SEO', 'wp-ai-seo'),
            'social-seo' => __('Sosyal Medya', 'wp-ai-seo'),
            'performance' => __('Performans', 'wp-ai-seo'),
            'settings' => __('Ayarlar', 'wp-ai-seo'),
        ];

        foreach ($submenus as $slug => $title) {
            add_submenu_page(
                'wp-ai-seo',
                $title,
                $title,
                'manage_options',
                'wp-ai-seo-' . $slug,
                [$this, 'render_admin_page']
            );
        }
    }

    /**
     * Admin sayfasını render et
     */
    public function render_admin_page(): void {
        $current_page = $_GET['page'] ?? 'wp-ai-seo';
        require_once WP_AI_SEO_PATH . 'views/admin.php';
    }

    /**
     * Admin assets'lerini yükle
     */
    public function enqueue_admin_assets(): void {
        $screen = get_current_screen();
        if (strpos($screen->id, 'wp-ai-seo') !== false) {
            wp_enqueue_style(
                'wp-ai-seo-admin',
                WP_AI_SEO_URL . 'assets/css/admin.css',
                [],
                WP_AI_SEO_VERSION
            );

            wp_enqueue_script(
                'wp-ai-seo-admin',
                WP_AI_SEO_URL . 'assets/js/admin.js',
                ['jquery'],
                WP_AI_SEO_VERSION,
                true
            );

            wp_localize_script('wp-ai-seo-admin', 'wpAiSeoAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp-ai-seo-admin-nonce')
            ]);
        }
    }

    /**
     * Ayarlar bağlantısını ekle
     */
    public function add_settings_link($links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wp-ai-seo-settings'),
            __('Ayarlar', 'wp-ai-seo')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
}