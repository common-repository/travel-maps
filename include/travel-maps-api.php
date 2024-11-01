<?php


class Baidu_Travel_Maps_API {


	/**
	 *    Create HTML for the Baidu Map
	 */
	public function createMapElement( $id, $width, $height) {
		$height = esc_attr( $height ) . 'px';
		$width  = '100%';//  . 'px';

		$html[] = "<div class='baidu-map-container' style='width: {$width}' >";
		$html[] = "<div id='$id' class='baidu-map' style='width: {$width}; height: {$height};'></div>";
		$html[] = "</div>";

		return implode( "\n", $html );
	}

    // Create map for admin/edit page
	public function createMap( $id, $zoom, $lat, $lng ) {
		$markers    = get_post_meta( $id, 'markers', true );
		$center_lat = get_post_meta( $id, 'travel_maps_meta_center_lat', true );
		$center_lng = get_post_meta( $id, 'travel_maps_meta_center_lng', true );
		$prefix     = 'travel_maps_marker_meta_';
		?>

		<script type='text/javascript'>
			(function ($) {
				$(document).ready(function () {
					// Create the map
					window.map = new BMap.Map('<?php echo $id; ?>', {
						enableMapClick: false
					});
					map.centerAndZoom(new BMap.Point(<?php echo $lng; ?>, <?php echo $lat; ?>), <?php echo $zoom; ?>);
					map.addControl(new BMap.NavigationControl());
					var opts = {anchor: BMAP_ANCHOR_TOP_LEFT}; 
					map.addControl(new BMap.ScaleControl(opts));
					map.enableScrollWheelZoom(true);

                    var points_in_route = new Array();
                    var route_color;
                    var route_colors = new Array();
					<?php if (is_array($markers) || is_object($markers)){ ?>
					<?php foreach($markers as $marker_count => $marker) : ?>

					<?php
							$meta_lat = $marker[$prefix . 'lat' . '-' . $marker_count];
							$meta_lng = $marker[$prefix . 'lng' . '-' . $marker_count];
							$meta_bgcolor = $marker[$prefix . 'bgcolor' . '-' . $marker_count];

							if (isset($marker[$prefix . 'inroute' . '-' . $marker_count])) {
								$meta_inroute = $marker[$prefix . 'inroute' . '-' . $marker_count];
							}else{
								$meta_inroute = false;
							}

							if(($meta_lat == '' || !is_numeric($meta_lat))
									&& ($meta_lng == '' || !is_numeric($meta_lng))) continue;
					?>
					var point = new BMap.Point(<?php echo $meta_lng?>, <?php echo $meta_lat?>);
                    var marker_count = <?php echo $marker_count?>;
                    var opts = {
                      position : point,
                      offset   : new BMap.Size(5, -40)
                    }
                    var label = new BMap.Label(marker_count, opts);
                        label.setStyle({
                             color : "red",
                             fontSize : "12px",
                             height : "20px",
                             lineHeight : "20px"
                         });
                    map.addOverlay(label);
					var checked_inroute = "<?php echo esc_attr($meta_inroute); ?>";
                    if(checked_inroute){
                        points_in_route.push(point);
                        route_color = "<?php echo esc_attr($meta_bgcolor); ?>";
                        route_colors.push(route_color);
                    }
					<?php endforeach; ?>
					<?php } ?>
                    for (var i=1;i<points_in_route.length;i++){ 
                        var polyline = new BMap.Polyline(points_in_route.slice(i-1,i+1) , {strokeColor:'blue', strokeWeight:3, strokeOpacity:0.5});
                        map.addOverlay(polyline);
                    }


					<?php
							if(($center_lat != '' || is_numeric($center_lat))
									&& ($center_lng != '' || is_numeric($center_lng))) {
					?>
                        var lng = <?php echo $center_lng ?>;
                        var lat = <?php echo $center_lat ?>;
             		    point = new BMap.Point(<?php echo $center_lng ?>, <?php echo $center_lat?>);
                        var vectorCenter = new BMap.Marker(point , {
                          icon: new BMap.Symbol(BMap_Symbol_SHAPE_FORWARD_CLOSED_ARROW, {
                            scale: 1,
                            strokeWeight: 1,
                            rotation: 0,
                            fillColor: 'orange',
                            fillOpacity: 0.9
                          })
                        });
                        map.addOverlay(vectorCenter);
					<?php } ?>


				})
			})(window.jQuery)
		</script>

	<?php
	}

