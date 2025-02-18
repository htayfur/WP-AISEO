<?php
namespace WP_AI_SEO\Admin;

class DatabaseCleaner {
    /**
     * Temizlik işlemini gerçekleştir
     *
     * @param array $items Temizlenecek öğeler
     * @return array Temizlik sonuçları
     */
    public function cleanup(array $items): array {
        global $wpdb;
        $results = [];
        
        // Yazı revizyonları
        if (in_array('revisions', $items)) {
            $count = $this->delete_revisions();
            $results['revisions'] = sprintf(
                __('%d revizyon silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Otomatik taslaklar
        if (in_array('auto_drafts', $items)) {
            $count = $this->delete_auto_drafts();
            $results['auto_drafts'] = sprintf(
                __('%d otomatik taslak silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Çöp kutusundaki yazılar
        if (in_array('trash_posts', $items)) {
            $count = $this->delete_trash_posts();
            $results['trash_posts'] = sprintf(
                __('%d çöp yazı silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Spam yorumlar
        if (in_array('spam_comments', $items)) {
            $count = $this->delete_spam_comments();
            $results['spam_comments'] = sprintf(
                __('%d spam yorum silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Çöp kutusundaki yorumlar
        if (in_array('trash_comments', $items)) {
            $count = $this->delete_trash_comments();
            $results['trash_comments'] = sprintf(
                __('%d çöp yorum silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Süresi dolmuş geçici veriler
        if (in_array('expired_transients', $items)) {
            $count = $this->delete_expired_transients();
            $results['expired_transients'] = sprintf(
                __('%d süresi dolmuş geçici veri silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Kullanılmayan etiketler
        if (in_array('unused_tags', $items)) {
            $count = $this->delete_unused_terms();
            $results['unused_tags'] = sprintf(
                __('%d kullanılmayan etiket silindi.', 'wp-ai-seo'),
                $count
            );
        }
        
        // Kullanılmayan meta veriler
        if (in_array('unused_meta', $items)) {
            $count = $this->delete_unused_meta();
            $results['unused_meta'] = sprintf(
                __('%d kullanılmayan meta veri silindi.', 'wp-ai-seo'),
                $count
            );
        }

        return $results;
    }

    /**
     * Revizyonları sil
     *
     * @return int Silinen revizyon sayısı
     */
    private function delete_revisions(): int {
        global $wpdb;
        
        $query = "DELETE FROM $wpdb->posts WHERE post_type = 'revision'";
        return $wpdb->query($query);
    }

    /**
     * Otomatik taslakları sil
     *
     * @return int Silinen taslak sayısı
     */
    private function delete_auto_drafts(): int {
        global $wpdb;
        
        $query = "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'";
        return $wpdb->query($query);
    }

    /**
     * Çöp yazıları sil
     *
     * @return int Silinen yazı sayısı
     */
    private function delete_trash_posts(): int {
        global $wpdb;
        
        $query = "DELETE FROM $wpdb->posts WHERE post_status = 'trash'";
        return $wpdb->query($query);
    }

    /**
     * Spam yorumları sil
     *
     * @return int Silinen yorum sayısı
     */
    private function delete_spam_comments(): int {
        global $wpdb;
        
        $query = "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'";
        return $wpdb->query($query);
    }

    /**
     * Çöp yorumları sil
     *
     * @return int Silinen yorum sayısı
     */
    private function delete_trash_comments(): int {
        global $wpdb;
        
        $query = "DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'";
        return $wpdb->query($query);
    }

    /**
     * Süresi dolmuş geçici verileri sil
     *
     * @return int Silinen geçici veri sayısı
     */
    private function delete_expired_transients(): int {
        global $wpdb;
        
        $time = time();
        $count = 0;

        // Süresi dolmuş geçici verileri bul
        $expired = $wpdb->get_col(
            "SELECT option_name 
             FROM $wpdb->options 
             WHERE option_name LIKE '_transient_timeout_%' 
             AND option_value < $time"
        );

        if ($expired) {
            foreach ($expired as $transient) {
                $name = str_replace('_transient_timeout_', '', $transient);
                
                delete_transient($name);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Kullanılmayan terimleri sil
     *
     * @return int Silinen terim sayısı
     */
    private function delete_unused_terms(): int {
        global $wpdb;
        
        // Kullanılmayan terimleri bul
        $query = "DELETE t, tt 
                 FROM $wpdb->terms AS t 
                 INNER JOIN $wpdb->term_taxonomy AS tt 
                 ON t.term_id = tt.term_id 
                 WHERE tt.taxonomy = 'post_tag' 
                 AND tt.count = 0";
                 
        return $wpdb->query($query);
    }

    /**
     * Kullanılmayan meta verileri sil
     *
     * @return int Silinen meta veri sayısı
     */
    private function delete_unused_meta(): int {
        global $wpdb;
        
        // Kullanılmayan meta verileri bul
        $query = "DELETE pm 
                 FROM $wpdb->postmeta pm 
                 LEFT JOIN $wpdb->posts wp 
                 ON wp.ID = pm.post_id 
                 WHERE wp.ID IS NULL";
                 
        return $wpdb->query($query);
    }
}