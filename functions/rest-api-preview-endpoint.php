<?php
// used ->https://github.com/WP-API/WP-API/issues/2624
// https://www.envano.com/2014/10/creating-a-custom-preview-post-page-in-wordpress/
// https://stackoverflow.com/questions/21544161/wordpress-query-for-preview

/**
 * Custom class designed to mostly mimic `WP_REST_Posts_Controller`, but allows
 * querying for post previews.
 *
 * New filters:
 *  - `rest_multiple_post_type_query` Filters the query arguments as generated
 *    from the request parameters.
 *
 * @author Ruben Vreeken
 */
class WP_REST_Post_Preview_Controller extends WP_REST_Posts_Controller
{

    /**
     * Post type.
     *
     * @since 4.7.0
     * @access protected
     * @var string
     */
    protected $post_type;

    /**
     * Instance of a post meta fields object.
     *
     * @since 4.7.0
     * @access protected
     * @var WP_REST_Post_Meta_Fields
     */
    protected $meta;

    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $post_type Post type.
     */
    public function __construct($post_type)
    {
        $this->post_type = $post_type;
        $this->namespace = 'wp/v2';
        $obj             = get_post_type_object($post_type);
        $this->rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;

        $this->meta = new WP_REST_Post_Meta_Fields($this->post_type);
    }

    /**
     * Registers the preview routes.
     *
     * @since 4.7.0
     * @access public
     *
     * @see register_rest_route()
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/preview', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'get_item_permissions_check'),
            ),
            'schema' => array($this, 'get_public_item_schema'),
        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/preview', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_items'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
                'args'                => $this->get_collection_params(),
            ),
        ));
    }

    /**
     * Checks if a given request has access to read post previews.
     *
     * @since 4.7.0
     * @access public
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {

        $post_type = get_post_type_object($this->post_type);

        if (!current_user_can($post_type->cap->edit_posts)) {
            return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to preview these posts in this post type'), array('status' => rest_authorization_required_code()));
        }

        return true;
    }

    /**
     * Retrieves a collection of post previews.
     *
     * @since 4.7.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items($request)
    {

        // Ensure an include parameter is set in case the orderby is set to 'include'.
        if (!empty($request['orderby']) && 'include' === $request['orderby'] && empty($request['include'])) {
            return new WP_Error('rest_orderby_include_missing_include', sprintf(__('Missing parameter(s): %s'), 'include'), array('status' => 400));
        }

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();
        $args       = array();

        /*
         * This array defines mappings between public API query parameters whose
         * values are accepted as-passed, and their internal WP_Query parameter
         * name equivalents (some are the same). Only values which are also
         * present in $registered will be set.
         */
        $parameter_mappings = array(
            'exclude'        => 'post__not_in',
            'include'        => 'post__in',
            'offset'         => 'offset',
            'order'          => 'order',
            'orderby'        => 'orderby',
            'page'           => 'paged',
            'parent'         => 'post_parent__in',
            'parent_exclude' => 'post_parent__not_in',
            'slug'           => 'post_name__in',
        );

        /*
         * For each known parameter which is both registered and present in the request,
         * set the parameter's value on the query $args.
         */
        foreach ($parameter_mappings as $api_param => $wp_param) {
            if (isset($registered[$api_param], $request[$api_param])) {
                $args[$wp_param] = $request[$api_param];
            }
        }

        // Ensure our per_page parameter overrides any provided posts_per_page filter.
        if (isset($registered['per_page'])) {
            $args['posts_per_page'] = $request['per_page'];
        }

        // Force the post_type argument, since it's not a user input variable.
        $args['post_type'] = $this->post_type;

        /**
         * Filters the query arguments for a request.
         *
         * Enables adding extra arguments or setting defaults for a post collection request.
         *
         * @since 4.7.0
         *
         * @link https://developer.wordpress.org/reference/classes/wp_query/
         *
         * @param array           $args    Key value array of query var to query value.
         * @param WP_REST_Request $request The request used.
         */
        $args       = apply_filters("rest_{$this->post_type}_preview_query", $args, $request);
        $query_args = $this->prepare_items_query($args, $request);

