(function() {
  'use-strict';
  $(document).ready(function() {
    var $gallery, $galleryControlLeft, $galleryControlRight;
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
    $('.filter-triggers a').click(function(e) {
      var idRef;
      e.preventDefault();
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
