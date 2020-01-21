<?php

namespace WP_CLI\Hammer;
use WP_CLI;

class Sanitize {

	protected $dry_run;
	protected $sanitizers;

	function __construct( $sanitizers, $dry_run ) {
		$this->dry_run = $dry_run;
		$this->parse_sanitizers( $sanitizers );
	}

	function get_sanitizers() {
		return $this->sanitizers;
	}

	function parse_sanitizers( $sanitizers ) {
		$this->sanitizers = (array) $sanitizers;
	}

	function run() {
		/**
		 * Any code that needs to be run to setup the pruning.
		 */
		do_action( 'wp_hammer_before_run_sanitizers' );
		WP_CLI::line( "Running content sanitizers" );
		foreach ( $this->sanitizers as $sanitizer ) {
			if ( $this->dry_run ) {
				WP_CLI::line( "Dry run sanitize for $sanitizer" );
			} else {
				WP_CLI::line( "Sanitize run for table: $sanitizer" );
				/**
				 * Any code that needs to be run prior to pruning a table.
				 */
				do_action( 'wp_hammer_run_sanitize_' . $sanitizer );
			}

		}
	}
}
