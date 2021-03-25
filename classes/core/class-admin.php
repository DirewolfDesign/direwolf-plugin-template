<?php
/**
 * Plugin admin.
 *
 * @package {{plugin_namespace}}
 */

namespace {{plugin_namespace}};

if( ! defined( 'ABSPATH' ) ) wp_die( 'End of Line, Man' );

/**
 * {{plugin_namespace}} Admin class.
 */
class Admin {

    /**
     * Plugin Menu Pages
     * @var array $menu_pages
     */
    public $menu_pages = array();

    /**
     * Top Level Plugin Pages
     * @var array $toplevel_pages
     */
    public $toplevel_pages = array();

    /**
     * Plugin External Menu Pages
     * @var array $external_menu_pages
     */
    public $external_menu_pages = array();

    /**
     * Plugin Post Types
     * @var array $post_types
     */
    public $post_types = array();

    /**
     * Admin constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
        add_action( 'admin_menu', array( $this, 'admin_submenu' ), 12 );
        add_action( 'admin_menu', array( $this, 'maybe_hide_menu_item' ), 13 );
        add_filter( 'clean_url',  array( $this, 'clean_url' ), 10, 3 );

        add_action( 'admin_enqueue_scripts',        array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'enqueue_block_editor_assets',  array( $this, 'constructor_enqueue_scripts' ) );
        add_action( 'enqueue_block_editor_assets',  array( $this, 'enqueue_script_translations' ), 9 );

        add_action( '{{plugin_text_domain}}/admin_page_header', array( $this, 'in_admin_header' ) );
    }

    /**
     * Admin menu
     * Controls toplevel admin pages.
     * By default, the plugin will add a toplevel page named after your plugin.
     * You can remove or edit this page below or add any new pages as needed.
     *
     * Note: If you want to add a submenu page to any of these pages, see the
     * admin_submenu function below.
     */
    public function admin_menu() {
        // Default Plugin Menu Page
        $this->add_menu_page(
            esc_html__( '{{plugin_name}}', '{{plugin_text_domain}}' ),  // $page_title
            esc_html__( '{{plugin_name}}', '{{plugin_text_domain}}' ),  // $menu_title
            'manage_options',                                           // $capability
            '{{plugin_text_domain}}'                                    // $menu_slug
        );

        // Fire an action to allow other methods to hook in and run stuff
        // when this plugin registers toplevel admin menu pages...
        do_action( '{{plugin_text_domain}}/admin_menu' );
    }

    /**
     * Override for WP's `add_menu_page` function that allows the plugin to keep
     * track of any added menu pages.
     *
     * @param   string      $page_title
     * @param   string      $menu_title
     * @param   string      $capability
     * @param   string      $menu_slug
     * @param   callable    $function
     * @param   string      $icon_url
     * @param   int         $position
     *
     * @return string
     */
    public function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
        /**
         * If the menu points to an external URL, store a reference to the URL
         * that we can use later in our `clean_url` hook and generate a temporary
         * $menu_slug to use for this menu item.
         */
        if( strstr( $menu_slug, 'https:' ) ) :
            $url = $menu_slug;
            $menu_slug = "{{plugin_text_domain}}_ext_" . count( $this->external_menu_pages );
            $this->external_menu_pages[ $menu_slug ] = $url;
        endif;