        // Load the `parent` posts first (The regular version of the posts)
        $parent_query = new WP_Query();
        $query_result = $parent_query->query($query_args);

        // Keep the posts we're allowed to preview
        $parents = [];
        foreach ($query_result as $parent) {
            if (!$this->check_update_permission($parent)) {
                continue;
            }
            $parents[$parent->ID] = $parent;
        }

        // Now try to load the preview revisions
        $revision_query = new WP_Query();
        add_filter('posts_join', array($this, 'revisions_join_newest_revision'), 10, 2);
        $query_result = $revision_query->query([
            'post_type'       => 'revision',
            'post_status'     => 'inherit',
            'post_parent__in' => array_keys($parents),
            'paged'           => $request['page'],
            'posts_per_page'  => $request['per_page'],
        ]);
        remove_filter('posts_join', array($this, 'revisions_join_newest_revision'));

        // Index the revisions by post_parent (the ID of the regular version of the posts)
        $revisions = [];
        foreach ($query_result as $revision) {
            $revisions[$revision->post_parent] = $revision;
        }

        // Prepare results
        $posts = [];
        foreach ($parents as $post_id => $parent) {
            $revision = isset($revisions[$post_id]) ? $revisions[$post_id] : null;
            $data     = $this->prepare_item_for_response($parent, $request, $revision);
            $posts[]  = $this->prepare_response_for_collection($data);
        }

        // Calc total post count
        $page        = (int) $query_args['paged'];
        $total_posts = $parent_query->found_posts;

        if ($total_posts < 1) {
            // Out-of-bounds, run the query again without LIMIT for total count
            unset($query_args['paged']);

            $count_query = new WP_Query();
            $count_query->query($query_args);
            $total_posts = $count_query->found_posts;
        }

        $max_pages = ceil($total_posts / (int) $query_args['posts_per_page']);
        $response  = rest_ensure_response($posts);

        $response->header('X-WP-Total', (int) $total_posts);
        $response->header('X-WP-TotalPages', (int) $max_pages);

        $request_params = $request->get_query_params();
        $base           = add_query_arg($request_params, rest_url(sprintf('/%s/%s/preview', $this->namespace, $this->rest_base)));

        if ($page > 1) {
            $prev_page = $page - 1;

            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }

