<?php 
require_once dirname( __FILE__ ) . "/lib/class-tgm-plugin-activation.php";
require_once dirname( __FILE__ ) . "/functions/require-plugins.php";
require_once dirname( __FILE__ ) . "/functions/rest-api-endpoint-all-pages-posts.php";
require_once dirname( __FILE__ ) . "/functions/write_log.php";
require_once dirname( __FILE__ ) . "/functions/populate-templates-from-json-file.php";

require_once dirname( __FILE__ ) . "/functions/acf-nullify-empty.php";

require_once dirname( __FILE__ ) . "/functions/discourage-search-engines.php";
require_once dirname( __FILE__ ) . "/functions/redirect-index-to-admin.php";

require_once dirname( __FILE__ ) . "/functions/main-options-page.php";

require_once dirname( __FILE__ ) . "/functions/menus.php";

require_once dirname( __FILE__ ) . "/functions/always-one-post-one-term.php";

// previews / editing
require_once dirname( __FILE__ ) . "/functions/update-nonce-cookie.php";
require_once dirname( __FILE__ ) . "/functions/build-hook.php";
require_once dirname( __FILE__ ) . "/functions/fix-edit-page-permalinks.php";
require_once dirname( __FILE__ ) . "/functions/set-templates.php";
require_once dirname( __FILE__ ) . "/functions/change-post-preview-link.php";
require_once dirname( __FILE__ ) . "/functions/rest-api-preview-endpoint.php";
require_once dirname( __FILE__ ) . "/lib/GatsbyPreviews/receivePreviews.php";
// end previews

require_once dirname( __FILE__ ) . "/functions/image-settings.php";

require_once dirname( __FILE__ ) . "/functions/admin-menu.php";
require_once dirname( __FILE__ ) . "/functions/replace-admin-bar.php";
require_once dirname( __FILE__ ) . "/functions/replace-wp-dashboard.php";

require_once dirname( __FILE__ ) . "/lib/class-download-remote-image.php"; 
require_once dirname( __FILE__ ) . "/functions/random-unsplash-avatars.php";


require_once dirname( __FILE__ ) . "/lib/PsychicWindow/psychic-window-posttype.php";
?>