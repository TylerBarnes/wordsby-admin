<?php 
$branch 
    = defined('WORDSBY_GITLAB_BRANCH') 
                ? WORDSBY_GITLAB_BRANCH 
                : 'master';

$mediaBranch 
    = defined('WORDSBY_GITLAB_MEDIA_BRANCH') 
                ? WORDSBY_GITLAB_MEDIA_BRANCH 
                : 'wordlify-media--automatic-branch';



require_once __DIR__ . "/utils/branching.php";
require_once __DIR__ . "/utils/setup.php";
require_once __DIR__ . "/utils/general.php";
require_once __DIR__ . "/utils/getOptions.php";

require_once __DIR__ . "/hooks/collections.php";
require_once __DIR__ . "/hooks/media.php";
require_once __DIR__ . "/hooks/menus.php";

?>