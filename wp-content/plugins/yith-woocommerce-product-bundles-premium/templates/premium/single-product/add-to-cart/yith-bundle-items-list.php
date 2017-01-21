<?php
/**
 * Template for bundles
 *
 * @version 4.8.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
global $product;

$bundled_items     = $product->get_bundled_items();
$per_items_pricing = $product->per_items_pricing;
do_action( 'yith_wcpb_before_bundle_items_list' );

if ( $bundled_items ) {
    echo '<table class="yith-wcpb-product-bundled-items">';
    foreach ( $bundled_items as $bundled_item ) {
        /**
         * @var YITH_WC_Bundled_Item $bundled_item
         */
        $bundled_product = $bundled_item->get_product();
        $bundled_post    = $bundled_product->get_post_data();
        $quantity        = $bundled_item->get_quantity();    //FREE
        $hide_thumbnail  = $bundled_item->hide_thumbnail;
        $hidden          = $bundled_item->hidden;
        $bundled_item_id = $bundled_item->item_id;
        $min_qty         = $bundled_item->min_quantity;
        $max_qty         = $bundled_item->max_quantity;
        $item_id         = $bundled_item->item_id;
        $title           = !empty( $bundled_item->title ) ? $bundled_item->title : $bundled_post->post_title;
        $description     = !empty( $bundled_item->description ) ? $bundled_item->description : $bundled_post->post_excerpt;
        $optional        = $bundled_item->optional;

        $my_price_max = $bundled_product->get_display_price( $bundled_product->get_regular_price() );
        $my_discount  = $my_price_max * $bundled_item->discount / 100;
        $my_price     = $my_price_max - $my_discount;

        if ( $hidden )
            continue;

        // free -> premium
        if ( ( !isset( $min_qty ) && !isset( $max_qty ) ) || $min_qty < 1 || $max_qty < 1 ) {
            $min_qty = $quantity;
            $max_qty = $quantity;
        }


        $quantity_lbl = '';
        if ( $min_qty == $max_qty ) {
            $quantity_lbl = $min_qty . ' x ';
        }

        ?>
        <tr class="product">
            <td class="yith-wcpb-product-bundled-item-image">
                <div class="images">
                    <?php

                    if ( !$hide_thumbnail ) {
                        echo $bundled_product->get_image();
                    }

                    ?>
                </div>
                <?php
                if ( !$bundled_item->has_variables() ) {
                    if ( !$per_items_pricing ) {
                        ?>
                        <div class="price">
                            <del><span class="amount"><?php echo wc_price( $my_price_max ) ?></span></del>
                        </div>
                        <?php
                    } else {
                        if ( $my_price_max > $my_price ) {
                            ?>
                            <div class="price">
                                <del><span class="amount"><?php echo wc_price( $my_price_max ) ?></span></del>
                                <ins><span class="amount"><?php echo wc_price( $my_price ) ?></span></ins>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="price">
                                <ins><span class="amount"><?php echo wc_price( $my_price ) ?></span></ins>
                            </div>
                            <?php
                        }
                    }
                } else {
                    //VARIABLE
                    if ( !$per_items_pricing ) {
                        ?>
                        <div class="price">
                            <del></del>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="price">
                            <del></del>
                            <ins></ins>
                        </div>
                        <?php
                    }
                }
                ?>

            </td>
            <td class="yith-wcpb-product-bundled-item-data">

                <h3>
                    <?php if ( $bundled_product->is_visible() ): ?>
                        <a href="<?php echo $bundled_product->get_permalink() ?>">
                            <?php echo $quantity_lbl . $title ?>
                        </a>
                    <?php else: ?>
                        <?php echo $quantity_lbl . $title ?>
                    <?php endif; ?>
                </h3>

                <p><?php echo $description; ?></p>

                <?php if ( $optional && !$bundled_item->has_variables() ) { ?>
                    <input type="checkbox" name="yith_bundle_optional_<?php echo $item_id ?>" class="yith-wcpb-bundled-optional" data-item-id="<?php echo $item_id ?>">
                    <?php if ( !$per_items_pricing ) { ?>
                        <label><?php _e( 'Add', 'yith-woocommerce-product-bundles' ); ?></label>
                    <?php } else { ?>
                        <label><?php echo sprintf( __( 'Add for %s', 'yith-woocommerce-product-bundles' ), wc_price( $my_price ) ); ?></label>
                    <?php } ?>
                <?php } ?>


                <?php
                if ( $bundled_item->has_variables() ) {

                    $b_attributes = $attributes[ $bundled_item_id ];

                    $bundle_product_id = $bundled_item->get_wpml_product_id_current_language();

                    ?>
                    <div class="bundled_item_cart_content variations_form" data-optional="<?php echo( $bundled_item->is_optional() ? 1 : 0 ); ?>"
                         data-type="<?php echo $bundled_product->product_type; ?>"
                         data-product_variations="<?php echo esc_attr( json_encode( $available_variations[ $bundled_item_id ] ) ); ?>"
                         data-bundled_item_id="<?php echo $bundled_item->item_id; ?>" data-product_id="<?php echo $product->id . str_replace( '_', '', $bundled_item->item_id ); ?>"
                         data-bundle_id="<?php echo $product->id; ?>">

                        <input name="yith_bundle_variation_id_<?php echo $bundled_item_id ?>" class="variation_id" value="" type="hidden" data-item-id="<?php echo $bundled_item_id ?>">
                        <?php if ( $optional ) { ?>
                            <input type="checkbox" name="yith_bundle_optional_<?php echo $item_id ?>" class="yith-wcpb-bundled-optional" data-item-id="<?php echo $item_id ?>">
                            <label><?php _e( 'Add', 'yith-woocommerce-product-bundles' ); ?></label>
                        <?php } ?>

                        <table class="variations" cellspacing="0">
                            <tbody>
                            <?php $loop = 0;
                            foreach ( $b_attributes as $name => $options ) : $loop++; ?>
                                <tr>
                                    <td class="label"><label for="<?php echo sanitize_title( $name ); ?>"><?php echo wc_attribute_label( $name ); ?></label></td>

                                    <td class="value"><select data-type="select" class="yith-wcpb-select-for-variables"
                                                              id="<?php echo esc_attr( sanitize_title( $name ) ) . '_' . $bundled_item_id;; ?>"
                                                              name="yith_bundle_attribute_<?php echo sanitize_title( $name ) . '_' . $bundled_item_id; ?>"
                                                              data-attribute_name="attribute_<?php echo sanitize_title( $name ); ?>">
                                            <option value=""><?php echo __( 'Choose an option', 'woocommerce' ) ?>&hellip;</option>
                                            <?php
                                            if ( is_array( $options ) ) {
                                                if ( isset( $_REQUEST[ 'yith_bundle_attribute_' . sanitize_title( $name ) . '_' . $bundled_item_id ] ) ) {
                                                    $selected_value = $_REQUEST[ 'yith_bundle_attribute_' . sanitize_title( $name ) . '_' . $bundled_item_id ];
                                                } elseif ( isset( $selected_attributes[ $bundled_item_id ][ sanitize_title( $name ) ] ) ) {
                                                    $selected_value = $selected_attributes[ $bundled_item_id ][ sanitize_title( $name ) ];
                                                } else {
                                                    $selected_value = '';
                                                }

                                                // Get terms if this is a taxonomy - ordered
                                                if ( taxonomy_exists( $name ) ) {
                                                    $bundle_product_id = $bundled_item->get_wpml_product_id_current_language();

                                                    $terms = wc_get_product_terms( $bundle_product_id, $name, array( 'fields' => 'all' ) );

                                                    foreach ( $terms as $term ) {

                                                        if ( !in_array( $term->slug, $options ) ) {
                                                            continue;
                                                        }
                                                        echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $term->slug ), false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';

                                                    }

                                                } else {
                                                    foreach ( $options as $option ) {
                                                        echo '<option value="' . $option . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $option ), false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
                                                    }

                                                }
                                            }
                                            ?>
                                        </select> <?php
                                        if ( sizeof( $b_attributes ) === $loop ) {
                                            echo '<a class="reset_variations" href="#reset">' . __( 'Clear selection', 'woocommerce' ) . '</a>';
                                        }
                                        ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="single_variation_wrap bundled_item_wrap" style="display:none;">
                            <div class="single_variation bundled_item_cart_details"></div>

                            <?php if ( $min_qty < $max_qty ) { ?>
                                <input step="1" min="<?php echo $min_qty ?>" max="<?php echo $max_qty ?>" name="yith_bundle_quantity_<?php echo $item_id ?>"
                                       value="<?php echo $min_qty ?>"
                                       title="Qty" class="input-text qty text yith-wcpb-bundled-quantity" size="4" type="number">
                            <?php } else { ?>
                                <input class="yith-wcpb-bundled-quantity" name="yith_bundle_quantity_<?php echo $item_id ?>" value="<?php echo $min_qty ?>" type="hidden">
                            <?php } ?>
                            <?php /*
												woocommerce_quantity_input(array(
												'input_name'	=> 'yith_bundle_quantity_'. $item_id,
												'min_value'		=> intval($min_qty),
												'max_value'		=> intval($max_qty),
												'input_value'	=> intval($min_qty)
											));
											*/
                            ?>

                        </div>

                    </div>
                    <?php
                }
                ?>

                <?php if ( !$bundled_item->has_variables() ) { ?>
                    <?php if ( $min_qty < $max_qty ) { ?>
                        <input step="1" min="<?php echo $min_qty ?>" max="<?php echo $max_qty ?>" name="yith_bundle_quantity_<?php echo $item_id ?>" value="<?php echo $min_qty ?>"
                               title="Qty"
                               class="input-text qty text yith-wcpb-bundled-quantity" size="4" type="number">
                    <?php } else { ?>
                        <input name="yith_bundle_quantity_<?php echo $item_id ?>" value="<?php echo $min_qty ?>" type="hidden" class="yith-wcpb-bundled-quantity">
                    <?php } ?>

                    <?php
                    if ( $bundled_product->managing_stock() ) {
                        echo '<div class="yith-wcpb-product-bundled-item-availability not-variation">';

                        $bp_availability      = $bundled_product->get_availability();
                        $bp_availability_html = empty( $bp_availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $bp_availability[ 'class' ] ) . '">' . esc_html( $bp_availability[ 'availability' ] ) . '</p>';
                        echo apply_filters( 'woocommerce_stock_html', $bp_availability_html, $bp_availability[ 'availability' ], $bundled_product );

                        echo '</div>';
                    }
                    ?>

                <?php } ?>
            </td>
        </tr>
        <?php
    }
    echo '</table>';
}

do_action( 'yith_wcpb_after_bundle_items_list' );



