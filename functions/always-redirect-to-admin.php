<?php 
function always_redirect_to_admin()
{
    wp_redirect(get_admin_url());
    die;
}
add_action( 'template_redirect', 'always_redirect_to_admin' );
?>