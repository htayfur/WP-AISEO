<?php
namespace WP_AI_SEO\Admin;

class ContentAnalyzer {
    /**
     * Yazı ID'si
     *
     * @var int
     */
    private $post_id;

    /**
     * Constructor
     *
     * @param int $post_id
     */
    public function __construct(int $post_id) {
        $this->post_id = $post_id;
    }

    /**
     * İçeriği analiz et
     *
     * @return array Analiz sonuçları
     */
    public function analyze(): array {
        $post = get_post($this->post_id);
        if (!$post) {
            return [];
        }

        $content = $post->post_content;
        $title = $post->post_title;
        $excerpt = $post->post_excerpt;
        
        // Meta verileri al
        $meta = [
            'title' => get_post_meta($this->post_id, '_wp_ai_seo_meta_title', true),
            'description' => get_post_meta($this->post_id, '_wp_ai_seo_meta_description', true),
            'focus_keywords' => get_post_meta($this->post_id, '_wp_ai_seo_focus_keywords', true),
            'canonical_url' => get_post_meta($this->post_id, '_wp_ai_seo_canonical_url', true),
            'robots_meta' => get_post_meta($this->post_id, '_wp_ai_seo_robots_meta', true)
        ];

        // Kontrol listesi
        $checks = [];
        $score = 0;

        // İçerik uzunluğu kontrolü
        $word_count = str_word_count(strip_tags($content));
        if ($word_count >= 300) {
            $checks[] = [
                'status' => 'good',
                'icon' => 'yes',
                'message' => sprintf(
                    __('İçerik uzunluğu yeterli: %d kelime.', 'wp-ai-seo'),
                    $word_count
                )
            ];
            $score += 10;
        } else {
            $checks[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => sprintf(
                    __('İçerik çok kısa: %d kelime. En az 300 kelime olmalı.', 'wp-ai-seo'),
                    $word_count
                )
            ];
        }

        // Meta başlık kontrolü
        if (!empty($meta['title'])) {
            $title_length = mb_strlen($meta['title']);
            if ($title_length >= 50 && $title_length <= 60) {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => __('Meta başlık uzunluğu ideal.', 'wp-ai-seo')
                ];
                $score += 10;
            } else {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => __('Meta başlık uzunluğu ideal değil (50-60 karakter olmalı).', 'wp-ai-seo')
                ];
                $score += 5;
            }
        } else {
            $checks[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => __('Meta başlık girilmemiş.', 'wp-ai-seo')
            ];
        }

        // Meta açıklama kontrolü
        if (!empty($meta['description'])) {
            $desc_length = mb_strlen($meta['description']);
            if ($desc_length >= 120 && $desc_length <= 160) {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => __('Meta açıklama uzunluğu ideal.', 'wp-ai-seo')
                ];
                $score += 10;
            } else {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => __('Meta açıklama uzunluğu ideal değil (120-160 karakter olmalı).', 'wp-ai-seo')
                ];
                $score += 5;
            }
        } else {
            $checks[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => __('Meta açıklama girilmemiş.', 'wp-ai-seo')
            ];
        }

        // Anahtar kelime kontrolü
        if (!empty($meta['focus_keywords'])) {
            $keywords = array_map('trim', explode(',', $meta['focus_keywords']));
            $keyword_counts = [];
            
            foreach ($keywords as $keyword) {
                $count = substr_count(strtolower($content), strtolower($keyword));
                $density = ($count / $word_count) * 100;
                
                $keyword_counts[$keyword] = [
                    'count' => $count,
                    'density' => round($density, 2)
                ];
                
                if ($density >= 0.5 && $density <= 2.5) {
                    $checks[] = [
                        'status' => 'good',
                        'icon' => 'yes',
                        'message' => sprintf(
                            __('"%s" anahtar kelimesi ideal yoğunlukta (%.2f%%).', 'wp-ai-seo'),
                            $keyword,
                            $density
                        )
                    ];
                    $score += 10;
                } elseif ($density > 0) {
                    $checks[] = [
                        'status' => 'warning',
                        'icon' => 'warning',
                        'message' => sprintf(
                            __('"%s" anahtar kelime yoğunluğu ideal değil (%.2f%%). İdeal: 0.5%% - 2.5%%.', 'wp-ai-seo'),
                            $keyword,
                            $density
                        )
                    ];
                    $score += 5;
                } else {
                    $checks[] = [
                        'status' => 'bad',
                        'icon' => 'no',
                        'message' => sprintf(
                            __('"%s" anahtar kelimesi içerikte hiç kullanılmamış.', 'wp-ai-seo'),
                            $keyword
                        )
                    ];
                }
            }
        } else {
            $checks[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => __('Odak anahtar kelimesi belirlenmemiş.', 'wp-ai-seo')
            ];
        }

        // Başlık yapısı kontrolü
        preg_match_all('/<h([1-6]).*?>(.*?)<\/h\1>/i', $content, $headings);
        if (!empty($headings[0])) {
            if (in_array('1', $headings[1])) {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => __('İçerikte H1 etiketi kullanılmış. Sayfa başlığı dışında H1 kullanılmamalı.', 'wp-ai-seo')
                ];
                $score += 5;
            } else {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => __('Başlık yapısı doğru kullanılmış.', 'wp-ai-seo')
                ];
                $score += 10;
            }
        } else {
            $checks[] = [
                'status' => 'warning',
                'icon' => 'warning',
                'message' => __('İçerikte alt başlık (H2-H6) kullanılmamış.', 'wp-ai-seo')
            ];
        }

        // Görsel alt etiketi kontrolü
        preg_match_all('/<img[^>]+>/i', $content, $images);
        if (!empty($images[0])) {
            $total_images = count($images[0]);
            $images_with_alt = 0;
            
            foreach ($images[0] as $image) {
                if (preg_match('/alt=["\'](.*?)["\']/i', $image)) {
                    $images_with_alt++;
                }
            }
            
            if ($images_with_alt === $total_images) {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => __('Tüm görsellerde alt etiketi kullanılmış.', 'wp-ai-seo')
                ];
                $score += 10;
            } else {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => sprintf(
                        __('%d görselden %d tanesinde alt etiketi eksik.', 'wp-ai-seo'),
                        $total_images,
                        $total_images - $images_with_alt
                    )
                ];
                $score += 5;
            }
        }

        // İç bağlantı kontrolü
        preg_match_all('/<a[^>]+href=["\'](.*?)["\'][^>]*>/i', $content, $links);
        if (!empty($links[1])) {
            $internal_links = 0;
            $site_url = get_site_url();
            
            foreach ($links[1] as $link) {
                if (strpos($link, $site_url) === 0 || strpos($link, '/') === 0) {
                    $internal_links++;
                }
            }
            
            if ($internal_links > 0) {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => sprintf(
                        __('%d iç bağlantı kullanılmış.', 'wp-ai-seo'),
                        $internal_links
                    )
                ];
                $score += 10;
            } else {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => __('İç bağlantı kullanılmamış.', 'wp-ai-seo')
                ];
            }
        }

        // Okunabilirlik analizi
        $readability = $this->analyze_readability($content);
        if ($readability >= 60) {
            $checks[] = [
                'status' => 'good',
                'icon' => 'yes',
                'message' => sprintf(
                    __('İçerik okunabilirlik puanı iyi: %d/100.', 'wp-ai-seo'),
                    $readability
                )
            ];
            $score += 10;
        } elseif ($readability >= 40) {
            $checks[] = [
                'status' => 'warning',
                'icon' => 'warning',
                'message' => sprintf(
                    __('İçerik okunabilirlik puanı orta: %d/100.', 'wp-ai-seo'),
                    $readability
                )
            ];
            $score += 5;
        } else {
            $checks[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => sprintf(
                    __('İçerik okunabilirlik puanı düşük: %d/100.', 'wp-ai-seo'),
                    $readability
                )
            ];
        }

        // Meta robots kontrolü
        if (!empty($meta['robots_meta'])) {
            if ($meta['robots_meta'] === 'noindex,nofollow') {
                $checks[] = [
                    'status' => 'warning',
                    'icon' => 'warning',
                    'message' => __('Sayfa arama motorlarından gizlenmiş.', 'wp-ai-seo')
                ];
            } else {
                $checks[] = [
                    'status' => 'good',
                    'icon' => 'yes',
                    'message' => __('Meta robots ayarı yapılmış.', 'wp-ai-seo')
                ];
                $score += 10;
            }
        }

        // Canonical URL kontrolü
        if (!empty($meta['canonical_url'])) {
            $checks[] = [
                'status' => 'good',
                'icon' => 'yes',
                'message' => __('Canonical URL ayarlanmış.', 'wp-ai-seo')
            ];
            $score += 10;
        }

        // Toplam puanı normalize et
        $total_checks = count($checks);
        $max_score = $total_checks * 10;
        $normalized_score = round(($score / $max_score) * 100);

        return [
            'score' => [
                'value' => $normalized_score,
                'class' => $normalized_score >= 80 ? 'good' : ($normalized_score >= 50 ? 'ok' : 'bad')
            ],
            'checks' => $checks,
            'meta' => $meta,
            'content_stats' => [
                'word_count' => $word_count,
                'headings' => count($headings[0]),
                'images' => $total_images ?? 0,
                'links' => count($links[0] ?? []),
                'readability' => $readability
            ]
        ];
    }

    /**
     * İçerik okunabilirliğini analiz et
     *
     * @param string $content
     * @return int Okunabilirlik puanı (0-100)
     */
    private function analyze_readability(string $content): int {
        // HTML etiketlerini temizle
        $text = strip_tags($content);
        
        // Cümle sayısı
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentence_count = count($sentences);
        
        if ($sentence_count === 0) {
            return 0;
        }
        
        // Kelime sayısı
        $words = str_word_count($text, 1);
        $word_count = count($words);
        
        if ($word_count === 0) {
            return 0;
        }
        
        // Hece sayısı
        $syllable_count = 0;
        foreach ($words as $word) {
            $syllable_count += $this->count_syllables($word);
        }
        
        // Ortalama cümle uzunluğu
        $avg_sentence_length = $word_count / $sentence_count;
        
        // Ortalama kelime uzunluğu
        $avg_syllables_per_word = $syllable_count / $word_count;
        
        // Flesch Reading Ease formülü
        $score = 206.835 - (1.015 * $avg_sentence_length) - (84.6 * $avg_syllables_per_word);
        
        // Skoru 0-100 aralığına normalize et
        return max(0, min(100, round($score)));
    }

    /**
     * Kelimede hece sayısını hesapla
     *
     * @param string $word
     * @return int Hece sayısı
     */
    private function count_syllables(string $word): int {
        $word = strtolower($word);
        $word = preg_replace('/[^a-z]/', '', $word);
        
        // Sessiz harfler arasındaki sesli harfleri say
        $count = preg_match_all('/[aeıioöuü]/ui', $word);
        
        // Sondaki sessiz e'yi düşür
        if (substr($word, -1) === 'e') {
            $count--;
        }
        
        // En az bir hece olmalı
        return max(1, $count);
    }
}