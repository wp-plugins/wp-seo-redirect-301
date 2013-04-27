<?php
/*
Plugin Name: SEO Redirect 301s
Plugin URI: http://wordpress.org/extend/plugins/wp-seo-redirect-301/
Description: Records urls and if a pages url changes, system redirects old url to the updated url.
Version: 1.7.1
Author: Tom Skroza
License: GPL2
*/

//call register settings function
add_action( 'admin_init', 'register_wp_seo_redirect_settings' );
function register_wp_seo_redirect_settings() {  

  $msg_content = "<div class='updated'><p>Sorry for the confusion but you must install and activate Tom M8te before you can use SEO Redirect 301s. Please go to Plugins/Add New and search/install the following plugin: Tom M8te </p></div>";
  if (!is_plugin_active("tom-m8te/tom-m8te.php")) {
    deactivate_plugins(__FILE__, true);
    echo($msg_content);
  }

}

add_action('admin_menu', 'register_seo_redirect_301_page');

function register_seo_redirect_301_page() {
   add_menu_page('SEO Redirect 301', 'SEO Redirect 301', 'manage_options', 'wp-seo-redirect-301/seo_redirect_list.php', '',  '', 180);
}

add_action( 'save_post', 'seo_redirect_save_current_slug' );
// Save history of slugs/permalinks for the saved page and child pages.
function seo_redirect_save_current_slug( $postid ) {
  $my_revision = tom_get_row("posts", "*", "post_type='revision' AND ID=".$postid);
  if ($my_revision != null) {
    $my_post = tom_get_row("posts", "*", "post_type IN ('page', 'post') AND ID=".$my_revision->post_parent);

    if (tom_get_row("slug_history", "*", "post_id='".$my_post->ID."' AND url='".get_permalink( $my_post->ID )."'") == null) {
      tom_insert_record("slug_history", array( 'post_id' => $my_post->ID, 'url' => get_permalink( $my_post->ID )));
    }

    $child_pages = get_posts( array('post_type' => 'page','post_parent' => $my_post->ID,'orderby' => 'menu_order'));
    foreach ($child_pages as $child_page) {
      if (tom_get_row("slug_history", "*", "post_id='".$child_page->ID."' AND url='".get_permalink( $child_page->ID )."'") == null) {
        tom_insert_record("slug_history", array( 'post_id' => $child_page->ID, 'url' => get_permalink( $child_page->ID )));
      }
    } 
  }
  
}

// GET the current url.
function seo_redirect_curl_page_url() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 $pageURL .= $_SERVER["SERVER_NAME"].preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]);
 return $pageURL;
}

add_action( 'template_redirect', 'seo_redirect_slt_theme_filter_404', 0 );  
// Check if page exists.
function seo_redirect_slt_theme_filter_404() {  
    
  global $wp_query, $post;
  // Get the name of the current template. 
  $template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );

  $acceptable_values = array("post", "page");

  // Check if page exists.
  if (($wp_query->post->ID == "" && $template_name == "") || !in_array($wp_query->post->post_type, $acceptable_values)) { 
    // Template is blank, which means page does not exist and is a 404. 
    $wp_query->is_404 = false;  
    $wp_query->is_archive = true;  
    $wp_query->is_post_type_archive = true;  
    $post = new stdClass();  
    $post->post_type = $wp_query->query['post_type']; 

    // Try to find record of a page with the current url.
    $row = tom_get_row("slug_history", "*", "post_id <> 0 AND url='".seo_redirect_curl_page_url()."/'");
    if ($row->post_id == "") {
      $row = tom_get_row("slug_history", "*", "post_id <> 0 AND url='".seo_redirect_curl_page_url()."'");
    }

    if ($row != null) {
      // Record found, find id of old url, now use id to find current slug/permalink.
      $post_row = tom_get_row("posts", "*", "ID=".$row->post_id);
      wp_redirect(get_permalink($row->post_id),301);exit;     
    } else {
      // Continue as 404, we can't find the page so do nothing.
    }

  }  
              
}  

function seo_redirect_301_activate() {
  global $wpdb;
  $table_name = $wpdb->prefix . "slug_history";
  $checktable = $wpdb->query("SHOW TABLES LIKE '$table_name'");
  if ($checktable == 0) {
    $sql = "CREATE TABLE $table_name (
    post_id mediumint(9) NOT NULL,
    url VARCHAR(255) DEFAULT '' NOT NULL,
    UNIQUE KEY post_id (post_id, url)
    );";
    $wpdb->query($sql); 
  }
}
register_activation_hook( __FILE__, 'seo_redirect_301_activate' );

?>