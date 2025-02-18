/**
 * WP AI-SEO Admin Scripts
 */
(function($) {
    'use strict';

    const WPAiSeo = {
        init: function() {
            this.initMetaBox();
            this.initSettingsPage();
            this.initCharacterCounter();
            this.initSeoAnalysis();
        },

        /**
         * Meta kutusu işlevselliği
         */
        initMetaBox: function() {
            const $metaBox = $('.wp-ai-seo-meta-box');
            if (!$metaBox.length) return;

            // Başlık önizleme
            const $titleInput = $('#wp_ai_seo_meta_title');
            const $titlePreview = $('.wp-ai-seo-preview-title');
            
            $titleInput.on('input', function() {
                let title = $(this).val();
                if (!title) {
                    title = wpAiSeoAdmin.defaultTitle;
                }
                $titlePreview.text(title);
            });

            // Açıklama önizleme
            const $descInput = $('#wp_ai_seo_meta_description');
            const $descPreview = $('.wp-ai-seo-preview-description');
            
            $descInput.on('input', function() {
                let desc = $(this).val();
                if (!desc) {
                    desc = wpAiSeoAdmin.defaultDescription;
                }
                $descPreview.text(desc);
            });

            // Anahtar kelime analizi
            const $keywordsInput = $('#wp_ai_seo_focus_keywords');
            $keywordsInput.on('change', this.analyzeKeywords);
        },

        /**
         * Ayarlar sayfası işlevselliği
         */
        initSettingsPage: function() {
            const $nav = $('.wp-ai-seo-settings-nav');
            if (!$nav.length) return;

            $nav.on('click', 'a', function(e) {
                e.preventDefault();
                const target = $(this).attr('href').replace('#', '');
                
                // Aktif sekmeyi güncelle
                $nav.find('a').removeClass('active');
                $(this).addClass('active');
                
                // İçeriği göster/gizle
                $('.wp-ai-seo-settings-section').removeClass('active');
                $('#' + target).addClass('active');
                
                // URL hash'i güncelle
                window.location.hash = target;
            });

            // Sayfa yüklendiğinde hash varsa ilgili sekmeyi aç
            if (window.location.hash) {
                $nav.find('a[href="' + window.location.hash + '"]').trigger('click');
            }
        },

        /**
         * Karakter sayacı
         */
        initCharacterCounter: function() {
            $('.wp-ai-seo-counter').each(function() {
                const $counter = $(this);
                const $input = $counter.prev('input, textarea');
                const $current = $counter.find('.wp-ai-seo-counter-current');
                const max = $input.attr('maxlength');

                function updateCount() {
                    const count = $input.val().length;
                    $current.text(count);

                    // Renk değişimi
                    if (count > max * 0.9) {
                        $current.css('color', '#dc3232'); // Kırmızı
                    } else if (count > max * 0.7) {
                        $current.css('color', '#ffb900'); // Sarı
                    } else {
                        $current.css('color', '#46b450'); // Yeşil
                    }
                }

                $input.on('input', updateCount);
                updateCount();
            });
        },

        /**
         * SEO analizi
         */
        initSeoAnalysis: function() {
            const self = this;
            $('#wp-ai-seo-analyze').on('click', function(e) {
                e.preventDefault();
                self.runSeoAnalysis();
            });
        },

        /**
         * SEO analizi çalıştır
         */
        runSeoAnalysis: function() {
            const postId = $('#post_ID').val();
            const $results = $('#wp-ai-seo-analysis-results');

            $.ajax({
                url: wpAiSeoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_ai_seo_analyze',
                    post_id: postId,
                    nonce: wpAiSeoAdmin.nonce
                },
                beforeSend: function() {
                    $results.html('<p class="wp-ai-seo-loading">Analiz yapılıyor...</p>');
                },
                success: function(response) {
                    if (response.success) {
                        self.renderAnalysisResults(response.data);
                    } else {
                        $results.html('<p class="wp-ai-seo-error">' + response.data + '</p>');
                    }
                },
                error: function() {
                    $results.html('<p class="wp-ai-seo-error">Analiz sırasında bir hata oluştu.</p>');
                }
            });
        },

        /**
         * Analiz sonuçlarını göster
         */
        renderAnalysisResults: function(results) {
            const $results = $('#wp-ai-seo-analysis-results');
            let html = '<div class="wp-ai-seo-analysis">';

            // Genel skor
            html += '<div class="wp-ai-seo-score ' + results.score.class + '">';
            html += results.score.value + '/100';
            html += '</div>';

            // Kontrol listesi
            html += '<ul class="wp-ai-seo-checklist">';
            results.checks.forEach(function(check) {
                html += '<li class="' + check.status + '">';
                html += '<span class="dashicons dashicons-' + check.icon + '"></span>';
                html += check.message;
                if (check.recommendation) {
                    html += '<p class="recommendation">' + check.recommendation + '</p>';
                }
                html += '</li>';
            });
            html += '</ul>';

            html += '</div>';
            $results.html(html);
        },

        /**
         * Anahtar kelime analizi
         */
        analyzeKeywords: function() {
            const keywords = $(this).val().split(',').map(k => k.trim());
            const content = $('#content').val();
            let densities = {};

            keywords.forEach(function(keyword) {
                if (!keyword) return;

                // Kelime yoğunluğu hesapla
                const regex = new RegExp(keyword, 'gi');
                const matches = content.match(regex);
                const count = matches ? matches.length : 0;
                const words = content.split(/\s+/).length;
                const density = ((count / words) * 100).toFixed(1);

                densities[keyword] = {
                    count: count,
                    density: density
                };
            });

            // Sonuçları göster
            let html = '<div class="wp-ai-seo-keyword-analysis">';
            Object.keys(densities).forEach(function(keyword) {
                const data = densities[keyword];
                let status = 'ok';

                if (data.density < 0.5) {
                    status = 'low';
                } else if (data.density > 2.5) {
                    status = 'high';
                }

                html += '<div class="keyword-item ' + status + '">';
                html += '<strong>' + keyword + '</strong>: ';
                html += data.count + ' kez kullanıldı (%;' + data.density + ')';
                html += '</div>';
            });
            html += '</div>';

            $('#wp-ai-seo-keyword-analysis').html(html);
        }
    };

    // DOM hazır olduğunda başlat
    $(function() {
        WPAiSeo.init();
    });

})(jQuery);