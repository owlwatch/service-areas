<ul class="service-areas">
<?php
while( $query->have_posts() ){
  $query->the_post();
  $title = get_post_meta( get_the_ID(), '_service_area', true );
  ?>
  <li><a href="<?= get_permalink(); ?>" title="<?= esc_attr( get_the_title() ) ?>"><?= $title ?></a></li>
  <?php
}
wp_reset_postdata();
?>
</ul>