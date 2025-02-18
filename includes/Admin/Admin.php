<?php
namespace WP_AI_SEO\Admin;

class Admin {
    /**
     * Admin sınıfı örneği
     *
     * @var Admin|null
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Admin sınıfı örneğini döndür
     *
     * @return Admin
     */
    public static function instance(): Admin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hook'ları başlat
     */
    private function init_hooks(): void {
        // Admin menüsünü ekle
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Admin assets'lerini yükle
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Admin bar'a menü ekle
        add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 999);
        
        // Dashboard widget'ı ekle
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        
        // Post kolonlarını ekle
        add_filter('manage_posts_columns', [$this, 'add_seo_columns']);
        add_filter('manage_pages_columns', [$this, 'add_seo_columns']);
        
        // Kolon içeriklerini doldur
        add_action('manage_posts_custom_column', [$this, 'render_seo_columns'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'render_seo_columns'], 10, 2);
        
        // Quick edit desteği
        add_action('quick_edit_custom_box', [$this, 'add_quick_edit_fields'], 10, 2);
        add_action('save_post', [$this, 'save_quick_edit_fields']);
        
        // AJAX işleyicileri
        add_action('wp_ajax_wp_ai_seo_analyze', [$this, 'ajax_analyze_content']);
        add_action('wp_ajax_wp_ai_seo_save_meta', [$this, 'ajax_save_meta']);
        add_action('wp_ajax_wp_ai_seo_cleanup', [$this, 'ajax_cleanup_database']);
    }

    /**
     * Admin menüsünü ekle
     */
    public function add_admin_menu(): void {
        $options = get_option('wp_ai_seo_settings', []);
        $capability = $options['custom_capabilities'] ? 'manage_seo' : 'manage_options';

        add_menu_page(
            __('WP AI-SEO', 'wp-ai-seo'),
            __('WP AI-SEO', 'wp-ai-seo'),
            $capability,
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
        $capability = get_option('wp_ai_seo_settings')['custom_capabilities'] ? 'manage_seo' : 'manage_options';

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
                $capability,
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
    public function enqueue_assets(): void {
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
     * Admin bar'a menü ekle
     *
     * @param \WP_Admin_Bar $admin_bar
     */
    public function add_admin_bar_menu($admin_bar): void {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $admin_bar->add_menu([
            'id'    => 'wp-ai-seo',
            'title' => __('SEO', 'wp-ai-seo'),
            'href'  => admin_url('admin.php?page=wp-ai-seo'),
            'meta'  => [
                'title' => __('WP AI-SEO', 'wp-ai-seo'),
            ],
        ]);

        // Alt menüler
        $admin_bar->add_menu([
            'id'     => 'wp-ai-seo-analyze',
            'parent' => 'wp-ai-seo',
            'title'  => __('İçerik Analizi', 'wp-ai-seo'),
            'href'   => '#',
            'meta'   => [
                'onclick' => 'return WPAiSeo.analyzeContent();',
            ],
        ]);
    }

    /**
     * Dashboard widget'ı ekle
     */
    public function add_dashboard_widget(): void {
        wp_add_dashboard_widget(
            'wp_ai_seo_dashboard',
            __('SEO Durumu', 'wp-ai-seo'),
            [$this, 'render_dashboard_widget']
        );
    }

    /**
     * Dashboard widget'ını render et
     */
    public function render_dashboard_widget(): void {
        require_once WP_AI_SEO_PATH . 'views/dashboard-widget.php';
    }

    /**
     * SEO kolonlarını ekle
     *
     * @param array $columns
     * @return array
     */
    public function add_seo_columns(array $columns): array {
        $columns['wp_ai_seo_score'] = __('SEO Skoru', 'wp-ai-seo');
        $columns['wp_ai_seo_focus_keywords'] = __('Anahtar Kelimeler', 'wp-ai-seo');
        return $columns;
    }

    /**
     * SEO kolonlarını render et
     *
     * @param string $column
     * @param int $post_id
     */
    public function render_seo_columns(string $column, int $post_id): void {
        switch ($column) {
            case 'wp_ai_seo_score':
                $score = get_post_meta($post_id, '_wp_ai_seo_score', true);
                $score = $score ? intval($score) : 0;
                
                $class = $score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'bad');
                echo sprintf(
                    '<div class="wp-ai-seo-score %s">%d%%</div>',
                    esc_attr($class),
                    $score
                );
                break;
                
            case 'wp_ai_seo_focus_keywords':
                $keywords = get_post_meta($post_id, '_wp_ai_seo_focus_keywords', true);
                echo $keywords ? esc_html($keywords) : '—';
                break;
        }
    }

    /**
     * Quick edit alanlarını ekle
     *
     * @param string $column_name
     * @param string $post_type
     */
    public function add_quick_edit_fields(string $column_name, string $post_type): void {
        if ($column_name !== 'wp_ai_seo_focus_keywords') {
            return;
        }

        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Anahtar Kelimeler', 'wp-ai-seo'); ?></span>
                    <span class="input-text-wrap">
                        <input type="text" 
                               name="wp_ai_seo_focus_keywords" 
                               class="wp-ai-seo-focus-keywords" 
                               value="">
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    /**
     * Quick edit alanlarını kaydet
     *
     * @param int $post_id
     */
    public function save_quick_edit_fields(int $post_id): void {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['wp_ai_seo_focus_keywords'])) {
            update_post_meta(
                $post_id,
                '_wp_ai_seo_focus_keywords',
                sanitize_text_field($_POST['wp_ai_seo_focus_keywords'])
            );
        }
    }

    /**
     * İçerik analizi AJAX işleyicisi
     */
    public function ajax_analyze_content(): void {
        check_ajax_referer('wp-ai-seo-admin-nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Bu işlem için yetkiniz yok.', 'wp-ai-seo'));
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(__('Geçersiz yazı ID\'si.', 'wp-ai-seo'));
        }

        // SEO analizi yap
        $analyzer = new ContentAnalyzer($post_id);
        $result = $analyzer->analyze();

        wp_send_json_success($result);
    }

    /**
     * Meta verilerini kaydet AJAX işleyicisi
     */
    public function ajax_save_meta(): void {
        check_ajax_referer('wp-ai-seo-admin-nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Bu işlem için yetkiniz yok.', 'wp-ai-seo'));
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(__('Geçersiz yazı ID\'si.', 'wp-ai-seo'));
        }

        // Meta verilerini kaydet
        $meta_fields = [
            'meta_title',
            'meta_description',
            'focus_keywords',
            'canonical_url',
            'robots_meta'
        ];

        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    '_wp_ai_seo_' . $field,
                    sanitize_text_field($_POST[$field])
                );
            }
        }

        wp_send_json_success(__('Meta verileri başarıyla kaydedildi.', 'wp-ai-seo'));
    }

    /**
     * Veritabanı temizliği AJAX işleyicisi
     */
    public function ajax_cleanup_database(): void {
        check_ajax_referer('wp-ai-seo-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Bu işlem için yönetici yetkisi gerekiyor.', 'wp-ai-seo'));
        }

        // Temizlik işlemlerini başlat
        $cleaner = new DatabaseCleaner();
        $result = $cleaner->cleanup($_POST['items'] ?? []);

        wp_send_json_success($result);
    }
}