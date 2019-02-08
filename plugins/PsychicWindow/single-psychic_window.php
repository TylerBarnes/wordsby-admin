<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta
			name="viewport"
			content="width=device-width, initial-scale=1.0"
		>
		<meta
			http-equiv="X-UA-Compatible"
			content="ie=edge"
		>
		<title></title>
	</head>

	<body>
		<?php get_header(); ?>
		<script
			src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/PsychicWindow/post-robot.js"
		></script>
		<script
			src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/PsychicWindow/ResizeSensor.js"
		></script>
		<style>
		html,
		body {
			margin-top: 0 !important;
			margin-bottom: 0 !important;
		}

		#wpadminbar,
		.gp-actions,
		#footer[role="contentinfo"] {
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

			<?php $frontend_url = get_field('build_site_url', 'option'); ?>
			<?php $frontend_url_trailing_slash = rtrim($frontend_url, '/') . '/'; ?>
			<?php $frontend_url_no_trailing_slash = rtrim($frontend_url, '/'); ?>
		</div>

		<?php get_footer(); ?>
		<?php 
        $refferrer = $_SERVER['HTTP_REFERER'];
        
        if (strpos($refferrer, 'localhost')) {
            // localhost is requesting the iframe.
            $url = parse_url($refferrer);
            $scheme = $url['scheme'];
            $host = $url['host'];
            $port = $url['port'];
            $frontend_url_no_trailing_slash = "${scheme}://${host}:${port}";
        }

        if (
            defined('DANGEROUS__PSYCHIC_WINDOW_DEBUG') && DANGEROUS__PSYCHIC_WINDOW_DEBUG
            ) {
            $frontend_url_no_trailing_slash = "";
        }

            ?>
		<script>
		let lastHeight = false;
		let lastHeightRecurse = 0;
		let domain = "<?php echo $frontend_url_no_trailing_slash; ?>";
		postRobot.on(
			'iframeDomElementLoaded', {
				domain: domain
			},
			function(event) {
				var css = event.data.css;
				var head =
					document.head || document.getElementsByTagName('head')[0];
				var style = document.createElement('style');
				style.type = 'text/css';
				style.appendChild(document.createTextNode(css));
				head.appendChild(style);
				sendSize();
				return {
					url: "<?php echo $_SERVER['REQUEST_URI']; ?>"
				};
			}
		);

		function eventName(name) {
			// this scopes these events to this window in case of multiple psychic windows
			return name + "<?php echo $_SERVER['REQUEST_URI']; ?>";
		}

		function getHeight() {
			var page = document.getElementById('psychic-contents');
			var height = page.clientHeight;
			return height + 50;
		}

		function sendSize() {
			var height = getHeight();
			if (height !== lastHeight) heightChange(height);
		}

		function heightChange(height) {
			postRobot.send(window.parent,
				eventName("iframeHeightChanged"), {
					height: height
				}, {
					domain: domain
				}
			).then(function() {
				// this attempts to get the height again if it returns 0.
				if (height === 50 || !height || height === 0) {
					if (lastHeightRecurse <= 10) {
						lastHeightRecurse++;
						setTimeout(function() {
							heightChange(getHeight());
						}, 100);
					}
				} else {
					lastHeight = height;
				}
			})
		}

		function setUpResizeListener() {
			new MutationObserver(sendSize).observe(
				document.getElementById('psychic-contents'), {
					attributes: true,
					childList: false,
					subtree: true
				}
			);
		}
		setUpResizeListener();
		</script>

	</body>

</html>
<?php get_footer(); ?>
