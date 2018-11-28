<?php 
// Discourage search engines from indexing the site
add_filter('robots_txt', 'wpse_248124_robots_txt', 10,  2);

function wpse_248124_robots_txt($output, $public) {

  return "
    User-agent: *
    Disallow: /
  ";
}
?>
