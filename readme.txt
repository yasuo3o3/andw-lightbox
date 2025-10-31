=== andW Lightbox ===
Contributors: yasuo3o3
Tags: lightbox, gallery, media, images
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

andW Lightbox is a GLightbox-based solution that enhances core Image, Gallery, and Media & Text blocks with accessible lightbox overlays.

== Description ==

The plugin automatically wraps your images with GLightbox links on the frontend while keeping the markup clean in the editor. Configure global defaults, override per attachment, and choose between CDN or bundled assets for GLightbox delivery.

* Automatic lightbox wrappers for core Image, Gallery, and Media & Text blocks
* Block editor inspector controls for gallery slot, animation, hover effect, and captions
* Global defaults covering hover strength, grouped galleries, and infinite scroll support
* Fallback GLightbox implementation bundled for offline environments

== Installation ==
1. Upload the `andw-lightbox` directory to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open **Settings → andW Lightbox** to adjust global defaults if needed.
4. In the block editor, use the inspector panel on supported blocks to override per-image lightbox behaviour.

== Frequently Asked Questions ==

= Does it work with classic editor content? =
Yes. The plugin filters `the_content` to wrap classic editor images in addition to block output.

= Can I disable the CDN? =
Switch the asset source to "同梱ファイル" under **Settings → andW Lightbox → アセット設定** to load the bundled fallback.

== Changelog ==

= 0.1.1 =
* Improved asset enqueue timing for better performance
* Fixed double-wrapping issues with existing image links
* Enhanced CDN fallback re-initialization system
* Optimized settings loading order for delayed asset loading
* Better handling of dynamic content scenarios

= 0.1.0 =
* Initial release with GLightbox integration, block inspector controls, and CDN fallback.

== 日本語 ==

=== 概要 ===
andW Lightbox は、GLightbox ベースのソリューションで、WordPress のコア画像、ギャラリー、メディア・テキストブロックにアクセシブルなライトボックスオーバーレイを追加します。

=== 説明 ===
このプラグインは、エディターでマークアップをクリーンに保ちながら、フロントエンドで画像を GLightbox リンクで自動的にラップします。グローバルデフォルトの設定、添付ファイルごとのオーバーライド、GLightbox 配信のための CDN またはバンドルアセットの選択が可能です。

* コア画像、ギャラリー、メディア・テキストブロックの自動ライトボックスラッパー
* ギャラリースロット、アニメーション、ホバー効果、キャプションのブロックエディターインスペクター制御
* ホバー強度、グループ化ギャラリー、無限スクロールサポートをカバーするグローバルデフォルト
* オフライン環境向けのフォールバック GLightbox 実装をバンドル

=== インストール ===
1. `andw-lightbox` ディレクトリを `/wp-content/plugins/` にアップロードします。
2. WordPress の **プラグイン** メニューでプラグインを有効化します。
3. 必要に応じて **設定 → andW Lightbox** を開いてグローバルデフォルトを調整します。
4. ブロックエディターで、サポートされているブロックのインスペクターパネルを使用して、画像ごとのライトボックス動作をオーバーライドします。

=== よくある質問 ===

= クラシックエディターのコンテンツで動作しますか？ =
はい。プラグインは `the_content` をフィルタリングして、ブロック出力に加えてクラシックエディターの画像もラップします。

= CDN を無効にできますか？ =
**設定 → andW Lightbox → アセット設定** で アセットソースを「同梱ファイル」に切り替えて、バンドルされたフォールバックを読み込みます。

=== 変更履歴 ===

= 0.2.0 =
* レビュー対応とコード品質向上
* CDN依存問題を修正してWordPress.org審査に対応
* libxml設定の適切な復元処理を実装
* PHP Coding Standards エラーを修正
* セキュリティ強化とパフォーマンス最適化

= 0.1.2 =
* WordPress.org 提出のためのドキュメント強化
* 包括的な CHANGELOG.txt と DEVELOPER.md を追加
* 英語と日本語セクションで README.md を更新
* WordPress コーディング標準への準拠を確認
* セキュリティとコードレビューのフィードバックに対応

= 0.1.1 =
* パフォーマンス向上のためのアセットエンキュータイミングの改善
* 既存の画像リンクでの二重ラッピング問題を修正
* CDN フォールバック再初期化システムの強化
* 遅延アセット読み込みのための設定読み込み順序の最適化
* 動的コンテンツシナリオの処理改善

= 0.1.0 =
* GLightbox 統合、ブロックインスペクター制御、CDN フォールバックを含む初回リリース


