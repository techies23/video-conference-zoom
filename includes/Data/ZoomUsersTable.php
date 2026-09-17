<?php

namespace Codemanas\VczApi\Data;

/**
 * Class ZoomUsersTable
 *
 * Handles the custom database table used to cache Zoom user data.
 *
 * @package Codemanas\VczApi\Data
 * @since   4.8.0
 */
class ZoomUsersTable {

	const TABLE_NAME = 'vczapi_zoom_users';
	const DB_VERSION = '1.0.0';

	/**
	 * Get the prefixed table name.
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Create or update the custom table schema.
	 *
	 * Usable on activation and on every admin page load (idempotent via dbDelta).
	 *
	 * @return void
	 */
	public static function create_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name   = self::get_table_name();
		$charset_coll = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			zoom_user_id VARCHAR(64) NOT NULL,
			email VARCHAR(255) NOT NULL DEFAULT '',
			first_name VARCHAR(128) NOT NULL DEFAULT '',
			last_name VARCHAR(128) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NULL DEFAULT NULL,
			last_login_time DATETIME NULL DEFAULT NULL,
			last_client_version VARCHAR(128) NOT NULL DEFAULT '',
			synced_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY zoom_user_id (zoom_user_id),
			KEY email (email)
		) {$charset_coll};";

		dbDelta( $sql );

		update_option( 'vczapi_db_version', self::DB_VERSION );
	}

	/**
	 * Check if the custom table exists in the database.
	 *
	 * @return bool
	 */
	public static function table_exists(): bool {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		);

		return $exists === $table_name;
	}

	/**
	 * Upsert a batch of Zoom users into the table.
	 *
	 * Uses a single multi-row INSERT ... ON DUPLICATE KEY UPDATE so that
	 * thousands of users can be written in one query.
	 *
	 * @param array $users Array of user arrays (or objects) from the Zoom API.
	 *
	 * @return int Number of affected rows.
	 */
	public static function upsert_users( array $users ): int {
		global $wpdb;

		if ( empty( $users ) ) {
			return 0;
		}

		$table_name = self::get_table_name();
		$rows       = array();

		foreach ( $users as $user ) {
			$user = is_object( $user ) ? (array) $user : (array) $user;

			$vals   = array();
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['id'] ) ? sanitize_text_field( $user['id'] ) : '' );
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['email'] ) ? sanitize_email( $user['email'] ) : '' );
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['first_name'] ) ? sanitize_text_field( $user['first_name'] ) : '' );
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['last_name'] ) ? sanitize_text_field( $user['last_name'] ) : '' );
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['status'] ) ? sanitize_text_field( $user['status'] ) : 'active' );

			$created_at = self::format_date( $user['created_at'] ?? '' );
			$last_login = self::format_date( $user['last_login_time'] ?? '' );

			$vals[] = $created_at !== null ? $wpdb->prepare( '%s', $created_at ) : 'NULL';
			$vals[] = $last_login !== null ? $wpdb->prepare( '%s', $last_login ) : 'NULL';
			$vals[] = $wpdb->prepare( '%s', ! empty( $user['last_client_version'] ) ? sanitize_text_field( $user['last_client_version'] ) : '' );
			$vals[] = $wpdb->prepare( '%s', current_time( 'mysql' ) );

			$rows[] = '(' . implode( ', ', $vals ) . ')';
		}

		$sql = "INSERT INTO {$table_name}
			( zoom_user_id, email, first_name, last_name, status, created_at, last_login_time, last_client_version, synced_at )
			VALUES " . implode( ', ', $rows ) . "
			ON DUPLICATE KEY UPDATE
				email = VALUES( email ),
				first_name = VALUES( first_name ),
				last_name = VALUES( last_name ),
				status = VALUES( status ),
				created_at = VALUES( created_at ),
				last_login_time = VALUES( last_login_time ),
				last_client_version = VALUES( last_client_version ),
				synced_at = VALUES( synced_at )";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->query( $sql );
	}

	/**
	 * Get paginated users for the admin Users screen.
	 *
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 * @type int $page Page number (1-based).
	 * @type int $page_size Number of records per page.
	 * @type string $status User status filter. active|pending|inactive. Empty = all.
	 * @type string $search Optional search term for email or name.
	 * @type string $order_by Sort column. Default 'email'.
	 * @type string $order Sort direction. ASC|DESC.
	 * }
	 *
	 * @return array {
	 * @type array $data Array of stdClass user objects.
	 * @type null $error Errors (always null).
	 * @type int $page_count Total number of pages.
	 * @type int $total_records Total rows.
	 * @type int $page_number Current page.
	 * @type int $page_size Rows per page.
	 * }
	 */
	public static function get_users( array $args = array() ): array {
		global $wpdb;

		$table_name = self::get_table_name();
		$defaults   = array(
			'page'      => 1,
			'page_size' => 20,
			'status'    => '',
			'search'    => '',
			'order_by'  => 'email',
			'order'     => 'ASC',
		);
		$args       = wp_parse_args( $args, $defaults );

		$page      = max( 1, (int) $args['page'] );
		$page_size = min( 300, max( 1, (int) $args['page_size'] ) );
		$offset    = ( $page - 1 ) * $page_size;

		$order_by = in_array( $args['order_by'], array( 'email', 'first_name', 'last_name', 'created_at', 'last_login_time' ), true ) ? $args['order_by'] : 'email';
		$order    = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$where  = array( '1 = 1' );
		$values = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = sanitize_text_field( $args['status'] );
		}

		if ( ! empty( $args['search'] ) ) {
			$where[]  = '( email LIKE %s OR first_name LIKE %s OR last_name LIKE %s )';
			$search   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[] = $search;
			$values[] = $search;
			$values[] = $search;
		}

		$where_sql = implode( ' AND ', $where );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}", $values ) );

		$page_count = $total > 0 ? (int) ceil( $total / $page_size ) : 1;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id AS row_id, zoom_user_id AS id, email, first_name, last_name, status, created_at, last_login_time, last_client_version, synced_at FROM {$table_name} WHERE {$where_sql} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d",
				array_merge( $values, array( $page_size, $offset ) )
			)
		);

		return array(
			'data'          => $results ?: array(),
			'error'         => null,
			'page_count'    => $page_count,
			'total_records' => $total,
			'page_number'   => $page,
			'page_size'     => $page_size,
		);
	}

	/**
	 * Search users for the select2 host picker.
	 *
	 * @param string $term Optional search term. Empty returns all users.
	 *
	 * @return array Array of [id, text] pairs for select2.
	 */
	public static function search_for_picker( string $term = '' ): array {
		global $wpdb;

		$table_name = self::get_table_name();
		$results    = array();

		if ( empty( $term ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( "SELECT zoom_user_id, email, first_name, last_name FROM {$table_name} ORDER BY email ASC" );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT zoom_user_id, email, first_name, last_name FROM {$table_name} WHERE email LIKE %s OR first_name LIKE %s OR last_name LIKE %s ORDER BY email ASC LIMIT 50",
					'%' . $wpdb->esc_like( $term ) . '%',
					'%' . $wpdb->esc_like( $term ) . '%',
					'%' . $wpdb->esc_like( $term ) . '%'
				)
			);
		}

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				$name = trim( $row->first_name . ' ' . $row->last_name );
				$text = ! empty( $name ) ? $name . ' (' . $row->email . ')' : $row->email;

				$results[] = array(
					'id'    => $row->zoom_user_id,
					'text'  => $text,
					'email' => $row->email,
				);
			}
		}

		return $results;
	}

	/**
	 * Get all users as objects for backwards-compatible consumers.
	 *
	 * Mirrors the shape the legacy option cache returned (array of stdClass
	 * with ->id, ->email, ->first_name, ->last_name, ->created_at, ... ).
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function get_all_as_objects( int $limit = 0 ): array {
		global $wpdb;

		$table_name = self::get_table_name();
		$statuses   = array( 'active', 'pending', 'inactive' );

		// Build placeholder string (%s, %s, %s) safely for IN clause
		$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

		// Base query using IN () to replace 3 separate database calls with 1
		$query = "SELECT zoom_user_id, email, first_name, last_name, status 
              FROM {$table_name} 
              WHERE status IN ({$placeholders}) 
              ORDER BY email ASC";

		$params = $statuses;

		// Conditionally append LIMIT clause if $limit is greater than 0
		if ( $limit > 0 ) {
			$query    .= " LIMIT %d";
			$params[] = $limit;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare( $query, ...$params )
		);

		if ( empty( $rows ) ) {
			return array();
		}

		// Map DB columns to match API field names
		foreach ( $rows as $row ) {
			$row->id = $row->zoom_user_id;
		}

		return $rows;
	}

	/**
	 * Get the last time the table was synced.
	 *
	 * @return string|null MySQL datetime string or null if never synced.
	 */
	public static function get_last_sync_time(): ?string {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( "SELECT MAX(synced_at) FROM {$table_name}" ) ?: null;
	}

	/**
	 * Get the total number of users stored in the table.
	 *
	 * @return int
	 */
	public static function count_users(): int {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
	}

	/**
	 * Truncate the table before a fresh full sync.
	 *
	 * @return void
	 */
	public static function truncate(): void {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( "TRUNCATE TABLE {$table_name}" );
	}

	/**
	 * Delete rows that were not touched during the last completed sync.
	 *
	 * Used after a fully successful sync to remove users that no longer
	 * exist in Zoom. Safe on partial failure because it only runs when
	 * the whole sync completed.
	 *
	 * @param string $synced_at The sync timestamp (MySQL datetime) to keep.
	 *
	 * @return int Number of deleted rows.
	 */
	public static function delete_stale( string $synced_at ): int {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$table_name} WHERE synced_at <> %s", $synced_at )
		);
	}

	/**
	 * Normalize a date string to MySQL DATETIME format.
	 *
	 * @param string|null $date Raw date (ISO 8601 from Zoom or empty).
	 *
	 * @return string|null
	 */
	private static function format_date( ?string $date ): ?string {
		if ( empty( $date ) || ! is_string( $date ) ) {
			return null;
		}

		$timestamp = strtotime( $date );

		return $timestamp ? date( 'Y-m-d H:i:s', $timestamp ) : null;
	}
}