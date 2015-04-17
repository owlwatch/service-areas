<?php

class ServiceAreas extends Snap_Wordpress_Plugin
{
  
  protected $dev = true;
  
  protected $plugin_dir;
  protected $plugin_url;
  protected $assets_dir;
  protected $assets_url;
  
  public function __construct()
  {
    parent::__construct();
    
    $this->plugin_dir = dirname( dirname(__FILE__) );
    $this->plugin_url = plugins_url('', dirname(__FILE__));
    $this->assets_dir = $this->plugin_dir.'/assets';
    $this->assets_url = $this->plugin_url.'/assets';
    $this->tmpl_dir = $this->plugin_dir.'/tmpl';
    
    $this->add_settings_page();
    $this->init_post_types();
    
    Snap::inst('ServiceAreas_Shortcodes');
  }
  
  public function tmpl( $tmpl )
  {
    return $this->tmpl_dir."/{$tmpl}.php";
  }
  
  /**
   * This is for development purposes only
   *
   * @wp.filter             acf/settings/save_json
   */
  public function acf_json_save_dir( $path )
  {
    if( !$this->dev ) return $path;
    return $this->plugin_dir.'/data/acf-json/';
  }
  
  protected function add_settings_page()
  {
    acf_add_options_page(array(
      'title'             => 'Service Areas Settings',
      'icon_url'          => 'dashicons-location-alt',
      'menu_slug'         => 'service-areas',
      'position'          => 21,
      'menu_title'        => 'Service Areas'
    ));
    
    acf_add_options_sub_page(array(
      'title'             => 'Service Areas Settings',
      'menu_slug'         => 'service-areas-settings',
      'parent_slug'       => 'service-areas',
      'menu_title'        => 'Settings'
    ));
  }
  
  /**
   * @wp.filter             acf/location/rule_values/options_page
   * @wp.priority           20
   */
  public function less_generic_rules_name_please( $choices )
  {
    if( @$choices['service-areas-settings'] ){
      $choices['service-areas-settings'] = 'Service Areas Settings';
    }
    return $choices;
  }
  
  protected function init_post_types()
  {
    Snap::inst('ServiceAreas_PostType_Page');
    Snap::inst('ServiceAreas_PostType_Template');
  }
  
  /**
   * @wp.action             widgets_init
   */
  public function register_widgets()
  {
    register_widget('ServiceAreas_Widget_List');
  }
  
  public static function get_areas( $value=false )
  {
    if( !$value ) $value = get_field('service_areas','option');
    return array_filter( array_map('trim', explode("\n", $value)) );
  }
}
