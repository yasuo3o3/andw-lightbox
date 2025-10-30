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

            $fallback = 'window.addEventListener("load",function(){if("function"!==typeof window.GLightbox){var dispatchReady=function(source){var evt;try{evt=new CustomEvent("andwLightboxReady",{detail:{source:source}});}catch(err){evt=document.createEvent("Event");evt.initEvent("andwLightboxReady",true,true);evt.detail={source:source};}document.dispatchEvent(evt);};var link=document.createElement("link");link.rel="stylesheet";link.href=' . wp_json_encode( $local_css ) . ';document.head.appendChild(link);var script=document.createElement("script");script.src=' . wp_json_encode( $local_js ) . ';script.onload=function(){if("function"===typeof window.GLightbox){dispatchReady("cdn-inline-fallback");}};document.head.appendChild(script);}});';
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

        // デザイン設定によるカスタムCSS出力
        $custom_css = $this->get_design_css();
        if ( $custom_css ) {
            wp_add_inline_style( 'andw-lightbox', $custom_css );
        }
    }

    /**
     * Conditionally enqueue frontend assets.
     */
    public function enqueue_front_assets() {
        if ( ! $this->front_required ) {
            return;
        }

        $this->ensure_front_assets_loaded();
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

        // Ensure styles and scripts are loaded even when detected late.
        $this->output_fallback_assets();
    }

    /**
     * Ensure assets load when requirement is detected late in the request.
     */
    private function output_fallback_assets() {
        $this->ensure_front_assets_loaded();
    }

    /**
     * Ensure frontend assets are enqueued and localized.
     */
    private function ensure_front_assets_loaded() {
        if ( ! wp_style_is( 'andw-lightbox', 'enqueued' ) ) {
            wp_enqueue_style( 'andw-lightbox' );
        }

        if ( ! wp_script_is( 'andw-lightbox', 'enqueued' ) ) {
            wp_enqueue_script( 'andw-lightbox' );
        }

        if ( $this->localized ) {
            return;
        }

        $this->localized = true;

        wp_add_inline_script(
            'andw-lightbox',
            'window.andwLightboxSettings = ' . wp_json_encode( $this->get_frontend_settings() ) . ';',
            'before'
        );
    }

    /**
     * Build localized settings for the frontend script.
     *
     * @return array
     */
    private function get_frontend_settings() {
        $animation = $this->settings->get( 'default_animation' );
        $gallery_animation = $this->settings->get( 'gallery_animation' );

        if ( 'default' === $animation ) {
            $animation = 'zoom';
        }

        if ( ! in_array( $gallery_animation, array( 'slide', 'zoom', 'fade', 'none' ), true ) ) {
            $gallery_animation = 'slide';
        }

        return array(
            'defaultAnimation' => $animation,
            'galleryAnimation' => $gallery_animation,
            'observer'         => $this->settings->is_enabled_flag( 'infinite_scroll' ),
            'hover'            => array(
                'effect'   => $this->settings->get( 'default_hover' ),
                'strength' => intval( $this->settings->get( 'default_hover_strength' ) ),
            ),
        );
    }

    /**
     * Generate custom CSS based on design settings.
     *
     * @return string
     */
    private function get_design_css() {
        $css_parts = array();

        // サイズ制御（CDN版とローカル版両方に対応）
        $max_width = $this->settings->get( 'design_max_width' );
        $max_height = $this->settings->get( 'design_max_height' );
        if ( $max_width || $max_height ) {
            $style_props = '';
            if ( $max_width ) {
                $style_props .= 'max-width: ' . esc_attr( $max_width ) . ' !important; ';
            }
            if ( $max_height ) {
                $style_props .= 'max-height: ' . esc_attr( $max_height ) . ' !important; ';
            }

            // CDN版GLightbox用（ライトボックス全体サイズ制限）
            $css_parts[] = '.glightbox-container .gslide { ' . $style_props . '}';

            // ローカル版（フォールバック）用
            $css_parts[] = '.andw-glightbox-stage { ' . $style_props . '}';
        }

        // オーバーレイ背景（CDN版 .goverlay と ローカル版 .andw-glightbox-backdrop 両方に対応）
        $color = $this->settings->get( 'design_overlay_color' );
        $opacity = $this->settings->get( 'design_overlay_opacity' );
        if ( $color && $opacity ) {
            $hex = ltrim( $color, '#' );
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );

            $rgba_background = sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, esc_attr( $opacity ) );

            // CDN版GLightbox用
            $css_parts[] = '.goverlay { background: ' . $rgba_background . ' !important; }';

            // ローカル版（フォールバック）用
            $css_parts[] = '.andw-glightbox-backdrop { background: ' . $rgba_background . ' !important; }';
        }

        // カスタムCSS（サニタイズ済み生文字列をそのまま出力）
        if ( $this->settings->is_enabled_flag( 'design_mobile_navigation' ) ) {
            $css_parts[] = <<<CSS
@media (max-width: 782px) {
    .glightbox-clean .gprev,
    .glightbox-clean .gnext {
        top: 50% !important;
        transform: translateY(-50%);
        width: 25px;
        height: 44px;
        background: rgba(0, 0, 0, 0.25);
    }
    .glightbox-clean .gprev {
        left: 0 !important;
        right: auto !important;
    }
    .glightbox-clean .gnext {
        right: 0 !important;
        left: auto !important;
    }
    .glightbox-clean .gprev svg,
    .glightbox-clean .gnext svg {
        width: 15px;
        height: auto;
    }
    .andw-glightbox-prev,
    .andw-glightbox-next {
        width: 25px;
        height: 44px;
        background: rgba(0, 0, 0, 0.25);
    }
    .andw-glightbox-prev {
        left: 0;
        right: auto;
    }
    .andw-glightbox-next {
        right: 0;
        left: auto;
    }
    .andw-glightbox-prev svg,
    .andw-glightbox-next svg {
        width: 15px;
        height: auto;
    }
}
CSS;
        }

        $custom = $this->settings->get( 'design_custom_css' );
        if ( $custom ) {
            $css_parts[] = $custom;
        }

        // CDN版GLightbox 説明文エリアのデフォルトスタイルリセット
        $css_parts[] = '.glightbox-container .glightbox-clean .gdesc-inner { padding: 0; }';
        $css_parts[] = '.glightbox-container .glightbox-clean .gslide-title { font-size: inherit; font-weight: inherit; font-family: inherit; margin-bottom: 0; line-height: inherit; }';
        $css_parts[] = '.glightbox-container .glightbox-clean .gslide-desc { font-size: inherit; margin-bottom: 0; font-family: inherit; line-height: inherit; }';

        return implode( "\n", $css_parts );
    }
}

