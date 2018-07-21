
$(document).ready(function(){
    $('#show_more').on('click', function() {
        $('#show_more').hide();
        $('.course_description').addClass('show_more_description');
        $('#show_less').show();
    });
    $('#show_less').on('click', function() {
        $('#show_less').hide();
        $('.course_description').removeClass('show_more_description');
        $('#show_more').show();
    });
    
        setTimeout(function(){
            var move = "145px";
            var length = $(".yui3-tabview-list li").length;
            var wid = $("div[role=main]").width();
            var l = length;
            if(wid <= 700 && wid >= 430){
                l = length-4;
            }else if(wid <= 400){
                l = length-2;
            }else{
                l = length-6;
            }
            var width = l*145;
            var sliderLimit = -width;
            var view2 = $(".yui3-tabview-list");
            $("#rightArrow2").click(function () {
                var currentPosition = parseInt(view2.css("left"));
                if (currentPosition >= sliderLimit) view2.stop(false, true).animate({ left: "-=" + move }, { duration: 400 })
            });
            $("#leftArrow2").click(function () {
                var currentPosition = parseInt(view2.css("left"));
                if (currentPosition < 0) view2.stop(false, true).animate({ left: "+=" + move }, { duration: 400 })
            });
        }, 500);
});