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

global $wpdb;


function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}



//////// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ ////////




function blogai_plugin_menu() {
    $capability  = apply_filters( 'blogai_required_capabilities', 'manage_options' );
    $parent_slug = 'blogai_main_menu';

    add_menu_page( esc_html__( 'Blog Ai', 'blog-ai' ), esc_html__( 'BLOG AI', 'blog-ai' ), $capability, $parent_slug, 'create_ui');

}

function create_ui() {
    include 'public/html/settings.php';

    update_table_html_data();
}



// // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% // //

function blogai_is_active() {
    global $wpdb;
    debug_to_console('Blog AI is installed');

    create_blogai_table();
}

function create_blogai_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $create_table_sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        frequency VARCHAR(3) NOT NULL CHECK (frequency REGEXP '^(1d|3d|1w|2w|1m|3m)$'),
        subject VARCHAR(250) NOT NULL,
        description VARCHAR(250),
        withImages BOOLEAN
    )";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $create_table_sql );

    debug_to_console('Table blogai created successfully');
}

function on_delete_plugin() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $delete_table_sql = "DROP TABLE IF EXISTS $table_name";

    $wpdb->query($delete_table_sql);

    debug_to_console('Table blogai deleted successfully');
}

function update_table_html_data() {
    global $frequency_input, $subject_input, $description_input, $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $check_query = "SELECT COUNT(*) AS count FROM $table_name";
    $row_count = $wpdb->get_var($check_query);

    if ($row_count > 0) {
        $update_query = "UPDATE $table_name SET frequency = %s, subject = %s, description = %s LIMIT 1";
        $wpdb->query($wpdb->prepare($update_query, $frequency_input, $subject_input, $description_input));
    } else {
        $wpdb->insert($table_name, array(
            'frequency' => $frequency_input,
            'subject' => $subject_input,
            'description' => $description_input
        ));
    }
}



// // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% // //



// [-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-] //

function custom_cron_schedule() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $query = "SELECT frequency FROM $table_name";
    $results = $wpdb->get_results($query);

    $schedules = array();

    if ($results) {
        foreach ($results as $row) {
            $frequency_value = $row->frequency;

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
    global $wpdb;

    $schedules = custom_cron_schedule();

    $table_name = $wpdb->prefix . 'blogai';
    $query = "SELECT COUNT(*) AS count FROM $table_name";
    $row_count = $wpdb->get_var($query);

    if ($row_count > 0 && !empty($schedules)) {
        $first_schedule = key($schedules);

        if (!wp_next_scheduled('cron_text_to_console')) {
            wp_schedule_event(time(), $first_schedule, 'cron_text_to_console');
        }
    }
}


register_deactivation_hook(__FILE__, 'on_unactive');
function on_unactive() {
    wp_clear_scheduled_hook("cron_text_to_console");
}

function update_schedule_event() {
    //wp_clear_scheduled_hook("cron_text_to_console");
    global $wpdb;

    $schedules = custom_cron_schedule();

    $table_name = $wpdb->prefix . 'blogai';
    $query = "SELECT COUNT(*) AS count FROM $table_name";
    $row_count = $wpdb->get_var($query);

    if ($row_count > 0 && !empty($schedules)) {
        $first_schedule = key($schedules);
        $recurrence = wp_get_schedule('cron_text_to_console');

        if ($first_schedule != $recurrence) {
            wp_clear_scheduled_hook("cron_text_to_console");
            if (!wp_next_scheduled('cron_text_to_console')) {
                wp_schedule_event(time(), $first_schedule, 'cron_text_to_console');
            }
        }
    }
}


// [-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-]~[-] //



//
////
//////
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
//////
////
//





// // // ------------------- // // //

function check_if_active() {
    if (is_plugin_active('blogai/blogai.php')) {
        blogai_is_active();
    }
}





// <><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><> //

add_action('admin_init', 'check_if_active');
add_action('admin_menu', 'blogai_plugin_menu');
register_uninstall_hook(__FILE__, 'on_delete_plugin');

add_action('init', 'update_schedule_event');
add_filter('cron_schedules', 'custom_cron_schedule');
add_action('cron_text_to_console', 'generate_post');

// <><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><><> //