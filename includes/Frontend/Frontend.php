<?php
namespace WP_AI_SEO\Frontend;

class Frontend {
    /**
     * Frontend sınıfı örneği
     *
     * @var Frontend|null
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Frontend sınıfı örneğini döndür
     *
     * @return Frontend
     */
    public static function instance(): Frontend {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hook'ları başlat
     */
    private function init_hooks(): void {
        // Meta etiketleri ekle
        add_action('wp_head', [$this, 'add_meta_tags']);
        
        // JSON-LD schema ekle
        add_action('wp_footer', [$this, 'add_schema']);
        
        // Title'ı filtrele
        add_filter('pre_get_document_title', [$this, 'filter_title']);
        add_filter('document_title_parts', [$this, 'filter_title_parts']);
        
        // Canonical URL ekle
        add_action('wp_head', [$this, 'add_canonical']);
        
        // Sosyal medya meta etiketleri
        add_action('wp_head', [$this, 'add_social_meta']);
        
        // Robots meta
        add_action('wp_head', [$this, 'add_robots_meta']);
        
        // RSS feed'i güncelle
        add_filter('the_excerpt_rss', [$this, 'update_rss_excerpt']);
        add_filter('the_content_feed', [$this, 'update_rss_content']);
    }

    /**
     * Meta etiketleri ekle
     */
    public function add_meta_tags(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $meta = [
            'title' => get_post_meta($post->ID, '_wp_ai_seo_meta_title', true),
            'description' => get_post_meta($post->ID, '_wp_ai_seo_meta_description', true)
        ];

        if (!empty($meta['description'])) {
            echo '<meta name="description" content="' . esc_attr($meta['description']) . '" />' . "\n";
        }
    }

    /**
     * JSON-LD schema ekle
     */
    public function add_schema(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $options = get_option('wp_ai_seo_advanced', []);

        // Site schema
        $site_schema = [
            '@context' => 'https://schema.org',
            '@type' => $options['schema_type'] ?? 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
        ];

        if (!empty($options['organization_logo'])) {
            $site_schema['logo'] = $options['organization_logo'];
        }

        // Sayfa/yazı schema
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => is_page() ? 'WebPage' : 'BlogPosting',
            'headline' => get_the_title($post),
            'description' => get_the_excerpt($post),
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ],
            'publisher' => $site_schema
        ];

