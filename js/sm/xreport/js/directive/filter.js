/**
 * Created by vjcspy on 11/3/15.
 */
window.fistInitDateRage = true;
xApp.directive('izFilterContainer', function () {
    var _s = '';
    _s += '<div class="row filter-controls">';
    _s += '<div class="panel panel-default">';
    _s += '<div class="panel-body body-iz-filter" ng-transclude>';
    _s += '</div>';
    _s += '</div>';
    _s += '</div>';
    _s += '</div>';
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            izDatatable: '=',
            izDataColumnIndex: '&',
            izDataTableInitColumn: '='
        },
        controller: ['$scope', function ($scope) {
            var dataSearch = [];
            var dataItemsSelectedInControl = {};
            $scope.currentCriterial = null; // current criterial: mac dinh =null;

            /*TODO: FILTER*/
            this.removeAllFilter = function () {
                $.each(dataSearch, function (_k, _v) {
                    _classColumn = '.' + _v.columnId;
                    $scope.izDatatable.column(_classColumn)
                        .search('');
                });
                $scope.izDatatable.draw();
                dataSearch = [];
            };

            this.filterColumn = function () {
                if ($scope.izDatatable == null) {
                    iLog("can't filter", null, 5);
                    return;
                }

                // Trong trường hợp có criterial thì sẽ thêm vào global search
                if ($scope.currentCriterial != null) {
                    $scope.izDatatable.search($scope.currentCriterial);
                }

                //console.log($scope.izDatatable);
                $.each(dataSearch, function (_k, _v) {

                    _classColumn = '.' + _v.columnId;
                    _dataColumn = _v.columnId + ':data';

                    /*
                     * Kiem tra xem control cua column ay co duoc checked khong. Neu khong duoc check thi khong filter theo column day.
                     * */
                    if (dataItemsSelectedInControl[_v.columnId] == false) {
                        $scope.izDatatable.column(_classColumn)
                            .search('');
                        return true;
                    }
                    //console.log(dataItemsSelectedInControl[_v.columnId]);

                    $scope.izDatatable.column(_classColumn)
                        .search(_v.searchValue);
                });
                $scope.izDatatable.draw();
            };

            this.addDataSeach = function (dataObj) {
                _isExisted = false;
                $.each(dataSearch, function (_k, _v) {
                    if (_v.columnId == dataObj.columnId) {
                        _isExisted = true;
                        dataSearch[_k].searchValue = dataObj.searchValue;
                    }
                });
                if (!_isExisted) {
                    dataSearch.push(dataObj);
                }
            };

            /*TODO: Thao tac voi control*/
            //set data nhung item duoc select cho container
            this.setDataItemsSelectedInControl = function (data) {
                dataItemsSelectedInControl = data;
                //console.log(dataItemsSelectedInControl);
            };

            /*TODO: CRITERIAL*/
            //group by criterial:
            this.groupByCriterial = function (_columnId) {

                /*code below to remove filter but interface not reset. */
                /*FIXME: will check again*/
                if (false) {
                    this.setCurrentCriterial(_columnId);
                    $.each(dataSearch, function (_k, _v) {
                        _classColumn = '.' + _v.columnId;
                        $scope.izDatatable.column(_classColumn)
                            .search('');
                    });
                    dataSearch = [];
                }

                $scope.izDatatable.search(_columnId).draw();
            };
            this.setCurrentCriterial = function (_columnId) {
                $scope.currentCriterial = _columnId;
            };

            /*TODO: show/hiden column*/
            $scope.izDataColumnIndex = $scope.izDataColumnIndex();
            //console.log($scope.izDataColumnIndex);

            //cominicate with child controller
            this.getDataColumnDisplay = function () {
                return $scope.izDataColumnIndex;
            };
            this.setDataColumnDisplay = function (_arrayColumnData) {
                $scope.izDataColumnIndex = _arrayColumnData;
            };


            this.displayColumn = function (arrayDatacolumn) {
                // arrayDatacolumn: truyen len id nhung column duoc show
                $.each($scope.izDataColumnIndex, function (_k, _v) {
                    _classColumn = '.' + _v.columnId;
                    _column = $scope.izDatatable.column(_classColumn);

                    if (arrayDatacolumn == null) {
                        /*init column - hoac sua trong criteria*/
                        if (_v.show == true || _v.defaultCriteria == true)
                            _column.visible(true);
                        else
                            _column.visible(false);
                    } else {
                        //Neu la criteria thi se khong lam gi
                        if (_v.defaultCriteria == true || _v.isCriteria == true)
                            return true;

                        var show = false;
                        $.each(arrayDatacolumn, function (k, v) {
                            if (_v.columnId == v) {
                                show = true;
                                $scope.izDataColumnIndex[_k].show = true;
                            }
                        });

                        if (show)
                            _column.visible(true);
                        else
                            _column.visible(false);

                        // Toggle the visibility
                        //column.visible(!column.visible());
                    }
                });
            };

            $scope.izDataTableInitColumn = this.displayColumn;

        }],
        link: function (scope, elem, attrs) {
        },
        template: _s
    };
});

