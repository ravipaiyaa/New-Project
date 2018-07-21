 


 //pagingCtrl
 var myModule = angular.module('hello', ['angularUtils.directives.dirPagination']);
    myModule.controller('courseController', function ($scope, $http) {
 // function courseController($scope,$http) {
         $scope.employees = [];

         $scope.tab= 1;
          $scope.tabfunction = function(tab, page, search_criteria, category, enrolltype, fromtab) {
            if (page<1) {
                page=1;
            }
            
            if(typeof page == 'undefined'){
               page=1;
               //console.log(page);
            }
            
            if(typeof search_criteria == 'undefined'){
               search_criteria=null;
               //console.log(page);
            }
            
            if(typeof enrolltype == 'undefined'){
               enrolltype=0;
               //console.log(page);
            }
            
            if (fromtab==1) {
                angular.element('#enrolltype').val(0);
                 angular.element('#search').val('');
            }   
            
            $scope.tab=tab;
            
            if (tab) {
               $.each([ 1,2,3,4 ], function( index, value ) {                 
                     if (tab==value) {                        
                          angular.element('.tab'+tab).addClass('active');
                     }
                     else{                         
                         if(angular.element('.tab'+value).hasClass('active')){                              
                              angular.element('.tab'+value).removeClass('active');
                         }
                     }
               });       
               
            }

               $scope.showLoader = true; 
           var url = M.cfg.wwwroot + '/blocks/manage/courseajax.php?tab='+tab+'&page='+page+'&search='+search_criteria+'&category='+category+'&enrolltype='+enrolltype;
            
            $http.get(url).success( function(response) {
                 //console.log(response);
                  $scope.showLoader = false;  
                 $scope.courseinfo = response;                
                 $scope.numberofrecords =  response.numberofrecords;                 
            });
          } 
        
        
        
          $scope.init = function( tab){     
              $scope.showLoader = true;         
            $scope.tabfunction(tab,0);            
          }        
        
          
     
          
          $scope.pageChangeHandler = function(num,tab) {
               var categoryid=angular.element('#categoryid').val();
               
               var search_criteria=angular.element('#search').val();
               var enrolltype=angular.element('#enrolltype').val();
               if (tab==1) {
                    var categoryid=angular.element('#categoryid').val();
                    $scope.tabfunction(tab,num,search_criteria, categoryid,enrolltype);
               }
               else
              
               $scope.tabfunction(tab,num, search_criteria,categoryid,enrolltype);
               
          };
    
          $scope.filterbyname= function(tab){          
               var search_criteria=angular.element('#search').val();
               //console.log(search_criteria);
               var enrolltype=angular.element('#enrolltype').val();
               if (tab==1) {
                    var categoryid=angular.element('#categoryid').val();
                    $scope.tabfunction(tab,0,search_criteria, categoryid,enrolltype);
               }
               else
               $scope.tabfunction(tab,0,search_criteria,0, enrolltype);
          };
          
          $scope.modelidchange = function (tab) {
               var categoryid=angular.element('#categoryid').val();
               var search_criteria=angular.element('#search').val();
               var enrolltype=angular.element('#enrolltype').val();
               $scope.tabfunction(tab,0,search_criteria,categoryid,enrolltype );
          } // end of  modelidchange function
          
          
          $scope.enrolltypechange= function (tab){             
               var categoryid=angular.element('#categoryid').val();
               var search_criteria=angular.element('#search').val();          
               var enrolltype=angular.element('#enrolltype').val();         
               $scope.tabfunction(tab,0,search_criteria,categoryid, enrolltype );                          
          } // end of  enrolltypechange function
     
    }); 
    
    myModule.filter('unsafe', ['$sce', function ($sce) {
    return function (val) {
        return $sce.trustAsHtml(val);
    };
}]);