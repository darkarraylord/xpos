/**
 * Created by vjcspy on 10/21/15.
 */
window.firstTimeLoadStore = true;
var dashboard = angular.module('dashboard', ['chart.js', 'ngTable'], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('//');
    $interpolateProvider.endSymbol('//');
});
dashboard.controller('dashboardController', ['$scope', 'switchStore', function ($scope, switchStore) {
    /*init Data*/
    $scope.report = {};
    $scope.report.store = '';
    $scope.dashboadData = {
        dataSalesChart: {},
        dataQuantityChart: {}
    };

    //item per order
    $scope.labelsItem = ['2006', '2007', '2008', '2009', '2010', '2011', '2012'];
    $scope.seriesItem = ['Series A', 'Series B'];

    $scope.dataItem = [
        [65, 59, 80, 81, 56, 55, 40],
        [28, 48, 40, 19, 86, 27, 90]
    ];

    // in/out stock
    $scope.labelsStock = ["In Sales", "Out-Store Sales"];
    $scope.dataStock = [0,0];
    //$scope.colorStock = ['#00CC00','#CC0000'];

    $scope.$watch('switchStore.currentStore', function (value) {
        if (!!window.firstTimeLoadStore) {
            window.firstTimeLoadStore = false;
            return;
        }
        switchStore.switch($scope.switchStore.currentStore);
        location.reload();
    });

}]);

dashboard.controller('salesOverview', ['$scope', '$http', function ($scope, $http) {

    /*angular-chart-js*/
    $scope.salesOverview = {
        labelsSales: [],
        seriesSales: [],
        dataSales: []
    };
    $scope.overview = {
        periodTitle: 'Last 24 Hours'
    };
    //$scope.salesOverview.labelsSales = ["January", "February", "March", "April", "May", "June", "July"];
    //$scope.salesOverview.seriesSales = ['Series A', 'Series B'];
    //$scope.salesOverview.dataSales = [
    //    [65, 59, 80, 81, 56, 55, 40],
    //    [28, 48, 40, 19, 86, 27, 90]
    //];

    /*FUNCTION*/
    $scope.changePeriod = function (index) {
        switch (index) {
            case 0:
                $scope.overview.periodTitle = 'Last 24 Hours';
                showLoad();
                $scope.getDataChartFromServer('24h', 0, 'revenue');
                break;
            case 1:
                showLoad();
                $scope.overview.periodTitle = 'Last 7 days';
                $scope.getDataChartFromServer('7d', 0, 'revenue');
                break;
            case 2:
                showLoad();
                $scope.getDataChartFromServer('1m', 0, 'revenue');
                $scope.overview.periodTitle = 'Current Month';
                break;
            case 3:
                $scope.overview.periodTitle = 'YTD';
                showLoad();
                $scope.getDataChartFromServer('1y', 0, 'revenue');
                break;
            case 4:
                $scope.overview.periodTitle = '2YTD';
                showLoad();
                $scope.getDataChartFromServer('2y', 0, 'revenue');
                break;
        }
    };

    $(document).ready(function () {
        $scope.changePeriod(0);
        $scope.$apply();
    });

    $scope.getDataChartFromServer = function (period, filter, type) {
        $http({
            method: 'GET',
            url: window.urlViewController + '?period=' + period + '&filter=' + filter + '&type=' + type
        }).then(function (response) {
                //console.log('OK');
                hideLoad();
                $scope.dashboadData.dataSalesChart = response.data;
                $scope.salesOverview.labelsSales = $scope.dashboadData.dataSalesChart.label;
                $scope.salesOverview.seriesSales = ['revene'];
                $scope.salesOverview.dataSales = [$scope.dashboadData.dataSalesChart.value];
            }, function (response) {
                hideLoad();
                console.log('Error');
            }
        );
    };

}]);

