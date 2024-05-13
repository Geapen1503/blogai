<?php
/**
 * @package Blog_AI
 * @version 1.0.0
 */
/*
Plugin Name: Blog AI
Plugin URI: http://localhost
Description: Un plugin qui génère des articles de blog de manière totalement automatisée.
Author: Theo GILABERT
Version: 1.0.0
Author URI: http://localhost
*/


/* function administration_add_admin_page() {
    add_submenu_page(
        'options-general.php',
        'Mes options',
        'Blog AI',
        'manage_options',
        'administration',
        'administration_page'
    );
}

add_action('admin_menu', 'administration_add_admin_page'); */

function blogai_plugin_menu() {

    $capability  = apply_filters( 'blogai_required_capabilities', 'manage_options' );
    $parent_slug = 'blogai_main_menu';


    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'blogai_settings' );

}

add_action( 'admin_menu', 'blogai_plugin_menu');


function blogai_settings() {
    $css = file_get_contents('../wp-content/plugins/blogai/css/style.css');
    echo '<style>' . $css . '</style>';


}