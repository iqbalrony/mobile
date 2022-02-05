(function ($) {
  "use strict";

  jQuery(document).ready(function ($) {

    // empty paragraphs
    // $('p:empty').remove();

    $(".portfolioFilter li a").click(function () {
      $(".portfolioFilter li.active").removeClass("active");
      $(this).parent().addClass('active');
    });

    $(document).on({
      mouseenter: function () {
        var elm = $('ul:first', this);
        var off = elm.offset();

        if (typeof off != "undefined") {
          var l = off.left;
          var w = elm.width();
          var docW = $("section.content").outerWidth(true);

          var isEntirelyVisible = (l + w <= docW);

          if (!isEntirelyVisible) {
            $(this).addClass('other_side');
          }
        }
      },

      mouseleave: function () {
        if ($(this).hasClass('other_side')) {
          $(this).removeClass('other_side');
        }
      }
    }, ".dropdown li");

    // fancy box
    if ($(".fancybox").length) {
      $("a.fancybox").fancybox();
    }

    // dropdown menu
    if ($('.mobile_dropdown_menu .dropdown .dropdown').length) {

      $('.mobile_dropdown_menu .nav-item.dropdown').each(function () {
        var $self = $(this);
        var handle = $self.children('[data-toggle="dropdown"]');

        $(handle).on("click tap", function (e) {
          e.preventDefault();

          if($(this).hasClass('first-run-init')){
            var submenu = $self.children('.dropdown-menu').eq(0);

            if(submenu.is(':hidden')){
              $(submenu).show();
            } else {
              $(submenu).hide();
            }

            return false;
          } else {
            $(this).addClass('first-run-init');
          }
        });
      });


      $('.mobile_dropdown_menu .dropdown .dropdown').each(function () {
        var $self = $(this);
        var handle = $self.children('[data-toggle="dropdown"]');

        $(handle).on("click tap", function () {
          var submenu = $self.children('.dropdown-menu').eq(0);
          $(submenu).toggle();

          return false;
        });
      });
    }

    $.fn.evenElements = function () {
      var heights = [];

      $(this).removeAttr('style').height('auto');

      this.each(function () {
        var height = $(this).height('auto').outerHeight();

        heights.push(height);
      });

      var largest = Math.max.apply(Math, heights);

      return this.each(function () {
        $(this).height(largest);
      });
    };

    $('li.product > .woocommerce-title-price-area').evenElements();

    // close mobile menu
    $(document).click(function(event) {
      $(event.target).closest(".navbar").length || $(".navbar-collapse.show").length && $(".navbar-collapse.show").collapse("hide")
    });

    $(document).on('click', '.fullsize_menu li.dropdown a[data-toggle="dropdown"]', function(e){
      window.location.href = $(this).attr('href');
    });

    if($('.blog-boxed').length){
      $('.blog-boxed').isotope({
        itemSelector: '.col-xs-12',
        sortBy: 'order-order',
      });
    }

    $(document).on("submit", "#automotive_login_form", function (e) {
      e.preventDefault();

      var nonce = $(this).find(".ajax_login").data("nonce");
      var username = $(this).find(".username_input");
      var password = $(this).find(".password_input");
      var loading = $(this).find(".login_loading");
      var empty_fields = false;

      if (!username.val()) {
        empty_fields = true;
        username.css("border", "1px solid #F00");
      } else {
        username.removeAttr("style");
      }

      if (!password.val()) {
        empty_fields = true;
        password.css("border", "1px solid #F00");
      } else {
        password.removeAttr("style");
      }

      if (!empty_fields) {
        loading.show();

        jQuery.ajax({
          url: ajax_variables.ajaxurl,
          type: 'POST',
          data: {action: 'ajax_login', username: username.val(), password: password.val(), nonce: nonce},
          success: function (response) {
            if ("success" == response) {
              username.removeAttr("style");
              password.removeAttr("style");

              location.reload();
            } else {
              username.css("border", "1px solid #F00");
              password.css("border", "1px solid #F00");

              loading.hide();
            }
          }
        });
      }

    });

    // if wow exists
    if (typeof WOW == 'function') {
      WOW = new WOW({
        boxClass: 'auto_animate',
        offset: 15
      });

      WOW.init();
    }

  });

  woocommerce_slider_height();

  $(document).on("mouseenter", ".woocommerce li.product_style_2", function () {
    var height = $(this).height();
    var div_height = $(this).find(".woocommerce-product-back-align").height();

    $(this).find(".woocommerce-product-back-align").css("margin-top", ((height - div_height) / 2) - 10);
  });

  // woocommerce dropdowns
  var $woo_dropdowns = $('body.woo-auto-dropdowns .woocommerce.widget_product_categories ul.product-categories > li.cat-parent, body.woo-auto-dropdowns .woocommerce.widget_product_categories ul.product-categories > li.cat-parent ul.children > li.cat-parent');

  if($woo_dropdowns.length){
    $woo_dropdowns.each(function(){
      $(this).find('> a').after('<span class="woo-dropdown-toggle"><i class="fas fa-angle-double-down"></i></span>')
    });

    $woo_dropdowns.on('click', '.woo-dropdown-toggle', function(e){
      e.preventDefault();
      e.stopPropagation();

      var $parent = $(this).closest('.cat-parent');

      $parent.find('> .children').slideToggle();
      $(this).toggleClass('is-open');
    });
  }

function woocommerce_slider_height() {
  var $ = jQuery;

  if ($(".woocommerce-product-gallery").length) {
    var height = $(".woocommerce-product-gallery .woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image").height();

    $(".woocommerce-product-gallery .woocommerce-product-gallery__wrapper .thumbs").css('height', height);
  }
}

(function ($, sr) {

  // debouncing function from John Hann
  // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
  var debounce = function (func, threshold, execAsap) {
    var timeout;

    return function debounced() {
      var obj = this, args = arguments;

      function delayed() {
        if (!execAsap)
          func.apply(obj, args);
        timeout = null;
      };

      if (timeout)
        clearTimeout(timeout);
      else if (execAsap)
        func.apply(obj, args);

      timeout = setTimeout(delayed, threshold || 100);
    };
  }
  // smartresize
  jQuery.fn[sr] = function (fn) {
    return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr);
  };

})(jQuery, 'smartresize');

jQuery(window).smartresize(function ($) {
  woocommerce_slider_height();
});

jQuery(window).on("load", function () {
  if (jQuery(".woocommerce-menu-basket ul").length && jQuery.fn.mCustomScrollbar) {
    jQuery(".woocommerce-menu-basket ul").mCustomScrollbar({
      scrollInertia: 0,
      mouseWheelPixels: 500,
      scrollEasing: 'linear'
    });
  }
});

jQuery(window).on('scroll', function (event) {
  if(!ajax_variables.disable_header_resize){

  if((document.body.clientWidth <= 768 && !ajax_variables.disable_mobile_header_resize) || document.body.clientWidth >= 768){
      var $ = jQuery;
      var $header = $("body > header");
      var scrollValue = $(window).scrollTop();

      if (scrollValue > 1) {
        $header.addClass('affix').removeClass('affix-top');
      } else {
        $header.addClass('affix-top').removeClass('affix');
      }
    }
  }
});

})(jQuery);
