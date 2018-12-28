<?php 

function getOptionsJSON() {
    return makeImagesRelative(
        json_encode(
            custom_api_get_all_options_callback(),
            JSON_UNESCAPED_SLASHES
        )
    );
}

?>