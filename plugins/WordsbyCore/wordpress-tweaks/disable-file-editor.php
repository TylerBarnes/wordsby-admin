<?php 
function disable_mytheme_action() {
    define('DISALLOW_FILE_EDIT', TRUE);
  }
  add_action('init','disable_mytheme_action');
?>