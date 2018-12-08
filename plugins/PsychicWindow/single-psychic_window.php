<?php get_header(); ?>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/PsychicWindow/post-robot.js"></script>
<?php 
    if ( have_posts() ) {
        
        while ( have_posts() ) { 
            the_post();
            the_content();
        }

        wp_reset_postdata();
    } 
?>

<script>
    postRobot.on('iframeDomElementLoaded', function(event) {
        var css = event.data.css;

        var head = document.head || document.getElementsByTagName('head')[0],
        style = document.createElement('style');

        style.type = 'text/css';
        style.appendChild(document.createTextNode(css));
        head.appendChild(style);
        return {
            height: document.body.scrollHeight
        };
    });
</script>