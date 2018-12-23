<?php 
// 
// This code was adapted from the WP-API-Menus plugin.

/**
 * Returns all child nav_menu_items under a specific parent.
 *
 * @since   1.2.0
 * @param int   $parent_id      The parent nav_menu_item ID
 * @param array $nav_menu_items Navigation menu items
 * @param bool  $depth          Gives all children or direct children only
 * @return array	returns filtered array of nav_menu_items
 */
 function wordlify_get_nav_menu_item_children( $parent_id, $nav_menu_items, $depth = true ) {

    $nav_menu_item_list = array();

    foreach ( (array) $nav_menu_items as $nav_menu_item ) :

        if ( $nav_menu_item->menu_item_parent == $parent_id ) :

            $nav_menu_item_list[] = wordlify_format_menu_item( $nav_menu_item, true, $nav_menu_items );

            if ( $depth ) {
                if ( $children = wordlify_get_nav_menu_item_children( $nav_menu_item->ID, $nav_menu_items ) ) {
                    $nav_menu_item_list = array_merge( $nav_menu_item_list, $children );
                }
            }

        endif;

    endforeach;

    return $nav_menu_item_list;
}


/**
 * Check if a collection of menu items contains an item that is the parent id of 'id'.
 *
 * @since  1.2.0
 * @param  array $items
 * @param  int $id
 * @return array
 */
function has_children( $items, $id ) {
    return array_filter( $items, function( $i ) use ( $id ) {
        return $i['parent'] == $id;
    } );
}


/**
 * Handle nested menu items.
 *
 * Given a flat array of menu items, split them into parent/child items
 * and recurse over them to return children nested in their parent.
 *
 * @since  1.2.0
 * @param  $menu_items
 * @param  $parent
 * @return array
 */
function wordlify_nested_menu_items( &$menu_items, $parent = null ) {

    $parents = array();
    $children = array();

    // Separate menu_items into parents & children.
    array_map( function( $i ) use ( $parent, &$children, &$parents ){
        if ( $i['id'] != $parent && $i['parent'] == $parent ) {
            $parents[] = $i;
        } else {
            $children[] = $i;
        }
    }, $menu_items );

    foreach ( $parents as &$parent ) {

        if ( has_children( $children, $parent['id'] ) ) {
            $parent['children'] = wordlify_nested_menu_items( $children, $parent['id'] );
        }
    }

    return $parents;
}


/**
 * Format a menu item for REST API consumption.
 *
 * @since  1.2.0
 * @param  object|array $menu_item  The menu item
 * @param  bool         $children   Get menu item children (default false)
 * @param  array        $menu       The menu the item belongs to (used when $children is set to true)
 * @return array	a formatted menu item for REST
 */
function wordlify_format_menu_item( $menu_item, $children = false, $menu = array() ) {

    $item = (array) $menu_item;

    $menu_item = array(
        'id'          => abs( $item['ID'] ),
        'wordpress_id'          => abs( $item['ID'] ),
        'order'       => (int) $item['menu_order'],
        'parent'      => abs( $item['menu_item_parent'] ),
        'title'       => $item['title'],
        'pathname'    => $item['url'],
        'attr'        => $item['attr_title'],
        'target'      => $item['target'],
        'classes'     => implode( ' ', $item['classes'] ),
        'xfn'         => $item['xfn'],
        'description' => $item['description'],
        'object_id'   => abs( $item['object_id'] ),
        'object'      => $item['object'],
        'object_slug' => get_post( $item['object_id'] )->post_name,
        'type'        => $item['type'],
        'type_label'  => $item['type_label'],
        'acf'         => get_fields($item['ID'] ) ?  get_fields($item['ID'] ) : null
    );

    if ( $children === true && ! empty( $menu ) ) {
        $menu_item['children'] = wordlify_get_nav_menu_item_children( $item['ID'], $menu );
    }

    return apply_filters( 'rest_menus_wordlify_format_menu_item', $menu_item );
}

/**
 * Get menus.
 *
 * @since  1.2.0
 * @return array All registered menus
 * borrowed from wp-api-menus plugin
 */
function wordlify_get_menus() {
    $wp_menus = wp_get_nav_menus();

    $i = 0;
    $rest_menus = array();
    foreach ( $wp_menus as $wp_menu ) :

        $menu = (array) $wp_menu;

        $id = $menu['term_id'];

        $rest_menus[ $i ]                = $menu;
        $rest_menus[ $i ]['ID']          = $id;
        $rest_menus[ $i ]['wordpress_id']          = $id;
        $rest_menus[ $i ]['name']        = $menu['name'];
        $rest_menus[ $i ]['slug']        = $menu['slug'];
        $rest_menus[ $i ]['description'] = $menu['description'];
        $rest_menus[ $i ]['count']       = $menu['count'];


        $wp_menu_items  = $id ? wp_get_nav_menu_items( $id ) : array();


        $rest_menu_items = array();
        foreach ( $wp_menu_items as $item_object ) {
            $rest_menu_items[] = wordlify_format_menu_item( $item_object );
        }

        $rest_menu_items = wordlify_nested_menu_items($rest_menu_items, 0);
        $rest_menus[ $i ]['items']       = $rest_menu_items;


        $i ++;
    endforeach;

    return $rest_menus;
}

?>