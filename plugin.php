<?php
/**
 * WordPress Plugin Template by Direwolf Design
 *
 * PHP version 7
 *
 * @package     DirewolfPluginTemplate
 * @author      Direwolf Design <developers@direwolfdesign.co>
 * @version     0.1.0-alpha
 * @link        https://github.com/DirewolfDesign/direwolf-plugin-template
 */

/**
 * @wordpress-header
 * Plugin Name: {{plugin_name}}
 * Description: {{plugin_description}}
 * Version:     {{plugin_version}}
 * Author:      {{plugin_author}} <{{plugin_author_email}}>
 * Author URI:  {{plugin_author_uri}}
 * License:     {{plugin_license}}
 * License URI: {{plugin_license_uri}}
 * Text Domain: {{plugin_text_domain}}
 */

namespace {{plugin_namespace}};

if( ! defined( 'ABSPATH' ) ) wp_die( 'End of Line, Man' );

if( ! class_exists( '{{plugin_namespace}}' ) ) :

    /**
     * Main Plugin Class
     */
     class {{plugin_namespace}} {

         /**
          * The single class instance.
          * @var null
          */
         private static $instance = null;

         /**
          * Main Instance
          * Ensures only one instance of this class exists in memory at any one time.
          */
         public static function instance() {
             if ( is_null( self::$instance ) ) {
                 self::$instance = new self();
                 self::$instance->init();
             }
             return self::$instance;
         }

         /**
          * The base path to the plugin in the file system.
          * @var string
          */
         public $plugin_path;

         /**
          * URL Link to plugin
          * @var string
          */
         public $plugin_url;

         /**
          * An array of dynamic methods created when loading in
          * sub-classes to make calling them externally easier.
          *
          * Note: Should not be called directly, the __call magic method will
          * handle calling of dynamic methods.
          *
          * @var array
          */
         private $plugin_methods = array();

         /**
          * {{plugin_namespace}} constructor.
          */
         public function __construct() {
             /* We do nothing here! */
         }

         /**
          * Method call override
          * Allows us to call dynamically added class methods as if they were
          * predefined, so we can add some methods for calling subclasses
          */
         public function __call( $method, $params ) {
             // If a callable method exists, just go ahead and run it...
             if( method_exists( $this, $method ) ) return $this->method( $params );

             // Otherwise, if we have a dynamic method listed we'll call that...
             if( ! is_null( $this->plugin_methods[ $method ] ) )
                return $this->plugin_methods[ $method ]( $params );

             // Error out here if a method doesn't exist...
             wp_die( "Error: No such method `$method` in class `" . static::class . "`" );
         }

         /**
          * Activation Hook
          * Most commonly used to flush permalinks etc. when a plugin registers
          * any Custom Post Types to avoid any 404 errors.
          *
          * @see https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/#example
          */
         public function activation_hook() {
             // Fire an action to allow other methods to hook in and run stuff
             // when this plugin is activated...
             do_action( '{{plugin_text_domain}}/activation_hook' );
         }

         /**
          * Deactivation Hook
          * Most commonly used to unregister any custom post types and flush the
          * permalinks to avoid any 404 errors.
          *
          * @see https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/#example
          */
         public function deactivation_hook() {
             // Fire an action to allow other methods to hook in and run stuff
             // when this plugin is deactivated...
             do_action( '{{plugin_text_domain}}/deactivation_hook' );
         }

         /**
          * Init.
          */
         public function init() {
             $this->plugin_path = plugin_dir_path( __FILE__ );
             $this->plugin_url  = plugin_dir_url( __FILE__ );

             $this->load_text_domain();
             $this->include_dependencies();

             // Fire an action to allow other methods to hook in and run stuff
             // when this plugin is initialized...
             do_action( '{{plugin_text_domain}}/init' );
         }

         /**
          * Get plugin_path.
          */
         public function plugin_path() {
             // Fire a filter to allow other methods to hook in and modify the
             // path to this plugin...
             return apply_filters( '{{plugin_text_domain}}/plugin_path', $this->plugin_path );
         }

         /**
          * Get plugin_url.
          */
         public function plugin_url() {
             // Fire a filter to allow other methods to hook in and modify the
             // url to this plugin...
             return apply_filters( '{{plugin_text_domain}}/plugin_url', $this->plugin_url );
         }

         /**
          * Sets the text domain with the plugin translated into other languages.
          */
         public function load_text_domain() {
             load_plugin_textdomain( '{{plugin_text_domain}}', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
         }

         public function load_class( $classname, $filename = false, $params = null, $prop = true ) {
             if( ! $filename ) $filename = strtolower( str_replace( '_', '-', $classname ) );

             // Check for existence of the class file...
             if( file_exists( $this->plugin_path() . "/classes/class-$filename.php" ) ) :
                 // Require the class file.
                 // Note: Class filenames should be in the format `class-$filename.php`
                 // and be stored in the `/classes` directory.
                 require_once $this->plugin_path() . "/classes/class-$filename.php";

                 // If the property name is set to a string, go ahead and set the
                 // property. We'll assume the developer knows that properties can
                 // only contain letters & underscores...
                 if( is_string( $prop ) ) {
                     $this->$prop = new $classname( $params );
                     // Create an anonymous function that returns the class instance
                     $this->methods[ $prop ] = function() { return $this->$prop; }
                 }

                 // If $prop is `true`, generate a property name from the provided
                 // $classname parameter.
                 elseif( $prop ) {
                     // We can safely use the lower case classname as the property
                     // name as classes can only contain underscores, not dashes
                     // or spaces.
                     $propname = strtolower( $classname );
                     $this->$propname = new $classname( $params );
                     // Create an anonymous function that returns the class instance
                     $this->methods[ $propname ] = function() { return $this->$propname; }
                 }
             endif;
         }

         /**
          * Set plugin Dependencies.
          */
         private function include_dependencies() {
             $this->load_class( 'Admin' );
             $this->load_class( 'Icons' );
             $this->load_class( 'Controls' );
             $this->load_class( 'Blocks' );
             $this->load_class( 'Templates' );
             $this->load_class( 'Tools' );
             $this->load_class( 'Rest' );
             $this->load_class( 'Force_Gutenberg' );

             // Fire an action to allow other methods to hook in and load in
             // additional dependencies
             do_action( '{{plugin_text_domain}}/load_dependencies' );
         }

     }

     /**
      * The main cycle of the plugin.
      *
      * Note: To use any reference to the plugin from outside the plugin_namespace
      * namespace, use the following statement at the top of your file:
      *
      *     use function {{plugin_namespace}}\plugin as {{plugin_namespace}};
      *
      * You can then invoke the plugin instance using `{{plugin_namespace}}()` and
      * access any public methods from the global namespace.
      * @return null|{{plugin_namespace}}
      */
     function plugin() {
         return {{plugin_namespace}}::instance();
     }

     // Initialize the plugin...
     plugin();

     // Register the activation and deactivation hooks...
     register_activation_hook( __FILE__, array( plugin(), 'activation_hook' ) );
     register_deactivation_hook( __FILE__, array( plugin(), 'deactivation_hook' ) );

endif;
