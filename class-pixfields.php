<?php
/**
 * PixFields.
 * @package   PixFields
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      http://pixelgrade.com
 * @copyright 2014 Pixelgrade
 */

/**
 * Plugin class.
 * @package   PixFields
 * @author    Pixelgrade <contact@pixelgrade.com>
 */
class PixFieldsPlugin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.0.0
	 * @const   string
	 */
	protected $version = '0.6.2';

	/**
	 * Unique identifier for your plugin.
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'pixfields';

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Path to the plugin.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_basepath = null;

	public $display_admin_menu = false;

	protected $config;

	protected static $number_of_images;

	public $plugin_settings = null;

	public static $fields_list = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 * @since     1.0.0
	 */
	protected function __construct() {

		$this->plugin_basepath = plugin_dir_path( __FILE__ );
		$this->config          = self::config();

		$this->get_pixfields_list();
		$this->get_plugin_settings();


		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'pixfields.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 99999999999 );
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// add the metabox
		// modal
		add_action( 'add_meta_boxes', array( $this, 'pixfields_add_modal_meta_box' ) );
		add_action( 'save_post', array( $this, 'pixfields_save_meta_data' ) );
		// fields
		add_action( 'add_meta_boxes', array( $this, 'add_pixfields_meta_box' ) );
//		add_action( 'save_post', array( $this, 'pixfields_save_modal_data' ) );

		//@deprecated
		//add_filter( 'icl_wpml_config_array', array( $this, 'add_fields_to_the_wpml_options' ), 1 );

		// a little hook into the_content
		add_filter( 'the_content', array( $this, 'hook_into_the_content' ), 10, 1 );

		/**
		 * Ajax Callbacks
		 */
		add_action( 'wp_ajax_save_pixfields', array( $this, 'ajax_save_pixfields' ) );
		add_action( 'wp_ajax_pixfield_autocomplete', array( $this, 'ajax_pixfield_autocomplete' ) );
		// only admins can access this
		add_action( 'wp_ajax_nopriv_save_pixfields', array( $this, 'ajax_no_access' ) );
		add_action( 'wp_ajax_nopriv_pixfield_autocomplete', array( $this, 'ajax_no_access' ) );
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function config() {
		// @TODO maybe check this
		return include 'plugin-config.php';
	}

	/**
	 * Fired when the plugin is activated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

	}

	/**
	 * Fired when the plugin is deactivated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$current_post_type = get_post_type();
		$is_post_page = false;

		if ( $current_post_type && isset( $this->plugin_settings['display_on_post_types'] ) ) {
			$is_post_page = array_key_exists( $current_post_type, $this->plugin_settings['display_on_post_types'] );
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix || $is_post_page ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();

		$current_post_type = get_post_type();
		$is_post_page = false;

		if ( $current_post_type && isset( $this->plugin_settings['display_on_post_types'] ) ) {
			$is_post_page = array_key_exists( $current_post_type, $this->plugin_settings['display_on_post_types'] );
		}

		if ( $screen->id == $this->plugin_screen_hook_suffix || ( $is_post_page && $this->is_edit_page() ) ) {

			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ), $this->version );

			$localized_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);

			if ( isset( $this->plugin_settings['display_on_post_types'] ) || ! empty( $this->plugin_settings['display_on_post_types'] ) ) {
				$localized_array['pixfields'] =  $this->get_pixfields_list();
			}

			wp_localize_script( $this->plugin_slug . '-admin-script', 'pixfields_l10n',$localized_array);
		}
	}

	function is_edit_page($new_edit = null){
		global $pagenow;
		//make sure we are on the backend
		if (!is_admin()) return false;


		if($new_edit == "edit")
			return in_array( $pagenow, array( 'post.php',  ) );
		elseif($new_edit == "new") //check for new post page
			return in_array( $pagenow, array( 'post-new.php' ) );
		else //check for either new or edit
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 * @since    1.0.0
	 */
	function enqueue_styles() {
		//		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array('wpgrade-main-style'), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version, true );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page( __( 'PixFields', $this->plugin_slug ), __( 'PixFields', $this->plugin_slug ), 'manage_options', $this->plugin_slug, array(
			$this,
			'display_plugin_admin_page'
		) );
	}

	/**
	 * Render the settings page for this plugin.
	 */
	function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	function add_action_links( $links ) {
		return array_merge( array( 'settings' => '<a href="' . admin_url( 'options-general.php?page=pixfields' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' ), $links );
	}

	/**
	 * Adds a box to the main column on any post type checked in settings
	 */
	function add_pixfields_meta_box() {

		if ( ! isset( $this->plugin_settings['display_on_post_types'] ) || empty( $this->plugin_settings['display_on_post_types'] ) ) {
			return;
		}

		foreach ( $this->plugin_settings['display_on_post_types'] as $post_type => $val ) {

			// Make a nice metabox title
			$post_type_obj = get_post_type_object($post_type);
			$post_type_name = $post_type;
			if ( $post_type_obj !== null ) {
				$post_type_name = $post_type_obj->labels->singular_name;
			}

			add_meta_box(
				'pixfields',
				$post_type_name . __( ' fields', 'pixfields_txtd' ),
				array( $this, 'pixfields_meta_box_callback' ),
				$post_type,
				'side'
			);
		}
	}

	function pixfields_meta_box_callback( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'pixfields_meta_box', 'pixfields_meta_box_nonce' );

		// these settings depend on post type
		$post_type = $post->post_type;
		if ( empty( $post_type ) ) {
			return false;
		} ?>
		<ul class="pixfields" data-post_type="<?php echo $post_type; ?>">
			<?php // check if we have fields for this post type

			$fields_list = $this->get_pixfields_list();
			if ( isset( $fields_list[$post->post_type] ) && ! empty( $fields_list[$post->post_type] ) ) {
					foreach ( $fields_list[$post->post_type] as $key => $field ) {
						$meta_key = 'pixfield_' . $field['meta_key'];
						$value = get_post_meta( $this->get_the_post_id( $post->ID, $post->post_type ), $meta_key, true); ?>
						<li class="pixfield" data-pixfield="<?php echo $meta_key ?>">
							<label for="<?php echo $meta_key; ?>"><?php echo $field['label'];?></label>
							<br/>
							<input type="text" class="pixfield_value" name="<?php echo $meta_key; ?>" <?php echo ( !empty( $value ) ) ? 'value="'.$value . '"' :''; ?>/>
						</li>
					<?php } ?>
				<?php
			} ?>
		</ul>

		<?php
		if ( isset( $this->plugin_settings['allow_edit_on_post_page'] ) && $this->plugin_settings['allow_edit_on_post_page'] ) { ?>
			<span class="manage_button_wrapper">
				<a href="#" class="open_pixfields_modal"><?php _e( 'Manage fields', 'pixfields_txtd' ); ?></a>
			</span>
		<?php }
	}

	function get_the_post_id( $id, $post_type = 'post' ) {
		if(function_exists('icl_object_id')) {
			return icl_object_id($id, $post_type, true);
		} else {
			return $id;
		}
	}

	/**
	 * When the post is saved, saves our custom data.
	 * @param int $post_id The ID of the post being saved.
	 */
	function pixfields_save_meta_data( $post_id ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
		// Check if our nonce is set and if it's valid.
		if ( ! isset( $_POST['pixfields_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['pixfields_meta_box_nonce'], 'pixfields_meta_box' ) ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Make sure that it is set.
		if ( ! isset( $_POST['pixfields_list'] ) || ! is_array( $_POST['pixfields_list'] ) ) {
			return;
		}

		global $post;
		// get only our pixfields keys and values #regex #hack #danger
		$meta_keys = self::get_pixfields_keys( $post->post_type );
		$pixfield_keys = array_intersect_key( $_POST, $meta_keys );

		foreach ( $pixfield_keys as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	function pixfields_add_modal_meta_box() {

		if ( ! isset( $this->plugin_settings['display_on_post_types'] ) || empty( $this->plugin_settings['display_on_post_types'] ) ) {
			return;
		}
		foreach ( $this->plugin_settings['display_on_post_types'] as $post_type => $val ) {
			add_meta_box(
				'pixfields_manager',
				__( 'PixFields', 'pixfields_txtd' ),
				array( $this, 'modal_meta_box_callback' ),
				$post_type
			);
		}
	}

	function modal_meta_box_callback ( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'pixfields_modal_meta_box', 'pixfields_modal_meta_box_nonce' ); ?>

		<div class="pixfields_manager_modal">
			<?php
			$config = include pixfields::pluginpath() . 'plugin-config' . EXT;
			$processor = pixfields::processor( $config );

			$f = pixfields::form( $config, $processor ); ?>
			<div class="pixfields_form">
				<?php echo $f->field( 'fields_manager' )->render(); ?>
			</div>
		</div>

	<?php }

	function ajax_no_access() {
		echo 'you have no access here';
		die();
	}

	function ajax_save_pixfields(){

		ob_start();
		if ( isset( $_REQUEST['fields'] ) ) {
			$fields_string = $_REQUEST['fields'];
		} else {
			wp_send_json_error( 'No fields sent' );
			exit;
		}

		$post = get_post( $_REQUEST['post_id'] );

		parse_str($fields_string, $fields);

		if ( ! isset ( $fields['pixfields_list'] ) ) {
			$fields['pixfields_list'] = array( $post->post_type => array() );
		}

		$this->make_fields( $post->post_type, $fields['pixfields_list'] );
		echo $this->pixfields_meta_box_callback( $post );
		$out =  ob_get_clean();
		wp_send_json_success($out);
		exit;
	}

	function ajax_pixfield_autocomplete() {
		ob_start();
		if ( ! isset( $_REQUEST['post_type'] ) && ! isset( $_REQUEST['pixfield'] ) && ! isset( $_REQUEST['value'] ) ) {
			wp_send_json_error( 'No data recived' );
			exit;
		}
		$values = $this->get_meta_values( $_REQUEST['pixfield'], $_REQUEST['post_type'] );
		echo json_encode($values);
		exit;
	}

	function make_fields( $post_type, $pixfields_list ) {
		if ( ! empty ( $pixfields_list ) ) {
			foreach ( $pixfields_list as $post_type => $fields ){

				// check if this post type has fields
				if ( empty( $fields ) ) {
					self::$fields_list[$post_type] = array();
					continue;
				}

				// make a list with all meta_keys already existent ( they should already be unique )
				$unique_meta_keys = array();
				foreach ( $fields as $key => $field ) {
					if ( isset( $field['meta_key'] ) ) {
						$unique_meta_keys[ $field['meta_key'] ] = $field['meta_key'];
					}
				}

				$fields = array_values( $fields );

				foreach ( $fields as $key => $field ) {

					$fields[$key] = array_map('sanitize_text_field', $field );

					// if we don't have a meta key this means this is a new field
					// so we ensure there isn't already a meta_key with the same value
					if ( ! isset( $field['meta_key'] ) ) {
						$meta_key = $field['label']; // old way sanitize_title_with_dashes( $field['label'] );

						// try to sanitize a little
						$meta_key = strip_tags($meta_key);
						$meta_key = strtolower($meta_key);
						$meta_key = preg_replace('/&.+?;/', '', $meta_key); // kill entities
						$meta_key = str_replace(array(' ', '.', '!', '?', ',', ':' ), '-', $meta_key);

						// but if it is we make it unique with a timestamp suffix
						if ( in_array($meta_key, $unique_meta_keys) ) {
							$meta_key = $meta_key . '-'. time();
						}

						array_push($unique_meta_keys, $meta_key);

						$fields[$key]['meta_key'] = $meta_key;
					}
				}

				self::$fields_list[$post_type] = $fields;
			}

			// Update the meta field in the database.
			update_option('pixfields_list', self::$fields_list);
		} else {
			self::$fields_list[ $post_type ] = array();
			update_option('pixfields_list', self::$fields_list[ $post_type ] );
		}
	}

	function hook_into_the_content( $content ) {
		if ( ! isset( $this->plugin_settings['display_place'] ) ) {
			return $content;
		}

		// when we have a shotcode this is not needed anymore
		if ( has_shortcode( $content, 'pixfields' ) ) {
			return $content;
		}

		global $post;

		$metadata = $this->get_template( $post->ID );
		if ( $this->plugin_settings['display_place'] == 'after_content' ) {
			return $content . $metadata;
		} elseif ( $this->plugin_settings['display_place'] == 'before_content') {
			return $metadata . $content;
		}
		return $content;
	}

	public function get_template( $post_id = null ) {

		if ( $post_id == null ) {
			$post = get_post( $post_id );
		} else {
			global $post;
		}

		$template_name = 'pixfields_box' . EXT;
		$_located      = locate_template( "templates/" . $template_name, false, false );

		// use the default one if the (child) theme doesn't have it
		if ( ! $_located ) {
			$_located = dirname( __FILE__ ) . '/views/' . $template_name;
		}

		$pixfields = $this->get_post_pixfields( $post->post_type, $post->ID );

		ob_start();

		require $_located;

		return ob_get_clean();
	}

	public function get_post_pixfields( $post_type, $post_id ){
		$fields_list = $this->get_pixfields_list();

		$keys = array();
		if ( isset($fields_list[$post_type] ) ) {
			foreach ($fields_list[$post_type] as $field ) {

				$keys[ $field['meta_key'] ] = array(
					'label' => $field['label'],
					'value' => get_post_meta( $post_id, 'pixfield_' . $field['meta_key'], true),
				);

				if ( isset( $field['filter'] ) ) {
					$keys[ $field['meta_key'] ]['filter'] = $field['filter'];
				}
			}
		}

		return $keys;
	}

	static function get_pixfields_keys( $post_type ) {

		if ( isset( self::$fields_list[$post_type] ) ) {
			$keys = array();
			foreach ( self::$fields_list[$post_type] as $field ) {
				$keys[ 'pixfield_' . $field['meta_key'] ] = '';
			}
			return $keys;
		}
		return false;
	}

	function update_plugin_setting( $name, $value ) {
		// it doesn't matter if the settings doesn't exist we create one then
		$this->plugin_settings[ $name ] = $value;
		update_option( 'pixfields_settings', $this->plugin_settings );
	}

	public function get_plugin_settings() {

		if ( $this->plugin_settings === null ) {
			$this->plugin_settings = get_option( 'pixfields_settings' );
		}

		return $this->plugin_settings;
	}


	public function get_pixfields_list(){

		if ( self::$fields_list === null ) {
			self::$fields_list = get_option( 'pixfields_list' );
		}

		return self::$fields_list;
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 * @deprecated
	 */
	// inform WPML about PixField's labels and pass them as admin-texts
	function  add_fields_to_the_wpml_options( $options ) {

		if ( isset( $options['wpml-config']['admin-texts'] ) ) {

			$pixfields_list = get_option('pixfields_list');

			// add pixfields labels to the wpml config list
			$pixfields_wpml_config = array(
				'attr' => array( 'name' => 'pixfields_list' )
			);

			if ( ! empty( $pixfields_list ) ) {
				foreach ( $pixfields_list as $post_type => $pixfields ) {
					$these_args = array(
						'attr' => array( 'name' => $post_type )
					);

					foreach ( $pixfields as $pkey => $pixfield ) {
						$these_args['key'][] = array(
							'attr' => array( 'name' => $pkey ),
							'key' => array( 'attr' => array( 'name' => 'label' ) )
						);
					}

					$pixfields_wpml_config['key'][] = $these_args;
				}
			}

			$pixfields_wpml_config['type'] = 'plugin';
			$pixfields_wpml_config['context'] = 'pixfields';

			$options['wpml-config']['admin-texts']['key'][] = $pixfields_wpml_config;
		}

		return $options;
	}

	function get_meta_values( $key = '', $type = 'post', $status = 'publish' ) {

		if( empty( $key ) )
			return;

		//first get all posts of a certain type
		$args = array(
			'numberposts' => -1,
			'post_type' => $type,
			'post_status'      => $status,
			'suppress_filters' => false, //allow filters - like WPML
		);

		$posts = get_posts($args);

		//now go through each and get the meta value for the given meta key
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post) {
				$meta_value = get_post_meta($post->ID, $key, true);

				if ( ! empty($meta_value) && strlen($meta_value) < 26 ) { //only 25 characters max
					$r[] = $meta_value;
				}
			}
		}

		if ( !empty($r) ) {
			$r = array_filter( $r );
			return array_combine($r, $r);
		}
		return false;
	}

	static function get_base_path() {
		return plugin_dir_path( __FILE__ );
	}
}
