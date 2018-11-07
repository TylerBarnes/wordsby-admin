// check this github issue for the php preview endpoint implementation
// https://github.com/WP-API/WP-API/issues/2624

(function($) {
  $(document).ready(function() {
    //     var previewButton = $(".preview.button");
    //     var previewLink = previewButton.attr("href");

    //     previewButton.remove();

    //     var previewContainer = $("#preview-action");

    //     previewContainer.append(`
    //     <button class="button" data-href="${previewLink}">Preview</button>`);

    //     $(previewButton).on("click", function(e) {
    //       e.preventDefault();
    //     });
    $("#page_template").on("change", function() {
      var selected = $("#page_template").find(":selected");
      $("#page_template option").removeAttr("selected");
      $(selected).attr("selected", "selected");
    });
  });
})(jQuery);
