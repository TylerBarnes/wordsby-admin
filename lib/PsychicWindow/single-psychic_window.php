<?php get_header(); ?>
<script src="/wp-content/themes/gatsby-wordpress-admin-theme/lib/PsychicWindow/post-robot.js"></script>
<?php 
// Psychic Window single template
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();

        the_content();
    }
    wp_reset_postdata();
} ?>

<script>
    postRobot.on('iframeDomElementLoaded', function() {
        postRobot.send(
        window.parent, 
        'psychicWindowLoaded', 
        { 
            height: document.body.scrollHeight 
        })
            .then(function(event) {
                var css = event.data.css;

                var head = document.head || document.getElementsByTagName('head')[0],
                style = document.createElement('style');

                style.type = 'text/css';
                style.appendChild(document.createTextNode(css));
                head.appendChild(style);

            }).catch(function(err) {
                // Handle any errors that stopped our call from going through
                console.error(err);
            });
    })
</script>