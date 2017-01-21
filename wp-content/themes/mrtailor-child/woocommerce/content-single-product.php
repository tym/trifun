<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
    global $post, $product, $mr_tailor_theme_options;

    //woocommerce_before_single_product
	//nothing changed
	
	//woocommerce_before_single_product_summary
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	
	add_action( 'woocommerce_before_single_product_summary_sale_flash', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_images', 20 );
	
	//woocommerce_single_product_summary
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	
	add_action( 'woocommerce_single_product_summary_single_title', 'woocommerce_template_single_title', 5 );
	add_action( 'woocommerce_single_product_summary_single_rating', 'woocommerce_template_single_rating', 10 );
	add_action( 'woocommerce_single_product_summary_single_price', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary_single_excerpt', 'woocommerce_template_single_excerpt', 20 );
	add_action( 'woocommerce_single_product_summary_single_add_to_cart', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary_single_meta', 'woocommerce_template_single_meta', 40 );
	add_action( 'woocommerce_single_product_summary_single_sharing', 'woocommerce_template_single_sharing', 50 );
	
	//woocommerce_after_single_product_summary
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
// 	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_after_single_product_summary_data_tabs', 'woocommerce_output_product_data_tabs', 10 );

	//woocommerce_after_single_product
	//nothing changed

	//custom actions
	add_action( 'woocommerce_before_main_content_breadcrumb', 'woocommerce_breadcrumb', 20, 0 );
	add_action( 'woocommerce_product_summary_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
	
	$product_page_has_sidebar = false;
	
	if ( (isset($mr_tailor_theme_options['products_layout'])) && ($mr_tailor_theme_options['products_layout'] == "0" ) ) {
		
		$product_page_has_sidebar = false;
	
	} else {
	
		$product_page_has_sidebar = true;
	
	}
	
?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="container">
		<div class="row">
			
			<div id="page-thumb" class="large-3 push-0 columns">

				<?php the_post_thumbnail(); ?>
				
				<div class="venue-info">
					<p class="phone"><strong>Phone #: </strong><br/><?php the_field('phone'); ?></p>
					<p class="location"><strong>Location: </strong><br/><?php the_field('location'); ?></p>
					<p class="website"><strong>Website: </strong><br/><a href="http://<?php echo get_field('website'); ?>" target="blank"><?php the_field('website'); ?></a></p>
					<div class="content"><?php the_content(); ?></div>				
				</div>
			</div>
			
			<div id="page-content" class="large-9 push-0 columns">
				<?php the_field('description'); ?>
				<div class="prod-price"><?php do_action( 'woocommerce_single_product_summary_single_price' ); ?></div>
				<div class="add"><?php do_action( 'woocommerce_single_product_summary_single_add_to_cart' ); ?></div>
				<p class="the-button"><a class="buy-now" href="<?php echo get_site_url(); ?>/<?php echo get_field('buy_now_button'); ?>">Buy Membership Now!</a></p>
				<p class="message"><em>*Multiple memberships purchased together will be listed on one membership card. If you prefer separate membership cards for each member, they must be purchased in separate transactions (once you create your account you can use the same account for each of the separate transactions).</em></p>
			</div>
			
		</div>
	</div>

</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>