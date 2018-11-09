// check this github issue for the php preview endpoint implementation
// https://github.com/WP-API/WP-API/issues/2624

(function($) {
  $(document).ready(function() {
    const originalurl = $(".preview.button").attr("href");
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

    console.log(doesDefaultSingleExist);

    $("#page_template").on("change", function() {
      const selected = $("#page_template").find(":selected");
      const selectedAttr = selected.attr("value");
      const selectedTemplate =
        selectedAttr === "default" ? availableDefaultTemplate : selectedAttr;

      const previewUrl = "/preview/" + selectedTemplate + "/?" + querystring;

      $(".preview.button").attr("href", previewUrl);

      console.log(selectedTemplate);
    });
  });
})(jQuery);
