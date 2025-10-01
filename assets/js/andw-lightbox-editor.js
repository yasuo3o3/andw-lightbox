(function ( wp, data ) {
    'use strict';

    if ( ! wp || !wp.hooks || !wp.element || !wp.compose || !wp.components ) {
        return;
    }

    data = data || {};

    var allowedBlocks = Array.isArray( data.blocks ) ? data.blocks : [];
    var defaults = data.defaults || {};
    var options = data.options || {};
    var labels = data.labels || {};
    var ranges = data.range || {};
    var attributeSchema = data.attributeSchema || {};

    var addFilter = wp.hooks.addFilter;
    var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
    var Fragment = wp.element.Fragment;
    var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : ( wp.editor && wp.editor.InspectorControls );
    var PanelBody = wp.components.PanelBody;
    var ToggleControl = wp.components.ToggleControl;
    var SelectControl = wp.components.SelectControl;
    var RangeControl = wp.components.RangeControl;
    var TextControl = wp.components.TextControl;
    var TextareaControl = wp.components.TextareaControl;

    var galleryOptions = Array.isArray( options.gallery ) ? options.gallery : [];
    var animationOptions = Array.isArray( options.animation ) ? options.animation : [];
    var hoverOptions = Array.isArray( options.hover ) ? options.hover : [];
    var sizeOptions = Array.isArray( options.size ) ? options.size : [];

    var hoverRange = ranges.hoverStrength || { min: 0, max: 100 };

    if ( addFilter && wp.blocks ) {
        addFilter( 'blocks.registerBlockType', 'andw-lightbox/attributes', function ( settings, name ) {
            if ( allowedBlocks.indexOf( name ) === -1 ) {
                return settings;
            }

            var attrs = Object.assign( {}, attributeSchema );
            settings.attributes = Object.assign( {}, settings.attributes || {}, attrs );

            return settings;
        } );
    }

    if ( ! InspectorControls ) {
        return;
    }

    var getAttributeValue = function ( attributes, key ) {
        if ( Object.prototype.hasOwnProperty.call( attributes, key ) ) {
            return attributes[ key ];
        }
        return defaults[ key ];
    };

    var sanitizeBoolean = function ( value, fallback ) {
        if ( typeof value === 'boolean' ) {
            return value;
        }
        if ( typeof value === 'string' ) {
            if ( value === 'true' ) {
                return true;
            }
            if ( value === 'false' ) {
                return false;
            }
        }
        if ( typeof value === 'number' ) {
            return value !== 0;
        }
        return !! fallback;
    };

    var sanitizeNumber = function ( value, fallback ) {
        var intValue = parseInt( value, 10 );
        if ( Number.isNaN( intValue ) ) {
            return fallback;
        }
        return intValue;
    };

    var withInspectorControls = createHigherOrderComponent( function ( BlockEdit ) {
        return function ( props ) {
            if ( allowedBlocks.indexOf( props.name ) === -1 ) {
                return wp.element.createElement( BlockEdit, props );
            }

            var attributes = props.attributes || {};
            var setAttributes = props.setAttributes;

            var currentEnabled = sanitizeBoolean( getAttributeValue( attributes, 'andwLightboxEnabled' ), defaults.andwLightboxEnabled );
            var currentSlide = sanitizeBoolean( getAttributeValue( attributes, 'andwLightboxSlide' ), defaults.andwLightboxSlide );
            var currentGallery = getAttributeValue( attributes, 'andwLightboxGallery' ) || defaults.andwLightboxGallery;
            var currentAnimation = getAttributeValue( attributes, 'andwLightboxAnimation' ) || defaults.andwLightboxAnimation;
            var currentHover = getAttributeValue( attributes, 'andwLightboxHover' ) || defaults.andwLightboxHover;
            var currentHoverStrength = sanitizeNumber( getAttributeValue( attributes, 'andwLightboxHoverStrength' ), defaults.andwLightboxHoverStrength );
            var currentSize = getAttributeValue( attributes, 'andwLightboxSize' ) || defaults.andwLightboxSize;
            var currentTitle = getAttributeValue( attributes, 'andwLightboxTitle' ) || '';
            var currentDescription = getAttributeValue( attributes, 'andwLightboxDescription' ) || '';

            var panel = wp.element.createElement(
                PanelBody,
                {
                    title: labels.panel || 'ライトボックス設定',
                    initialOpen: false,
                },
                wp.element.createElement( ToggleControl, {
                    label: labels.enabled || 'ライトボックスを使う',
                    checked: currentEnabled,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxEnabled: !! value } );
                    },
                } ),
                wp.element.createElement( ToggleControl, {
                    label: labels.slide || 'スライド表示',
                    checked: currentSlide,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxSlide: !! value } );
                    },
                } ),
                wp.element.createElement( SelectControl, {
                    label: labels.gallery || 'ギャラリー選択',
                    value: currentGallery,
                    options: galleryOptions,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxGallery: value } );
                    },
                } ),
                wp.element.createElement( SelectControl, {
                    label: labels.animation || 'スライドアニメーション',
                    value: currentAnimation,
                    options: animationOptions,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxAnimation: value } );
                    },
                } ),
                wp.element.createElement( SelectControl, {
                    label: labels.hover || 'ホバー効果',
                    value: currentHover,
                    options: hoverOptions,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxHover: value } );
                    },
                } ),
                wp.element.createElement( RangeControl, {
                    label: labels.hoverStrength || 'ホバー強度',
                    value: currentHoverStrength,
                    min: hoverRange.min,
                    max: hoverRange.max,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxHoverStrength: sanitizeNumber( value, defaults.andwLightboxHoverStrength ) } );
                    },
                } ),
                wp.element.createElement( SelectControl, {
                    label: labels.size || '拡大画像サイズ',
                    value: currentSize,
                    options: sizeOptions,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxSize: value } );
                    },
                } ),
                wp.element.createElement( TextControl, {
                    label: labels.title || 'タイトル',
                    value: currentTitle,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxTitle: value } );
                    },
                } ),
                wp.element.createElement( TextareaControl, {
                    label: labels.description || '説明文',
                    value: currentDescription,
                    onChange: function ( value ) {
                        setAttributes( { andwLightboxDescription: value } );
                    },
                } )
            );

            return wp.element.createElement(
                Fragment,
                {},
                wp.element.createElement( BlockEdit, props ),
                wp.element.createElement( InspectorControls, {}, panel )
            );
        };
    }, 'withAndwLightboxInspector' );

    addFilter( 'editor.BlockEdit', 'andw-lightbox/with-inspector', withInspectorControls );
})( window.wp || {}, window.andwLightboxEditorData );

