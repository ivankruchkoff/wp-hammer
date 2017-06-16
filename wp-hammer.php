<?php
/**
 * Plugin Name: wp hammer
 * Plugin URI: http://example.com
 * Description: This plugin adds a wp-cli ha command to clean your environment and prepare it for staging / development by removing Personally Identifiable Information
 * Version: 1.0.1
 * Author: Ivan Kruchkoff
 * License: BSD
 */

if ( ! defined('WP_CLI') || ! WP_CLI ) {
	return;
}

// If you install via package, the autoloader is already included and doesn't live in the root folder.
if ( ! class_exists('WP_CLI\Hammer\Command') ) {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
	} else {
		WP_CLI::error( "Please, run composer install first" );
	}
}

WP_CLI::add_command( 'ha', 'WP_CLI\Hammer\Command' );
WP_CLI::add_command( 'hammer', 'WP_CLI\Hammer\Command' );

