<?php
/**
 * Settings handler for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Settings {
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
            'default_animation'      => 'slide',
            'default_size'           => 'default',
            'allow_full'             => '0',
            'infinite_scroll'        => '0',
            'glightbox_source'       => 'cdn',
            'glightbox_version'      => '3.3.0',
            // デザイン設定
            'design_show_title'      => '1',
            'design_show_description' => '1',
            'design_max_width'       => '',
            'design_max_height'      => '',
            'design_overlay_color'   => '#000000',
            'design_overlay_opacity' => '0.92',
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
        $defaults  = $this->get_defaults();
        $sanitized = array();

        $sanitized['enabled']         = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'enabled', $defaults['enabled'] ) );
        $sanitized['default_slide']   = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'default_slide', $defaults['default_slide'] ) );
        $sanitized['default_gallery'] = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'default_gallery', $defaults['default_gallery'] ), andw_lightbox_get_gallery_options() );
        $sanitized['default_hover']   = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'default_hover', $defaults['default_hover'] ), andw_lightbox_get_hover_options() );
        $sanitized['default_hover_strength'] = andw_lightbox_sanitize_int_range( andw_lightbox_array_get( $input, 'default_hover_strength', $defaults['default_hover_strength'] ), 0, 100 );
        $sanitized['default_transform']      = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'default_transform', $defaults['default_transform'] ), andw_lightbox_get_transform_options() );
        $sanitized['default_transform_strength'] = andw_lightbox_sanitize_int_range( andw_lightbox_array_get( $input, 'default_transform_strength', $defaults['default_transform_strength'] ), 0, 100 );
        $sanitized['default_animation']      = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'default_animation', $defaults['default_animation'] ), andw_lightbox_get_animation_options() );
        $sanitized['default_size']           = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'default_size', $defaults['default_size'] ), andw_lightbox_get_registered_size_choices() );
        $sanitized['allow_full']             = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'allow_full', $defaults['allow_full'] ) );
        $sanitized['infinite_scroll']        = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'infinite_scroll', $defaults['infinite_scroll'] ) );

        $source_choices = array(
            'cdn'   => __( 'CDN', 'andw-lightbox' ),
            'local' => __( 'ローカル', 'andw-lightbox' ),
        );

        $sanitized['glightbox_source'] = andw_lightbox_sanitize_select( andw_lightbox_array_get( $input, 'glightbox_source', $defaults['glightbox_source'] ), $source_choices );

        $version = sanitize_text_field( andw_lightbox_array_get( $input, 'glightbox_version', $defaults['glightbox_version'] ) );
        $sanitized['glightbox_version'] = $version ? $version : $defaults['glightbox_version'];

        // デザイン設定のサニタイズ
        $sanitized['design_show_title'] = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'design_show_title', $defaults['design_show_title'] ) );
        $sanitized['design_show_description'] = andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $input, 'design_show_description', $defaults['design_show_description'] ) );

        $sanitized['design_max_width'] = sanitize_text_field( andw_lightbox_array_get( $input, 'design_max_width', $defaults['design_max_width'] ) );
        $sanitized['design_max_height'] = sanitize_text_field( andw_lightbox_array_get( $input, 'design_max_height', $defaults['design_max_height'] ) );

        $overlay_color = sanitize_hex_color( andw_lightbox_array_get( $input, 'design_overlay_color', $defaults['design_overlay_color'] ) );
        $sanitized['design_overlay_color'] = $overlay_color ? $overlay_color : $defaults['design_overlay_color'];

        $opacity = floatval( andw_lightbox_array_get( $input, 'design_overlay_opacity', $defaults['design_overlay_opacity'] ) );
        $sanitized['design_overlay_opacity'] = max( 0, min( 1, $opacity ) );

        $custom_css = andw_lightbox_array_get( $input, 'design_custom_css', $defaults['design_custom_css'] );
        $sanitized['design_custom_css'] = sanitize_textarea_field( $custom_css );

        $this->options = wp_parse_args( $sanitized, $defaults );

        return $this->options;
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
