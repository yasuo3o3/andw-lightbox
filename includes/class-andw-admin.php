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
        add_settings_field( 'default_animation', __( '開閉アニメーション', 'andw-lightbox' ), array( $this, 'field_default_animation' ), 'andw-lightbox', 'andw_lightbox_general' );
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

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'andW Lightbox 設定', 'andw-lightbox' ) . '</h1>';
        echo '<form action="' . esc_url( admin_url( 'options.php' ) ) . '" method="post">';
        settings_fields( 'andw_lightbox_settings' );
        do_settings_sections( 'andw-lightbox' );
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
}
