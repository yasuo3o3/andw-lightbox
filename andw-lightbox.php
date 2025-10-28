<?php
/**
 * Plugin Name: andW Lightbox
 * Description: Accessible lightbox integration for WordPress media and core image blocks using GLightbox.
 * Version: 0.1.2
 * Author: Netservice
 * Author URI: https://netservice.jp/
 * License: GPLv2 or later
 * Text Domain: andw-lightbox
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'ANDW_LIGHTBOX_VERSION', '0.1.2' );
define( 'ANDW_LIGHTBOX_PLUGIN_FILE', __FILE__ );
define( 'ANDW_LIGHTBOX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANDW_LIGHTBOX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/helpers.php';
require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/class-andw-settings.php';
require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/class-andw-assets.php';
require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/class-andw-admin.php';
require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/class-andw-block-editor.php';
require_once ANDW_LIGHTBOX_PLUGIN_DIR . 'includes/class-andw-frontend.php';

if ( ! function_exists( 'andw_lightbox' ) ) {
    /**
     * Retrieve the andW Lightbox bootstrap instance.
     */
    function andw_lightbox() {
        static $instance = null;

        if ( null === $instance ) {
            $instance = new Andw_Lightbox_Plugin();
        }

        return $instance;
    }
}

add_action( 'plugins_loaded', 'andw_lightbox' );

/**
 * Main plugin orchestrator.
 */
final class Andw_Lightbox_Plugin {
    /** @var Andw_Lightbox_Settings */
    private $settings;

    /** @var Andw_Lightbox_Assets */
    private $assets;

    /** @var Andw_Lightbox_Admin */
    private $admin;

    /** @var Andw_Lightbox_Block_Editor */
    private $block_editor;

    /** @var Andw_Lightbox_Frontend */
    private $frontend;

    public function __construct() {
        $this->settings     = Andw_Lightbox_Settings::get_instance();
        $this->assets       = new Andw_Lightbox_Assets( $this->settings );
        $this->admin        = new Andw_Lightbox_Admin( $this->settings );
        $this->block_editor = new Andw_Lightbox_Block_Editor( $this->settings );
        $this->frontend     = new Andw_Lightbox_Frontend( $this->settings, $this->assets );
    }
}
