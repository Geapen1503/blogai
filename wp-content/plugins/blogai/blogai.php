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
$password = 'root';
$dbname = 'blogai_db';
$conn = new mysqli($servername, $username, $password);
$api_url = 'localhost:3000/';

global $wpdb, $user_id;


function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";

    //error_log('Debug Objects: ' . $output);
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

    //get_api_data();

    create_blogai_table();
    //add_data_to_wp_posts();
}

function create_blogai_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $create_table_sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        frequency VARCHAR(3) NOT NULL CHECK (frequency REGEXP '^(1d|3d|1w|2w|1m|3m)$'),
        description VARCHAR(250),
        withImages BOOLEAN DEFAULT FALSE,
        sketch_input BOOLEAN DEFAULT TRUE,
        generate_now BOOLEAN DEFAULT FALSE,
        user_api_key VARCHAR(255) NOT NULL
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($create_table_sql);


    if (is_array($result) && !empty($result)) debug_to_console('Table blogai created successfully');
    else debug_to_console('Failed to create table blogai: ' . print_r($result, true));
}



function on_delete_plugin() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $delete_table_sql = "DROP TABLE IF EXISTS $table_name";

    $wpdb->query($delete_table_sql);

    debug_to_console('Table blogai deleted successfully');
}


function update_table_html_data() {
    global $frequency_input, $description_input, $sketch_input, $w_img_input, $gen_now_input, $user_api_key, $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $check_query = "SELECT COUNT(*) AS count FROM $table_name";
    $row_count = $wpdb->get_var($check_query);

    if ($row_count > 0) {
        $update_query = "UPDATE $table_name SET frequency = %s, description = %s, withImages = %d, sketch_input = %d, generate_now = %d, user_api_key = %s LIMIT 1";
        $wpdb->query($wpdb->prepare($update_query, $frequency_input, $description_input, $w_img_input, $sketch_input, $gen_now_input, $user_api_key));
    } else {
        $wpdb->insert($table_name, array(
            'frequency' => $frequency_input,
            'description' => $description_input,
            'withImages' => $w_img_input,
            'sketch_input' => $sketch_input,
            'generate_now' => $gen_now_input,
            'user_api_key' => $user_api_key
        ));
    }

    debug_to_console('Table blogai updated successfully');
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

    create_blogai_table();

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


// ///// // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ // ///// //


function add_data_to_wp_posts() {
    
}



/*function make_api_link() {
    global $wpdb, $api_url;

    $table_name = $wpdb->prefix . 'blogai';
    $query = $wpdb->prepare("SELECT description, withImages, user_api_key FROM $table_name LIMIT 1");
    $result = $wpdb->get_row($query, ARRAY_A);

    if (!$result) {
        debug_to_console('No data found in the database.');
        return '';
    }

    $description = $result['description'];
    $withImages = (bool)$result['withImages'];
    $user_api_key = $result['user_api_key'];

    $data = [
        'description' => $description,
        'includeImages' => $withImages,
        'numImages' => 1,
        'maxTokens' => 1200,
        'gptModel' => "GPT3_5",
        'apiKey' => $user_api_key,
    ];

    $json_data = json_encode($data);
    $api_endpoint = $api_url . 'blog/generate';

    $response = send_json_request($api_endpoint, $json_data);

    if ($response) {
        if (is_array($response)) $decoded_response = $response;
        else $decoded_response = json_decode($response, true);


        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded_response);
        } else {
            debug_to_console('Failed to decode JSON from API response: ' . json_last_error_msg());
            return '';
        }
    } else {
        debug_to_console('No response from API');
        return '';
    }
}


function add_data_to_wp_posts() {
    global $wpdb;


    $table_name = $wpdb->prefix . 'blogai';
    $query = "SELECT sketch_input FROM $table_name";
    $row_count = $wpdb->get_var($query);

    $status_for_post = $row_count ? 'publish' : 'draft';
    $json_data = make_api_link();
    $data = json_decode($json_data, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $post_content = $data['article'];
        //$post_title = $data['title'];

        $new_post = array(
            'post_title' => 'Article',
            'post_content'  => $post_content,
            'post_status'   => $status_for_post,
            'post_author'   => get_current_user_id(),
            'post_date'     => current_time('mysql'),
            'post_type'     => 'post'
        );

        $post_id = wp_insert_post($new_post);

        if (!is_wp_error($post_id)) debug_to_console('Post added successfully with ID: ' . $post_id);
        else debug_to_console('Failed to add post: ' . $post_id->get_error_message());
    } else {
        debug_to_console('Failed to decode JSON: ' . json_last_error_msg());
    }
}


function send_json_request($url, $data) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return 'cURL error: ' . $error_msg;
    }

    curl_close($ch);
    return array('response' => $response, 'httpcode' => $httpcode);
}
*/



// ///// // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ // ///// //




//
////
//////
function generate_post() {
    add_data_to_wp_posts();

    /*$to = 'theogilat@gmail.com';
    $subject = 'Test Email';
    $message = 'This is a test email sent from WordPress using PHP.';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $additional_headers = implode("\r\n", $headers);

    $result = wp_mail($to, $subject, $message, $additional_headers);

    if ($result) debug_to_console('Email sent successfully');
    else debug_to_console('Email sent failed');*/
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
