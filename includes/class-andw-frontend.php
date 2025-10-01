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

    /** @var Andw_Lightbox_Media_Meta */
    private $media_meta;

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

    public function __construct( Andw_Lightbox_Settings $settings, Andw_Lightbox_Assets $assets, Andw_Lightbox_Media_Meta $media_meta ) {
        $this->settings   = $settings;
        $this->assets     = $assets;
        $this->media_meta = $media_meta;

        add_filter( 'render_block', array( $this, 'filter_block' ), 20, 2 );
        add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
        add_filter( 'post_thumbnail_html', array( $this, 'filter_thumbnail' ), 20, 5 );
    }

    public function filter_block( $block_content, $block ) {
        if ( empty( $block_content ) || ! is_array( $block ) ) {
            return $block_content;
        }

        if ( ! in_array( andw_lightbox_array_get( $block, 'blockName', '' ), $this->supported_blocks, true ) ) {
            return $block_content;
        }

        if ( ! $this->should_process() ) {
            return $block_content;
        }

        return $this->process_html( $block_content, get_the_ID() );
    }

    public function filter_content( $content ) {
        if ( ! $this->should_process() || empty( $content ) ) {
            return $content;
        }

        return $this->process_html( $content, get_the_ID() );
    }

    public function filter_thumbnail( $html, $post_id, $thumbnail_id ) {
        if ( empty( $html ) || ! $this->should_process() ) {
            return $html;
        }

        return $this->process_html( $html, $post_id );
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

    private function process_html( $html, $post_id ) {
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

            $profile = $this->media_meta->get_attachment_settings( $attachment_id );

            if ( empty( $profile['enabled'] ) ) {
                continue;
            }

            $allow_full = $this->settings->is_enabled_flag( 'allow_full' );
            $href       = andw_lightbox_select_target_image_src( $attachment_id, $profile['size'], $allow_full );

            if ( ! $href ) {
                continue;
            }

            $anchor = $dom->createElement( 'a' );
            $anchor->setAttribute( 'href', esc_url( $href ) );
            $anchor->setAttribute( 'class', 'andw-lightbox-link glightbox' );
            $anchor->setAttribute( 'data-andw-lightbox', '1' );

            if ( ! empty( $profile['slide'] ) ) {
                $anchor->setAttribute( 'data-gallery', andw_lightbox_build_gallery_id( $post_id, $profile['gallery'] ) );
            }

            if ( ! empty( $profile['animation'] ) ) {
                $anchor->setAttribute( 'data-andw-animation', $profile['animation'] );
            }

            if ( ! empty( $profile['hover'] ) && 'none' !== $profile['hover'] ) {
                $anchor->setAttribute( 'data-andw-hover', $profile['hover'] );
                $anchor->setAttribute( 'style', '--andw-hover-strength:' . andw_lightbox_strength_to_css_value( $profile['hover_strength'] ) . ';' );
            }

            $title       = $profile['title'];
            $description = $profile['description'];

            if ( '' === $title ) {
                $title = get_the_title( $attachment_id );
            }

            if ( '' === $description ) {
                $description = wp_get_attachment_caption( $attachment_id );
            }

            if ( $title ) {
                $anchor->setAttribute( 'data-title', esc_attr( $title ) );
            }

            if ( $description ) {
                $anchor->setAttribute( 'data-description', esc_attr( $description ) );
            }

            $alt       = $img->hasAttribute( 'alt' ) ? $img->getAttribute( 'alt' ) : '';
            $aria_text = andw_lightbox_prepare_aria_label( $title, $alt );

            if ( $aria_text ) {
                $anchor->setAttribute( 'aria-label', esc_attr( $aria_text ) );
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
