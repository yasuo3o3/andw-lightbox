<?php
/**
 * Attachment meta interface for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Media_Meta {
    const META_KEY = '_andw_lightbox_meta';

    /** @var Andw_Lightbox_Settings */
    private $settings;

    public function __construct( Andw_Lightbox_Settings $settings ) {
        $this->settings = $settings;

        add_action( 'init', array( $this, 'register_meta' ) );
        add_filter( 'attachment_fields_to_edit', array( $this, 'add_fields' ), 10, 2 );
        add_filter( 'attachment_fields_to_save', array( $this, 'save_fields' ), 10, 2 );
    }

    public function register_meta() {
        register_post_meta(
            'attachment',
            self::META_KEY,
            array(
                'type'              => 'array',
                'single'            => true,
                'sanitize_callback' => array( $this, 'sanitize_meta' ),
                'auth_callback'     => function () {
                    return current_user_can( 'upload_files' );
                },
                'show_in_rest'      => true,
            )
        );
    }

    private function get_defaults() {
        return array(
            'enabled'        => $this->settings->is_enabled_flag( 'enabled' ) ? '1' : '0',
            'slide'          => $this->settings->is_enabled_flag( 'default_slide' ) ? '1' : '0',
            'gallery'        => $this->settings->get( 'default_gallery' ),
            'animation'      => $this->settings->get( 'default_animation' ),
            'hover'          => $this->settings->get( 'default_hover' ),
            'hover_strength' => (string) intval( $this->settings->get( 'default_hover_strength' ) ),
            'size'           => $this->settings->get( 'default_size' ),
            'title'          => '',
            'description'    => '',
        );
    }

    public function get_meta( $attachment_id ) {
        $meta = get_post_meta( $attachment_id, self::META_KEY, true );
        return is_array( $meta ) ? $meta : array();
    }

    public function get_attachment_settings( $attachment_id ) {
        $defaults = $this->get_defaults();
        $meta     = wp_parse_args( $this->get_meta( $attachment_id ), $defaults );

        return array(
            'enabled'        => '1' === andw_lightbox_array_get( $meta, 'enabled', $defaults['enabled'] ),
            'slide'          => '1' === andw_lightbox_array_get( $meta, 'slide', $defaults['slide'] ),
            'gallery'        => sanitize_key( andw_lightbox_array_get( $meta, 'gallery', $defaults['gallery'] ) ),
            'animation'      => sanitize_key( andw_lightbox_array_get( $meta, 'animation', $defaults['animation'] ) ),
            'hover'          => sanitize_key( andw_lightbox_array_get( $meta, 'hover', $defaults['hover'] ) ),
            'hover_strength' => intval( andw_lightbox_array_get( $meta, 'hover_strength', $defaults['hover_strength'] ) ),
            'size'           => sanitize_key( andw_lightbox_array_get( $meta, 'size', $defaults['size'] ) ),
            'title'          => andw_lightbox_array_get( $meta, 'title', '' ),
            'description'    => andw_lightbox_array_get( $meta, 'description', '' ),
        );
    }

    public function add_fields( $form_fields, $post ) {
        if ( 'image' !== substr( $post->post_mime_type, 0, 5 ) ) {
            return $form_fields;
        }

        $defaults = $this->get_defaults();
        $meta     = wp_parse_args( $this->get_meta( $post->ID ), $defaults );

        $form_fields['andw_lightbox_enabled'] = array(
            'label' => __( 'ライトボックスを使う', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => sprintf(
                '<label><input type="checkbox" name="attachments[%1$d][andw_lightbox_enabled]" value="1" %2$s> %3$s</label>',
                absint( $post->ID ),
                checked( '1', andw_lightbox_array_get( $meta, 'enabled', $defaults['enabled'] ), false ),
                esc_html__( 'フロントでライトボックス表示', 'andw-lightbox' )
            ),
        );

        $form_fields['andw_lightbox_slide'] = array(
            'label' => __( 'スライド表示', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => sprintf(
                '<label><input type="checkbox" name="attachments[%1$d][andw_lightbox_slide]" value="1" %2$s> %3$s</label>',
                absint( $post->ID ),
                checked( '1', andw_lightbox_array_get( $meta, 'slide', $defaults['slide'] ), false ),
                esc_html__( '同じギャラリー内での移動を許可', 'andw-lightbox' )
            ),
        );

        $form_fields['andw_lightbox_gallery'] = array(
            'label' => __( 'ギャラリー選択', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => $this->render_select( $post->ID, 'andw_lightbox_gallery', $meta['gallery'], andw_lightbox_get_gallery_options() ),
        );

        $form_fields['andw_lightbox_animation'] = array(
            'label' => __( 'スライドアニメーション', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => $this->render_select( $post->ID, 'andw_lightbox_animation', $meta['animation'], $this->get_animation_choices() ),
        );

        $form_fields['andw_lightbox_hover'] = array(
            'label' => __( 'ホバー効果', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => $this->render_select( $post->ID, 'andw_lightbox_hover', $meta['hover'], andw_lightbox_get_hover_options() ),
        );

        $form_fields['andw_lightbox_hover_strength'] = array(
            'label' => __( 'ホバー強度', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => sprintf(
                '<input type="number" min="0" max="100" step="1" name="attachments[%1$d][andw_lightbox_hover_strength]" value="%2$d" class="small-text">',
                absint( $post->ID ),
                intval( $meta['hover_strength'] )
            ),
        );

        $form_fields['andw_lightbox_size'] = array(
            'label' => __( '拡大画像サイズ', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => $this->render_select( $post->ID, 'andw_lightbox_size', $meta['size'], andw_lightbox_get_registered_size_choices() ),
        );

        $form_fields['andw_lightbox_title'] = array(
            'label' => __( 'タイトル', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => sprintf(
                '<input type="text" class="widefat" name="attachments[%1$d][andw_lightbox_title]" value="%2$s">',
                absint( $post->ID ),
                esc_attr( $meta['title'] )
            ),
        );

        $form_fields['andw_lightbox_description'] = array(
            'label' => __( '説明文', 'andw-lightbox' ),
            'input' => 'html',
            'html'  => sprintf(
                '<textarea class="widefat" name="attachments[%1$d][andw_lightbox_description]" rows="4">%2$s</textarea>',
                absint( $post->ID ),
                esc_textarea( $meta['description'] )
            ),
        );

        return $form_fields;
    }

    public function save_fields( $post, $attachment ) {
        if ( ! current_user_can( 'upload_files' ) || ! wp_attachment_is_image( $post['ID'] ) ) {
            return $post;
        }

        $sanitized = $this->sanitize_payload( $attachment );
        update_post_meta( $post['ID'], self::META_KEY, $sanitized );

        return $post;
    }

    public function sanitize_meta( $meta ) {
        if ( ! is_array( $meta ) ) {
            return array();
        }

        return $this->sanitize_payload( $meta );
    }

    private function sanitize_payload( $payload ) {
        $animation_choices = $this->get_animation_choices();
        $size_choices      = andw_lightbox_get_registered_size_choices();
        $gallery_choices   = andw_lightbox_get_gallery_options();
        $hover_choices     = andw_lightbox_get_hover_options();

        return array(
            'enabled'        => andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $payload, 'andw_lightbox_enabled', andw_lightbox_array_get( $payload, 'enabled', '0' ) ) ),
            'slide'          => andw_lightbox_sanitize_checkbox( andw_lightbox_array_get( $payload, 'andw_lightbox_slide', andw_lightbox_array_get( $payload, 'slide', '0' ) ) ),
            'gallery'        => andw_lightbox_sanitize_select( andw_lightbox_array_get( $payload, 'andw_lightbox_gallery', andw_lightbox_array_get( $payload, 'gallery', $this->settings->get( 'default_gallery' ) ) ), $gallery_choices ),
            'animation'      => andw_lightbox_sanitize_select( andw_lightbox_array_get( $payload, 'andw_lightbox_animation', andw_lightbox_array_get( $payload, 'animation', $this->settings->get( 'default_animation' ) ) ), $animation_choices ),
            'hover'          => andw_lightbox_sanitize_select( andw_lightbox_array_get( $payload, 'andw_lightbox_hover', andw_lightbox_array_get( $payload, 'hover', $this->settings->get( 'default_hover' ) ) ), $hover_choices ),
            'hover_strength' => andw_lightbox_sanitize_int_range( andw_lightbox_array_get( $payload, 'andw_lightbox_hover_strength', andw_lightbox_array_get( $payload, 'hover_strength', $this->settings->get( 'default_hover_strength' ) ) ), 0, 100 ),
            'size'           => andw_lightbox_sanitize_select( andw_lightbox_array_get( $payload, 'andw_lightbox_size', andw_lightbox_array_get( $payload, 'size', $this->settings->get( 'default_size' ) ) ), $size_choices ),
            'title'          => sanitize_text_field( andw_lightbox_array_get( $payload, 'andw_lightbox_title', andw_lightbox_array_get( $payload, 'title', '' ) ) ),
            'description'    => sanitize_textarea_field( andw_lightbox_array_get( $payload, 'andw_lightbox_description', andw_lightbox_array_get( $payload, 'description', '' ) ) ),
        );
    }

    private function get_animation_choices() {
        $choices = andw_lightbox_get_animation_options();
        unset( $choices['default'] );
        return $choices;
    }

    private function render_select( $attachment_id, $field, $current, $choices ) {
        $name = sprintf( 'attachments[%d][%s]', absint( $attachment_id ), sanitize_key( $field ) );
        $html = '<select name="' . esc_attr( $name ) . '">';

        foreach ( $choices as $value => $label ) {
            $html .= sprintf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $value ),
                selected( $value, $current, false ),
                esc_html( $label )
            );
        }

        $html .= '</select>';

        return $html;
    }
}