            $prev_link = add_query_arg('page', $prev_page, $base);
            $response->link_header('prev', $prev_link);
        }
        if ($max_pages > $page) {
            $next_page = $page + 1;
            $next_link = add_query_arg('page', $next_page, $base);

            $response->link_header('next', $next_link);
        }

        return $response;
    }

    /**
     * Select the newest existing revisions for each post parent. Then join such
     * that the selected posts match both post_parent and post_modified of these
     * revisions.
     *
     * @since 4.7.0
     * @access public
     *
     * @return string  The new JOIN clause of the query.
     */
    public function revisions_join_newest_revision()
    {
        global $wpdb;

        return "
        JOIN (
            SELECT
                $wpdb->posts.post_parent,
                MAX($wpdb->posts.post_modified) post_modified
            FROM
                $wpdb->posts
            GROUP BY
                $wpdb->posts.post_parent
        ) last_revision ON (
            $wpdb->posts.post_modified=last_revision.post_modified
        AND
            $wpdb->posts.post_parent=last_revision.post_parent
        )
        ";
    }

    /**
     * Checks if a given request has access to read a post preview.
     *
     * @since 4.7.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check($request)
    {

        $post = get_post((int) $request['id']);

        if ($post && !$this->check_update_permission($post)) {
            return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to preview this post'), array('status' => rest_authorization_required_code()));
        }

        if ($post) {
            return $this->check_read_permission($post);
        }

        return true;
    }

    /**
     * Retrieves a single post preview.
     *
     * @since 4.7.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item($request)
    {
        $id = (int) $request['id'];

        // Load the `parent` post first (the regular version of the post)
        $parent_query = new WP_Query();
        $query_result = $parent_query->query([
            'post_type'      => $this->post_type,
            'p'              => $id,
            'page'           => 1,
            'posts_per_page' => 1,
        ]);
        $parent = count($query_result) ? reset($query_result) : null;

        // Check for valid post
        if (!$parent || empty($id) || empty($parent->ID) || $this->post_type !== $parent->post_type) {
            return new WP_Error('rest_post_invalid_id', __('Invalid post id.'), array('status' => 404));
        }

        // Now try to load a preview revision
        $revision_query = new WP_Query();
        $query_result   = $revision_query->query([
            'post_type'      => 'revision',
            'post_status'    => 'inherit',
            'post_parent'    => $id,
            'orderby'        => 'post_modified',
            'order'          => 'desc',
            'page'           => 1,
            'posts_per_page' => 1,
        ]);
        $revision = count($query_result) ? reset($query_result) : null;

        $data     = $this->prepare_item_for_response($parent, $request, $revision);
        $response = rest_ensure_response($data);

        if (is_post_type_viewable(get_post_type_object($parent->post_type))) {
            $response->link_header('alternate', get_permalink($id), array('type' => 'text/html'));
        }

        return $response;
    }

    /**
     * Determines the allowed query_vars for a get_items() response and prepares
     * them for WP_Query.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param array           $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
     * @param WP_REST_Request $request       Optional. Full details about the request.
     * @return array Items query arguments.
     */
    protected function prepare_items_query($prepared_args = array(), $request = null)
    {
        $query_args = array();

        foreach ($prepared_args as $key => $value) {
            /**
             * Filters the query_vars used in get_items() for the constructed query.
             *
             * The dynamic portion of the hook name, `$key`, refers to the query_var key.
             *
             * @since 4.7.0
             *
             * @param string $value The query_var value.
             */
            $query_args[$key] = apply_filters("rest_query_var-{$key}", $value);
        }

        // Map to proper WP_Query orderby param.
        if (isset($query_args['orderby']) && isset($request['orderby'])) {
            $orderby_mappings = array(
                'id'      => 'ID',
                'include' => 'post__in',
                'slug'    => 'post_name',
            );

            if (isset($orderby_mappings[$request['orderby']])) {
                $query_args['orderby'] = $orderby_mappings[$request['orderby']];
            }
        }

        return $query_args;
    }

    /**
     * Prepare a single post output for response.
     *
     * @since 4.7.0
     * @access public
     *
     * @param WP_Post           $parent     Post object.
     * @param WP_REST_Request   $request    Request object.
     * @param WP_Post|null      $revision   Preview revision of Post object.
     *
     * @return WP_REST_Response $data
     */
    public function prepare_item_for_response($parent, $request, $revision = null)
    {
        // Set the global post to the parent, some plugins might use this
        // global $post;
        // $post = $parent;
        $GLOBALS['post'] = $parent;
        setup_postdata($parent);

        // If we have no preview revision, just use the parent and pretend we
        // have one. This keeps things simple going forward.
        $preview = $revision instanceof WP_Post ? $revision : $parent;

        $schema = $this->get_item_schema();

        // Base fields for every post.
        $data = array();


        $data['gatsby'] = posts_formatted_for_gatsby($parent->ID, true);

        if (!empty($schema['properties']['id'])) {
            $data['id']        = $parent->ID;
            $data['previewId'] = $preview->ID;
        }

        if (!empty($schema['properties']['date'])) {
            $data['date'] = $this->prepare_date_response($parent->post_date_gmt, $parent->post_date);
        }

        if (!empty($schema['properties']['date_gmt'])) {
            $data['date_gmt'] = $this->prepare_date_response($parent->post_date_gmt);
        }

        if (!empty($schema['properties']['guid'])) {
            $data['guid'] = array(
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters('get_the_guid', $parent->guid),
                'raw'      => $parent->guid,
            );
        }

        if (!empty($schema['properties']['modified'])) {
            $data['modified'] = $this->prepare_date_response($preview->post_modified_gmt, $preview->post_modified);
        }

        if (!empty($schema['properties']['modified_gmt'])) {
            $data['modified_gmt'] = $this->prepare_date_response($preview->post_modified_gmt);
        }

        if (!empty($schema['properties']['password'])) {
            $data['password'] = $parent->post_password;
        }

        if (!empty($schema['properties']['slug'])) {
            $data['slug'] = $parent->post_name;
        }

        if (!empty($schema['properties']['status'])) {
            $data['status'] = $preview->post_status;
        }

        if (!empty($schema['properties']['type'])) {
            $data['type'] = $preview->post_type;
        }

        if (!empty($schema['properties']['link'])) {
            $data['link'] = get_permalink($parent->ID);
        }

        if (!empty($schema['properties']['title'])) {
            add_filter('protected_title_format', array($this, 'protected_title_format'));

            $data['title'] = array(
                'raw'      => $preview->post_title,
                'rendered' => get_the_title($preview->ID),
            );

            remove_filter('protected_title_format', array($this, 'protected_title_format'));
        }

        $has_password_filter = false;

        if ($this->can_access_password_content($post, $request)) {
            // Allow access to the post, permissions already checked before.
            add_filter('post_password_required', '__return_false');

            $has_password_filter = true;
        }

        if (!empty($schema['properties']['content'])) {
            $data['content'] = array(
                'raw'       => $preview->post_content,
                /** This filter is documented in wp-includes/post-template.php */
                'rendered'  => post_password_required($parent) ? '' : apply_filters('the_content', $preview->post_content),
                'protected' => (bool) $parent->post_password,
            );
        }

        if (!empty($schema['properties']['excerpt'])) {
            /** This filter is documented in wp-includes/post-template.php */
            $excerpt         = apply_filters('the_excerpt', apply_filters('get_the_excerpt', $preview->post_excerpt, $preview));
            $data['excerpt'] = array(
                'raw'       => $preview->post_excerpt,
                'rendered'  => post_password_required($parent) ? '' : $excerpt,
                'protected' => (bool) $parent->post_password,
            );
        }

        if ($has_password_filter) {
            // Reset filter.
            remove_filter('post_password_required', '__return_false');
        }

        if (!empty($schema['properties']['author'])) {
            $data['author'] = (int) $preview->post_author;
        }

        if (!empty($schema['properties']['featured_media'])) {
            $data['featured_media'] = (int) get_post_thumbnail_id($preview->ID);
        }

        if (!empty($schema['properties']['parent'])) {
            $data['parent'] = (int) $parent->post_parent;
        }

        if (!empty($schema['properties']['menu_order'])) {
            $data['menu_order'] = (int) $preview->menu_order;
        }

        if (!empty($schema['properties']['comment_status'])) {
            $data['comment_status'] = $preview->comment_status;
        }

        if (!empty($schema['properties']['ping_status'])) {
            $data['ping_status'] = $preview->ping_status;
        }

        if (!empty($schema['properties']['sticky'])) {
            $data['sticky'] = is_sticky($preview->ID);
        }

        if (!empty($schema['properties']['template'])) {
            if ($template = get_page_template_slug($preview->ID)) {
                $data['template'] = $template;
            } else {
                $data['template'] = '';
            }
        }

        if (!empty($schema['properties']['format'])) {
            $data['format'] = get_post_format($preview->ID);

            // Fill in blank post format.
            if (empty($data['format'])) {
                $data['format'] = 'standard';
            }
        }

        if (!empty($schema['properties']['meta'])) {
            $data['meta'] = $this->meta->get_value($preview->ID, $request);
        }

        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), array('show_in_rest' => true));

        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

            if (!empty($schema['properties'][$base])) {
                $terms       = get_the_terms($preview, $taxonomy->name);
                $data[$base] = $terms ? array_values(wp_list_pluck($terms, 'term_id')) : array();
            }
        }

        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, 'view');

        // Wrap the data in a response object.
        $response = rest_ensure_response($data);

        $response->add_links($this->prepare_links($parent, $revision));

        // Restore whatever used to be in global post
        // wp_reset_postdata();

        /**
         * Filter the post data for a response.
         *
         * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
         * prepared for the response.
         *
         * @param WP_REST_Response   $response   The response object.
         * @param WP_Post            $preview    The preview revision if available, regular Post object otherwise.
         * @param WP_REST_Request    $request    Request object.
         */
        return apply_filters("rest_prepare_{$this->post_type}", $response, $preview, $request);
    }

    /**
     * Prepare links for the request.
     *
     * @param WP_Post        $parent     Post object.
     * @param WP_Post|null   $revision   Preview revision of Post object.
     *
     * @return array Links for the given post.
     */
    protected function prepare_links($parent, $revision = null)
    {
        // If we have no preview revision, just use the parent and pretend we
        // have one. This keeps things simple going forward.
        $preview = $revision instanceof WP_Post ? $revision : $parent;

        $base = sprintf('%s/%s', $this->namespace, $this->rest_base);

        // Entity meta
        $links = array(
            'self'       => array(
                'href' => rest_url(trailingslashit($base) . $parent->ID),
            ),
            'collection' => array(
                'href' => rest_url($base),
            ),
            'about'      => array(
                'href' => rest_url('/wp/v2/types/' . $this->post_type),
            ),
        );

        if ((in_array($parent->post_type, array('post', 'page')) || post_type_supports($parent->post_type, 'author'))
            && !empty($preview->post_author)) {
            $links['author'] = array(
                'href'       => rest_url('/wp/v2/users/' . $preview->post_author),
                'embeddable' => true,
            );
        };

        if (in_array($parent->post_type, array('post', 'page')) || post_type_supports($parent->post_type, 'comments')) {
            $replies_url      = rest_url('/wp/v2/comments');
            $replies_url      = add_query_arg('post', $parent->ID, $replies_url);
            $links['replies'] = array(
                'href'       => $replies_url,
                'embeddable' => true,
            );
        }

        if (in_array($parent->post_type, array('post', 'page')) || post_type_supports($parent->post_type, 'revisions')) {
            $links['version-history'] = array(
                'href' => rest_url(trailingslashit($base) . $parent->ID . '/revisions'),
            );
        }

        $post_type_obj = get_post_type_object($parent->post_type);

        if ($post_type_obj->hierarchical && !empty($parent->post_parent)) {
            $links['up'] = array(
                'href'       => rest_url(trailingslashit($base) . (int) $parent->post_parent),
                'embeddable' => true,
            );
        }

        // If we have a featured media, add that.
        if ($featured_media = get_post_thumbnail_id($parent->ID)) {
            $image_url                                = rest_url('wp/v2/media/' . $featured_media);
            $links['https://api.w.org/featuredmedia'] = array(
                'href'       => $image_url,
                'embeddable' => true,
            );
        }

        if (!in_array($parent->post_type, array('attachment', 'nav_menu_item', 'revision'))) {
            $attachments_url = rest_url('wp/v2/media');
            $attachments_url = add_query_arg('parent', $parent->ID, $attachments_url);

            $links['https://api.w.org/attachment'] = array(
                'href' => $attachments_url,
            );
        }

        $taxonomies = get_object_taxonomies($parent->post_type);

        if (!empty($taxonomies)) {
            $links['https://api.w.org/term'] = array();

            foreach ($taxonomies as $tax) {
                $taxonomy_obj = get_taxonomy($tax);

                // Skip taxonomies that are not public.
                if (empty($taxonomy_obj->show_in_rest)) {
                    continue;
                }

                $tax_base = !empty($taxonomy_obj->rest_base) ? $taxonomy_obj->rest_base : $tax;

                $terms_url = add_query_arg(
                    'post',
                    $parent->ID,
                    rest_url('wp/v2/' . $tax_base)
                );

                $links['https://api.w.org/term'][] = array(
                    'href'       => $terms_url,
                    'taxonomy'   => $tax,
                    'embeddable' => true,
                );
            }
        }

        return $links;
    }

    /**
     * Get the query params for collections of attachments.
     *
     * @return array
     */
    public function get_collection_params()
    {
        $params = parent::get_collection_params();

        // The collection params for previews are severely restricted.
        $params = array_intersect_key($params, array_flip(array(
            'include',
            'exclude',
            'slug',
            'parent',
            'parent_exclude',
            'offset',
            'page',
            'per_page',
        )));

        return $params;
    }

}

add_action( 'rest_api_init', 'register_preview_route' );
function register_preview_route() {
	foreach (get_post_types(array('show_in_rest' => true), 'objects') as $post_type) {
		$controller = new WP_REST_Post_Preview_Controller($post_type->name);
		$controller->register_routes();
	}
}
?>