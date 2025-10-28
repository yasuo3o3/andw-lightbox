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

andW Lightbox は、WordPress のコアブロック（画像、ギャラリー、メディアとテキスト）に GLightbox を統合し、アクセシブルなライトボックス機能を提供するモダンでアクセシブルなプラグインです。

プラグインは WordPress サイトの画像、ギャラリー、メディア・テキストブロックを GLightbox 対応のライトボックス機能で自動的に強化します。エディターでクリーンなマークアップを維持しながら、フロントエンドに動的にライトボックス機能を追加します。

### 主な機能

- **シームレス統合**: WordPress コアの画像、ギャラリー、メディア・テキストブロックとの連携
- **ブロックエディター制御**: 画像ごとのカスタマイズのためのインスペクターパネル制御
- **グローバル設定**: ホバー効果、アニメーション、ギャラリーグループ化のデフォルト動作設定
- **CDN・ローカルアセット**: GLightbox の CDN 配信またはバンドルアセットの選択
- **クラシックエディター対応**: `the_content` フィルターでクラシックエディター画像を自動処理
- **アクセシビリティ重視**: WordPress アクセシビリティ標準に準拠した構築
- **パフォーマンス最適化**: 必要な場合のみスマートなアセット読み込み

### インストール

#### WordPress.org から（近日公開予定）

このプラグインは WordPress 管理画面から簡単にインストールできるよう、WordPress.org プラグインディレクトリに提出予定です。

#### 手動インストール

1. プラグインファイルをダウンロード
2. `andw-lightbox` ディレクトリを `/wp-content/plugins/` にアップロード
3. WordPress の **プラグイン** メニューでプラグインを有効化
4. **設定 → andW Lightbox** で設定を行う

### 使用方法

#### 基本設定

1. プラグインを有効化
2. **設定 → andW Lightbox** でグローバルデフォルトを設定
3. 投稿/ページに画像、ギャラリー、メディア・テキストブロックを追加
4. インスペクターパネルでブロックごとのライトボックス動作をカスタマイズ

#### グローバル設定

- **ライトボックスデフォルト状態**: サイト全体でのライトボックス機能の有効/無効
- **スライド表示**: 同じギャラリー内での画像間ナビゲーションの許可
- **デフォルトギャラリー**: ギャラリーグループ化動作の設定
- **アニメーション効果**: 開閉アニメーションの選択
- **ホバー効果**: ホバーオーバーレイと強度の設定
- **トランスフォーム効果**: 画像変換アニメーションの設定
- **アセットソース**: CDN またはローカル GLightbox 配信の選択

#### ブロックレベル制御

サポートされている各ブロックには以下のインスペクター制御が含まれます：
- ギャラリースロット割り当て
- アニメーション設定
- ホバー効果のオーバーライド
- キャプション表示オプション

### 技術詳細

#### サポートブロック

- `core/image` - 単一画像
- `core/gallery` - 画像ギャラリー
- `core/media-text` - メディア・テキストブロック

#### アセット読み込み

プラグインはライトボックス機能が必要な場合を賢く検出し、それに応じてアセットを読み込みます：
- `wp_enqueue_scripts` 前のコンテンツ分析による早期検出
- 動的コンテンツシナリオのフォールバック読み込み
- 自動ローカルフォールバック付き CDN 配信

#### ブラウザ互換性

以下をサポートするすべてのモダンブラウザと互換性があります：
- ES6 JavaScript 機能
- CSS Grid と Flexbox
- CustomEvent API

### 開発

#### 要件

- WordPress 6.0+
- PHP 7.4+
- モダンブラウザサポート

#### コード標準

このプラグインは WordPress コーディング標準に従い、以下でテストされています：
- PHPCS (WordPress 標準)
- WordPress Plugin Check ツール
- 手動アクセシビリティテスト

### コントリビューション

コントリビューションを歓迎します！以下をお願いします：
1. WordPress コーディング標準に従う
2. サポートされている WordPress/PHP バージョンでテスト
3. アクセシビリティ準拠の確保
4. 適切なドキュメントの含有

### WordPress.org 提出

このプラグインは WordPress.org 公式プラグインディレクトリへの提出準備中です。提出には以下が含まれます：
- 完全な WPCS 準拠
- 包括的なテスト
- アクセシビリティ検証
- セキュリティレビュー準拠

### ライセンス

GPLv2 またはそれ以降。詳細は [LICENSE](LICENSE) をご確認ください。

このプラグインは MIT License でライセンスされた GLightbox を統合しています：
Copyright (c) 2018 Biati Digital (https://www.biati.digital)

### クレジット

このプラグインは Biati Digital による [GLightbox](https://github.com/biati-digital/glightbox) を統合しています。