<?php
namespace WP_AI_SEO;

class Security {
    /**
     * Security sınıfı örneği
     *
     * @var Security|null
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Security sınıfı örneğini döndür
     *
     * @return Security
     */
    public static function instance(): Security {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hook'ları başlat
     */
    private function init_hooks(): void {
        // AJAX güvenliği
        add_action('admin_init', [$this, 'verify_ajax_nonce']);
        
        // XSS koruması
        add_action('admin_init', [$this, 'prevent_xss']);
        
        // SQL injection koruması
        add_action('init', [$this, 'prevent_sql_injection']);
        
        // File upload güvenliği
        add_filter('upload_mimes', [$this, 'restrict_upload_types']);
        add_filter('wp_handle_upload_prefilter', [$this, 'validate_file_upload']);
        
        // Brute force koruması
        add_filter('authenticate', [$this, 'check_failed_login'], 30, 3);
        
        // CSRF koruması
        add_action('admin_init', [$this, 'verify_nonce_token']);
    }

    /**
     * AJAX nonce doğrulama
     */
    public function verify_ajax_nonce(): void {
        if (wp_doing_ajax()) {
            $actions = [
                'wp_ai_seo_analyze',
                'wp_ai_seo_save_meta',
                'wp_ai_seo_cleanup'
            ];

            if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], $actions)) {
                check_ajax_referer('wp-ai-seo-nonce', 'nonce');
            }
        }
    }

    /**
     * XSS koruması
     */
    public function prevent_xss(): void {
        // Header güvenliği
        header('X-XSS-Protection: 1; mode=block');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        
        // Input temizleme
        if (!empty($_POST)) {
            array_walk_recursive($_POST, [$this, 'sanitize_input']);
        }
        if (!empty($_GET)) {
            array_walk_recursive($_GET, [$this, 'sanitize_input']);
        }
    }

    /**
     * Input temizleme
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize_input(&$value) {
        if (is_string($value)) {
            $value = wp_kses_post($value);
        }
        return $value;
    }

    /**
     * SQL injection koruması
     */
    public function prevent_sql_injection(): void {
        global $wpdb;
        
        // SQL mod ayarları
        $wpdb->query("SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        
        // Prepared statements kullan
        add_filter('query', function($query) {
            if (preg_match('/(INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE)/i', $query)) {
                if (!preg_match('/^\s*(INSERT|UPDATE|DELETE)\s+INTO\s+`?' . $wpdb->prefix . '/i', $query)) {
                    wp_die(__('Güvenlik ihlali tespit edildi!', 'wp-ai-seo'));
                }
            }
            return $query;
        });
    }

    /**
     * Dosya yükleme tiplerini kısıtla
     *
     * @param array $mimes
     * @return array
     */
    public function restrict_upload_types(array $mimes): array {
        // İzin verilen dosya tipleri
        $allowed_mimes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'pdf' => 'application/pdf'
        ];

        return array_intersect_key($mimes, $allowed_mimes);
    }

    /**
     * Dosya yükleme doğrulama
     *
     * @param array $file
     * @return array
     */
    public function validate_file_upload(array $file): array {
        $filename = $file['name'];
        
        // Uzantı kontrolü
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'gif', 'png', 'pdf'];
        
        if (!in_array($ext, $allowed_exts)) {
            $file['error'] = __('Bu dosya tipi için yükleme izniniz yok.', 'wp-ai-seo');
            return $file;
        }

        // Dosya boyutu kontrolü (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $file['error'] = __('Dosya boyutu çok büyük. Maximum 5MB olabilir.', 'wp-ai-seo');
            return $file;
        }

        return $file;
    }

    /**
     * Başarısız giriş denemelerini kontrol et
     *
     * @param null|\WP_User|\WP_Error $user
     * @param string $username
     * @param string $password
     * @return null|\WP_User|\WP_Error
     */
    public function check_failed_login($user, string $username, string $password) {
        if (!empty($username)) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $failed_login_limit = 5;
            $lockout_duration = 15 * MINUTE_IN_SECONDS;
            
            // Başarısız giriş sayısı
            $failed_attempts = get_transient('failed_login_' . $ip) ?: 0;
            
            if (is_wp_error($user)) {
                $failed_attempts++;
                set_transient('failed_login_' . $ip, $failed_attempts, $lockout_duration);
                
                if ($failed_attempts >= $failed_login_limit) {
                    return new \WP_Error(
                        'too_many_attempts',
                        sprintf(
                            __('Çok fazla başarısız giriş denemesi. %d dakika sonra tekrar deneyin.', 'wp-ai-seo'),
                            ceil($lockout_duration / MINUTE_IN_SECONDS)
                        )
                    );
                }
            } else {
                delete_transient('failed_login_' . $ip);
            }
        }
        
        return $user;
    }

    /**
     * CSRF token doğrulama
     */
    public function verify_nonce_token(): void {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $nonce_actions = [
                'wp_ai_seo_settings',
                'wp_ai_seo_meta_box'
            ];

            foreach ($nonce_actions as $action) {
                if (isset($_POST[$action . '_nonce'])) {
                    check_admin_referer($action, $action . '_nonce');
                }
            }
        }
    }

    /**
     * Güvenlik log kaydı
     *
     * @param string $type
     * @param string $message
     * @param array $data
     */
    public function log_security_event(string $type, string $message, array $data = []): void {
        if (!wp_next_scheduled('wp_ai_seo_security_log_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wp_ai_seo_security_log_cleanup');
        }

        $log = [
            'time' => current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => get_current_user_id(),
            'data' => $data
        ];

        $logs = get_option('wp_ai_seo_security_logs', []);
        array_unshift($logs, $log);
        
        // Maximum 1000 log kaydı tut
        $logs = array_slice($logs, 0, 1000);
        
        update_option('wp_ai_seo_security_logs', $logs);
    }

    /**
     * Eski log kayıtlarını temizle (30 günden eski)
     */
    public function cleanup_security_logs(): void {
        $logs = get_option('wp_ai_seo_security_logs', []);
        $threshold = strtotime('-30 days');
        
        foreach ($logs as $key => $log) {
            if (strtotime($log['time']) < $threshold) {
                unset($logs[$key]);
            }
        }
        
        update_option('wp_ai_seo_security_logs', array_values($logs));
    }
}