xApp.directive('izFilterControl', function () {
    var _s = '';
    _s += '<div class="col-lg-10">';
    _s += '<div class="report-filter-control" style="float: left">';
    _s += '<form class="form-inline">';
    _s += '<div class="btn-group btn-group-filter-main form-group" data-toggle="tooltip" title="Add/Remove Filter">';
    _s += '<button type="button" class="btn btn-default dropdown-toggle btn-filter form-control" data-toggle="dropdown"><i class="fa fa-filter fa-fw"></i></button>';
    _s += '<ul class="dropdown-menu" role="menu">';
    _s += '<li class="remove-filters"><a href="javascript:void(0)" ng-click="removeAllFilter()"><span class="glyphicon glyphicon-remove"></span>Remove all filters</a></li>';
    _s += '<li class="divider"></li>';
    _s += '<li data-filter-field="id" ng-repeat="item in items"><a href="javascript:void(0)" style="display: block" ng-click="changeState(item.id)"> <input type="checkbox" ng-model="selection.ids[item.id]" name="filter_select_group" id="//item.id//" ng-change="stateChanged(item.id)"/> //item.name//</a></li>';
    _s += '</ul>';
    _s += '</div>&nbsp;';
    _s += '<div class="form-group" ng-transclude>';
    _s += '</div>';
    _s += '</form>';
    _s += '</div>';
    _s += '</div>';
    return {
        require: '^izFilterContainer',
        restrict: 'E',
        transclude: true,
        scope: {
            title: '@'
        },
        controller: ['$scope', function ($scope) {
            $scope.filterContainer = {};
            var items = $scope.items = [];

            $scope.selection = {ids: {}};

            $scope.removeAllFilter = function () {
                // Change state select to false
                $.each($scope.selection.ids, function (_k, _v) {
                    $scope.selection.ids[_k] = false;

                    // thay doi trang thai element theo select
                    $scope.stateChanged(_k);
                });
                $scope.filterContainer.removeAllFilter();
            };

            $scope.changeState = function (_id) {
                // Change state by click in a tag
                $scope.selection.ids[_id] = $scope.selection.ids[_id] != true;
                $scope.stateChanged(_id);
            };

            $scope.stateChanged = function (_id) {
                var elemId = '#elem' + _id, textId = '#elemText' + _id;
                _elem = $(elemId);
                _textElem = $(textId);
                // hide or show element input form
                if ($scope.selection.ids[_id] == true) {
                    _elem.show();
                } else {
                    //console.log('hiden elem');
                    _textElem.val('');
                    _elem.hide();
                }

                //set data selected to container
                $scope.setDataSelectedToContainer();
            };
            $scope.select = function (item) {
                angular.forEach(items, function (i) {
                    i.selected = false;
                });
                item.selected = true;
            };

            this.addItem = function (item) { //Phai la this, neu sử dụng $scope thì không share được
                // Moi khi add vao thi nhung thang nao co thuoc tinh la checked=true thi se duoc hien thi va checked
                if (item.checked == true) {
                    $scope.selection.ids[item.id] = true;
                }
                items.push(item);

            };


        }
        ],
        link: function (scope, element, attrs, tabsCtrl) {
            scope.filterContainer.removeAllFilter = tabsCtrl.removeAllFilter;

            // init function to set data selected items to container
            scope.setDataSelectedToContainer = function () {
                tabsCtrl.setDataItemsSelectedInControl(scope.selection.ids);
            };
            //set data selected to container
            scope.setDataSelectedToContainer();
        },
        template: _s
    };
});

