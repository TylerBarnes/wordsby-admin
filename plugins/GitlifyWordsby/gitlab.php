<?php 
$branch 
    = defined('WORDSBY_GITLAB_BRANCH') 
                ? WORDSBY_GITLAB_BRANCH 
                : 'master';

$mediaBranch 
    = defined('WORDSBY_GITLAB_MEDIA_BRANCH') 
                ? WORDSBY_GITLAB_MEDIA_BRANCH 
                : 'wordlify-media--automatic-branch';


// This file is generated by Composer
require_once __DIR__ . '/vendor/autoload.php';

function getGitlabToken() {
    // https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html
    $gitlab_token = defined('WORDSBY_GITLAB_API_TOKEN') ? WORDSBY_GITLAB_API_TOKEN : false;
    
    if (!$gitlab_token) return;
    return $gitlab_token;
}

function getGitlabClient () {
    $gitlab_url = 'https://gitlab.com/api/v4/';
    $test_url = $gitlab_url . 'license';

    $gitlab_headers = @get_headers($test_url);
    if(!$gitlab_headers || strpos($gitlab_headers[0], '404')) {
        $error = "Your post couldn't be saved due to a server network error. Try again later.";
        jp_notices_add_error($error);
        write_log($error); 
        return false;
    }

    try {
        return \Gitlab\Client::create($gitlab_url)
        ->authenticate(getGitlabToken(), \Gitlab\Client::AUTH_URL_TOKEN);
    } catch (Exception $e) {
        write_log($e); 
        jp_notices_add_error($e);
        return false;
    }

}

function getTree($client, $base_path) {
    global $branch;
    $tree = $client->api('repositories')->tree(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
            'path' => $base_path,
            'recursive' => true,
            'ref' => $branch,
            'per_page' => 9999999
        )
    );

    return $tree;
}

function createMediaBranch($client, $desiredBranch = "") {
    global $branch;

    if ($desiredBranch === "") {
        global $mediaBranch;
        $desiredBranch = $mediaBranch;
    }

    return $client->api('repositories')->createBranch(
        WORDSBY_GITLAB_PROJECT_ID,
        $desiredBranch,
        $branch
    );
}

function createMediaBranchIfItDoesntExist($client) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return false;

    global $branch;
    global $mediaBranch;
    $desiredBranch = $mediaBranch;

    $branches = $client->api('repositories')->branches(
        WORDSBY_GITLAB_PROJECT_ID
    );  

    $desiredBranchExists = in_array(
        $desiredBranch, array_column($branches, 'name')
    );

    $response = [
        'branch' => $desiredBranch
    ];

    if (!$desiredBranchExists) {
        createMediaBranch($client);
        $response['action'] = 'created';
    } else {
        $response['action'] = 'exists';
    }

    return $response;
}

// add_action('admin_init', 'test');

// function test() {
//     $branch = createMediaBranchIfItDoesntExist(
//         getGitlabClient()
//     );

//     write_log($branch);
// }

function isFileInRepo($client, $base_path, $filename) {
    $tree = getTree($client, $base_path);

    return in_array($filename, array_column($tree, 'name'));
}

function getAllEditedFileVersionsInRepo($client, $base_path, $filename) {
    $tree = getTree($client, $base_path);

    $match = '/-e([0-9]+)/';
    
    $normalized_filename = preg_replace( 
            $match, '', $filename 
        );
    
    $allEditedVersions = [];

    foreach($tree as $file) {
        $normalized_tree_filename = preg_replace( 
            $match, '', $file['name'] 
        );
        if ($normalized_tree_filename === $normalized_filename) {
            array_push($allEditedVersions, $file);
        }
    }

    return $allEditedVersions;
}

function array_search_partial($arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}

function makeImagesRelative($json) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '../', $json
    );
}


add_action('acf/save_post', 'commitData');

