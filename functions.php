<?php 
require_once dirname( __FILE__ ) . "/lib/class-tgm-plugin-activation.php";
require_once dirname( __FILE__ ) . "/functions/require-plugins.php";

require_once dirname( __FILE__ ) . "/functions/write_log.php";

require_once dirname( __FILE__ ) . "/functions/discourage-search-engines.php";
require_once dirname( __FILE__ ) . "/functions/redirect-index-to-admin.php";
require_once dirname( __FILE__ ) . "/functions/activate-pretty-permalinks.php";

require_once dirname( __FILE__ ) . "/plugins/WordsbyCore/wordsby-core.php";

require_once dirname( __FILE__ ) . "/plugins/BetterAdmin/better-admin.php";

require_once dirname( __FILE__ ) . "/plugins/AlwaysAvatars/always-avatars.php";


require_once dirname( __FILE__ ) . "/plugins/PsychicWindow/psychic-window-posttype.php";

require_once dirname( __FILE__ ) . "/functions/acf-google-map-key.php";
?>