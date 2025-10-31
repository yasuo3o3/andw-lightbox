<?php
/**
 * Settings handler for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Settings {
    /**
     * Default placeholder CSS template content.
     *
     * @var string
     */
    const PLACEHOLDER_CSS = '/* GLightbox 説明文エリアのカスタマイズ */
.glightbox-clean .gslide-media{ /* メディア表示のコンテナ */
margin-top:25px;
}

.glightbox-clean .gslide-description { /* 説明文全体のコンテナ */
background-color:rgba(0,0,0,0); padding-bottom:10px;
}

.glightbox-clean .gdesc-inner { /* 説明文内部コンテナ */
padding: 0 0.5rem 0.2rem 0.5rem;
}

.glightbox-clean .gslide-title { /* タイトル部分（h4要素） */
color:#fff; display:inline; font-size:1rem; line-height:1.1;
}

.glightbox-clean .gslide-desc { /* 説明文テキスト部分（div要素） */
color:#fff; display:inline; font-size:0.8rem; line-height:1.1;
}

.glightbox-clean .gslide-desc::before {
content:"-"; margin: 0 5px;
}';

    /**
     * Singleton instance.
     *
     * @var Andw_Lightbox_Settings|null
     */
    private static $instance = null;

    /**
     * Option name used for persistent storage.
     *
     * @var string
     */
    private $option_name = 'andw_lightbox_options';

    /**
     * Cached option values.
     *
     * @var array
     */
    private $options = array();

    private function __construct() {
        $this->options = wp_parse_args( get_option( $this->option_name, array() ), $this->get_defaults() );
    }

    /**
     * Retrieve singleton instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Return defaults for all options.
     */
    public function get_defaults() {
        return array(
            'enabled'                => '1',
            'default_slide'          => '1',
            'default_gallery'        => 'single',
            'default_hover'          => 'none',
            'default_hover_strength' => '40',
            'default_transform'      => 'none',
            'default_transform_strength' => '40',
            'default_animation'      => 'zoom',
            'gallery_animation'      => 'slide',
            'enforce_group_animation' => '1',
            'enforce_gallery_animation' => '0',
            'default_size'           => 'default',
            'allow_full'             => '0',
            'infinite_scroll'        => '0',
            'glightbox_source'       => 'local',
            'glightbox_version'      => '3.3.0',
            // デザイン設定
            'design_show_title'      => '1',
            'design_show_description' => '1',
            'design_max_width'       => '',
            'design_max_height'      => '',
            'design_overlay_color'   => '#000000',
            'design_overlay_opacity' => '0.92',
            'design_mobile_navigation' => '0',
            'design_custom_css'      => '',
        );
    }

    /**
     * Register settings with WordPress.
     */
    public function register() {
        register_setting(
            'andw_lightbox_settings',
            $this->option_name,
            array(
                'sanitize_callback' => array( $this, 'sanitize' ),
                'default'           => $this->get_defaults(),
            )
        );
    }

    /**
     * Sanitize stored settings.
     *
     * @param array $input Raw user input.
     */
    public function sanitize( $input ) {
        $defaults = $this->get_defaults();

        // 既存の保存値を取得（タブ別フォーム対応）
        $existing_options = get_option( $this->option_name, array() );

        $sanitized = array();

        // 基本設定のサニタイズ（POSTに含まれていない場合は既存値を保持）
        if ( isset( $input['enabled'] ) ) {
            $sanitized['enabled'] = andw_lightbox_sanitize_checkbox( $input['enabled'] );
        } else {
            $sanitized['enabled'] = isset( $existing_options['enabled'] ) ? $existing_options['enabled'] : $defaults['enabled'];
        }

        if ( isset( $input['default_slide'] ) ) {
            $sanitized['default_slide'] = andw_lightbox_sanitize_checkbox( $input['default_slide'] );
        } else {
            $sanitized['default_slide'] = isset( $existing_options['default_slide'] ) ? $existing_options['default_slide'] : $defaults['default_slide'];
        }

        if ( isset( $input['default_gallery'] ) ) {
            $sanitized['default_gallery'] = andw_lightbox_sanitize_select( $input['default_gallery'], andw_lightbox_get_gallery_options() );
        } else {
            $sanitized['default_gallery'] = isset( $existing_options['default_gallery'] ) ? $existing_options['default_gallery'] : $defaults['default_gallery'];
        }

        if ( isset( $input['default_hover'] ) ) {
            $sanitized['default_hover'] = andw_lightbox_sanitize_select( $input['default_hover'], andw_lightbox_get_hover_options() );
        } else {
            $sanitized['default_hover'] = isset( $existing_options['default_hover'] ) ? $existing_options['default_hover'] : $defaults['default_hover'];
        }

        if ( isset( $input['default_hover_strength'] ) ) {
            $sanitized['default_hover_strength'] = andw_lightbox_sanitize_int_range( $input['default_hover_strength'], 0, 100 );
        } else {
            $sanitized['default_hover_strength'] = isset( $existing_options['default_hover_strength'] ) ? $existing_options['default_hover_strength'] : $defaults['default_hover_strength'];
        }

        if ( isset( $input['default_transform'] ) ) {
            $sanitized['default_transform'] = andw_lightbox_sanitize_select( $input['default_transform'], andw_lightbox_get_transform_options() );
        } else {
            $sanitized['default_transform'] = isset( $existing_options['default_transform'] ) ? $existing_options['default_transform'] : $defaults['default_transform'];
        }

        if ( isset( $input['default_transform_strength'] ) ) {
            $sanitized['default_transform_strength'] = andw_lightbox_sanitize_int_range( $input['default_transform_strength'], 0, 100 );
        } else {
            $sanitized['default_transform_strength'] = isset( $existing_options['default_transform_strength'] ) ? $existing_options['default_transform_strength'] : $defaults['default_transform_strength'];
        }

        if ( isset( $input['default_animation'] ) ) {
            $sanitized['default_animation'] = andw_lightbox_sanitize_select( $input['default_animation'], andw_lightbox_get_animation_options() );
        } else {
            $sanitized['default_animation'] = isset( $existing_options['default_animation'] ) ? $existing_options['default_animation'] : $defaults['default_animation'];
        }

        if ( isset( $input['gallery_animation'] ) ) {
            $sanitized['gallery_animation'] = andw_lightbox_sanitize_select( $input['gallery_animation'], andw_lightbox_get_gallery_animation_options() );
        } else {
            $sanitized['gallery_animation'] = isset( $existing_options['gallery_animation'] ) ? $existing_options['gallery_animation'] : $defaults['gallery_animation'];
        }

        if ( isset( $input['enforce_group_animation'] ) ) {
            $sanitized['enforce_group_animation'] = andw_lightbox_sanitize_checkbox( $input['enforce_group_animation'] );
        } else {
            $sanitized['enforce_group_animation'] = isset( $existing_options['enforce_group_animation'] ) ? $existing_options['enforce_group_animation'] : $defaults['enforce_group_animation'];
        }

        if ( isset( $input['enforce_gallery_animation'] ) ) {
            $sanitized['enforce_gallery_animation'] = andw_lightbox_sanitize_checkbox( $input['enforce_gallery_animation'] );
        } else {
            $sanitized['enforce_gallery_animation'] = isset( $existing_options['enforce_gallery_animation'] ) ? $existing_options['enforce_gallery_animation'] : $defaults['enforce_gallery_animation'];
        }

        if ( isset( $input['default_size'] ) ) {
            $sanitized['default_size'] = andw_lightbox_sanitize_select( $input['default_size'], andw_lightbox_get_registered_size_choices() );
        } else {
            $sanitized['default_size'] = isset( $existing_options['default_size'] ) ? $existing_options['default_size'] : $defaults['default_size'];
        }

        if ( isset( $input['allow_full'] ) ) {
            $sanitized['allow_full'] = andw_lightbox_sanitize_checkbox( $input['allow_full'] );
        } else {
            $sanitized['allow_full'] = isset( $existing_options['allow_full'] ) ? $existing_options['allow_full'] : $defaults['allow_full'];
        }

        if ( isset( $input['infinite_scroll'] ) ) {
            $sanitized['infinite_scroll'] = andw_lightbox_sanitize_checkbox( $input['infinite_scroll'] );
        } else {
            $sanitized['infinite_scroll'] = isset( $existing_options['infinite_scroll'] ) ? $existing_options['infinite_scroll'] : $defaults['infinite_scroll'];
        }

        // アセット設定のサニタイズ
        $source_choices = array(
            'cdn'   => __( 'CDN', 'andw-lightbox' ),
            'local' => __( 'ローカル', 'andw-lightbox' ),
        );

        if ( isset( $input['glightbox_source'] ) ) {
            $sanitized['glightbox_source'] = andw_lightbox_sanitize_select( $input['glightbox_source'], $source_choices );
        } else {
            $sanitized['glightbox_source'] = isset( $existing_options['glightbox_source'] ) ? $existing_options['glightbox_source'] : $defaults['glightbox_source'];
        }

        if ( isset( $input['glightbox_version'] ) ) {
            $version = sanitize_text_field( $input['glightbox_version'] );
            $sanitized['glightbox_version'] = $version ? $version : $defaults['glightbox_version'];
        } else {
            $sanitized['glightbox_version'] = isset( $existing_options['glightbox_version'] ) ? $existing_options['glightbox_version'] : $defaults['glightbox_version'];
        }

        // デザイン設定のサニタイズ（POSTに含まれていない場合は既存値を保持）
        if ( isset( $input['design_show_title'] ) ) {
            $sanitized['design_show_title'] = andw_lightbox_sanitize_checkbox( $input['design_show_title'] );
        } else {
            $sanitized['design_show_title'] = isset( $existing_options['design_show_title'] ) ? $existing_options['design_show_title'] : $defaults['design_show_title'];
        }

        if ( isset( $input['design_show_description'] ) ) {
            $sanitized['design_show_description'] = andw_lightbox_sanitize_checkbox( $input['design_show_description'] );
        } else {
            $sanitized['design_show_description'] = isset( $existing_options['design_show_description'] ) ? $existing_options['design_show_description'] : $defaults['design_show_description'];
        }

        if ( isset( $input['design_max_width'] ) ) {
            $sanitized['design_max_width'] = sanitize_text_field( $input['design_max_width'] );
        } else {
            $sanitized['design_max_width'] = isset( $existing_options['design_max_width'] ) ? $existing_options['design_max_width'] : $defaults['design_max_width'];
        }

        if ( isset( $input['design_max_height'] ) ) {
            $sanitized['design_max_height'] = sanitize_text_field( $input['design_max_height'] );
        } else {
            $sanitized['design_max_height'] = isset( $existing_options['design_max_height'] ) ? $existing_options['design_max_height'] : $defaults['design_max_height'];
        }

        if ( isset( $input['design_overlay_color'] ) ) {
            $overlay_color = sanitize_hex_color( $input['design_overlay_color'] );
            $sanitized['design_overlay_color'] = $overlay_color ? $overlay_color : $defaults['design_overlay_color'];
        } else {
            $sanitized['design_overlay_color'] = isset( $existing_options['design_overlay_color'] ) ? $existing_options['design_overlay_color'] : $defaults['design_overlay_color'];
        }

        if ( isset( $input['design_overlay_opacity'] ) ) {
            $opacity = floatval( $input['design_overlay_opacity'] );
            $sanitized['design_overlay_opacity'] = max( 0, min( 1, $opacity ) );
        } else {
            $sanitized['design_overlay_opacity'] = isset( $existing_options['design_overlay_opacity'] ) ? $existing_options['design_overlay_opacity'] : $defaults['design_overlay_opacity'];
        }

        if ( isset( $input['design_mobile_navigation'] ) ) {
            $sanitized['design_mobile_navigation'] = andw_lightbox_sanitize_checkbox( $input['design_mobile_navigation'] );
        } else {
            $sanitized['design_mobile_navigation'] = isset( $existing_options['design_mobile_navigation'] ) ? $existing_options['design_mobile_navigation'] : $defaults['design_mobile_navigation'];
        }

        if ( isset( $input['design_custom_css'] ) ) {
            $css_content = sanitize_textarea_field( $input['design_custom_css'] );

            // Restore half-width comment markers that were previously widened for WAF avoidance
            $css_content = str_replace(
                array( "/\xEF\xBC\x8A", "\xEF\xBC\x8A/" ),
                array( '/*', '*/' ),
                $css_content
            );

            // Check if the content exactly matches the placeholder template
            $normalized_input = $this->normalize_css_content( $css_content );
            $normalized_placeholder = $this->normalize_css_content( self::PLACEHOLDER_CSS );

            if ( $normalized_input === $normalized_placeholder ) {
                $css_content = '';
            }

            $sanitized['design_custom_css'] = $css_content;
        } else {
            $sanitized['design_custom_css'] = isset( $existing_options['design_custom_css'] ) ? $existing_options['design_custom_css'] : $defaults['design_custom_css'];
        }


        $this->options = wp_parse_args( $sanitized, $defaults );

        return $this->options;
    }

    /**
     * Normalize CSS content for comparison.
     * Removes excess whitespace, normalizes line endings, and trims.
     *
     * @param string $css_content CSS content to normalize.
     * @return string Normalized CSS content.
     */
    private function normalize_css_content( $css_content ) {
        // Convert different line endings to \n
        $normalized = str_replace( array( "\r\n", "\r" ), "\n", $css_content );

        // Remove leading/trailing whitespace
        $normalized = trim( $normalized );

        // Split into lines, normalize each line, then rejoin
        $lines = explode( "\n", $normalized );
        $normalized_lines = array();

        foreach ( $lines as $line ) {
            // Trim each line and normalize spaces/tabs
            $trimmed_line = trim( $line );
            if ( ! empty( $trimmed_line ) ) {
                // Normalize multiple consecutive spaces/tabs to single space
                $normalized_line = preg_replace( '/[ \t]+/', ' ', $trimmed_line );
                $normalized_lines[] = $normalized_line;
            }
        }

        return implode( "\n", $normalized_lines );
    }

    /**
     * Get full options array.
     */
    public function all() {
        return $this->options;
    }

    /**
     * Fetch single option by key.
     *
     * @param string $key Option key.
     */
    public function get( $key ) {
        if ( isset( $this->options[ $key ] ) ) {
            return $this->options[ $key ];
        }

        $defaults = $this->get_defaults();

        return isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;
    }

    /**
     * Determine if option equals truthy value.
     *
     * @param string $key Option key.
     */
    public function is_enabled_flag( $key ) {
        return '1' === $this->get( $key );
    }

    /**
     * Option name accessor.
     */
    public function get_option_name() {
        return $this->option_name;
    }

    /**
     * Reload cached options from storage.
     */
    public function refresh() {
        $this->options = wp_parse_args( get_option( $this->option_name, array() ), $this->get_defaults() );
    }
}
