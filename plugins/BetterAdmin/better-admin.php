<?php 
add_action( 'admin_enqueue_scripts', 'addAdminMenuScripts' );
function addAdminMenuScripts() {
    wp_register_script( 'menu-aim', get_stylesheet_directory_uri() . "/plugins/BetterAdmin/assets/js/jquery.menu-aim.js" );
    wp_enqueue_script( 'menu-aim' );
    
    wp_register_script( 'menu', get_stylesheet_directory_uri() . "/plugins/BetterAdmin/assets/js/betteradmin-menu.js", array('menu-aim') );
    wp_enqueue_script( 'menu' );

}



require_once dirname( __FILE__ ) . "/better-admin-menu.php";
require_once dirname( __FILE__ ) . "/better-toolbar.php";
require_once dirname( __FILE__ ) . "/better-dashboard.php";
?>