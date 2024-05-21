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

    //get_api_data();

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
        withImages BOOLEAN,
        sketch_input BOOLEAN DEFAULT TRUE
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
    global $frequency_input, $subject_input, $description_input, $sketch_input, $wpdb;

    $table_name = $wpdb->prefix . 'blogai';

    $check_query = "SELECT COUNT(*) AS count FROM $table_name";
    $row_count = $wpdb->get_var($check_query);

    if ($row_count > 0) {
        $update_query = "UPDATE $table_name SET frequency = %s, subject = %s, description = %s, sketch_input = %d LIMIT 1";
        $wpdb->query($wpdb->prepare($update_query, $frequency_input, $subject_input, $description_input, $sketch_input));
    } else {
        $wpdb->insert($table_name, array(
            'frequency' => $frequency_input,
            'subject' => $subject_input,
            'description' => $description_input,
            'sketch_input' => $sketch_input
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

function make_api_link() {
    $api_url = 'https://api.sampleapis.com/switch/games/1';
    $response = wp_remote_get($api_url);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // return $data;

        $json = '{
        "title": "Quels sont tous les types de sites internet et quels sont les avantages et inconvénients de chacun",
        "content": "<h1>Quels sont tous les types de sites internet et quels sont les avantages et inconvénients de chacun</h1> <h2>Introduction</h2> <p>Avec la prolifération des technologies numériques, les sites internet sont devenus des outils indispensables pour les entreprises et les particuliers. Il existe différents types de sites web, chacun ayant ses propres avantages et inconvénients. Cet article explore ces différents types de sites pour vous aider à choisir celui qui correspond le mieux à vos besoins.</p> <h2>Types de sites internet</h2> <h3>Sites vitrines</h3> <p>Les sites vitrines sont des sites statiques conçus principalement pour présenter une entreprise, ses produits ou ses services. Ils servent de carte de visite en ligne.</p> <h4>Avantages</h4> <ul> <li>Coût de développement et de maintenance relativement bas.</li> <li>Facilité de navigation et de mise en place.</li> <li>Bonne visibilité en ligne pour les petites entreprises.</li> </ul> <h4>Inconvénients</h4> <ul> <li>Fonctionnalités limitées.</li> <li>Interaction utilisateur restreinte.</li> <li>Peut rapidement devenir obsolète sans mises à jour régulières.</li> </ul> <h3>Sites e-commerce</h3> <p>Les sites e-commerce permettent de vendre des produits ou services en ligne. Ils incluent des fonctionnalités comme des paniers d\'achat, des systèmes de paiement en ligne, et des outils de gestion des stocks.</p> <h4>Avantages</h4> <ul> <li>Possibilité de toucher un large public mondial.</li> <li>Augmentation des ventes grâce à la disponibilité 24/7.</li> <li>Outils analytiques pour suivre et optimiser les performances.</li> </ul> <h4>Inconvénients</h4> <ul> <li>Coût de développement et de maintenance élevé.</li> <li>Nécessité de sécuriser les transactions et les données clients.</li> <li>Compétition féroce avec d\'autres sites e-commerce.</li> </ul> <h3>Blogs</h3> <p>Les blogs sont des sites où les individus ou les entreprises publient régulièrement des articles ou des billets sur divers sujets. Ils sont souvent utilisés pour partager des opinions, des nouvelles, ou des conseils.</p> <h4>Avantages</h4> <ul> <li>Facilité de création et de gestion.</li> <li>Amélioration du référencement naturel (SEO).</li> <li>Interaction avec les lecteurs à travers les commentaires.</li> </ul> <h4>Inconvénients</h4> <ul> <li>Nécessité de créer régulièrement du contenu de qualité.</li> <li>Peut nécessiter beaucoup de temps et d\'efforts pour fidéliser une audience.</li> <li>Potentiel de monétisation variable.</li> </ul> <h3>Sites communautaires</h3> <p>Les sites communautaires permettent aux utilisateurs de se connecter, de partager des informations et d\'interagir autour de centres d\'intérêt communs. Les réseaux sociaux sont des exemples populaires de sites communautaires.</p> <h4>Avantages</h4> <ul> <li>Grande interaction et engagement des utilisateurs.</li> <li>Création d\'une communauté fidèle.</li> <li>Opportunités de monétisation à travers la publicité et les abonnements.</li> </ul> <h4>Inconvénients</h4> <ul> <li>Nécessité d\'une modération active pour éviter les abus.</li> <li>Coût de maintenance et d\'hébergement élevé en cas de fort trafic.</li> <li>Problèmes potentiels de confidentialité et de sécurité des données.</li> </ul> <h3>Sites éducatifs</h3> <p>Les sites éducatifs fournissent des ressources et des informations pédagogiques. Ils peuvent offrir des cours en ligne, des tutoriels, des livres électroniques, et d\'autres matériaux d\'apprentissage.</p> <h4>Avantages</h4> <ul> <li>Accès à des ressources éducatives de qualité partout dans le monde.</li> <li>Flexibilité d\'apprentissage pour les utilisateurs.</li> <li>Possibilité de monétisation à travers les abonnements et les ventes de cours.</li> </ul> <h4>Inconvénients</h4> <ul> <li>Coût élevé de développement et de production de contenu.</li> <li>Nécessité de maintenir les informations à jour.</li> <li>Compétition avec de nombreuses autres plateformes éducatives.</li> </ul> <h2>Conclusion</h2> <p>Choisir le bon type de site internet dépend de vos objectifs, de votre budget et de votre public cible. Que vous souhaitiez présenter votre entreprise, vendre des produits, partager vos connaissances ou créer une communauté, il existe un type de site web qui répondra à vos besoins spécifiques. En pesant soigneusement les avantages et les inconvénients de chaque type de site, vous pourrez faire un choix éclairé qui maximisera votre présence en ligne.</p>"
        }';

        return $json;
    } else {

        return is_wp_error( $response ) ? $response->get_error_code() : wp_remote_retrieve_response_code( $response );
    }
}

function get_api_data() {
    $result = make_api_link();

    if (is_array($result)) {
        debug_to_console($result['name'] . '\n' . $result['developers'][0]);
        return $result['content'];
    } else {
        debug_to_console('ERROR CANNOT ESTABLISH LINK WITH API');
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
        $post_title = $data['title'];
        $post_content = $data['content'];

        $new_post = array(
            'post_title'    => $post_title,
            'post_content'  => $post_content,
            'post_status'   => $status_for_post,
            'post_author'   => get_current_user_id(),
            'post_date'     => current_time('mysql'),
            'post_type'     => 'post'
        );

        $post_id = wp_insert_post($new_post);

        if (!is_wp_error($post_id)) {
            debug_to_console('Post added successfully with ID: ' . $post_id);
        } else {
            debug_to_console('Failed to add post: ' . $post_id->get_error_message());
        }
    } else {
        debug_to_console('Failed to decode JSON: ' . json_last_error_msg());
    }
}


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