xApp.directive('izFilterControllerFilterType', function () {
    var _s = '';
    _s += '<div class="form-group animated fadeIn" ng-hide="isFilterByType">';
    _s += '<div class="input-group" data-toggle="tooltip" title="Change Type Report"> ';

    _s += '<span class="input-group-addon"><i class="fa fa-at"></i></span>';
    _s += '<select name="selectFilterByType" class="selectpicker form-control" data-live-search="true" ng-options="option.label for option in dataSelectFilterByType track by option.value" ng-model="data.typeSelectFilterByType" ng-change="filterByTypeChange()" aria-describedby="inputGroupSuccess3Status"></select>';
    _s += '</div>';
    _s += '</div>';
    return {
        restrict: 'E',
        require: ['^izFilterControl', '^izFilterContainer'],
        transclude: true,
        scope: {},
        controller: ['$scope', function ($scope) {
        }],
        link: function (scope, element, attrs, filterCtrl) {
            var arrayColumnIndex = filterCtrl[1].getDataColumnDisplay();

            scope.notFilterByType = true;
            scope.dataSelectFilterByType = [];


            //init dataSelectFilterByType and show default type report
            $.each(arrayColumnIndex, function (_k, _v) {
                if (_v.isCriteria == true) {
                    scope.notFilterByType = false;
                    scope.dataSelectFilterByType.push({
                        label: _v.name,
                        value: _v.columnId
                    });
                }
                if (_v.defaultCriteria == true) {
                    scope.data = {
                        typeSelectFilterByType: {
                            label: _v.name,
                            value: _v.columnId
                        }
                    };
                }
            });

            //filter by type function
            scope.filterByTypeChange = function () {


                //check xem co filterByType:
                if (scope.notFilterByType === true) {
                    iLog('Not Sort by Criteria');
                    return;
                }

                _columnId = scope.data.typeSelectFilterByType.value;

                /*Code below to check type=country. Current version not support this type*/
                if (_columnId == 'country') {
                    $.notify({
                        // options
                        message: 'Current version does not support filter by type "' + _columnId + '"'
                    }, {
                        // settings
                        type: 'danger'
                    });
                    return;
                }

                //change data column:
                _arrayDataColumn = filterCtrl[1].getDataColumnDisplay();
                _newArrayDataColumn = [];
                $.each(_arrayDataColumn, function (_k, _v) {
                    if (_v.hasOwnProperty('isCriteria')) {
                        _v.show = false;
                        _v.isCriteria = true;
                        _v.defaultCriteria = false;
                    }
                    if (_v.columnId == _columnId) {
                        _v.show = true;
                        _v.isCriteria = true;
                    }
                    _newArrayDataColumn.push(_v);
                });
                filterCtrl[1].setDataColumnDisplay(_newArrayDataColumn);

                //show cot day len: OK
                filterCtrl[1].displayColumn();

                //Show input filter:
                $('.criteriaElem').hide();
                _elem = '#elem' + _columnId;
                $(_elem).fadeIn('slow');

                //Xoa tat ca cac filter criteria:
                $('.textElem').val('');

                //Set current Criteria:

                //renderer lai table
                filterCtrl[1].groupByCriterial(_columnId);
            }

        },
        template: _s
    };
});

