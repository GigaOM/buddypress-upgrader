<?php
/*
Plugin Name: BuddyPress Upgrader
Plugin URI: http://gigaom.com
Description: Upgrade from BuddyPress 1.0.3 to 1.6.1
Version: 1.0
Author: Matthew Batchelder
Author URI: http://borkweb.com
*/

global $bp_upgrader_log;

$bp_upgrader_log = array();

function upgrade_to_1_1_3() {
	do_action( 'debug_robot', __FUNCTION__);
	global $wpdb;

	$bp_prefix = bp_core_get_table_prefix();

	/* Drop the old sitewide and user activity tables */
	do_action( 'debug_robot', "Drop {$bp_prefix}bp_activity_user_activity and {$bp_prefix}bp_activity_sitewide");
	$wpdb->query( "RENAME TABLE {$bp_prefix}bp_activity_user_activity_cached TO {$bp_prefix}bp_activity" );
	$wpdb->query( "DROP TABLE IF EXISTS {$bp_prefix}bp_activity_user_activity_cached" );
	$wpdb->query( "DROP TABLE IF EXISTS {$bp_prefix}bp_activity_user_activity" );
	$wpdb->query( "DROP TABLE IF EXISTS {$bp_prefix}bp_activity_sitewide" );

	update_site_option( 'bp-activity-db-version', 1800 );
}//end upgrade_to_1_1_3

function upgrade_to_1_2_9() {
	do_action( 'debug_robot', __FUNCTION__);

	global $wpdb;

	$bp_prefix = bp_core_get_table_prefix();

	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_messages_messages ADD thread_id bigint(20) NOT NULL AFTER id" );

	/* Upgrade and remove the message threads table if it exists */
	if ( $wpdb->get_var( "SHOW TABLES LIKE '%{$bp_prefix}bp_messages_threads%'" ) ) {

		$errors = false;
		$threads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp_prefix}bp_messages_threads" ) );

		/* Nothing to upgrade, just return true to remove the table */
		if ( ! empty( $threads ) ) {
			do_action( 'debug_robot', 'Updating bp_messages_messages thread_id' );
			foreach( (array)$threads as $thread ) {
				$message_ids = maybe_unserialize( $thread->message_ids );

				if ( !empty( $message_ids ) ) {
					$message_ids = implode( ',', $message_ids );

					/* Add the thread_id to the messages table */
					if ( !$wpdb->query( $wpdb->prepare( "UPDATE {$bp_prefix}bp_messages_messages SET thread_id = %d WHERE id IN ({$message_ids})", $thread->id ) ) ) {
						$errors = true;
					}//end if
				}//end if
			}//end foreach
		}//end if

		if ( ! $errors ) {
			do_action( 'debug_robot', 'Dropping bp_messages_threads' );
			$wpdb->query( "DROP TABLE {$bp_prefix}bp_messages_threads" );
		}//end if
	}//end if

	add_site_option( 'bp-messages-db-version', 2000 );
}//end upgrade_to_1_2_9

function upgrade_to_1_5_7()
{
	do_action( 'debug_robot', __FUNCTION__);

	global $wpdb;

	$bp_prefix = bp_core_get_table_prefix();

	// Rename fields from pre BP 1.2
	if ( $wpdb->get_var( "SHOW TABLES LIKE '%{$bp_prefix}bp_activity%'" ) ) {
		if ( $wpdb->get_var( "SHOW COLUMNS FROM {$bp_prefix}bp_activity LIKE 'component_action'" ) ) {
			do_action( 'debug_robot', 'Renaming activity table column "component_action" to "type"');
			$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity CHANGE component_action type varchar(75) NOT NULL" );
		}//end if

		if ( $wpdb->get_var( "SHOW COLUMNS FROM {$bp_prefix}bp_activity LIKE 'component_name'" ) ) {
			do_action( 'debug_robot', 'Renaming activity table column "component_name" to "component"');
			$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity CHANGE component_name component varchar(75) NOT NULL" );
		}//end if
	}//end if
}//end upgrade_to_1_5_7

function upgrade_to_1_6_1() {
	do_action( 'debug_robot', __FUNCTION__);

	global $wpdb;

	$bp_prefix = bp_core_get_table_prefix();

	do_action( 'debug_robot', 'Altering bp_activity' );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity CHANGE primary_link primary_link varchar(255) NOT NULL" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity CHANGE item_id item_id bigint(20) NOT NULL" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity CHANGE secondary_item_id secondary_item_id bigint(20)" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity ADD is_spam tinyint(1) NOT NULL DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity DROP COLUMN is_private" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_activity DROP COLUMN date_cached" );

	do_action( 'debug_robot', 'Altering bp_groups' );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups CHANGE slug slug varchar(200) NOT NULL" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups CHANGE status status varchar(10) NOT NULL DEFAULT 'public'" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN news" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN is_invitation_only" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN enable_wire" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN enable_photos" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN photos_admin_only" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN avatar_thumb" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_groups DROP COLUMN avatar_full" );

	do_action( 'debug_robot', 'Dropping group tables' );
	$wpdb->query( "DROP TABLE {$bp_prefix}bp_groups_wire" );
	
	do_action( 'debug_robot', 'Altering bp_messages_messages' );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_messages_messages DROP COLUMN message_order" );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_messages_messages DROP COLUMN sender_is_group" );

	do_action( 'debug_robot', 'Dropping blogs tables' );
	$wpdb->query( "DROP TABLE {$bp_prefix}bp_user_blogs_comments" );
	$wpdb->query( "DROP TABLE {$bp_prefix}bp_user_blogs_posts" );

	do_action( 'debug_robot', 'Altering bp_xprofile_fields' );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_xprofile_fields DROP COLUMN is_public" );

	do_action( 'debug_robot', 'Altering bp_xprofile_groups' );
	$wpdb->query( "ALTER TABLE {$bp_prefix}bp_xprofile_groups ADD group_order bigint(20) NOT NULL DEFAULT 0 AFTER description" );

	do_action( 'debug_robot', 'Dropping xprofile_wire' );
	$wpdb->query( "DROP TABLE {$bp_prefix}bp_xprofile_wire" );
}//end upgrade_to_1_6_1

function bp_upgrader_options_page() {
	if ( $_GET['upgrade'] ) {
		upgrade_to_1_1_3();
		upgrade_to_1_2_9();
		upgrade_to_1_5_7();
		upgrade_to_1_6_1();
		?>
		<div class="wrap">
			<h2>BuddyPress Upgrader: v1.0.3 -> v1.6.1</h2>
			<p>Upgraded!</p>
		</div>
		<?php
	} else {
		?>
		<div class="wrap">
			<h2>BuddyPress Upgrader: v1.0.3 -> v1.6.1</h2>
			<p><a href="?page=bp_upgrader&upgrade=true">Upgrade</a></p>
		</div>
		<?php
	}//end else
}//end bp_upgrader_options_page


function bp_upgrader_menu()
{
	add_menu_page( 'BP Magical Upgrader', 'BP Magical Upgrader', 'manage_options', 'bp_upgrader', 'bp_upgrader_options_page');
}//end bp_upgrader_menu

add_action( 'network_admin_menu', 'bp_upgrader_menu' );
