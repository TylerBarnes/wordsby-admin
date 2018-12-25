<?php 

function getCurrentUser() {
    $current_user = wp_get_current_user()->data;

    return [
        'name' => $current_user->user_nicename,
        'email' => $current_user->user_email
    ];
}

?>