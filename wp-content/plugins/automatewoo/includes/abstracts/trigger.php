<?php
/**
 * @class       AW_Trigger
 * @package     AutomateWoo/Abstracts
 */

abstract class AW_Trigger {

	/** @var string */
	public $title;

	/** @var string */
	public $name;

	/** @var string */
	public $description;

	/** @var string */
	public $group = 'Other';

	/** @var array */
	public $supplied_data_items = [];

	/** @var bool */
	public $allow_queueing = true;

	/** @var array */
	public $fields = [];

	/** @var array */
	public $workflows = [];

	/** @var bool */
	public $workflows_loaded;

	/** @var array */
	public $options;

	/** @var array */
	protected $rules;

	/** @var bool */
	protected $_fields_loaded = false;


	abstract function register_hooks();

	abstract function load_fields();

	abstract function validate_workflow( $workflow );



	/**
	 * Construct
	 */
	function __construct() {
		add_action( 'automatewoo_init_triggers', [ $this, 'init' ] );
	}



	/**
	 * Construct
	 */
	function init() {
		$this->register_hooks();

		// Register the class
		AW()->registered_triggers[$this->name] = $this;
	}



	/**
	 * @param $option object
	 */
	function add_field( $option ) {
		$option->set_name_base( 'aw_workflow_data[trigger_options]' );
		$this->fields[ $option->get_name() ] = $option;
	}


	/**
	 * @param $option_name
	 */
	function remove_field( $option_name ) {
		if ( isset( $this->fields[ $option_name ] ) )
			unset( $this->fields[ $option_name ] );
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
	 * @return array
	 */
	function get_fields() {

		if ( ! $this->_fields_loaded ) {
			$this->load_fields();
			$this->_fields_loaded = true;
		}

		return $this->fields;
	}


	/**
	 * @return bool
	 */
	function has_workflows() {
		$workflows = get_posts([
			'post_type' => 'aw_workflow',
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => 'trigger_name',
					'value' => $this->name
				]
			]
		]);

