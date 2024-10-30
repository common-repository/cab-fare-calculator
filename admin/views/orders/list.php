<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'TaxiBooking - Orders', 'cab-fare-calculator' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=orders&action=edit' ) ); ?>" class="page-title-action"><?php esc_attr_e( 'Add New', 'cab-fare-calculator' ); ?></a>

	<div class="meta-box-sortables ui-sortable">
		<form method="post">
		<?php
		$this->orders_obj->prepare_items();
		$this->orders_obj->search_box( 'Search', 'search' );
		$this->orders_obj->display();
		?>
		</form>
	</div>
</div>