dashboard.controller('quantityController', ['$scope', '$http', 'NgTableParams', function ($scope, $http, NgTableParams) {

    /*angular-chart-js*/
    $scope.quantityOverview = {
        labelsSales: [],
        seriesSales: [],
        dataSales: []
    };
    $scope.quantityTile = {
        periodTitle: 'Last 24 Hours'
    };
    $scope.quantityOverview.labelsSales = ["January", "February", "March", "April", "May", "June", "July"];
    $scope.quantityOverview.seriesSales = ['Series A', 'Series B'];
    $scope.quantityOverview.dataSales = [
        [65, 59, 80, 81, 56, 55, 40],
        [28, 48, 40, 19, 86, 27, 90]
    ];

    /*FUNCTION*/
    $scope.changePeriod = function (index) {
        switch (index) {
            case 0:
                $scope.quantityTile.periodTitle = 'Last 24 Hours';
                showLoad();
                $scope.getDataChartFromServer('24h', 0, 'quantity');
                break;
            case 1:
                showLoad();
                $scope.quantityTile.periodTitle = 'Last 7 days';
                $scope.getDataChartFromServer('7d', 0, 'quantity');
                break;
            case 2:
                showLoad();
                $scope.getDataChartFromServer('1m', 0, 'quantity');
                $scope.quantityTile.periodTitle = 'Current Month';
                break;
            case 3:
                $scope.quantityTile.periodTitle = 'YTD';
                showLoad();
                $scope.getDataChartFromServer('1y', 0, 'quantity');
                break;
            case 4:
                $scope.quantityTile.periodTitle = '2YTD';
                showLoad();
                $scope.getDataChartFromServer('2y', 0, 'quantity');
                break;
        }
    };

    $(document).ready(function () {
        $scope.changePeriod(0);
        $scope.$apply();
    });

    $scope.getDataChartFromServer = function (period, filter, type) {
        $http({
            method: 'GET',
            url: window.urlViewController + '?period=' + period + '&filter=' + filter + '&type=' + type
            //headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function (response) {
                //console.log('OK');
                hideLoad();
                $scope.dashboadData.dataQuantityChart = response.data;
                $scope.quantityOverview.labelsItem = $scope.dashboadData.dataQuantityChart.label;
                $scope.quantityOverview.seriesSales = ['quantity'];
                $scope.quantityOverview.dataItem = [$scope.dashboadData.dataQuantityChart.value];
                var arrayQuantity = [];
                $.each($scope.dashboadData.dataQuantityChart.label, function (_k, _v) {
                    arrayQuantity.push({label: _v, qty: (response.data.value)[_k]});
                });
                $scope.customConfigParams = createUsingFullOptions();
                //console.log(arrayQuantity);
                function createUsingFullOptions() {
                    var initialParams = {
                        count: 10 // initial page size
                    };
                    var initialSettings = {
                        // page size buttons (right set of buttons in demo)
                        counts: [],
                        // determines the pager buttons (left set of buttons in demo)
                        paginationMaxBlocks: 13,
                        paginationMinBlocks: 2,
                        data: arrayQuantity
                    };
                    return new NgTableParams(initialParams, initialSettings);
                }

            }, function (response) {
                hideLoad();
                console.log('Error');
            }
        )
        ;
    };

}]);

