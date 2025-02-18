<?php
namespace WP_AI_SEO;

/**
 * Installer sınıfı
 * Eklenti kurulum ve kaldırma işlemlerini yönetir
 */
class Installer {
    /**
     * Veritabanı tabloları
     *
     * @var array
     */
    private static $tables = [
        'wp_ai_seo_redirects' => "
            CREATE TABLE IF NOT EXISTS `%prefix%wp_ai_seo_redirects` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `source_url` varchar(255) NOT NULL,
                `target_url` varchar(255) NOT NULL,
                `redirect_type` smallint(4) NOT NULL DEFAULT 301,
                `hits` bigint(20) NOT NULL DEFAULT 0,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `source_url` (`source_url`),
                KEY `status` (`status`)
            ) %charset_collate%",
            
        'wp_ai_seo_meta' => "
            CREATE TABLE IF NOT EXISTS `%prefix%wp_ai_seo_meta` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `post_id` bigint(20) unsigned NOT NULL,
                `meta_title` varchar(255) DEFAULT NULL,
                `meta_description` text DEFAULT NULL,
                `focus_keywords` text DEFAULT NULL,
                `canonical_url` varchar(255) DEFAULT NULL,
                `robots_meta` varchar(255) DEFAULT NULL,
                `schema_data` longtext DEFAULT NULL,
                `social_meta` longtext DEFAULT NULL,
                `seo_score` tinyint(3) unsigned DEFAULT 0,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `post_id` (`post_id`)
            ) %charset_collate%"
    ];

    /**
     * Varsayılan ayarlar
     *
     * @var array
     */
    private static $default_options = [
        'wp_ai_seo_version' => WP_AI_SEO_VERSION,
        'wp_ai_seo_basic' => [
            'title_separator' => '-',
            'homepage_title' => '%sitename% %separator% %sitedesc%',
            'post_title' => '%title% %separator% %sitename%',
            'page_title' => '%title% %separator% %sitename%',
            'category_title' => '%category% %separator% %sitename%',
            'tag_title' => '%tag% %separator% %sitename%'
        ],
        'wp_ai_seo_sitemap' => [
            'enable_sitemap' => 1,
            'enable_html_sitemap' => 1,
            'exclude_post_types' => [],
            'exclude_taxonomies' => []
        ],
        'wp_ai_seo_social' => [
            'facebook_app_id' => '',
            'facebook_admin_id' => '',
            'twitter_card_type' => 'summary_large_image',
            'social_image' => ''
        ],
        'wp_ai_seo_advanced' => [
            'enable_breadcrumbs' => 1,
            'enable_schema' => 1,
            'verify_meta' => []
        ]
    ];

    /**
     * Plugin aktivasyon işlemleri
     */
    public static function activate(): void {
        // WordPress veritabanı işlemleri için gerekli dosyayı dahil et
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;

        // Karakter seti
        $charset_collate = $wpdb->get_charset_collate();

        // Tabloları oluştur
        foreach (self::$tables as $table => $query) {
            $query = str_replace(
                ['%prefix%', '%charset_collate%'],
                [$wpdb->prefix, $charset_collate],
                $query
            );
            dbDelta($query);
        }

        // Varsayılan ayarları kaydet
        foreach (self::$default_options as $option => $value) {
            if (!get_option($option)) {
                update_option($option, $value);
            }
        }

        // Veritabanı versiyon kontrolü
        update_option('wp_ai_seo_db_version', WP_AI_SEO_VERSION);

        // Yeniden yönlendirme kurallarını temizle
        flush_rewrite_rules();
    }

    /**
     * Plugin deaktivasyon işlemleri
     */
    public static function deactivate(): void {
        // Yeniden yönlendirme kurallarını temizle
        flush_rewrite_rules();
    }

    /**
     * Plugin kaldırma işlemleri
     */
    public static function uninstall(): void {
        global $wpdb;

        // Tabloları sil
        foreach (array_keys(self::$tables) as $table) {
            $table_name = $wpdb->prefix . $table;
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }

        // Ayarları sil
        foreach (array_keys(self::$default_options) as $option) {
            delete_option($option);
        }

        // Veritabanı versiyon bilgisini sil
        delete_option('wp_ai_seo_db_version');
    }
}