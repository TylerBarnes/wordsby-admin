<?php 

function getCollectionsJSON() {
    return makeImagesRelative(
        json_encode(
            posts_formatted_for_gatsby(false), 
            JSON_UNESCAPED_SLASHES
        )
    );
}

?>