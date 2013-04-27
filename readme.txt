=== Plugin Name ===
Contributors: MMDeveloper
Tags: seo, redirect, 301, slug
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 1.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

WP-SEO-Redirect-301 is a WordPress plugin that remembers your old urls and redirects users to the updated url, to prevent 404s when urls change

Installation:

1) Install WordPress 3.5.1 or higher

2) Download the latest from:

http://wordpress.org/extend/plugins/tom-m8te 

http://wordpress.org/extend/plugins/wp-seo-redirect-301

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


Thats it, you don't need to worry, update your page urls and this plugin will redirect your users to the updated url.

If you click on "SEO Redirect 301" menu link, you will see a list of old urls pointing to the new ones. Its here where you can delete ones you don't want anymore.

== Changelog ==

= 1.7.1 =

* Added more Tom M8te functions to make it easier to manage.

= 1.6.4 =

* After looking at logs, I found duplicate entry errors, which didn't create them, just warned about them. This patch removes the chance of the warning.

= 1.6.3 =

* Small bug fix, happens rarely. If the database is corrupt and post id didn't exist it produced an error. This patch fixes it.

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


== Upgrade notice ==

= 1.7.1 =

* Added more Tom M8te functions to make it easier to manage.

= 1.6.4 =

* After looking at logs, I found duplicate entry errors, which didn't create them, just warned about them. This patch removes the chance of the warning.

= 1.6.3 =

* Small bug fix, happens rarely. If the database is corrupt and post id didn't exist it produced an error. This patch fixes it.

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
