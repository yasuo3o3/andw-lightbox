<?php
/**
 * Asset loader for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Assets {
    /**
     * Settings accessor.
     *
     * @var Andw_Lightbox_Settings
     */
    private $settings;

    /**
     * Whether frontend assets must be emitted.
     *
     * @var bool
     */
    private $front_required = false;

    /**
     * Whether localization has been printed.
     *
     * @var bool
     */
    private $localized = false;

    /**
     * Track registration.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Track late enqueue setup.
     *
     * @var bool
     */
    private $late_enqueue_setup = false;

    public function __construct( Andw_Lightbox_Settings $settings ) {
        $this->settings = $settings;

        add_action( 'wp_enqueue_scripts', array( $this, 'register_front_assets' ), 5 );
        add_action( 'wp', array( $this, 'check_if_front_assets_needed' ), 5 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ), 20 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Ensure assets are loaded for current request.
     */
    public function mark_front_needed() {
        $this->front_required = true;

        // wp_enqueue_scripts後に呼ばれた場合は安全な遅延読み込みを設定
        if ( did_action( 'wp_enqueue_scripts' ) && ! did_action( 'wp_footer' ) ) {
            $this->setup_safe_late_enqueue();
        }
    }

    /**
     * Check if front assets are needed before wp_enqueue_scripts.
     */
    public function check_if_front_assets_needed() {
        if ( is_admin() || ! is_singular() ) {
            return;
        }

        global $post;
        if ( ! $post ) {
            return;
        }

        // コンテンツに画像が含まれているかチェック
        if ( has_blocks( $post->post_content ) ) {
            $blocks = parse_blocks( $post->post_content );
            if ( $this->has_supported_blocks_with_images( $blocks ) ) {
                $this->front_required = true;
                return;
            }
        }

        // クラシックエディターコンテンツをチェック
        if ( false !== strpos( $post->post_content, '<img' ) ) {
            $this->front_required = true;
            return;
        }

        // ショートコード系をチェック
        if ( $this->has_image_generating_shortcodes( $post->post_content ) ) {
            $this->front_required = true;
        }
    }

    /**
     * Check if blocks contain supported blocks with images.
     */
    private function has_supported_blocks_with_images( $blocks ) {
        $supported_blocks = array( 'core/image', 'core/gallery', 'core/media-text' );

        foreach ( $blocks as $block ) {
            if ( in_array( $block['blockName'], $supported_blocks, true ) ) {
                return true;
            }

            // 入れ子ブロックもチェック
            if ( ! empty( $block['innerBlocks'] ) ) {
                if ( $this->has_supported_blocks_with_images( $block['innerBlocks'] ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if content contains shortcodes that generate images.
     *
     * @param string $content Post content.
     * @return bool
     */
    private function has_image_generating_shortcodes( $content ) {
        $image_shortcodes = array(
            'gallery',
            'wp_caption',
            'caption',
        );

        foreach ( $image_shortcodes as $shortcode ) {
            if ( has_shortcode( $content, $shortcode ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register frontend asset handles.
     */
    public function register_front_assets() {
        if ( true === self::$registered ) {
            return;
        }

        self::$registered = true;

        $glightbox_handle = 'andw-lightbox-glightbox';
        $source           = $this->settings->get( 'glightbox_source' );
        $version          = $this->settings->get( 'glightbox_version' );

        $local_js  = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/glightbox-fallback.js';
        $local_css = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/glightbox-fallback.css';

        if ( 'cdn' === $source ) {
            $base = sprintf( 'https://cdn.jsdelivr.net/npm/glightbox@%s/dist', rawurlencode( $version ) );

            wp_register_style( $glightbox_handle, $base . '/css/glightbox.min.css', array(), $version );
            wp_register_script( $glightbox_handle, $base . '/js/glightbox.min.js', array(), $version, true );

            $fallback = 'window.addEventListener("load",function(){if("function"!==typeof window.GLightbox){var l=document.createElement("link");l.rel="stylesheet";l.href="' . esc_js( $local_css ) . '";document.head.appendChild(l);var s=document.createElement("script");s.src="' . esc_js( $local_js ) . '";s.onload=function(){if("function"===typeof window.GLightbox){var e=new CustomEvent("andwLightboxReady",{detail:{source:"cdn-inline-fallback"}});document.dispatchEvent(e);}};document.head.appendChild(s);}});';
            wp_add_inline_script( $glightbox_handle, $fallback );
        } else {
            wp_register_style( $glightbox_handle, $local_css, array(), ANDW_LIGHTBOX_VERSION );
            wp_register_script( $glightbox_handle, $local_js, array(), ANDW_LIGHTBOX_VERSION, true );
        }

        wp_register_style(
            'andw-lightbox',
            ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/andw-lightbox.css',
            array( $glightbox_handle ),
            ANDW_LIGHTBOX_VERSION
        );

        wp_register_script(
            'andw-lightbox',
            ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/andw-lightbox.js',
            array( $glightbox_handle ),
            ANDW_LIGHTBOX_VERSION,
            true
        );
    }

    /**
     * Conditionally enqueue frontend assets.
     */
    public function enqueue_front_assets() {
        if ( ! $this->front_required ) {
            return;
        }

        wp_enqueue_style( 'andw-lightbox' );
        wp_enqueue_script( 'andw-lightbox' );

        if ( $this->localized ) {
            return;
        }

        $this->localized = true;

        $animation = $this->settings->get( 'default_animation' );
        if ( 'default' === $animation ) {
            $animation = 'slide';
        }

        $data = array(
            'defaultAnimation' => $animation,
            'observer'         => $this->settings->is_enabled_flag( 'infinite_scroll' ),
            'hover'            => array(
                'effect'   => $this->settings->get( 'default_hover' ),
                'strength' => intval( $this->settings->get( 'default_hover_strength' ) ),
            ),
        );

        wp_add_inline_script( 'andw-lightbox', 'window.andwLightboxSettings = ' . wp_json_encode( $data ) . ';', 'before' );
    }

    /**
     * Load admin-only assets when necessary.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_andw-lightbox' === $hook ) {
            wp_enqueue_style(
                'andw-lightbox-admin',
                ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/andw-lightbox-admin.css',
                array(),
                ANDW_LIGHTBOX_VERSION
            );
        }
    }


    /**
     * Setup safe late enqueue using wp_footer hook.
     * This prevents dependency issues by using proper timing.
     */
    private function setup_safe_late_enqueue() {
        // 一度だけ実行
        if ( $this->late_enqueue_setup ) {
            return;
        }

        $this->late_enqueue_setup = true;

        // wp_footer フックで安全に遅延読み込み
        add_action( 'wp_footer', array( $this, 'safe_late_enqueue' ), 5 );
    }

    /**
     * Safely enqueue assets in footer if needed.
     */
    public function safe_late_enqueue() {
        // 既にenqueueされていれば何もしない
        if ( wp_style_is( 'andw-lightbox', 'enqueued' ) && wp_script_is( 'andw-lightbox', 'enqueued' ) ) {
            return;
        }

        // まず assets が register されているか確認
        if ( ! wp_style_is( 'andw-lightbox', 'registered' ) ) {
            $this->register_front_assets();
        }

        // フッターでの遅延読み込み（インライン出力）
        $this->output_fallback_assets();
    }

    /**
     * Output complete assets inline in footer.
     */
    private function output_fallback_assets() {
        // 1. 必要なCSSファイルを順序通りに出力
        $css_urls = $this->get_required_css_urls();
        foreach ( $css_urls as $id => $url ) {
            if ( $url ) {
                echo '<link rel="stylesheet" href="' . esc_url( $url ) . '" id="' . esc_attr( $id ) . '">' . "\n";
            }
        }

        // 2. 設定の出力（JSファイル読み込み前に実行）
        if ( ! $this->localized ) {
            $this->output_inline_settings();
        }

        // 3. 必要なJSファイルを順序通りに出力
        $js_urls = $this->get_required_js_urls();
        foreach ( $js_urls as $id => $url ) {
            if ( $url ) {
                echo '<script src="' . esc_url( $url ) . '" id="' . esc_attr( $id ) . '"></script>' . "\n";
            }
        }

        // 4. CDN利用時のフォールバック機能（設定適用後に実行）
        if ( 'cdn' === $this->settings->get( 'glightbox_source' ) ) {
            $this->output_cdn_fallback_script();
        }
    }

    /**
     * Get required CSS URLs in proper order.
     *
     * @return array Array of CSS URLs with IDs as keys.
     */
    private function get_required_css_urls() {
        $urls = array();

        $source  = $this->settings->get( 'glightbox_source' );
        $version = $this->settings->get( 'glightbox_version' );

        // 1. GLightbox CSS (依存関係)
        if ( 'cdn' === $source ) {
            $base = sprintf( 'https://cdn.jsdelivr.net/npm/glightbox@%s/dist', rawurlencode( $version ) );
            $urls['andw-lightbox-glightbox-css'] = $base . '/css/glightbox.min.css';
        } else {
            $urls['andw-lightbox-glightbox-css'] = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/glightbox-fallback.css';
        }

        // 2. プラグイン固有CSS
        $urls['andw-lightbox-css'] = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/andw-lightbox.css';

        return $urls;
    }

    /**
     * Get required JS URLs in proper order.
     *
     * @return array Array of JS URLs with IDs as keys.
     */
    private function get_required_js_urls() {
        $urls = array();

        $source  = $this->settings->get( 'glightbox_source' );
        $version = $this->settings->get( 'glightbox_version' );

        // 1. GLightbox JS (依存関係)
        if ( 'cdn' === $source ) {
            $base = sprintf( 'https://cdn.jsdelivr.net/npm/glightbox@%s/dist', rawurlencode( $version ) );
            $urls['andw-lightbox-glightbox-js'] = $base . '/js/glightbox.min.js';
        } else {
            $urls['andw-lightbox-glightbox-js'] = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/glightbox-fallback.js';
        }

        // 2. プラグイン固有JS
        $urls['andw-lightbox-js'] = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/andw-lightbox.js';

        return $urls;
    }

    /**
     * Output inline settings for fallback.
     */
    private function output_inline_settings() {
        $animation = sanitize_key( $this->settings->get( 'default_animation' ) );

        $data = array(
            'defaultAnimation' => $animation,
            'observer'         => $this->settings->is_enabled_flag( 'infinite_scroll' ),
            'hover'            => array(
                'effect'   => $this->settings->get( 'default_hover' ),
                'strength' => intval( $this->settings->get( 'default_hover_strength' ) ),
            ),
        );

        echo '<script>window.andwLightboxSettings = ' . wp_json_encode( $data ) . ';</script>' . "\n";
        $this->localized = true;
    }

    /**
     * Output CDN fallback script with proper timing.
     */
    private function output_cdn_fallback_script() {
        $fallback_js = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/glightbox-fallback.js';
        $fallback_css = ANDW_LIGHTBOX_PLUGIN_URL . 'assets/css/glightbox-fallback.css';

        echo '<script>' . "\n";
        echo 'window.addEventListener("load", function() {' . "\n";
        echo '  if (typeof window.GLightbox !== "function") {' . "\n";
        echo '    // フォールバックCSS読み込み' . "\n";
        echo '    var fallbackCSS = document.createElement("link");' . "\n";
        echo '    fallbackCSS.rel = "stylesheet";' . "\n";
        echo '    fallbackCSS.href = "' . esc_js( $fallback_css ) . '";' . "\n";
        echo '    document.head.appendChild(fallbackCSS);' . "\n";
        echo '    // フォールバックJS読み込み' . "\n";
        echo '    var fallbackJS = document.createElement("script");' . "\n";
        echo '    fallbackJS.src = "' . esc_js( $fallback_js ) . '";' . "\n";
        echo '    fallbackJS.onload = function() {' . "\n";
        echo '      // 設定が既に定義されていることを前提に初期化' . "\n";
        echo '      if (window.andwLightboxSettings && typeof window.GLightbox === "function") {' . "\n";
        echo '        console.log("GLightbox fallback loaded successfully");' . "\n";
        echo '        // カスタムイベント発火で再初期化をトリガ' . "\n";
        echo '        var event = new CustomEvent("andwLightboxReady", {' . "\n";
        echo '          detail: { source: "cdn-fallback" }' . "\n";
        echo '        });' . "\n";
        echo '        document.dispatchEvent(event);' . "\n";
        echo '      }' . "\n";
        echo '    };' . "\n";
        echo '    document.head.appendChild(fallbackJS);' . "\n";
        echo '  }' . "\n";
        echo '});' . "\n";
        echo '</script>' . "\n";
    }
}
