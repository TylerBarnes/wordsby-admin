<?php 
add_action( 'rest_api_init', 'custom_api_get_all_taxonomies_terms' );   

function custom_api_get_all_taxonomies_terms() {
    register_rest_route( 'wp/v1', '/tax-terms', array(
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
        $taxonomy_details = get_taxonomy($taxonomy);

        $tax_term = [
            'name' => $taxonomy_details->name,
            'id' => $taxonomy_details->name,
            'label' => $taxonomy_details->label,
            'pathname' => get_taxonomy_archive_link($taxonomy),
            'terms' => []
        ];
        $terms = get_terms($taxonomy);
        // if (count($terms) === 0) $tax_term['terms']['dummy'] = null;

        foreach ($terms as $term) {
            $term_link = get_term_link($term);
            $pathname = $term_link ? str_replace($site_url, '', $term_link) : null;
            $tax_term['terms'][] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'wordpress_id' => $term->term_id,
                'taxonomy' => $term->taxonomy,
                'pathname' => $pathname
            ];
        }

        $taxonomies_terms[] = $tax_term;
    }

    return $taxonomies_terms;
}
?>