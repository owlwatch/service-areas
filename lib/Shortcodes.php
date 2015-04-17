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
    
    include Snap::inst( 'ServiceAreas' )->tmpl( $style );
  }
}
