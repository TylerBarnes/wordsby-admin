<?php 

// function copy_preview_uploader_to_root() {
//     $wproot = get_home_path();
//     $public_key = GATSBYPRESS_PUBLIC_KEY;
//     $uploader_path_to = $wproot . $public_key . ".php";
//     $uploader_path_from = get_template_directory() . "/webhooks/receivePreviews.php";

//     if (!file_exists($uploader_path_to)) {
//         if (!copy($uploader_path_from, $uploader_path_to)) {
//             write_log('Gatsbypress uploader not copied to root.');
//         } 
//     }
// }

// add_action('admin_init', 'copy_preview_uploader_to_root');

?>