xApp.directive('izFilterControlEle', function () {
    var _s = '';
    _s += '<div class="form-group animated fadeIn" id="//\'elem\' +elemId //" style="display://sDisplay//">';
    _s += '<input id="//\'elemText\' +elemId //" type="//elemType//" class="form-control" placeholder="//elemHolder//" style="font-size: 11px;" ng-hide="notText" ng-model="searchValue" ng-keyup="filterData($event)" data-toggle="tooltip" data-placement="bottom" title="Hooray! sdf \n sdf" tooltipFilter="izTooltipFilter">';
    _s += '<select name="selectFilter" class="form-control" ng-options="option.label for option in dataSelect track by option.value" ng-model="dataSelectedOption" ng-hide="notSelect" ng-change="filterData(\'select\')"></select>';
    _s += '<input date-range-picker class="form-control date-picker" type="text" ng-model="dataDatePicker" ng-hide="notDateRange"/>';
    _s += '</div>';
    return {
        require: ['^izFilterControl', '^izFilterContainer'],
        restrict: 'E',
        transclude: true,
        scope: {
            elemName: '@',
            elemId: '@',
            elemHolder: '@',
            elemChecked: '@',
            elemType: '@',
            elemDataSelect: '@'
        },
        link: function (scope, element, attrs, filterControlCtrl) {

            /*Check type input*/
            switch (scope.elemType) {
                case 'text':
                    scope.notText = false;
                    scope.notSelect = true;
                    scope.notDateRange = true;

                    break;
                case 'select':
                    scope.notText = true;
                    scope.notSelect = false;
                    scope.notDateRange = true;

                    /*Data select*/
                    scope.dataSelect = [];
                    scope.elemDataSelect = JSON.parse(scope.elemDataSelect);
                    $.each(scope.elemDataSelect, function (_k, _v) {
                        if (typeof _v.label != 'undefined')
                            scope.dataSelect.push({label: _v.label, value: _v.value});
                    });

                    scope.dataSelectedOption = {
                        label: 'Please Select..',
                        value: '-1'
                    };

                    break;

                case 'daterange':
                    scope.notText = true;
                    scope.notSelect = true;
                    scope.notDateRange = false;
                    scope.dataDatePicker = {startDate: moment(), endDate: moment()};

                    scope.applyDateRange = function (ev, picker) {
                        console.log(ev);
                        console.log(picker);
                    };


                    // send to filter
                    scope.$watch('dataDatePicker', function (newValue, oldValue) {
                        start = newValue.startDate.get('year') + '-';
                        if (parseFloat(newValue.startDate.get('month')) < 9)
                            _startMonth = '0' + (newValue.startDate.get('month') + 1);
                        else
                            _startMonth = (newValue.startDate.get('month') + 1);
                        if (parseFloat(newValue.startDate.get('date')) < 10)
                            _startDate = '0' + (newValue.startDate.get('date'));
                        else
                            _startDate = newValue.startDate.get('date');
                        start += _startMonth + '-' + _startDate;

                        end = newValue.endDate.get('year') + '-';
                        if (parseFloat(newValue.endDate.get('month')) < 9)
                            _endtMonth = '0' + (newValue.endDate.get('month') + 1);
                        else
                            _endtMonth = (newValue.endDate.get('month') + 1);
                        if (parseFloat(newValue.endDate.get('date')) < 10)
                            _endDate = '0' + (newValue.endDate.get('date'));
                        else
                            _endDate = newValue.endDate.get('date');
                        end += _endtMonth + '-' + _endDate;
                        if (window.fistInitDateRage) {
                            window.fistInitDateRage = false;
                        } else {
                            console.log(start);
                            filterControlCtrl[1].addDataSeach({
                                columnId: scope.elemId,
                                searchValue: start + '/' + end
                            });
                            filterControlCtrl[1].filterColumn();
                        }
                    });
                    break;
                default :
                    scope.elemType = 'text';

                    scope.notText = false;
                    scope.notSelect = true;

                    break;
            }

            /*Check option checked*/
            if (scope.elemChecked == 'false')
                scope.sDisplay = 'none';
            else
                scope.sDisplay = 'inline';

            /*Add element for filter button*/
            var ele = {name: scope.elemName, checked: scope.elemChecked, id: scope.elemId};
            filterControlCtrl[0].addItem(ele);


            /*Function to filter data*/
            scope.filterData = function (event) {
                if (event == 'select') {
                    if (scope.elemType == 'select') {
                        if (scope.dataSelectedOption.value == -1)
                            return;
                        filterControlCtrl[1].addDataSeach({
                            columnId: scope.elemId,
                            searchValue: scope.dataSelectedOption.value
                        });
                    }
                    filterControlCtrl[1].filterColumn();
                } else {
                    if (event.keyCode == 13) {
                        filterControlCtrl[1].filterColumn();
                    } else {
                        if (scope.elemType == 'text')
                            filterControlCtrl[1].addDataSeach({columnId: scope.elemId, searchValue: scope.searchValue});
                    }
                }
            };
        },
        template: _s
    };
});

