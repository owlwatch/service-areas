<?php

class ServiceAreas_Shortcodes extends Snap_Wordpress_Shortcodes
{
  /**
   * @wp.shortcode
   */
  public function service_areas( $atts=array(), $content='' )
  {
    extract( shortcode_atts(array(
      'templates'           => '',
      'style'               => 'ul'
    ), $atts, 'service_areas') );
    
    $cache_key = $this->get_cache_key( $atts );
    
    if( ($html = get_transient($cache_key)) ){
      echo $html;
      return;
    }
    
    $query_args = array(
      'post_type'         => 'service-area-page',
      'posts_per_page'    => -1,
      'meta_key'          => '_service_area_tmpl',
      'meta_compare'      => 'EXISTS',
      'orderby'           => array('meta_value' => 'ASC', 'title'=>'ASC'),
    );
    
    if( $templates ){
      $templates = array_filter( explode( ',', $templates ) );
      $templates = array_map( function($i){
        return intval( $i, 10 );
      }, $templates);
      $query_args['meta_value'] = $templates;
      $query_args['meta_compare'] = 'IN';
    }
    
    $query = new WP_Query( $query_args );
    
    ob_start();
    include Snap::inst( 'ServiceAreas' )->tmpl( $style );
    $html = ob_get_clean();
    
    // save this...
    set_transient($cache_key, $html);
    
    echo $html;
  }
  
  protected function get_cache_key( $args )
  {
    return 'sa-'.md5( serialize( $args ) );
  }
}
