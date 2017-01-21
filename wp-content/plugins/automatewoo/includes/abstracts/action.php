<?php
/**
 * @class       AW_Action
 * @package     AutomateWoo/Abstracts
 */

abstract class AW_Action {

	/** @var string */
	public $title;

	/** @var string */
	public $name;

	/** @var string */
	public $description;

	/** @var string */
	public $group = 'Other';

	/** @var array */
	public $fields = [];

	/** @var array */
	public $options;

	/** @var AW_Model_Workflow */
	public $workflow;

	/** @var array */
	public $required_data_items = [];

	/** @var bool */
	public $can_be_previewed = false;

	/** @var bool */
	protected $_fields_loaded = false;


	/**
	 * Fields only need to be loaded when editing a workflow
	 *
	 * @return mixed
	 */
	abstract function load_fields();

	/**
	 * @return void
	 */
	abstract function run();


	/**
	 * Construct
	 */
	function __construct() {
		add_action( 'automatewoo_init_actions', [ $this, 'init' ] );
	}


	/**
	 * Construct
	 */
	function init() {
		// Register the class
		AW()->registered_actions[$this->name] = $this;
	}


	/**
	 * @param $option object
	 */
	function add_field( $option ) {
		$option->set_name_base( 'aw_workflow_data[actions]' );
		$this->fields[ $option->get_name() ] = $option;
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	function get_field( $name ) {
		if ( ! $this->_fields_loaded ) {
			$this->load_fields();
			$this->_fields_loaded = true;
		}

		if ( ! isset( $this->fields[$name] ) )
			return false;

		return $this->fields[$name];
	}


	/**
	 * @return mixed
	 */
	function get_fields() {
		if ( ! $this->_fields_loaded ) {
			$this->load_fields();
			$this->_fields_loaded = true;
		}

		return $this->fields;
	}


	/**
	 * @return string
	 */
	function get_name() {
		return $this->name;
	}


	/**
	 * @param bool $prepend_group
	 * @return string
	 */
	function get_title( $prepend_group = false ) {
		if ( $prepend_group && $this->group != 'Other' ) {
			return $this->group . ' - ' . $this->title;
		}

		return $this->title;
	}


	/**
	 * @return string|null
	 */
	function get_description() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	function get_description_html() {

		if ( ! $this->get_description() )
			return '';

		return '<p class="aw-field-description">' . $this->get_description() .'</p>';
	}


	/**
	 * @param $options
	 */
	function set_options( $options ) {
		$this->options = $options;
	}


	/**
	 * Will return all data if no $field is false
	 *
	 * @param bool $field
	 * @param bool $replace_vars
	 * @param bool $allow_html
	 *
	 * @return mixed
	 */
	function get_option( $field, $replace_vars = false, $allow_html = false ) {

		$field = wp_check_invalid_utf8( $field );
		$value = false;

		if ( isset( $this->options[$field] ) ) {
			if ( $replace_vars ) {
				$value = $this->workflow->variable_processor()->process_field( $this->options[$field], $allow_html );
			}
			else {
				$value = $this->options[$field];
			}
		}

		return apply_filters( 'automatewoo_action_option', $value, $field, $replace_vars, $this );
	}


	/**
	 *
	 */
	function check_requirements() {}


	function warning( $message ) {
		if ( ! is_admin() ) return;
?>
		<script type="text/javascript">
			alert('ERROR: <?php echo $message ?>');
		</script>
<?php
	}


}