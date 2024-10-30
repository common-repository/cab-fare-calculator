<?php

require_once TBLIGHT_PLUGIN_PATH . 'controllers/onepage.php';

class TBLight_Ajax {

	// class constructor
	public function __construct() {
		 add_action( 'wp_ajax_getPrice', array( $this, 'getPrice' ) );
		add_action( 'wp_ajax_nopriv_getPrice', array( $this, 'getPrice' ) );

		add_action( 'wp_ajax_getVehicles', array( $this, 'getVehicles' ) );
		add_action( 'wp_ajax_nopriv_getVehicles', array( $this, 'getVehicles' ) );

		add_action( 'wp_ajax_bookNow', array( $this, 'bookNow' ) );
		add_action( 'wp_ajax_nopriv_bookNow', array( $this, 'bookNow' ) );

		add_action( 'wp_ajax_calculateTotal', array( $this, 'calculateTotal' ) );
		add_action( 'wp_ajax_nopriv_calculateTotal', array( $this, 'calculateTotal' ) );

		add_action( 'wp_ajax_submitOrder', array( $this, 'submitOrder' ) );
		add_action( 'wp_ajax_nopriv_submitOrder', array( $this, 'submitOrder' ) );

		add_action( 'wp_ajax_resetBookingForm', array( $this, 'resetBookingForm' ) );
		add_action( 'wp_ajax_nopriv_resetBookingForm', array( $this, 'resetBookingForm' ) );

		add_action( 'wp_ajax_getAvailableCars', array( $this, 'getAvailableCars' ) );
		//add_action( 'wp_ajax_nopriv_getAvailableCars', array( $this, 'getAvailableCars' ) );

		add_action( 'wp_ajax_changeOrderStatus', array( $this, 'changeOrderStatus' ) );
		//add_action( 'wp_ajax_nopriv_changeOrderStatus', array( $this, 'changeOrderStatus' ) );
	}

	public function getPrice() {
		// Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->getPrice();
		echo json_encode( $result );
		exit();
	}

	public function getVehicles() {
		 // Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->getVehicles();
		echo json_encode( $result );
		exit();
	}

	public function bookNow() {
		 // Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->bookNow();
		echo json_encode( $result );
		exit();
	}

	public function calculateTotal() {
		// Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->calculateTotal();
		echo json_encode( $result );
		exit();
	}

	public function submitOrder() {
		 // Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->submitOrder();
		echo json_encode( $result );
		exit();
	}

	public function resetBookingForm() {
		// Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$controller = new TblightControllerOnepage();
		$result     = $controller->resetBookingForm();
		echo json_encode( $result );
		exit();
	}

	public function changeOrderStatus() {
		// Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$ajaxController = new TblightControllerOnepage();
		$result     = $ajaxController->changeStatusAjax();
		echo json_encode( $result );
		exit();
   	}

	public function getAvailableCars() {
		// Check for nonce security
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tblight-ajax-nonce' ) ) {
			wp_die( 'Something went wrong!' );
		}

		$ajaxController = new TblightControllerOnepage();
		$result     = $ajaxController->getAvailableCarsAjax();
		echo json_encode( $result );
		exit();
   	}
}
