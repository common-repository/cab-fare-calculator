<?php
class Cars_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Car', 'sp' ), // singular name of the listed records
				'plural'   => __( 'Cars', 'sp' ), // plural name of the listed records
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
	public static function get_cars( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'id',
			'order'   => 'ASC',
			'search'  => '',
		);

		$offset = ( $page_number - 1 ) * $per_page;
		$args   = array(
			'number' => $per_page,
			'offset' => $offset,
		);

		$table_name = $wpdb->prefix.'tblight_cars';

		$conditions = [];
		$values = [];

		if (!empty($_REQUEST['s'])) {
			$conditions[] = "title LIKE %s";
			$values[] = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'; // Use esc_like() for LIKE queries
		}

		$orderby = isset($_GET['orderby']) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id'; // Default column
		$order = isset($_GET['order']) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC'; // Default order

		// List of allowed columns for ORDER BY.
		$allowed_orderby = ['id', 'title', 'min_passenger_no', 'passenger_no', 'child_seat_no', 'unit_price']; // Specify allowed columns
		$allowed_order = ['ASC', 'DESC']; // Allowed directions

		// Validate the orderby and order inputs.
		if (!in_array($orderby, $allowed_orderby)) {
			$orderby = 'id'; // Default fallback
		}
		if (!in_array(strtoupper($order), $allowed_order)) {
			$order = 'ASC'; // Default fallback
		}

		// Build the WHERE clause dynamically.
		$where = '';
		if (!empty($conditions)) {
			$where = 'WHERE ' . implode(' AND ', $conditions);
		}

		// Execute the query.
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, min_passenger_no, passenger_no, child_seat_no, unit_price, state FROM $table_name $where ORDER BY $orderby $order",
				...$values
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
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}tblight_cars",
			array( 'id' => $id ),
			array( '%d' )
		);
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
							"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_cars WHERE title LIKE %s;",
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'
						)
					);
		}
		else {
			$result = $wpdb->get_var( 
						"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_cars;"
					);
		}

		return $result;
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_attr_e( 'No cars avaliable.', 'cab-fare-calculator' );
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
			case 'min_passenger_no':
			case 'passenger_no':
			case 'child_seat_no':
			case 'unit_price':
			case 'state':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Method for Pickup date column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_state( $item ) {

		$icon_waiting   = TBLIGHT_PLUGIN_DIR_URL . 'admin/images/publish_x.png';
		$icon_published = TBLIGHT_PLUGIN_DIR_URL . 'admin/images/icon-16-allow.png';

		if ( $item['state'] == 1 ) {
			$label = 'Published';
			$img   = '<img src="' . $icon_published . '" alt="Published" />';
		} else {
			$label = 'Unpublished';
			$img   = '<img src="' . $icon_waiting . '" alt="Unpublished" />';
		}

		return $img;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_title( $item ) {

		$delete_nonce = wp_create_nonce( 'tblight_delete_car' );
		$status_nonce = wp_create_nonce( 'tblight_status_car' );

		$title = '<strong>' . sprintf( '<a href="?page=cars&action=%s&id=%s">' . $item['title'] . '</a>', 'show', absint( $item['id'] ) ) . '</strong>';

		$actions = array(
			'edit'   => sprintf( '<a href="?page=cars&action=%s&id=%s">Edit</a>', 'edit', absint( $item['id'] ) ),
			'delete' => sprintf( '<a href="?page=cars&action=%s&id=%s&_wpnonce=%s">Delete</a>', 'delete', absint( $item['id'] ), $delete_nonce ),
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'title'            => __( 'Name', 'sp' ),
			'min_passenger_no' => __( 'Min Passenger', 'sp' ),
			'passenger_no'     => __( 'Max Passenger', 'sp' ),
			'child_seat_no'    => __( 'Max Childs', 'sp' ),
			'unit_price'       => __( 'Price', 'sp' ),
			'state'            => __( 'Status', 'sp' ),
		);

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'            => array( 'title', true ),
			'min_passenger_no' => array( 'min_passenger_no', true ),
			'passenger_no'     => array( 'passenger_no', true ),
			'child_seat_no'    => array( 'child_seat_no', true ),
			'unit_price'       => array( 'unit_price', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'items_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page'    => $per_page, // WE have to determine how many items to show on a page
			)
		);

		$this->items = self::get_cars( $per_page, $current_page );
	}
}