<?php
/**
 * Theme functions and definitions
 *
 * @package EazyShop
 */

/**
 * After setup theme hook
 */
function eazyshop_theme_setup(){
    /*
     * Make child theme available for translation.
     * Translations can be filed in the /languages/ directory.
     */
    load_child_theme_textdomain( 'eazyshop' );	
}
add_action( 'after_setup_theme', 'eazyshop_theme_setup' );

/**
 * Load assets.
 */

function eazyshop_theme_css() {
	wp_enqueue_style( 'eazyshop-parent-theme-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'eazyshop_theme_css', 99);

require get_stylesheet_directory() . '/theme-functions/controls/class-customize.php';

/**
 * Import Options From Parent Theme
 *
 */
function eazyshop_parent_theme_options() {
	$eazyshop_mods = get_option( 'theme_mods_shopire' );
	if ( ! empty( $eazyshop_mods ) ) {
		foreach ( $eazyshop_mods as $eazyshop_mod_k => $eazyshop_mod_v ) {
			set_theme_mod( $eazyshop_mod_k, $eazyshop_mod_v );
		}
	}
}
add_action( 'after_switch_theme', 'eazyshop_parent_theme_options' );