<?php 
/**
 * Pass in a taxonomy value that is supported by WP's `get_taxonomy`
 * and you will get back the url to the archive view.
 * @param $taxonomy string|int
 * @return string
 */
function get_taxonomy_archive_link( $taxonomy ) {
    $tax = get_taxonomy( $taxonomy ) ;
    return '/' . $tax->rewrite['slug'] . '/';
  }
?>