<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
extract( $instance );
?>

<div class="clearfix widget store-location">
    <h3 class="widget-title"><?php echo $title ?></h3>
    <div class="yith-wpv-store-location-wrapper">
        <div id="store-maps" class="gmap3" style="height: 300px;"></div>
        <a href="<?php echo $gmaps_link ?>" target="_blank"><?php _e( 'Show in Google Maps', 'yith-woocommerce-product-vendors' ) ?></a>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    $("#store-maps").gmap3({
        map   : {
            options: {
                zoom                     : 15,
                disableDefaultUI         : true,
                mapTypeControl           : false,
                panControl               : false,
                zoomControl              : false,
                scaleControl             : false,
                streetViewControl        : false,
                rotateControl            : false,
                rotateControlOptions     : false,
                overviewMapControl       : false,
                OverviewMapControlOptions: false
            },
            address: "<?php echo $vendor->location ?>"
        },
        marker: {
            address: "<?php echo $vendor->location ?>"
        }
    });
})(jQuery)
</script>