<?php
namespace WP_CLI\Hammer;

use WP_CLI;

class Settings {

	/**
	 * @var array of tables and columns which should have their content modified, and HOW it should be modified.
	 * Specified via -f parameter and comma-separated.
	 * e.g. array(
	 *   "posts.post_author=random", // randomly assign post author to each post
	 *   "posts.post_title=ipsum", // Use lorem ipsum to generate post titles
	 *   "user_pass=auto", // Auto generate user passwords for all remaining users
	 * );
	 * The table content formatters are specified in the /generators/ folder, /generators/users.php will handle users table etc.
	 *
	 */
	public $formats;

	/**
	 * @var array of tables, how many rows should remain in them, and (optional) how we should determine which rows to keep.
	 * These are specified via -l parameter.
	 *
	 * e.g. array(
	 *   "users=5", // keep 5 rows of users table
	 *   "posts=100.post_date", // keep 100 posts, using the latest post_date to determine which remain, and which go.
	 * );
	 * These limits are processed by the pruners, pruners/users.php will handle the users table etc.
	 */
	public $limits;

	/**
	 * @var array of tables to sanitize
	 * These are specified via -s parameter.
	 *
	 * e.g. array(
	 *   "options",
	 *   "usermeta",
	 * );
	 * These tables are processed by the sanitizers, sanitizers/options.php will handle the options table etc.
	 */
	public $sanitizes;

	/**
	 * @var bool whether or not we make changes to the database or simply do a dry run.
	 */
	public $dry_run = false;

	/**
	 * Arguments parser for all supplied arguments
	 * @param $args
	 * @param $assoc_args
	 */
	function parse_arguments( $args, $assoc_args ) {
		while ( count( $args ) ) {
			$arg = array_shift( $args );
			switch ( $arg ) {
				case '-f':
					$this->parse_argument( $args, 'formats' );
					break;
				case '-l':
					$this->parse_argument( $args, 'limits' );
					break;
				case '-s':
					$this->parse_argument( $args, 'sanitizes' );
					break;
			}
		}

		$this->dry_run = ! empty( $assoc_args[ 'dry-run' ] );
	}

	/**
	 * Parse an arg for an individual property, if it exists.
	 *
	 * @param $args
	 * @param $property
	 *
	 * @return mixed
	 */
	function parse_argument( $args, $property ) {
		/**
		 * Actions to run before parsing a particular property.
		 */
		do_action( 'wp_hammer_before_parse_argument_' . $property, $args );
		if ( property_exists( $this, $property ) && count( $args ) && '-' !== substr( $args[0], 0, 1 ) ) {
			$arg_values = explode( ',', array_shift( $args ) );
			/*
			 * Filter called to allow the parsed argument, can be used in combination with a pruner/formatter for custom functionality.
			 */
			$this->{ "$property" } = apply_filters( 'wp_hammer_argument_' . $property, array_unique( array_merge_recursive( (array) $this->{ "$property" }, $arg_values ) ) );
		}
		return $this->{ "$property" };
	}
}
