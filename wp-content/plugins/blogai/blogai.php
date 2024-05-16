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

    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'create_ui');

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

    $query = 'SELECT frequency FROM blogai';
    $result = mysqli_query($conn, $query);

    $schedules = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $frequency_value = $row['frequency'];

            switch ($frequency_value) {
                case '1d':
                    $schedules['every_day'] = get_cron_data('every_day', 86400);
                    break;
                case '3d':
                    $schedules['every_three_day'] = get_cron_data('every_three_day', 172800);
                    break;
                case '1w':
                    $schedules['every_week'] = get_cron_data('every_week', 604800);
                    break;
                case '2w':
                    $schedules['every_two_week'] = get_cron_data('every_two_week', 1209600);
                    break;
                case '1m':
                    $schedules['every_month'] = get_cron_data('every_month', 2419200);
                    break;
                case '3m':
                    $schedules['every_three_month'] = get_cron_data('every_three_month', 7257600);
                    break;
            }
        }
    }

    return $schedules;
}


function get_cron_data($name, $interval) {
    return array(
        'interval' => $interval,
        'display' => __($name)
    );
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

    $query = 'INSERT INTO blogai(frequency, subject, description) VALUES (?, ?, ?)';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $frequency_input, $subject_input, $description_input);

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