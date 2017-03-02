/**
 * Created by vjcspy on 11/2/15.
 */
var xApp = angular.module('xReportApp', ['daterangepicker', 'ui.bootstrap','xNotify'], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('//');
    $interpolateProvider.endSymbol('//');

});

xApp.service('switchStore', function ($http) {
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
window.firstTimeLoadStore = true;
xApp.controller('xReportController', ['$scope', 'switchStore', function ($scope, switchStore) {
    $scope.$watch('switchStore.currentStore', function (value) {
        if (!!window.firstTimeLoadStore) {
            window.firstTimeLoadStore = false;
            return;
        }
        switchStore.switch($scope.switchStore.currentStore);
        location.reload();
    });
}]);

//active all tooltip bootstrap
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();


    //nav-bar
    jQuery('.toggle-nav').click(function () {
        if (jQuery('.sidebar ul').is(":visible") === true) {
            jQuery('#page-wrapper').css({
                'margin-left': '0px'
            });
            jQuery('.sidebar').removeClass('animated fadeInLeft').css({
                'margin-left': '-250px'
            });
            jQuery('.sidebar ul').hide();
            jQuery("#container").addClass("sidebar-closed");
        } else {
            jQuery('#page-wrapper').css({
                'margin-left': '250px'
            });
            jQuery('.sidebar ul').show();
            jQuery('.sidebar').addClass('animated fadeInLeft').css({
                'margin-left': '0'
            });
            jQuery("#container").removeClass("sidebar-closed");
        }
    });
    jQuery('.toggle-nav').click();
    //$("html").niceScroll({styler:"fb",cursorcolor:"#000"});

});


