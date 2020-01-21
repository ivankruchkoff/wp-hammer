<?php

namespace WP_CLI\Hammer\Sanitizers\Usermeta;
use WP_CLI;

/**
 * Sanitize sensitive data from the `wp_usermeta` table.
 */
function sanitizer() {
	global $wpdb;

	$safe_usermeta = get_safe_usermeta_names();
	$exact_names   = "'" . implode( "','", $safe_usermeta['exact_names'] ) . "'";

	foreach ( $safe_usermeta['partial_names'] as $partial_names ) {
		$like_clauses[] = "meta_key NOT LIKE '$partial_names' ";
	}

	if ( ! empty( $like_clauses ) ) {
		$like_clauses = ' AND ' . implode( ' AND ', $like_clauses );
	}

	/*
	 * This is intentionally not escaped/prepared, to allow the use of wildcards in the input.
	 * It is still safe, though, since the input only comes from trusted sources.
	 */
	$usermeta_to_delete = $wpdb->get_results( "
		SELECT DISTINCT meta_key
		FROM `{$wpdb->prefix}usermeta`
		WHERE
			`meta_key` NOT IN ( $exact_names )
			$like_clauses
	" );
	$usermeta_to_delete = wp_list_pluck( $usermeta_to_delete, 'meta_key' );

	if ( empty( $usermeta_to_delete ) ) {
		WP_CLI::line( "No sensitive usermeta was found. Aborting." );
		return;
	}

	foreach ( $usermeta_to_delete as $usermeta ) {
		WP_CLI::line( "Deleting usermeta `{$usermeta}`." );
	}

	// Delete with a query because `delete_user_meta()` requires the user ID, but we want to delete from all users.
	$usermeta_placeholders = implode( ', ', array_fill( 0, count( $usermeta_to_delete ), '%s' ) );;

	$wpdb->query( $wpdb->prepare( "
		DELETE
		FROM `{$wpdb->prefix}usermeta`
		WHERE `meta_key` IN ( $usermeta_placeholders )",
		$usermeta_to_delete
	) );
}

/**
 * Get a list of usermeta that are safe to remain in local development environments.
 *
 * This uses a safelist instead of a blocklist, because we can't predict what plugins -- or future
 * versions of Core -- will store in the `wp_usermeta` table. A blocklist would inevitably result
 * in sensitive data being left in the database.
 *
 * @return array
 */
function get_safe_usermeta_names() {
	$safelist = array(
		'exact_names' => array(
			'admin_color', 'closedpostboxes_dashboard', 'closedpostboxes_dashboard-network', 'closedpostboxes_page',
			'closedpostboxes_post', 'comment_shortcuts', 'description', 'dismissed_wp_pointers',
			'edit_category_per_page', 'edit_comments_per_page', 'edit_post_per_page', 'edit_post_tag_per_page',
			'first_name', 'last_name', 'locale', 'managenav-menuscolumnshidden', 'meta-box-order_dashboard-network',
			'meta-box-order_post', 'metaboxhidden_dashboard', 'metaboxhidden_dashboard-network',
			'metaboxhidden_nav-menus', 'metaboxhidden_page', 'metaboxhidden_post', 'nickname', 'primary_blog',
			'rich_editing', 'screen_layout_post', 'show_admin_bar_front', 'show_try_gutenberg_panel',
			'show_welcome_panel', 'source_domain', 'use_ssl', 'wp_dashboard_quick_press_last_post_id'
		),

		'partial_names' => array( '%_capabilities', '%_user_level' ),
	);

	/**
	 * Filter the list of safe usermeta names.
	 *
	 * Entries in the `exact_names` array should be the exact usermeta name. Entries in the `partial_names` array
	 * should contain partial usermeta names and MySQL wildcards (i.e., `_` and `%` ), just like you would if you
	 * were adding `LIKE` clause to a SQL statement.
	 *
	 * @param array $safelist The list of safe names.
	 */
	return apply_filters( 'wp_hammer_safe_usermeta_names', $safelist );
}

add_action( 'wp_hammer_run_sanitize_usermeta', __NAMESPACE__ . '\sanitizer' );
