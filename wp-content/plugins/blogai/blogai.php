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

function blogai_is_active() {
    global $conn;
    debug_to_console('Blog AI is installed');

    create_blogai_base();
    create_blogai_table();

    // $conn->close();
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

//

function custom_cron_schedule() {
    $schedules['every_two_day'] = array(
        'interval' => 172800,
        'display' => __("Every two day")
    );
    return $schedules;
}

// Dunno if it works but useful for later
/*function custom_cron_schedule() {
    global $conn;

    $sql_get_fre = "SELECT frequency FROM blogai";

    $result = $conn->query($sql_get_fre);

    switch ($sql_get_fre) {
        case $sql_get_fre == '1d';
            custom_cron_schedule_data('every_day', 86400);
            break;
        case $sql_get_fre == '3d';
            custom_cron_schedule_data('every_three_days', 259200);
            break;
    }
}

function custom_cron_schedule_data($name, $interval) {
    $schedules['$name'] = array(
        'interval' => $interval,
        'display' => __("$name")
    );
    return $schedules;
}*/


register_activation_hook(__FILE__, 'on_active');
function on_active() {
    if (!wp_next_scheduled('cron_text_to_console')) {
        wp_schedule_event(time(), 'every_two_day', 'cron_text_to_console');
    }
}

register_deactivation_hook(__FILE__, 'on_unactive');
function on_unactive() {
    wp_clear_scheduled_hook("cron_text_to_console");
}


function generate_post() {
    $to = 'theogilat@gmail.com';
    $subject = 'Test Email';
    $message = 'This is a test email sent from WordPress using PHP.';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $additional_headers = implode("\r\n", $headers);

    $result = wp_mail($to, $subject, $message, $additional_headers);

    if ($result) debug_to_console('Email sent successfully');
    else debug_to_console('Email sent failed');
}


function check_if_active() {
    if (is_plugin_active('blogai/blogai.php')) {
        blogai_is_active();
    }
}




add_action('admin_init', 'check_if_active');
add_action('admin_menu', 'blogai_plugin_menu');
register_uninstall_hook(__FILE__, 'on_delete_plugin');

add_filter('cron_schedules', 'custom_cron_schedule');
add_action('cron_text_to_console', 'generate_post');