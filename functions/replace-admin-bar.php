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
    <style>
      #wpadminbar {
        display: none;
      }

      .gp-actions {
        z-index: 9999;
        position: fixed;
        top: 0;
        left: 0;
      }

      .gp-user-avatar {
        position: fixed;
        top: 25px;
        left: 20px;
        width: 120px;
        height: auto;
        display: none;
        text-align: center;
        text-decoration: none;
      }

      .gp-user-avatar p {
        font-size: 13px;
        margin-top: 5px;
        color: white;
        text-transform: uppercase;
        letter-spacing: 1px;
      }

      .gp-user-avatar img {
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
        border-radius: 50%;
        width: 100%;
        height: auto;
      }

      @media screen and (min-width: 783px) {
        .gp-user-avatar {
          display: block;
        }
      }

      .gp-bottom-left-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 160px;
        background: #1F2044;
        padding: 10px 0;
        text-align: center;
      }

      /* .gp-bottom-left-actions a:last-child {
        border-top: 2px solid white !important;
      } */

      .gp-bottom-left-actions a {
        padding: 5px 20px;
        display: block;
        font-size: 11px;
        letter-spacing: 2px;
        font-weight: bold;
        text-transform: uppercase;
        color: white;
        text-decoration: none;
      }

      .gp-new-collection {
        position: fixed;
        bottom: 40px;
        right: 50px;
        background: white;
        color: red;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
        width: auto;
        padding: 20px 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 10%;
        font-weight: bold;
      }
    </style>

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
          <label>+ New Post</label>

          <div class="gp-new-collection__collections">

          </div>
      </div>
    </section>
    <?php
}


?>