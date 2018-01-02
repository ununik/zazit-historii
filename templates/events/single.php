<?php
/**
 * The events single template
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */
get_header();
?>
    <h3><?php the_title() ?> <span class="event_title_date">(<?php echo $frontend->get_date_from_timestamps((int) get_post_meta( get_the_ID(), '_event_date_from', true ), (int) get_post_meta( get_the_ID(), '_event_date_to', true )); ?>)</span></h3>
<?php
get_footer();