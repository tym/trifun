<?php
	function trifi_meta_is_member_name($meta) {
		return strpos($meta['meta_key'], 'Member Name -') !== false;
	}

	function trifi_format_member_name_meta($meta) {
		return wp_kses_post( make_clickable( rawurldecode( $meta['meta_value'] ) ) );
	}

	function trifi_build_is_used_input_for_member_name($post_id, $meta) {
		$member_meta = trifi_get_or_create_meta_is_used_for_member($post_id, $meta);
		$member_meta_id = $member_meta['meta_id'];
		$member_meta_key = $member_meta['meta_key'];
		$checked_property = ($member_meta_key == 'yes') ? 'checked="checked"' : '';

		return '<input type="checkbox" name="' . $member_meta_id . '" value="yes"' . $checked_property . ' />';
	}

	function trifi_stub($post_id, $meta) {
		return array(
			'meta_id' => '_trifi_is_used[Member Name - 1]',
			'meta_key' => 'yes'
		);
	}

	function trifi_get_or_create_meta_is_used_for_member($post_id, $meta) {
		$is_used_meta_key = '_trifi_is_used';
		$member_name_key = $meta['meta_key'];
		$meta_key = $is_used_meta_key . ':' . $member_name_key;
		$member_is_used_value = get_post_meta($post_id, $meta_key, true);

		if (empty($member_is_used_value)) {
			update_post_meta(
				$post_id,
				$meta_key,
				'no'
			);

			return array(
				'meta_id' => '_trifi_is_used[' . $member_name_key . ']',
				'meta_key' => 'no'
			);
		} else {
			return array(
				'meta_id' => '_trifi_is_used[' . $member_name_key . ']',
				'meta_key' => $member_is_used_value
			);
		}
	}
?>
<div class="view">
	<?php
		global $wpdb;

		if ( $metadata = $order->has_meta( $item_id ) ) {
			echo '<table cellspacing="0" class="display_meta">';
			foreach ( $metadata as $meta ) {

				// Skip hidden core fields
				if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
					'_qty',
					'_tax_class',
					'_product_id',
					'_variation_id',
					'_line_subtotal',
					'_line_subtotal_tax',
					'_line_total',
					'_line_tax',
					'method_id',
					'cost'
				) ) ) ) {
					continue;
				}

				// Skip serialised meta
				if ( is_serialized( $meta['meta_value'] ) ) {
					continue;
				}

				// Get attribute data
				if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
					$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
					$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
					$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
				} else {
					$meta['meta_key']   = wc_attribute_label( $meta['meta_key'], $_product );
				}

				if (!trifi_meta_is_member_name($meta)) {
					echo '<tr><th>' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ':</th><td>' . wp_kses_post( wpautop( make_clickable( rawurldecode( $meta['meta_value'] ) ) ) ) . '</td></tr>';
				}
			}
			echo '</table>';

			echo '<table cellspacing="0" class="trifi-is-used-table">';
				echo '<thead>';
					echo '<tr>';
						echo '<th>Name</th>';
						echo '<th>Is Used</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
					foreach ( $metadata as $meta ) {
						// Skip hidden core fields
						if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(

							'_qty',
							'_tax_class',
							'_product_id',
							'_variation_id',
							'_line_subtotal',
							'_line_subtotal_tax',
							'_line_total',
							'_line_tax',
							'method_id',
							'cost'
						) ) ) ) {
							continue;
						}

						// Skip serialised meta
						if ( is_serialized( $meta['meta_value'] ) ) {
							continue;
						}

						// Get attribute data
						if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
							$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
						} else {
							$meta['meta_key']   = wc_attribute_label( $meta['meta_key'], $_product );
						}

						if (trifi_meta_is_member_name($meta)) {
							echo '<tr>';
								echo '<td>' . trifi_format_member_name_meta($meta) . '</td>';
								echo '<td>' . trifi_build_is_used_input_for_member_name($order->id, $meta) . '</td>';
							echo '</tr>';
						}
					}
				echo '</tbody>';
			echo '</table>';
		}
		
	?>
</div>
<?php /*
<div class="edit" style="display: none;">
	<table class="meta" cellspacing="0">
		<tbody class="meta_items">
		<?php
			if ( $metadata = $order->has_meta( $item_id )) {
				foreach ( $metadata as $meta ) {
					// Skip hidden core fields
					if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
						'_qty',
						'_tax_class',
						'_product_id',
						'_variation_id',
						'_line_subtotal',
						'_line_subtotal_tax',
						'_line_total',
						'_line_tax',
						'method_id',
						'cost'
					) ) ) ) {
						continue;
					}

					// Skip serialised meta
					if ( is_serialized( $meta['meta_value'] ) ) {
						continue;
					}

					$meta['meta_key']   = rawurldecode( $meta['meta_key'] );
					$meta['meta_value'] = esc_textarea( rawurldecode( $meta['meta_value'] ) ); // using a <textarea />
					$meta['meta_id']    = absint( $meta['meta_id'] );

					echo '<tr data-meta_id="' . esc_attr( $meta['meta_id'] ) . '">
						<td>
							<input type="text" name="meta_key[' . $meta['meta_id'] . ']" value="' . esc_attr( $meta['meta_key'] ) . '" />
							<textarea name="meta_value[' . $meta['meta_id'] . ']">' . $meta['meta_value'] . '</textarea>
						</td>
						<td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
					</tr>';
				}
			}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4"><button class="add_order_item_meta button"><?php _e( 'Add&nbsp;meta', 'woocommerce' ); ?></button></td>
			</tr>
		</tfoot>
	</table>
</div>
*/ ?>