        if (has_post_thumbnail($post)) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'full');
            if ($thumbnail) {
                $page_schema['image'] = $thumbnail[0];
            }
        }

        // WooCommerce ürün schema
        if (function_exists('is_product') && is_product()) {
            $product = wc_get_product($post);
            
            $product_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->get_name(),
                'description' => $product->get_short_description(),
                'sku' => $product->get_sku(),
                'brand' => [
                    '@type' => 'Brand',
                    'name' => wp_strip_all_tags(wc_get_product_category_list($product->get_id()))
                ]
            ];

            if ($product->get_price()) {
                $product_schema['offers'] = [
                    '@type' => 'Offer',
                    'price' => $product->get_price(),
                    'priceCurrency' => get_woocommerce_currency(),
                    'availability' => $product->is_in_stock() ? 'InStock' : 'OutOfStock',
                    'url' => get_permalink($post)
                ];
            }

            if ($product->get_average_rating()) {
                $product_schema['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $product->get_average_rating(),
                    'reviewCount' => $product->get_review_count()
                ];
            }

            echo '<script type="application/ld+json">' . 
                 wp_json_encode($product_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . 
                 '</script>' . "\n";
        }

        echo '<script type="application/ld+json">' . 
             wp_json_encode($site_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . 
             '</script>' . "\n";

        if (!is_front_page()) {
            echo '<script type="application/ld+json">' . 
                 wp_json_encode($page_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . 
                 '</script>' . "\n";
        }
    }

    /**
     * Title'ı filtrele
     *
     * @param string|null $title
     * @return string|null
     */
    public function filter_title(?string $title): ?string {
        if (!is_singular()) {
            return $title;
        }

        global $post;
        $meta_title = get_post_meta($post->ID, '_wp_ai_seo_meta_title', true);

        if (!empty($meta_title)) {
            return $meta_title;
        }

        return $title;
    }

    /**
     * Title parçalarını filtrele
     *
     * @param array $title_parts
     * @return array
     */
    public function filter_title_parts(array $title_parts): array {
        $options = get_option('wp_ai_seo_basic', []);
        
        if (!empty($options['title_separator'])) {
            $title_parts['separator'] = $options['title_separator'];
        }

        return $title_parts;
    }

    /**
     * Canonical URL ekle
     */
    public function add_canonical(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $canonical = get_post_meta($post->ID, '_wp_ai_seo_canonical_url', true);

        if (empty($canonical)) {
            $canonical = get_permalink($post);
        }

        echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
    }

    /**
     * Sosyal medya meta etiketleri ekle
     */
    public function add_social_meta(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $options = get_option('wp_ai_seo_social', []);
        
        // Facebook meta
        if (!empty($options['facebook']['app_id'])) {
            echo '<meta property="fb:app_id" content="' . esc_attr($options['facebook']['app_id']) . '" />' . "\n";
        }

        $og_title = get_post_meta($post->ID, '_wp_ai_seo_og_title', true);
        $og_description = get_post_meta($post->ID, '_wp_ai_seo_og_description', true);
        $og_image = get_post_meta($post->ID, '_wp_ai_seo_og_image', true);

        if (empty($og_title)) {
            $og_title = get_the_title($post);
        }

        if (empty($og_description)) {
            $og_description = get_the_excerpt($post);
        }

        if (empty($og_image) && has_post_thumbnail($post)) {
            $og_image = get_the_post_thumbnail_url($post, 'large');
        }

        echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
        
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '" />' . "\n";
        }

        echo '<meta property="og:url" content="' . esc_url(get_permalink($post)) . '" />' . "\n";
        echo '<meta property="og:type" content="' . (is_front_page() ? 'website' : 'article') . '" />' . "\n";
        
        // Twitter meta
        $twitter_title = get_post_meta($post->ID, '_wp_ai_seo_twitter_title', true) ?: $og_title;
        $twitter_description = get_post_meta($post->ID, '_wp_ai_seo_twitter_description', true) ?: $og_description;
        $twitter_image = get_post_meta($post->ID, '_wp_ai_seo_twitter_image', true) ?: $og_image;

        echo '<meta name="twitter:card" content="' . 
             esc_attr($options['twitter']['card_type'] ?? 'summary_large_image') . '" />' . "\n";
        
        if (!empty($options['twitter']['username'])) {
            echo '<meta name="twitter:site" content="@' . 
                 esc_attr($options['twitter']['username']) . '" />' . "\n";
        }

        echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '" />' . "\n";
        
        if ($twitter_image) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_image) . '" />' . "\n";
        }
    }

    /**
     * Robots meta ekle
     */
    public function add_robots_meta(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $robots = get_post_meta($post->ID, '_wp_ai_seo_robots_meta', true);

        if (!empty($robots)) {
            echo '<meta name="robots" content="' . esc_attr($robots) . '" />' . "\n";
        }
    }

    /**
     * RSS feed özetini güncelle
     *
     * @param string $excerpt
     * @return string
     */
    public function update_rss_excerpt(string $excerpt): string {
        global $post;
        $meta_description = get_post_meta($post->ID, '_wp_ai_seo_meta_description', true);

        if (!empty($meta_description)) {
            return $meta_description;
        }

        return $excerpt;
    }

    /**
     * RSS feed içeriğini güncelle
     *
     * @param string $content
     * @return string
     */
    public function update_rss_content(string $content): string {
        global $post;
        
        // Copyright notunu ekle
        $content .= sprintf(
            "\n\n<p>" . __('Orijinal yazı: %s', 'wp-ai-seo') . "</p>",
            '<a href="' . get_permalink($post) . '">' . get_the_title($post) . '</a>'
        );

        return $content;
    }
}