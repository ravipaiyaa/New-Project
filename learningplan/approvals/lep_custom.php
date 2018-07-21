
<script type="text/javascript">

/* * @param {Object} [args.scope] The scope to use when calling the callback.
 * @param {Object} [args.callbackargs] Any arguments to pass to the callback.
 * @param {String} [args.cancellabel] The label to use on the cancel button.
 * @param {String} [args.continuelabel] The label to use on the continue button.
 * 
 */

//coded by sheetal
<?php
global $DB, $USER, $CFG,$PAGE,$OUTPUT;
$planid = optional_param('id', 0, PARAM_INT);
if($planid){
 $url = new moodle_url('/local/learningplan/approvals/lep_request.php', array('plan'=>$planid));
 $action='action="'.$url.'"';
}else{
 $action='';
}
?>
$('#overlay').fadeIn('fast').delay(2000).fadeOut('slow');
function doSubmit(type,id){ 
 $("#hid").attr("value",id);
        $("#test01").dialog({
            modal: true,
            autoOpen: false,
            title: "Rejection reason",
            width: 300,
            height: 150,
            color:'#e2e2e2'
        });
 $("#test01").dialog('open');
}

$(document).ready(function() {
 
    var template='';

    function format (d) {
    
        var resp = $.ajax({
        dataType: "json",
        url: M.cfg.wwwroot+'/local/learningplan/approvals/lep_approve_ajax.php?id=<?php echo $planid ?>',
        data:{
            req : 'req_data',
            fid : d.data3,
            cat:'new'         
        },
        async: false
        }).responseText;
              
        if(resp!='0') {
       
            var jsonResponse = JSON.parse(resp);
            var aryLen=jsonResponse.length;
            var i;
            //console.log(jsonResponse[0]);  
            template ='<form name ="student_examination"<?php echo $action; ?> method ="POST" class = "student_toggle" id ="student_verificatoin"><div><h2>Requested users</h2></div><div id="requser"  style= "width: 50%;color: #336799;text-align: center;">These are the requested users,here you can approve or reject</div>'+
            '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%">'+
  
            '<thead><tr>'+
              '<th><input type="checkbox" name="examid"  id="checkAll"  ></th>'+
            '<th>Name</th>'+
            '<th>Email Address</th>'+
            '<th>Action</th>'+
            
            '</tr></thead>';
                          
            for(i=0;i<aryLen;i++) {
                template +='<tr>'+
                '<td><input type="checkbox" name="f2fapprovalids[]"  class="check_approve" id="approve_enable" value='+jsonResponse[i].fapprovalid+'></td>'+  
                '<td >'+jsonResponse[i].data1+'</td>'+
                '<td>'+jsonResponse[i].data2+'</td>'+                          
                '<td>'+jsonResponse[i].data6+'</td>'+                       
                '</tr>';
            }
              
            template +='</table>'+'<div style="text-align: center; margin-top:10px;">'+                            
                       '<input type="hidden" name=action  value=approve  />'+
                       '<input type = "submit" name="submit" value="Approve" id="student_submit" disabled="true" />'+
                       '</div></form>';
            template +='<div style="display:none; padding: 0px 2%;width: 96%;" id="test01">'+
                       '<form method="post" <?php echo $action; ?>><label></label><input type=text name="text" required/>'+
                       '<input type=submit  name="sub"/>'+
                       '<input type=hidden name=action value=reject />'+
                       '<input type=hidden id="hid" name="fapprovalid" />'+
                       '</form></div><style>#test01 + .ui-dialog-titlebar.ui-widget-header{float:left;width:100%;}</style>';
  
   $(document).on("change", "#checkAll",  function(){
             $("input.check_approve").prop('checked', $(this).prop("checked"));//changes by sheetal
                         if ($('input#approve_enable').is(':checked')) {
                          //alert("if");
            document.getElementById("student_submit").disabled = false;
        }
                else  {
                // alert("else");
          document.getElementById("student_submit").disabled = true;
        }
        });
            $(document).on("change", "#approve_enable", function(){
            if ($('input#approve_enable').is(':checked')) {
            document.getElementById("student_submit").disabled = false;
        }
        else  {
          document.getElementById("student_submit").disabled = true;
        }
                });
  
  
           return template;
          //test
                        // document.getElementById("student_submit").disabled = true;


          //end
        }
        else {
           return   "<div class='alert alert-info text-center'>No user in this Learning plan</div>";
        }                  
    } // end of format function
      
      
       function approved(d) {
         
        var resp = $.ajax({
        dataType: "json",
        url: M.cfg.wwwroot+'/local/learningplan/approvals/lep_approve_ajax.php',
        data:{
            req : 'req_data',
            fid : d.data3,
            cat:'approve'        
        },
        async: false
        }).responseText;
           
        if(resp!='0') {                 
            var jsonResponse = JSON.parse(resp);
            var aryLen=jsonResponse.length;
            var i;
           // console.log(jsonResponse[0]);

        template ='<form name ="student_examination" <?php echo $action; ?> method ="POST" class = "student_toggle" id ="student_verificatoin"><div><h2>Approved users</h2></div><div id="requser"  style= "width: 50%;color: #336799;text-align: center;">These are the approved users,here we can only reject the users</div>'+
        '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%;background-color:red;">'+

        '<thead><tr>'+
        '<th>Name</th>'+
        '<th>Email Address</th>'+
        '<th>Action</th>'+
     
        '</tr></thead>';
           
        for(i=0;i<aryLen;i++){
            template +='<tr>'+
            '<td>'+jsonResponse[i].data1+'</td>'+
            '<td>'+jsonResponse[i].data2+'</td>'+
            '<td>'+jsonResponse[i].data6+'</td>'+
            '</tr>';
        }
            
        template +='</table></form>';
        template += '<div style="display:none;  padding: 0px 2%;width: 96%;" id="test01">'+
                    '<form method="post" <?php echo $action; ?>><label></label><input type=text name="text" required/>'+                    
                    '<input type=hidden name=action value=reject />'+
                    '<input type=hidden id="hid" name="fapprovalid" />'+
                    '<input type=submit  name="sub" />'+
                    '</form></div><style>#test01 + .ui-dialog-titlebar.ui-widget-header{float:left;width:100%;}</style>';
        return template;
       }
       else  {
         return   "<div class='alert alert-info text-center'>No user in this Learning plan</div>";
       }                
    }
    //end for approved
      
      
       //for reject
     function rejected(d) {       
             
         var resp = $.ajax({
         dataType: "json",
         url: M.cfg.wwwroot+'/local/learningplan/approvals/lep_approve_ajax.php',
         data:{
            req : 'req_data',
            fid : d.data3,
            cat:'rejected'        
         },
         async: false
         }).responseText;
               
         if(resp!='0') {
                 
              var jsonResponse = JSON.parse(resp);
              var aryLen=jsonResponse.length;
              var i;
              //console.log(jsonResponse[0]);

              template ='<form name ="student_examination" <?php echo $action; ?> method ="POST" class = "student_toggle" id ="student_verificatoin"><div><h2>Rejected users</h2></div><div id="requser"  style= "width: 50%;color: #336799;text-align: center;">These are the rejected users,here we can only approve the users</div>'+
                        '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%">'+

                         '<thead><tr>'+
                         '<th><input type="checkbox" name="examid"  id="checkAll"  ></th>'+
                         '<th>Name</th>'+
                         '<th>Email Address</th>'+                                     
                         '</tr></thead>';
           
              for(i=0;i<aryLen;i++) {
                    template +='<tr>'+
                       '<td><input type="checkbox" name="f2fapprovalids[]"  class="check_approve" id="approve_enable" value='+jsonResponse[i].fapprovalid+'></td>'+

                       '<td>'+jsonResponse[i].data1+'</td>'+
                       '<td>'+jsonResponse[i].data2+'</td>'+
                       '</tr>';
              }
              template +='</table>'+'<div style="text-align: center; margin-top:10px;">'+
                         '<input type="hidden" name=action  value=approve />'+ 
                         '<input type = "submit" name="submit" value="Approve" id="student_submit" disabled="true"/></div></form>';      
              template +='</table></form>';
                                      //test
                      // document.getElementById("student_submit").disabled = true;
            $(document).on("change", "#checkAll",  function(){
             $("input.check_approve").prop('checked', $(this).prop("checked"));//changes by sheetal
                         if ($('input#approve_enable').is(':checked')) {
                          //alert("if");
            document.getElementById("student_submit").disabled = false;
        }
                else  {
                // alert("else");
          document.getElementById("student_submit").disabled = true;
        }
        });
            $(document).on("change", "#approve_enable", function(){
            if ($('input#approve_enable').is(':checked')) {
            document.getElementById("student_submit").disabled = false;
        }
        else  {
          document.getElementById("student_submit").disabled = true;
        }
                });
          //end
              return template;
              }

              else{
                return   "<div class='alert alert-info text-center'>No user in this Learning plan</div>";
              }
                
     } //end for reject
     
      
      var table = $('#publishedexams').DataTable({
            "ajax":  M.cfg.wwwroot+"/local/learningplan/approvals/lep_approve_ajax.php?action=request_user&id=<?php echo $planid ?>",
            "columns": [
              {"data": "data1"},
              {  "className": 'details-control',
                  "orderable":      false,
                  "data":           'data2',
                  "defaultContent": ''
                  },
              {  "className": 'details-control1',
                  "orderable":      false,
                  "data":           'data4',
                  "defaultContent": ''
                  },
              {  "className": 'details-control2',
                  "orderable":      false,
                  "data":           'data5',
                  "defaultContent": ''
                  },
            /* { "data": "data3" },
              { "data": "data4"},
              { "data": "data5"},
              { "data": "data6"},
              { "data": "data7"}  */
          ],
          //"order": [[1, 'asc']]
      });   

 
      $('#publishedexams tbody').on('click', 'td.details-control', function() {
      
           var tr = $(this).closest('tr');            
           var row = table.row( tr );             
           var data = row.data();
           
            
          if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('details-control shown');
               $('#studentexam_details_'+data.data3+'').DataTable({});
              // Remove from the 'open' array
            detailRows.splice( idx, 1 );
            } else {
              // Open this row
              template='';
              row.child(format(data)).show();
              tr.addClass('details-control shown');      
              $('#studentexam_details_'+data.data3+'').DataTable({});
            }
                // $('#studentexam_details_'+data.data3+'').DataTable({});        
      });
      
      $('#publishedexams tbody').on('click', 'td.details-control1', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );
            var data = row.data();
          if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('details-control1 shown1');
              $('#studentexam_details_'+data.data3+'').DataTable({});   
          } else {
              // Open this row
              template='';
              row.child(approved(data)).show();
              tr.addClass('details-control1 shown1');
              $('#studentexam_details_'+data.data3+'').DataTable({});   
          }
               
      });
      
      
      $('#publishedexams tbody').on('click', 'td.details-control2', function () {
          var tr = $(this).closest('tr');
          var row = table.row( tr );
          var data = row.data();
          if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('shown2');
              $('#studentexam_details_'+data.data3+'').DataTable({});   
          } else {
              // Open this row
              template='';
              row.child(rejected(data)).show();
              tr.addClass('shown2');
              $('#studentexam_details_'+data.data3+'').DataTable({});   
          }
              
      });
                  
                  // On each draw, loop over the `detailRows` array and show any child rows
    //table.on( 'draw', function () {
    //<?php
    //     $fapprovalid=optional_param('fapprovalid',0,PARAM_INT);
    //     $action=optional_param('action','',PARAM_TEXT);
    //     global $DB;
    //     if($fapprovalid>0){ 
    //         $faceapprovalinfo=$DB->get_record('local_learningplan_approval',array('id'=>$fapprovalid));
    //         if($action=='approve'){           
    //             $classname='details-control1';
    //             $rowid= $faceapprovalinfo->planid;
    //         } 
    //         else if($action=='reject'){
    //             $classname='details-control2';
    //             $rowid= $faceapprovalinfo->planid;              
    //         }
    //         
    //        
    //     }
    //     //$.each( detailRows, function ( i, id ) {
    //     //console.log(id);
    //     
    //   ?>
    //     var rowid = <?php echo ($rowid?$rowid:0) ?>;
    //     var classname = 'td.<?php echo ($classname?$classname:0) ?>';
    //     if ( rowid>0 && classname) {
    //          var selector="#row_"+rowid+' '+classname;
    //         
    //         $(selector).trigger( 'click' );
    //     }
    //       // $('#row_63 td.details-control2').trigger( 'click' );
    //    
    //});
                  
        
    });
    

</script>

