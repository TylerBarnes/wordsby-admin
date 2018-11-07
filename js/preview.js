// check this github issue for the php preview endpoint implementation
// https://github.com/WP-API/WP-API/issues/2624

(function($) {
  $(document).ready(function() {
    var previewButton = $(".preview.button");

    $(previewButton).on("click", function(e) {
      e.preventDefault();
    });
  });
})(jQuery);
