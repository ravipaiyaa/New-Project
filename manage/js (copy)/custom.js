 


 //pagingCtrl
 var myModule = angular.module('hello', ['angularUtils.directives.dirPagination']);
    myModule.controller('courseController', function ($scope, $http) {
 // function courseController($scope,$http) {
         $scope.employees = [];

         $scope.tab= 1;
        $scope.tabfunction = function(tab,page) {
            if (page<1) {
                page=1;
            }
            $scope.tab= tab;
           var url = M.cfg.wwwroot + '/blocks/manage/courseajax.php?tab='+tab+'&page='+page;
            
            $http.get(url).success( function(response) {
                 console.log(response);
                $scope.employees = response;
                console.log(response.numberofrecords);
                $scope.numberofrecords =  response.numberofrecords;
                console.log( $scope.numberofrecords);
                 
            });
        }
        
        $scope.init = function( tab){
            $scope.tab= tab;
            $scope.tabfunction(tab,1);
            
        }
        
         $scope.pageChangeHandler = function(num,tab) {
           $scope.tabfunction(tab,num);
       };
    
     
    }); 
    