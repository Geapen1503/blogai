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

$plugin_file = '/blogai/blogai.php';


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

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function blogai_plugin_menu() {

    $capability  = apply_filters( 'blogai_required_capabilities', 'manage_options' );
    $parent_slug = 'blogai_main_menu';


    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'blogai_settings' );

}

function blogai_settings() {
    $css = file_get_contents('../wp-content/plugins/blogai/css/style.css');
    echo '<style>' . $css . '</style>';

}

function blogai_is_active() {
    debug_to_console('Blog AI is installed');

    $servername = 'localhost';
    $username = 'root';
    $password = '';

    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $dbname = 'blogai_db';
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($create_db_sql) === TRUE) {
        debug_to_console('Database created successfully');
    } else {
        debug_to_console('Error creating database: ' . $conn->error);
        $conn->close();
        return;
    }

    $conn->select_db($dbname);

    $create_table_sql = "CREATE TABLE IF NOT EXISTS BlogAI (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        frequency VARCHAR(20) NOT NULL,
        subject VARCHAR(250) NOT NULL,
        description VARCHAR(250),
        withImages BOOLEAN,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($create_table_sql) === TRUE) debug_to_console('Table blogai created successfully');
     else debug_to_console('Error creating table: ' . $conn->error);

    $conn->close();
}





if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
    blogai_is_active();
}




add_action( 'admin_menu', 'blogai_plugin_menu');