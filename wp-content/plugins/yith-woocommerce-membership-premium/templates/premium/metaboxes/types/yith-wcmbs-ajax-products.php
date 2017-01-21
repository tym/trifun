<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
wp_enqueue_script( 'woocommerce_admin' );
extract( $args );
$is_multiple        = isset( $multiple ) && $multiple;
$multiple           = ( $is_multiple ) ? ' multiple' : '';
$include_variations = isset( $include_variations ) ? $include_variations : false;
$wc_ajax_action = !!$include_variations ? 'woocommerce_json_search_products_and_variations' : 'woocommerce_json_search_products';
$wc_ajax_action = 'yith_wcmbs_json_search_products_and_variations';
if (isset($no_value) && $no_value){
    $value = array();
}
?>

<div id="<?php echo $id ?>-container" <?php if ( isset( $deps ) ): ?>data-field="<?php echo $id ?>" data-dep="<?php echo $deps[ 'ids' ] ?>"
     data-value="<?php echo $deps[ 'values' ] ?>" <?php endif ?>>

    <label for="<?php echo $id ?>"><?php echo $label ?></label>

    <select id="<?php echo $id ?>" name="<?php echo $name ?><?php if ( $is_multiple )
        echo "[]" ?>" multiple="multiple" data-placeholder="<?php _e( 'Search for a product', 'yith-plugin-fw' ) ?>">
        <?php
        if ( $value ) {
            $value = (array)$value;
            foreach ( $value as $product_id ) {
                $product = wc_get_product( $product_id );
                if ( $product ) {
                    echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . $product->get_formatted_name() . '</option>';
                } else {
                    echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . sprintf( __( 'Deleted product by ID: %s', 'yith-woocommerce-membership' ), $product_id ) . '</option>';
                }
            }
        }
        ?>
    </select>

    <span class="desc inline"><?php echo $desc ?></span>
</div>
<script>
    (function ( $ ) {
        $( document ).ready( function () {
            $( "select#<?php echo $id?>" ).ajaxChosen( {
                width:          '149px',
                method:         'GET',
                url:            '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                dataType:       'json',
                afterTypeDelay: 100,
                data:           {
                    action:   '<?php echo $wc_ajax_action; ?>',
                    security: '<?php echo wp_create_nonce( "search-products" ) ?>'
                }
            }, function ( data ) {
                var terms = {};

                $.each( data, function ( i, val ) {
                    terms[ i ] = val;
                } );

                return terms;
            } );
        } )

    })( jQuery );
</script>