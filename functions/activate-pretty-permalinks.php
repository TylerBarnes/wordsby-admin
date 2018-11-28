<?php 
function activate_pretty_permalinks() {
    global $wp_rewrite; 

    //Write the rule
    $wp_rewrite->set_permalink_structure('/%postname%/'); 

    //Set the option
    update_option( "rewrite_rules", FALSE ); 

    //Flush the rules and tell it to write htaccess
    $wp_rewrite->flush_rules( true );
}

add_action("after_switch_theme", "activate_pretty_permalinks");
?>