    // create map for real web page in blog
	public function createMapWithID( $id, $showtime ) {
		$height     = get_post_meta( $id, 'travel_maps_meta_height', true );
		$width      = get_post_meta( $id, 'travel_maps_meta_width', true );
		$center_lat = get_post_meta( $id, 'travel_maps_meta_center_lat', true );
		$center_lng = get_post_meta( $id, 'travel_maps_meta_center_lng', true );
		$zoom       = get_post_meta( $id, 'travel_maps_meta_zoom', true );
		$markers    = get_post_meta( $id, 'markers', true );
		$prefix     = 'travel_maps_marker_meta_';

		$map_element = $this->createMapElement( $id, $width, $height);

		if ( ( $center_lat == '' || ! is_numeric( $center_lat ) ) ) {
			$center_lat = '39.915';
		}
		if ( ( $center_lng == '' || ! is_numeric( $center_lng ) ) ) {
			$center_lng = '116.404';
		}
		if ( ( $zoom == '' || ! is_numeric( $center_lng ) ) ) {
			$zoom = 13;
		}

		?>

		<script>
			(function ($) {
				$(document).ready(function () {
					// Create the map
					var map = new BMap.Map('<?php echo $id; ?>', {
						enableMapClick: false
					});
					map.centerAndZoom(new BMap.Point(<?php echo $center_lng; ?>, <?php echo $center_lat; ?>), <?php echo $zoom; ?>);
					map.addControl(new BMap.NavigationControl());
					var opts = {anchor: BMAP_ANCHOR_TOP_LEFT}; 
					map.addControl(new BMap.ScaleControl(opts));
					map.enableScrollWheelZoom(true);

                    var points_in_route = new Array();
                    var route_color;
                    var route_colors = new Array();
					<?php foreach($markers as $marker_count => $marker) : ?>

					<?php
							$meta_name = $marker[$prefix . 'name' . '-' . $marker_count];
							$meta_description = $marker[$prefix . 'description' . '-' . $marker_count];
							$meta_lat = $marker[$prefix . 'lat' . '-' . $marker_count];
							$meta_lng = $marker[$prefix . 'lng' . '-' . $marker_count];
							$meta_bgcolor = $marker[$prefix . 'bgcolor' . '-' . $marker_count];
							$meta_fgcolor = $marker[$prefix . 'fgcolor' . '-' . $marker_count];

							if (isset($marker[$prefix . 'isopen' . '-' . $marker_count])) {
								$meta_isopen = $marker[$prefix . 'isopen' . '-' . $marker_count];
							}else{
								$meta_isopen = false;
							}

							if (isset($marker[$prefix . 'inroute' . '-' . $marker_count])) {
								$meta_inroute = $marker[$prefix . 'inroute' . '-' . $marker_count];
							}else{
								$meta_inroute = false;
							}

							if(($meta_lat == '' || !is_numeric($meta_lat))
									&& ($meta_lng == '' || !is_numeric($meta_lng))) continue;
					?>

					var point = new BMap.Point(<?php echo $meta_lng?>, <?php echo $meta_lat?>);
					var checked_isopen = "<?php echo esc_attr($meta_isopen); ?>";
					var checked_inroute = "<?php echo esc_attr($meta_inroute); ?>";
                    route_color = "<?php echo esc_attr($meta_bgcolor); ?>";
                    if(checked_inroute){
                        points_in_route.push(point);
                        route_colors.push(route_color);
                    }

					var tmp = "<?php echo esc_attr($meta_name); ?>";
					if(tmp.length > 0 || !checked_inroute){
					//  Draw normal marker if it's a named or a standalone one
                        var vectorMarker = new BMap.Marker(new BMap.Point(point.lng,point.lat+0.00), {
                          // 指定Marker的icon属性为Symbol
                          icon: new BMap.Symbol(BMap_Symbol_SHAPE_POINT, {
                            scale: 1,//图标缩放大小
                            fillColor: route_color,//填充颜色
                            fillOpacity: 0.8//填充透明度
                          })
                        });
                         map.addOverlay(vectorMarker);
					var data = {
						name       : "<?php echo esc_attr($meta_name); ?>",
						description: "<?php echo esc_attr($meta_description); ?>",
						bgcolor    : "<?php echo esc_attr($meta_bgcolor); ?>",
						fgcolor    : "<?php echo esc_attr($meta_fgcolor); ?>",
						showtime   : "<?php echo esc_attr($showtime); ?>",
						isHidden   : checked_isopen ? false : true,
						marker     : ''
					};
					console.log(data.showtime);

					data.marker = vectorMarker;
					var marker = new TBMarker(point, data);

					map.addOverlay(marker);

					}else{
                        var vectorCircle = new BMap.Marker(point, {
                          icon: new BMap.Symbol(BMap_Symbol_SHAPE_CIRCLE, {
                            scale: 2,
                            fillColor: "red",//填充颜色
                            rotation: 0
                          })
                        });
                        map.addOverlay(vectorCircle);
                    }

					<?php endforeach; ?>
                    for (var i=1;i<points_in_route.length;i++){ 
                        var polyline = new BMap.Polyline(points_in_route.slice(i-1,i+1) , {strokeColor:route_colors[i], strokeWeight:3, strokeOpacity:0.9});
                        map.addOverlay(polyline);
                    }

				});
			})(window.jQuery)
		</script>

		<?php


		return $map_element;
	}
}