dashboard.controller('bestController', ['$scope', 'NgTableParams', '$http', function ($scope, NgTableParams, $http) {
    $scope.bestTitle = ' - Seller';
    $scope.dataTop = {
        mostView: '',
        newCus: '',
        bestCus: '',
        topSearch: ''
    };
    $scope.cols = [
        {field: "name", title: "Name", sortable: "name", show: true},
        {field: "price", title: "Price", sortable: "price", show: true},
        {field: "qty", title: "Qty", show: true}
    ];
    $scope.bestTableParams = new NgTableParams({
        // initial sort order
        sorting: {qty: "desc"}
    }, {
        data: []
    });

    $scope.changeMeasure = function (_index) {

        var dataBest = [];
        if (_index == 0) {
            dataBest = [];
            $scope.bestTitle = ' Best Seller';
            $scope.cols = [
                {field: "name", title: "Name", sortable: "name", show: true},
                {field: "price", title: "Price", sortable: "price", show: true},
                {field: "qty", title: "Qty", show: true}
            ];
            $.each($scope.dataBestSeller, function (_k, _v) {
                dataBest.push({
                    name: _v.name,
                    qty: _v.qty,
                    price: _v.price
                })
            });
            $scope.bestTableParams = new NgTableParams({
                // initial sort order
                sorting: {qty: "desc"},
                count: 10
            }, {
                counts: [],
                paginationMaxBlocks: 13,
                paginationMinBlocks: 2,
                data: dataBest
            });
        }

        if (_index == 'mostView') {
            dataBest = [];
            $scope.bestTitle = ' Most viewed';
            if ($scope.dataTop.mostView == '') {
                showLoad();
                $scope.getDataTopFromServer(0, 'mostView');
            } else {
                $scope.cols = [
                    {field: "name", title: "Name", sortable: "name", show: true, align: "left"},
                    {field: "price", title: "Price", sortable: "price", show: true, align: "center"},
                    {field: "views", title: "Vies", sortable: "views", show: true, align: "center"}
                ];
                $.each($scope.dataTop.mostView, function (_k, _v) {
                    dataBest.push({
                        name: _v.name,
                        price: _v.price,
                        views: parseFloat(_v.views)
                    })
                });
                $scope.bestTableParams = new NgTableParams({
                    // initial sort order
                    sorting: {views: "desc"},
                    count: 10
                }, {
                    counts: [],
                    paginationMaxBlocks: 13,
                    paginationMinBlocks: 2,
                    data: dataBest
                });
            }
        }

        if (_index == 'newCus') {
            dataBest = [];
            $scope.bestTitle = ' Newest Customers';
            if ($scope.dataTop.newCus == '') {
                showLoad();
                $scope.getDataTopFromServer(0, 'newCus');
            } else {
                $scope.cols = [
                    {field: "name", title: "Name", show: true, align: "left"},
                    {
                        field: "numofOrder",
                        title: "Number of Order",

                        show: true,
                        align: "center"
                    },
                    {field: "sum_amount", title: "Total", show: true, align: "center"}
                ];
                $.each($scope.dataTop.newCus, function (_k, _v) {
                    dataBest.push({
                        name: _v.name,
                        numofOrder: (_v.numofOrder),
                        sum_amount: (_v.sum_amount)
                    })
                });
                $scope.bestTableParams = new NgTableParams({
                    // initial sort order
                    sorting: {views: "desc"},
                    count: 10
                }, {
                    counts: [],
                    paginationMaxBlocks: 13,
                    paginationMinBlocks: 2,
                    data: dataBest
                });
            }
        }

        if (_index == 'bestCus') {
            dataBest = [];
            $scope.bestTitle = ' Best Customers';
            if ($scope.dataTop.bestCus == '') {
                showLoad();
                $scope.getDataTopFromServer(0, 'bestCus');
            } else {
                $scope.cols = [
                    {field: "name", title: "Name", show: true, align: "left"},
                    {
                        field: "numOfOrder",
                        title: "Number of Order",

                        show: true,
                        align: "center"
                    },
                    {field: "sum_amount", title: "Total", show: true, align: "center"}
                ];
                $.each($scope.dataTop.bestCus, function (_k, _v) {
                    dataBest.push({
                        name: _v.name,
                        numOfOrder: (_v.numOfOrder),
                        sum_amount: (_v.sum_amount)
                    })
                });
                $scope.bestTableParams = new NgTableParams({
                    // initial sort order
                    sorting: {views: "desc"},
                    count: 10
                }, {
                    counts: [],
                    paginationMaxBlocks: 13,
                    paginationMinBlocks: 2,
                    data: dataBest
                });
            }
        }


        if (_index == 'topSearch') {
            dataBest = [];
            $scope.bestTitle = ' Top Search';
            if ($scope.dataTop.topSearch == '') {
                showLoad();
                $scope.getDataTopFromServer(0, 'topSearch');
            } else {
                $scope.cols = [
                    {field: "name", title: "Search Term", show: true, align: "left"},
                    {
                        field: "num_results",
                        title: "Number of Result",

                        show: true,
                        align: "center"
                    },
                    {field: "popularity", title: "Popularity", show: true, align: "center"}
                ];
                $.each($scope.dataTop.topSearch, function (_k, _v) {
                    dataBest.push({
                        name: _v.name,
                        num_results: (_v.num_results),
                        popularity: parseFloat(_v.popularity)
                    })
                });
                $scope.bestTableParams = new NgTableParams({
                    // initial sort order
                    sorting: {popularity: "desc"},
                    count: 10
                }, {
                    counts: [],
                    paginationMaxBlocks: 13,
                    paginationMinBlocks: 2,
                    data: dataBest
                });
            }
        }
    };

    $scope.getDataTopFromServer = function (filter, type) {
        var _url = '';
        if (type == 'mostView') {
            _url = window.urlMostViewController;
        }
        if (type == 'newCus') {
            _url = window.urlNewCustomerController;
        }
        if (type == 'bestCus') {
            _url = window.urlBestCustomerController;
        }
        if (type == 'topSearch') {
            _url = window.urlTopSearchController;
        }
        $http({
            method: 'GET',
            url: _url + '?filter=' + filter
            //headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function (response) {
                //console.log('OK');
                hideLoad();
                var dataBest = [];
                if (type == 'mostView') {
                    $scope.dataTop.mostView = response.data;
                    $scope.cols = [
                        {field: "name", title: "Name", sortable: "name", show: true, align: "left"},
                        {field: "price", title: "Price", sortable: "price", show: true, align: "center"},
                        {field: "views", title: "Vies", sortable: "views", show: true, align: "center"}
                    ];
                    $.each($scope.dataTop.mostView, function (_k, _v) {
                        dataBest.push({
                            name: _v.name,
                            price: _v.price,
                            views: parseFloat(_v.views)
                        })
                    });
                    $scope.bestTableParams = new NgTableParams({
                        // initial sort order
                        sorting: {views: "desc"},
                        count: 10
                    }, {
                        counts: [],
                        paginationMaxBlocks: 13,
                        paginationMinBlocks: 2,
                        data: dataBest
                    });
                }

                if (type == 'newCus') {
                    $scope.dataTop.newCus = response.data;
                    $scope.cols = [
                        {field: "name", title: "Name", show: true, align: "left"},
                        {
                            field: "numofOrder",
                            title: "Number of Order",

                            show: true,
                            align: "center"
                        },
                        {field: "sum_amount", title: "Total", show: true, align: "center"}
                    ];
                    $.each($scope.dataTop.newCus, function (_k, _v) {
                        dataBest.push({
                            name: _v.name,
                            numofOrder: (_v.numofOrder),
                            sum_amount: (_v.sum_amount)
                        })
                    });
                    $scope.bestTableParams = new NgTableParams({
                        // initial sort order
                        sorting: {views: "desc"},
                        count: 10
                    }, {
                        counts: [],
                        paginationMaxBlocks: 13,
                        paginationMinBlocks: 2,
                        data: dataBest
                    });
                }


                if (type == 'bestCus') {
                    $scope.dataTop.bestCus = response.data;
                    $scope.cols = [
                        {field: "name", title: "Name", show: true, align: "left"},
                        {
                            field: "numOfOrder",
                            title: "Number of Order",

                            show: true,
                            align: "center"
                        },
                        {field: "sum_amount", title: "Total", show: true, align: "center"}
                    ];
                    $.each($scope.dataTop.bestCus, function (_k, _v) {
                        dataBest.push({
                            name: _v.name,
                            numOfOrder: (_v.numOfOrder),
                            sum_amount: (_v.sum_amount)
                        })
                    });
                    $scope.bestTableParams = new NgTableParams({
                        // initial sort order
                        sorting: {views: "desc"},
                        count: 10
                    }, {
                        counts: [],
                        paginationMaxBlocks: 13,
                        paginationMinBlocks: 2,
                        data: dataBest
                    });
                }

                if (type == 'topSearch') {
                    $scope.dataTop.topSearch = response.data;
                    $scope.cols = [
                        {field: "name", title: "Search Term", show: true, align: "left"},
                        {
                            field: "num_results",
                            title: "Result",

                            show: true,
                            align: "center"
                        },
                        {field: "popularity", title: "Popularity", show: true, align: "center"}
                    ];
                    $.each($scope.dataTop.topSearch, function (_k, _v) {
                        dataBest.push({
                            name: _v.name,
                            num_results: (_v.num_results),
                            popularity: parseFloat(_v.popularity)
                        })
                    });
                    $scope.bestTableParams = new NgTableParams({
                        // initial sort order
                        sorting: {popularity: "desc"},
                        count: 10
                    }, {
                        counts: [],
                        paginationMaxBlocks: 13,
                        paginationMinBlocks: 2,
                        data: dataBest
                    });
                }

            }, function (response) {
                hideLoad();
                console.log('Error');
            }
        )
        ;
    };

    $(document).ready(function () {
        $scope.changeMeasure(0);
    });
}
]);
dashboard.service('switchStore', function ($http) {
    this.switch = function (storeId) {
        $http({
            method: 'GET',
            url: window.urlSwitchStore + '?store_id=' + storeId
        }).then(function (response) {
            iLog('seted Store: ' + response.data.store_id);
        }, function () {
            iLog('setStore Failed', null, 5);
        });
    }
});