function commitData($id) {
    if (isset($_POST['nav-menu-data'])) return;

    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    if (
        isset($_POST) && 
        isset($_POST['wp-preview']) && 
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;

    global $branch;
    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    $title = get_the_title($id);
    $base_path = "wordsby/data/";
    
    $client = getGitlabClient();

    if (!$client) return;
    
    $collections_action = isFileInRepo($client, $base_path, 'collections.json') 
                                ? 'update' : 'create';

    $tax_terms_action = isFileInRepo($client, $base_path, 'tax-terms.json') 
                                ? 'update' : 'create';

    $options_action = isFileInRepo($client, $base_path, 'options.json') 
                                ? 'update' : 'create';

    $site_meta_action = isFileInRepo($client, $base_path, 'site-meta.json') 
                                ? 'update' : 'create';

    $collections = json_encode(
        posts_formatted_for_gatsby(false), 
        JSON_UNESCAPED_SLASHES
    );

    $collections_content = makeImagesRelative($collections);
    
    $tax_terms_content = json_encode(
        custom_api_get_all_taxonomies_terms_callback(), 
        JSON_UNESCAPED_SLASHES
    );

    $options_content = makeImagesRelative(json_encode(
        custom_api_get_all_options_callback(),
        JSON_UNESCAPED_SLASHES
    ));

    

    $site_meta_content = json_encode(array(
        array(
            'key' => 'url',
            'value' => get_bloginfo('url')
        ),
        array(
            'key' => 'name',
            'value' => get_bloginfo('name')
        ),
        array(
            'key' => 'description',
            'value' => get_bloginfo('description')
        ),
    ));


    $commit = $client->api('repositories')->createCommit(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
        'branch' => $branch, 
        'commit_message' => "
                        Post \"$title\" updated [id:$id] 
                        — by $username (from $site_url)
        ",
        'actions' => array(
            array(
                'action' => $collections_action,
                'file_path' => $base_path . "collections.json",
                'content' => $collections_content,
                'encoding' => 'text'
            ),
            array(
                'action' => $tax_terms_action,
                'file_path' => $base_path . "tax-terms.json",
                'content' => $tax_terms_content,
                'encoding' => 'text'
            ),
            array(
                'action' => $options_action,
                'file_path' => $base_path . "options.json",
                'content' => $options_content,
                'encoding' => 'text'
            ),
            array(
                'action' => $site_meta_action,
                'file_path' => $base_path . "site-meta.json",
                'content' => $site_meta_content,
                'encoding' => 'text'
            ),
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
        )
    );

    return $commit; 

}

add_action('delete_attachment', 'deleteMedia');
function deleteMedia($id) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    global $branch;

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $filepath = wp_get_attachment_metadata($id)['file'];
    $filename = basename($filepath);
    $filedirectory = dirname($filepath); 

    $base_path = 'wordsby/uploads';

    $fulldirectory = "$base_path/$filedirectory/";
    $full_filepath = "$fulldirectory$filename";

    $client = getGitlabClient();

    if (!$client) return;

    $media_exists = isFileInRepo($client, $fulldirectory, $filename);

    if (!$media_exists) return;

    $actions = array();

    $edited_file_versions = getAllEditedFileVersionsInRepo(
        $client, $fulldirectory, $filename
    );

    if (count($edited_file_versions) > 0) {
        foreach($edited_file_versions as $file) {
            array_push($actions, [
                'action' => 'delete',
                'file_path' => $file['path']
            ]);
        }
    } else {
        array_push($actions, array(
            'action' => 'delete',
            'file_path' => $full_filepath
        ));
    }

    $commit = $client->api('repositories')->createCommit(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
            'branch' => $branch, 
            'commit_message' => "
                        \"$filename\" deleted 
                        — by $username (from $site_url)
            ",
            'actions' => $actions,
            'author_email' => $username,
            'author_name' => $current_user->user_email
        )
    );
}