xApp.directive('izFilterControllerElem', function () {
    var _s = '';
    _s += '<div ng-repeat="filterElem in data.filterArray" class="//\'form-group animated fadeIn \' + filterElem.class//" id="//\'elem\' + filterElem.columnId //" ng-style="{display:filterElem.dataDisplay}" style="padding-left: 4px">';
    _s += '<input id="//\'elemText\' +filterElem.columnId //" type="//filterElem.filterType//" class="form-control textElem" placeholder="//filterElem.name//" style="font-size: 11px;" ng-hide="filterElem.dataHide.text" ng-model="data.typeText[filterElem.columnId]" ng-keyup="filterData($event,filterElem.columnId,\'text\')" data-toggle="tooltip" data-placement="bottom" title="" tooltipFilter="izTooltipFilter" isCriterial="filterElem.isCriterial"> ';
    _s += '<select name="selectFilter" class="form-control" ng-options="option.label for option in filterElem.dataSelect track by option.value" ng-model="data.typeSelect[filterElem.columnId]" ng-hide="filterElem.dataHide.select" ng-change="filterData(\'null\',filterElem.columnId,\'select\')"></select> ';
    _s += '<input date-range-picker class="form-control date-picker" type="text" id="//\'elemDateRange\' +filterElem.columnId //" ng-model="data.typeDateRange[filterElem.columnId]" ng-hide="filterElem.dataHide.dateRange" options="filterElem.opt"/> ';
    _s += '</div>';
    return {
        require: ['^izFilterControl', '^izFilterContainer'],
        restrict: 'E',
        scope: {},
        controller: ['$scope', function ($scope) {
            $scope.filterChecked = {};
            $scope.notText = {};
            $scope.searchValue = {};
        }],
        link: function (scope, element, attrs, filterControlCtrl) {
            //init model element:
            scope.data = {
                typeText: {},
                typeDateRange: {},
                typeSelect: {},
                filterArray: []
            };


            scope.initFilterArrayElement = function () {
                //get Filter array element:
                var allFilterArray = filterControlCtrl[1].getDataColumnDisplay();
                scope.data.filterArray.length = 0;
                var isHasCriterial = false;

                $.each(allFilterArray, function (_k, _v) {

                    // check elem xem co duoc show default hay khong
                    if (_v.filterChecked == false && !(_v.defaultCriteria == true))
                        _dataDisplay = 'none';
                    else
                        _dataDisplay = 'inline';


                    // check xem kieu gi
                    _isText = false;
                    _isSelect = false;
                    _isDataRange = false;

                    _optionForDateRange = null;

                    if (_v.filterType == 'text' || _v.filterType == '') {
                        _filterType = 'text';
                        _isText = true;
                    } else if (_v.filterType == 'select') {
                        _filterType = 'select';
                        _isSelect = true;
                        scope.data.typeSelect[_v.columnId] = {
                            label: 'Please Select..',
                            value: ''
                        };
                    } else if (_v.filterType == 'dateRange') {
                        _filterType = 'dateRange';
                        _isDataRange = true;
                        scope.data.typeDateRange[_v.columnId] = {
                            startDate: moment().subtract(6, 'days'),
                            endDate: moment()
                        };
                        _optionForDateRange = {
                            locale: {
                                applyClass: 'btn-green',
                                applyLabel: "Apply",
                                fromLabel: "From",
                                toLabel: "To",
                                cancelLabel: 'Cancel',
                                customRangeLabel: 'Custom range',
                                //daysOfWeek: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
                                firstDay: 1
                                //monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září',
                                //    'Říjen', 'Listopad', 'Prosinec'
                                //]
                            },
                            ranges: {
                                'Today': [moment(), moment()],
                                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                'This Month': [moment().startOf('month'), moment().endOf('month')],
                                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                            },
                            eventHandlers: {
                                'apply.daterangepicker': function (ev, picker) {
                                    // lay theo kieu jquery
                                    //_elem = '#elemDateRange' + _v.columnId;
                                    //newValue = $(_elem).data('daterangepicker');

                                    newValue = scope.data.typeDateRange[_v.columnId];

                                    if (typeof newValue == 'undefined')
                                        return;
                                    start = newValue.startDate.get('year') + '-';
                                    if (parseFloat(newValue.startDate.get('month')) < 9)
                                        _startMonth = '0' + (newValue.startDate.get('month') + 1);
                                    else
                                        _startMonth = (newValue.startDate.get('month') + 1);
                                    if (parseFloat(newValue.startDate.get('date')) < 10)
                                        _startDate = '0' + (newValue.startDate.get('date'));
                                    else
                                        _startDate = newValue.startDate.get('date');
                                    start += _startMonth + '-' + _startDate;

                                    end = newValue.endDate.get('year') + '-';
                                    if (parseFloat(newValue.endDate.get('month')) < 9)
                                        _endtMonth = '0' + (newValue.endDate.get('month') + 1);
                                    else
                                        _endtMonth = (newValue.endDate.get('month') + 1);
                                    if (parseFloat(newValue.endDate.get('date')) < 10)
                                        _endDate = '0' + (newValue.endDate.get('date'));
                                    else
                                        _endDate = newValue.endDate.get('date');
                                    end += _endtMonth + '-' + _endDate;
                                    //console.log(start);
                                    filterControlCtrl[1].addDataSeach({
                                        columnId: _v.columnId,
                                        searchValue: start + '/' + end
                                    });
                                    filterControlCtrl[1].filterColumn();

                                    //scroll after filter:
                                    $('body').scrollTo('#salesHistoryTable_wrapper');
                                }
                            }
                        };
                    }

                    _className = 'normalElem';
                    if (_v.isCriteria === true) {
                        _className = 'criteriaElem';
                        isHasCriterial = true;
                    }
                    // init data element
                    _currentDataElem = {
                        columnId: _v.columnId,
                        dataDisplay: _dataDisplay,
                        filterType: _filterType,
                        name: _v.name,
                        dataHide: {
                            text: !_isText,
                            select: !_isSelect,
                            dateRange: !_isDataRange
                        },
                        dataSelect: (_v.dataSelect),
                        opt: _optionForDateRange,
                        class: _className,
                        isCriterial: isHasCriterial
                    };

                    scope.data.filterArray.push(_currentDataElem);

                    if (_v.isCriteria === true) {
                        return true;
                    }
                    /*Add element for filter button*/
                    var ele = {name: _v.name, checked: _v.filterChecked, id: _v.columnId};
                    filterControlCtrl[0].addItem(ele);


                });
            };
            scope.initFilterArrayElement();

            //active tooltip text:
            $(document).ready(function () {
                var tooltipFilter = $('[tooltipFilter="izTooltipFilter"]');
                _text = 'Exactly: "data" </br>First: ^data </br>More/Less: >data </br>Equal: =data';
                //_text = _text.replace("\\", "");
                tooltipFilter.qtip({ // Grab some elements to apply the tooltip to
                    content: {
                        text: _text
                    },
                    style: {classes: 'qtip-bootstrap myCustomClass'}
                });
            });

            /*Function to filter data*/
            scope.filterData = function (event, columnId, type) {
                if (type == 'select') {
                    //console.log(scope.data.typeSelect[columnId]);
                    if (scope.data.typeSelect[columnId].value == -1)
                        return;
                    filterControlCtrl[1].addDataSeach({
                        columnId: columnId,
                        searchValue: scope.data.typeSelect[columnId].value
                    });
                    filterControlCtrl[1].filterColumn();
                } else if (type == 'text') {
                    if (event.keyCode == 13) {
                        filterControlCtrl[1].filterColumn();
                    } else {
                        filterControlCtrl[1].addDataSeach({
                            columnId: columnId,
                            searchValue: scope.data.typeText[columnId]
                        });
                    }
                }
            };


        },
        template: _s
    };
});

