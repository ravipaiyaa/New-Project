

<script type="text/javascript">

/* * @param {Object} [args.scope] The scope to use when calling the callback.
 * @param {Object} [args.callbackargs] Any arguments to pass to the callback.
 * @param {String} [args.cancellabel] The label to use on the cancel button.
 * @param {String} [args.continuelabel] The label to use on the continue button.
 * 
 */

//coded by sheetal
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
 $('#test01').dialog('open');
}

$(document).ready(function() {
    var template='';

    function format (d) {                       
        var resp = $.ajax({
        dataType: "json",
        url: M.cfg.wwwroot+'/mod/facetoface/approvals/approve_ajax.php',
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
            template ='<form name ="student_examination" method ="POST" class = "student_toggle" id ="student_verificatoin">'+
            '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%">'+
  
            '<thead><tr>'+
             '<th><input type="checkbox" name="examid"  id="checkAll" ></th>'+
            '<th>Name</th>'+
            '<th>Email ID</th>'+
            '<th>Action</th>'+
            '</tr></thead><tbody>';
                          
            for(i=0;i<aryLen;i++) {
                template +='<tr>'+
                '<td><input type="checkbox" name="f2fapprovalids[]" class="check_approve"  id="approve_enable" value='+jsonResponse[i].fapprovalid+'></td>'+  
                '<td >'+jsonResponse[i].data1+'</td>'+
                '<td>'+jsonResponse[i].data2+'</td>'+                          
                '<td>'+jsonResponse[i].data6+'</td>'+                       
                '</tr>';
            }
              
            template +='</tbody></table>'+'<div style="text-align: center; margin-top:10px;">'+                            
                       '<input type="hidden" name=action  value=approve  />'+
                       '<input type = "submit" name="submit" value="Approve" id="student_submit" />'+
                       '</div></form>';
            template +='<div style="display:none; background-color:#e0e0e0;" id="test01">'+
                       '<form method="post" action=""><input type=text name="text" required/>'+
                       '<input type=submit  name="sub"/>'+
                       '<input type=hidden name=action value=reject />'+
                       '<input type=hidden id="hid" name="fapprovalid" />'+
                       '</form></div>';
  
           return template;
        }
        else {
           return   "<span style='font-size:14px;color:red;margin-left:42%;'>No user in this Ilt</span>";
        }                  
    } // end of format function
      
      
    function approved(d) {              
        var resp = $.ajax({
        dataType: "json",
        url: M.cfg.wwwroot+'/mod/facetoface/approvals/approve_ajax.php',
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

        template ='<form name ="student_examination" method ="POST" class = "student_toggle" id ="student_verificatoin">'+
        '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%">'+

        '<thead><tr>'+
        '<th>Name</th>'+
        '<th>Email ID</th>'+
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
        template += '<div style="display:none; background-color:#e0e0e0;" id="test01">'+
                    '<form method="post" action=""><input type=text name="text" required/>'+                    
                    '<input type=hidden name=action value=reject />'+
                    '<input type=hidden id="hid" name="fapprovalid" />'+
                    '<input type=submit  name="sub" />'+
                    '</form></div>';
        return template;
       }
       else  {
         return   "<span style='font-size:14px;color:red;margin-left:42%;'>No user in this Ilt</span>";
       }                
    }
    //end for approved
      
      
         //for reject
     function rejected(d) {       
               
         var resp = $.ajax({
         dataType: "json",
         url: M.cfg.wwwroot+'/mod/facetoface/approvals/approve_ajax.php',
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

              template ='<form name ="student_examination" method ="POST" class = "student_toggle" id ="student_verificatoin">'+
                        '<table id ="studentexam_details_'+d.data3+'" cellpadding="5" cellspacing="0" border="0" style="margin:auto;width:100%">'+

                         '<thead><tr>'+
                         '<th><input type="checkbox" name="examid"  id="checkAll"  ></th>'+
                         '<th>Name</th>'+
                         '<th>Email ID</th>'+                                     
                         '</tr></thead>';
           
              for(i=0;i<aryLen;i++) {
                    template +='<tr>'+
                       '<td><input type="checkbox" name="f2fapprovalids[]"  class="check_approve"  id="approve_enable" value='+jsonResponse[i].fapprovalid+'></td>'+

                       '<td>'+jsonResponse[i].data1+'</td>'+
                       '<td>'+jsonResponse[i].data2+'</td>'+
                       '</tr>';
              }
              template +='</table>'+'<div style="text-align: center; margin-top:10px;">'+
                         '<input type="hidden" name=action  value=approve  />'+ 
                         '<input type = "submit" name="submit" value="Approve" id="student_submit" /></div></form>';      
              template +='</table></form>'; 
              return template;
              }
              else{
                return   "<span style='font-size:14px;color:red;margin-left:42%;'>No user in this Ilt</span>";
              }
                
     } //end for reject
     
      
      var table = $('#publishedexams').DataTable({
            "ajax":  M.cfg.wwwroot+"/mod/facetoface/approvals/approve_ajax.php?action=request_user",
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
        
   // $("#row_2 td.details-control" ).trigger( "click" );
    
    //   var tr= $('#row_2');
    //       var row = table.row( tr );
    //        var data = row.data();
    //tr.addClass('shown');
    //    console.log(data);
      

    //var tr = $('#publishedexams tbody td.details-control').closest('tr');
    console.log(table);
    //var row = table.row( tr );
    //var data = row.data();
    //console.log(row);
    //console.log(data);
       var child = table.row('#row_2').child;
        child.show();
       
      // Add event listener for opening and closing details
      $('#publishedexams tbody').on('click', 'td.details-control', function() {
      // alert('hi');
            var tr = $(this).closest('tr');
            console.log(tr);
            var row = table.row( tr );
             console.log(row);
            var data = row.data();
            console.log(data);
            if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('shown');
            } else {
              // Open this row
              template='';
              row.child(format(data)).show();
              tr.addClass('shown');
            }
// checkbox handling
             document.getElementById("student_submit").disabled = true;
            $(document).on("change", "#checkAll",  function(){
             $("input.check_approve").prop('checked', $(this).prop("checked"));//changes by sheetal
                         if ($('input#approve_enable').is(':checked')) {
            document.getElementById("student_submit").disabled = false;
        }
                else  {
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
            //end check box handling
                      $('#studentexam_details_'+data.data3+'').DataTable({});
      });
      
      $('#publishedexams tbody').on('click', 'td.details-control1', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );
            var data = row.data();
          if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('shown');
          } else {
              // Open this row
              template='';
              row.child(approved(data)).show();
              tr.addClass('shown');
          }
          $('#studentexam_details_'+data.data3+'').DataTable({});
      });
      
      $('#publishedexams tbody').on('click', 'td.details-control2', function () {
          var tr = $(this).closest('tr');
          var row = table.row( tr );
          var data = row.data();
          if ( row.child.isShown() ) {
              // This row is already open - close it
              row.child.hide();
              tr.removeClass('shown');
          } else {
              // Open this row
              template='';
              row.child(rejected(data)).show();
              tr.addClass('shown');
          }
    // checkbox handling
        //  document.getElementById("student_submit").disabled = true;
          $(document).on("change", "#checkAll",  function(){
            $("input.check_approve").prop('checked', $(this).prop("checked"));//changes by sheetal
                if ($('input#approve_enable').is(':checked')) {
                  document.getElementById("student_submit").disabled = false;
                }
                else  {
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
            //end check box handling
         $('#studentexam_details_'+data.data3+'').DataTable({});
      });
                  
      table.on( 'draw', function () {
      <?php
         $fapprovalid=optional_param('fapprovalid',0,PARAM_INT);
         $action=optional_param('action','',PARAM_TEXT);
         global $DB;
         if($fapprovalid>0){ 
             $faceapprovalinfo=$DB->get_record('local_facetoface_approval',array('id'=>$fapprovalid));
             if($action=='approve'){           
                 $classname='details-control1';
                 $rowid= $faceapprovalinfo->f2fid;
             } 
             else if($action=='reject'){
                 $classname='details-control2';
                 $rowid= $faceapprovalinfo->f2fid;              
             }
             
            
         }
         //$.each( detailRows, function ( i, id ) {
         //console.log(id);
         
       ?>
         var rowid = <?php echo ($rowid?$rowid:0) ?>;
         var classname = 'td.<?php echo ($classname?$classname:0) ?>';
         if ( rowid>0 && classname) {
              var selector="#row_"+rowid+' '+classname;
              console.log(selector);
             $('#row_63 td.details-control2').trigger( 'click' );
         }
         $('#row_63 td.details-control2').trigger( 'click' );
       } );
    });
               
    

</script>

