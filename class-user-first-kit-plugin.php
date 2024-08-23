<?php
/**
 * Plugin Name: User First Kit
 * Plugin URI: https://wordpress.org/plugins/user-first-kit
 * Description: This plugin helps you set permalink structure and remove default plugins, themes, posts, and pages.
 * Author: Huzaifa Al Mesbah
 * Author URI: https://profiles.wordpress.org/huzaifaalmesbah/
 * Text Domain: user-first-kit
 * License: GPL v3
 * Requires at least: 5.6
 * Tested up to: 6.6.1
 * Requires PHP: 7.0
 * Version: 1.0.2
 *
 * @package User_Frist_Kit
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

/**
 * Plugin main class.
 */
class User_First_Kit_Plugin {

	/**
	 * Contruct Functions.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
	}
	/**
	 * Text domain load.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'user-first-kit', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Add settings link to plugin page.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=ufk-settings">' . esc_html__( 'Settings', 'user-first-kit' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Sub menu register.
	 */
	public function add_menu_page() {
		add_submenu_page(
			'tools.php', // Parent menu slug (Tools).
			esc_html__( 'User First Kit', 'user-first-kit' ),
			esc_html__( 'User First Kit', 'user-first-kit' ),
			'manage_options',
			'ufk-settings',
			array( $this, 'settings_page' )
		);
	}
	/**
	 * Plugin Action.
	 */
	public function settings_page() {
		$current_permalink_structure = get_option( 'permalink_structure' );
		$plugins_removed             = $this->are_default_plugins_removed();
		$themes_removed              = $this->are_default_themes_removed();
		$posts_pages_removed         = $this->are_default_posts_pages_removed();
		?>
<div class="wrap">
	<h2><?php echo esc_html__( 'User First Kit Settings', 'user-first-kit' ); ?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'ufk_action', 'ufk_nonce' ); ?>
		<h3><?php echo esc_html__( 'Permalink Settings', 'user-first-kit' ); ?></h3>
		<label>
			<input type="checkbox" name="set_permalink" value="yes" />
			<?php echo esc_html__( 'Set Permalink Structure to Post Name', 'user-first-kit' ); ?>
			<?php if ( '/%postname%/' === $current_permalink_structure ) : ?>
			<span style="color: green; margin-left: 5px;">&#10004;</span>
			<?php endif; ?>
		</label>

		<h3><?php echo esc_html__( 'Remove Default Plugins', 'user-first-kit' ); ?></h3>
		<label> <input type="checkbox" name="remove_default_plugins" value="yes" />
			<?php echo esc_html__( 'Remove Default Plugins (Hello Dolly and Akismet)', 'user-first-kit' ); ?>
			<?php if ( $plugins_removed ) : ?>
			<span style="color: green; margin-left: 5px;">&#10004;</span>
			<?php endif; ?>
		</label>
		<h3><?php echo esc_html__( 'Remove Default Themes', 'user-first-kit' ); ?></h3>
		<label>
			<input type="checkbox" name="remove_default_themes" value="yes" />
			<?php echo esc_html__( 'Remove Default Themes (Twenty Twenty-One, Twenty Twenty-Two, Twenty Twenty Three)', 'user-first-kit' ); ?>
			<?php if ( $themes_removed ) : ?>
			<span style="color: green; margin-left: 5px;">&#10004;</span>
			<?php endif; ?>
		</label>

