<?php
/**
 * @wp.posttype.name                        service-area-tmpl
 * @wp.posttype.single                      Service Area Template
 * @wp.posttype.plural                      Service Area Templates
 *
 * @wp.posttype.labels.all_items            Templates
 *
 * @wp.posttype.args.show_ui                true
 * @wp.posttype.args.show_in_menu           service-areas-settings
 * @wp.posttype.args.rewrite.slug           service-area-template
 * @wp.posttype.args.has_archive            false
 * @wp.posttype.args.rewrite.with_front     false
 *
 */
class ServiceAreas_PostType_Template extends Snap_Wordpress_PostType
{
  /**
   * @wp.action           wp_insert_post
   * @wp.priority         2000
   */
  public function update_posts( $post_id )
  {
    
    if( get_post_type( $post_id ) !== $this->name ) return;
    if( get_post_status( $post_id) !== 'publish' ) return;
    $this->update( get_post( $post_id, ARRAY_A ) );
  }
  
  /**
   * @wp.action           delete_post
   */
  public function cleanup( $post_id )
  {
    if( get_post_type( $post_id ) !== $this->name ) return;
    Snap::inst('ServiceAreas_PostType_Page')->cleanup_orphans();
  }
  
  public function update( $tmpl, $names=false, $insert_only = false )
  {
    if( !$names ) $names = get_field('service_areas','option');
    
    $names = $areas = ServiceAreas::get_areas( $names );
    
    if( !is_array( $tmpl ) ) $tmpl = (array) $tmpl;
    $tmpl_id = $tmpl['ID'];
    
    unset( $tmpl['ID'] );
    unset( $tmpl['guid'] );
    unset( $tmpl['post_date'] );
    unset( $tmpl['post_date_gmt'] );
    
    $tmpl['post_type'] = 'service-area-page';
    $tmpl['post_status'] = 'publish';
    
    $meta = get_post_meta( $tmpl_id );
    
    $pages = get_posts(array(
      'post_type'         => 'service-area-page',
      'meta_key'          => '_service_area_tmpl',
      'meta_value'        => $tmpl_id,
      'orderby'           => 'date',
      'order'             => 'ASC',
      'posts_per_page'    => -1
    ));
    
    $existing = array();
    
    // lets go through and update all these
    foreach( $pages as $page ){
      $name = get_post_meta( $page->ID, '_service_area', true );
      if( !in_array($name, $names) || isset( $existing[$name] ) ){
        wp_delete_post( $page->ID, true );
        continue;
      }
      $existing[$name] = $page;
    }
    
    $updated = 0;
    
    foreach( $names as $name ){
      
      if( $insert_only && $existing[$name] ) continue;
      
      if( $existing[$name] ){
        $id = $tmpl['ID'] = $existing[$name]->ID;
        if( $existing[$name]->post_modified < $tmpl['post_modified'] ){
          wp_update_post( $this->replace($tmpl, $name) );
          $updated++;
        }
        else {
          continue;
        }
      }
      else {
        unset( $tmpl['ID'] );
        $id = wp_insert_post( $this->replace($tmpl, $name) );
      }
      
      update_post_meta( $id, '_service_area', $name);
      update_post_meta( $id, '_service_area_tmpl', $tmpl_id );
      
      foreach( $this->replace($meta, $name) as $key => $values ){
        delete_post_meta( $id, $key );
        if( count( $values ) > 1 ) foreach( $values as $value ){
          add_post_meta( $id, $key, $value );
        }
        else {
          update_post_meta( $id, $key, $values[0] );
        }
      }
    }
    
    // lets find the existing ones that shouldn't be there anymore...
    $diff = array_diff(array_keys($existing), $names);
    
    foreach( $diff as $name ){
      wp_delete_post( $existing[$name]->ID, true );
    }
  }
  
  
  public function replace( $value, $name )
  {
    if( is_array( $value ) ) foreach( $value as $key => $val ){
      $value[$key] = $this->replace( $val, $name );
    }
    else if( is_object( $value ) ) foreach( (array)$value as $key => $val ){
      $value->$key = $this->replace( $val, $name );
    }
    else if( is_string( $value ) ){
      $value = str_replace(array('service_area', 'SERVICE_AREA'), $name, $value);
    }
    return $value;
  }
}
