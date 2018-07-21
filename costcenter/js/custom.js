$(document).ready(function() {
  $("#id_type").select2();
  $("#id_category").select2();
  $("#id_costcenter").select2();
  

  $('#courseallocation').dataTable({
								'bPaginate' : true,
								'searching': false,
                'pageLength': 1,
                'bLengthChange': false,
                'processing': true,
                'serverSide': true,
                'ajax': M.cfg.wwwroot +'/local/costcenter/courseallocation_ajax.php',
								language: {
									paginate: {
									  'previous': '<',
									  'next': '>'
									}
								}
							});
});
  
//code commented by Shivani -- started by Shivani
function featuredcourses(id,featured){
   
   var lang=id;
   var course = featured;
$.ajax({
    url:M.cfg.wwwroot+"/local/costcenter/featured_courses.php?id="+lang+"&featured="+course,
    beforeSend: function(){
    },
    success:function(result){
	    $(".featured"+id).html(result);
     },
     error: function(){
       $(".featured"+id).html('error');
     },
     cache: false,dataType: "html"});

}
  
