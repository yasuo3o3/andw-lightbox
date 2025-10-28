# andW Lightbox

A modern, accessible WordPress plugin that integrates GLightbox with core WordPress blocks to provide elegant lightbox overlays for images.

## Overview

andW Lightbox automatically enhances your WordPress site's Image, Gallery, and Media & Text blocks with GLightbox-powered lightbox functionality. The plugin maintains clean markup in the editor while dynamically adding lightbox capabilities to the frontend.

## Features

- **Seamless Integration**: Works with core WordPress Image, Gallery, and Media & Text blocks
- **Block Editor Controls**: Inspector panel controls for per-image customization
- **Global Settings**: Configure default behavior for hover effects, animations, and gallery grouping
- **CDN & Local Assets**: Choose between CDN delivery or bundled GLightbox assets
- **Classic Editor Support**: Automatically processes classic editor images via `the_content` filter
- **Accessibility Focused**: Built with WordPress accessibility standards in mind
- **Performance Optimized**: Smart asset loading only when needed

## Installation

### From WordPress.org (Coming Soon)

This plugin will be submitted to the WordPress.org plugin directory for easy installation through the WordPress admin.

### Manual Installation

1. Download the plugin files
2. Upload the `andw-lightbox` directory to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Configure settings at **Settings → andW Lightbox**

## Usage

### Basic Setup

1. Activate the plugin
2. Go to **Settings → andW Lightbox** to configure global defaults
3. Add Image, Gallery, or Media & Text blocks to your posts/pages
4. Use the inspector panel to customize lightbox behavior per block

### Global Settings

- **Lightbox Default State**: Enable/disable lightbox functionality site-wide
- **Slide Display**: Allow navigation between images in the same gallery
- **Default Gallery**: Set gallery grouping behavior
- **Animation Effects**: Choose opening/closing animations
- **Hover Effects**: Configure hover overlays and strength
- **Transform Effects**: Set image transformation animations
- **Asset Source**: Choose between CDN or local GLightbox delivery

### Block-Level Controls

Each supported block includes inspector controls for:
- Gallery slot assignment
- Animation preferences
- Hover effect overrides
- Caption display options

## Technical Details

### Supported Blocks

- `core/image` - Single images
- `core/gallery` - Image galleries
- `core/media-text` - Media & Text blocks

### Asset Loading

The plugin intelligently detects when lightbox functionality is needed and loads assets accordingly:
- Early detection via content analysis before `wp_enqueue_scripts`
- Fallback loading for dynamic content scenarios
- CDN delivery with automatic local fallback

### Browser Compatibility

Compatible with all modern browsers that support:
- ES6 JavaScript features
- CSS Grid and Flexbox
- CustomEvent API

## Development

### Requirements

- WordPress 6.0+
- PHP 7.4+
- Modern browser support

### Code Standards

This plugin follows WordPress Coding Standards and is tested with:
- PHPCS (WordPress standards)
- WordPress Plugin Check tool
- Manual accessibility testing

## Contributing

We welcome contributions! Please:
1. Follow WordPress coding standards
2. Test with supported WordPress/PHP versions
3. Ensure accessibility compliance
4. Include appropriate documentation

## WordPress.org Submission

This plugin is being prepared for submission to the official WordPress.org plugin directory. The submission will include:
- Full WPCS compliance
- Comprehensive testing
- Accessibility validation
- Security review compliance

## License

GPLv2 or later. See [LICENSE](LICENSE) for details.

This plugin integrates GLightbox which is licensed under the MIT License:
Copyright (c) 2018 Biati Digital (https://www.biati.digital)

## Credits

This plugin integrates [GLightbox](https://github.com/biati-digital/glightbox) by Biati Digital.

---

## 日本語

### 概要

andW Lightbox は、WordPress のコアブロック（画像、ギャラリー、メディアとテキスト）に GLightbox を統合し、アクセシブルなライトボックス機能を提供するプラグインです。

### 主な機能

- **シームレス統合**: WordPress コアブロックとの自動連携
- **ブロックエディター対応**: インスペクターパネルでの個別設定
- **グローバル設定**: サイト全体のデフォルト動作設定
- **CDN・ローカル配信**: GLightbox の配信方法選択
- **クラシックエディター対応**: 既存コンテンツの自動処理
- **アクセシビリティ重視**: WordPress 標準に準拠
- **パフォーマンス最適化**: 必要時のみアセット読み込み

### WordPress.org 登録予定

このプラグインは WordPress.org 公式ディレクトリへの登録を予定しており、WordPress 管理画面から直接インストール可能になります。

### ライセンス

GPLv2 またはそれ以降。詳細は [LICENSE](LICENSE) をご確認ください。