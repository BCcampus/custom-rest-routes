<?php
/**
 * Plugin Name:     Custom REST Routes
 * Description:     Activate routes for custom post types that were registered by another plugin or theme. Visit the settings menu and click on the "REST Routes" menu item.
 * Author:          BCcampus
 * Text Domain:     custom-rest-routes
 * Domain Path:     /languages
 * Version:         2.0.1
 * Licence:         MIT
 * Tags: pressbooks, rest api, custom, post types, routes, endpoints
 *
 * @package         custom-rest-routes
 */
namespace BCcampus\Rest;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Routes
 *
 * Detects registered custom post types on either a single or multisite
 * Gives a user the ability to add custom endpoints to the existing WP REST API
 *
 */
class Routes {

	/**
	 * @var string
	 */
	private $admin_menu = 'network_admin_menu';

	/**
	 * @var string
	 */
	private $get_option = 'get_site_option';

	/**
	 * @var string
	 */
	private $file_name = 'settings.php';

	/**
	 * @var string
	 */
	private $add_option = 'add_site_option';

	/**
	 * @var string
	 */
	private $update_option = 'update_site_option';

	/**
	 * @var string
	 */
	private $slug = 'rest_routes';

	/**
	 * Routes constructor checks for multisite, adds appropriate hooks
	 */
	function __construct() {
		$this->isMultisite();
		add_action( $this->admin_menu, [ $this, 'settingsPage' ] );
		add_action( 'admin_init', [ $this, 'restOptions' ] );
		add_action( 'init', [ $this, 'customPostTypeRestSupport' ] );
	}

	/**
	 * Sets class variables, depending on the environment
	 */
	function isMultisite() {
		if ( is_multisite() ) {
			$this->admin_menu    = 'network_admin_menu';
			$this->get_option    = 'get_site_option';
			$this->file_name     = 'settings.php';
			$this->add_option    = 'add_site_option';
			$this->update_option = 'update_site_option';
		} else {
			$this->admin_menu    = 'admin_menu';
			$this->get_option    = 'get_option';
			$this->file_name     = 'options-general.php';
			$this->add_option    = 'add_option';
			$this->update_option = 'update_option';
		}
	}

	/**
	 * Adds REST support for existing custom content types
	 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types/#adding-rest-api-support-to-existing-content-types
	 *
	 */
	function customPostTypeRestSupport() {
		global $wp_post_types;
		$option = call_user_func( $this->get_option, $this->slug );

		if ( ! empty( $option ) && is_array( $option ) ) {

			// Set to support rest endpoint
			foreach ( $option as $key => $val ) {
				if ( 1 === $val && isset( $wp_post_types[ $key ] ) ) {
					$wp_post_types[ $key ]->show_in_rest = true;
				}
			}
		}
	}

	/**
	 * Add the submenu item and page
	 */
	function settingsPage() {
		$slug     = $this->slug;
		$callback = [ $this, 'settingsPageContent' ];
		add_submenu_page( $this->file_name, 'REST Routes', 'REST Routes', 'manage_options', $slug, $callback );
	}

	/**
	 * Add content to the settings page
	 */
	function settingsPageContent() {

		echo '<div class="wrap">';
		echo '<h2>REST Routes Settings</h2>';
		echo '<form class="welcome-panel" method="post" action="">';
		settings_fields( $this->slug );
		do_settings_sections( $this->slug );
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Sections set up, hooks into admin_init
	 *
	 */
	function restOptions() {
		$_page    = $_option = $this->slug;
		$_section = $this->slug . '_section';

		$defaults = [];

		if ( false == call_user_func( $this->get_option, $this->slug ) ) {
			call_user_func( $this->add_option, $this->slug, $defaults );
		}

		/****************************************
		 * Add Routes Section
		 *****************************************/
		add_settings_section(
			'available_' . $_section,
			__( 'Available Custom Post Types', 'custom-rest-routes' ),
			[ $this, 'renderSectionCallback' ],
			$_page
		);

		add_settings_field(
			'add_rest_routes',
			__( 'Add Routes', 'custom-rest-routes' ),
			[ $this, 'renderFieldCallback' ],
			$_page,
			'available_' . $_section
		);

		register_setting(
			$_page,
			$_option
		);

		/****************************************
		 * Test Routes Section
		 * only fired on single installs
		 *****************************************/
		if ( ! is_multisite() ) {
			add_settings_section(
				'test_' . $_section,
				__( 'Test Routes', 'custom-rest-routes' ),
				[ $this, 'renderSectionCallback' ],
				$_page
			);
		}

		$this->updateSettings();
	}

	/**
	 * Updates options on a specific post event
	 */
	function updateSettings() {
		if ( isset( $_POST['option_page'] ) && $this->slug === $_POST['option_page'] ) {
			call_user_func( $this->update_option, $this->slug, self::sanitize( $_POST[ $this->slug ] ) );
		}
	}

	/**
	 * Content of each section is based on the ID
	 * Catch all for different sections on the same page
	 *
	 * @param $args
	 */
	function renderSectionCallback( $args ) {
		$_section = $this->slug . '_section';

		switch ( $args['id'] ) {
			// Get public custom post types on this website and exclude the built in ones.
			case 'available_' . $_section:
				echo '<p>Custom post types on this website are listed here for your convenience:</p>';

				break;
			case 'test_' . $_section:
				echo '<p>After adding routes for existing custom post types, click on the one you want to test below:</p>';
				// get the value of the setting we've registered with register_setting()
				$options = get_site_option( $this->slug );
				if ( ! empty( $options ) ) {

					// display each one in an ordered list
					echo '<ol>';
					foreach ( $options as $endpoint => $val ) {
						// only grab the ones that have been checked
						if ( 1 === $val ) {
							//@TODO different function required for multisite?
							$url = home_url( '/wp-json/wp/v2/' . $endpoint );
							echo '<li><a href=' . esc_url( $url ) . ' target="_blank">' . $endpoint . '</a></li>';
						}
					}
					echo '</ol>';
				} // If no routes were found, display a friendly message
				else {
					echo '<p class="notice notice-warning"><i><small>Sorry, you have not added any routes yet.</i></small></p>';
				}
				break;

		}
	}


	/**
	 * Outputs appropriate html input and fills it with the old value
	 *
	 */
	function renderFieldCallback() {
		$options    = call_user_func( $this->get_option, $this->slug );
		$args       = [
			'public'   => true,
			'_builtin' => false,
		];
		$output     = 'names';
		$operator   = 'and';
		$post_types = get_post_types( $args, $output, $operator );

		// If there is registered custom post types, let's display them in an ordered list
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				echo "<p><input name='{$this->slug}[{$post_type}]' id='{$post_type}' type='checkbox' value='1' " . checked( 1, $options[ $post_type ], false ) . '/>';
				echo "<label for='{$post_type}'>{$post_type}</label></p>";
			}
		} // If none were found, display a friendly message
		else {
			echo '<p class="notice notice-warning"><i><small>Sorry, no custom post types were found registered on this website.</i></small></p>';
		}

	}

	/**
	 * Modifies input from string to integer value
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function sanitize( $input ) {
		// ensure intval
		foreach ( $input as $key => $val ) {
			$options[ $key ] = intval( $val );
		}

		return $options;
	}

}

new Routes();

