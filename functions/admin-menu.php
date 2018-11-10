<?php 

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

function get_collections_menu_items($menu_item) {
    global $wp_post_types;

    foreach($wp_post_types as $post_type) {
        if($post_type->label === $menu_item[0]) return true;
        // if($menu_item[5] == 'menu-comments') return true;
        // if($menu_item[5] == 'menu-users') return true;
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
                            <a href="<?php echo $item[2]; ?>">
                                <?php echo $item[0]; ?>
                            </a>
                            
                            <?php if($submenu_items): ?>
                                <nav class="betternav__submenu">
                                    <pre><?php //create_section_submenu($submenu_items); ?></pre>
                                    <?php //write_log($submenu_items); ?>
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
    foreach ($menu as $key => $item) {
        if ($item[5] === "menu-dashboard") {
            $dashboard = $item;
            unset($menu[$key]);
            break;
        }
    }

    // remove users from menu
    $users = false;
    foreach ($menu as $key => $item) {
        if ($item[5] === "menu-users") {
            $users = $item;
            unset($menu[$key]);
            break;
        }
    }

    // remove comments from menu
    $comments = false;
    foreach ($menu as $key => $item) {
        if ($item[5] === "menu-comments") {
            $comments = $item;
            unset($menu[$key]);
            break;
        }
    }

    $final_menu = array(
        'dashboard' => [$dashboard],
        'collections' => $collections,
        'comments' => [$comments],
        'users' => [$users],
        'settings' => $menu
    );

    ?>
    <style>
        .betternav {
            /* display: none; */
            position: fixed;
            top: 32px;
            left: 0;
            z-index: 9991;
            min-height: 100vh;
            width: 160px;
            background: blue;
        }

        .betternav * {
            color: white;
        }

        .betternav__section-title > a {
            padding: 0 !important;
        }

        .betternav__section-title {
            width: 100%;
            box-sizing: border-box;
            display: block;
            padding: 10px 20px;
        }

        .betternav__item, .betternav__section-title {
            position: relative;
        }

        .betternav__item--active {
            background: black;
            color: white;
        }

        .betternav__section-menu, .betternav__submenu {
            display: none;
            position: absolute;
            right: 0;
            top: 0;
            transform: translateX(100%);
            background: black;
        }

        .betternav__section-title:hover .betternav__section-menu,
        .betternav__item:hover .betternav__submenu {
            display: block;
        }
    </style>
    
    <nav class="betternav">
        <?php foreach($final_menu as $section_title => $section_menu): ?>
            <?php if ($section_menu && count($section_menu) > 1): ?>

                <?php 
                

                             $is_active = false;
                ?>
                <article class="
                    betternav__section-title
                    <?php if ($is_active) echo "betternav__section-title--active"; ?>
                    ">
                    <?php echo $section_title; ?>

                    <div class="betternav__section-menu">
                        <?php create_section_menu($section_menu); ?>
                    </div>
                </article>
            <?php elseif ($section_menu && count($section_menu) == 1): ?>
            <?php global $parent_file, $self;
                write_log($section_title);
                write_log($self);
                // $is_active = ( $parent_file && $section_menu[2] == $parent_file ) 
                //              || ( empty($typenow) && $self == $section_menu[2] ); ?>
                <article class="betternav__section-title">
                    <?php //echo $section_title; ?>
                        <?php create_section_submenu($section_menu[0]); ?>

                    <!-- <div class="betternav__section-menu">
                    </div> -->
                </article>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    
    <?php
}
?>