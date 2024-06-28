# BlogAi WP plugin Documentation

## The files you'll find in the WP plugin blogai directory
<br>

- ./blogai.php this is the main script you will find all the functions in it. <br>
- ./public/html/settings.php this is the html ui, everything in it is pretty much self explaining. If it's a php file it's for extracting the data from the fields. <br>
- ./public/docs/documentation.md this is this file. <br>
- ./public/css/style.css useless, the css is already in html/php ui.<br>
- ./public/img classic img directory if you need to add a logo or something.<br>

<br><br><br>


## blogai.php functions explained

<br>
debug_to_console(); Sends debugging information to the browser's console for easier debugging.
<br>
blogai_plugin_menu(); Adds a menu item for the Blog AI plugin in the WordPress admin dashboard.
<br>
create_ui(); Includes the settings page for the plugin and updates the table with HTML data.
<br>
blogai_is_active(); Checks if the Blog AI plugin is active, initializes debugging, and calls functions to create tables and add data.
<br>
create_blogai_table(); Creates a custom table in the WordPress database to store plugin-specific data.
<br>
on_delete_plugin(); Deletes the custom table when the plugin is uninstalled.
<br>
update_table_html_data(); Updates the custom table with form input values or inserts new data if the table is empty.
<br>
custom_cron_schedule(); Defines custom cron schedules based on the frequency values stored in the database.
<br>
get_cron_data($name, $interval); Returns cron schedule data for a given name and interval.
<br>
on_active(); Actions performed when the plugin is activated, such as creating the table and scheduling events.
<br>
on_unactive(); Clears scheduled events when the plugin is deactivated.
<br>
update_schedule_event(); Updates the schedule for cron events based on the frequency values in the database.
<br>
send_post_request(); Sends a POST request to the API to generate blog content based on stored parameters.
<br>
add_data_to_wp_posts(); Adds generated content as a new WordPress post, with an option to include custom CSS and images.
<br>
generate_post(); Generates a blog post by calling add_data_to_wp_posts().
<br>
check_if_active(); Checks if the plugin is active and calls blogai_is_active() if it is.
<br>

## blogai.php action and filter hooks explained

<br>
add_action('admin_init', 'check_if_active'): Checks if the plugin is active during the admin initialization.
<br>
add_action('admin_menu', 'blogai_plugin_menu'): Adds the plugin menu to the admin dashboard.
<br>
register_uninstall_hook(__FILE__, 'on_delete_plugin'): Registers the uninstall hook for the plugin.
<br>
add_action('init', 'update_schedule_event'): Updates the cron schedule on initialization.
<br>
add_filter('cron_schedules', 'custom_cron_schedule'): Adds custom cron schedules.
<br>
add_action('cron_text_to_console', 'generate_post'): Schedules the generate_post function to run at defined intervals.
<br>

