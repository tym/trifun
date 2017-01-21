<?php
/**
 * Plugin Name: WooCommerce Gift Card
 * Plugin URI: http://codemypain.com
 * Description: WooCommerce extension that provides the functionality of selling gift card vouchers on your store.
 * Version: 2.3
 * Author: Isaac Oyelowo
 * Author URI: http://isaacoyelowo.com
 * Requires at least: 3.0
 * Tested up to: 4.2
 */
class SB_WCGifts
{
	public function __construct()
	{
	    $this->_plugin_dir = dirname(__FILE__);
		$this->_plugin_url = get_site_url(null, 'wp-content/plugins/' . basename($this->_plugin_dir));
		$this->addActions();
		$this->addFilters();
		$this->addShortcodes();
	}
	public static function onActivate()
	{
	    $tps = 'You have received new gift card(s)';
		$tpl = "<html><body>\n\n"."<h1>Howdy [receiver_name],</h1><br />\n\n".
				"You have received new gift voucher(s) <strong>[coupons]</strong> to use for shopping on [blog_name]."
				."\n\n" . "Your gift voucher is redeemable at [site_url]\n\n".
				"[receiver_contents]\n\n"."</body></html>";
				
		update_option('sb_wc_gift_email_tpl', stripslashes($tpl));
		update_option('sb_wc_gift_email_tps', stripslashes($tps));
	}
	
	public function addFilters()
	{
	    add_filter('product_type_options', array($this, 'filter_product_type_options'));
		if( is_admin() )
		{
			add_filter('woocommerce_product_data_tabs', array($this, 'filter_woocommerce_product_data_tabs'));
			add_filter('woocommerce_settings_tabs_array', array($this, 'filter_woocommerce_settings_tabs_array'), 50);
		}
	}
	
