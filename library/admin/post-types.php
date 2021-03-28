<?php
/**
 * Plugin post types library.
 *
 * Contains overrides for default WordPress post type registration that allows
 * the plugin to track new post types registered from within the plugin. Also
 * contains some helpful functions to retrieve registered post types etc.
 *
 * @package {{plugin_namespace}}
 */

namespace {{plugin_namespace}};

if( ! defined( 'ABSPATH' ) ) wp_die( 'End of Line, Man' );

/**
 * Override the default WP register_post_type function so we can keep
 * track of any post types registered within the scope of the plugin.
 *
 * @param   string          $post_type
 * @param   array|string    $args
 */
function register_post_type( $post_type, $args = array() ) {
    // Using `\` to reference the `register_post_type` function from the global
    // namespace instead of creating some kind of nightmarish register_post_type
    // loopiness...
    $obj = \register_post_type( $post_type, $args );

    // Make sure our post type has registered correctly, then add the post_type
    // reference into the plugin.
    if( $obj && ! is_wp_error( $obj ) ) {
        plugin()->admin()->add_post_type( $post_type );
    }
}

/**
 * Get a list of all post types registered from within this plugin
 */
function get_registered_post_types() {
    return plugin()->admin()->post_types();
}
