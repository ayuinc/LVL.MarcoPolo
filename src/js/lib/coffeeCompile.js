(function() {
  $(document).ready(function() {
    var $container, itemTempl;
    $container = $('.pins-grid');
    $container.imagesLoaded(function() {
      $container.isotope({
        itemSelector: '.grid-list-item',
        layoutMode: 'fitRows'
      });
    });
    itemTempl = '<div class="grid-list-item">' + '<div class="pin">' + '<div class="pin-top">' + '<small class="pin-category">{categories show_group="1"}{category_name}{/categories} in {categories show_group="3"}{category_name}{/categories}</small>' + '<h2 class="pin-title"><a href="{site_url}project/{url_title}">{title}</a></h2>' + '<em class="pin-location">{categories show_group="2"}{category_name}{/categories}</em>' + '</div>' + '<div class="pin-center">' + '<div class="pin-pic">' + '{project_imagenes limit="1"}' + '<img src="{image:url:small}" alt="Grid 1" class="ignore-srcset">' + '{/project_imagenes}' + '<a href="{site_url}project/{url_title}" class="overlay">' + '<div class="pin-bottom">' + '<button class="btn btn-icon-custom btn-gray-darker btn-icon-left btn-sm">' + '<i>45</i>' + '<span>VOTE</span>' + '</button>' + '</div>' + '</a>' + '</div>' + '</div>' + '</div>' + '</div>';
  });

}).call(this);

(function() {
  'use-strict';
  $(document).ready(function() {
    var $gallery, $galleryControlLeft, $galleryControlRight, bodyScrollController, lockBody;
    $('.disable-anchors a').click(function(e) {
      e.preventDefault();
    });
    $('[data-href]').click(function(e) {
      var lastPath, locationArr, mainPath, pathObj;
      locationArr = window.location.pathname.split('/');
      lastPath = locationArr[locationArr.length - 1];
      mainPath = locationArr[locationArr.length - 2];
      pathObj = {};
      pathObj[mainPath] = lastPath;
      window.history.pushState(pathObj, '', lastPath);
      document.location.replace($(this).data('href'));
    });
    bodyScrollController = new ScrollMagic.Controller();
    bodyScrollController.scrollTo(function(newpos) {
      TweenMax.to(window, 0.5, {
        scrollTo: {
          y: newpos
        }
      });
    });
    lockBody = function() {
      var body;
      body = 'body';
      $(body).css({
        overflow: 'hidden'
      });
      bodyScrollController.scrollTo(body);
    };
    $('.filter-triggers a').click(function(e) {
      var idRef;
      e.preventDefault();
      lockBody();
      $('.filters-wrapper [class*=filter-module-]').removeClass('on-screen');
      idRef = $(this).attr('href');
      console.log(idRef);
      $('.filter-triggers li').removeClass('active');
      $('.site-wrapper').addClass('filters-on');
      $(this).parent().addClass('active');
      $('.filters-wrapper').find(idRef).addClass('on-screen');
    });
    $('.filters-close a').click(function(e) {
      e.preventDefault();
      $('.filter-triggers li').removeClass('active');
      $('.site-wrapper').removeClass('filters-on');
      $('body').css({
        overflow: 'auto'
      });
    });
    $gallery = $('.gallery');
    $gallery.flickity({
      cellAlign: 'left',
      contain: true,
      wrapAround: true,
      pageDots: false,
      prevNextButtons: false
    });
    $galleryControlLeft = $('.gallery-control-left');
    $galleryControlRight = $('.gallery-control-right');
    $galleryControlLeft.on('click', 'a', function(e) {
      e.preventDefault();
      $gallery.flickity('previous');
    });
    $galleryControlRight.on('click', 'a', function(e) {
      e.preventDefault();
      $gallery.flickity('next');
    });
  });

}).call(this);

(function() {
  'use-strict';
  $(document).ready(function() {
    var RBGIMG, addBgImg, screenSizes, windowWidth;
    windowWidth = $(window).width();
    RBGIMG = $('[data-responsive-bg-img]');
    screenSizes = {
      screenPhone: 480,
      screenTablet: 960,
      screenDesktop: 960,
      screenLargeDesktop: 1140
    };
    addBgImg = function(item, img) {
      $(item).addClass('bg-img-block').css({
        'background-image': 'url(' + img + ')'
      });
    };
    $.each(RBGIMG, function(index, item) {
      var imgSizeObj;
      imgSizeObj = $(item).data('responsive-bg-img');
      switch (true) {
        case windowWidth <= screenSizes['screenPhone']:
          addBgImg(item, imgSizeObj['vw-phone']);
          break;
        case windowWidth <= screenSizes['screenTablet'] && windowWidth > screenSizes['screenPhone']:
          addBgImg(item, imgSizeObj['vw-tablet']);
          break;
        case windowWidth <= screenSizes['screenDesktop'] && windowWidth > screenSizes['screenTablet']:
          addBgImg(item, imgSizeObj['vw-desktop']);
          break;
        case windowWidth > screenSizes['screenLargeDesktop']:
          addBgImg(item, imgSizeObj['vw-lg-desktop']);
          break;
        default:
          null;
      }
    });
  });

}).call(this);
