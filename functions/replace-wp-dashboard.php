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
        global $wp_post_types;

        write_log($menu_item);

        foreach($wp_post_types as $post_type) {
            if($post_type->label === $menu_item[0]) return true;
        }

        return false;
    }

    /**
     * Output the content for your custom dashboard
     */
    function page_content() {
        $content = __( 'Welcome to your new dashboard!' );
        echo <<<HTML
    <div class="wrap">
        <h2>{$this->title}</h2>
        <p>{$content}</p>

HTML;
        global $menu;
        $collections = get_post_types(['public' => true], 'objects'); 
        foreach($collections as $post_type):
            ?>
            <a href="<?php echo $post_type->_edit_link; ?>" class="collection">
                <?php echo $post_type->label; ?>
                <?php $post_count = wp_count_posts($post_type->name); ?>
                <?php echo $post_count->publish; ?>
            </a>
            <?php
        endforeach;
echo <<<HTML
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
        add_menu_page( $this->title, '', $this->capability, 'custom-page', array( $this, 'page_content' ) );

        /**
         * Remove the custom page from the admin menu
         */
        remove_menu_page('custom-page');

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
            wp_safe_redirect( admin_url('admin.php?page=custom-page') );
            exit;
        }
    }

}

new Replace_WP_Dashboard();