add_filter('image_make_intermediate_size', 'commitEditedMedia');
function commitEditedMedia($full_filepath) {
    // bail out if this is an upload
    if (
        isset($_POST) && 
        isset($_POST['action']) && 
        $_POST['action'] !== 'image-editor'
        ) return $full_filepath;

    // bail out if this is an intermediate image size. 
    // gatsby creates our image sizes so we only commit full size images to the repo.
    if (
        !preg_match(
            '/^(?!.*-\d{2,4}x\d{2,4}).*\.(jpg|png|bmp|gif|ico)$/', $full_filepath
            )
        ) return $full_filepath;

    // bail if the file doesn't exist. It should, but just in case.
    if (!file_exists($full_filepath)) {
        jp_notices_add_error("There was an error saving your image. Please try again.");
        return $full_filepath;
    };

	$dirname = pathinfo( $full_filepath, PATHINFO_DIRNAME );
	$ext = pathinfo( $full_filepath, PATHINFO_EXTENSION );
    $filename = pathinfo( $full_filepath, PATHINFO_FILENAME );

    $repo_full_filepath = "wordsby/" . substr(
        $full_filepath, 
        strpos($full_filepath, "/uploads/") + 1
    );  

    $repo_filename = pathinfo( $repo_full_filepath, PATHINFO_BASENAME );
	$repo_dirname  = pathinfo( $repo_full_filepath, PATHINFO_DIRNAME );


    global $branch;

    $client = getGitlabClient();
    if (!$client) return null; 


    $media_exists = isFileInRepo($client, $repo_dirname, $repo_filename);
    $action = $media_exists ? 'update' : 'create';

    $original_filename = preg_replace( 
        '/-e([0-9]+)$/', '', $filename 
        ) . ".$ext";

    $repo_original_filepath = "$repo_dirname/$original_filename";
    
    $original_media_exists = isFileInRepo(
        $client, $repo_dirname, $original_filename
    );


    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $commit_message = "
            \"$filename\" edited (\"$repo_filename\") 
            — by $username (from $site_url)
        ";

    $actions = array(
        array(
            'action' => $action,
            'file_path' => $repo_full_filepath,
            'content' => base64_encode(file_get_contents($full_filepath)),
            'encoding' => 'base64'
        )
    );

    // delete the original media file from the repo if 
    // IMAGE_EDIT_OVERWRITE is true.
    if (
        defined( 'IMAGE_EDIT_OVERWRITE' ) && 
        IMAGE_EDIT_OVERWRITE &&
        $original_media_exists
        ) {
        array_push($actions, array(
            'action' => 'delete',
            'file_path' => $repo_original_filepath,
        ));
    }

    $commit = $client->api('repositories')->createCommit(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
            'branch' => $branch, 
            'commit_message' => $commit_message,
            'actions' => $actions,
            'author_email' => $username,
            'author_name' => $current_user->user_email
        )
    );

    return $full_filepath;
}


