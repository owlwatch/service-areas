<?php
/*
Plugin Name: Service Areas
Plugin URI: http://www.seedprod.com
Description: Improve SEO for local town / region names
Version:  1.0.0
Author: Owl Watch Consulting
Author URI: http://www.owlwatch.com
TextDomain: service-areas
License: MIT
*/

add_action('plugins_loaded', function(){
  
  $all_set = true;
  
  if( !class_exists('Snap') ){
    
    $all_set = false;
    
    add_action('admin_notices', function(){
      ?>
    <div class="info">
      <p>Service Areas requires the <a href="https://github.com/fabrizim/Snap">Snap plugin</a>.</p>
    </div>
      <?php
    });
  }
  
  if( !function_exists('acf_get_setting') ||
      !version_compare(acf_get_setting('version'), '5.0.9', '>=') ){
    
    $all_set = false;
    
    add_action('admin_notices', function(){
      ?>
    <div class="info">
      <p>Service Areas requires the <a href="http://www.advancedcustomfields.com/pro/">Advanced Custom Fields Pro</a>.</p>
    </div>
      <?php
    });
  }
  
  if( !$all_set ) return;
  
  // lets create the plugin
  Snap_Loader::register('ServiceAreas', dirname(__FILE__).'/lib');
  Snap::inst('ServiceAreas');
});