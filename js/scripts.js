$(document).ready(function () {

  $('#slideshow').owlCarousel({
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 3000
  });

  $(function () {
    $('.navbar').stickyNavbar({
      mobile: true,
      navOffset: 0
    })
  })

});
