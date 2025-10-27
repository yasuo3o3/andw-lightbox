<?php
/**
 * Frontend rendering filters for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Frontend {
    /** @var Andw_Lightbox_Settings */
    private $settings;

    /** @var Andw_Lightbox_Assets */
    private $assets;

    /**
     * Supported block names.
     *
     * @var array
     */
    private $supported_blocks = array(
        'core/image',
        'core/gallery',
        'core/media-text',
    );

    public function __construct( Andw_Lightbox_Settings $settings, Andw_Lightbox_Assets $assets ) {
        $this->settings = $settings;
        $this->assets   = $assets;

        add_filter( 'render_block', array( $this, 'filter_block' ), 20, 2 );
        add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
        add_filter( 'post_thumbnail_html', array( $this, 'filter_thumbnail' ), 20, 5 );
    }

    public function filter_block( $block_content, $block ) {
        if ( empty( $block_content ) || ! is_array( $block ) ) {
            return $block_content;
        }

        $block_name = andw_lightbox_array_get( $block, 'blockName', '' );
        if ( ! in_array( $block_name, $this->supported_blocks, true ) ) {
            return $block_content;
        }

        if ( ! $this->should_process() ) {
            return $block_content;
        }

        $attrs    = andw_lightbox_array_get( $block, 'attrs', array() );
        $settings = $this->normalize_block_settings( $attrs );

        if ( ! $settings['enabled'] ) {
            return $block_content;
        }

        return $this->process_html( $block_content, get_the_ID(), $settings );
    }

    public function filter_content( $content ) {
        if ( ! $this->should_process() || empty( $content ) ) {
            return $content;
        }

        $settings = $this->normalize_block_settings( array() );
        if ( ! $settings['enabled'] ) {
            return $content;
        }

        return $this->process_html( $content, get_the_ID(), $settings );
    }

    public function filter_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        if ( empty( $html ) || ! $this->should_process() ) {
            return $html;
        }

        if ( false !== strpos( $html, '<a ' ) ) {
            return $html;
        }

        $settings = $this->normalize_block_settings( array() );
        if ( ! $settings['enabled'] ) {
            return $html;
        }

        return $this->process_html( $html, $post_id, $settings );
    }

    private function should_process() {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return false;
        }

        if ( wp_is_json_request() ) {
            return false;
        }

        return true;
    }

    /**
     * Merge block attributes with global defaults.
     *
     * @param array $attrs Block attributes.
     * @return array
     */
    private function normalize_block_settings( $attrs ) {
        $defaults = array(
            'enabled'        => $this->settings->is_enabled_flag( 'enabled' ),
            'slide'          => $this->settings->is_enabled_flag( 'default_slide' ),
            'gallery'        => sanitize_key( $this->settings->get( 'default_gallery' ) ),
            'animation'      => sanitize_key( $this->settings->get( 'default_animation' ) ),
            'hover'          => sanitize_key( $this->settings->get( 'default_hover' ) ),
            'hover_strength' => intval( $this->settings->get( 'default_hover_strength' ) ),
            'transform'      => sanitize_key( $this->settings->get( 'default_transform' ) ),
            'transform_strength' => intval( $this->settings->get( 'default_transform_strength' ) ),
            'size'           => sanitize_key( $this->settings->get( 'default_size' ) ),
            'title'          => '',
            'description'    => '',
            'allow_full'     => $this->settings->is_enabled_flag( 'allow_full' ),
        );

        $animation = isset( $attrs['andwLightboxAnimation'] ) ? sanitize_key( $attrs['andwLightboxAnimation'] ) : $defaults['animation'];
        if ( 'default' === $animation ) {
            $animation = 'slide';
        }
        $animation_choices = array( 'none', 'fade', 'zoom', 'slide' );
        if ( ! in_array( $animation, $animation_choices, true ) ) {
            $animation = 'slide';
        }

        $hover = isset( $attrs['andwLightboxHover'] ) ? sanitize_key( $attrs['andwLightboxHover'] ) : $defaults['hover'];
        $hover_choices = array( 'none', 'darken', 'lighten', 'transparent' );
        if ( ! in_array( $hover, $hover_choices, true ) ) {
            $hover = 'none';
        }

        $gallery = isset( $attrs['andwLightboxGallery'] ) ? sanitize_key( $attrs['andwLightboxGallery'] ) : $defaults['gallery'];
        $gallery_options = array_keys( andw_lightbox_get_gallery_options() );
        if ( ! in_array( $gallery, $gallery_options, true ) ) {
            $gallery = 'single';
        }

        $size = isset( $attrs['andwLightboxSize'] ) ? sanitize_key( $attrs['andwLightboxSize'] ) : $defaults['size'];
        $size_options = array_keys( andw_lightbox_get_registered_size_choices() );
        if ( ! in_array( $size, $size_options, true ) ) {
            $size = 'default';
        }

        $transform = isset( $attrs['andwLightboxTransform'] ) ? sanitize_key( $attrs['andwLightboxTransform'] ) : $defaults['transform'];
        $transform_choices = array( 'none', 'slide', 'zoom' );
        if ( ! in_array( $transform, $transform_choices, true ) ) {
            $transform = 'none';
        }

        $hover_strength = isset( $attrs['andwLightboxHoverStrength'] ) ? intval( $attrs['andwLightboxHoverStrength'] ) : $defaults['hover_strength'];
        if ( $hover_strength < 0 ) {
            $hover_strength = 0;
        }
        if ( $hover_strength > 100 ) {
            $hover_strength = 100;
        }

        $transform_strength = isset( $attrs['andwLightboxTransformStrength'] ) ? intval( $attrs['andwLightboxTransformStrength'] ) : $defaults['transform_strength'];
        if ( $transform_strength < 0 ) {
            $transform_strength = 0;
        }
        if ( $transform_strength > 100 ) {
            $transform_strength = 100;
        }

        return array(
            'enabled'        => isset( $attrs['andwLightboxEnabled'] ) ? (bool) $attrs['andwLightboxEnabled'] : (bool) $defaults['enabled'],
            'slide'          => isset( $attrs['andwLightboxSlide'] ) ? (bool) $attrs['andwLightboxSlide'] : (bool) $defaults['slide'],
            'gallery'        => $gallery,
            'animation'      => $animation,
            'hover'          => $hover,
            'hover_strength' => $hover_strength,
            'transform'      => $transform,
            'transform_strength' => $transform_strength,
            'size'           => $size,
            'title'          => isset( $attrs['andwLightboxTitle'] ) ? sanitize_text_field( $attrs['andwLightboxTitle'] ) : $defaults['title'],
            'description'    => isset( $attrs['andwLightboxDescription'] ) ? sanitize_textarea_field( $attrs['andwLightboxDescription'] ) : $defaults['description'],
            'allow_full'     => (bool) $defaults['allow_full'],
        );
    }

    /**
     * Apply lightbox markup to HTML.
     */
    private function process_html( $html, $post_id, $settings ) {
        if ( false === strpos( $html, '<img' ) ) {
            return $html;
        }

        $post_id = absint( $post_id );
        $updated = false;

        $dom = new DOMDocument( '1.0', 'UTF-8' );
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();

        $images = $dom->getElementsByTagName( 'img' );

        for ( $i = $images->length - 1; $i >= 0; $i-- ) {
            $img = $images->item( $i );

            if ( andw_lightbox_dom_has_parent_anchor( $img ) ) {
                continue;
            }

            $attachment_id = andw_lightbox_extract_attachment_id_from_img( $img );
            if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
                continue;
            }

            $href = andw_lightbox_select_target_image_src( $attachment_id, $settings['size'], $settings['allow_full'] );
            if ( ! $href ) {
                continue;
            }

            $anchor = $dom->createElement( 'a' );
            $anchor->setAttribute( 'href', esc_url_raw( $href ) );
            $anchor->setAttribute( 'class', 'andw-lightbox-link glightbox' );
            $anchor->setAttribute( 'data-andw-lightbox', '1' );

            if ( $settings['slide'] ) {
                $anchor->setAttribute( 'data-gallery', andw_lightbox_build_gallery_id( $post_id, $settings['gallery'] ) );
            }

            if ( $settings['animation'] ) {
                $anchor->setAttribute( 'data-andw-animation', $settings['animation'] );
            }

            $style_parts = array();

            if ( 'none' !== $settings['hover'] ) {
                $anchor->setAttribute( 'data-andw-hover', $settings['hover'] );
                $style_parts[] = '--andw-hover-strength:' . andw_lightbox_strength_to_css_value( $settings['hover_strength'] );
            }

            if ( 'none' !== $settings['transform'] ) {
                $anchor->setAttribute( 'data-andw-transform', $settings['transform'] );
                $style_parts[] = '--andw-transform-strength:' . andw_lightbox_strength_to_css_value( $settings['transform_strength'] );
            }

            if ( ! empty( $style_parts ) ) {
                $anchor->setAttribute( 'style', implode( ';', $style_parts ) . ';' );
            }

            $title = $settings['title'];
            if ( '' === $title ) {
                $title = get_the_title( $attachment_id );
            }

            $description = $settings['description'];
            if ( '' === $description ) {
                $description = wp_get_attachment_caption( $attachment_id );
            }

            $title_attr = sanitize_text_field( $title );
            if ( '' !== $title_attr ) {
                $anchor->setAttribute( 'data-title', $title_attr );
            }

            $description_attr = sanitize_textarea_field( $description );
            if ( '' !== $description_attr ) {
                $anchor->setAttribute( 'data-description', $description_attr );
            }

            $alt       = $img->hasAttribute( 'alt' ) ? $img->getAttribute( 'alt' ) : '';
            $aria_text = andw_lightbox_prepare_aria_label( $title_attr, $alt );
            if ( $aria_text ) {
                $anchor->setAttribute( 'aria-label', sanitize_text_field( $aria_text ) );
            }

            $img->setAttribute( 'class', trim( $img->getAttribute( 'class' ) . ' andw-lightbox-image' ) );

            $anchor->appendChild( $img->cloneNode( true ) );
            $img->parentNode->replaceChild( $anchor, $img );

            $updated = true;
        }

        if ( ! $updated ) {
            return $html;
        }

        $output = '';
        foreach ( $dom->documentElement->childNodes as $child ) {
            $output .= $dom->saveHTML( $child );
        }

        $this->assets->mark_front_needed();

        return $output;
    }
}
