//FOR SLIDE MENU
$(document).ready(function(){
  $('.menu-tab').click(function(){
    $('.menu-hide').toggleClass('show');
  });
  $('.menu-close').click(function(){
    $('.menu-hide').toggleClass('show');
  });
});

$(document).ready(function() {

    $('#accordion li').children('ul').hide();

    $('#accordion a').click(function() {

        $(this).parent().siblings('.active').removeClass('active').find('ul').slideUp('fast');

        if ($(this).parent().hasClass('active')) {
            $(this).next('ul').slideUp('fast');
            $(this).parent().removeClass('active');
        } else {
            $(this).next('ul').slideDown('fast');
            $(this).parent().addClass('active');
        }

    });

});
