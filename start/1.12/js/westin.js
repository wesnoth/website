(function($) {
  /*
   * Smooth scrolling for anchor links
   */
  $('a[href*=#]:not(#anntoc-button):not([href=#])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'')
        && location.hostname == this.hostname)
    {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length)
      {
        $('html,body').animate({ scrollTop: target.offset().top }, 1000);
        return false;
      }
    }
  });


  $('#anntoc-button').click(function() {
    $('#anntoc-button, #toc').toggleClass('expanded collapsed');
    return false;
  });
}(jQuery));

/* kate: indent-mode normal; indent-width 2; space-indent on; */
