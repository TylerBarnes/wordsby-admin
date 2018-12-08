jQuery(document).ready(function($) {
  // begin menu diagonal hover fix
  var $menu = $(".betternav-menu");

  $(".betternav").mouseleave(function() {
    console.log("hide!");
    $(".submenu:visible").hide();
    menuAimInit();
  });

  function menuAimInit() {
    $menu.menuAim({
      activate: activateSubmenu,
      deactivate: deactivateSubmenu,
      rowSelector: ".betternav__section-title--has-children"
    });

    $(".submenu").menuAim({
      activate: activateSubmenu,
      deactivate: deactivateSubmenu,
      // rowSelector: ".betternav__section-title--has-children",
      rowSelector: "> article"
    });
  }

  menuAimInit();

  function activateSubmenu(row) {
    $submenu = $(row).children(".betternav__section-menu, .betternav__submenu");
    if (!$submenu.length) return;

    $submenu.css({
      display: "flex"
    });

    if (!$submenu.hasClass("positioned")) {
      var viewportHeight = $(window).height();
      var menuHeight = $submenu.outerHeight();

      var menuOffset = $submenu.offset().top - $(window).scrollTop();
      var isOverlappingBottom = menuOffset + menuHeight > viewportHeight;

      if (isOverlappingBottom) {
        var overlapDistance = menuOffset + menuHeight - viewportHeight;
        $submenu.css({ top: "-" + overlapDistance + "px" });
        $submenu.addClass("positioned");
      }
    }
  }
  function deactivateSubmenu(row) {
    $submenu = $(row).children(".betternav__section-menu, .betternav__submenu");
    $submenu.css("display", "none");
  }

  // end menu diagonal hover fix

  // begin menu vertical window overlap fix

  // end menu vertical window overlap fix
});
