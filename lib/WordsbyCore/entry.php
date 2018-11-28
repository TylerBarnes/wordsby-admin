<?php 
require_once dirname( __FILE__ ) . "/image-settings.php";
require_once dirname( __FILE__ ) . "/build-hook.php";
require_once dirname( __FILE__ ) . "/replace_urls_with_pathnames.php";
require_once dirname( __FILE__ ) . "/get_taxonomy_archive_link.php";
require_once dirname( __FILE__ ) . "/rest-api-endpoint-all-pages-posts.php";
require_once dirname( __FILE__ ) . "/rest-api-endpoint-all-taxonomies-terms.php";
require_once dirname( __FILE__ ) . "/populate-templates-from-json-file.php";
require_once dirname( __FILE__ ) . "/acf-nullify-empty.php";
require_once dirname( __FILE__ ) . "/fix-edit-page-permalinks.php";
require_once dirname( __FILE__ ) . "/discourage-search-engines.php";
require_once dirname( __FILE__ ) . "/redirect-index-to-admin.php";
require_once dirname( __FILE__ ) . "/main-options-page.php";
require_once dirname( __FILE__ ) . "/menus.php";
require_once dirname( __FILE__ ) . "/always-one-post-one-term.php";
require_once dirname( __FILE__ ) . "/acf-fields.php";
?>