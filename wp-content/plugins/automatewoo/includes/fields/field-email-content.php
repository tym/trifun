<?php
/**
 * @class       AW_Field_Email_Content
 * @package     AutomateWoo/Fields
 */

class AW_Field_Email_Content extends AW_Field {

	protected $default_title = 'Email Content';

	protected $default_name = 'email_content';

	protected $type = 'email-content';


	function __construct() {
		$this->set_description(__( 'The contents of this field will be wrapped in the WooCommerce mailer.', 'automatewoo' ));
	}


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {
		$id = uniqid();

		wp_editor( $value, $id, [
			'textarea_name' => $this->get_full_name(),
			'tinymce' => true, // default to visual
			'quicktags' => true,
		]);

		if ( is_ajax() ) {
			$this->ajax_init( $id );
		}
	}



	/**
	 * @param $id
	 */
	function ajax_init( $id ) {
		?>
		<script type="text/javascript">
			(function(){
				AutomateWoo.Workflows.init_ajax_wysiwyg('<?php echo $id; ?>');
			}());
		</script>
		<?php
	}


}