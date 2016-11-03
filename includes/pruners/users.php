<?php

namespace WP_CLI\Hammer\Pruners\Users;

/**
 * @param            $limit      How many users to keep
 * @param bool|false $sort_type  How to determine which users to keep
 */
function pruner( $limit, $sort_type = false ) {
	$limit = absint( $limit );
	\WP_CLI::line( "Fetching all users for removing all but $limit. This could take a while." );
	$roles = array_keys( get_editable_roles() );
	$user_ids_by_role = array();
	$total_users = 0;
	foreach ( $roles as $role ) {
		$users = new \WP_User_Query( array( 'role' => $role, 'fields' => 'ID' ) );
		if ( $users->total_users ) {
			$total_users += $users->total_users;
			$user_ids_by_role[ $role ] = $users->results;
		}
	}
	$all_users = new \WP_User_Query( array( 'role' => '', 'fields' => 'ID' ) );
	$no_role_users =  $all_users->total_users ? array_diff( $all_users->results, call_user_func_array( 'array_merge', $user_ids_by_role ) ) : array();
	$total_users += count( $no_role_users );
	$user_ids_by_role[ 'none' ] = $no_role_users;

	if ( $total_users < $limit ) {
		\WP_CLI::line( "Total users $total_users is less than the limit of $limit, no users removed." );
	} elseif ( $total_users === $limit ) {
			\WP_CLI::line( "Total users $total_users is identical to the limit, no users removed." );
	} else {
		\WP_CLI::line( "Pruning the user count from $total_users to $limit." );
		// User IDs to keep
		$keep_ids = array();
		$remaining = $limit;

		while ( $remaining ) {
			// Iterate roles, keeping one more of each role until we've hit our limit
			foreach ( $user_ids_by_role as $role => &$ids ) {
				$keep_id = array_shift( $ids );
				if ( ! is_null( $keep_id ) && ! in_array( $keep_id, $keep_ids ) ) {
					$keep_ids[] = $keep_id;
					$remaining--;
				}
			}
		}

		// Find all users we're not keeping
		$users_to_delete = new \WP_User_Query( array( 'exclude' => $keep_ids ) );
		if ( $users_to_delete->total_users ) {
			require_once( ABSPATH . 'wp-admin/includes/user.php' );

			// Determine the author ID to use for post reassignment.
			$new_author_id = ! empty( $keep_ids ) ? min( $keep_ids ) : null;
			foreach( $users_to_delete->results as $user_to_delete ) {
				\WP_CLI::line( "Deleting user {$user_to_delete->ID} and reassigning their posts to user ID: $new_author_id" );
				// Delete user and reassign their posts to the smallest user ID that will remain.
				wp_delete_user( $user_to_delete->ID, $new_author_id );
			}
		}
	}
}

add_action( 'wp_hammer_run_prune_users', __NAMESPACE__ . '\pruner' );
