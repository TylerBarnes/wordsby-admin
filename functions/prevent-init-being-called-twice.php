<?php 
// prevent init from being called twice.
remove_filter('wp_head','adjacent_posts_rel_link_wp_head',10);
?>