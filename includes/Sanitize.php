<?php

namespace WP_CLI\Hammer;
use WP_CLI;

class Sanitize {

	protected $dry_run;
	protected $sanitizes;

	function __construct( $sanitizes, $dry_run ) {
		$this->dry_run = $dry_run;
		$this->parse_sanitizes( $sanitizes );
	}

	function get_sanitizes() {
		return $this->sanitizes;
	}

	function parse_sanitizes( $sanitizes ) {
		$this->sanitizes = (array) $sanitizes;
	}

	function run() {
		/**
		 * Any code that needs to be run to setup the pruning.
		 */
		do_action( 'wp_hammer_before_run_sanitizes' );
		WP_CLI::line( "Running content sanitizers" );
		foreach ( $this->sanitizes as $sanitize ) {
			if ( $this->dry_run ) {
				WP_CLI::line( "Dry run sanitize for $sanitize" );
			} else {
				WP_CLI::line( "Sanitize run for table: $sanitize" );
				/**
				 * Any code that needs to be run prior to pruning a table.
				 */
				do_action( 'wp_hammer_run_sanitize_' . $sanitize );
			}

		}
	}
}
