<?php
/*
Plugin Name: SEO Redirect 301s
Plugin URI: http://wordpress.org/extend/plugins/wp-seo-redirect-301/
Description: Records urls and if a pages url changes, system redirects old url to the updated url.
Version: 2.0.0
Author: Tom Skroza
License: GPL2
*/

if (!class_exists("TomM8")) {
  require_once("lib/tom-m8te.php");
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

add_action('admin_menu', 'register_seo_redirect_301_page');

function register_seo_redirect_301_page() {
   add_menu_page('SEO Redirect 301', 'SEO Redirect 301', 'manage_options', 'wp-seo-redirect-301/seo_redirect_list.php', '',  '', 180);
}

add_action( 'admin_init', 'register_seo_redirect_301_install_dependency_settings' );
function register_seo_redirect_301_install_dependency_settings() {

  // Check to see if file exists.
  if (!file_exists(ABSPATH."/301-sitemap.xml")) {

    // File does not exist, so create file.
    seo_redirect_301_do_this_daily(); 
  }
  
  if (isset($_GET["seo_redirect_301_install_dependency"])) {
    if (wp_verify_nonce($_REQUEST['_wpnonce'], "activate-seo-redirect-301-dependencies")) {
      switch ($_GET["seo_redirect_301_install_dependency"]) { 
        case 'tom-m8te':  
          activate_plugin('tom-m8te/tom-m8te.php', 'plugins.php?error=false&plugin=tom-m8te.php');
          wp_redirect(get_option("siteurl")."/wp-admin/admin.php?page=wp-seo-redirect-301/seo_redirect_list.php");
          exit();
          break;   
        default:
          throw new Exception("Sorry unable to install plugin.");
          break;
      }
    } else {
      die("Security Check Failed.");
    }
  }
}

add_action( 'save_post', 'seo_redirect_save_current_slug' );
// Save history of slugs/permalinks for the saved page and child pages.
function seo_redirect_save_current_slug( $postid ) {
  
  $my_revision = TomM8::get_row("posts", "*", "post_type='revision' AND ID=".$postid);
  if ($my_revision != null) {
    $my_post = TomM8::get_row("posts", "*", "post_type IN ('page', 'post') AND ID=".$my_revision->post_parent);

    if (TomM8::get_row("slug_history", "*", "post_id='".$my_post->ID."' AND url='".get_permalink( $my_post->ID )."'") == null) {
      TomM8::insert_record("slug_history", array( 'post_id' => $my_post->ID, 'url' => get_permalink( $my_post->ID )));
    }

    $child_pages = get_posts( array('post_type' => 'page','post_parent' => $my_post->ID,'orderby' => 'menu_order'));
    foreach ($child_pages as $child_page) {
      if (TomM8::get_row("slug_history", "*", "post_id='".$child_page->ID."' AND url='".get_permalink( $child_page->ID )."'") == null) {
        TomM8::insert_record("slug_history", array( 'post_id' => $child_page->ID, 'url' => get_permalink( $child_page->ID )));
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

add_action('template_redirect', 'seo_redirect_slt_theme_filter_404');  
// Check if page exists.
function seo_redirect_slt_theme_filter_404() {  
  
  global $wp_query, $post;
   // Get the name of the current template. 
   $template_name = get_post_meta( get_the_id(), '_wp_page_template', true );

   $acceptable_values = array("post", "page");

   // Check if page exists.
   if ((get_the_id() == "" && $template_name == "") || !in_array($wp_query->post->post_type, $acceptable_values)) { 

      // Template is blank, which means page does not exist and is a 404. 

      // Try to find record of a page with the current url.
      $row = TomM8::get_row("slug_history", "*", "post_id <> 0 AND url='".seo_redirect_curl_page_url()."/'");
      if ($row->post_id == "") {
        $row = TomM8::get_row("slug_history", "*", "post_id <> 0 AND url='".seo_redirect_curl_page_url()."'");
      }

      if ($row != null) {
        // Record found, find id of old url, now use id to find current slug/permalink.
        $post_row = TomM8::get_row("posts", "*", "ID=".$row->post_id);
        wp_redirect(get_permalink($row->post_id),301);exit;
      } else {
        // Continue as 404, we can't find the page so do nothing.
      }
   }  
          
}  

if (isset($_GET["post"]) && $_GET["post"] != "") {
  add_action( 'add_meta_boxes', 'seo_redirect_admin_page_widget_box' );
}
function seo_redirect_admin_page_widget_box() {
  
  if (isset($_GET["delete_url"]) && isset($_GET["post"])) {
    $record = TomM8::get_row("slug_history", array("post_id", "url"), "post_id=".$_GET["post"]."&url='".$_GET["delete_url"]."'");
    // Check if slug history record exists
    if ($record) {
      // slug history record does exist so attempt to delete it.
      TomM8::delete_record("slug_history", "post_id=".$_GET["post"]." AND url='".$_GET["delete_url"]."'");
    }
  }
  
  $screens = array( 'post', 'page' );
  foreach ($screens as $screen) {
      add_meta_box(
          'seo_redirect_admin_widget_id',
          __( 'SEO Redirect 301s', 'seo_redirect_url' ),
          'seo_redirect_inner_custom_box',
          $screen
      );
  }
}

/* Prints the box content */
function seo_redirect_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'seo_redirect_noncename' );  
  
  $my_redirects = TomM8::get_results("slug_history", "*", "post_id=".$post->ID);
  ?>
  <p>
    <label style="display: block;" for="seo_redirect_url">Please type in a custom url that you want to use to redirect to this page:</label>
    <br/>
    <span style="background: #cac9c9; padding: 10px;">
      <?php echo(get_option("siteurl")); ?>/<input type="text" name="seo_redirect_url" id="seo_redirect_url" />
    </span>
  </p>
  <h4><span>These URLs redirect to this page</span></h4>
  <table class="data">
    <tbody> 
      <?php 
        $record_count = 0;
        foreach($my_redirects as $redirect) { ?>
        <?php if ((get_permalink($redirect->post_id) != "") && (preg_replace("/\/$/", "", $redirect->url) != preg_replace("/\/$/", "", get_permalink($redirect->post_id)))) { 
          $record_count++;
          ?>
          <tr>
            <td><a target="_blank" href="<?php echo($redirect->url); ?>"><?php echo($redirect->url); ?></a></td>
            <td><a class="delete" href="<?php echo(get_option("siteurl")); ?>/wp-admin/post.php?post=<?php echo($redirect->post_id); ?>&action=edit&delete_url=<?php echo($redirect->url); ?>">Delete</a></td>
          </tr>
        <?php } ?>
      <?php } ?>          
    </tbody>
    <?php if ($record_count == 0) { ?>
      <tfoot>
        <tr>
          <td colspan="4">You haven't changed the page/post slug names or created a custom url yet.</td>
        </tr>
      </tfoot>  
    <?php } ?>
  </table>
  <?php

}

/* Do something with the data entered */
add_action( 'save_post', 'seo_redirect_save_postdata' );
/* When the post is saved, saves our custom data */
function seo_redirect_save_postdata( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if ( 'page' == $_REQUEST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['seo_redirect_noncename'] ) || ! wp_verify_nonce( $_POST['seo_redirect_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $redirect_url = get_option("siteurl")."/".sanitize_text_field( $_POST['seo_redirect_url'] );

  if ($_POST['seo_redirect_url'] != "") {
    TomM8::insert_record("slug_history", array("post_id" => $post_ID, "url" => $redirect_url));
  }

}


add_action( 'wp', 'seo_redirect_301_setup_schedule' );
/**
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
function seo_redirect_301_setup_schedule() {
  if ( ! wp_next_scheduled( 'seo_redirect_301_daily_event' ) ) {
    wp_schedule_event( time(), 'daily', 'seo_redirect_301_daily_event');
  }
}


add_action( 'seo_redirect_301_daily_event', 'seo_redirect_301_do_this_daily' );
/**
 * On the scheduled action hook, run a function.
 */
function seo_redirect_301_do_this_daily() {
  
    $my_redirects = TomM8::get_results("slug_history", "*", "");
    $content = "<?xml version='1.0' encoding='UTF-8'?><urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'>";
      foreach ($my_redirects as $redirect) {
        $the_url = preg_replace("/\?(.+)*/", "", $redirect->url);
        if ($the_url != "" && $the_url != get_option("siteurl") && $the_url != get_option("siteurl")."/") {
          $content .= 
          "<url> 
            <loc>".$the_url."</loc>
            <lastmod>".gmdate( 'Y-m-d')."T".gmdate( 'H:i')."+00:00</lastmod> 
            <changefreq>daily</changefreq> 
            <priority>0.6</priority> 
          </url>";
        }
      }
    $content .= "</urlset>";
    TomM8::write_to_file($content, ABSPATH."/301-sitemap.xml");

}


?>