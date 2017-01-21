<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="<?php echo $store_header_class ?>">
    <!--  Header Image -->
    <?php if( ! empty( $vendor->header_image) ): ?>
        <?php echo $header_image ?>
    <?php endif; ?>

    <!--  Store Information -->
    <div class="store-info <?php echo $header_skin ?>">
        <div class="owner-avatar">
            <?php if( $show_gravatar ) : ?>
                <span class="avatar">
                    <?php echo $owner_avatar ?>
                </span>
            <?php endif; ?>
            <span class="store-name">
                <?php echo $name ?>
            </span>
        </div>
        <div class="store-contact">
            <?php if( ! empty( $vendor->location ) ) : ?>
                <span class="store-location">
                    <i class="fa fa-location-arrow"></i>
                    <?php echo $vendor->location ?>
                </span>
            <?php endif; ?>
            <?php if( ! empty( $vendor->telephone ) ) : ?>
                <span class="store-telephone">
                    <i class="fa fa-phone"></i>
                    <?php echo $vendor->telephone ?>
                </span>
            <?php endif; ?>
            <?php if( ! empty( $vendor->store_email ) ) : ?>
                <span class="store-email">
                    <i class="fa fa-envelope"></i>
                    <a class="store-email-link" href="mailto:<?php echo $vendor->store_email ?>">
                        <?php echo $vendor->store_email ?>
                    </a>
                </span>
            <?php endif; ?>
            <?php if( $show_vendor_vat && ! empty( $vendor->vat ) ) : ?>
                <span class="store-vat">
                    <i class="<?php echo $icons['vat'] ?>"></i>
                    <?php printf( '%s: %s', apply_filters( 'yith_wcmv_tax_label_frontend', __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ) ), $vendor->vat ); ?>
                </span>
            <?php endif; ?>
            <?php if( ! empty( $vendor->legal_notes ) ) : ?>
                <span class="store-vat">
                    <i class="<?php echo $icons['legal_notes'] ?>"></i>
                    <?php printf( __( '%s', 'yith-woocommerce-product-vendors' ), $vendor->legal_notes ); ?>
                </span>
            <?php endif; ?>
            <?php if( ! empty( $vendor_reviews['reviews_product_count'] ) ) : ?>
                <span class="store-rating">
                    <i class="<?php echo $icons['rating'] ?>"></i>
                    <?php printf(
                        _n( '%s average rating from %d review', '%s average rating from %d reviews', $vendor_reviews['reviews_product_count'],'yith-woocommerce-product-vendors' ),
                        $vendor_reviews['average_rating'], $vendor_reviews['reviews_product_count'] ); ?>
                </span>
            <?php endif; ?>
            <?php if( $show_total_sales ) : ?>
                <span class="store-sales">
                    <i class="<?php echo $icons['sales'] ?>"></i>
                    <?php printf( __( 'Total sales: %d', 'yith-woocommerce-product-vendors' ), $total_sales ); ?>
                </span>
            <?php endif; ?>
        </div>

    </div>

    <!--  Store Information -->
    <div class="store-socials">
        <span class="socials-container">
            <?php foreach( $vendor->socials as $social => $uri ) : ?>
                <?php if( ! empty( $uri ) ) : ?>
                    <a class="vendor-social-uri" href="<?php echo $uri ?>" target="_blank">
                        <i class="fa <?php echo $socials_list['social_fields'][ $social ]['icon'] ?>"></i>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </span>
    </div>
</div>