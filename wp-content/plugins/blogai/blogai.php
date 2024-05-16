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

    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'create_ui' );

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

function create_ui() {
    include 'public/html/settings.php';

    update_table_html_data();
}


function on_delete_plugin() {
    global $dbname, $conn;

    $delete_base_sql = "DROP DATABASE IF EXISTS $dbname";

    if ($conn->query($delete_base_sql) === TRUE) debug_to_console('Database deleted successfully');
    else debug_to_console('Error deleting database: ' . $conn->error);

    $conn->close();
}


//

/*function custom_cron_schedule() {
    $schedules['every_two_day'] = array(
        'interval' => 172800,
        'display' => __("Every two day")
    );
    return $schedules;
}*/

function custom_cron_schedule() {
    global $conn;

    $query = 'SELECT frequency FROM blogai LIMIT 1';
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $frequency_value = $row['frequency'];

        switch ($frequency_value) {
            case '1d':
                return get_cron_data('every_day', 86400);
            case '3d':
                return get_cron_data('every_three_day', 172800);
            case '1w':
                return get_cron_data('every_week', 604800);
            case '2w':
                return get_cron_data('every_two_week', 1209600);
            case '1m':
                return get_cron_data('every_month', 2419200);
            case '3m':
                return get_cron_data('every_three_month', 7257600);
            default:
                return get_cron_data('every_day', 86400);
        }
    }

    return array();
}

function get_cron_data($name, $interval) {
    $schedules[$name] = array(
        'interval' => $interval,
        'display' => __('Every')
    );
    return $schedules;
}





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


function update_table_html_data() {
    global $frequency_input, $subject_input, $description_input, $conn;

    $check_query = 'SELECT COUNT(*) AS count FROM blogai';
    $check_result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    $row_count = $row['count'];

    if ($row_count > 0) {
        $update_query = 'UPDATE blogai SET frequency = ?, subject = ?, description = ? LIMIT 1';
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $frequency_input, $subject_input, $description_input);
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO blogai(frequency, subject, description) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, "sss", $frequency_input, $subject_input, $description_input);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "";
    } else {
        echo "Error : " . mysqli_error($conn);
    }
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