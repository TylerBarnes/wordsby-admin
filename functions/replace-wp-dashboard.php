<?php

/**
 * Plugin Name: Replace WordPress Dashboard
 * Description: Replaces the default WordPress dashboard with a custom one.
 * Author: Micah Wood
 * Author URI: http://micahwood.me
 * Version: 0.1
 * License: GPL3
 */

/**
 * This plugin offers a starting point for replacing the WordPress dashboard.  If you are familiar with object oriented
 * programming, just subclass and overwrite the set_title() and page_content() methods. Otherwise, just alter the
 * set_title() and page_content() functions as needed.
 *
 * Customize which users are redirected to the custom dashboard by changing the capability property.
 *
 * If you don't want this plugin to be deactivated, just drop this file in the mu-plugins folder in the wp-content
 * directory.  If you don't have an mu-plugins folder, just create one.
 */

class Replace_WP_Dashboard {

    protected $capability = 'read';

    protected $title;

    final public function __construct() {
        if( is_admin() ) {
            add_action( 'init', array( $this, 'init' ) );
        }
    }

    final public function init() {
        if( current_user_can( $this->capability ) ) {
            $this->set_title();
            add_filter( 'admin_title', array( $this, 'admin_title' ), 10, 2 );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'current_screen', array( $this, 'current_screen' ) );
        }
    }

    /**
     * Sets the page title for your custom dashboard
     */
    function set_title() {
        if( ! isset( $this->title ) ) {
            $this->title = __( 'Dashboard' );
        }
    }

    function get_collections_menu_items($menu_item) {
        global $wp_post_types, $collections_blacklist;

        if (in_array($menu_item[0], $collections_blacklist)) return false;

        foreach($wp_post_types as $post_type) {
            if($post_type->label === $menu_item[0]) return true;
        }

        return false;
    }

    /**
     * Output the content for your custom dashboard
     */
    function page_content() {
        // $content = __( 'Welcome to your new dashboard!' );
        echo <<<HTML
    <div class="wrap">
        <h2>{$this->title}</h2>

        <style>

            .counter {
                background: #222447;
                color: white;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                text-align: center;
                font-size: 14px;
                line-height: 25px;
                margin-left: 5px;
                position: absolute;
                top: 50%;
                left: 0;
                transform: translate(-50%, -50%);
            }

            .collections-title {
                margin-top: 50px;    
            }

            .collections__collection {
                display: block;
                min-height: 100px;
                background: white;
                box-shadow: 0px 5px 30px 0px rgba(0,0,0,0.1);
                margin-bottom: 40px;
                position: relative;
                font-weight: bold;
                letter-spacing: .5px;

                display: flex;
                align-items: center;
                padding: 0 40px;

                text-decoration: none;
                color: #1F2044;
            }

            @supports(display: grid) {
                .collections {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    grid-gap: 40px;
                }

                .collections__collection {
                    margin-bottom: 0;
                }
            }
        </style>
        <h3 class="collections-title">Collections</h3>
        <section class="collections">
HTML;
        global $menu, $collections_blacklist;
        $collections = get_post_types(['public' => true], 'objects'); 
        foreach($collections as $post_type):

            if (in_array($post_type->label, $collections_blacklist)) {
                write_log('test');
                continue;
            };
            ?>
            <a 
                href="<?php echo admin_url('edit.php?post_type=' . $post_type->name); ?>" 
                class="collections__collection"
                >
                    <?php echo $post_type->label; ?>
            </a>
            <?php
        endforeach;
echo <<<HTML
        </section>  
    </div>
HTML;
    }

    /**
     * Fixes the page title in the browser.
     *
     * @param string $admin_title
     * @param string $title
     * @return string $admin_title
     */
    final public function admin_title( $admin_title, $title ) {
        global $pagenow;
        if( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'custom-page' == $_GET['page'] ) {
            $admin_title = $this->title . $admin_title;
        }
        return $admin_title;
    }

    final public function admin_menu() {

        /**
         * Adds a custom page to WordPress
         */
        add_menu_page( $this->title, '', $this->capability, 'gatsbypress', array( $this, 'page_content' ) );

        /**
         * Remove the custom page from the admin menu
         */
        remove_menu_page('gatsbypress');

        /**
         * Make dashboard menu item the active item
         */
        global $parent_file, $submenu_file;
        $parent_file = 'index.php';
        $submenu_file = 'index.php';

        /**
         * Rename the dashboard menu item
         */
        global $menu;
        $menu[2][0] = $this->title;

        /**
         * Rename the dashboard submenu item
         */
        global $submenu;
        $submenu['index.php'][0][0] = $this->title;

    }

    /**
     * Redirect users from the normal dashboard to your custom dashboard
     */
    final public function current_screen( $screen ) {
        if( 'dashboard' == $screen->id ) {
            wp_safe_redirect( admin_url('admin.php?page=gatsbypress') );
            exit;
        }
    }

}

new Replace_WP_Dashboard();