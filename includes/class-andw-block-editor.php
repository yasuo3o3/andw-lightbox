<?php
/**
 * Block editor integration for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Block_Editor {
    /**
     * Target block names.
     *
     * @var string[]
     */
    private $targets = array(
        'core/image',
        'core/gallery',
        'core/media-text',
    );

    /**
     * Settings accessor.
     *
     * @var Andw_Lightbox_Settings
     */
    private $settings;

    /**
     * Constructor.
     */
    public function __construct( Andw_Lightbox_Settings $settings ) {
        $this->settings = $settings;

        add_filter( 'register_block_type_args', array( $this, 'register_block_attributes' ), 10, 2 );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Inject custom attributes into supported block types.
     */
    public function register_block_attributes( $args, $name ) {
        if ( ! in_array( $name, $this->targets, true ) ) {
            return $args;
        }

        $schema = $this->get_attribute_schema();
        $args['attributes'] = isset( $args['attributes'] ) ? array_merge( $args['attributes'], $schema ) : $schema;

        return $args;
    }

    /**
     * Enqueue editor assets.
     */
    public function enqueue_editor_assets() {
        $handle = 'andw-lightbox-editor';

        wp_register_script(
            $handle,
            ANDW_LIGHTBOX_PLUGIN_URL . 'assets/js/andw-lightbox-editor.js',
            array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-compose', 'wp-block-editor', 'wp-edit-post' ),
            ANDW_LIGHTBOX_VERSION,
            true
        );

        wp_localize_script( $handle, 'andwLightboxEditorData', $this->get_editor_data() );
        wp_enqueue_script( $handle );
    }

    /**
     * Build attribute schema for registration.
     *
     * @return array
     */
    private function get_attribute_schema() {
        $defaults = $this->get_attribute_defaults();

        return array(
            'andwLightboxEnabled'       => array(
                'type'    => 'boolean',
                'default' => $defaults['andwLightboxEnabled'],
            ),
            'andwLightboxSlide'         => array(
                'type'    => 'boolean',
                'default' => $defaults['andwLightboxSlide'],
            ),
            'andwLightboxGallery'       => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxGallery'],
            ),
            'andwLightboxAnimation'     => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxAnimation'],
            ),
            'andwLightboxHover'         => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxHover'],
            ),
            'andwLightboxHoverStrength' => array(
                'type'    => 'number',
                'default' => $defaults['andwLightboxHoverStrength'],
            ),
            'andwLightboxTransform'     => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxTransform'],
            ),
            'andwLightboxTransformStrength' => array(
                'type'    => 'number',
                'default' => $defaults['andwLightboxTransformStrength'],
            ),
            'andwLightboxSize'          => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxSize'],
            ),
            'andwLightboxTitle'         => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxTitle'],
            ),
            'andwLightboxDescription'   => array(
                'type'    => 'string',
                'default' => $defaults['andwLightboxDescription'],
            ),
        );
    }

    /**
     * Calculate attribute defaults from plugin settings.
     */
    private function get_attribute_defaults() {
        $animation = $this->settings->get( 'default_animation' );
        if ( 'default' === $animation ) {
            $animation = 'slide';
        }

        return array(
            'andwLightboxEnabled'       => $this->settings->is_enabled_flag( 'enabled' ),
            'andwLightboxSlide'         => $this->settings->is_enabled_flag( 'default_slide' ),
            'andwLightboxGallery'       => sanitize_key( $this->settings->get( 'default_gallery' ) ),
            'andwLightboxAnimation'     => sanitize_key( $animation ),
            'andwLightboxHover'         => sanitize_key( $this->settings->get( 'default_hover' ) ),
            'andwLightboxHoverStrength' => intval( $this->settings->get( 'default_hover_strength' ) ),
            'andwLightboxTransform'     => sanitize_key( $this->settings->get( 'default_transform' ) ),
            'andwLightboxTransformStrength' => intval( $this->settings->get( 'default_transform_strength' ) ),
            'andwLightboxSize'          => sanitize_key( $this->settings->get( 'default_size' ) ),
            'andwLightboxTitle'         => '',
            'andwLightboxDescription'   => '',
        );
    }

    /**
     * Prepare localized data for the editor script.
     */
    private function get_editor_data() {
        $defaults = $this->get_attribute_defaults();

        return array(
            'blocks'          => $this->targets,
            'defaults'        => $defaults,
            'options'         => array(
                'gallery'   => $this->format_choices( andw_lightbox_get_gallery_options() ),
                'animation' => array(
                    array( 'value' => 'zoom', 'label' => __( 'ズーム（ふわっと拡大）', 'andw-lightbox' ) ),
                    array( 'value' => 'fade', 'label' => __( 'フェード（フェードイン）', 'andw-lightbox' ) ),
                    array( 'value' => 'none', 'label' => __( 'なし（瞬間表示）', 'andw-lightbox' ) ),
                ),
                'hover'     => $this->format_choices( andw_lightbox_get_hover_options() ),
                'transform' => $this->format_choices( andw_lightbox_get_transform_options() ),
                'size'      => $this->format_choices( andw_lightbox_get_registered_size_choices() ),
            ),
            'labels'          => array(
                'panel'         => __( 'ライトボックス設定', 'andw-lightbox' ),
                'enabled'       => __( 'ライトボックスを使う', 'andw-lightbox' ),
                'slide'         => __( 'スライド表示', 'andw-lightbox' ),
                'gallery'       => __( 'ギャラリー選択', 'andw-lightbox' ),
                'animation'     => __( '開閉アニメーション', 'andw-lightbox' ),
                'hover'         => __( 'ホバー効果', 'andw-lightbox' ),
                'hoverStrength' => __( 'ホバー強度', 'andw-lightbox' ),
                'transform'     => __( 'トランスフォーム', 'andw-lightbox' ),
                'transformStrength' => __( 'トランスフォーム強度', 'andw-lightbox' ),
                'size'          => __( '拡大画像サイズ', 'andw-lightbox' ),
                'title'         => __( 'タイトル', 'andw-lightbox' ),
                'description'   => __( '説明文', 'andw-lightbox' ),
            ),
            'range'           => array(
                'hoverStrength' => array(
                    'min' => 0,
                    'max' => 100,
                ),
                'transformStrength' => array(
                    'min' => 0,
                    'max' => 100,
                ),
            ),
            'attributeSchema' => $this->get_attribute_schema(),
        );
    }

    /**
     * Transform associative choices into value/label pairs.
     *
     * @param array $choices Choices map.
     * @return array
     */
    private function format_choices( $choices ) {
        $formatted = array();

        foreach ( $choices as $value => $label ) {
            $formatted[] = array(
                'value' => sanitize_key( $value ),
                'label' => is_string( $label ) ? sanitize_text_field( $label ) : '',
            );
        }

        return $formatted;
    }
}
