<?php 
function always_redirect_to_admin()
{
    // global $prevent_redirect;
    // if (!$prevent_redirect) {
        // wp_redirect(get_admin_url());
        // die;
    // } else {
    //     return;
    // }
}
add_action( 'template_redirect', 'always_redirect_to_admin' );
?>