<?php
/**
 * @class 		AW_Admin_Edit_Workflow
 * @package		AutomateWoo/Admin
 * @since		2.6.1
 */

class AW_Admin_Workflow_Edit {

	/** @var AW_Model_Workflow */
	public $workflow;


	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'admin_head', [ $this, 'setup_workflow' ] );
		add_action( 'admin_head', [ $this, 'register_meta_boxes'] );
		add_action( 'admin_head', [ $this, 'enqueue_scripts' ], 15 );
		add_action( 'admin_footer', [ $this, 'workflow_js_templates' ], 15 );
		add_action( 'save_post', [ $this, 'save' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox_misc_actions' ] );

		add_filter( 'wp_insert_post_data', [ $this, 'insert_post_data' ] );
	}


	/**
	 * Setup workflow object
	 */
	function setup_workflow() {
		global $post;

		if ( $post && $post->post_status !== 'auto-draft' ) {
			$this->workflow = AW()->get_workflow( $post );
		}
	}


	/**
	 * Enqueue scripts
	 * Do this on the admin_head action so we have access to the post object
	 */
	function enqueue_scripts() {

		wp_dequeue_script( 'autosave' );

		wp_localize_script( 'automatewoo-workflows', 'automatewooWorkflowLocalizeScript', $this->get_js_data() );

		wp_enqueue_script( 'automatewoo-workflows' );
		wp_enqueue_script( 'automatewoo-variables' );
		wp_enqueue_script( 'automatewoo-rules' );

		wp_enqueue_media();

		// dummy editor for ajax cloning
		?><div style="display: none"><?php wp_editor( '', 'automatewoo_editor' ); ?></div><?php
	}


	/**
	 * @return array
	 */
	function get_js_data() {

		global $post;

		AW()->rules()->get_rules(); // load all the rules into memory so the order is preserved

		// get rule options
		if ( $this->workflow ) {

			$rule_options = $this->workflow->get_rule_options();

			foreach ( $rule_options as &$rule_group ) {
				foreach ( $rule_group as &$rule ) {
					$rule_object = AW()->rules()->get_rule( $rule['name'] );

					if ( $rule_object->type === 'object' ) {
						$rule['selected'] = $rule_object->get_object_display_value( $rule['value'] );
					}

					if ( $rule_object->type === 'select' ) {
						$rule_object->get_select_choices(); // load options in to object cache
					}
				}
			}
		}
		else {
			$rule_options = [];
		}


		// Pass action data map
		$actions_data = [];

		foreach ( AW()->get_actions() as $action ) {
			$actions_data[ $action->get_name() ] = [
				'can_be_previewed' => $action->can_be_previewed,
				'required_data_items' => $action->required_data_items
			];
		}

		return [
			'id' => $post->ID,
			'isNew' => $post->post_status == 'auto-draft',
			'trigger' => $this->workflow ? $this->workflow->get_trigger() : false,
			'ruleOptions' => $rule_options,
			'allRules' => AW()->rules()->get_rules(),
			'actions' => $actions_data
		];
	}



	/**
	 * Workflow meta boxes
	 */
	function register_meta_boxes() {

		AW()->admin->add_meta_box( 'trigger_box',
			__('Trigger','automatewoo'), [ $this, 'meta_box_triggers' ],
			'aw_workflow', 'normal', 'high'
		);

		AW()->admin->add_meta_box( 'rules_box',
			__( 'Rules <small>(optional)</small>','automatewoo' ), [ $this, 'meta_box_rules' ],
			'aw_workflow', 'normal', 'high'
		);

		AW()->admin->add_meta_box( 'actions_box',
			__('Actions','automatewoo'), [ $this, 'meta_box_actions' ],
			'aw_workflow', 'normal', 'high'
		);

		AW()->admin->add_meta_box( 'options_box',
			__('Options','automatewoo'), [ $this, 'meta_box_options' ],
			'aw_workflow', 'side'
		);

		AW()->admin->add_meta_box( 'variables_box',
			__('Variables','automatewoo'), [ $this, 'meta_box_variables' ],
			'aw_workflow', 'side'
		);
	}


	/**
	 * Triggers meta box
	 */
	function meta_box_triggers() {
		AW()->admin->get_view('meta-box-trigger', [
			'workflow' => $this->workflow,
			'selected_trigger' => $this->workflow ? $this->workflow->get_trigger() : false
		]);
	}


	/**
	 * Rules meta box
	 */
	function meta_box_rules() {
		AW()->admin->get_view('meta-box-rules', [
			'workflow' => $this->workflow,
			'selected_trigger' => $this->workflow ? $this->workflow->get_trigger() : false
		]);
	}


	/**
	 * Actions meta box
	 */
	function meta_box_actions() {

		$action_select_box_values = [];

		foreach ( AW()->get_actions() as $registered_action ) {
			$action_select_box_values[$registered_action->group][$registered_action->get_name()] = $registered_action->get_title();
		}

		AW()->admin->get_view('meta-box-actions', [
			'workflow' => $this->workflow,
			'actions' => $this->workflow ? $this->workflow->get_actions() : false,
			'action_select_box_values' => $action_select_box_values
		]);
	}


	/**
	 * Variables meta box
	 */
	function meta_box_variables() {
		AW()->admin->get_view('meta-box-variables');
	}


	/**
	 * Options meta box
	 */
	function meta_box_options() {
		AW()->admin->get_view('meta-box-options', [
			'workflow' => $this->workflow
		]);
	}


	/**
	 *
	 */
	function workflow_js_templates() {
		AW()->admin->get_view( 'js-workflow-templates' );
	}


	/**
	 * @param $post_id
	 */
	function save( $post_id ) {

		$data = aw_request( 'aw_workflow_data' );

		if ( ! is_array( $data ) )
			return;

		// Some validation for the trigger
		if ( isset( $data['trigger_name'] ) ) {

			$trigger_name = $data['trigger_name'];

			if ( $trigger = AW()->get_registered_trigger( $trigger_name ) ) {

				// If queueing is disabled for the trigger force when to run option
				if ( ! $trigger->allow_queueing ) {
					$data['workflow_options']['when_to_run'] = 'immediately';
				}
			}
		}

		// empty rules if there are none
		if ( ! isset( $data['rule_options'] ) ) {
			$data['rule_options'] = [];
		}

		// Save the data into meta
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}


	/**
	 * @param $data
	 */
	function insert_post_data( $data ) {

		if ( $status = aw_clean( aw_request('workflow_status') ) ) {
			$data['post_status'] = $status === 'active' ? 'publish' : 'aw-disabled';
		}

		return $data;
	}



	/**
	 *
	 */
	function post_submitbox_misc_actions() {
		?>
		<script type="text/javascript">
			(function($) {

				// remove options
				$('#minor-publishing-actions, .misc-pub-visibility, .misc-pub-post-status').remove();

				// remove edit links
				$('#misc-publishing-actions a').remove();

				// remove editables (fixes status text changing on submit)
				$('#misc-publishing-actions .hide-if-js').remove();

			})(jQuery);
		</script>
		<?php
	}

}

new AW_Admin_Workflow_Edit();