        return $this->toplevel_pages[] = $this->menu_pages[] = \add_menu_page(
            $page_title,
            $menu_title,
            $capability,
            $menu_slug,
            $function ? $function : array( $this, 'render_admin_page' ),
            $icon_url,
            $position
        );
    }

    /**
     * Admin submenu
     */
    public function admin_submenu() {
        // Rename default submenu page to `Dashboard`
        $this->add_submenu_page(
            '{{plugin_text_domain}}',                                               // $parent_slug
            esc_html__( '{{plugin_name}} Dashboard', '{{plugin_text_domain}}' ),    // $page_title
            esc_html__( 'Dashboard', '{{plugin_text_domain}}' ),                    // $menu_title
            'manage_options',                                                       // $capability
            '{{plugin_text_domain}}',                                               // $menu_slug
        );

        // Add the Direwolf Plugin Template documentation link to the menu
        $this->add_submenu_page(
            '{{plugin_text_domain}}',                                               // $parent_slug
            esc_html__( 'Documentation', 'test-plugin' ),                           // $page_title
            esc_html__( 'Documentation', 'test-plugin' ),                           // $menu_title
            'manage_options',                                                       // $capability
            'https://github.com/DirewolfDesign/direwolf-plugin-template/wiki'       // $menu_slug
        );

        // Fire an action to allow other methods to hook in and run stuff
        // when this plugin registers toplevel admin menu pages...
        do_action( '{{plugin_text_domain}}/admin_submenu' );
    }

    /**
     * Override for WP's `add_submenu_page` function that allows the plugin to keep
     * track of any added submenu pages.
     *
     * @param   string      $parent_slug
     * @param   string      $page_title
     * @param   string      $menu_title
     * @param   string      $capability
     * @param   string      $menu_slug
     * @param   callable    $function
     * @param   int         $position
     */
    public function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null ) {
        /**
         * If the menu points to an external URL, store a reference to the URL
         * that we can use later in our `clean_url` hook and generate a temporary
         * $menu_slug to use for this menu item.
         */
        if( strstr( $menu_slug, 'https:' ) ) :
            $url = $menu_slug;
            $menu_slug = "{$parent_slug}_ext_" . count( $this->external_menu_pages );
            $this->external_menu_pages[ $menu_slug ] = $url;
        endif;

        return $this->menu_pages[] = \add_submenu_page(
            $parent_slug,
            $page_title,
            $menu_title,
            $capability,
            $menu_slug,
            $function ? $function : array( $this, 'render_admin_page' ),
            $position
        );
    }

    /**
     * Clean URL
     * Hooks into the `clean_url` hooks to allow external links to be added to
     * our admin menu pages.
     *
     * @param   string  $good_protocol_url
     * @param   string  $original_url
     * @param   string  $_context
     *
     * @return string
     */
    public function clean_url( $good_protocol_url, $original_url, $_context ) {
        $parts = parse_url( $original_url );
        if( ! isset( $parts['query'] ) ) return $good_protocol_url;

        // Parse the URL Query string
        parse_str( $parts['query'], $params );

        // Check if the `page` parameter is set and whether the page has an
        // external URL set in our `external_menu_pages` array.
        if( isset( $params['page'] ) && isset( $this->external_menu_pages[ $params['page'] ] ) ) {
            $good_protocol_url = $this->external_menu_pages[ $params['page'] ];
        }

        return $good_protocol_url;
    }

    /**
     * Renders a default admin page complete with hooks
     *
     * TODO: This could really be a template file that can be dynamically
     * assigned using a filter and the $current_screen parameter.
     */
    public function render_admin_page() {
        global $pagenow, $current_screen;

        do_action( '{{plugin_text_domain}}/admin_page_header', $current_screen );

        do_action( '{{plugin_text_domain}}/before_admin_page_title', $current_screen );
        echo "<h1>" . get_admin_page_title() . "</h1>";
        do_action( '{{plugin_text_domain}}/after_admin_page_title', $current_screen );

        do_action( '{{plugin_text_domain}}/before_admin_page_content', $current_screen );
        do_action( '{{plugin_text_domain}}/admin_page_content', $current_screen );
        do_action( '{{plugin_text_domain}}/after_admin_page_content', $current_screen );
    }

    /**
     * Add post type
     * Stores a reference to registered post types
     *
     * @param   string  $post_type
     */
    public function add_post_type( $post_type ) {
        $this->post_types[] = $post_type;
    }

    /**
     * Get registered post types.
     *
     * @return array
     */
    public function post_types() {
        return $this->post_types;
    }

    /**
     * Hide menu / submenu items
     */
    public function maybe_hide_menu_item() {
        $show_menu_item = apply_filters( '{{plugin_text_domain}}/show_admin_menu', true );

        // Hide any menu items here
        if ( ! $show_menu_item ) :
            remove_menu_page( '{{plugin_text_domain}}' );
        endif;
    }

    /**
     * Enqueue Admin Styles & Scripts
     */
    public function admin_enqueue_scripts() {
        global $wp_locale;

        wp_enqueue_script( 'date_i18n', plugin()->plugin_url() . 'vendor/date_i18n/date_i18n.js', array(), '{{plugin_version}}', true );

        $month_names       = array_map( array( &$wp_locale, 'get_month' ), range( 1, 12 ) );
        $month_names_short = array_map( array( &$wp_locale, 'get_month_abbrev' ), $month_names );
        $day_names         = array_map( array( &$wp_locale, 'get_weekday' ), range( 0, 6 ) );
        $day_names_short   = array_map( array( &$wp_locale, 'get_weekday_abbrev' ), $day_names );

        wp_localize_script(
            'date_i18n',
            'DATE_I18N',
            array(
                'month_names'       => $month_names,
                'month_names_short' => $month_names_short,
                'day_names'         => $day_names,
                'day_names_short'   => $day_names_short,
            )
        );

        // TODO: add direwolf-plugin-admin style to assets directory
        if( ! wp_style_is( 'direwolf-plugin-admin', 'enqueued' ) )
            wp_enqueue_style( 'direwolf-plugin-admin', plugin()->plugin_url() . 'assets/core/admin/css/style.min.css', '', '{{plugin_version}}' );

        wp_enqueue_style( '{{plugin_text_domain}}-admin', plugin()->plugin_url() . 'assets/admin/css/style.min.css', '', '{{plugin_version}}' );
        wp_style_add_data( '{{plugin_text_domain}}-admin', 'rtl', 'replace' );
        wp_style_add_data( '{{plugin_text_domain}}-admin', 'suffix', '.min' );
    }

    /**
     * Enqueue constructor styles and scripts.
     *
     * TODO: Fix all this...
     */
    public function constructor_enqueue_scripts() {
        // Enqueue block editor assets here...

        // Example:
        // wp_enqueue_script(
        //     '{{plugin_text_domain}}-scriptname',                   // $handle
        //     plugin()->plugin_url() . 'assets/js/script.min.js',    // $src
        //     array(),                                               // $deps
        //     '{{plugin_version}}',                                  // $ver
        //     false                                                  // $in_footer
        // );
        // wp_localize_script(
        //     '{{plugin_text_domain}}-scriptname',                   // $handle
        //     '{{plugin_namespace}}ScriptnameData',                  // $object_name
        //     array()                                                // $l10n
        // );

        // Fire an action to allow other methods to hook in and run stuff
        // when this plugin enqueues script translations...
        do_action( '{{plugin_text_domain}}/constructor_enqueue_scripts' );
    }

    /**
     * Add script translations
     *
     * TODO: Fix all this...
     */
    public function enqueue_script_translations() {
        if( ! function_exists( 'wp_set_script_translations' ) ) return;

        // Enqueue script translations here...

        // Example:
        // wp_enqueue_script(
        //     '{{plugin_text_domain}}-translation',                        // $handle
        //     plugin()->plugin_url() . 'assets/js/translation.min.js',     // $src
        //     array(),                                                     // $deps
        //     '{{plugin_version}}',                                        // $ver
        //     false                                                        // $in_footer
        // );
        // wp_set_script_translations(
        //     '{{plugin_text_domain}}-translation',                        // $handle
        //     '{{plugin_text_domain}}',                                    // $domain
        //     plugin()->plugin_path() . 'languages'                        // $path
        // );

        // Fire an action to allow other methods to hook in and run stuff
        // when this plugin enqueues script translations...
        do_action( '{{plugin_text_domain}}/enqueue_script_translations' );
    }

    /**
     * Admin Navigation
     * Adds a custom navigation menu to the top of all relevant
     * plugin pages & post types.
     */
     public function in_admin_header() {
         if( ! function_exists( 'get_current_screen' ) ) return;

         $show_toolbar = apply_filters( '{{plugin_text_domain/show_admin_toolbar}}', true );
         if( ! $show_toolbar ) return;

         $screen = get_current_screen();

         // Check if we're on a plugin screen or in a plugin post type...
         $on_plugin_screen = $screen->post_type ?
            in_array( $screen->post_type, $this->post_types ) :
            in_array( $screen->id, $this->menu_pages )
         );

         if( ! $on_plugin_screen ) return;

         global $submenu, $submenu_file, $plugin_page;

         $parent_slug = $screen->parent_base;
         $tabs = array();

         $tabs = apply_filters( '{{plugin_text_domain}}/admin_toolbar_tabs', $tabs );

         // Bail early if set to false or empty.
         if ( ! $tabs || empty( $tabs ) ) return;

         // phpcs:ignore
         $default_logo_url = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( plugin()->plugin_path() . 'assets/svg/{{plugin_text_domain}}-dark.svg' ) );
         // phpcs:ignore
         $logo_url = apply_filters( '{{plugin_text_domain}}/admin_toolbar_logo', $default_logo_url );

         // phpcs:ignore
         $default_toolbar = plugin()->plugin_path() . 'components/admin/dw-admin-toolbar.php';
         // phpcs:ignore
         $toolbar_path = apply_filters( '{{plugin_text_domain}}/admin_toolbar_path', $default_toolbar );

         // Include the admin page toolbar
         if( file_exists( $toolbar_path ) ) include( $toolbar_path );
     }

}
