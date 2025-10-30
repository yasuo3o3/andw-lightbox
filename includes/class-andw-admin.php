<?php
/**
 * Admin UI for andW Lightbox.
 */

defined( 'ABSPATH' ) || exit;

class Andw_Lightbox_Admin {
    /**
     * @var Andw_Lightbox_Settings
     */
    private $settings;

    public function __construct( Andw_Lightbox_Settings $settings ) {
        $this->settings = $settings;

        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
    }

    /**
     * Register options page.
     */
    public function register_menu() {
        add_options_page(
            __( 'andW Lightbox', 'andw-lightbox' ),
            __( 'andW Lightbox', 'andw-lightbox' ),
            'manage_options',
            'andw-lightbox',
            array( $this, 'render_page' )
        );
    }

    /**
     * Register settings sections and fields.
     */
    public function init_settings() {
        $this->settings->register();

        add_settings_section(
            'andw_lightbox_general',
            __( '基本設定', 'andw-lightbox' ),
            array( $this, 'render_general_intro' ),
            'andw-lightbox'
        );

        add_settings_field( 'enabled', __( 'ライトボックスの既定状態', 'andw-lightbox' ), array( $this, 'field_enabled' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_slide', __( 'スライド表示', 'andw-lightbox' ), array( $this, 'field_default_slide' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_gallery', __( '既定グループ', 'andw-lightbox' ), array( $this, 'field_default_gallery' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_animation', __( '開閉エフェクト', 'andw-lightbox' ), array( $this, 'field_default_animation' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'gallery_animation', __( 'ギャラリー切替アニメーション', 'andw-lightbox' ), array( $this, 'field_gallery_animation' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'enforce_group_animation', __( 'グループ内アニメーション統一', 'andw-lightbox' ), array( $this, 'field_enforce_group_animation' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'enforce_gallery_animation', __( 'ギャラリー切替統一設定', 'andw-lightbox' ), array( $this, 'field_enforce_gallery_animation' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_hover', __( 'ホバー効果', 'andw-lightbox' ), array( $this, 'field_default_hover' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_hover_strength', __( 'ホバー強度', 'andw-lightbox' ), array( $this, 'field_default_hover_strength' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_transform', __( 'トランスフォーム', 'andw-lightbox' ), array( $this, 'field_default_transform' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_transform_strength', __( 'トランスフォーム強度', 'andw-lightbox' ), array( $this, 'field_default_transform_strength' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'default_size', __( '拡大画像サイズ', 'andw-lightbox' ), array( $this, 'field_default_size' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'allow_full', __( 'フルサイズ許可', 'andw-lightbox' ), array( $this, 'field_allow_full' ), 'andw-lightbox', 'andw_lightbox_general' );
        add_settings_field( 'infinite_scroll', __( '無限スクロール対応', 'andw-lightbox' ), array( $this, 'field_infinite_scroll' ), 'andw-lightbox', 'andw_lightbox_general' );

        add_settings_section(
            'andw_lightbox_assets',
            __( 'アセット設定', 'andw-lightbox' ),
            array( $this, 'render_assets_intro' ),
            'andw-lightbox'
        );

        add_settings_field( 'glightbox_source', __( 'GLightbox 取得元', 'andw-lightbox' ), array( $this, 'field_glightbox_source' ), 'andw-lightbox', 'andw_lightbox_assets' );
        add_settings_field( 'glightbox_version', __( 'GLightbox バージョン', 'andw-lightbox' ), array( $this, 'field_glightbox_version' ), 'andw-lightbox', 'andw_lightbox_assets' );

        // デザイン設定セクション
        add_settings_section(
            'andw_lightbox_design',
            __( 'デザイン設定', 'andw-lightbox' ),
            array( $this, 'render_design_intro' ),
            'andw-lightbox-design'
        );

        // デザイン設定フィールド
        add_settings_field( 'design_show_title', __( 'タイトル表示', 'andw-lightbox' ), array( $this, 'field_design_show_title' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_show_description', __( 'ディスクリプション表示', 'andw-lightbox' ), array( $this, 'field_design_show_description' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_max_width', __( '最大幅', 'andw-lightbox' ), array( $this, 'field_design_max_width' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_max_height', __( '最大高さ', 'andw-lightbox' ), array( $this, 'field_design_max_height' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_overlay_color', __( 'オーバーレイ色', 'andw-lightbox' ), array( $this, 'field_design_overlay_color' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_overlay_opacity', __( 'オーバーレイ透明度', 'andw-lightbox' ), array( $this, 'field_design_overlay_opacity' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_mobile_navigation', __( 'モバイルでナビゲーション表示', 'andw-lightbox' ), array( $this, 'field_design_mobile_navigation' ), 'andw-lightbox-design', 'andw_lightbox_design' );
        add_settings_field( 'design_custom_css', __( 'カスタムCSS', 'andw-lightbox' ), array( $this, 'field_design_custom_css' ), 'andw-lightbox-design', 'andw_lightbox_design' );
    }

    /**
     * Intro text for general section.
     */
    public function render_general_intro() {
        echo '<p>' . esc_html__( '既定値はメディア個別設定で上書きできます。', 'andw-lightbox' ) . '</p>';
    }

    /**
     * Intro text for assets section.
     */
    public function render_assets_intro() {
        echo '<p>' . esc_html__( 'CDN が利用できない環境ではローカル配信に切り替えてください。', 'andw-lightbox' ) . '</p>';
    }

    /**
     * Render main settings page.
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'andW Lightbox 設定', 'andw-lightbox' ) . '</h1>';

        // ナビタブ
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=andw-lightbox&tab=general" class="nav-tab ' . ( 'general' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( '基本設定', 'andw-lightbox' ) . '</a>';
        echo '<a href="?page=andw-lightbox&tab=design" class="nav-tab ' . ( 'design' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'デザイン設定', 'andw-lightbox' ) . '</a>';
        echo '</h2>';

        echo '<form action="' . esc_url( admin_url( 'options.php' ) ) . '" method="post">';
        settings_fields( 'andw_lightbox_settings' );

        // タブ別セクション表示
        if ( 'design' === $active_tab ) {
            do_settings_sections( 'andw-lightbox-design' );
        } else {
            do_settings_sections( 'andw-lightbox' );
        }

        submit_button();
        echo '</form>';
        echo '</div>';
    }

    private function option_name() {
        return $this->settings->get_option_name();
    }


    public function field_enabled() {
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[enabled]" value="1" ';
        checked( $this->settings->is_enabled_flag( 'enabled' ), true );
        echo '> ' . esc_html__( 'フロントエンドでライトボックスを既定で有効化', 'andw-lightbox' ) . '</label>';
        echo '<p class="description">' . esc_html__( '添付ファイルのメタ情報で個別に無効化できます。', 'andw-lightbox' ) . '</p>';
    }

    public function field_default_slide() {
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[default_slide]" value="1" ';
        checked( $this->settings->is_enabled_flag( 'default_slide' ), true );
        echo '> ' . esc_html__( '同一グループ内でのスライド切り替えを許可', 'andw-lightbox' ) . '</label>';
        echo '<p class="description">' . esc_html__( 'OFF にすると個別画像のみ表示します。', 'andw-lightbox' ) . '</p>';
    }

    public function field_default_gallery() {
        $current = $this->settings->get( 'default_gallery' );
        $choices = andw_lightbox_get_gallery_options();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[default_gallery]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( '固定グループ番号の既定値を指定します。', 'andw-lightbox' ) . '</p>';
    }

    public function field_default_animation() {
        $current = $this->settings->get( 'default_animation' );
        $choices = andw_lightbox_get_animation_options();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[default_animation]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'ライトボックスを開閉するときの演出を選択します。', 'andw-lightbox' ) . '</p>';
    }

    public function field_gallery_animation() {
        $current = $this->settings->get( 'gallery_animation' );
        $choices = andw_lightbox_get_gallery_animation_options();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[gallery_animation]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'ギャラリー内で前後の画像へ移動する際の動きを選択します。', 'andw-lightbox' ) . '</p>';
    }

    public function field_default_hover() {
        $current = $this->settings->get( 'default_hover' );
        $choices = andw_lightbox_get_hover_options();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[default_hover]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function field_default_hover_strength() {
        $value = intval( $this->settings->get( 'default_hover_strength' ) );
        printf(
            '<input type="number" min="0" max="100" step="1" name="%1$s[default_hover_strength]" value="%2$s" class="small-text"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( '0〜100 の範囲でホバー強度を指定します。', 'andw-lightbox' )
        );
    }

    public function field_default_size() {
        $current = $this->settings->get( 'default_size' );
        $choices = andw_lightbox_get_registered_size_choices();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[default_size]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function field_allow_full() {
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[allow_full]" value="1" ';
        checked( $this->settings->is_enabled_flag( 'allow_full' ), true );
        echo '> ' . esc_html__( '中間サイズが無い場合にフルサイズへフォールバックを許可', 'andw-lightbox' ) . '</label>';
    }

    public function field_infinite_scroll() {
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[infinite_scroll]" value="1" ';
        checked( $this->settings->is_enabled_flag( 'infinite_scroll' ), true );
        echo '> ' . esc_html__( 'MutationObserver を使用して無限スクロールで追加された要素を初期化', 'andw-lightbox' ) . '</label>';
        echo '<p class="description">' . esc_html__( '大量の DOM 変更がある環境ではパフォーマンスへ影響する可能性があります。', 'andw-lightbox' ) . '</p>';
    }

    public function field_glightbox_source() {
        $current = $this->settings->get( 'glightbox_source' );
        $choices = array(
            'cdn'   => __( 'CDN（既定）', 'andw-lightbox' ),
            'local' => __( '同梱ファイル', 'andw-lightbox' ),
        );

        echo '<select name="' . esc_attr( $this->option_name() ) . '[glightbox_source]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function field_default_transform() {
        $current = $this->settings->get( 'default_transform' );
        $choices = andw_lightbox_get_transform_options();

        echo '<select name="' . esc_attr( $this->option_name() ) . '[default_transform]">';
        foreach ( $choices as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ';
            selected( $value, $current );
            echo '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function field_default_transform_strength() {
        $value = intval( $this->settings->get( 'default_transform_strength' ) );
        printf(
            '<input type="number" min="0" max="100" step="1" name="%1$s[default_transform_strength]" value="%2$s" class="small-text"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( '0〜100 の範囲でトランスフォーム強度を指定します。', 'andw-lightbox' )
        );
    }

    public function field_glightbox_version() {
        $value = $this->settings->get( 'glightbox_version' );
        printf(
            '<input type="text" name="%1$s[glightbox_version]" value="%2$s" class="regular-text" pattern="[0-9A-Za-z\.\-]+"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( 'CDN 利用時に読み込むバージョン番号を指定します。', 'andw-lightbox' )
        );
    }

    /**
     * Intro text for design section.
     */
    public function render_design_intro() {
        echo '<p>' . esc_html__( 'ライトボックスの表示スタイルを調整できます。', 'andw-lightbox' ) . '</p>';
    }

    public function field_design_show_title() {
        $checked = $this->settings->get( 'design_show_title' );
        echo '<input type="hidden" name="' . esc_attr( $this->option_name() ) . '[design_show_title]" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[design_show_title]" value="1" ';
        checked( '1', $checked );
        echo '> ' . esc_html__( 'ライトボックスでタイトルを表示する', 'andw-lightbox' ) . '</label>';
    }

    public function field_design_show_description() {
        $checked = $this->settings->get( 'design_show_description' );
        echo '<input type="hidden" name="' . esc_attr( $this->option_name() ) . '[design_show_description]" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[design_show_description]" value="1" ';
        checked( '1', $checked );
        echo '> ' . esc_html__( 'ライトボックスで説明文を表示する', 'andw-lightbox' ) . '</label>';
    }

    public function field_design_mobile_navigation() {
        $checked = $this->settings->get( 'design_mobile_navigation' );
        echo '<input type="hidden" name="' . esc_attr( $this->option_name() ) . '[design_mobile_navigation]" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[design_mobile_navigation]" value="1" ';
        checked( '1', $checked );
        echo '> ' . esc_html__( 'スマートフォン表示でも前後ナビを表示する', 'andw-lightbox' ) . '</label>';
    }

    public function field_design_max_width() {
        $value = $this->settings->get( 'design_max_width' );
        printf(
            '<input type="text" name="%1$s[design_max_width]" value="%2$s" placeholder="80%%" class="regular-text"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( '例: 80%, 800px（空欄=デフォルト）', 'andw-lightbox' )
        );
    }

    public function field_design_max_height() {
        $value = $this->settings->get( 'design_max_height' );
        printf(
            '<input type="text" name="%1$s[design_max_height]" value="%2$s" placeholder="80%%" class="regular-text"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( '例: 80vh, 600px（空欄=デフォルト）', 'andw-lightbox' )
        );
    }

    public function field_design_overlay_color() {
        $value = $this->settings->get( 'design_overlay_color' );
        printf(
            '<input type="color" name="%1$s[design_overlay_color]" value="%2$s"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( 'ライトボックス背景色を指定', 'andw-lightbox' )
        );
    }

    public function field_design_overlay_opacity() {
        $value = $this->settings->get( 'design_overlay_opacity' );
        printf(
            '<input type="number" min="0" max="1" step="0.01" name="%1$s[design_overlay_opacity]" value="%2$s" class="small-text"> <span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_attr( $value ),
            esc_html__( '0（透明）〜1（不透明）の範囲で指定', 'andw-lightbox' )
        );
    }

    public function field_enforce_group_animation() {
        $checked = $this->settings->get( 'enforce_group_animation' );
        echo '<input type="hidden" name="' . esc_attr( $this->option_name() ) . '[enforce_group_animation]" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[enforce_group_animation]" value="1" ';
        checked( '1', $checked );
        echo '> ' . esc_html__( 'グループ化された画像では開閉アニメーションを統一する', 'andw-lightbox' ) . '</label>';
        echo '<p class="description">' . esc_html__( 'ON時、グループ内では最初の画像の設定を適用します。個別設定は単独表示時のみ有効になります。', 'andw-lightbox' ) . '</p>';
    }

    public function field_enforce_gallery_animation() {
        $checked = $this->settings->get( 'enforce_gallery_animation' );
        echo '<input type="hidden" name="' . esc_attr( $this->option_name() ) . '[enforce_gallery_animation]" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr( $this->option_name() ) . '[enforce_gallery_animation]" value="1" ';
        checked( '1', $checked );
        echo '> ' . esc_html__( 'ギャラリー切替アニメーションをサイト全体で統一する', 'andw-lightbox' ) . '</label>';
        echo '<p class="description">' . esc_html__( 'ON時、個別ページでのギャラリー切替アニメーション設定を無効化します。', 'andw-lightbox' ) . '</p>';
    }

    public function field_design_custom_css() {
        $value = $this->settings->get( 'design_custom_css' );
        printf(
            '<textarea name="%1$s[design_custom_css]" rows="10" cols="50" class="large-text code">%2$s</textarea><br><span class="description">%3$s</span>',
            esc_attr( $this->option_name() ),
            esc_textarea( $value ),
            esc_html__( 'GLightbox説明文エリア用のCSSルールを記述してください。', 'andw-lightbox' )
        );
    }
}
