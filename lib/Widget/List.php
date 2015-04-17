<?php
/**
 * @wp.widget.name            service_areas_list
 * @wp.widget.label           Service Areas List
 */
class ServiceAreas_Widget_List extends Snap_Wordpress_Widget
{
  /**
   * @wp.widget.field.type    text
   * @wp.widget.field.label   Title
   */
  public $title;
  
  public function widget( $args, $instance )
  {
    echo Snap::inst('ServiceAreas_Shortcodes')->shortcode(array(
      
    ), null, 'service_areas');
  }
}
