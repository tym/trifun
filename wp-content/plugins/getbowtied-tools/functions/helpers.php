<?php 

	if (!class_exists("GetbowtiedToolsHelper")):
	class GetbowtiedToolsHelper
	{
		private static $getbowtied_themes = array("Merchandiser", "Shopkeeper", "The Retailer", "Mr. Tailor");

		public function __construct(){}

		public static function theme_name()
		{
			$theme = wp_get_theme();
			if ($theme->parent()):
				return $theme->parent()->get('Name');
			else:
				return  $theme->get('Name');
			endif;

			return $theme_name;
		}

		public static function theme_slug()
		{
			$theme = wp_get_theme();
			$theme_slug = $theme->template;
			return $theme_slug;
		}

		public static function is_getbowtied_theme()
		{
			if (in_array(self::theme_name(), self::$getbowtied_themes))
				return true;

			return false;
		}
	}
	endif;


	if (!defined("THEME_NAME")): define( 'THEME_NAME', GetbowtiedToolsHelper::theme_name() ); endif;
	if (!defined("THEME_SLUG")): define( 'THEME_SLUG', GetbowtiedToolsHelper::theme_slug() ); endif;
	define( 'GETBOWTIED_TOOLS_URL', trailingslashit( plugins_url() ) . trailingslashit( 'getbowtied-tools' ));