add_action('wp_handle_upload', 'commitMedia');
function commitMedia($upload) {
    if (!defined("WORDSBY_GITLAB_PROJECT_ID")) return $upload;

    global $branch;

    $initial_filepath = explode("uploads/",$upload['file'])[1];
    $filename = basename($initial_filepath);
    $subdir = dirname($initial_filepath);
    
    $base_path = 'wordsby/uploads';
    $file_dir = "$base_path/$subdir";
    $filepath = "$file_dir/$filename";

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $client = getGitlabClient();

    if (!$client) return;

    $media_exists = isFileInRepo($client, $file_dir, $filename);
    $action = $media_exists ? 'update' : 'create';

    $commit = $client->api('repositories')->createCommit(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
        'branch' => $branch, 
        'commit_message' => "
                    \"$filename\" 
                    — by $username (from $site_url)
        ",
        'actions' => array(
            array(
                'action' => $action,
                'file_path' => $filepath,
                'content' => base64_encode(file_get_contents($upload['file'])),
                'encoding' => 'base64'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    return $upload;
}





/**
 * Returns all child nav_menu_items under a specific parent.
 *
 * @since   1.2.0
 * @param int   $parent_id      The parent nav_menu_item ID
 * @param array $nav_menu_items Navigation menu items
 * @param bool  $depth          Gives all children or direct children only
 * @return array	returns filtered array of nav_menu_items
 */
function wordlify_get_nav_menu_item_children( $parent_id, $nav_menu_items, $depth = true ) {

    $nav_menu_item_list = array();

    foreach ( (array) $nav_menu_items as $nav_menu_item ) :

        if ( $nav_menu_item->menu_item_parent == $parent_id ) :

            $nav_menu_item_list[] = wordlify_format_menu_item( $nav_menu_item, true, $nav_menu_items );

            if ( $depth ) {
                if ( $children = wordlify_get_nav_menu_item_children( $nav_menu_item->ID, $nav_menu_items ) ) {
                    $nav_menu_item_list = array_merge( $nav_menu_item_list, $children );
                }
            }

        endif;

    endforeach;

    return $nav_menu_item_list;
}


/**
 * Check if a collection of menu items contains an item that is the parent id of 'id'.
 *
 * @since  1.2.0
 * @param  array $items
 * @param  int $id
 * @return array
 */
function has_children( $items, $id ) {
    return array_filter( $items, function( $i ) use ( $id ) {
        return $i['parent'] == $id;
    } );
}


/**
 * Handle nested menu items.
 *
 * Given a flat array of menu items, split them into parent/child items
 * and recurse over them to return children nested in their parent.
 *
 * @since  1.2.0
 * @param  $menu_items
 * @param  $parent
 * @return array
 */
function wordlify_nested_menu_items( &$menu_items, $parent = null ) {

    $parents = array();
    $children = array();

    // Separate menu_items into parents & children.
    array_map( function( $i ) use ( $parent, &$children, &$parents ){
        if ( $i['id'] != $parent && $i['parent'] == $parent ) {
            $parents[] = $i;
        } else {
            $children[] = $i;
        }
    }, $menu_items );

    foreach ( $parents as &$parent ) {

        if ( has_children( $children, $parent['id'] ) ) {
            $parent['children'] = wordlify_nested_menu_items( $children, $parent['id'] );
        }
    }

    return $parents;
}


/**
 * Format a menu item for REST API consumption.
 *
 * @since  1.2.0
 * @param  object|array $menu_item  The menu item
 * @param  bool         $children   Get menu item children (default false)
 * @param  array        $menu       The menu the item belongs to (used when $children is set to true)
 * @return array	a formatted menu item for REST
 */
function wordlify_format_menu_item( $menu_item, $children = false, $menu = array() ) {

    $item = (array) $menu_item;

    $menu_item = array(
        'id'          => abs( $item['ID'] ),
        'wordpress_id'          => abs( $item['ID'] ),
        'order'       => (int) $item['menu_order'],
        'parent'      => abs( $item['menu_item_parent'] ),
        'title'       => $item['title'],
        'pathname'    => $item['url'],
        'attr'        => $item['attr_title'],
        'target'      => $item['target'],
        'classes'     => implode( ' ', $item['classes'] ),
        'xfn'         => $item['xfn'],
        'description' => $item['description'],
        'object_id'   => abs( $item['object_id'] ),
        'object'      => $item['object'],
        'object_slug' => get_post( $item['object_id'] )->post_name,
        'type'        => $item['type'],
        'type_label'  => $item['type_label'],
        'acf'         => get_fields($item['ID'] ) ?  get_fields($item['ID'] ) : null
    );

    if ( $children === true && ! empty( $menu ) ) {
        $menu_item['children'] = wordlify_get_nav_menu_item_children( $item['ID'], $menu );
    }

    return apply_filters( 'rest_menus_wordlify_format_menu_item', $menu_item );
}

/**
 * Get menus.
 *
 * @since  1.2.0
 * @return array All registered menus
 * borrowed from wp-api-menus plugin
 */
function wordlify_get_menus() {
    $wp_menus = wp_get_nav_menus();

    $i = 0;
    $rest_menus = array();
    foreach ( $wp_menus as $wp_menu ) :

        $menu = (array) $wp_menu;

        $id = $menu['term_id'];

        $rest_menus[ $i ]                = $menu;
        $rest_menus[ $i ]['ID']          = $id;
        $rest_menus[ $i ]['wordpress_id']          = $id;
        $rest_menus[ $i ]['name']        = $menu['name'];
        $rest_menus[ $i ]['slug']        = $menu['slug'];
        $rest_menus[ $i ]['description'] = $menu['description'];
        $rest_menus[ $i ]['count']       = $menu['count'];


        $wp_menu_items  = $id ? wp_get_nav_menu_items( $id ) : array();


        $rest_menu_items = array();
        foreach ( $wp_menu_items as $item_object ) {
            $rest_menu_items[] = wordlify_format_menu_item( $item_object );
        }

        $rest_menu_items = wordlify_nested_menu_items($rest_menu_items, 0);
        $rest_menus[ $i ]['items']       = $rest_menu_items;


        $i ++;
    endforeach;

    return $rest_menus;
}

add_action('wp_update_nav_menu', 'commitMenus');
function commitMenus($id) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    global $branch;

    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    $menu_object = wp_get_nav_menu_object($id);
    $title = $menu_object->name;
    
    $base_path = "wordsby/data/";
    
    $client = getGitlabClient();

    if (!$client) return;
    
    $menus_action = isFileInRepo($client, $base_path, 'menus.json') 
    ? 'update' : 'create';

    $menus = json_encode(
        wordlify_get_menus(), JSON_UNESCAPED_SLASHES
    );

    $url = preg_quote(get_site_url(), "/");

    $menus_content = preg_replace(
        "/$url/", '', makeImagesRelative($menus)
    );

    $commit = $client->api('repositories')->createCommit(WORDSBY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => "Menu $menus_action \"$title\" [id:$id] — by $username (from $site_url)",
        'actions' => array(
            array(
                'action' => $menus_action,
                'file_path' => $base_path . "menus.json",
                'content' => $menus_content,
                'encoding' => 'text'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    return $commit; 

}

?>