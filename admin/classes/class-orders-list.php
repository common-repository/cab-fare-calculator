<?php
class Orders_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Order', 'sp' ), // singular name of the listed records
				'plural'   => __( 'Orders', 'sp' ), // plural name of the listed records
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
	public static function get_orders( $per_page = 20, $page_number = 1 ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'datetime1',
			'order'   => 'DESC',
			'search'  => '',
		);

		$offset = ( $page_number - 1 ) * $per_page;
		$args   = array(
			'number' => $per_page,
			'offset' => $offset,
		);

		$table_name = $wpdb->prefix.'tblight_orders';

		$conditions = [];
		$values = [];

		if (!empty($_REQUEST['s'])) {
			$conditions[] = "order_number LIKE %s";
			$values[] = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'; // Use esc_like() for LIKE queries
		}

		$orderby = isset($_GET['orderby']) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'datetime1'; // Default column
		$order = isset($_GET['order']) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC'; // Default order

		// List of allowed columns for ORDER BY.
		$allowed_orderby = ['id', 'names', 'datetime1', 'begin', 'end', 'selpassengers', 'vehicle_title', 'payment_name', 'cprice']; // Specify allowed columns
		$allowed_order = ['ASC', 'DESC']; // Allowed directions

		// Validate the orderby and order inputs.
		if (!in_array($orderby, $allowed_orderby)) {
			$orderby = 'datetime1'; // Default fallback
		}
		if (!in_array(strtoupper($order), $allowed_order)) {
			$order = 'DESC'; // Default fallback
		}

		// Build the WHERE clause dynamically.
		$where = '';
		if (!empty($conditions)) {
			$where = 'WHERE ' . implode(' AND ', $conditions);
		}

		// Execute the query.
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, order_number, names, cprice, selpassengers, datetime1, `begin`, `end`, `state`, vehicletype, vehicle_title, payment, payment_name FROM $table_name $where ORDER BY $orderby $order",
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
			"{$wpdb->prefix}tblight_orders",
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
							"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_orders WHERE order_number LIKE %s OR names LIKE %s OR email LIKE %s OR begin LIKE %s OR end LIKE %s;",
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%',
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%',
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%',
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%',
							'%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%'
						)
					);
		}
		else {
			$result = $wpdb->get_var( 
						"SELECT COUNT(id) FROM {$wpdb->prefix}tblight_orders;",
					);
		}

		return $result;
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_attr_e( 'No orders avaliable.', 'cab-fare-calculator' );
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
			case 'names':
			case 'begin':
			case 'end':
			case 'cprice':
			case 'state':
			case 'selpassengers':
			case 'id':
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
	 * Method for Order number column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_order_number( $item ) {
		$delete_nonce = wp_create_nonce( 'tblight_delete_order' );
		$status_nonce = wp_create_nonce( 'tblight_status_order' );

		$order_number = '<strong>' . sprintf( '<a href="?page=orders&action=%s&id=%s">' . $item['order_number'] . '</a>', 'show', absint( $item['id'] ) ) . '</strong>';

		$actions = array(
			'edit'   => sprintf( '<a href="?page=orders&action=%s&id=%s">Edit</a>', 'edit', absint( $item['id'] ) ),
			'delete' => sprintf( '<a href="?page=orders&action=%s&id=%s&_wpnonce=%s">Delete</a>', 'delete', absint( $item['id'] ), $delete_nonce ),
		);

		return $order_number . $this->row_actions( $actions );
	}

	/**
	 * Method for Pickup date column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_pickup_date( $item ) {

		return gmdate( 'Y-m-d H:i', $item['datetime1'] );
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
		$icon_rejected  = TBLIGHT_PLUGIN_DIR_URL . 'admin/images/publish_r.png';
		$icon_archived  = TBLIGHT_PLUGIN_DIR_URL . 'admin/images/disabled.png';
		$icon_published = TBLIGHT_PLUGIN_DIR_URL . 'admin/images/icon-16-allow.png';

		if ( $item['state'] == 1 ) {
			$label = 'Accepted';
			$img   = '<img src="' . $icon_published . '" alt="Accepted" />';
		} elseif ( $item['state'] == 0 ) {
			$label = 'Rejected';
			$img   = '<img src="' . $icon_rejected . '" alt="Accepted" />';
		} elseif ( $item['state'] == -1 ) {
			$label = 'Archived';
			$img   = '<img src="' . $icon_archived . '" alt="Accepted" />';
		} else {
			$label = 'Waiting';
			$img   = '<img src="' . $icon_waiting . '" alt="Accepted" />';
		}

		return $img;
	}

	/**
	 * Method for Vehicle title column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_vehicle_title( $item ) {

		$column = sprintf( '<a href="?page=%s&action=%s&id=%s">' . $item['vehicle_title'] . '</a>', esc_attr( 'cars' ), 'show', absint( $item['vehicletype'] ) );

		return $column;
	}

	/**
	 * Method for Vehicle title column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_payment_name( $item ) {

		$column = sprintf( $item['payment_name'], esc_attr( 'paymentmethods' ), '', absint( $item['payment'] ) );

		return $column;
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'order_number'  => __( 'Order Number', 'sp' ),
			'names'         => __( 'Customer', 'sp' ),
			'pickup_date'   => __( 'Pickup Date', 'sp' ),
			'begin'         => __( 'Pickup', 'sp' ),
			'end'           => __( 'DropOff', 'sp' ),
			'selpassengers' => __( 'Passengers', 'sp' ),
			'vehicle_title' => __( 'Vehicle', 'sp' ),
			'payment_name'  => __( 'Payment Name', 'sp' ),
			'cprice'        => __( 'Price', 'sp' ),
			'state'         => __( 'Status', 'sp' ),
			'id'            => __( 'ID', 'sp' ),
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
			'pickup_date'   => array( 'datetime1', true ),
			'names'         => array( 'names', true ),
			'begin'         => array( 'begin', true ),
			'end'           => array( 'end', true ),
			'selpassengers' => array( 'selpassengers', true ),
			'cprice'        => array( 'cprice', true ),
			'vehicle_title' => array( 'vehicle_title', true ),
			'payment_name'  => array( 'payment_name', true ),
			'id'            => array( 'id', true ),
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

		$this->items = self::get_orders( $per_page, $current_page );
	}
}