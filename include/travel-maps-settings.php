<?php

class Baidu_Travel_Maps_Settings {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct( $plugin_url ) {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		$this->plugin_url = $plugin_url;
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_submenu_page(
			'edit.php?post_type=btmap',
			__( 'Settings', 'travel-maps' ),
			__( 'Settings', 'travel-maps' ),
			'manage_options',
			'travel-maps-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'travel_maps_settings' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( "Travel Map Settings", 'travel-maps' ); ?></h2>

			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'travel_maps' );
				do_settings_sections( 'travel-maps-settings' );
				submit_button();
				?>
			</form>

			<hr>
			<br>
			<h3>Where can I get a Baidu Developers API Key ?</h3>
			<p>
				Please go <a href='https://developer.baidu.com/'>there</a> to apply a Baidu developer account.
			</p>

		</div>
	<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'travel_maps',
			'travel_maps_settings',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'travel_maps_setting_general',
			__( 'General Settings', 'travel-maps' ),
			array( $this, 'print_section_info' ),
			'travel-maps-settings'
		);

		add_settings_field(
			'api_key',
			__( 'Baidu Developers API Key', 'travel-maps' ),
			array( $this, 'api_key_callback' ),
			'travel-maps-settings',
			'travel_maps_setting_general'
		);

		add_settings_field(
			'default_color',
			__( 'Default route color', 'travel-maps' ),
			array( $this, 'default_color_callback' ),
			'travel-maps-settings',
			'travel_maps_setting_general'
		);

		add_settings_field(
			'showtime',
			__( 'Default time to show detail after clicking marker', 'travel-maps' ),
			array( $this, 'default_showtime_callback' ),
			'travel-maps-settings',
			'travel_maps_setting_general'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		if ( ! empty( $input['api_key'] ) )
			$input['api_key'] = sanitize_text_field( $input['api_key'] );

		if ( ! empty( $input['showtime'] ) )
			$input['showtime'] = sanitize_text_field( $input['showtime'] );

		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function api_key_callback() {
		printf(
			'<input type="text" id="api_key" name="travel_maps_settings[api_key]" value="%s" style="width: 300px;"/>',
			esc_attr( $this->options['api_key'] )
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function default_showtime_callback() {
        $showtime = esc_attr( $this->options['showtime'] );
        if($showtime == ''){
    		printf(
    			'<input type="text" id="showtime" name="travel_maps_settings[showtime]" value="3000" style="width: 100px;"/>ms');
		}else{
    		printf(
    			'<input type="text" id="showtime" name="travel_maps_settings[showtime]" value="%s" style="width: 100px;"/>ms',
    			esc_attr( $this->options['showtime'] )
    		);
		}
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function default_color_callback() {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style( 'wp-color-picker' );
        $color = esc_attr( $this->options['default_color'] );
        if($color == ''){
		    $html[] = "<input type='text' class='color-picker-control' name='travel_maps_settings[default_color]' value='#000079' size='30' >";
		}else{
		    $html[] = "<input type='text' class='color-picker-control' name='travel_maps_settings[default_color]' value='" . $color . "' size='30' >";
		}
        echo implode( "\n", $html );
    ?>

    <script type="text/javascript">
    jQuery(document).ready(function($) {   
        $('.color-picker-control').wpColorPicker();
    });             
    </script>

<?php
	}

}
?>
