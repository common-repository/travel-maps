<?php

class Baidu_Travel_Maps_Admin {
	public function __construct( $plugin_url, $parent ) {

		// Register Plugins Settings
		$settings_page = new Baidu_Travel_Maps_Settings( $plugin_url );

		// Create the custom post-type and the meta boxes
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_map_details' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_marker_details' ) );

		// Modify wp_list_table with new columns
		add_filter( 'manage_edit-btmap_columns', array( $this, 'set_travel_maps_custom_columns' ) );
		add_action( 'manage_btmap_posts_custom_column', array( $this, 'travel_maps_custom_column' ), 10, 2 );


		$this->plugin_url = $plugin_url;
		$this->parent = $parent;
	}

	/**
	 * Register the custom post-type (btmap)
	 *
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => __( 'Travel Map', 'travel-maps' ),
			'singular_name'      => __( 'Travel Map', 'travel-maps' ),
			'add_new'            => __( 'Add New', 'travel-maps' ),
			'add_new_item'       => __( 'Add New', 'travel-maps' ),
			'edit_item'          => __( 'Edit Map', 'travel-maps' ),
			'new_item'           => __( 'New Map', 'travel-maps' ),
			'all_items'          => __( 'Travel Map', 'travel-maps' ),
			'view_item'          => __( 'View Map', 'travel-maps' ),
			'search_items'       => __( 'Search Maps', 'travel-maps' ),
			'not_found'          => __( 'No Maps found', 'travel-maps' ),
			'not_found_in_trash' => __( 'No Maps found in Trash', 'travel-maps' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Travel Map', 'travel-maps' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'baidu-map' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 100,
			'menu_icon'          => $this->plugin_url . 'assets/icons/menu-icon.png' ,
			'supports'           => array( 'title' )
		);

		register_post_type( 'btmap', $args );
	}

	/**
	 *
	 */
	public function create_meta_box() {
		global $post;

		add_meta_box( 'btmap-map-assistant', 'Grainbuds', array( $this, 'render_meta_box_assistant' ), 'btmap', 'side', 'high' );
		add_meta_box( 'btmap-map-shortcode', __( 'Map Shortcode', 'travel-maps' ), array( $this, 'render_meta_box_map_shortcode' ), 'btmap', 'side', 'low'  );
		add_meta_box( 'btmap-map-details', __( 'Map Settings', 'travel-maps' ), array( $this, 'render_meta_box_map_details' ), 'btmap', 'side', 'low' );
		add_meta_box( 'btmap-map-markers', __( 'Map Markers', 'travel-maps' ), array( $this, 'render_meta_box_map_markers' ), 'btmap', 'normal', 'low' );
		add_meta_box( 'btmap-map-location-check', __( 'Map', 'travel-maps' ), array( $this, 'render_meta_box_map_location_check' ), 'btmap', 'normal', 'high' );
	}

	/**
	 * Populates an array of form fields to use with the btmap custom post-type
	 *
	 * @return the array of form fields
	 */
	private function populate_meta_box_map_details() {

		$prefix = 'travel_maps_meta_';

		$baidu_meta_maps_details = array(
			array(
				'label' => __( 'Map Height', 'travel-maps' ) . ' (px)',
				'id'    => $prefix . 'height',
				'type'  => 'text',
				'placeholder' => '300'
			),
			array(
				'label' => __( 'Map Width', 'travel-maps' ) . ' (px|%)',
				'id'    => $prefix . 'width',
				'type'  => 'text',
				'placeholder' => '100%'
			),
			array(
				'label' => __( 'Zoom', 'travel-maps' ),
				'desc'  => '[1 - 20]',
				'id'    => $prefix . 'zoom',
				'type'  => 'text',
				'placeholder' => '13'
			),
			array(
				'label' => __( 'Map (Latitude)', 'travel-maps' ),
				'id'    => $prefix . 'center_lat',
				'type'  => 'text',
				'placeholder' => '39.915'
			),
			array(
				'label' => __( 'Map (Longitude)', 'travel-maps' ),
				'id'    => $prefix . 'center_lng',
				'type'  => 'text',
				'placeholder' => '116.404'
			),
			array(
				'label' => __( 'Map (Address)', 'travel-maps' ),
				'id'    => $prefix . 'center_address',
				'type'  => 'text',
				'hide'  => true,
				'placeholder' => '北京'
			),
			array(
				'label' => __( 'Default color', 'travel-maps' ),
				'id'    => $prefix . 'default_color',
				'type'  => 'text',
				'hide'  => true,
				'placeholder' => $this->parent->settings['default_color'],
				'value' => $this->parent->settings['default_color']
			),
		);

		return $baidu_meta_maps_details;
	}

	public function render_meta_box_map_shortcode() {
		global $post;
		$html = array();

		$html[] = '<div id="btmap-shortcode" class="click-to-copy"><code>[btmap id="' . $post->ID . '"]</code></div>';

		echo implode("\n", $html);
	}

	/**
	 * Render the map details meta box
	 *
	 */
	public function render_meta_box_map_details() {
		global $baidu_meta_maps_details, $post;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		wp_nonce_field( 'travel_maps_meta_box_map_details_nonce', 'travel_maps_meta_box_nonce' );

		$html[] = "<table class='form-table'>";

		foreach ( $baidu_meta_maps_details as $field ) {
			$meta = get_post_meta( $post->ID, $field['id'], true );

			if ( isset( $field['hide'] ) ) {
			    $html[] = '<span style="display:none">';
			} else{
    			$html[] = "<tr>";
    			$html[] = "<th>";
    			$html[] = "<label for='" . $field['id'] . "'>" . $field['label'] . "</label>";
    			$html[] = "</th>";
    			$html[] = "<td>";
            }
			switch ( $field['type'] ) {
				case 'text':
                    if ( isset( $field['value'] ) ) {
					    $html[] = "<input type='text' name='" . $field['id'] . "' id='" . $field['id'] . "' placeholder='" . $field['placeholder'] .  "' value='" . $field['value'] . "' size='10'>";
					}else{
					    $html[] = "<input type='text' name='" . $field['id'] . "' id='" . $field['id'] . "' placeholder='" . $field['placeholder'] .  "' value='" . $meta . "' size='10'>";
					}
					$html[] = "<br>";
					if ( isset( $field['desc'] ) ) {
						$html[] = "<span class='desc'>" . $field['desc'] . "</span>";
					}
					break;

				case 'checkbox':
					$checked = $meta ? "checked='checked'" : "";
					$html[]  = "<input type='checkbox' name='" . $field['id'] . "' id='" . $field['id'] . "'" . $checked . "/>";
					$html[]  = "<label for='" . $field['id'] . "'>" . $field['desc'] . "</label>";
					break;

				default:
					break;
			}
			if ( isset( $field['hide'] ) ) {
			    $html[] = '</span>';
			} else {
    			$html[] = "</td>";
    			$html[] = "</tr>";
		    }
		}

		$html[] = "</table>";


		echo implode( "\n", $html );
	}

	/**
	 * Render the marker details meta box
	 *
	 */
	public function render_meta_box_map_markers() {
		global $post;

		$prefix = 'travel_maps_marker_meta_';

		wp_nonce_field( 'travel_maps_meta_box_marker_details_nonce', 'travel_maps_meta_box_markers_nonce' );

		$markers = get_post_meta( $post->ID, 'markers', true );

		$html[] = "<div class='marker-container'>";


		if ( is_array( $markers ) ) { 
			foreach ( $markers as $marker_count => $marker ) {
				if ( empty( $marker ) ) continue;
				$html[] = "<div class='markers'>";

				$meta_name        = $marker[$prefix . 'name' . '-' . $marker_count];
				$meta_description = $marker[$prefix . 'description' . '-' . $marker_count];
				$meta_lat         = $marker[$prefix . 'lat' . '-' . $marker_count];
				$meta_lng         = $marker[$prefix . 'lng' . '-' . $marker_count];
				$meta_bgcolor     = $marker[$prefix . 'bgcolor' . '-' . $marker_count];
				$meta_fgcolor     = $marker[$prefix . 'fgcolor' . '-' . $marker_count];


				if (isset($marker[$prefix . 'isopen' . '-' . $marker_count])) {
					$meta_isopen = $marker[$prefix . 'isopen' . '-' . $marker_count];
				}else{
					$meta_isopen = false;
				}
				$checked_isopen   = $meta_isopen ? "checked='checked'" : "";

				if (isset($marker[$prefix . 'inroute' . '-' . $marker_count])) {
					$meta_inroute = $marker[$prefix . 'inroute' . '-' . $marker_count];
				}else{
					$meta_inroute = false;
				}
				$checked_inroute   = $meta_inroute ? "checked='checked'" : "";


				$html[] = "<div class='marker-controls'>";
				$html[] = "<button class='button delete_marker'>" . __( 'Delete Marker', 'travel-maps' ) . "</button>";
        		$html[] = "<label><font color='red'><strong>" . $marker_count . "</strong></font></label>";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_name marker_row_default'>";
				$html[] = "<label>" . __( "Name", 'travel-maps' ) . "</label>";
				$html[] = "<input style='width:255px' type='text' name='" . $prefix . 'name' . '-' . $marker_count . "' value='" . $meta_name . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_desc marker_row_default'>";
				$html[] = "<label>" . __( "Desc", 'travel-maps' ) . "</label>";
				$html[] = "<input type='text' name='" . $prefix . 'description' . '-' . $marker_count . "' value='" . $meta_description . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_location'>";
				$html[] = "<label>" . __( "Latitude / Longitude", 'travel-maps' ) . "</label>";
				$html[] = "<input type='text' name='" . $prefix . 'lat' . '-' . $marker_count . "' value='" . $meta_lat . "' size='30' >";
				$html[] = "<input type='text' name='" . $prefix . 'lng' . '-' . $marker_count . "' value='" . $meta_lng . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_color'>";
				$html[] = "<label>" . __( "Back / Front Color", 'travel-maps' ) . "</label>";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'bgcolor' . '-' . $marker_count . "' value='" . $meta_bgcolor . "' size='30' >";
				$html[] = "<input type='text' class='color-picker-control' name='" . $prefix . 'fgcolor' . '-' . $marker_count . "' value='" . $meta_fgcolor . "' size='30' >";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_is_open'>";
				$html[] = "<label>" . __( "Show Marker Details", 'travel-maps' ) . "</label>";
				$html[] = "<input type='checkbox' name='" . $prefix . 'isopen' . '-' . $marker_count . "' " . $checked_isopen . "/>";
				$html[] = "<span class='caption'>" . __( "Check to always show marker details", 'travel-maps' ) . "</span>";
				$html[] = "</div>";

				$html[] = "<div class='marker_row marker_row_default marker_row_in_route'>";
				$html[] = "<label>" . __( "Add in travel route", 'travel-maps' ) . "</label>";
				$html[] = "<input type='checkbox' name='" . $prefix . 'inroute' . '-' . $marker_count . "' " . $checked_inroute . "/>";
				$html[] = "<span class='caption'>" . __( "Check to add this point in the travel route", 'travel-maps' ) . "</span>";
				$html[] = "</div>";

				$html[] = "</div>";
			}
		}

		$html[] = "</div>";

		echo implode( "\n", $html );
	}

	public function render_meta_box_map_location_check() {
		global $post;

		$travel_maps_api = new Baidu_Travel_Maps_API();

        $id = $post->ID;
		$map = $travel_maps_api->createMapElement( $id, '0', '300');//this is map sourcr, not object

		$default_lat = get_post_meta( $post->ID, 'travel_maps_meta_center_lat', true );
		if ( empty( $default_lat ) ) $default_lat = '39.915';
		$default_lng = get_post_meta( $post->ID, 'travel_maps_meta_center_lng', true );
		if ( empty( $default_lng ) ) $default_lng = '116.404';
		$default_address = get_post_meta( $post->ID, 'travel_maps_meta_center_address', true );
		if ( empty( $default_address ) ) $default_address = '北京';
		$default_zoom = get_post_meta( $post->ID, 'travel_maps_meta_zoom', true );
		if ( empty( $default_zoom ) ) $default_zoom = '13';


		$travel_maps_api->createMap( $id, $default_zoom, $default_lat, $default_lng);


		$html[] = "<div class='location-check-box'>";
		$html[] = $map;
		$html[] = "</div>";


		echo implode( "\n", $html );
	}

	public function render_meta_box_assistant( $post_id ) {

		$html[] = "<label>" . __( 'Search for location', 'travel-maps' ) . "</label>";
		$html[] = "<input type='text' class='location-check-url' />";
		$html[] = "<button class='location-check-button button'>" . __( 'Search', 'travel-maps' ) . "</button>";

		echo implode( "\n", $html );
	}


	public function save_meta_box_map_details( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( ! isset( $_POST['travel_maps_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['travel_maps_meta_box_nonce'], 'travel_maps_meta_box_map_details_nonce' ) ) return;

		if ( ! current_user_can( 'edit_post' ) ) return;

		$baidu_meta_maps_details = $this->populate_meta_box_map_details();

		foreach ( $baidu_meta_maps_details as $field ) {
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, $field['id'], $new );
			}
			elseif ( $field['placeholder'] !== '' && $new === '' ){
				update_post_meta( $post_id, $field['id'], $field['placeholder'] );
			}
			elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, $field['id'], $old );
			}
		}
	}

	public function save_meta_box_marker_details( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( ! isset( $_POST['travel_maps_meta_box_markers_nonce'] ) || ! wp_verify_nonce( $_POST['travel_maps_meta_box_markers_nonce'], 'travel_maps_meta_box_marker_details_nonce' ) ) return;

		if ( ! current_user_can( 'edit_post' ) ) return;

		$prefix  = 'travel_maps_marker_meta_';
		$markers = array( array() );


		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, $prefix ) === 0 ) {
				$strs_marker  = explode( '-', $key );
				$marker_count = $strs_marker[1];

				$markers[$marker_count][$key] = $value;
			}
		}


		foreach ( $markers as $key => $value ) {
			$old = get_post_meta( $post_id, 'markers', true );
			$new = $markers;
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, 'markers', $new );
			}
			elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, 'markers', $old );
			}
		}


	}

	public function set_travel_maps_custom_columns( $columns ) {
		unset( $columns['date'] );
		$columns['shortcode']    = __( 'Shortcode', 'travel-maps' );
		$columns['center']  = __( 'Center point', 'travel-maps' );
		$columns['real_marker'] = __( 'Named makrer', 'travel-maps' );
		$columns['detail_out_marker'] = __( 'Detail On makrer', 'travel-maps' );
		$columns['marker_standalone'] = __( 'Marker alone', 'travel-maps' );
		$columns['marker_in_route'] = __( 'Marker in route', 'travel-maps' );
		$columns['anchor'] = __( 'Anchors', 'travel-maps' );
		$columns['allmarker'] = __( 'All makrers', 'travel-maps' );

		return $columns;
	}

	public function travel_maps_custom_column( $column, $post_id ) {
		$prefix = 'travel_maps_marker_meta_';
		switch ( $column ) {

			case 'allmarker' :
				$markers = get_post_meta( $post_id, 'markers', true );
				$all_markers = sizeof( $markers );
				if ( $all_markers > 0 )  {
					if ( sizeof( $markers[0] ) > 0 ) {
						echo $all_markers;
					}else{
						echo '0';
					}
				}else{
					echo '0';
				}
				break;

			case 'anchor' :
				$markers = get_post_meta( $post_id, 'markers', true );
        		if ( is_array( $markers ) ) { 
        			$anchor = 0;
        			foreach ( $markers as $marker_count => $marker ) {
        				if ( empty( $marker ) ) continue;
        
        				$meta_name = $marker[$prefix . 'name' . '-' . $marker_count];
        				if(strlen($meta_name)==0) {$anchor++ ;}
                    }
                    echo $anchor;
                }
				break;

			case 'real_marker' :
				$markers = get_post_meta( $post_id, 'markers', true );
        		if ( is_array( $markers ) ) { 
        			$real_marker = 0;
        			foreach ( $markers as $marker_count => $marker ) {
        				if ( empty( $marker ) ) continue;
        
        				$meta_name = $marker[$prefix . 'name' . '-' . $marker_count];
        				if(strlen($meta_name)>0) {$real_marker++ ;}
                    }
                    echo $real_marker;
                }
				break;

			case 'marker_in_route' :
				$markers = get_post_meta( $post_id, 'markers', true );
        		if ( is_array( $markers ) ) { 
        			$in_route = 0;
        			foreach ( $markers as $marker_count => $marker ) {
        				if ( empty( $marker ) ) continue;
        
        				if (isset($marker[$prefix . 'inroute' . '-' . $marker_count])) {
        			        $in_route++;
        				}
                    }
                    echo $in_route;
                }
				break;

			case 'marker_standalone' :
				$markers = get_post_meta( $post_id, 'markers', true );
        		if ( is_array( $markers ) ) { 
        			$out_route = 0;
        			foreach ( $markers as $marker_count => $marker ) {
        				if ( empty( $marker ) ) continue;
        
        				if (!isset($marker[$prefix . 'inroute' . '-' . $marker_count])) {
        			        $out_route++;
        				}
                    }
                    echo $out_route;
                }
				break;

			case 'detail_out_marker' :
				$markers = get_post_meta( $post_id, 'markers', true );
        		if ( is_array( $markers ) ) { 
        			$detail = 0;
        			foreach ( $markers as $marker_count => $marker ) {
        				if ( empty( $marker ) ) continue;
        
        				if (isset($marker[$prefix . 'isopen' . '-' . $marker_count])) {
        			        $detail++;
        				}
                    }
                    echo $detail;
                }
				break;

			case 'shortcode' :
				echo '[btmap id="' . get_the_ID( $post_id ) . '"]';
				break;
			case 'center' :
				$lat = get_post_meta( $post_id, 'travel_maps_meta_center_lat', true );
				$lng = get_post_meta( $post_id, 'travel_maps_meta_center_lng', true );
				$address = get_post_meta( $post_id, 'travel_maps_meta_center_address', true );

				if ( $lat && $lng ) {
					echo $address;
					echo '(';
					echo $lat;
					echo ' , ';
					echo $lng;
					echo ')';
					//echo $addr;
				}
				else {
					echo _e( "No Location Defined", 'travel-maps' );
				}

				break;

		}
	}

}
