'use strict';
$(document).ready(function () {
      $('#coursesearch').dataTable({
            "language": {
                            "paginate": {
                                "next": ">",
                                "previous": "<"
                              }
                        },
            "iDisplayLength": 5,
             "bSort" : false
            });
});
//$(document).ready(function () {
//    /* Initialise the DataTable */
//      $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
//    var responsiveHelper = undefined;
//    var breakpointDefinition = {
//        tablet: 1024,
//        phone: 480
//    };
//
//   // $('#id_questionpool').DataTable();
//    var tableContainer = $('#coursesearch');
//  
//    var oTable = tableContainer.dataTable({
//        "iDisplayLength": 5,
//       "sPaginationType": "full_numbers",
//        "aaSorting": [],
//        "aoColumnDefs": [
//            {"bVisible": false, "aTargets": [1,2]},
//             {"bSearchable": false, "aTargets": [0]},
//        ],
//        "bInfo": true,
//        "oLanguage": {
//            "sLengthMenu": 'View:  <select>' +
//                    '<option value="5">5</option>' +
//                    '<option value="10">10</option>' +
//                    '<option value="15">15</option>' +
//                    '<option value="20">20</option>' +
//                    '<option value="50">50</option>' +
//                    '<option value="-1">All</option>' +
//                    '</select>'
//        },
//        // Setup for responsive datatables helper.
//        bAutoWidth: false,
//        PreDrawCallback: function () {
//            // Initialize the responsive datatables helper once.
//            if (!responsiveHelper) {
//                responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
//            }
//        },
//        RowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
//            responsiveHelper.createExpandIcon(nRow);
//        },
//    });
//     /* Add event listeners to the two range filtering inputs */
//      $.fn.dataTable.ext.errMode = 'throw';
//    oTable.coFilter({
//        sPlaceHolder: ".filterarea",
//        aoColumns: [0,1,2],
//        //commented by Raghuvaran For dataTable displays in IE 
//         //aoColumns: [2],
//        //customdata: {9: {0: "Global", 1: "Organization", 2: "Program", 3: "Course Offering"}},
//        //columntitles: {0: "Select Belt", 1:"Select Belt Type", 4:"Select Status", 2:"Strat Date", 3:"End Date"},
//        //filtertype: {0: "select", 1: "select", 4: "select",2:"date",3:"date"},
//         columntitles: {2:"Select Belt Type"},
//         filtertype: {2: "select"},
//    });
//     
//
//});
