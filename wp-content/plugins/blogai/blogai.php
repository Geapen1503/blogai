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

$plugin_file = 'blogai/blogai.php';

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'blogai_db';
$conn = new mysqli($servername, $username, $password);



function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function blogai_plugin_menu() {

    $capability  = apply_filters( 'blogai_required_capabilities', 'manage_options' );
    $parent_slug = 'blogai_main_menu';


    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'blogai_settings' );

}

/*function blogai_settings() {
    $css = file_get_contents('../wp-content/plugins/blogai/css/style.css');
    echo '<style>' . $css . '</style>';

}*/

function blogai_is_active() {
    global $conn;
    debug_to_console('Blog AI is installed');

    /*if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }*/

    create_blogai_base();
    create_blogai_table();

    //$conn->close();
}

function create_blogai_base() {
    global $dbname, $conn;

    $create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($create_db_sql) === TRUE) {
        debug_to_console('Database created successfully');
    } else {
        debug_to_console('Error creating database: ' . $conn->error);
        $conn->close();
    }
}

function create_blogai_table() {
    global $dbname, $conn;

    $conn->select_db($dbname);

    $create_table_sql = "CREATE TABLE IF NOT EXISTS BlogAI (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        frequency VARCHAR(3) NOT NULL CHECK (frequency REGEXP '^(1d|3d|1w|2w|1m|3m)$'),
        subject VARCHAR(250) NOT NULL,
        description VARCHAR(250),
        withImages BOOLEAN
    )";


    if ($conn->query($create_table_sql) === TRUE) {
        debug_to_console('Table blogai created successfully');
    } else {
        debug_to_console('Error creating table: ' . $conn->error);
    }

}

function on_delete_plugin() {
    global $dbname, $conn;

    $delete_base_sql = "DROP DATABASE IF EXISTS $dbname";

    if ($conn->query($delete_base_sql) === TRUE) debug_to_console('Database deleted successfully');
    else debug_to_console('Error deleting database: ' . $conn->error);


    $conn->close();
}


// beginning of hook part

function custom_cron_schedule() {
    // do not forget to add a switch case that translate every frequency in the database to a proper schedule.

    $schedules['every_day'] = array(
        'interval' => 86400,
        'display' => __("Every day")
    );

    return $schedules;
}

function cron_text_to_console() {
    debug_to_console("Le cron vient d'être exécuter");
}


// ending of the hook part

function on_active() {
    if (!wp_next_scheduled('send_mail_hook')) {
        wp_schedule_event(time(), '60-seconds', 'send_mail_hook');
    }
}

function on_unactive() {
    wp_clear_scheduled_hook("send_mail_hook");
}


function check_if_active() {
    if (is_plugin_active('blogai/blogai.php')) {
        blogai_is_active();
    }
}





add_action( 'admin_init', 'check_if_active');
add_action( 'admin_menu', 'blogai_plugin_menu');


register_activation_hook(__FILE__, 'on_active');
register_deactivation_hook(__FILE__, 'on_unactive');
add_filter('cron_schedules', 'custom_cron_schedule');
add_action('send_mail_hook', 'cron_text_to_console');

//register_deactivation_hook(__FILE__, 'on_delete_plugin');
register_uninstall_hook(__FILE__, 'on_delete_plugin');
