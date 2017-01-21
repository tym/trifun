<?php
/**
 * Edit address form
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;

$page_title = ( $load_address === 'billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );

get_currentuserinfo();

?>

<?php wc_print_notices(); 
$row1 = get_option('myaccount_general_setting');

?>
<div id="phoen-wcmap-wrap" class="phoen-wcmap woocommerce">
<div class="phoen-wcmap-row">
<div class="phoen-myaccount-menu <?php if($row1['menu_style']!='sidebar'){?> pho-horizontal<?php } ?>">
<ul class="myaccount-menu">
<li><a href="<?php echo get_site_url().'/my-account'?>?temp=dashboard">Dashboard</a></li>
<li><a href="<?php echo get_site_url().'/my-account'?>?temp=downloads">My Downloads</a></li>
<li><a href="<?php echo get_site_url().'/my-account'?>?temp=orders">My Orders</a></li>
<li><a href="<?php echo get_site_url().'/my-account'?>?temp=edit_account">Edit Account</a></li>
<li><a href="<?php echo get_site_url().'/my-account'?>?temp=my_address">Edit Address</a></li>
</ul>
</div>
<div class="phoen-myaccount-content<?php if($row1['menu_style']!='sidebar'){?> pho-horizontal<?php } ?>">
<?php if ( ! $load_address ) : ?>

	<?php wc_get_template( 'myaccount/my-address.php' ); ?>

<?php else : ?>

	<form method="post">

		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title ); ?></h3>

		<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

		<?php foreach ( $address as $key => $field ) : ?>

			<?php woocommerce_form_field( $key, $field, ! empty( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : $field['value'] ); ?>

		<?php endforeach; ?>

		<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

		<p>
			<input type="submit" class="button" name="save_address" value="<?php esc_attr_e( 'Save Address', 'woocommerce' ); ?>" />
			<?php wp_nonce_field( 'woocommerce-edit_address' ); ?>
			<input type="hidden" name="action" value="edit_address" />
		</p>

	</form>

<?php endif; ?>
</div>
</div>
</div>
