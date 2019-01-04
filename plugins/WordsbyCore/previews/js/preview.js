// check this github issue for the php preview endpoint implementation
// https://github.com/WP-API/WP-API/issues/2624

(function($) {
  $(document).ready(function() {
    const originalurl = $(".preview.button").attr("href");

    if (originalurl.indexOf("localhost=true") !== -1) {
      $(".preview.button").html("Preview Changes Locally");
    }

    if (originalurl) {
      const querystring = originalurl.split("?")[1];
      const bodyClasses = $("body")
        .attr("class")
        .split(" ");

      const postType = bodyClasses
        .filter(function(classname) {
          return (
            typeof classname == "string" && classname.indexOf("post-type-") > -1
          );
        })[0]
        .replace("post-type-", "");

      const doesDefaultSingleExist = !!availableTemplates["single/" + postType];

      const availableDefaultTemplate = doesDefaultSingleExist
        ? "single/" + postType
        : "single/index";

      function getPreviewUrl(selectedTemplate) {
        return "/preview/" + selectedTemplate + "/?" + querystring;
      }

      function setPreviewButtonLink(selectedTemplate) {
        const previewUrl = getPreviewUrl(selectedTemplate);

        $(".preview.button").attr("href", previewUrl);
      }

      setPreviewButtonLink(availableDefaultTemplate);

      $("#page_template").on("change", function() {
        const selected = $("#page_template").find(":selected");
        const selectedAttr = selected.attr("value");
        const selectedTemplate =
          selectedAttr === "default" ? availableDefaultTemplate : selectedAttr;

        setPreviewButtonLink(selectedTemplate);
      });
    }
  });
})(jQuery);
