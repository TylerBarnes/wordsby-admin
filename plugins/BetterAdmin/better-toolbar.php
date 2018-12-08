<?php 

add_action( 'wp_before_admin_bar_render', 'modify_admin_bar' );

function modify_admin_bar( $wp_admin_bar ){
    global $wp_admin_bar;
    $gp_nodes = $wp_admin_bar->get_nodes();

    $gp_user_actions = $gp_nodes['user-actions'];
    $gp_user_info = $gp_nodes['user-info'];
    $gp_user_edit = $gp_nodes['edit-profile'];
    $gp_user_logout = $gp_nodes['logout'];
    $gp_menu_toggle = $gp_nodes['menu-toggle'];
    $gp_user_account = $gp_nodes['my-account'];

    $user_meta = get_user_meta(get_current_user_id());

    ?>

    <section class="gp-actions">
      <a href='<?php echo $gp_user_account->href; ?>'
      class="gp-user-avatar"
      >
        <img 
        src="<?php echo $user_meta['local_unsplash_avatar'][0]; ?>" 
        alt="random unplash.com profile image"
        >
        <p><?php echo $user_meta['nickname'][0]; ?></p>
      </a>

      <div class="gp-bottom-left-actions">
          <!-- <a href="">
            Site Name
          </a> -->
          <a href="<?php echo $gp_user_logout->href; ?>"><?php echo $gp_user_logout->title; ?></a>
      </div>

      <div class="gp-new-collection">
          <label>+ New</label>

          <div class="gp-new-collection__collections">
            <?php global $menu, $collections_blacklist;
            $collections = get_post_types(['public' => true], 'objects'); 
            foreach($collections as $post_type):
              if (in_array($post_type->label, $collections_blacklist)) continue;
                ?>
                <a 
                    href="<?php echo admin_url('post-new.php?post_type=' . $post_type->name); ?>" 
                    class="gp-new-collection__collection"
                    >
                    New <?php echo $post_type->labels->singular_name; ?>
                </a>
                <?php
            endforeach; ?>
          </div>
      </div>
    </section>
    <?php
}


?>