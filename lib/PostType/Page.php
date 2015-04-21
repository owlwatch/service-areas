<?php
/**
 * @wp.posttype.name                        service-area-page
 * @wp.posttype.single                      Service Area Page
 * @wp.posttype.plural                      Service Area Pages
 *
 * @wp.posttype.labels.all_items            Pages
 *
 * @wp.posttype.args.rewrite.with_front     false
 * @wp.posttype.args.rewrite.slug           service-area
 * @wp.posttype.args.public                 true
 * @wp.posttype.args.show_ui                false
 * @-wp.posttype.args.show_in_menu           service-areas-settings
 * @wp.posttype.args.has_archive            false
 * @wp.posttype.args.publicly_queryable     true
 *
 * @wp.posttype.supports.slug               true
 */
class ServiceAreas_PostType_Page extends Snap_Wordpress_PostType
{
  
  protected $pages;
  
  protected $flush_rewrite_rules = false;
  
  public function __construct()
  {
    parent::__construct();
  }
  
  protected function filterArgs( $args )
  {
    $slug = get_field('service_areas_slug','option');
    $args['rewrite']['slug'] = $slug ? $slug : 'service-area';
    return $args;
  }
  
  /**
   * @wp.action           update_option_service_areas_slug
   */
  public function slug_change( $value, $old, $new )
  {
    flush_rewrite_rules();
  }
  
  
  /**
   * @wp.filter           acf/update_value/name=service_areas
   */
  public function save_areas( $value )
  {
    $templates = get_posts(array(
      'posts_per_page'  => -1,
      'post_type'       => 'service-area-tmpl'
    ));
    
    $template_ids = array();
    
    foreach( $templates as $template ){
      Snap::inst('ServiceAreas_PostType_Template')->update( $template, $value );
      $template_ids[] = $template->ID;
    }
    
    $this->cleanup_orphans( $template_ids, $value );
    
    return $value;
  }
  
  public function cleanup_orphans( $template_ids=null, $areas=null )
  {
    
    global $wpdb;
    
    if( !$template_ids ){
      $templates = get_posts(array(
        'posts_per_page'  => -1,
        'post_type'       => 'service-area-tmpl'
      ));
      
      $template_ids = array();
      
      foreach( $templates as $template ){
        $template_ids[] = $template->ID;
      }
    }
    
    $areas = ServiceAreas::get_areas( $areas );
    
    $orphans = get_posts(array(
      'post_type'     => 'service-area-page',
      'posts_per_page'=> -1,
      'meta_query'    => array(
        'relation'      => 'OR',
        array(
          'key'           => '_service_area_tmpl',
          'value'         => $template_ids,
          'compare'       => 'NOT IN'
        ),
        array(
          'key'           => '_service_area',
          'value'         => $areas,
          'compare'       => 'NOT IN'
        )
      )
    ));
    
    foreach( $orphans as $orphan ){
      wp_delete_post( $orphan->ID, true);
    }
  }
  
  /**
   * @wp.action               admin_bar_menu
   * @wp.priority             90
   */
  public function change_edit_link( $admin_bar )
  {
    if( is_admin() || !is_singular( $this->name ) ) return;
    
    $admin_bar->remove_node('edit');
    $admin_bar->add_node(array(
      'id'            => 'edit',
      'title'         => 'Edit Template',
      'href'          => get_edit_post_link( get_post_meta(get_the_ID(),'_service_area_tmpl', true))
    ));
  }
  
}
