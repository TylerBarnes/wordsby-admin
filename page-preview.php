<?php 
$post_id = $_GET['preview'];
$rest_base = $_GET['rest_base'];
$nonce = $_GET['_wpnonce'];
$rest_url = get_site_url() . "/wp-json/wp/v2/$rest_base/$post_id/preview/?_wpnonce=$nonce";

?>
<script>
    fetch("<?php echo $rest_url; ?>")
        .then(res => res.json())
        .then(res => console.log(res));
</script>