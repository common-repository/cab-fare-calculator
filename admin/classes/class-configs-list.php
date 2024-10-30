<?php
class Configs_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Config', 'sp' ), // singular name of the listed records
				'plural'   => __( 'Configs', 'sp' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_configs( $per_page = 10, $page_number = 1 ) {

		global $wpdb;

		$table_name = $wpdb->prefix.'tblight_configs';

		$orderby = 'id'; // Default column
		$order = 'ASC'; // Default order

		// Execute the query.
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY $orderby $order"
			), 
			'ARRAY_A'
		);

		return $result;
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_item( $id ) {
		return false;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		if ( ! empty( $_REQUEST['s'] ) ) {
			$result = $wpdb->get_var( 
						$wpdb->prepare(
							"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_configs WHERE title LIKE %s;",
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'
						)
					);
		}
		else {
			$result = $wpdb->get_var( 
						"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_configs;"
					);
		}

		return $result;
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_attr_e( 'No configs avaliable.', 'cab-fare-calculator' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			// case 'min_passenger_no':
			// case 'passenger_no':
				// return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_title( $item ) {

		if($item['alias'] == 'order-email-settings') {
			$title = '<strong>' . sprintf( '<a href="https://kanev.com/products/taxi-booking-for-wordpress" target="_blank">' . esc_attr( $item['title'] ) . '<span class="list-pro-label">PRO</span></a>', 'edit', absint( $item['id'] ) ) . '</strong>';
		} else {
			$title = '<strong>' . sprintf( '<a href="?page=configs&action=%s&id=%s">' . esc_attr( $item['title'] ) . '</a>', 'edit', absint( $item['id'] ) ) . '</strong>';
		}
		

		$actions = array();

		return $title . $this->row_actions( $actions );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'title' => __( 'Name', 'sp' ),
		);

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array() );

		$this->items = self::get_configs( $per_page, $current_page );
	}
}
