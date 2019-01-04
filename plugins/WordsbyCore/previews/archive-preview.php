<?php wp_head(); ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Preview</title>
    <style>
        #wpadminbar, .gp-actions {
            display: none;
        }
        iframe {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
    <script src="<?php echo get_stylesheet_directory_uri(); ?>/plugins/WordsbyCore/previews/js/lib/post-robot.js"></script>
</head>
<body>
<?php $available_template = htmlspecialchars($_GET['available_template']); ?>
<?php $id = htmlspecialchars($_GET['id']); ?>
<?php $query_vars = htmlspecialchars($_SERVER['QUERY_STRING']); ?>
<?php $frontend_url = get_field('build_site_url', 'option'); ?>
<?php $frontend_url_trailing_slash = rtrim($frontend_url, '/') . '/'; ?>
<?php if ($frontend_url): ?>
    <iframe 
    id='preview'
    src="<?php 
     echo $frontend_url_trailing_slash;
     ?>preview/<?php 
     echo $available_template; 
     ?>" 
     frameborder="0"></iframe>
<?php endif; ?>
</body>
</html>
<?php wp_footer(); ?>

<script>
const urlParams =
    typeof window !== "undefined"
    ? new URLSearchParams(window.location.search)
    : false;
const rest_base = urlParams.get("rest_base");
const post_id = urlParams.get("id");
const nonce = urlParams.get("nonce");

const rest_url = 
`/wp-json/wp/v2/${rest_base}/${post_id}/preview/?_wpnonce=${nonce}`;
console.log("rest_url", rest_url);

const iframe = document.getElementById('preview');

fetch(rest_url)
  .then(res => {
    console.log("response", res);
    return res.json();
  })
  .then(res => {
    console.log("json response", res);

    if (res && res.ID) {
      console.log("Updating preview data");
      postRobot.on('iframeReadyForData', event => {
          console.log('iframe ready');

          const iframe = document.getElementById('preview').contentWindow;
          console.log(iframe);

        postRobot
            .send(
                iframe,
                "previewDataLoaded",
                {
                    previewData: res
                },
                { domain: "<?php echo $frontend_url; ?>" }
            )
            .then(event => {
            //   this.loading.style.display = "none";
            //   this.iframe.style.height = `${event.data.height}px`;
            });
      })
    //   this.setState({ previewData: res });
    } else if (res && res.code) {
    //   this.setState({
    //     error: {
    //       title: "Oh no! <br> There's been an error :(",
    //       message: `To fix: <br>
    //                 Close this page, log out of WordPress, log back in, and try again.<br>
    //                 If the issue persists, copy this message and send it to your web developer.`,
    //       error: res
    //     }
    //   });
    } else {
    //   this.setState({
    //     error: {
    //       title: "Dang,",
    //       message:
    //         "<h3>looks like something went wrong!</h3><br> There was no response from the server. <br>Contact your web developer for help."
    //     }
    //   });
    }
  })
  .catch(error => console.warn(error));
</script>