<?php
/**
 * Admin Bundle Options TAB
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$bundle_data = get_post_meta( $post->ID, '_yith_wcpb_bundle_data', true );

$default_advanced_options = array(
    'min' => 0,
    'max' => 0,
);
$advanced_options         = get_post_meta( $post->ID, '_yith_wcpb_bundle_advanced_options', true );
$advanced_options         = wp_parse_args( $advanced_options, $default_advanced_options );

?>
<div id="yith_bundled_product_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

    <div class="options_group yith-wcpb-bundle-metaboxes-wrapper">

        <div id="yith-wcpb-bundle-metaboxes-wrapper-inner">

            <p class="toolbar">
                <a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
                <a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
            </p>

            <div class="yith-wcpb-bundled-items wc-metaboxes ui-sortable">
                <?php
                if ( !empty( $bundle_data ) ) {
                    $i = 0;
                    foreach ( $bundle_data as $item_id => $item_data ) {
                        //$metabox_id     = $item_data[ 'bundle_order' ];
                        $i++;
                        $metabox_id = $i;
                        $post_id    = $post->ID;
                        $product_id = $item_data[ 'product_id' ];

                        $title       = get_the_title( $product_id );
                        $open_closed = 'closed';
                        ob_start();
                        include YITH_WCPB_TEMPLATE_PATH . '/premium/admin/admin-bundled-product-item.php';
                        echo ob_get_clean();
                    }
                }
                ?>
            </div>
            <p class="yith-wcpb-bundled-prod-toolbar toolbar">
                            <span class="yith-wcpb-bundled-prod-toolbar-wrapper">
                                <span class="yith-wcpb-bundled-prod-selector">
                                    <input type="hidden" class="wc-product-search" style="width: 250px;" id="yith-wcpb-bundled-product" name="yith_wcpb_bundled_product"
                                           data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products"
                                           data-multiple="false" data-selected="" value=""/>
                                </span>
                                <button type="button" id="yith-wcpb-add-bundled-product"
                                        class="button button-primary"><?php _e( 'Add Product', 'yith-woocommerce-product-bundles' ); ?></button>
                            </span>
            </p>
        </div>
    </div>
</div>

<div id="yith_bundle_options" class="panel woocommerce_options_panel wc-metaboxes-wrapper">
    <div class="options_group">
        <p class="form-field">
            <label for="_yith_wcpb_bundle_advanced_options_min"><?php _e( 'Minimum number of items in bundle', 'yith-woocommerce-product-bundles' ) ?></label>
            <input type="number" class="short"
                   name="_yith_wcpb_bundle_advanced_options[min]"
                   id="_yith_wcpb_bundle_advanced_options_min"
                   min="0"
                   step="1"
                   value="<?php echo $advanced_options[ 'min' ] ?>"
                   placeholder="0">
            <span class="woocommerce-help-tip" data-tip="<?php _e( 'Minimum number of items/products that customers have to pick in order to be able to add the bundle to the cart', 'yith-woocommerce-product-bundles' ) ?>"></span>
        </p>

        <p class="form-field">
            <label for="_yith_wcpb_bundle_advanced_options_max"><?php _e( 'Maximum number of items in bundle', 'yith-woocommerce-product-bundles' ) ?></label>
            <input type="number" class="short"
                   name="_yith_wcpb_bundle_advanced_options[max]"
                   id="_yith_wcpb_bundle_advanced_options_max"
                   min="0"
                   step="1"
                   value="<?php echo $advanced_options[ 'max' ] ?>"
                   placeholder="0">
            <span class="woocommerce-help-tip" data-tip="<?php _e( 'Maximum number of items/products that customers can pick in order to be able to add the bundle to the cart', 'yith-woocommerce-product-bundles' ) ?>"></span>
        </p>
    </div>
</div>