//slides the element with class "menu_body" when paragraph with class "menu_head" is clicked
function dept_show_form(type, id) {
    //dept
    if (type == 'dept') {
        $(".assign_form_dept_"+id).toggleClass('show_form');
    }
    //subdept
    if (type == 'subdept') {
        $(".assign_form_subdept_"+id).toggleClass('show_form');
    }
}
    
$(document).ready(function () {
    //$( ".menu_body:first" ).css( "display", "block" );
    //$( ".summary:first" ).css( "display", "block" );
    //$("#firstpane p.menu_head .accordian_trigger").click(function ()
    //{
    //    alert('form');
    //    var assign = $(this).parent().parent();
    //    //alert(assign.attr('class'));
    //    //$(assign).next("div.menu_body").addClass('show_form');
    //    ////document.getElementById("accordion").style.display = 'none';
    //    $(assign).next("div.menu_body").child(".assign_form").show(300)
    //                .siblings("div.menu_body").hide("slow");
    //    //$(assign).css({backgroundImage: "url(pix/t/expanded.png)"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
    //    $(assign).siblings().css({backgroundImage: "url(pix/t/collapsed.png)"});
    //   
    //   
    //});
    
    
    $("#firstpane p.menu_head").click(function ()
    {
        //var assign = $(this);
        //$(assign).next("div.menu_body").removeClass('show_form');
        //document.getElementById("accordion").style.display = 'none';
        $(this).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
        $(this).siblings().css({backgroundImage: "url(pix/t/collapsed.png)"});
       
       
    });
    
    $(".menu_head").mouseover(function () {
        $(this).addClass("color_white");
    })
            .mouseout(function () {
                $(this).removeClass("color_white");
            });
});
$("#accordion").accordion();
$("#accordion .ui-accordion-header").unbind('click');
$(".accordion_options").click(function() {
    
    var current = parseInt($(this).attr("rel"));
    
    if(typeof current !== "undefined")
  
        $("#accordion").accordion({ active: current });
});
//function hide(target) {
//    document.getElementById(target).style.display = 'none';
//}