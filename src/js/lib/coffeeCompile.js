(function() {
  'use-strict';
  $(document).ready(function() {
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
  });

}).call(this);
