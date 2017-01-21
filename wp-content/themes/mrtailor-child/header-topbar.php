<div id="site-top-bar">

    <div class="row">

        <div id="lang-message" class="large-8 columns">

			<div class="language-and-currency">

				<?php if (function_exists('icl_get_languages')) { ?>

					<?php $additional_languages = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str'); ?>

					<select class="topbar-language-switcher">
						<option><?php echo ICL_LANGUAGE_NAME; ?></option>
						<?php

						if (count($additional_languages) > 1) {
							foreach($additional_languages as $additional_language){
							  if(!$additional_language['active']) $langs[] = '<option value="'.$additional_language['url'].'">'.$additional_language['native_name'].'</option>';
							}
							echo join(', ', $langs);
						}

						?>
					</select>

				<?php } ?>

				<?php if (class_exists('woocommerce_wpml')) { ?>
					<?php echo(do_shortcode('[currency_switcher]')); ?>
				<?php } ?>

			</div><!--.language-and-currency-->
			
			<a id="home-icon" href="<?php echo site_url(); ?>"><i class="fa fa-home"></i></a>
			
            <!-- <div class="site-top-message"><?php if ( isset($mr_tailor_theme_options['top_bar_text']) ) _e( $mr_tailor_theme_options['top_bar_text'], 'mr_tailor' ); ?></div> -->

			<nav id="site-navigation-top-bar" class="main-navigation" role="navigation">
				<?php
                    wp_nav_menu(array(
                        'theme_location'  => 'top-bar-navigation',
                        'fallback_cb'     => false,
                        'container'       => false,
                        'items_wrap'      => '<ul id="%1$s">%3$s</ul>',
                    ));
                ?>

                <?php if ( is_user_logged_in() ) { ?>
                    <ul id="login"><li><a href="<?php echo get_site_url(); ?>/?<?php echo get_option('woocommerce_logout_endpoint'); ?>=true" class="logout_link"><?php _e('Logout', 'mr_tailor'); ?></a></li></ul>
                <?php } ?>

            </nav>

        </div><!-- .large-6 .columns -->

        <div id="custom-nav" class="large-4 columns">

            <div class="site-social-icons-wrapper">
                <div class="site-social-icons">
                    <ul class="//animated //flipY">
                        <?php if ( (isset($mr_tailor_theme_options['facebook_link'])) && (trim($mr_tailor_theme_options['facebook_link']) != "" ) ) { ?><li class="site-social-icons-facebook"><a target="_blank" href="<?php echo $mr_tailor_theme_options['facebook_link']; ?>"><i class="fa fa-facebook"></i><span>Facebook</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['twitter_link'])) && (trim($mr_tailor_theme_options['twitter_link']) != "" ) ) { ?><li class="site-social-icons-twitter"><a target="_blank" href="<?php echo $mr_tailor_theme_options['twitter_link']; ?>"><i class="fa fa-twitter"></i><span>Twitter</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['vkontakte_link'])) && (trim($mr_tailor_theme_options['vkontakte_link']) != "" ) ) { ?><li class="site-social-icons-vkontakte"><a target="_blank" href="<?php echo $mr_tailor_theme_options['vkontakte_link']; ?>"><i class="fa fa-vk"></i><span>Vkontakte</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['pinterest_link'])) && (trim($mr_tailor_theme_options['pinterest_link']) != "" ) ) { ?><li class="site-social-icons-pinterest"><a target="_blank" href="<?php echo $mr_tailor_theme_options['pinterest_link']; ?>"><i class="fa fa-pinterest"></i><span>Pinterest</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['linkedin_link'])) && (trim($mr_tailor_theme_options['linkedin_link']) != "" ) ) { ?><li class="site-social-icons-linkedin"><a target="_blank" href="<?php echo $mr_tailor_theme_options['linkedin_link']; ?>"><i class="fa fa-linkedin"></i><span>LinkedIn</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['googleplus_link'])) && (trim($mr_tailor_theme_options['googleplus_link']) != "" ) ) { ?><li class="site-social-icons-googleplus"><a target="_blank" href="<?php echo $mr_tailor_theme_options['googleplus_link']; ?>"><i class="fa fa-google-plus"></i><span>Google+</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['rss_link'])) && (trim($mr_tailor_theme_options['rss_link']) != "" ) ) { ?><li class="site-social-icons-rss"><a target="_blank" href="<?php echo $mr_tailor_theme_options['rss_link']; ?>"><i class="fa fa-rss"></i><span>RSS</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['tumblr_link'])) && (trim($mr_tailor_theme_options['tumblr_link']) != "" ) ) { ?><li class="site-social-icons-tumblr"><a target="_blank" href="<?php echo $mr_tailor_theme_options['tumblr_link']; ?>"><i class="fa fa-tumblr"></i><span>Tumblr</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['instagram_link'])) && (trim($mr_tailor_theme_options['instagram_link']) != "" ) ) { ?><li class="site-social-icons-instagram"><a target="_blank" href="<?php echo $mr_tailor_theme_options['instagram_link']; ?>"><i class="fa fa-instagram"></i><span>Instagram</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['youtube_link'])) && (trim($mr_tailor_theme_options['youtube_link']) != "" ) ) { ?><li class="site-social-icons-youtube"><a target="_blank" href="<?php echo $mr_tailor_theme_options['youtube_link']; ?>"><i class="fa fa-youtube-play"></i><span>Youtube</span></a></li><?php } ?>
                        <?php if ( (isset($mr_tailor_theme_options['vimeo_link'])) && (trim($mr_tailor_theme_options['vimeo_link']) != "" ) ) { ?><li class="site-social-icons-vimeo"><a target="_blank" href="<?php echo $mr_tailor_theme_options['vimeo_link']; ?>"><i class="fa fa-vimeo-square"></i><span>Vimeo</span></a></li><?php } ?>
                    </ul>
                </div>
	        </div>

            <div class="site-tools">

	            <ul>

		            <li id="mobile-menu" class="mobile-menu-button"><a href="javascript:void(0)"><span class="mobile-menu-text"><?php _e( 'MENU', 'mr_tailor' )?></span><i class="fa fa-bars"></i></a></li>

                    <?php if (class_exists('YITH_WCWL')) : ?>
                    <?php if ( (isset($mr_tailor_theme_options['main_header_wishlist'])) && (trim($mr_tailor_theme_options['main_header_wishlist']) == "1" ) ) : ?>
                    <li class="wishlist-button">
                        <a href="<?php echo esc_url($yith_wcwl->get_wishlist_url()); ?>">
                            <?php if ( (isset($mr_tailor_theme_options['main_header_wishlist_icon']['url'])) && ($mr_tailor_theme_options['main_header_wishlist_icon']['url'] != "") ) : ?>
                            <img src="<?php echo esc_url($mr_tailor_theme_options['main_header_wishlist_icon']['url']); ?>">
                            <?php else : ?>
                            <i class="getbowtied-icon-heart"></i>
                            <?php endif; ?>
                            <span class="wishlist_items_number"><?php echo yith_wcwl_count_products(); ?></span>
                        </a>
                    </li>
					<?php endif; ?>
                    <?php endif; ?>

		            <?php if (class_exists('WooCommerce')) : ?>
		            <?php if ( (isset($mr_tailor_theme_options['main_header_shopping_bag'])) && (trim($mr_tailor_theme_options['main_header_shopping_bag']) == "1" ) ) : ?>
		            <?php if ( (isset($mr_tailor_theme_options['catalog_mode'])) && ($mr_tailor_theme_options['catalog_mode'] == 1) ) : ?>
		            <?php else : ?>
		            <li class="shopping-bag-button">
		                <a href="cart">
		                    <?php if ( (isset($mr_tailor_theme_options['main_header_shopping_bag_icon']['url'])) && ($mr_tailor_theme_options['main_header_shopping_bag_icon']['url'] != "") ) : ?>
		                    <img src="<?php echo esc_url($mr_tailor_theme_options['main_header_shopping_bag_icon']['url']); ?>">
		                    <?php else : ?>
		                    <i class="getbowtied-icon-shop"></i>
		                    <?php endif; ?>
		                    <span class="shopping_bag_items_number"><?php echo $woocommerce->cart->cart_contents_count; ?></span>
		                </a>
		            </li>
					<?php endif; ?>
		            <?php endif; ?>
		            <?php endif; ?>

	            </ul>

	        </div>

        </div><!-- .large-5 .columns -->

    </div><!-- .row -->

</div><!-- #site-top-bar -->
