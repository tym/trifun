<?php
/**
 * @class       AW_Field_Workflow
 * @package     AutomateWoo/Fields
 */

class AW_Field_Workflow extends AW_Field_Select {

	protected $default_title = 'Workflow';

	protected $default_name = 'workflow';

	protected $type = 'select';

	/** @var array  */
	public $query_args = [];


	/**
	 *
	 */
	function __construct() {
		$this->title = __('Workflow', 'automatewoo');
		$this->set_placeholder('- Select -');
	}


	/**
	 * @return array
	 */
	function get_options() {

		$args = array_merge([
			'post_type' => AW()->post_type,
			'post_status' => 'any',
			'posts_per_page' => -1
		], $this->query_args );

		$workflows = new WP_Query($args);

		$options = [];

		if ( $workflows->have_posts() ) {
			foreach( $workflows->posts as $workflow ) {
				$options[$workflow->ID] = $workflow->post_title;
			}
		}

		$this->set_options( $options );

		return $this->options;
	}


	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	function add_query_arg( $key, $value )
	{
		$this->query_args[$key] = $value;
		return $this;
	}

}
