=== Plugin Name ===
Contributors: MMDeveloper
Tags: seo, redirect, 301, slug
Requires at least: 3.0.1
Tested up to: 3.4.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

WP-SEO-Redirect-301 is a WordPress plugin that remembers your old urls and redirects users to the updated url, to prevent 404s when urls change

Installation:

1) Install WordPress 3.4.2 or higher

2) Download the following file:
http://downloads.wordpress.org/plugin/wp-seo-redirect-301.1.1.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


Thats it, you don't need to worry, update your page urls and this plugin will redirect your users to the updated url.


== Changelog ==


= 1.1 =

* Fixed up updating children urls. So now if parent slug name changes, the child slugs are updated as well. For example, lets say you have a page called http://localhost/cars with a child page http://localhost/cars/holden, if you change the slug name to car and you navigated to http://localhost/cars it will redirect you to http://localhost/car. Similarly if you navigated to http://localhost/cars/holden it will redirect you to http://localhost/car/holden.

= 1.0 =

* Initial Checkin
