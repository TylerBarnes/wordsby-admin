<?php
    add_filter('get_sample_permalink_html', 'perm', '',4);

    function perm($return, $id, $new_title, $new_slug){
        $mydomain = get_field('build_site_url', 'option');
        
        // if $mydomain ends in a slash, remove it
        if(substr($mydomain, -1) == '/') {
            $mydomain = substr($mydomain, 0, -1);
        }

        $currentsiteurl = get_site_url();

        $newurl = preg_replace("($currentsiteurl)", $mydomain, $return);

        return $newurl;
    }
?>