	public function addActions()
	{
		if( is_admin() ):
			add_action('woocommerce_process_product_meta_simple', array($this, 'action_woocommerce_process_product_meta_simple'));
			add_action('woocommerce_product_data_panels', array($this, 'wgc_metabox'));
			add_action('woocommerce_settings_gift_settings', array($this, 'settings_gift_settings'));
			add_action('woocommerce_settings_save_gift_settings', array($this, 'action_save_settings'));
		else:
			add_action('woocommerce_before_order_notes', array($this, 'action_woocommerce_before_order_notes'));		
		endif;		
        add_filter( 'wp_mail_content_type', array($this,'wgc_set_html_content_type') );
        add_action('save_post',array($this, 'save_wgc_metabox')); 
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'gift_cart'), 5 );
        add_action('woocommerce_add_to_cart', array($this,  'add_to_cart_hook'));
        add_action('woocommerce_before_calculate_totals',  array($this,  'add_custom_price' ));
		add_action('woocommerce_order_status_completed', array($this, 'actionPaymentComplete'));
		add_action('woocommerce_order_status_processing', array($this, 'actionPaymentComplete'));
		add_action('woocommerce_checkout_update_order_meta', array($this, 'action_woocommerce_checkout_update_order_meta'), 10, 2);
		add_action('woocommerce_add_order_item_meta', array($this, 'action_woocommerce_add_order_item_meta'), 10, 2);
		add_action('init', array($this,'wgc_localize') );
	}
	
	
	public function wgc_localize()  {
        // Localization
		load_plugin_textdomain('wgc', false, dirname(plugin_basename(__FILE__)). "/languages" );
    }
	
	public function wgc_set_html_content_type() 
	{
		return 'text/html';
    }
	
	public function filter_woocommerce_product_data_tabs($tabs)
	{
		$tabs['gift'] = array(
				'label'  => __( 'Gift Card', 'wgc' ),
				'target' => 'gift_data',
				'class'  => array( 'hide_if_grouped' ),
		);
		
		return $tabs;
	}
	
	public function wgc_metabox()
	{
	    global $post;
	    $dgift_op = get_post_meta($post->ID, 'dgift_op', true);
		?>
		<div id="gift_data" class="panel woocommerce_options_panel sb-wc-cp-panel" style="border-bottom:1px solid #ddd">
	<h2><?php _e('Gift Card Settings','wgc'); ?></h2>
	<div class="options_group" >
    <p class="form-field">
		<label><?php _e('Duration of coupon expiry' , 'wgc'); ?></label>
		<input type="text" name="wgc_duration" value="<?php print get_post_meta($post->ID, 'wgc_duration', true);?>" size="100" style="padding:3px;width:150px;" />
		<span class="description"><?php _e('days(will override expiry date below)','wgc'); ?></span>
		</p>
		<p class="form-field">
		<label><?php _e('Expiry Ddte' , 'wgc'); ?></label>
		<input type="text" name="wgc_expiry" id="wgc_expiry" value="<?php print get_post_meta($post->ID, 'wgc_expiry', true); ?>" size="100" style="padding:3px;width:150px;" />
		</p>
		<div class="options_group" style="border-top:1px solid #ddd">
	<p class="form-field">
		<label><?php _e('Exclude products by ID' , 'wgc'); ?></label>
		<input type="text" name="wgc_products_id" value="<?php print get_post_meta($post->ID, 'wgc_products_id', true); ?>" />
		<span class="description"><?php _e('(separate each ID with comma)','wgc'); ?></span>
	</p>
</div>
	<div class="options_group" style="border-top:1px solid #ddd">
	<p class="form-field">
		<label><?php _e('Allow buyers to enter gift amount' , 'wgc'); ?></label>
		<input type="checkbox" name="dgift_op" value="yes" <?php print $dgift_op == 'yes' ? 'checked' : ''; ?> />
	</p>
	<p class="form-field">
		<label><?php _e('Coupon code prefix in lowercase(optional)' , 'wgc'); ?></label>
		<input type="text" name="wgc_code" id="wgc_expiry" placeholder="xxx" value="<?php print get_post_meta($post->ID, 'wgc_code', true); ?>" size="100" style="padding:3px;width:150px;" />
	    <span class="description"><?php _e('Recommended 3 random alphabets','wgc'); ?></span>
	</p>
	</div>
	</div>
	</div>
	<script>
jQuery(function()
{
	jQuery('#wgc_expiry').datepicker({ dateFormat: "yy-mm-dd" });
});
</script>
	<?php
    }
    public function save_wgc_metabox($post_id)
	{
		if( !isset($_POST['post_type']) || $_POST['post_type'] != 'product')
			return false;
		if( isset( $_POST[ 'wgc_duration' ] ) ) 
		{
            update_post_meta( $post_id, 'wgc_duration', sanitize_text_field( $_POST[ 'wgc_duration' ] ) );
        }
		if( isset( $_POST[ 'wgc_expiry' ] ) ) 
		{
            update_post_meta( $post_id, 'wgc_expiry', sanitize_text_field( $_POST[ 'wgc_expiry' ] ) );
        }
		if( isset( $_POST[ 'wgc_products_id' ] ) ) 
		{
            update_post_meta( $post_id, 'wgc_products_id', sanitize_text_field( $_POST[ 'wgc_products_id' ] ) );
        }
        if( isset( $_POST[ 'wgc_code' ] ) ) 
		{
            update_post_meta( $post_id, 'wgc_code', sanitize_text_field( $_POST[ 'wgc_code' ] ) );
        }
        if( !isset($_POST['dgift_op']) )
		{
			update_post_meta( $post_id,'dgift_op', 'no');
		}
		else
		{
		update_post_meta( $post_id,'dgift_op', 'yes');
		}
	}
	
	public function filter_woocommerce_settings_tabs_array($tabs)
	{
		$tabs['gift_settings'] = __('Gift Card','wgc');
		return $tabs;
	}
	
	public function action_save_settings()
	{
	    if( isset($_POST['tpl']) )
		{
			update_option('sb_wc_gift_email_tpl', stripslashes($_POST['tpl']));
		}
		if( isset($_POST['tps']) )
		{
		    update_option('sb_wc_gift_email_tps', stripslashes($_POST['tps']));
		}
		if( !isset($_POST['dgift']) )
		{
			update_option('dgift', 'no');
		}
		else
		{
		update_option('dgift', 'yes');
		}
	}

	public function gift_cart() 
	{
		global $post;
		if(get_post_meta($post->ID, 'dgift_op', true) == 'yes')
		{
			echo '<input type="text" name="gift_cart"  placeholder="Enter Price" style="display:block;margin-bottom:10px;" />
			<style type="text/css">span.amount{display:none;}</style>';
		}
	}

	public function add_to_cart_hook($key)
    {
        global $woocommerce;
		if(isset($_POST['gift_cart']))
		{
			$newprice = $_POST['gift_cart'];
			foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) 
            {
                $thousands_sep  = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
                $decimal_sep = stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );
                $newprice = str_replace($thousands_sep, '', $newprice);
                $newprice = str_replace($decimal_sep, '.', $newprice);
                $_POST['price'] = wc_format_decimal($newprice);
                if($cart_item_key == $key)
                {
                    $values['data']->set_price($newprice);
                    $woocommerce->session->__set($key .'_named_price', $newprice);
			    }
		    }
		}
    return $key;
    }
	
	public function add_custom_price( $cart_object ) {
        global $woocommerce;
        foreach ( $cart_object->cart_contents as $key => $value ) 
        {
            $named_price = $woocommerce->session->__get($key .'_named_price');
            if($named_price)
            {
                $value['data']->price = $named_price;
            }
		}
    }
	    
	public function settings_gift_settings()
	{	
	    $email_tpl = get_option('sb_wc_gift_email_tpl');
		$email_tps = get_option('sb_wc_gift_email_tps');
		$dgift = get_option('dgift');
		?>
		<div class="wrap">
			<h2><?php _e('Gift Settings' , 'wgc'); ?></h2>
			<p class="form-field">
			<label><?php _e('Hide gift cards from shop page : ','wgc'); ?></label>
			<input type="checkbox" name="dgift" value="yes" class="checkbox" <?php print $dgift == 'yes' ? 'checked' : ''; ?> />
			<span class="description">This will hide gift cards from the shop page and can be placed on any page using the shortcode[coupon_page].
			If this is changed,you will need to update all your gift card products manually!</span>
		</p>
			<p>
			<label>
				<strong><?php _e('Email subject' , 'wgc'); ?></label></strong><br />
				<input type="text" name="tps" style="width:300px;" value="<?php print $email_tps; ?>" />
				</p>
					<strong><label><?php _e('Email template' , 'wgc'); ?></label></strong><br/>
					<textarea rows="" cols="" style="width:50%;height:200px;" name="tpl"><?php print $email_tpl; ?></textarea>
				</p>
		</div>
		<?php 
		}
