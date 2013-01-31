=== Plugin Name ===
Contributors: MMDeveloper
Tags: seo, redirect, 301, slug
Requires at least: 3.0.1
Tested up to: 3.4.2
Stable tag: 1.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

WP-SEO-Redirect-301 is a WordPress plugin that remembers your old urls and redirects users to the updated url, to prevent 404s when urls change

Installation:

1) Install WordPress 3.4.2 or higher

2) Download the following files:
http://downloads.wordpress.org/plugin/tom-m8te.1.2.zip
http://downloads.wordpress.org/plugin/wp-seo-redirect-301.1.6.2.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


Thats it, you don't need to worry, update your page urls and this plugin will redirect your users to the updated url.

If you click on "SEO Redirect 301" menu link, you will see a list of old urls pointing to the new ones. Its here where you can delete ones you don't want anymore.

== Changelog ==

= 1.6.2 =

* Fixed bug with redirect. Sometimes the system redirects to an attachment, not a page or post, which makes no sense. Anyways I put a guard on it so if it tries to redirect to the attachment, it redirects to the page instead.

= 1.6.1 =

* Fixed bug with https urls.

= 1.6 =

* Dependent on plugin Tom M8te to make some features easier to code. There is no new features since 1.5.

= 1.5 =

* Fixed redirect posts to correct urls. Fixed delete redirects which did delete but didn't refresh changes at correct time.

= 1.4 =

* Fixed some typos, whoops.

= 1.3 =

* Better description on table when you haven't yet changed your urls.

= 1.2 =

* UI to show user which old urls are pointing to the new updated urls.

= 1.1 =

* Fixed up updating children urls. So now if parent slug name changes, the child slugs are updated as well. For example, lets say you have a page called http://localhost/cars with a child page http://localhost/cars/holden, if you change the slug name to car and you navigated to http://localhost/cars it will redirect you to http://localhost/car. Similarly if you navigated to http://localhost/cars/holden it will redirect you to http://localhost/car/holden.

= 1.0 =

* Initial Checkin
