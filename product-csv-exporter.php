<?php
/**
 * Plugin Name: Product CSV Exporter
 */

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


add_action( 'admin_menu', 'custom_csv_exporter_products' );

function custom_csv_exporter_products()
{
    add_menu_page(
        'CSV Exporter',     // page title
        'CSV Exporter',     // menu title
        'manage_options',   // capability
        'csv-exporter',     // menu slug
        'csv_exporter_template' // callback function
    );
}

function csv_exporter_template()
{
    global $title;

    print '<div class="wrap">';
    print "<h1>$title</h1>";

    $file = plugin_dir_path( __FILE__ ) . "/inc/product-template.php";

    if ( file_exists( $file ) )
        require $file;

        print "<p style='text-align: center;' class='description'>Created with love by bitcraftx</p>";

        print '</div>';
}