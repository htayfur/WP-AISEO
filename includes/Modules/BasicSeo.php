<?php
namespace WP_AI_SEO\Modules;

class BasicSeo {
    /**
     * Modül başlatma
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook'ları başlat
     */
    private function init_hooks(): void {
        // Meta etiketlerini ekle
        add_action('wp_head', [$this, 'add_meta_tags']);
        
        // Yönetici paneline meta kutusu ekle
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        
        // Meta verilerini kaydet
        add_action('save_post', [$this, 'save_meta_box']);
        
        // Başlık filtreleme
        add_filter('document_title_parts', [$this, 'filter_title']);
        
        // SEO kolonu ekle
        add_filter('manage_posts_columns', [$this, 'add_seo_column']);
        add_filter('manage_pages_columns', [$this, 'add_seo_column']);
        
        // SEO kolon içeriğini doldur
        add_action('manage_posts_custom_column', [$this, 'render_seo_column'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'render_seo_column'], 10, 2);
    }

    /**
     * Meta etiketleri ekle
     */
    public function add_meta_tags(): void {
        global $post;

        if (!is_singular()) {
            return;
        }

        $meta = $this->get_post_meta($post->ID);
        
        if (!empty($meta['meta_title'])) {
            echo '<meta property="og:title" content="' . esc_attr($meta['meta_title']) . '" />' . "\n";
        }
        
        if (!empty($meta['meta_description'])) {
            echo '<meta name="description" content="' . esc_attr($meta['meta_description']) . '" />' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($meta['meta_description']) . '" />' . "\n";
        }
        
        if (!empty($meta['canonical_url'])) {
            echo '<link rel="canonical" href="' . esc_url($meta['canonical_url']) . '" />' . "\n";
        }
        
        if (!empty($meta['robots_meta'])) {
            echo '<meta name="robots" content="' . esc_attr($meta['robots_meta']) . '" />' . "\n";
        }
    }

    /**
     * Meta kutusu ekle
     */
    public function add_meta_box(): void {
        $screens = ['post', 'page'];
        
        foreach ($screens as $screen) {
            add_meta_box(
                'wp_ai_seo_meta_box',
                __('SEO Ayarları', 'wp-ai-seo'),
                [$this, 'render_meta_box'],
                $screen,
                'normal',
                'high'
            );
        }
    }

    /**
     * Meta kutusunu render et
     *
     * @param \WP_Post $post
     */
    public function render_meta_box($post): void {
        $meta = $this->get_post_meta($post->ID);
        wp_nonce_field('wp_ai_seo_meta_box', 'wp_ai_seo_meta_box_nonce');
        
        // Meta kutusu şablonunu yükle
        require WP_AI_SEO_PATH . 'views/meta-box.php';
    }

    /**
     * Meta kutusunu kaydet
     *
     * @param int $post_id
     */
    public function save_meta_box(int $post_id): void {
        // Nonce kontrolü
        if (!isset($_POST['wp_ai_seo_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['wp_ai_seo_meta_box_nonce'], 'wp_ai_seo_meta_box')) {
            return;
        }

        // Otomatik kayıt kontrolü
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Yetki kontrolü
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Meta verileri kaydet
        $fields = [
            'meta_title',
            'meta_description',
            'focus_keywords',
            'canonical_url',
            'robots_meta'
        ];

        $meta = [];
        foreach ($fields as $field) {
            if (isset($_POST['wp_ai_seo_' . $field])) {
                $meta[$field] = sanitize_text_field($_POST['wp_ai_seo_' . $field]);
            }
        }

        $this->update_post_meta($post_id, $meta);
    }

    /**
     * Post meta verilerini getir
     *
     * @param int $post_id
     * @return array
     */
    private function get_post_meta(int $post_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wp_ai_seo_meta';
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d", $post_id),
            ARRAY_A
        );

        return $result ?: [];
    }

    /**
     * Post meta verilerini güncelle
     *
     * @param int $post_id
     * @param array $meta
     */
    private function update_post_meta(int $post_id, array $meta): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wp_ai_seo_meta';
        $meta['updated_at'] = current_time('mysql');

        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table} WHERE post_id = %d", $post_id)
        );

        if ($exists) {
            $wpdb->update(
                $table,
                $meta,
                ['post_id' => $post_id]
            );
        } else {
            $meta['post_id'] = $post_id;
            $wpdb->insert($table, $meta);
        }
    }

    /**
     * Başlığı filtrele
     *
     * @param array $title
     * @return array
     */
    public function filter_title(array $title): array {
        if (is_singular()) {
            global $post;
            $meta = $this->get_post_meta($post->ID);
            
            if (!empty($meta['meta_title'])) {
                $title['title'] = $meta['meta_title'];
            }
        }
        
        return $title;
    }

    /**
     * SEO kolonu ekle
     *
     * @param array $columns
     * @return array
     */
    public function add_seo_column(array $columns): array {
        $columns['wp_ai_seo_score'] = __('SEO Skoru', 'wp-ai-seo');
        return $columns;
    }

    /**
     * SEO kolon içeriğini render et
     *
     * @param string $column
     * @param int $post_id
     */
    public function render_seo_column(string $column, int $post_id): void {
        if ($column === 'wp_ai_seo_score') {
            $meta = $this->get_post_meta($post_id);
            $score = $meta['seo_score'] ?? 0;
            
            $color = $score >= 80 ? 'green' : ($score >= 50 ? 'orange' : 'red');
            echo sprintf(
                '<span style="color: %s;">%d%%</span>',
                $color,
                $score
            );
        }
    }
}