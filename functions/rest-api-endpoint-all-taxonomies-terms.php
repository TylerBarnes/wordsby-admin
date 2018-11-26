<?php 
add_action( 'rest_api_init', 'custom_api_get_all_taxonomies_terms' );   

function custom_api_get_all_taxonomies_terms() {
    register_rest_route( 'wp/v1', '/taxonomies-terms', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_taxonomies_terms_callback'
    ));
}

function custom_api_get_all_taxonomies_terms_callback() {
    $taxonomies = get_taxonomies([
        'show_ui' => true,
        'show_in_rest' => true,
        'public' => true
    ]);

    $taxonomies_terms = [];

    $site_url = get_site_url();

    foreach ($taxonomies as $taxonomy) {
        $taxonomies_terms[$taxonomy] = [
            'pathname' => get_taxonomy_archive_link($taxonomy),
            'terms' => []
        ];
        $terms = get_terms($taxonomy);
        if (count($terms) === 0) $taxonomies_terms[$taxonomy]['terms']['dummy'] = null;

        foreach ($terms as $term) {
            $term_link = get_term_link($term);
            $pathname = $term_link ? str_replace($site_url, '', $term_link) : null;
            $taxonomies_terms[$taxonomy]['terms'][$term->slug] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'id' => $term->term_id,
                'taxonomy' => $term->taxonomy,
                'pathname' => $pathname
            ];
        }
    }

    return $taxonomies_terms;
}
?>