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

    public function __construct( Andw_Lightbox_Settings $settings ) {
        $this->settings = $settings;

        add_action( 'wp_enqueue_scripts', array( $this, 'register_front_assets' ), 5 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ), 20 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Ensure assets are loaded for current request.
     */
    public function mark_front_needed() {
        $this->front_required = true;
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

            $fallback = 'window.addEventListener("load",function(){if("function"!==typeof window.GLightbox){var s=document.createElement("script");s.src="' . esc_js( $local_js ) . '";document.head.appendChild(s);var l=document.createElement("link");l.rel="stylesheet";l.href="' . esc_js( $local_css ) . '";document.head.appendChild(l);}});';
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
}
