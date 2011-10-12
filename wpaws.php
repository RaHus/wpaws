<?php
/*
Plugin Name: Squat.gr Statistics
Plugin URI: http://github.com/RaHus/wpaws
Description: Statistika gia to blog sas
Version: 0.1
Author: RaHus
Author URI: http://e-maladapted.squat.gr

Installation: copy wpaws.php to the wp-content/plugins directory of
your WordPress installation, then activate the plugin. Plugin will create a table that holds 
the static webpage with statistics info for the domain. Data is computed periodically by wpaws.py
using cron jobs
*/


register_activation_hook(__FILE__,'wpaws_install');
add_action('admin_menu', 'wpaws_admin_menu');

function wpaws_admin_menu() {
  add_menu_page('WpAwstats', 'Squat.gr Statistics', 'activate_plugins', 'wpawsplugin', 'wpaws_plugin_page');

}

function wpaws_plugin_page() {

  if (!current_user_can('activate_plugins'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  global $wpdb;
  $wpdb->show_errors();
  echo '<div class="wrap">';
  echo '<p>Statistics data is updated every day at 6:00</p>';
  echo '</div>';
  $table_name= "wp_wpaws";
  $row = $wpdb->get_row("select * from $table_name where rblogid=$wpdb->blogid");
  $wpdb->print_error();
  echo $row->report;
}

//Database Init
function wpaws_install () {
   global $wpdb;
   $table_name = "wp_wpaws";
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
       $sql = "CREATE TABLE " . $table_name . " (
    	   rblogid mediumint(9) NOT NULL,
	   time datetime NOT NULL,
	   report text NOT NULL,
           PRIMARY KEY (rblogid)
       );";

       $wpdb->query($sql);
   }
   if($wpdb->get_row("select * from $table_name where rblogid=$wpdb->blogid")==NULL ) { 
       $welcome_text = "Congratulations, you just completed the installation!, Now you'll have to wait for a maximum of 24 hours so that your domain statistics get updated";
       $rows_affected = $wpdb->insert( $table_name, array( 'rblogid' => $wpdb->blogid, 'time' => current_time('mysql'), 'report' => $welcome_text ) );
   }
}


define("SP_REGEXP", "/\[tag-mnemonic ([[:print:]]+) ([[:print:]]+)\]/");
define("SP_TARGET", "<ul><li>###Param1###</li><li>###Param2###</li></ul>" );

function sp_plugin_callback($match)
{
	$output = SP_TARGET;
	$output = str_replace("###Param1###", $match[1], $output);
	$output = str_replace("###Param2###", $match[2], $output);
	return ($output);
}

function sp_plugin($content)
{
	return (preg_replace_callback(SP_REGEXP, 'sp_plugin_callback', $content));
}

add_filter('the_content', 'sp_plugin');
add_filter('comment_text', 'sp_plugin');

?>
