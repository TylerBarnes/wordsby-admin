<?php get_header(); ?>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/PsychicWindow/post-robot.js"></script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/PsychicWindow/ResizeSensor.js"></script>
<style>
    html, body {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    #wpadminbar, .gp-actions, #footer[role="contentinfo"] {
        display: none;
    }
</style>

<div id="psychic-contents">
<?php 
    if ( have_posts() ) {
        
        while ( have_posts() ) { 
            the_post();
            the_content();
        }

        wp_reset_postdata();
    } 
?>
</div>

<?php get_footer(); ?>

<script>
    function getHeight() {
        return jQuery('#psychic-contents').height() + 50;
    }
    postRobot.on('iframeDomElementLoaded', function(event) {
        var css = event.data.css;

        var head = document.head || document.getElementsByTagName('head')[0],
        style = document.createElement('style');

        style.type = 'text/css';
        style.appendChild(document.createTextNode(css));
        head.appendChild(style);
    

        return {
            height: getHeight()
        };
    });

    // jQuery(window).bind("load", function() {
    //     postRobot.send(window.parent,
    //             "iframeWindowOnload" + "<?php echo $_SERVER['REQUEST_URI']; ?>",
    //             {
    //                 height: getHeight()
    //             }
    //             // { domain: "<?php echo $frontend_url_no_trailing_slash; ?>" }
    //         )
    // });

    // console.log("Child: <?php echo $_SERVER['REQUEST_URI']; ?>");

    function sendSize() {
        postRobot.send(window.parent,
            "iframeHeightWillChange" + "<?php echo $_SERVER['REQUEST_URI']; ?>",
            {
                height: 'unset'
            }
            // { domain: "<?php echo $frontend_url_no_trailing_slash; ?>" }
        )
        .then(event => {
            // setTimeout(() => {
                postRobot.send(window.parent,
                    "iframeHeightChanged" + "<?php echo $_SERVER['REQUEST_URI']; ?>",
                    {
                        height: getHeight()
                    }
                    // { domain: "<?php echo $frontend_url_no_trailing_slash; ?>" }
                )
                .then(event => {
                    // console.log('Child: sent changed height ' + getHeight() + ' from the iframe to the parent.');
                });
            // }, 10);
        });
    }

    new ResizeSensor(document.getElementById('psychic-contents'), sendSize);
</script>