		<h3><?php echo esc_html__( 'Remove Default Posts and Pages', 'user-first-kit' ); ?></h3>
		<label>
			<input type="checkbox" name="remove_default_posts_pages" value="yes" />
			<?php echo esc_html__( 'Remove Default "Hello World" Post and "Sample Page"', 'user-first-kit' ); ?>
			<?php if ( $posts_pages_removed ) : ?>
			<span style="color: green; margin-left: 5px;">&#10004;</span>
			<?php endif; ?>
		</label>
		<h3></h3>
		<input type="submit" name="ufk_remove_all" class="button button-primary"
			value="<?php echo esc_attr__( 'Set All', 'user-first-kit' ); ?>" />
	</form>
</div>
		<?php
	}
	/**
	 * Setting Page From Submit handle.
	 */
	public function handle_form_submission() {
		if ( isset( $_POST['ufk_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ufk_nonce'] ) ), 'ufk_action' ) ) {
			if ( isset( $_POST['set_permalink'] ) ) {
				global $wp_rewrite;
				$wp_rewrite->set_permalink_structure( '/%postname%/' );
				$wp_rewrite->flush_rules();
			}

			if ( isset( $_POST['remove_default_plugins'] ) ) {
				$this->remove_default_plugins();
			}

			if ( isset( $_POST['remove_default_themes'] ) ) {
				$this->remove_default_themes();
			}

			if ( isset( $_POST['remove_default_posts_pages'] ) ) {
				$this->remove_default_posts_pages();
			}
		}
	}

	/**
	 * Remove Default Plugins.
	 */
	public function remove_default_plugins() {
		$plugins_to_remove = array(
			'hello.php',
			'hello-dolly/hello.php',
			'akismet/akismet.php',
		);

		foreach ( $plugins_to_remove as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
			}

			// Delete the plugin using delete_plugins().
			$deleted = delete_plugins( array( $plugin ) );

			// Check if the plugin was deleted.
			if ( isset( $deleted[ $plugin ] ) ) {
				// Plugin deleted successfully.
			} else {
				// Plugin deletion failed.
			}
		}
	}

	/**
	 * Remove Defualt Theme.
	 */
	public function remove_default_themes() {
		$themes_to_remove = array(
			'twentytwentyone',
			'twentytwentytwo',
			'twentytwentythree',
		);

		foreach ( $themes_to_remove as $theme ) {
			if ( wp_get_theme( $theme )->exists() ) {
				switch_theme( 'ufk-temp-theme' );
				delete_theme( $theme );
			}
		}
	}

	/**
	 * Remove Default Posts and Pages.
	 */
	public function remove_default_posts_pages() {
		// Remove the default "Hello World" post by ID.
		$hello_world_post_id = 1; // Assuming the ID of "Hello World" post is 1.
		$hello_world_post    = get_post( $hello_world_post_id );
		if ( $hello_world_post && 'post' === $hello_world_post->post_type ) {
			wp_delete_post( $hello_world_post->ID, true );
		}

		// Remove the default "Sample Page" by ID.
		$sample_page_id = 2; // Assuming the ID of "Sample Page" is 2.
		$sample_page    = get_post( $sample_page_id );
		if ( $sample_page && 'page' === $sample_page->post_type ) {
			wp_delete_post( $sample_page->ID, true );
		}
	}

	/**
	 * Check if default posts and pages are removed.
	 */
	public function are_default_posts_pages_removed() {
		// Check if default "Hello World" post and "Sample Page" are removed by ID.
		$hello_world_post_id = 1; // Assuming the ID of "Hello World" post is 1.
		$sample_page_id      = 2; // Assuming the ID of "Sample Page" is 2.

		$hello_world_post = get_post( $hello_world_post_id );
		$sample_page      = get_post( $sample_page_id );

		if ( $hello_world_post || $sample_page ) {
			return false;
		}

		return true; // All default posts and pages are removed.
	}

	/**
	 * Check if default plugins are removed.
	 */
	public function are_default_plugins_removed() {
		$plugins_to_check = array(
			'hello.php',
			'hello-dolly/hello.php',
			'akismet/akismet.php',
		);

		foreach ( $plugins_to_check as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return false; // At least one default plugin is still active.
			}

			// Check if the plugin files still exist.
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				return false; // At least one plugin file still exists.
			}
		}

		return true; // All default plugins are removed.
	}

	/**
	 * Remove Defualt Theme.
	 */
	public function are_default_themes_removed() {
		// Check if all default themes are removed.
		$themes_to_remove = array( 'twentytwentyone', 'twentytwentytwo', 'twentytwentythree' );

		foreach ( $themes_to_remove as $theme ) {
			if ( wp_get_theme( $theme )->exists() ) {
				return false;
			}
		}

		return true;
	}
}

// Instantiate the plugin class.
$user_first_kit_plugin = new User_First_Kit_Plugin();
