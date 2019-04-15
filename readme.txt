=== Rest Routes ===
Tags: rest, api, endpoints, json, routes
Requires at least: 4.8.1
Tested up to: 4.8.1
Stable tag: 2.0.1
License: MIT
License URI: https://opensource.org/licenses/MIT 

Activate WP REST API routes for custom post types that were registered by another plugin or theme.

== Description ==
 
This plugin allows you to quickly add REST API support by activating routes to existing custom content types that were registered by another plugin or theme. This can be useful if you don't have control or want to modify the plugin or theme
that registered the custom post type.  

== Installation ==

1. Upload the `custom-rest-routes` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the "REST routes" menu item location under the Settings menu.

== Frequently Asked Questions ==

= Why do I need this plugin? =

If you installed a plugin or theme that registered a custom content type for you without enabling its REST API endpoint, then this plugin is for you!

= Why wouldn't I just enable REST API support on the other plugin/theme? =

Modifying a plugin or theme you did not write can be time consuming, and can lead to issues such as losing the ability to update the plugin.  

== Screenshots ==

1. The location of the settings page for this plugin.
2. The initial settings page with no routes activated.
3. After activating the routes you can test them by clicking the URL.
4. Multisite and Pressbooks support

== Changelog ==
= 2.0.1 =
* updating author to org
* updating deps

= 2.0.0 =
* renaming plugin to avoid conflict

= 1.0.3 =
* add multisite support
* coding standards, optimization
* language support
* changing endpoints to routes to reflect functionality

= 1.0 =
* Initial Release 

== Overview of Features ==

* Quick and Painless REST API support for custom post types
* Lists all of your registered custom post types  
* Interface for testing a routes url