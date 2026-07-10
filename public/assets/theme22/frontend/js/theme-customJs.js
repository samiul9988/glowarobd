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

// for image preview in support ticket details page
function readURL(input) {
  if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
          document.getElementById('imagePreview').style.backgroundImage = 'url(' + e.target.result + ')';
          document.getElementById('imagePreview').style.display = 'none';
          fadeIn(document.getElementById('imagePreview'), 650);
      }
      reader.readAsDataURL(input.files[0]);
  }
}

function fadeIn(element, duration) {
  var op = 0.1;  // initial opacity
  element.style.display = 'block';
  var timer = setInterval(function () {
      if (op >= 1){
          clearInterval(timer);
      }
      element.style.opacity = op;
      element.style.filter = 'alpha(opacity=' + op * 100 + ")";
      op += op * 0.1;
  }, duration / 10);
}

// document.getElementById("imageUpload").addEventListener("change", function() {
//   readURL(this);
// });

readURL(this);



