jQuery(document).ready(function($) {
  //   var closeMenuTimeout;

  //   $(".betternav__section-title--has-no-children")
  //     .mouseenter(function() {
  //       console.log("hovering no children");
  //       closeMenuTimeout = setTimeout(function() {
  //         $(".betternav__section-menu").hide();
  //       }, 500);
  //     })
  //     .mouseleave(function() {
  //       clearTimeout(closeMenuTimeout);
  //     });

  var $menu = $(".betternav-menu");

  $(".betternav").mouseleave(function() {
    console.log("hide!");
    $(".submenu:visible").hide();
    menuAimInit();
  });

  // jQuery-menu-aim: <meaningful part of the example>
  // Hook up events to be fired on menu row activation.
  function menuAimInit() {
    $menu.menuAim({
      activate: activateSubmenu,
      deactivate: deactivateSubmenu,
      rowSelector: ".betternav__section-title--has-children"
    });

    $(".submenu").menuAim({
      activate: activateSubmenu,
      deactivate: deactivateSubmenu,
      rowSelector: ".betternav__section-title--has-children",
      rowSelector: "> article"
    });
  }

  menuAimInit();
  // jQuery-menu-aim: </meaningful part of the example>
  // jQuery-menu-aim: the following JS is used to show and hide the submenu
  // contents. Again, this can be done in any number of ways. jQuery-menu-aim
  // doesn't care how you do this, it just fires the activate and deactivate
  // events at the right times so you know when to show and hide your submenus.
  function activateSubmenu(row) {
    $submenu = $(row).children(".betternav__section-menu, .betternav__submenu");
    $submenu.css({
      display: "block"
      //   top: -1,
      //   left: width - 3, // main should overlay submenu
      //   height: height - 4 // padding for main dropdown's arrow
    });
  }
  function deactivateSubmenu(row) {
    $submenu = $(row).children(".betternav__section-menu, .betternav__submenu");
    $submenu.css("display", "none");
  }
});
