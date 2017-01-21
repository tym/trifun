<?php
/**
	Plugin Name: Easy User Tags
	Description: Adds a tag taxonomy to users.
	Version: 1.2
	Author: Daniel Bitzer
	Author URI: http://danielbitzer.com
	License: GPLv3
	License URI: http://www.gnu.org/licenses/gpl-3.0
	Text Domain: easy-user-tags
 */


if ( ! class_exists('Easy_User_Tags') ):


class Easy_User_Tags
{
	private static $taxonomies	= array();
	
	/**
	 * Register all the hooks and filters we can in advance
	 * Some will need to be registered later on, as they require knowledge of the taxonomy name
	 */
	function __construct()
	{
		add_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ), 10, 3);
		add_filter( 'init', array( $this, 'init' ) );
		add_filter( 'admin_init', array( $this, 'admin_init') );

		// Menus
		add_action('admin_menu',				array($this, 'admin_menu'));
		add_filter('parent_file',				array($this, 'parent_menu'));

		// User Profiles
		add_action('show_user_profile',			array($this, 'user_profile'));
		add_action('edit_user_profile',			array($this, 'user_profile'));
		add_action('personal_options_update',	array($this, 'save_profile'));
		add_action('edit_user_profile_update',	array($this, 'save_profile'));
		add_filter( 'sanitize_user', array( $this, 'restrict_username' ) );

		// List table
		add_filter( 'manage_users_columns' , array($this, 'inject_column_header') );
		add_filter( 'manage_users_custom_column' , array($this, 'inject_column_row'), 10, 3 );
		add_filter( 'pre_user_query', array( $this, 'filter_admin_query' ) );
		add_filter( 'views_users', array( $this, 'filter_user_views' ), 1, 1 );
		add_action( 'restrict_manage_users', array( $this, 'inject_bulk_actions' ), 1, 1 );
		add_action( 'admin_init', array( $this, 'catch_bulk_edit_action'));
	}


	/**
	 * Init
	 */
	function init()
	{
		$this->register_taxonomy();
	}


	/**
	 * Admin init
	 */
	function admin_init()
	{
		// catch export endpoint
		if ( isset( $_REQUEST['eut_export_csv'] ) )
		{
			include_once('includes/export-csv.php');
			$exporter = new EUT_CSV_Export();
			$exporter->set_user_tag( absint( $_REQUEST['user_tag'] ) );
			$exporter->generate_csv();
		}
	}



	/**
	 * This is our way into manipulating registered taxonomies
	 * It's fired at the end of the register_taxonomy function
	 * 
	 * @param String $taxonomy	- The name of the taxonomy being registered
	 * @param String $object	- The object type the taxonomy is for; We only care if this is "user"
	 * @param Array $args		- The user supplied + default arguments for registering the taxonomy
	 */
	function registered_taxonomy($taxonomy, $object, $args)
	{
		global $wp_taxonomies;
		
		// Only modify user taxonomies, everything else can stay as is
		if ($object != 'user') return;
		
		// We're given an array, but expected to work with an object later on
		$args	= (object) $args;
		
		// Register any hooks/filters that rely on knowing the taxonomy now
		add_filter("manage_edit-{$taxonomy}_columns",	array($this, 'set_user_column'));
		add_action("manage_{$taxonomy}_custom_column",	array($this, 'set_user_column_values'), 10, 3);
		
		// Set the callback to update the count if not already set
		if(empty($args->update_count_callback)) {
			$args->update_count_callback	= array($this, 'update_count');
		}

		// We're finished, make sure we save out changes
		$wp_taxonomies[$taxonomy]		= $args;
		self::$taxonomies[$taxonomy]	= $args;
	}


	/**
	 * Create the user tags taxonomy
	 */
	function register_taxonomy()
	{
		register_taxonomy( 'user_tag', 'user', array(
			'public' => false,
			'show_ui' => true,
			'labels' => array(
				'name'                       => 'Tags',
				'singular_name'              => 'Tag',
				'menu_name'                  => 'Tags',
				'search_items'               => 'Search Tags',
				'popular_items'              => 'Popular Tags',
				'all_items'                  => 'All Tags',
				'edit_item'                  => 'Edit Tag',
				'update_item'                => 'Update Tag',
				'add_new_item'               => 'Add New Tag',
				'new_item_name'              => 'New Tag Name',
				'separate_items_with_commas' => 'Separate Tags with commas',
				'add_or_remove_items'        => 'Add or remove Tags',
				'choose_from_most_used'      => 'Choose from the most popular tags',
			),
			'rewrite' => false,
			'capabilities' => array(
				'manage_terms' => 'edit_users',
				'edit_terms'   => 'edit_users',
				'delete_terms' => 'edit_users',
				'assign_terms' => 'read',
			),
		) );
	}


	/**
	 * We need to manually update the number of users for a taxonomy term
	 * 
	 * @see	_update_post_term_count()
	 * @param Array $terms		- List of Term taxonomy IDs
	 * @param Object $taxonomy	- Current taxonomy object of terms
	 */
	function update_count( $terms, $taxonomy )
	{
		global $wpdb;
		
		foreach((array) $terms as $term) {
			$count	= $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term));
			
			do_action('edit_term_taxonomy', $term, $taxonomy);
			$wpdb->update($wpdb->term_taxonomy, compact('count'), array('term_taxonomy_id'=>$term));
			do_action('edited_term_taxonomy', $term, $taxonomy);
		}
	}


	/**
	 * Add each of the taxonomies to the Users menu
	 * They will behave in the same was as post taxonomies under the Posts menu item
	 * Taxonomies will appear in alphabetical order
	 */
	function admin_menu()
	{
		// Put the taxonomies in alphabetical order
		$taxonomies	= self::$taxonomies;
		ksort($taxonomies);
		
		foreach($taxonomies as $key=>$taxonomy) {
			add_users_page(
				$taxonomy->labels->menu_name, 
				$taxonomy->labels->menu_name, 
				$taxonomy->cap->manage_terms, 
				"edit-tags.php?taxonomy={$key}"
			);
		}
	}
	
	/**
	 * Fix a bug with highlighting the parent menu item
	 * By default, when on the edit taxonomy page for a user taxonomy, the Posts tab is highlighted
	 * This will correct that bug
	 */
	function parent_menu( $parent = '' )
	{
		global $pagenow;
		
		// If we're editing one of the user taxonomies
		// We must be within the users menu, so highlight that
		if(!empty($_GET['taxonomy']) && $pagenow == 'edit-tags.php' && isset(self::$taxonomies[$_GET['taxonomy']])) {
			$parent	= 'users.php';
		}
		
		return $parent;
	}

	/**
	 * Correct the column names for user taxonomies
	 * Need to replace "Posts" with "Users"
	 *
	 * @param array $columns
	 * @return array
	 */
	function set_user_column( $columns )
	{
		unset($columns['posts']);
		$columns['users'] = __('Users');

		if ( current_user_can( 'edit_users' ) )
		{
			$columns['export'] = __('Export', 'easy-user-tags');
		}

		return $columns;
	}
	
	/**
	 * Set values for custom columns in user taxonomies
	 */
	function set_user_column_values( $display, $column, $term_id )
	{
		if( 'users' === $column && isset( $_GET['taxonomy'] ) )
		{
			$term	= get_term( $term_id, $_GET['taxonomy'] );
			echo '<a href="' . admin_url( 'users.php?user_tag=' . $term->slug ) . '">' . $term->count . '</a>';
		}
		elseif ( 'export' === $column )
		{
			$url = wp_nonce_url(add_query_arg(array(
				'eut_export_csv' => '1',
				'user_tag' => $term_id
			)), 'eut_export_csv' );

			echo '<a href="' . $url . '" class="button">Export To CSV</a>';
		}
		else
		{
			echo '-';
		}
	}
	
	/**
	 * Add the taxonomies to the user view/edit screen
	 * 
	 * @param WP_User $user
	 */
	function user_profile( $user ) {

		// Using output buffering as we need to make sure we have something before outputting the header
		// But we can't rely on the number of taxonomies, as capabilities may vary
		ob_start();
		
		foreach( self::$taxonomies as $taxonomy => $taxonomy_args ):

			// Check the current user can assign terms for this taxonomy
			if( ! current_user_can( $taxonomy_args->cap->assign_terms ) )
				continue;
			
			// Get all the terms in this taxonomy
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

			?>
			<table class="form-table">
				<tr>
					<th><label for=""><?php printf(__("Select %s", 'easy-user-tags'), $taxonomy_args->labels->name ) ?></label></th>
					<td>
						<?php if( ! empty( $terms ) ): ?>
							<?php foreach( $terms as $term ): ?>
								<input type="checkbox" name="<?php echo $taxonomy?>[]" id="<?php echo "{$taxonomy}-{$term->slug}"?>" value="<?php echo $term->slug?>" <?php checked(true, is_object_in_term($user->ID, $taxonomy, $term))?> />
								<label for="<?php echo "{$taxonomy}-{$term->slug}"?>"><?php echo $term->name?></label><br />
							<?php endforeach; ?>
						<?php else: ?>
							<?php printf(__("There are no %s available.", 'easy-user-tags'), $taxonomy_args->labels->name ) ?>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php
		endforeach;
		
		// Output the above if we have anything, with a heading
		$output	= ob_get_clean();
		if( ! empty( $output ) ) {
			echo '<h3>', __('Taxonomies'), '</h3>';
			echo $output;
		}
	}
	
	/**
	 * Save the custom user taxonomies when saving a users profile
	 * 
	 * @param Integer $user_id	- The ID of the user to update
	 */
	function save_profile( $user_id ) {

		foreach( self::$taxonomies as $key => $taxonomy ) {

			// Check the current user can edit this user and assign terms for this taxonomy
			if ( ! current_user_can('edit_user', $user_id) && current_user_can($taxonomy->cap->assign_terms) )
				continue;

			$terms = [];

			// Save the data
			if ( isset( $_POST[$key] ) ) {
				$terms = array_map( 'sanitize_key', $_POST[$key] );
			}

			wp_set_object_terms( $user_id, $terms, $key, false );
			clean_object_term_cache( $user_id, $key );
		}
	}
	
	/**
	 * Usernames can't match any of our user taxonomies
	 * As otherwise it will cause a URL conflict
	 * This method prevents that happening
	 */
	function restrict_username($username) {

		if(isset(self::$taxonomies[$username])) return '';
		
		return $username;
	}


	/**
	 * @param $columns
	 * @return array
	 */
	function inject_column_header( $columns )
	{
		$pos = 5;
		$part = array_slice( $columns, 0, $pos );
		$part2 = array_slice( $columns, $pos );
		return array_merge( $part, array('user_tag' => __('Tags')), $part2 );
	}


	/**
	 * @param $content
	 * @param $column
	 * @param $user_id
	 *
	 * @return string
	 */
	function inject_column_row( $content, $column, $user_id )
	{
		if ( $column !== 'user_tag' ) return $content;

		if ( ! $tags = wp_get_object_terms( $user_id, $column ) )
		{
			return '<span class="na">&ndash;</span>';
		}
		else
		{
			$termlist = array();
			foreach ( $tags as $tag )
			{
				$termlist[] = '<a href="' . admin_url( 'users.php?user_tag=' . $tag->slug ) . ' ">' . $tag->name . '</a>';
			}

			return implode( ', ', $termlist );
		}
	}



	/**
	 * Filter the products in admin based on options
	 *
	 * @param mixed $query
	 */
	function filter_admin_query( $query )
	{
		global $wpdb, $pagenow;

		if ( is_admin() && $pagenow == 'users.php' && ! empty( $_GET['user_tag'] ) )
		{
			$tag_slug = $_GET['user_tag'];
			$query->query_from .= " INNER JOIN {$wpdb->term_relationships} ON {$wpdb->users}.ID = {$wpdb->term_relationships}.object_id INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id INNER JOIN {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id";
			$query->query_where .= " AND {$wpdb->terms}.slug = '{$tag_slug}'";
		}
	}



	/**
	 * @param array $views
	 * @return array
	 */
	function filter_user_views( $views ) {
		if ( ! empty( $_GET['user_tag'] ) ) {
			$views['all'] = str_replace( 'current', '', $views['all'] );
		}
		return $views;
	}


	/**
	 * @param $which
	 */
	function inject_bulk_actions( $which ) {

		if ( $which !== 'top' )
			return;

		if ( current_user_can( 'edit_users' ) ) : ?>

			<label class="screen-reader-text" for="add_user_tag"><?php _e( 'Add tag&hellip;', 'easy-user-tags' ) ?></label>
			<select name="add_user_tag" id="add_user_tag">
				<option value=""><?php _e( 'Add tag&hellip;', 'easy-user-tags' ) ?></option>
				<?php wp_dropdown_user_tags(); ?>
			</select>

			<label class="screen-reader-text" for="remove_user_tag"><?php _e( 'Remove tag&hellip;', 'easy-user-tags' ) ?></label>
			<select name="remove_user_tag" id="remove_user_tag">
				<option value=""><?php _e( 'Remove tag&hellip;', 'easy-user-tags' ) ?></option>
				<?php wp_dropdown_user_tags(); ?>
			</select>

		<?php endif;
	}


	/**
	 *
	 */
	function catch_bulk_edit_action() {

		global $pagenow;

		if ( $pagenow != 'users.php' || empty($_GET['changeit'] )  || empty( $_GET['users'] ) || ! current_user_can( 'edit_users' ) ) return;

		$users = array_map( 'absint', $_GET['users'] );

		if ( ! empty( $_GET['add_user_tag'] ) )
		{
			foreach ( $users as $user_id )
			{
				wp_add_object_terms( $user_id, absint( $_GET['add_user_tag'] ), 'user_tag' );
			}
		}

		if ( ! empty( $_GET['remove_user_tag'] ) )
		{
			foreach ( $users as $user_id )
			{
				wp_remove_object_terms( $user_id, absint( $_GET['remove_user_tag'] ), 'user_tag' );
			}
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>'. __( 'Tags updated.', 'easy-user-tags' ) .'</p></div>';
	}

}



/**
 * Print out option html elements for role selectors.
 *
 * @since 2.1.0
 *
 * @param string $selected Slug for the role that should be already selected.
 */
function wp_dropdown_user_tags( $selected = '' )
{
	$p = '';
	$r = '';

	$tags = get_terms('user_tag', array(
		'hide_empty' => false
	));

	foreach ( $tags as $tag ) {
		if ( $selected == $tag->term_id || $selected == $tag->slug )
			$p = "\n\t<option selected='selected' value='" . esc_attr($tag->term_id) . "'>$tag->name</option>";
		else
			$r .= "\n\t<option value='" . esc_attr($tag->term_id) . "'>$tag->name</option>";
	}
	echo $p . $r;
}




new Easy_User_Tags();


endif;
