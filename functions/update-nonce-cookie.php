<?php 
function my_update_cookie( $logged_in_cookie ){
            $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
        }
        add_action( 'set_logged_in_cookie', 'my_update_cookie' );
?>