xApp.directive('izFilterAction', function () {
    var _s = '';
    _s += '<div style="top: 10%;" id="myModal" class="modal fade" role="dialog">';
    _s += '<div class="modal-dialog">';
    _s += '<div class="modal-content">';
    _s += '<div class="modal-header">';
    _s += '<button type="button" class="close" data-dismiss="modal" ng-click="dislayColumn()">&times;</button>';
    _s += ' <h4 class="modal-title">Column display</h4>';
    _s += '</div>';
    _s += ' <div class="modal-body">';
    _s += '<form role="form">';
    _s += '<div class="form-group">';
    //_s += '<label>Select Column Display</label>';

    _s += '<select style="height: 169px;" class="form-control" name="repeatSelect" id="repeatSelect" ng-model="modal.columnSelect" multiple>';
    _s += ' <option ng-repeat="option in data.availableOptions" value="//option.columnId//">//option.name//</option>';
    _s += '</select>';
    _s += '</br><strong> Column Selected:</strong>';
    _s += '<button ng-repeat="column in modal.columnSelect" type="button" class="btn btn-outline btn-primary btn-xs" style="margin: 3px" ng-click="removeColumn(column)">//column//</button> ';
    _s += '</div>';
    _s += '</form>';
    _s += '</div>';
    _s += '<div class="modal-footer">';
    _s += '<button type="button" class="btn btn-default" data-dismiss="modal" ng-click="dislayColumn()">Close</button>';
    _s += '</div>';
    _s += '</div>';
    _s += '</div>';
    _s += '</div>';
    _s += '<div class="col-lg-2">';
    _s += ' <div class="report-filter-action" style="float: right" data-toggle="tooltip" title="Add/Remove Column">';
    //_s += '<a class="btn btn-outline btn-primary">Filter<i class="fa fa-filter fa-fw"></i> <span style="display: none">Remove Filters</span></a>&nbsp;';
    _s += '<a class="btn btn-outline btn-default" data-toggle="modal" data-target="#myModal">Customize <i class="fa fa-list"></i></a>';
    _s += '</div>';
    _s += '</div>';
    return {
        require: '^izFilterContainer',
        restrict: 'E',
        controller: ['$scope', function ($scope) {
            $scope.removeColumn = function (col) {
                var index = $scope.modal.columnSelect.indexOf(col);
                if (index > -1) {
                    $scope.modal.columnSelect.splice(index, 1);
                }
            }
        }],
        link: function (scope, element, attrs, izFilterContainerCtrl) {
            scope.getDataColumnDisplay = function () {
                _parentData = izFilterContainerCtrl.getDataColumnDisplay();
                _childData = [];
                $.each(_parentData, function (_k, _v) {
                    //console.log(_v.show);
                    if (_v.show != 'alwayFalse' && !(_v.isCriteria === true))
                        _childData.push(_parentData[_k]);
                });
                return _childData;
            };
            scope.data = {
                availableOptions: scope.getDataColumnDisplay()
            };

            //init selected in modal
            scope.modal = {columnSelect: []};
            $.each(scope.data.availableOptions, function (_k, _v) {
                if (_v.show == true)
                    scope.modal.columnSelect.push(_v.columnId)
            });

            scope.dislayColumn = function () {
                izFilterContainerCtrl.displayColumn(scope.modal.columnSelect);
            }

        },
        template: _s
    };
});
