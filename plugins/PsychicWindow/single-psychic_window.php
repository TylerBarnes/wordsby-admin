<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
</head>
<body>
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
    jQuery(document).ready(function() {
        let lastHeight = false;
        let lastHeightRecurse = 0;

        setUpResizeListener();

        postRobot.on(
            'iframeDomElementLoaded',
            { 
                domain: "<?php echo $frontend_url_no_trailing_slash; ?>" 
            },  
            function(event) {
                var css = event.data.css;

                var head = document.head || document.getElementsByTagName('head')[0],
                style = document.createElement('style');

                style.type = 'text/css';
                style.appendChild(document.createTextNode(css));
                head.appendChild(style);

                setTimeout(() => {
                    sendSize();
                }, 500);
            }
        );

        function setUpResizeListener() {
            var observer = new MutationObserver(sendSize);
            observer.observe(
                document.getElementById('psychic-contents'), 
                { 
                    attributes: true, 
                    childList: false, 
                    subtree: true 
                }
            );
        }

        function eventName(name) {
            // this scopes these events to this window in case of multiple psychic windows
            return name + "<?php echo $_SERVER['REQUEST_URI']; ?>";
        }

        function getHeight() {
            return document.getElementById('psychic-contents').clientHeight + 50;
        }

        function sendSize() {
            var height = getHeight();
            if (height !== lastHeight) heightChange(height);
        }

        function heightChange(height) {
            postRobot.send(window.parent,
                eventName("iframeHeightChanged"),
                {
                    height: height
                },
                { 
                    domain: "<?php echo $frontend_url_no_trailing_slash; ?>" 
                }
            ).then(function() {
                // this fixes browsers that return a height of 0.
                if (height === 50 || !height || height === 0) {
                    if (lastHeightRecurse <= 5) {
                        lastHeightRecurse++;
                        setTimeout(function() {
                            heightChange(getHeight());
                        }, 50);
                    }
                } else {
                    lastHeight = height;
                }
            })
        }
    });
</script>

</body>
</html>