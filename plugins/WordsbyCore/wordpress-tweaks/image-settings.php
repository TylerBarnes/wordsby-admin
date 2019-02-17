<?php 
// dont compress jpegs
add_filter('jpeg_quality', function() {
    return 100;
});
?>