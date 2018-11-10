<?php 

add_action( 'admin_bar_menu', 'modify_admin_bar' );

function modify_admin_bar( $wp_admin_bar ){
  // do something with $wp_admin_bar;
    // write_log($wp_admin_bar->get_nodes());
}

?>