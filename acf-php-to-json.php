<?php
/**
 * Plugin Name: Convert ACF PHP to JSON
 * Description: Convert Advanced Custom Fields Pro configuration from PHP to JSON.
 */

namespace ConvertAcfPhpToJson;

/**
 * Add submenu item under 'Custom Fields'
 */
function admin_menu() {
    add_submenu_page('edit.php?post_type=acf-field-group', 'Convert PHP fields to JSON', 'PHP to JSON', 'manage_options', 'acf-php-to-json', __NAMESPACE__ . '\\admin_page');
}
add_action('admin_menu', __NAMESPACE__ . '\\admin_menu', 20);

/**
 * Output the admin page
 */
function admin_page() {
    ?>
    <div class="wrap">
        <h1>Convert PHP fields to JSON</h1>
        <?php

        if (!isset($_GET['continue']) || $_GET['continue'] !== 'true') {
            admin_page_intro();
        }
        else {
            admin_page_convert();
        }
        ?>
    </div>
    <?php
}

/**
 * Output the introductory page
 */
function admin_page_intro() {
    $groups = get_groups_to_convert();

    if (empty($groups)) {
        echo '<p>No PHP field group configuration found. Nothing to convert.</p>';
        return;
    }
    else {
        echo sprintf('<p>%d field groups will be converted from PHP to JSON configuration.</p>', count($groups));
        echo '<a href="edit.php?post_type=acf-field-group&page=acf-php-to-json&continue=true" class="button button-primary">Convert Field Groups</a>';
    }
}

/**
 * Convert the field groups and output the conversion page
 */
function admin_page_convert() {
    $groups = acf_get_local_field_groups();
    echo sprintf('<p>Converting %d field groups from PHP to JSON configuration...</p>', count($groups));
    echo '<pre>';
    foreach ($groups as $group) {
        $fields = acf_get_fields($group['key']);
        // Remove unecessary key value pair with key "ID"
        unset($group['ID']);
        // Add the fields as an array to the group
        $group['fields'] = $fields;
        // Add this group to the main array
        $json[] = $group;
    }
    echo '</pre>';
    echo '<p>Done. File saved to your theme directory folder as acf-import.json</p>';

    $json = json_encode($json, JSON_PRETTY_PRINT);
    // Optional - echo the JSON data to the page
    echo "<pre>";
    echo $json;
    echo "</pre>";

    // Write output to file for easy import into ACF.
    // The file must be writable by the server process. In this case, the file is located in
    // the current theme directory.
    $file = get_template_directory() . '/acf-import.json';
    file_put_contents($file, $json);
}

/**
 * Get the PHP field groups which will be converted.
 *
 * @return array
 */
function get_groups_to_convert() {
    $groups = acf_get_local_field_groups();
    if (!$groups) return [];
    return array_filter($groups, function($group) {
        return $group['local'] == 'php';
    });
}
