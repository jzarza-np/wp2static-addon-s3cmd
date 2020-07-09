<?php

/**
 * Plugin Name:       WP2Static Add-on: s3cmd deployment
 * Plugin URI:        https://github.com/jzarza-np/wp2static-addon-s3cmd
 * Description:       S3cmd add-on for WP2Static.
 * Version:           0.1.0
 * Author:            Javier Zarza
 * Author URI:        https://norrispalmer.com
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-s3cmd
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_S3CMD_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_S3CMD_VERSION', '1.0-alpha-006' );

if ( file_exists( WP2STATIC_S3CMD_PATH . 'vendor/autoload.php' ) ) {
    require_once WP2STATIC_S3CMD_PATH . 'vendor/autoload.php';
}

function run_wp2static_addon_s3cmd() {
    $controller = new WP2StaticS3cmd\Controller();
    $controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticS3cmd\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticS3cmd\Controller', 'deactivate' ]
);

run_wp2static_addon_s3cmd();

