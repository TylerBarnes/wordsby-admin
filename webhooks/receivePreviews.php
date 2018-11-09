<?php 
require( '../../../../wp-load.php' );
require( '../../../../wp-admin/includes/file.php' );

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    write_log("someone tried to access gatsbypress receivePreviews.php directly");
    die();
}

$apikey = isset($_POST['apikey']) ? $_POST['apikey'] : false;
if ($apikey !== 'yep') {
    write_log('wrong api key used in gatsbypress..');
    die();
};

$wproot = get_home_path();
$previews_path = $wproot . "preview/";

if (!isset($_FILES)) die();

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
      header('HTTP/1.1 500 Internal Server Booboo');
          header('Content-Type: application/json; charset=UTF-8');
          die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
  }





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
?>