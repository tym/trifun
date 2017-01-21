<?php
/**
 * @class 		AW_Mailer
 * @since		2.2
 * @package		AutomateWoo
 */

class AW_Mailer {

	/** @var string */
	public $email;

	/** @var WP_User|bool */
	public $user;

	/** @var string */
	public $template;

	/** @var string */
	public $heading;

	/** @var string */
	public $content;

	/** @var string */
	public $subject;

	/** @var array */
	public $attachments = [];

	/** @var AW_Model_Workflow|bool : only set when email is being sent from a workflow  */
	public $workflow = false;

	/** @var string */
	public $email_type = 'html';


	/**
	 * @param $subject
	 * @param $email
	 * @param $content
	 * @param string $template
	 */
	function __construct( $subject, $email, $content, $template = 'default' ) {

		$this->email = $email;
		$this->subject = $subject;
		$this->content = $content;
		$this->template = $template;

		$this->user = get_user_by( 'email', $email );

		// include css inliner
		if ( ! class_exists( 'AW_Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
			include_once AW()->lib_path( '/emogrifier/emogrifier.php' );
		}

		// also include the WC packaged emogrifier incase other plugins are looking for this e.g. YITH email customizer
		if ( ! class_exists( 'Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
			include_once( WC()->plugin_path() . '/includes/libraries/class-emogrifier.php' );
		}
	}


	/**
	 * @param $heading
	 */
	function set_heading( $heading ) {
		$this->heading = $heading;
	}


	/**
	 * @param $workflow
	 */
	function set_workflow( $workflow ) {
		$this->workflow = $workflow;
	}


	/**
	 * @return string
	 */
	function get_from_email() {
		return AW()->email->get_from_address( $this->template );
	}


	/**
	 * @return string
	 */
	function get_from_name() {
		return AW()->email->get_from_name( $this->template );
	}


	/**
	 * Generate HTML, inline CSS and send email
	 *
	 * @return true|WP_Error
	 */
	function send() {

		if ( ! $this->email ) {
			return new WP_Error( 2, __( 'The to email field was blank.', 'automatewoo' ) );
		}

		if ( ! is_email( $this->email ) ) {
			return new WP_Error( 1, sprintf(__( "'%s' is not a valid email.", 'automatewoo' ), $this->email ) );
		}

		// TODO maybe move this logic
		if ( $this->workflow && $this->workflow->is_unsubscribed( $this->email ) ) {
			return new WP_Error( 2, sprintf( __( '%s is unsubscribed from this workflow.', 'automatewoo' ), $this->email ) );
		}

		do_action( 'automatewoo/email/before_send', $this );

		add_filter( 'wp_mail_from', [ $this, 'get_from_email' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
		add_action( 'wp_mail_failed', [ $this, 'log_wp_mail_errors' ] );

		$sent = wp_mail(
			$this->email,
			$this->subject,
			$this->get_html(),
			"Content-Type: " . $this->get_content_type() . "\r\n",
			$this->attachments
		);

		remove_filter( 'wp_mail_from', [ $this, 'get_from_email' ] );
		remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		remove_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
		remove_action( 'wp_mail_failed', [ $this, 'log_wp_mail_errors' ] );

		if ( $sent === false ) {

			global $phpmailer;

			if ( $phpmailer && is_array( $phpmailer->ErrorInfo ) && ! empty( $phpmailer->ErrorInfo ) ) {

				$error = current( $phpmailer->ErrorInfo );
				return new WP_Error( 4, sprintf( __( 'PHP Mailer - %s', 'automatewoo' ), $error->message ) );
			}

			return new WP_Error( 5, __( 'The wp_mail() function returned false.', 'automatewoo' ) );
		}

		return $sent;
	}


	/**
	 * @return string
	 */
	function get_html() {
		return apply_filters( 'woocommerce_mail_content', $this->style_inline( $this->get_raw_html() ) );
	}


	/**
	 * Returns html without CSS inline
	 *
	 * @return string
	 */
	function get_raw_html() {

		add_filter( 'woocommerce_email_footer_text', [ $this, 'footer_text' ] );

		AW_Mailer_API::$mailer = $this;
		$this->prepare_content();

		// Buffer
		ob_start();

		$this->get_template_part( 'email-header.php', [
			'email_heading' => $this->heading
		] );

		echo $this->content;

		$this->get_template_part( 'email-footer.php' );

		$html = ob_get_clean();

		remove_filter( 'woocommerce_email_footer_text', [ $this, 'footer_text' ] );

		AW_Mailer_API::$mailer = false;

		return $html;
	}



	/**
	 *
	 */
	function prepare_content() {
		// Remove instances of links with a double 'http://'
		// @todo convert to preg replace
		$this->content = str_replace( '"http://http://', '"http://', $this->content );
		$this->content = str_replace( '"https://https://', '"https://', $this->content );
		$this->content = str_replace( '"http://https://', '"https://', $this->content );
		$this->content = str_replace( '"https://http://', '"http://', $this->content );

		if ( $this->workflow && $this->workflow->is_tracking_enabled() ) {
			$this->content = $this->replace_urls_with_tracking_urls( $this->content );
			$this->content = $this->append_tracking_pixel_to_content( $this->content );
		}

		// pass through content filters to convert short codes etc
		// IMPORTANT do this after URLs are modified so entities are not encoded
		$this->content = apply_filters( 'automatewoo_email_content', $this->content );
	}



	/**
	 * Apply inline styles to dynamic content.
	 *
	 * @param string|null $content
	 * @return string
	 */
	function style_inline( $content ) {
		if ( ! class_exists( 'DOMDocument' ) ) return $content;

		ob_start();
		aw_get_template( 'email/styles.php' );
		$this->get_template_part( 'email-styles.php' );
		$css = apply_filters( 'automatewoo/mailer/styles', ob_get_clean(), $this );

		try {
			$emogrifier = new AW_Emogrifier( $content, $css );
			$emogrifier->disableStyleBlocksParsing();
			$content = $emogrifier->emogrify();
		}
		catch ( Exception $e ) {
			$logger = new WC_Logger();
			$logger->add( 'emogrifier', $e->getMessage() );
		}

		return $content;
	}


	/**
	 * @param $text
	 * @return string
	 */
	function footer_text( $text ) {

		if ( empty( $this->email ) )
			return $text;

		$unsubscribe_url = $this->get_unsubscribe_url();

		if ( $unsubscribe_url ) {
			$unsubscribe_text = apply_filters( 'automatewoo_email_unsubscribe_text', __( 'Unsubscribe', 'automatewoo' ) );

			// add separator if there is footer text
			if ( trim( $text ) ) {
				$text .= apply_filters( 'automatewoo_email_footer_separator',  ' - ' );
			}

			$text .= '<a href="' . $unsubscribe_url . '">' . $unsubscribe_text . '</a>';
		}

		return $text;
	}



	/**
	 * Use an email here rather than an id for security
	 *
	 * @return bool|string
	 */
	function get_unsubscribe_url() {

		if ( ! $this->workflow )
			return false;

		return AW()->email->generate_unsubscribe_url( $this->workflow->id, $this->email, false );
	}



	/**
	 * @param $file
	 * @param array $args
	 *
	 * @return bool
	 */
	function get_template_part( $file, $args = [] ) {

		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		switch( $this->template ) {
			case 'default':
				$template_name = 'emails/' . $file;
				$template_path = '';
				break;

			case 'plain':
				return false;
				break;

			default:
				$template_name = $file;
				$template_path = 'automatewoo/custom-email-templates/'. $this->template;
				break;
		}

		$located = wc_locate_template( $template_name, $template_path );

		// if using woo default, apply filters to support email customizer plugins
		if ( $this->template === 'default' ) {
			$located = apply_filters( 'wc_get_template', $located, $template_name, $args, $template_path, '' );

			do_action( 'woocommerce_before_template_part', $template_name, $template_path, $located, $args );

			include( $located );

			do_action( 'woocommerce_after_template_part', $template_name, $template_path, $located, $args );
		}
		else {
			include( $located );
		}

	}



	/**
	 * @param $content string
	 *
	 * @return string
	 */
	function replace_urls_with_tracking_urls( $content ) {
		$replacer = new AW_Replace_Helper( $content, [ $this, '_replace_urls_preg_callback' ], 'href_urls' );
		return $replacer->process();
	}


	/**
	 * @param $content
	 *
	 * @return string
	 */
	function append_tracking_pixel_to_content( $content ) {
		$tracking_pixel = '<img src="' . esc_url( AW()->email->generate_open_track_url( $this->workflow->log->id ) ) . '" height="1" width="1" alt="" style="display:inline">';
		return $content . $tracking_pixel;
	}


	/**
	 * @param $url
	 *
	 * @return string
	 */
	function _replace_urls_preg_callback( $url ) {

		$url = html_entity_decode( $url );
		$url = $this->workflow->append_ga_tracking_to_url( $url );

		if ( $this->workflow->log && $this->workflow->log->id ) {
			$url = AW()->email->generate_click_track_url( $this->workflow->log->id, $url );
		}

		return 'href="' . esc_url( $url ) . '"';
	}


	/**
	 * get_type function.
	 *
	 * @return string
	 */
	function get_email_type() {
		return $this->email_type && class_exists( 'DOMDocument' ) ? $this->email_type : 'plain';
	}


	/**
	 * Get email content type.
	 *
	 * @return string
	 */
	function get_content_type() {
		switch ( $this->get_email_type() ) {
			case 'html' :
				return 'text/html';
			case 'multipart' :
				return 'multipart/alternative';
			default :
				return 'text/plain';
		}
	}


	/**
	 * @param $error WP_Error
	 */
	function log_wp_mail_errors( $error ) {
		$log = new WC_Logger();
		$log->add( 'automatewoo-wp-mail', $error->get_error_message() );
	}

}
