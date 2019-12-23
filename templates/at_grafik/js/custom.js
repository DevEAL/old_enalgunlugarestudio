/* Put your custom JS here */
jQuery(function($) {
$(document).ready(function(){
    $(".lt-info-block-img-right").hover(function() {
         $(".lt-info-block-img-right-img img").addClass("shake");
         }, function() {
       $(".lt-info-block-img-right-img img").removeClass("shake");
       });
});
});

jQuery(function($) {
$(document).ready(function(){
    $(".lt-info-block-1-img").hover(function() {
         $(".lt-info-block-1-img img").addClass("flash");
         }, function() {
       $(".lt-info-block-1-img img").removeClass("flash");
       });
});
});