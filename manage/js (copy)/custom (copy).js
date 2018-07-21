 

  $( "li.active + li " ).css( "background-color", "red" );
 //pagingCtrl
 var myModule = angular.module('hello', []);
    myModule.controller('courseController', function ($scope, $http) {
 // function courseController($scope,$http) {
        
                console.log('hi');
        $scope.currentPage = 1;
        $scope.numPages = 5;
        $scope.pageSize = 10;
        $scope.pages = [10];
        
        $scope.tabfunction = function(tab,page) {
            if (page<1) {
                page=1;
            }
            $scope.tab= tab;
           var url = M.cfg.wwwroot + '/blocks/manage/courseajax.php?tab='+tab+'&page'+page;
            
            $http.get(url).success( function(response) {
                 console.log(response);
                $scope.employees = response;
                $scope.numPages =  response.numberofrecords;
                 
            });
        }
        
        $scope.init = function( tab){
            $scope.tabfunction(tab,1);
        }
        
    
      //$( "li.ng-scope active + li:nth-of-type(2)" ).siblings().css( "background-color", "red" );

        
         $scope.onSelectPage = function (page) {
            alert(page);
            $scope.tabfunction(1,page); 
            //replace your real data
            /*$http.get('url',
                {
                    method: 'GET',
                    params: {
                        'pageNo': page,
                        'pageSize': $scope.pageSize
                    },
                    responseType: "json"
                }).then(function (result) {
                    $scope.data = result.data.Data;
                    $scope.numPages = Math.ceil(result.data.Total / result.data.PageSize);
                });*/
        };
        
   });
    
  /*  myModule.controller('pagingCtrl', function ($scope, $http) {
       console.log('hi');
        $scope.currentPage = 1;
        $scope.numPages = 5;
        $scope.pageSize = 10;
        $scope.pages = [];
        //get first page
        /*$http.get('url',
                {
                    method: 'GET',
                    params: {
                        'pageNo': $scope.currentPage,
                        'pageSize': $scope.pageSize
                    },
                    responseType: "json"
                }).then(function (result) {
                    $scope.data = result.data.Data;
                    $scope.numPages = Math.ceil(result.data.Total / result.data.PageSize);
                });
        $scope.onSelectPage = function (page) {
            //replace your real data
            /*$http.get('url',
                {
                    method: 'GET',
                    params: {
                        'pageNo': page,
                        'pageSize': $scope.pageSize
                    },
                    responseType: "json"
                }).then(function (result) {
                    $scope.data = result.data.Data;
                    $scope.numPages = Math.ceil(result.data.Total / result.data.PageSize);
                });
        }; 
    });  */

    myModule.directive('paging', function() {
        return {
            restrict: 'E',
            //scope: {
            //    numPages: '=',
            //    currentPage: '=',
            //    onSelectPage: '&'
            //},
            template: '',
            replace: true,
            link: function(scope, element, attrs) {
                scope.$watch('numPages', function(value) {
                    scope.pages = [];
                    for (var i = 1; i <= value; i++) {
                        scope.pages.push(i);
                    }
                    alert(scope.currentPage )
                    if (scope.currentPage > value) {
                        scope.selectPage(value);
                    }
                });
                scope.isActive = function(page) {
                    return scope.currentPage === page;
                };
                scope.selectPage = function(page) {
                    if (!scope.isActive(page)) {
                        scope.currentPage = page;
                        scope.onSelectPage(page);
                    }
                };
                scope.selectPrevious = function() {
                    if (!scope.noPrevious()) {
                        scope.selectPage(scope.currentPage - 1);
                    }
                };
                scope.selectNext = function() {
                    if (!scope.noNext()) {
                        scope.selectPage(scope.currentPage + 1);
                    }
                };
                scope.noPrevious = function() {
                    return scope.currentPage == 1;
                };
                scope.noNext = function() {
                    return scope.currentPage == scope.numPages;
                };

            }
        };
    }); 
    