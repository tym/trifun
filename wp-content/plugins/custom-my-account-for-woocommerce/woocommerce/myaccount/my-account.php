<?php
/**
 * My Account page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices(); ?>
<div id="phoen-wcmap-wrap" clsss="phoen-wcmap">
<div class="phoen-wcmap-row">
<div class="phoen-myaccount-menu">
<ul class="myaccount-menu">
<li><a href="?temp=dashboard">Dashboard</a></li>
<li><a href="?temp=downloads">My Downloads</a></li>
<li><a href="?temp=orders">My Orders</a></li>
<li><a href="?temp=edit_account">Edit Account</a></li>
<li><a href="?temp=my_address">Edit Address</a></li>
</ul>
</div>
<div class="phoen-myaccount-content">
<?php 
	
do_action( 'woocommerce_before_my_account');

if(isset($_GET['temp']))
{
	 $tab_type = $_GET['temp'];
	 
	if($tab_type == 'downloads')
	{
		 wc_get_template( 'myaccount/my-downloads.php' ); 
	}
	else if($tab_type == 'orders')
	{
		 wc_get_template( 'myaccount/my-orders.php', array( 'order_count' => $order_count ) );
		 
	}	 
	else if($tab_type == 'edit_account')
	{
		
		 wc_get_template( 'myaccount/form-edit-account.php', array( 'user' => get_user_by( 'id', get_current_user_id() ) ) );
		 
	}
	else if($tab_type == 'my_address')
	{
		wc_get_template( 'myaccount/my-address.php' ); 
	}
	else if($tab_type == 'dashboard')
	{
		
	?>
		<p class="myaccount_user">
			<?php
			printf(
				__( 'Hello <strong>%1$s</strong> (not %1$s? <a href="%2$s">Sign out</a>).', 'woocommerce' ) . ' ',
				$current_user->display_name,
				wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) )
			);

			printf( __( 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">edit your password and account details</a>.', 'woocommerce' ),
				wc_customer_edit_account_url()
			);
			?>
		</p>
	
		<?php		
	}
}
else
{
	
	?>
		<p class="myaccount_user">
			<?php
			printf(
				__( 'Hello <strong>%1$s</strong> (not %1$s? <a href="%2$s">Sign out</a>).', 'woocommerce' ) . ' ',
				$current_user->display_name,
				wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) )
			);

			printf( __( 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">edit your password and account details</a>.', 'woocommerce' ),
				wc_customer_edit_account_url()
			);
			?>
		</p>

	<?php	

}

?>








<?php //wc_get_template( 'myaccount/form-edit-address.php' ); ?>



<?php do_action( 'woocommerce_after_my_account' ); ?>
</div>
</div>
</div>