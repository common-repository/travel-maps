(function ($) {
	$(document).ready(function () {

		mapSearch();
		mapPointer();

		$(document).on('click', '.marker-controls .delete_marker', function (e) {
			e.preventDefault();
			remove_marker($(this));
		});

		$('.color-picker-control').wpColorPicker();

	});

    /**
        To save time, not scroll down each time, and add line in the map each click except the first one.
    */
	function insert_marker_both_text_map($el) {
        // Use the last color as default if there is any, otherwise use dark blue
		var $last_marker= $('.markers:last');
        var last_bg_color = $last_marker.find('.marker_row_color input').eq(0).val();
		if (typeof(last_bg_color) == 'undefined') {
            last_bg_color = $('#travel_maps_meta_default_color').val();
		}

		var prefix = 'travel_maps_marker_meta_';
		var marker_count = $('.markers').length;
		var html = '';
		var defaultBGColor = last_bg_color;
		var defaultFGColor = '#ffffff';

		html += "<div class='markers'>";
		html += "<div class='marker-controls'>";
		html += "<button class='button delete_marker'> Delete Marker </button>";
   		html += "<font color='red'><label><strong>" + marker_count + "</strong></label></font>";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_name'>";
		html += "<label>" + "Name</label>";
		html += "<input style='width:255px' type='text' name='" + prefix + 'name' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_desc'>";
		html += "<label>" + "Desc</label>";
		html += "<input type='text' name='" + prefix + 'description' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_location'>";
		html += "<label> Latitude / Longitude </label>";
		html += "<input type='text' name='" + prefix + 'lat' + '-' + marker_count + "' value='' size='30' >";
		html += "<input type='text' name='" + prefix + 'lng' + '-' + marker_count + "' value='' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_color'>";
		html += "<label> Back / Front Color </label>";
		html += "<input type='text' class='color-picker-control' name='" + prefix + 'bgcolor' + '-' + marker_count + "' value='" + defaultBGColor + "' size='30' >";
		html += "<input type='text' class='color-picker-control' name='" + prefix + 'fgcolor' + '-' + marker_count + "' value='" + defaultFGColor + "' size='30' >";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_is_open'>";
		html += "<label> Show Marker Details  </label>";
		html += "<input type='checkbox' name='" + prefix + 'isopen' + '-' + marker_count + "' />";
		html += "<span class='caption'>Check to always show marker details</span>";
		html += "</div>";

		html += "<div class='marker_row marker_row_default marker_row_in_route'>";
		html += "<label> Add in travel route  </label>";
		html += "<input type='checkbox' name='" + prefix + 'inroute' + '-' + marker_count + "' checked/>";
		html += "<span class='caption'>Check to add this point in the travel route</span>";
		html += "</div>";

		html += "</div>";

		var $marker = $(html);
		$('.marker-container').append($marker);

		$('.color-picker-control').wpColorPicker();

		return $marker;
	}


	function remove_marker($el) {
		var $parent = jQuery($el).parent().parent();
		$parent.css('position', 'relative');
		$parent.animate({
			left   : '100px',
			opacity: 0
		}, {
			complete: function () {
				$parent.remove();
				reorder_markers();
				redraw_overlay();
			}
		})

	}

	function redraw_overlay() {
		map.clearOverlays(); 

        var prev_point = null;
        var prev_x; var prev_y; var x; var y;
		$('.marker_row_location input').each(function (index) {
            // each point appear twice in this loop, first latitude, then longitude
            // (0, 1) -> (2,3), (2,3) -> (4,5)
            if(index % 2 == 0 ){
                prev_x = x;prev_y=y;
                x = $(this).val();
            }
            if(index % 2 == 1 ){
                // point is here
                y = $(this).val();
                var point = new BMap.Point(y, x);
                if(index > 2 ){
                    prev_point = new BMap.Point(prev_y, prev_x);
                    var polyline = new BMap.Polyline([prev_point, point], {strokeColor:'blue', strokeWeight:3, strokeOpacity:0.7} );
                    map.addOverlay(polyline);
                }

        		var marker_count = Math.floor(index / 2);
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
            }
		});

		var lat = $('#travel_maps_meta_center_lat').val();
    	var lng = $('#travel_maps_meta_center_lng').val();
    	addCenterMark(new BMap.Point(lng, lat));
	}

	function reorder_markers() {

		$('.markers').each(function (index) {
			$(this).find('input').each(function () {
				var name = $(this).attr('name');
				if (typeof(name) != 'undefined') {
					var name_split = name.split('-');
					$(this).attr('name', name_split[0] + '-' + index);
				}
			});
		});
		$('.marker-controls').each(function (index) {
			$(this).find('label').each(function () {
				var name = $(this).html();
				if (typeof(name) != 'undefined') {
					$(this).html('<font color="red"><strong>'+index + '</strong></font>' );
				}
			});
		});
	}

	function centerHere(point) {

        var zoom_level = map.getZoom();
		map.centerAndZoom(point, zoom_level);

		$('.BMap_Marker').remove();
		addCenterMark(point);

		$('#travel_maps_meta_center_lat').val(point.lat);
		$('#travel_maps_meta_center_lng').val(point.lng);

	}

	function mapSearch() {
		var $locationCheckUrl = $('.location-check-url');
		var $locationCheckBtn = $('.location-check-button');

		var searchSettings = {
			onSearchComplete: function (w) {

				if (typeof(w.getPoi(0)) != 'undefined') {
					var p = w.getPoi(0).point; 
					centerHere(p);
		            $('#travel_maps_meta_center_address').val($locationCheckUrl.val());
				}
			}
		}
        if((typeof(map) == 'undefined')) return; // in edit mode, no map
		var search = new BMap.LocalSearch(map, searchSettings);

		$locationCheckBtn.on('click', function (e) {
			e.preventDefault();
			baiduMapSearchFunction();
		});

		$locationCheckUrl.keypress(function (e) {
			if (e.keyCode == 13) {
				$(this).trigger('enter');
				return false;
			}
		});
		$locationCheckUrl.on('enter', function (e) {
			baiduMapSearchFunction();
		});

		function baiduMapSearchFunction() {

			var searchString = $locationCheckUrl.val();
			if (searchString != '') {
				search.search(searchString);
			}
		}
	}

	function addCenterMark(point) {
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
	}

	function mapPointer() {
		var delay = 300;
		var r_clicks = 0;
		var r_timer = null;
		var clicks = 0;
		var timer = null;
		var timer2 = null;

        if((typeof(map) == 'undefined')) return; // in edit mode, no map
		map.addEventListener("zoomend", function () {
			$('#travel_maps_meta_zoom').val(map.getZoom());
		});

		map.addEventListener("rightclick", function (c) {
			r_clicks++;
			if (r_clicks == 1) {
				r_timer = setTimeout(function () {
        			$('#travel_maps_meta_center_lat').val(c.point.lat);
        			$('#travel_maps_meta_center_lng').val(c.point.lng);
                    var zoom_level = map.getZoom();
            		map.centerAndZoom(c.point, zoom_level);
            
        		    $('.BMap_Marker').remove();
        		    addCenterMark(c.point);

                    // Get the center point address
                    myGeo = new BMap.Geocoder();      
           			var lat = $('#travel_maps_meta_center_lat').val();
			        var lng = $('#travel_maps_meta_center_lng').val();
                    myGeo.getLocation(new BMap.Point(lng, lat), function(result){      
                        if (result){      
                            $('#travel_maps_meta_center_address').val(result.address);
                        }      
                    });
				    r_clicks = 0;
				}, delay*2);
			} else {
				clearTimeout(r_timer);
				r_clicks = 0;
			}
		});

		map.addEventListener("dblclick", function (c) {
			return;
		});

		map.addEventListener("click", function (c) {
			clicks++;
			if (clicks == 1) {
                var $marker;
				timer = setTimeout(function () {
					$('.BMap_Marker').remove();
                    var vectorCenter = new BMap.Marker(c.point, {
                      icon: new BMap.Symbol(BMap_Symbol_SHAPE_POINT, {
                        scale: 1,
                        fillColor: '#000079',
                        fillOpacity: 0.8
                      })
                    });
            		map.addOverlay(vectorCenter);

            		var marker_count = $('.markers').length;
                    var opts = {
                      position : c.point,
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

		            var lat = $('#travel_maps_meta_center_lat').val();
        		    var lng = $('#travel_maps_meta_center_lng').val();
         		    addCenterMark(new BMap.Point(lng, lat));

                    var prev_point = null;
		            if( $('.markers').length > 0){
		                var $last_marker= $('.markers:last');
        			    var $last_lng = $last_marker.find('.marker_row_location input').eq(0).val();
	        		    var $last_lat = $last_marker.find('.marker_row_location input').eq(1).val();
                        prev_point = new BMap.Point($last_lat, $last_lng);
                    }
                    if(prev_point !=null){
                        var polyline = new BMap.Polyline([prev_point, c.point], {strokeColor:'blue', strokeWeight:3, strokeOpacity:0.5} );
                        map.addOverlay(polyline);
                    }
                    $marker = insert_marker_both_text_map();
        			$marker.find('.marker_row_location input').eq(0).val(c.point.lat);
	        		$marker.find('.marker_row_location input').eq(1).val(c.point.lng);

					clicks = 0;
				}, delay);
				timer2 = setTimeout(function () {
                    var myGeo = new BMap.Geocoder();
                    myGeo.getLocation(new BMap.Point(c.point.lng, c.point.lat), function(result){      
                       if (result){      
	        		        $marker.find('.marker_row_desc input').eq(0).val("("+result.address+")");
                      }      
                    });
				}, delay*2);
			} else {
				map.zoomIn();
				clearTimeout(timer);
				clearTimeout(timer2);
				clicks = 0;
			}

		});
	}

})(jQuery)