// style --	.woocommerce ul.products li.first, .woocommerce-page ul.products li.first {clear:none !important}
	public function action_woocommerce_add_order_item_meta($item_id, $values)
	{
	    global $woocommerce,$post_id,$order_id ,$wpdb ,$user_id,$user;
	    $gift = get_post_meta($values['product_id'], '_gift', 1);
	    if( $gift != 'yes' )
	    	return false;
	    $this->orderID = $order_id;
        $customer_orders = get_posts(array(
            'numberposts' => '1',
            'meta_key' => '_customer_user',
            'meta_value' => get_current_user_id(),
            'post_type' => 'shop_order',
            'post_status' => 'publish'
        ));

        foreach ($customer_orders as $customer_order) {
            $order = new WC_Order();

            $order->populate($customer_order);
            

            $status = get_term_by('slug', $order->status, 'shop_order_status');
            $item_count = $order->get_item_count();
		}
        $amount = $values['line_total']/$values['quantity'];
		$usage = get_post_meta($values['product_id'], 'wgc_limit', 1);
		
		if(get_post_meta($values['product_id'], 'wgc_duration', true) != null || get_post_meta($values['product_id'], 'wgc_duration', true) !="")
		{
		    $duration = '+'. get_post_meta($values['product_id'], 'wgc_duration', true) . ' days';
		    $expiry = date('Y-m-d', strtotime($duration));
		}
		else
		{
		    $expiry = get_post_meta($values['product_id'], 'wgc_expiry', 1);
		}
		$exclude = get_post_meta($values['product_id'], 'wgc_products_id', 1);
		$coupon_codes = $coupon_ids = '';
		for($i = 0; $i < $values['quantity']; $i++)
		{
			$number = strtolower(substr(md5($values['product_id'].$item_id.$amount.time()), rand(0, 10), 5));
			if(get_post_meta($values['product_id'], 'wgc_code', 1) !=""){
				$wcg = get_post_meta($values['product_id'], 'wgc_code', 1);
			}else{
				$wcg = 'wcg';
			}
			$wcg = strtolower($wcg);
			$coupon_code = $wcg.'-'.$number.'-'.$amount;
			$coupon_ids .= $this->createCoupon($coupon_code, $amount, $usage, $expiry, $exclude) . '|';
			$coupon_codes .= $coupon_code . ',';
			$bar_url = $this->_plugin_url . '/code';
			$barcode .= "<img src='$bar_url/barcode.php?text=$coupon_code' alt='barcode' />" ."<br />";
		}
		$coupon_codes = substr($coupon_codes, 0, -1);
		woocommerce_add_order_item_meta($item_id, '_coupon_ids', $coupon_ids);
		woocommerce_add_order_item_meta($item_id, '_coupon_codes', $coupon_codes);
		woocommerce_add_order_item_meta($item_id, '_barcodes', $barcode);
	}
	public function action_woocommerce_process_product_meta_simple($product_id)
	{
	    $dgift = get_option('dgift');
		$is_gift = isset( $_POST['_gift'] ) ? 'yes' : 'no';
		update_post_meta($product_id, '_gift', $is_gift);
		update_post_meta($product_id, '_visibility', ($is_gift && $dgift == 'yes') ? 'hidden' : 'visible');
	}
	public function action_woocommerce_checkout_update_order_meta($order_id)
	{
		update_post_meta($order_id, '_gift_receiver_name', trim(@$_POST['gift_receipt_name']));
		update_post_meta($order_id, '_gift_receiver_email', trim(@$_POST['gift_receipt_email']));
		update_post_meta($order_id, '_gift_receiver_msg', trim(@$_POST['gift_receipt_msg']));
	}
	public function action_woocommerce_before_order_notes($checkout)
	{
		global $woocommerce;
		//print_r($woocommerce->cart->get_cart());
		$exists_gift = false;
		foreach($woocommerce->cart->get_cart() as $cart_item_key => $p)
		{
			$gift = get_post_meta($p['product_id'], '_gift', 1);
			if( $gift == 'yes' )
			{
				$exists_gift = true;
				break;
			}
		}
		if( !$exists_gift )
			return false;
		?>
		<div style="clear:both;"></div>
		<h3><?php _e('I\'m sending this Gift Card to someone' , 'wgc') ;?></h3>
		<p class="form-row form-row-wide">
			<label><?php _e('Recipient\'s name' , 'wgc'); ?></label>
			<input type="text" class="input-text" name="gift_receipt_name" style="width:200px;" />
		</p>
		<p class="form-row form-row-wide">
			<label><?php _e('Recipient\'s email' , 'wgc'); ?></label>
			<input type="text" class="input-text" name="gift_receipt_email" style="width:200px;" />
		</p>
		<p class="form-row form-row-wide">
		    <label><?php _e('Message to Recipient' , 'wgc'); ?></label>
		    <textarea style="width:200px;" name="gift_receipt_msg"></textarea>
		</p>
		<?php 
	}

	public function actionPaymentComplete($order_id)
	{
		$order = new WC_Order($order_id);
		$rname =  get_post_meta($order_id, '_gift_receiver_name', 1);
		$remail = get_post_meta($order_id, '_gift_receiver_email', 1);
		$rmsg = get_post_meta($order_id, '_gift_receiver_msg', 1);
		$email_tpl = get_option('sb_wc_gift_email_tpl');
		$subject = get_option('sb_wc_gift_email_tps');
		//self::log($email_tpl);
		$customer = new WP_User(get_post_meta($order_id, '_customer_user', 1));
		$to = empty($remail) ? $order->billing_email : $remail;
		$message = str_replace('[receiver_name]', empty($rname) ? sprintf("%s %s", $order->billing_first_name, $order->billing_last_name) : $rname, $email_tpl);
		$coupons = '';
		//self::log(get_post_meta($order_id));
		foreach($order->get_items() as $item_id => $item)
		{
			//check if order item is a product gift
			$is_gift = get_post_meta($item['product_id'], '_gift', 1);
			if( $is_gift != 'yes' ) continue;
			$coupons .= sprintf(" %s\n", $item['coupon_codes']);
			$coupons = $coupons . ' ';
			$barcodes .= sprintf(" %s\n", $item['barcodes']);
			$barcodes = $barcodes . '<br />';
		    $total += $item['line_total'];
		if( empty($coupons) )
		{
			return false;		
		}
		$blogname = get_bloginfo();
		$siteurl = '<a href="'.site_url().'">'.site_url().'</a>' ;
		$date = date("Y-m-d" ) ;
		$message = str_replace('[blog_name]', $blogname, $message);
		$message = str_replace('[site_url]', $siteurl, $message);
		$message = str_replace('[date]', $date, $message);
		$message = str_replace('[quantity]', $item['qty'], $message);
		$message = str_replace('[sub_total]', $item['line_subtotal'], $message);
		$message = str_replace('[tax]', $item['line_tax'], $message);
	    }
	    $message = str_replace('[coupons]', $coupons, $message);
	    $message = str_replace('[barcode]', $barcodes, $message);
	    $message = str_replace('[total]', $total, $message);
		if (!empty ($rname) )
	    {
		$contents = '<br />Additional message from sender:<br />'. $rmsg.'<br /><br />You\'ve received the Gift Voucher with kind heart from ' . $order->billing_first_name. ' ' . $order->billing_last_name ;
		}
		$message = str_replace('[receiver_contents]', $contents, $message);
		if( $is_gift == 'yes' )
		{
		$admin_mail = get_option( 'admin_email' );
		wp_mail($to,$subject, $message, array("From: $blogname <$admin_mail>"));
	    }
	}

	public function addShortcodes()
	{
		add_shortcode('coupon_page', array($this, 'shortcode_coupon_page'));
	}
	public function filter_product_type_options($types)
	{
		$types['gift'] = array('id' => '_gift', 
								'wrapper_class' => 'show_if_simple show_if_variable',
								'label' => __( 'Gift', 'wgc'),
								'description' => __( 'Gift products.', 'wgc'));
		return $types;
	}
	public function shortcode_coupon_page($atts)
	{
		ob_start();
		require_once dirname(__FILE__) . '/frontend/gifts-listing.php';
		return ob_get_clean();
	}
	public function createCoupon($coupon_code, $amount, $usage, $expiry, $exclude)
	{
		//$coupon_code = 'UNIQUECODE'; // Code
		//$amount = '10'; // Amount
		$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
		$coupon = array(
				'post_title' => $coupon_code,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'shop_coupon'
		);
		$new_coupon_id = wp_insert_post( $coupon );
		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
		update_post_meta( $new_coupon_id, 'individual_use', 'no' );
		update_post_meta( $new_coupon_id, 'product_ids', '' );
		update_post_meta( $new_coupon_id, 'exclude_product_ids', $exclude );
		update_post_meta( $new_coupon_id, 'usage_limit', '1' );
		update_post_meta( $new_coupon_id, 'expiry_date', $expiry );
		update_post_meta( $new_coupon_id, 'apply_before_tax', 'no' );//fix for coupon codes not applying to shipping fee
		update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
		
		return $new_coupon_id;
	}
	public static function log($str)
	{
		$log_file = dirname(__FILE__) . '/log.txt';
		$fh = file_exists($log_file) ? fopen($log_file, 'a+') : fopen($log_file, 'w+');
		fwrite($fh, print_r($str, 1)."\n");
		fclose($fh);
	}
}
global $sb_instances;
if( !is_array($sb_instances) )
	$sb_instances = array();
register_activation_hook(__FILE__, array('SB_WCGifts', 'onActivate'));
$sb_instances['sb_wc_gifts'] = new SB_WCGifts();