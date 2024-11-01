=== Plugin Name ===
Contributors:  samueljesse, uditvirwani, liuyang
Tags: maps, baidu, travel, route
Requires at least: 3.7
Tested up to: 4.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Travel Baidu Map is a Wordpress plugin to help people to create one or more Baidu maps with locations and 
route into your site.

== Description ==

Easily create Travel Baidu Maps to add to any Wordpress site. You can create one or multiple travel map 
, then insert the map with a shortcode.

With Travel Baidu Maps you can generate a map with multiple locations, a route, hyper links and pictures.

The motive is that China mainland user cannot access Google map, so they could not use these map plugins
based on Google API, but the map plugins based on Baidu API is a little limited.

This plugin comes from "Custom baidu maps" plugin, but the usage is totally different and lots of enhancement.

Basic Usage with shortcodes :

You just need to save the map as draft, then you can find a short code in the maps list page,
or you can find it in the current page after clicking "Publish" button.

[btmap id="id"] 
Options :
- id 	: unique id for a travel map, such as 333


Advanced Usage Travl Map post-type :

1. Enter your Baidu Developer API Key (if you have not already).
1. Select the "Travel Map" post type from the wordpess menu.
1. Click on "Add New".
1. Enter the map settings (height, width, zoom and coordinates).
1. Upon publishing, add the new generated shortcode to the page content.


Adding a Marker on the map (optional) :

1. Click on the map to add a new marker to this map.
1. Right click on the map to set the center point, the setting data will change too.
1. Scroll up or down to scale the map, or click the button within the map, the setting data will change too.
1. Click on "Show Marker Details" if you wish to see the details visible at start.
1. Change the "Back Color" if you wanna the color of that marker and the route ending into it.
1. The content of name and description for a marker supports HTML, so you could imbed some small image and or hyper link.

Adding a Marker on the map (optional) :

1. Click on "Add in travel route" if you wish to find it in the travel route.

Set Travel Map general setting:
1. Baidu Developers API key, used to access Baidu map API.
1. Default route color, you can specify it to make your first marker looks with this default color.
1. Default time to show detail after clicking marker, if a marker is set to show no detail at default, you can click
it to show detail, then you can click the detial area to hide it and show the marker again. But as my test result shows,
user cannot hide the detail in iPhone, so I added this option, to let the detail automatically hide after clicking. 



== Installation ==

Follow the steps below for the plugin installation :


1. Upload `travel-maps.zip` to the `/wp-content/plugins/` directory or add the plugin via wordpress plugin repository.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Enter your Baidu Developer API key in the settings page.


== Frequently Asked Questions ==

= Why is the plugin showing an error "You have not entered your Baidu Developer API Key" =

To use Baidu Maps, you need to have a Baidu Developer API Key.
To obtain the API Key please visit : http://lbsyun.baidu.com/apiconsole/key


== Screenshots ==
1. Setting in the travel map settings menu.
2. Snapshot for a travel map in editing with number marked markers.
3. Result of a travel map.

== Changelog ==

== Upgrade Notice ==
