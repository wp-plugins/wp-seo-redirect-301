<?php
/*
Plugin Name: SEO Redirect 301s
Plugin URI: http://wordpress.org/extend/plugins/wp-seo-redirect-301/
Description: Records urls and if a pages url changes, system redirects old url to the updated url.
Version: 1.6.2
Author: Tom Skroza
License: GPL2
*/

//call register settings function
add_action( 'admin_init', 'register_wp_seo_redirect_settings' );
function register_wp_seo_redirect_settings() {  
  @check_wp_seo_redirect_dependencies_are_active(
    "Seo Redirect 301s", 
    array(
      "Tom M8te" => array("plugin"=>"tom-m8te/tom-m8te.php", "url" => "http://downloads.wordpress.org/plugin/tom-m8te.zip", "version" => "1.2"))
  );
}

add_action('admin_menu', 'register_seo_redirect_301_page');

function register_seo_redirect_301_page() {
   add_menu_page('SEO Redirect 301', 'SEO Redirect 301', 'manage_options', 'wp-seo-redirect-301/seo_redirect_list.php', '',  '', 180);
}

add_action( 'save_post', 'save_current_slug' );
function save_current_slug( $postid ) {
  global $wpdb;
  $table_name = $wpdb->prefix . "slug_history";  
  $post_table = $wpdb->prefix . "posts";
  $my_revision = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $post_table WHERE post_type='revision' AND id= %d", $postid) );
  $my_post = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $post_table WHERE post_type IN ('page', 'post') AND id=%d", $my_revision->post_parent) );
  $rows_affected = $wpdb->insert( $table_name, array( 'post_id' => $my_post->ID, 'url' => get_permalink( $my_post->ID )) );
  $child_pages = get_posts( array('post_type' => 'page','post_parent' => $my_post->ID,'orderby' => 'menu_order'));
  foreach ($child_pages as $child_page) {
    $rows_affected = $wpdb->insert( $table_name, array( 'post_id' => $child_page->ID, 'url' => get_permalink( $child_page->ID )) );
  }
}


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 $pageURL .= $_SERVER["SERVER_NAME"].preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]);
 return $pageURL;
}

add_action( 'template_redirect', 'slt_theme_filter_404', 0 );  
function slt_theme_filter_404() {  
    
    global $wpdb, $wp_query, $post;
    // Get the name of the current template. 
    $template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );

    $acceptable_values = array("post", "page");

    if (($wp_query->post->ID == "" && $template_name == "") || !in_array($wp_query->post->post_type, $acceptable_values)) { 
       // Template is blank, which means page does not exist and is a 404. 
        $wp_query->is_404 = false;  
        $wp_query->is_archive = true;  
        $wp_query->is_post_type_archive = true;  
        $post = new stdClass();  
        $post->post_type = $wp_query->query['post_type']; 

       $table_name = $wpdb->prefix . "slug_history";
       $sql = "SELECT * FROM $table_name where post_id <> 0 AND url='".curPageURL()."/'";
       $row = $wpdb->get_row($sql);

       if ($row->post_id == "") {
        $sql = "SELECT * FROM $table_name where post_id <> 0 AND url='".curPageURL()."'";
        $row = $wpdb->get_row($sql);
       }

       $post_table = $wpdb->prefix."posts";
       $sql = "SELECT * FROM $post_table where ID = ".$row->post_id;
       $post_row = $wpdb->get_row($sql);

       if ($post_row->post_type == "post") {
         wp_redirect(get_option('siteurl')."/?p=".$row->post_id, 301);exit;
       } else if ($post_row->post_type == "page") {
         wp_redirect(get_option('siteurl')."/?page_id=".$row->post_id, 301);exit;
       }
    }  
    
                   
}  

function seo_redirect_301_activate() {
   global $wpdb;
   $table_name = $wpdb->prefix . "slug_history";
   $sql = "CREATE TABLE $table_name (
post_id mediumint(9) NOT NULL,
url VARCHAR(255) DEFAULT '' NOT NULL,
UNIQUE KEY post_id (post_id, url)
);";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

}
register_activation_hook( __FILE__, 'seo_redirect_301_activate' );


function check_wp_seo_redirect_dependencies_are_active($plugin_name, $dependencies) {
  $msg_content = "<div class='updated'><p>Sorry for the confusion but you must install and activate ";
  $plugins_array = array();
  $upgrades_array = array();
  define('PLUGINPATH', ABSPATH.'wp-content/plugins');
  foreach ($dependencies as $key => $value) {
    $plugin = get_plugin_data(PLUGINPATH."/".$value["plugin"],true,true);
    $url = $value["url"];
    if (!is_plugin_active($value["plugin"])) {
      array_push($plugins_array, $key);
    } else {
      if (isset($value["version"]) && str_replace(".", "", $plugin["Version"]) < str_replace(".", "", $value["version"])) {
        array_push($upgrades_array, $key);
      }
    }
  }
  $msg_content .= implode(", ", $plugins_array) . " before you can use $plugin_name. Please go to Plugins/Add New and search/install the following plugin(s): ";
  $download_plugins_array = array();
  foreach ($dependencies as $key => $value) {
    if (!is_plugin_active($value["plugin"])) {
      $url = $value["url"];
      array_push($download_plugins_array, $key);
    }
  }
  $msg_content .= implode(", ", $download_plugins_array)."</p></div>";
  if (count($plugins_array) > 0) {
    deactivate_plugins( __FILE__, true);
    echo($msg_content);
  } 

  if (count($upgrades_array) > 0) {
    deactivate_plugins( __FILE__,true);
    echo "<div class='updated'><p>$plugin_name requires the following plugins to be updated: ".implode(", ", $upgrades_array).".</p></div>";
  }
}

?>