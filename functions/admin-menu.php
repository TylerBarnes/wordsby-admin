<?php 

function get_menu_link_path($item) {
            $menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
            $menu_file = $item[2];
            $class = '';
			if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
				$menu_file = substr( $menu_file, 0, $pos );
			if ( ! empty( $menu_hook ) || ( ( 'index.php' != $item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
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

function get_collections_menu_items($menu_item) {
    global $wp_post_types;

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
                                <nav class="betternav__submenu">
                                    <?php foreach($submenu_items as $item): ?>
                                        <a href="<?php echo get_menu_link_path($item); ?>">
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
    <style>
    input[type=text] {
        border: 2px solid lightblue !important;
    }

    html body input[type=radio],
    html body input[type=checkbox] {
        box-shadow: 0 0 30px 0 rgba(0,0,0,0.1) !important;
        border: 2px solid lightgrey !important;
    }

    html body input[type=radio]:checked,
    html body input[type=checkbox]:checked {
        border-color: transparent !important;
    }

    input[type=radio]:checked:before {
        width: 100%;
        height: 100%;
        margin: 0;
    }
        body {
            background-color: white;
            /* background-color: #f6fafd; */
            /* background-color: #e9eff4; */
            font-size: 16px;
        }

        #wpbody-content {
            width: 90%;
            margin: 0 auto;
        }

        #wpadminbar {
            height: 50px;
            background: #241336;
            /* display: none; */
        }

        html.wp-toolbar {
            padding-top: 80px;
            /* padding-top: 0; */
        }

        .quicklinks {
                height: 100% !important;
                width: 100% !important;
                display: flex;
                align-items: center;
        }

        #wp-admin-bar-top-secondary {
            margin-left: auto !important;
            margin-right: 0 !important;
        }

        .postbox .inside h2, .wrap [class$=icon32]+h2, .wrap h1, .wrap>h2:first-child {
            font-size: 40px;
            margin-bottom: 20px;
        }

        @import url('https://fonts.googleapis.com/css?family=Amiri:700');
        @font-face {
            font-family: 'Inter UI';    
            src: url("<?php echo get_stylesheet_directory_uri(); ?>/assets/fonts/Inter-ui/Inter-UI-Regular.woff") format('woff');
            font-weight: 400;
            font-style: normal;
        }

        html, html * {
            font-family: 'Inter UI', sans-serif;
            border: none !important;
        }

        div.updated {
            /* border-left: 4px solid red !important;  */
            border: 2px solid #FF3366 !important; 
        }

        h1, h2, h3, h4, h5 {
            /* font-family: 'Amiri', serif; */
        }

        #wpcontent, #wpfooter {
            margin-left: 200px;
        }

        .betternav * {
            color: white !important;
            /* color: #241336 !important; */
        }

        .betternav img {
            display: none;
        }

        .betternav__section-title {
            position: relative;
        }
        .betternav__section-title > a {
            padding: 0 !important;
        }

        
        .betternav__section-menu {
            box-shadow: -17px 0px 80px 10px rgba(0,0,0,.1);
        }
        .betternav__submenu {
            box-shadow: 13px 0px 80px 0px rgba(0,0,0,.1);
        }

        .betternav__section-title, .betternav__item > a, .betternav__submenu > a {
            width: 100%;
            box-sizing: border-box;
            display: block;
            padding: 10px 20px !important;
        }

        .betternav__item--active {
            background: black;
            color: white;
        }

        .betternav, .betternav__section-menu, .betternav__submenu {
            width: 160px;
        }


        .betternav {
            position: fixed;
            top: 0;
            /* top: 50px; */
            left: 0;
            z-index: 9991;
        }

        .betternav, #wp-content-wrap {
            box-shadow: 0 0 80px -5px rgba(0,0,0,.1) !important;
        }

        .wp-list-table {
            box-shadow: 0 0 80px -5px rgba(0,0,0,.1) !important;
        }

        .wp-list-table {
            margin: 20px 0;
        }

        #adminmenu .awaiting-mod, #adminmenu .update-plugins {
            background-color: red;
            background-color: #FF3366;
        }

        .pending-count {
            /* color: #222447 !important; */
            color: white !important;
        }

        .update-count, .plugin-count {
            color: white !important;
        }


        .postbox {
            box-shadow: 0 0 30px -5px rgba(0,0,0,.1);
        }

        #titlewrap input, .wp-list-table {
            border: 2px solid rgb(204, 204, 204) !important;

        }


        #screen-meta-links {
            position: fixed;
            top: 0;
            right: 0;
        }

        .betternav__head {
            background: #663399;
            background: #401d64;
            background: white;
            /* background: black; */
            min-height: 75px;
            margin-bottom: 150px;
        }

        #footer-thankyou {
            display: none;
        }
        
        .betternav {
            height: 100vh;

        }
        .betternav {
            background: #222447;
            /* background: white; */
        }

        .betternav__section-menu, .betternav__submenu {
            background: #787abe;
            background: #97d5dc;
            background: #171717;
            background: white;
        }

        .betternav__section-menu a, .betternav__submenu a {
            padding-left: 20px;
            padding-right:20px;

            color: black !important;
        }

        .betternav__section-menu, .betternav__submenu {
            padding: 20px 0;
            display: none;
            position: absolute;
            right: 0;
            top: -20px;
            transform: translateX(100%);
            z-index: -1;
        }

        .betternav__item > .betternav__submenu {
            top: 0;
        }

        .betternav__section-title:hover > .betternav__section-menu,
        .betternav__item:hover > .betternav__submenu,
        .betternav__section-title:hover > .betternav__submenu {
            display: block;
        }
    </style>
    
    <nav class="betternav">
        <div class="betternav__head">
        
        </div>
        <?php foreach($final_menu as $section_title => $section_menu): ?>
            <?php if ($section_title 
                    && $section_menu 
                    && count($section_menu) > 1): ?>
                <?php $is_active = false; ?>
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
                <article class="betternav__section-title">
                    <?php create_section_submenu($section_menu[0]); ?>
                </article>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    
    <?php
}

?>