<?php
/**
 * Utility helpers for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'andw_lightbox_array_get' ) ) {
    function andw_lightbox_array_get( $source, $key, $default = null ) {
        if ( is_array( $source ) && array_key_exists( $key, $source ) ) {
            return $source[ $key ];
        }

        return $default;
    }
}

if ( ! function_exists( 'andw_lightbox_sanitize_checkbox' ) ) {
    function andw_lightbox_sanitize_checkbox( $value ) {
        return ( ! empty( $value ) && '0' !== $value ) ? '1' : '0';
    }
}

if ( ! function_exists( 'andw_lightbox_sanitize_select' ) ) {
    function andw_lightbox_sanitize_select( $value, $choices ) {
        $value = sanitize_key( (string) $value );

        if ( isset( $choices[ $value ] ) ) {
            return $value;
        }

        return key( $choices );
    }
}

if ( ! function_exists( 'andw_lightbox_sanitize_int_range' ) ) {
    function andw_lightbox_sanitize_int_range( $value, $min, $max ) {
        $value = intval( $value );

        if ( $value < $min ) {
            $value = $min;
        }

        if ( $value > $max ) {
            $value = $max;
        }

        return (string) $value;
    }
}

if ( ! function_exists( 'andw_lightbox_strength_to_css_value' ) ) {
    function andw_lightbox_strength_to_css_value( $value ) {
        $int = intval( $value );

        if ( $int < 0 ) {
            $int = 0;
        }

        if ( $int > 100 ) {
            $int = 100;
        }

        return number_format( $int / 100, 2, '.', '' );
    }
}

if ( ! function_exists( 'andw_lightbox_get_gallery_options' ) ) {
    function andw_lightbox_get_gallery_options() {
        return array(
            'single'   => __( '単独表示', 'andw-lightbox' ),
            'gallery1' => __( 'ギャラリー1', 'andw-lightbox' ),
            'gallery2' => __( 'ギャラリー2', 'andw-lightbox' ),
            'gallery3' => __( 'ギャラリー3', 'andw-lightbox' ),
            'gallery4' => __( 'ギャラリー4', 'andw-lightbox' ),
            'gallery5' => __( 'ギャラリー5', 'andw-lightbox' ),
        );
    }
}

if ( ! function_exists( 'andw_lightbox_get_animation_options' ) ) {
    function andw_lightbox_get_animation_options() {
        return array(
            'zoom' => __( 'ズーム（ふわっと拡大）', 'andw-lightbox' ),
            'fade' => __( 'フェード（さっと表示）', 'andw-lightbox' ),
            'none' => __( 'なし（瞬間表示）', 'andw-lightbox' ),
        );
    }
}

if ( ! function_exists( 'andw_lightbox_get_hover_options' ) ) {
    function andw_lightbox_get_hover_options() {
        return array(
            'none'        => __( 'なし', 'andw-lightbox' ),
            'darken'      => __( '暗くする', 'andw-lightbox' ),
            'lighten'     => __( '明るくする', 'andw-lightbox' ),
            'transparent' => __( '透明にする', 'andw-lightbox' ),
        );
    }
}

if ( ! function_exists( 'andw_lightbox_get_transform_options' ) ) {
    function andw_lightbox_get_transform_options() {
        return array(
            'none'  => __( 'なし', 'andw-lightbox' ),
            'slide' => __( 'スライド', 'andw-lightbox' ),
            'zoom'  => __( 'ズーム', 'andw-lightbox' ),
        );
    }
}

if ( ! function_exists( 'andw_lightbox_build_gallery_id' ) ) {
    function andw_lightbox_build_gallery_id( $post_id, $gallery_key ) {
        $post_id = absint( $post_id );

        if ( 'single' === $gallery_key ) {
            return 'p' . ( $post_id ? $post_id : '0' );
        }

        $gallery_key = preg_replace( '/[^0-9]/', '', $gallery_key );
        $gallery_key = $gallery_key ? intval( $gallery_key ) : 1;

        return 'p' . ( $post_id ? $post_id : '0' ) . '-g' . $gallery_key;
    }
}

if ( ! function_exists( 'andw_lightbox_extract_attachment_id_from_img' ) ) {
    function andw_lightbox_extract_attachment_id_from_img( $img ) {
        if ( ! $img instanceof DOMElement ) {
            return 0;
        }

        foreach ( array( 'data-id', 'data-attachment-id', 'data-wp-id' ) as $attr ) {
            if ( $img->hasAttribute( $attr ) ) {
                $value = absint( $img->getAttribute( $attr ) );
                if ( $value > 0 ) {
                    return $value;
                }
            }
        }

        if ( $img->hasAttribute( 'class' ) ) {
            if ( preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $matches ) ) {
                return absint( $matches[1] );
            }
        }

        if ( $img->hasAttribute( 'src' ) ) {
            $attachment_id = attachment_url_to_postid( $img->getAttribute( 'src' ) );
            if ( $attachment_id ) {
                return absint( $attachment_id );
            }
        }

        return 0;
    }
}

if ( ! function_exists( 'andw_lightbox_is_image_url' ) ) {
    function andw_lightbox_is_image_url( $url ) {
        if ( empty( $url ) ) {
            return false;
        }

        $image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico' );
        $parsed_url = wp_parse_url( $url );

        if ( ! isset( $parsed_url['path'] ) ) {
            return false;
        }

        $path_info = pathinfo( $parsed_url['path'] );
        $extension = isset( $path_info['extension'] ) ? strtolower( $path_info['extension'] ) : '';

        return in_array( $extension, $image_extensions, true );
    }
}

if ( ! function_exists( 'andw_lightbox_dom_has_parent_anchor' ) ) {
    function andw_lightbox_dom_has_parent_anchor( DOMElement $img ) {
        $parent = $img->parentNode;

        while ( $parent instanceof DOMElement ) {
            if ( 'a' === strtolower( $parent->nodeName ) ) {
                if ( $parent->hasAttribute( 'class' ) && false !== strpos( $parent->getAttribute( 'class' ), 'glightbox' ) ) {
                    return true;
                }

                if ( $parent->hasAttribute( 'data-andw-lightbox' ) ) {
                    return true;
                }

                if ( $parent->hasAttribute( 'href' ) ) {
                    $href = $parent->getAttribute( 'href' );

                    if ( ! andw_lightbox_is_image_url( $href ) ) {
                        return true;
                    }
                }
            }

            $parent = $parent->parentNode;
        }

        return false;
    }
}

if ( ! function_exists( 'andw_lightbox_get_registered_size_choices' ) ) {
    function andw_lightbox_get_registered_size_choices() {
        $sizes   = wp_get_registered_image_subsizes();
        $choices = array( 'default' => __( 'デフォルト（最大）', 'andw-lightbox' ) );

        foreach ( $sizes as $name => $data ) {
            $label = $name;

            if ( isset( $data['width'], $data['height'] ) ) {
                $label = sprintf( '%1$s (%2$dx%3$d)', $name, $data['width'], $data['height'] );
            }

            $choices[ sanitize_key( $name ) ] = $label;
        }

        return $choices;
    }
}

if ( ! function_exists( 'andw_lightbox_select_target_image_src' ) ) {
    function andw_lightbox_select_target_image_src( $attachment_id, $mode, $allow_full ) {
        $attachment_id = absint( $attachment_id );

        if ( ! $attachment_id ) {
            return '';
        }

        $metadata = wp_get_attachment_metadata( $attachment_id );
        if ( empty( $metadata ) || empty( $metadata['file'] ) ) {
            $image = wp_get_attachment_image_src( $attachment_id, 'full' );
            return $image ? esc_url_raw( $image[0] ) : '';
        }

        $sizes    = isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ? $metadata['sizes'] : array();
        $uploads  = wp_get_upload_dir();
        $base_url = trailingslashit( dirname( $uploads['baseurl'] . '/' . $metadata['file'] ) );

        if ( 'default' === $mode ) {
            $candidate = '';
            $max_area  = 0;

            foreach ( $sizes as $size ) {
                if ( empty( $size['file'] ) ) {
                    continue;
                }

                $area = intval( andw_lightbox_array_get( $size, 'width', 0 ) ) * intval( andw_lightbox_array_get( $size, 'height', 0 ) );
                if ( $area > $max_area ) {
                    $max_area  = $area;
                    $candidate = $size['file'];
                }
            }

            if ( $candidate ) {
                return esc_url_raw( $base_url . $candidate );
            }

            if ( $allow_full ) {
                $image = wp_get_attachment_image_src( $attachment_id, 'full' );
                return $image ? esc_url_raw( $image[0] ) : '';
            }

            return '';
        }

        $mode         = sanitize_key( $mode );
        $registered   = wp_get_registered_image_subsizes();
        $target_width = isset( $registered[ $mode ]['width'] ) ? intval( $registered[ $mode ]['width'] ) : 0;

        $under = array();
        $over  = array();

        foreach ( $sizes as $size ) {
            if ( empty( $size['file'] ) ) {
                continue;
            }

            $width = intval( andw_lightbox_array_get( $size, 'width', 0 ) );

            if ( $target_width && $width <= $target_width ) {
                $under[ $width ] = $size['file'];
                continue;
            }

            $over[ $width ] = $size['file'];
        }

        if ( $under ) {
            krsort( $under );
            return esc_url_raw( $base_url . reset( $under ) );
        }

        if ( $over ) {
            ksort( $over );
            return esc_url_raw( $base_url . reset( $over ) );
        }

        if ( $allow_full ) {
            $image = wp_get_attachment_image_src( $attachment_id, 'full' );
            return $image ? esc_url_raw( $image[0] ) : '';
        }

        if ( $sizes ) {
            $first = reset( $sizes );
            if ( isset( $first['file'] ) ) {
                return esc_url_raw( $base_url . $first['file'] );
            }
        }

        return '';
    }
}

if ( ! function_exists( 'andw_lightbox_prepare_aria_label' ) ) {
    function andw_lightbox_prepare_aria_label( $title, $alt ) {
        $parts = array();

        if ( $title ) {
            $parts[] = $title;
        }

        if ( $alt && ! in_array( $alt, $parts, true ) ) {
            $parts[] = $alt;
        }

        if ( ! $parts ) {
            return '';
        }

        return implode( ' / ', $parts );
    }
}