		return ! empty( $workflows );
	}


	/**
	 * @return AW_Model_Workflow[]
	 */
	function get_workflows() {

		if ( ! $this->workflows_loaded ) {
			$query = new AW_Query_Workflows();
			$query->set_trigger( $this );
			$this->workflows = $query->get_results();
		}

		return $this->workflows;
	}


	/**
	 * Every data item registered with the trigger should be supplied to this method in its object form.
	 * E.g. a 'user' should be passed as a WP_User object, and an 'order' should be passed as a WC_Order object
	 *
	 * @param array $data_items
	 */
	function maybe_run( $data_items = [] ) {

		// Get all workflows that are registered to use this trigger
		$workflows = $this->get_workflows();

		if ( ! $workflows )
			return;

		// Check if each workflow should be run based on its options
		foreach ( $workflows as $workflow ) {
			/** @var $workflow AW_Model_Workflow */
			$workflow->maybe_run( $data_items );
		}
	}


	/**
	 * @return string
	 */
	function get_name() {
		return $this->name;
	}


	/**
	 * @return string
	 */
	function get_title() {
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
	 * @param $options array
	 * @deprecated
	 */
	function set_options( $options ) {
		$this->options = $options;
	}


	/**
	 * Will return all data if $field is false
	 *
	 * @param string $field
	 * @return mixed
	 *
	 * @deprecated use $workflow->get_trigger_option()
	 */
	function get_option( $field ) {

		if ( ! $field ) return false;

		$value = false;

		if ( isset( $this->options[$field] ) ) {
			$value = $this->options[$field];
		}

		return apply_filters( 'automatewoo_trigger_option', $value, $field, $this );
	}



	/**
	 * Returns an array of conditions that are available for use with this trigger
	 *
	 * @return array
	 */
	function get_available_rules() {

		if ( ! isset( $this->rules ) ) {
			$rules = AW()->rules()->get_rules();

			foreach ( $rules as $rule ) {
				$valid = array_intersect( $this->supplied_data_items, $rule->required_data_items );
				if ( $valid ) $this->rules[] = $rule;
			}
		}

		return $this->rules;
	}


	/**
	 * This method is called just before a queued workflow runs
	 *
	 * @param AW_Model_Workflow $workflow
	 *
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		return true;
	}



	/**
	 * Checks if this trigger's language matches that of the user or guest
	 *
	 * @param AW_Model_Workflow $workflow
	 *
	 * @return bool
	 */
	function validate_workflow_language($workflow ) {

		if ( ! AW()->integrations()->is_wpml() ) return true;

		$language = $workflow->get_language();

		if ( ! $language ) return true; // workflow has no set language

		if ( $user = $workflow->get_data_item('user') ) {
			return AW()->language_helper->get_user_language( $user ) == $language;
		}
		elseif ( $guest = $workflow->get_data_item('guest') ) {
			return AW()->language_helper->get_guest_language( $guest ) == $language;
		}
		else {
			return true;
		}
	}


	/**
	 * @deprecated - use rules
	 */
	protected function add_user_limit_field() {
		$limit = ( new AW_Field_Number_Input() )
			->set_name('once_only')
			->set_title( __( 'Limit Per User', 'automatewoo' ))
			->set_description( __( 'Limit how many times this workflow will ever run for each user.', 'automatewoo'  ) )
			->set_placeholder( __( 'Leave blank for no limit', 'automatewoo'  ) );

		$this->add_field( $limit );
	}


	/**
	 * @deprecated - use rules
	 */
	protected function add_guest_limit_field() {
		$limit = ( new AW_Field_Number_Input() )
			->set_name('limit_per_guest')
			->set_title( __( 'Limit Per Guest', 'automatewoo' ))
			->set_description( __( 'Limit how many times this workflow will ever run for each guest email.', 'automatewoo'  ) )
			->set_placeholder( __( 'Leave blank for no limit', 'automatewoo'  ) );

		$this->add_field($limit);
	}



	/**
	 * @deprecated - use rules instead
	 */
	protected function add_user_tags_field() {

		$user_has_tags = new AW_Field_User_Tags();
		$user_has_tags->set_title( __('User Has Tags', 'automatewoo' ) );

		if ( $this->group == 'Order') {
			$user_has_tags->set_description(__( 'Only trigger when the user has all of the selected tags. Note this will always fail for guest orders because guests can have no tags.', 'automatewoo'  ) );
		}
		else {
			$user_has_tags->set_description(__('Only trigger when the user has all of the selected tags.', 'automatewoo'  ) );
		}


		$user_missing_tags = new AW_Field_User_Tags();
		$user_missing_tags->set_name('user_missing_tags');
		$user_missing_tags->set_title( __("User Missing Tags", 'automatewoo' ) );

		if ( $this->group == 'Order') {
			$user_missing_tags->set_description(__( "Only trigger when the user is missing all of the selected tags. Note this will always fail for guest orders because guests can have no tags.", 'automatewoo'  ) );
		}
		else {
			$user_missing_tags->set_description(__("Only trigger when the user is missing all of the selected tags.", 'automatewoo'  ) );
		}

		$this->add_field($user_has_tags);
		$this->add_field($user_missing_tags);
	}



	protected function add_field_validate_queued_order_status() {

		$field = new AW_Field_Checkbox();
		$field->set_name('validate_order_status_before_queued_run');
		$field->set_title( __('Recheck Status Before Run', 'automatewoo' ) );
		$field->default_to_checked = true;
		$field->set_description(
			__( "This is useful for Workflows that are not run immediately as it ensures the status of the order hasn't changed since initial trigger." ,
				'automatewoo'  ) );

		$this->add_field( $field );
	}


	/**
	 *
	 */
	protected function add_field_user_pause_period() {

		$field = ( new AW_Field_Number_Input() )
			->set_name( 'user_pause_period' )
			->set_title( __( 'User Pause Period (days)', 'automatewoo' ) )
			->set_description( __( 'Can be used to ensure that this trigger will only send once in a set period to a user or guest.', 'automatewoo' ) );
		$this->add_field( $field );
	}


	/**
	 * @param $object_name
	 */
	protected function add_field_recheck_status( $object_name ) {

		$field = ( new AW_Field_Checkbox() )
			->set_name( 'recheck_status_before_queued_run' )
			->set_title( __( 'Recheck Status Before Run', 'automatewoo') )
			->set_default_to_checked()
			->set_description( sprintf( __(
				"This is useful for workflows that are not run immediately as it ensures the status of the %s hasn't "
				. "changed since initial trigger." , 'automatewoo'  ), $object_name ) );

		$this->add_field( $field );
	}


	/**
	 * Checks if a user has all of the tags
	 *
	 * @deprecated - use rules instead
	 *
	 * @param $user WP_User
	 * @param $trigger AW_Trigger
	 *
	 * @return bool
	 */
	protected function validate_user_tag_fields( $user, $trigger ) {

		$has_tags = $trigger->get_option('user_tags');
		$missing_tags = $trigger->get_option('user_missing_tags');

		if ( ! $user ) return false;

		if ( is_array($has_tags) || is_array($missing_tags) ) {
			// always fail for guest orders
			if ( ! $user->ID === 0 ) return false;
		}


		if ( is_array($has_tags) ) {
			foreach( $has_tags as $tag ) {
				if ( ! is_object_in_term( $user->ID, 'user_tag', $tag ) )
					return false;
			}
		}

		if ( is_array($missing_tags) ) {
			foreach( $missing_tags as $tag ) {
				if ( is_object_in_term( $user->ID, 'user_tag', $tag ) )
					return false;
			}
		}


		return true;
	}



	/**
	 * Order status field must be named 'order_status'
	 *
	 * @param $trigger AW_Trigger
	 * @param $order WC_Order
	 *
	 * @return bool
	 * @since 2.0
	 */
	protected function validate_order_status_field( $trigger, $order ) {

		$status = $trigger->get_option('order_status');

		if ( ! $status ) return true;

		$status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;

		// wrong status
		if ( $order->get_status() !== $status )
			return false;

		return true;
	}


	/**
	 * @param $trigger AW_Trigger
	 * @param $user WP_User
	 *
	 * @deprecated - use rules instead
	 * @return bool
	 *
	 * @since 2.0
	 */
	protected function validate_user_type_field( $trigger, $user ) {

		if ( ! $valid_user_types = $trigger->get_option('user_type') )
			return true; // no user type requirement

		$valid_user_types = (array) $valid_user_types;

		if ( ! $user ) return false; // user missing

		if ( $user->ID === 0 ) { // user is a order guest
			if ( ! in_array( 'guest', $valid_user_types ) )
				return false;
		}
		else {
			if ( sizeof( array_intersect( $valid_user_types, $user->roles ) ) == 0 )
				return false;
		}

		return true;
	}


	/**
	 * Note: Has support for orders placed by guests
	 *
	 * @param $workflow AW_Model_Workflow
	 * @param $trigger AW_Trigger
	 * @param $user WP_User
	 *
	 * @return bool
	 *
	 * @deprecated - use rules
	 */
	protected function validate_limit_per_user( $workflow, $trigger, $user ) {

		$limit = $workflow->get_trigger_option('once_only');

		if ( ! $limit  ) return true;

		$times_run = $workflow->get_times_run_for_user( $user );

		if ( $limit <= $times_run )
			return false;

		return true;
	}



	/**
	 * @param $workflow AW_Model_Workflow
	 * @param $trigger AW_Trigger
	 * @param $guest AW_Model_Guest
	 *
	 * @return bool
	 *
	 * @deprecated - use rules
	 */
	protected function validate_limit_per_guest( $workflow, $trigger, $guest ) {

		$limit = $workflow->get_trigger_option('limit_per_guest');

		if ( ! $limit  ) return true;

		$times_run = $workflow->get_times_run_for_guest( $guest );

		if ( $limit <= $times_run )
			return false;

		return true;
	}


	/**
	 * Return false if field exists and is not the first order. Otherwise returns true
	 *
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 *
	 * @deprecated - use rule instead
	 */
	protected function validate_is_first_order_field( $workflow ) {

		$is_users_first_order = $workflow->get_trigger_option('is_users_first_order');

		// option not checked
		if ( ! $is_users_first_order ) return true;

		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order ) return true; // bail

		$query_args = [
			'posts_per_page' => 1,
			'post_type' => 'shop_order',
			'post_status' => [ 'wc-processing', 'wc-completed', 'wc-pending' ],
			'fields' => 'ids',
			'post__not_in' => [ $order->id ],
		];

		if ( $user->ID === 0 ) // order placed by guest
		{
			$query_args['meta_query'] = [
				[
					'key' => '_billing_email',
					'value' => $user->user_email,
				]
			];
		}
		else
		{
			$query_args['meta_query'] = [
				'relation' => 'OR',
				[
					'key' => '_customer_user',
					'value' => $user->ID,
				],
				[
					'key' => '_billing_email',
					'value' => $user->user_email,
				]
			];
		}

		$orders = get_posts( $query_args );

		return empty( $orders );
	}


	/**
	 * Also works for guests
	 *
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	protected function validate_field_user_pause_period( $workflow ) {

		$period = $workflow->get_trigger_option('user_pause_period');
		$user = $workflow->get_data_item('user');
		$guest = $workflow->get_data_item('guest');

		if ( empty( $period ) ) return true; // no pause period set

		if ( ! $user && ! $guest ) return true; // must have a user or guest

		$hours = $period * 24;

		$period_date = new DateTime( current_time('mysql', true ) ); // GMT
		$period_date->modify("-$hours hours");

		// Check to see if this workflow has run since the period date
		$log_query = ( new AW_Query_Logs() )
			->where( 'workflow_id', $workflow->id )
			->set_limit(1)
			->where('date', $period_date->format('Y-m-d H:i:s'), '>');

		if ( $user ) {
			if ( $user->ID === 0 ) { // guest user
				$log_query->where( 'guest_email', $user->user_email );
			}
			else {
				$log_query->where( 'user_id', $user->ID );
			}
		}
		elseif( $guest ) {
			$log_query->where( 'guest_email', $guest->email );
		}

		if ( $log_query->get_results() )
			return false;

		return true;
	}


	/**
	 * @param $allowed_statuses array|string
	 * @param $current_status string
	 *
	 * @return bool
	 */
	protected function validate_status_field( $allowed_statuses, $current_status ) {
		// allow all if left blank
		if ( empty( $allowed_statuses ) ) return true;

		if ( is_array( $allowed_statuses ) ) {
			// multi status match
			$with_prefix_match = in_array( 'wc-' . $current_status, $allowed_statuses );
			$no_prefix_match = in_array( $current_status, $allowed_statuses );

			// at least one has to match
			if ( ! $with_prefix_match && ! $no_prefix_match )
				return false;
		}
		else {
			// single status match, remove prefix
			$allowed_statuses = 'wc-' === substr( $allowed_statuses, 0, 3 ) ? substr( $allowed_statuses, 3 ) : $allowed_statuses;

			if ( $allowed_statuses != $current_status )
				return false;
		}

		return true;
	}

}
