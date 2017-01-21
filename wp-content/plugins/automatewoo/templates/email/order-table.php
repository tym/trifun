<?php
/**
 * Order table. Can only be used with the order.items variable
 * Override this template by copying it to yourtheme/automatewoo/email/order-table.php
 *
 * @var WC_Order $order
 * @var AW_Model_Workflow $workflow
 * @var string $variable_name
 * @var string $data_type
 * @var string $data_field
 */

wc_get_template( 'emails/email-order-details.php', [ 'order' => $order, 'sent_to_admin' => false, 'plain_text' => false, 'email' => '' ] );
