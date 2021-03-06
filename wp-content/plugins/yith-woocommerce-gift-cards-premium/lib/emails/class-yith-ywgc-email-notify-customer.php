<?php
if ( ! defined ( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists ( "WC_Email" ) ) {
    require_once ( WC ()->plugin_path () . '/includes/emails/class-wc-email.php' );
}

if ( ! class_exists ( "YITH_YWGC_Email_Notify_Customer" ) ) {
    /**
     * Create and send a digital gift card to the specific recipient
     *
     * @since 0.1
     * @extends \WC_Email
     */
    class YITH_YWGC_Email_Notify_Customer extends WC_Email {
        /**
         * An introductional message from the shop owner
         */
        public $introductory_text;

        /**
         * Set email defaults
         *
         * @since 0.1
         */
        public function __construct () {
            // set ID, this simply needs to be a unique name
            $this->id = 'ywgc-email-notify-customer';

            // this is the title in WooCommerce Email settings
            $this->title = __ ( "YITH Gift Cards - Notification", 'yith-woocommerce-gift-cards' );

            // this is the description in WooCommerce email settings
            $this->description = __ ( 'Customer notification - the coupon has been used by its owner', 'yith-woocommerce-gift-cards' );

            // these are the default heading and subject lines that can be overridden using the settings
            $this->heading = __ ( 'I have appreciated your gift', 'yith-woocommerce-gift-cards' );
            $this->subject = __ ( '[{site_title}] Your gift card has been used', 'yith-woocommerce-gift-cards' );

            // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
            $this->template_html  = 'emails/notify-customer.php';
            $this->template_plain = 'emails/plain/notify-customer.php';

            $this->introductory_text = __ ( 'The gift card you have sent to <code>{recipient_email}</code> has been used in our shop.', 'yith-woocommerce-gift-cards' );

            // Trigger on specific action call
            add_action ( 'ywgc-email-notify-customer_notification', array ( $this, 'trigger' ) );

            parent::__construct ();
            $this->email_type = "html";
        }

        /**
         * Send the digital gift card to the recipient
         *
         * @param YWGC_Gift_Card_Premium|YITH_YWGC_Gift_Card $gift_card the gift card to be sent
         *
         * @return bool|void
         */
        public function trigger ( $gift_card ) {

            if ( is_numeric ( $gift_card ) ) {
                $args = array( 'ID' => $gift_card );

                $gift_card = new YWGC_Gift_Card_Premium( $args );
            }

            if ( ! ( $gift_card instanceof YWGC_Gift_Card_Premium ) ) {
                return false;
            }

            if ( ! $gift_card->exists () ) {
                return false;
            }

            $this->object    = $gift_card;
            $this->recipient = $gift_card->recipient;

            $this->introductory_text = $this->get_option ( 'introductory_text', __ ( 'The gift card you have sent to <code>{recipient_email}</code> has been used in our shop.', 'yith-woocommerce-gift-cards' ) );

            $this->introductory_text = str_replace (
                array ( "{sender}", "{recipient_email}" ),
                array ( $this->object->sender, $this->object->recipient ),
                $this->introductory_text
            );

            $result = $this->send ( $this->get_recipient (),
                $this->get_subject (),
                $this->get_content (),
                $this->get_headers (),
                $this->get_attachments () );

            if ( $result ) {
                //  Set the gift card as sent
                $gift_card->set_as_sent ();
            }

            return $result;
        }

        /**
         * get_content_html function.
         *
         * @since 0.1
         * @return string
         */
        public function get_content_html () {
            ob_start ();
            wc_get_template ( $this->template_html, array (
                'gift_card'         => $this->object,
                'introductory_text' => $this->introductory_text,
                'email_heading'     => $this->get_heading (),
                'email_type'        => $this->email_type,
                'sent_to_admin'     => false,
                'plain_text'        => false,
                'email'             => $this,
            ),
                '',
                YITH_YWGC_TEMPLATES_DIR );

            return ob_get_clean ();
        }


        /**
         * Initialize Settings Form Fields
         *
         * @since 0.1
         */
        public function init_form_fields () {
            $this->form_fields = array (
                'enabled'           => array (
                    'title'   => __ ( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __ ( 'Enable this email notification', 'woocommerce' ),
                    'default' => 'yes',
                ),
                'subject'           => array (
                    'title'       => __ ( 'Subject', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf ( __ ( 'Defaults to <code>%s</code>', 'woocommerce' ), $this->subject ),
                    'placeholder' => '',
                    'default'     => '',
                ),
                'heading'           => array (
                    'title'       => __ ( 'Email Heading', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf ( __ ( 'Defaults to <code>%s</code>', 'woocommerce' ), $this->heading ),
                    'placeholder' => '',
                    'default'     => '',
                ),
                'introductory_text' => array (
                    'title'       => __ ( 'Introductive message', 'yith-woocommerce-gift-cards' ),
                    'type'        => 'textarea',
                    'description' => sprintf ( __ ( 'Defaults to <code>%s</code>', 'woocommerce' ), $this->introductory_text ),
                    'placeholder' => '',
                    'default'     => '',
                ),
            );
        }
    } // end \YITH_YWGC_Email_Notify_Customer class
}

return new YITH_YWGC_Email_Notify_Customer();