<?php 
function rrmdir($dir) { 
  if (is_dir($dir)) { 
    $objects = scandir($dir); 
    foreach ($objects as $object) { 
      if ($object != "." && $object != "..") { 
        if (is_dir($dir."/".$object))
          rrmdir($dir."/".$object);
        else
          unlink($dir."/".$object); 
      } 
    }
    rmdir($dir); 
  } 
}

add_action('init', 'recievePreviews');

function recievePreviews() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if(!isset($_POST['gatsbypress_previews']) && !isset($_POST['gatsbypress_preview_keycheck'])) {
    return;
  };

  if (!defined("GATSBYPRESS_PRIVATE_KEY")) {
    write_log('GATSBYPRESS_PRIVATE_KEY not defined in wp-config.php');
    echo json_encode('GATSBYPRESS_PRIVATE_KEY not defined');
    return;
  };

  $apikey = isset($_POST['apikey']) ? $_POST['apikey'] : false;

  if ($apikey !== GATSBYPRESS_PRIVATE_KEY) {
      write_log('wrong api key used in gatsbypress..');
      header('HTTP/1.1 401 unauthorized');
      echo json_encode('Wrong key..');
      return;
  } else {
    $wproot = get_home_path();
    $previews_path = $wproot . "preview/";

    if (isset($_POST['gatsbypress_preview_keycheck'])) {
      // api key == to private key + checking the key
      echo json_encode('success');
      return;
    };

    if (!isset($_FILES)) {
      return;
    };

    $temp_path = $_FILES['previews']['tmp_name'];

    if (file_exists($previews_path)) {
      rrmdir($previews_path);
    }

    $zip = new ZipArchive;
    if ($zip->open($temp_path) === TRUE) {
        $zip->extractTo($previews_path);
        $zip->close();
    } else {
        write_log('gatsbypress previews unzip failed');
        header('HTTP/1.1 500 Preview unzip failed');
            header('Content-Type: application/json; charset=UTF-8');
            wp_die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
            return json_encode(array('message' => 'ERROR', 'code' => 1337));
    }
  };
  }
}
?>