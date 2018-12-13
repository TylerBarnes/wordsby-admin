<?php 
// check wp-admin/menu-header.php for more
function get_menu_link_path($item, $submenu = "") {
            // $menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
            $menu_hookname = get_plugin_page_hookname( $item[2], 'admin.php' );
            $menu_file = $item[2];            

            if (
                    // ! empty( $menu_hook ) 
                    ! empty( $menu_hookname ) 
                    &&
                    'index.php' != $menu_file
                    &&
                    ! strpos($menu_file, '.php')
                    // ( 
                    //     ( 'index.php' != $menu_file ) && 
                    //     file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && 
                    //     ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) 
                    // ) 
                ) 
                {

                $admin_is_parent = true;
                return "admin.php?page={$item[2]}";

            } else {
                return $item[2];
            }
}

function remove_non_menu_items($value) {
    return strpos($value[2], 'separator') === false;
}

function check_diff_multi($array1, $array2){
    $result = array();
    foreach($array1 as $key => $val) {
         if(isset($array2[$key])){
           if(is_array($val) && $array2[$key]){
               $result[$key] = check_diff_multi($val, $array2[$key]);
           }
       } else {
           $result[$key] = $val;
       }
    }

    return $result;
}

$collections_blacklist = array('Psychic Window', 'Schema Builder');

function get_collections_menu_items($menu_item) {
    global $wp_post_types, $collections_blacklist;
    
    
    if (in_array($menu_item[0], $collections_blacklist)) return false;

    foreach($wp_post_types as $post_type) {
        if($post_type->label === $menu_item[0]) return true;
    }

    return false;
}

function find_submenu_items($item) {
        global $submenu;
        if ( ! empty( $submenu[$item[2]] ) ) {
            // $class[] = 'wp-has-submenu';
            return $submenu[$item[2]];
        }   else {
            return false;
        }
}

function create_section_submenu($item) {
            
                            $submenu_items = find_submenu_items($item);
                        ?>
                            <a href="<?php echo get_menu_link_path($item); ?>">
                                <?php echo $item[0]; ?>
                            </a>
                            
                            <?php if(is_array($submenu_items)): ?>
                                <nav class="betternav__submenu submenu">
                                    <?php foreach($submenu_items as $item): ?>
                                        <a href="<?php echo get_menu_link_path($item, true); ?>">
                                            <?php echo $item[0]; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </nav>
                            <?php endif; 
}

function create_section_menu($section_menu) {
    ?>
                    <?php foreach($section_menu as $item): ?>
                        <?php 
                        // check wp-admin/menu-header.php for more
                        // 0 = menu_title, 
                        // 1 = capability, 
                        // 2 = menu_slug, 
                        // 3 = page_title, 
                        // 4 = classes, 
                        // 5 = hookname, 
                        // 6 = icon_url ?>

                        
                        <article class="betternav__item">
                            <?php create_section_submenu($item); ?>
                        </article>

                    <?php endforeach; ?>
    <?php
}

add_action('adminmenu', 'my_admin_footer_function');
function my_admin_footer_function() {
    global $menu, $submenu;
    global $self, $parent_file, $menu;

    // remove separators
    $menu = array_filter($menu, 'remove_non_menu_items');
    // move post type menu items into collections
    $collections = array_filter($menu, 'get_collections_menu_items'); 
    // remove collections from menu items
    $menu = array_filter(check_diff_multi($menu, $collections));

    // remove dashboard from menu
    $dashboard = false;
    $users = false;
    $comments = false;
    foreach ($menu as $key => $item) {
        if ($item[5] === "menu-dashboard") {
            $dashboard = $item;
            unset($menu[$key]);
        }

        // remove users from menu
        if ($item[5] === "menu-users") {
            $users = $item;
            unset($menu[$key]);
        }

        // remove comments from menu
        if ($item[5] === "menu-comments") {
            $comments = $item;
            unset($menu[$key]);
        }

        if ($dashboard && $users && $comments) break;
    }


    $final_menu = array(
        'Dashboard' => [$dashboard],
        'Collections' => $collections,
        // remove comments menu if comments are disabled
        'Comments' => $comments ? [$comments] : [],
        'Users' => [$users],
        'Development' => $menu
    );

    // use this to set the active class later
    // $final_menu = array(
    //     'dashboard' => [
    //         'filename' => 'index.php',
    //         'menu' => $dashboard
    //     ],
    //     'collections' => [
    //         'filename' => false,
    //         'menu' => $collections
    //     ],
    //     'comments' => [
    //         'filename' => 'edit-comments.php',
    //         'menu' => $comments
    //     ],
    //     'users' => [
    //         'filename' => 'users.php',
    //         'menu' => $users
    //     ],
    //     'development' => [
    //         'filename' => false,
    //         'menu' => $menu
    //     ]
    // );

    ?>
    
    <nav class="betternav">
        <div class="betternav__head">
        
        </div>
        <ul class="betternav-menu">
        <?php foreach($final_menu as $section_title => $section_menu): ?>
            <?php if ($section_title 
                    && $section_menu 
                    && count($section_menu) > 1): ?>
                <?php $is_active = false; ?>
                <li class="
                    betternav__section-title betternav__section-title--has-children
                    <?php if ($is_active) echo "betternav__section-title--active"; ?>
                    ">
                    <?php echo $section_title; ?>

                    <div class="submenu betternav__section-menu betternav__section-title--has-no-children">
                        <?php create_section_menu($section_menu); ?>
                    </div>
                </li>
            <?php elseif ($section_menu && count($section_menu) == 1): ?>
                <li class="betternav__section-title betternav__section-title--has-children">
                    <?php create_section_submenu($section_menu[0]); ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        </ul>
    </nav>
    
    <?php
}

?>