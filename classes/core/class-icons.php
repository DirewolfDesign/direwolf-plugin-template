<?php
/**
 * Plugin icons.
 *
 * @package {{plugin_namespace}}
 *
 * TODO: These might be better stored in the `assets/svg` directory to make managing
 * the SVGs easier. We can use `glob()` to loop through the svg directories and pull
 * back files and images in categories.
 */

namespace {{plugin_namespace}};

if( ! defined( 'ABSPATH' ) ) wp_die( 'End of Line, Man' );

/**
 * {{plugin_namespace}} Icons class.
 */
class Icons {

    /**
     * Path to the icons directory
     *
     * @var string
     */
    private $iconpath = 'assets/core/icons'

    /**
     * Icon storage
     *
     * @var array
     */
    public $icons = array();

    /**
     * Icons Constructor.
     */
    public function __construct() {
        $this->_init();
    }

    /**
     * Icons Initializer
     */
    private function _init() {
        $this->$iconpath = apply_filters( '{{plugin_text_domain}}/icon_path', $this->iconpath );
        $this->use_categories = apply_filters( '{{plugin_text_domain}}/icons/use_categories', true );

        // Fire an action to allow other methods to hook in and run stuff
        // when this class initializes, before icons are fetched...
        do_action( '{{plugin_text_domain}}/icons/init' );

        // Populate the `$icons` array
        $this->icons = $this->_get_icons();
    }

    /**
     * Get all available categories and icons.
     *
     * @return array
     */
    private function _get_icons() {
        $dir = plugin()->plugin_path() . $this->$iconpath;
        $icons = array();

        // Loop over the `$iconpath` directory to get all icons and categories
        foreach( glob( "$dir/**/*.svg" as $file ) ) :
            $category = $this->use_categories ? basename( dirname( $file ) ) : null;
            $name = str_replace( '_', '-', basename( $filename, '.svg' ) );
            $svg = file_get_contents( $filename );

            if( $this->use_categories ) :
                if( ! isset( $icons[ $category ] ) ) { $icons[ $category ] = array(); }
                $icons[ $category ][ $name ] = $svg;
            else :
                $icons[ $name ] = $svg;
            endif;
        endforeach;

        // Filter the return value to allow custom icons to be added at runtime...
        return apply_filters( '{{plugin_text_domain}}/icons/_get_icons', $icons, $this->use_categories );
    }

    /**
     * Get all available icons, optionally from specified category
     *
     * @param   string  $category
     *
     * @return array
     */
    public function get_all( $category = null ) {
        $icons = $this->use_categories && $category ? $this->icons[ $category ] : $this->icons;
        return apply_filters( "{{plugin_text_domain}}/icons", $icons, $category, $this->use_categories );
    }

    /**
     * Get an icon.
     *
     * @param   string $icon
     * @param   string $category
     *
     * @return  string|null
     * Returns the icon SVG string on success or null if icon or category
     * doesn't exist.
     */
    public function get( $icon, $category = null ) {
        $icon = $this->use_categories ? $this->icons[ $category ][ $icon ] : $this->icons[ $icon ];
        return apply_filters( "{{plugin_text_domain}}/icons/$icon", $this->icons[ $icon ], $category, $this->use_categories );
    }
}
