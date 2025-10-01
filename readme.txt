=== andW Lightbox ===
Contributors: netservice
Tags: lightbox, gallery, media, images
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
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

= 0.1.0 =
* Initial release with GLightbox integration, block inspector controls, and CDN fallback.


