<?php
global $product, $woocommerce_loop, $paged;

$meta_query = array(
	'relation' => 'AND',
	array('key' => '_visibility', 'value' => 'hidden', 'compare' => '='),
	array('key' => '_gift', 'value' => 'yes', 'compare' => '='),
);
query_posts(array('post_type' => 'product', 'posts_per_page' => -1, 'meta_query' => $meta_query));

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )
	$classes[] = 'last';

?>
<div class="woocommerce">
	<?php if( have_posts() ): ?>
		<?php do_action('woocommerce_before_shop_loop'); ?>
		<?php woocommerce_product_loop_start(); ?>
		<?php while( have_posts() ): the_post(); ?>
		<?php	global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )
	$classes[] = 'last';
?>
<li <?php post_class( $classes ); ?>>
				<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
				<a href="<?php the_permalink(); ?>">
					<?php
						/**
						 * woocommerce_before_shop_loop_item_title hook
						 *
						 * @hooked woocommerce_show_product_loop_sale_flash - 10
						 * @hooked woocommerce_template_loop_product_thumbnail - 10
						 */
						do_action( 'woocommerce_before_shop_loop_item_title' );
					?>
					<h3><?php the_title(); ?></h3>
					<?php
						/**
						 * woocommerce_after_shop_loop_item_title hook
						 *
						 * @hooked woocommerce_template_loop_price - 10
						 */
						do_action( 'woocommerce_after_shop_loop_item_title' );
					?>
				</a>
				<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
			</li>
		<?php endwhile; ?>
		<?php woocommerce_product_loop_end(); ?>
		<?php do_action('woocommerce_after_shop_loop'); ?>
	<?php else: ?>
		<p><?php _e('There are no gift vouchers yet.'); ?></p>
	<?php endif; ?>
</div>
<?php wp_reset_query();?>