/**
 * Created by vjcspy on 11/6/15.
 */
xApp.controller('salesHistoryCtrl', ['$scope', function ($scope) {
    $scope.dataTable = {
        salesHistory: null, //IMPORTANCE: Link data table trong directive
        salesHistoryInitColumn: null // IMPORTANCE: Link function trong directive
    };

    $scope.setSalesHistory = function (dbTable) {
        $scope.dataTable.salesHistory = dbTable;
    };

    $(document).ready(function () {

        // De luc nhap vao thi hide cai cu di
        window.lastClickDb = {
            lastTr: null,
            lastRow: null
        };

        window.salesHistoryElemTable = _table = $('#salesHistoryTable');
        window.salesHistoryTable = table = _table.DataTable({
            "processing": true,
            "scrollX": true,
            "paging": true,
            "serverSide": true,
            scrollCollapse: true,
            responsive: true,
            "ajax": window.urlSalesDetailDataTable,
            "columnDefs": [ //FIXME:MUST HAVE TO FILTER BY CLASS
                {className: "method columnDataTableIz", "targets": [0]},
                {className: "status columnDataTableIz", "targets": [1]},
                {className: "shipping_method columnDataTableIz", "targets": [2]},
                {className: "sku columnDataTableIz", "targets": [3]},
                {className: "customer_group_code columnDataTableIz", "targets": [4]},
                {className: "customer_email columnDataTableIz", "targets": [5]},
                {className: "product_type columnDataTableIz", "targets": [6]},
                {className: "order_currency_code columnDataTableIz", "targets": [7]},
                {className: "country columnDataTableIz", "targets": [8]},
                {className: "day_of_week columnDataNumberTableIz", "targets": [9]},
                {className: "hour columnDataNumberTableIz", "targets": [10]},
                {className: "created_at columnDataTableIz", "targets": [11]},
                {className: "quantity columnDataNumberTableIz", "targets": [12]},
                {className: "shipping_amount columnDataNumberTableIz", "targets": [13]},
                {className: "base_subtotal columnDataNumberTableIz", "targets": [14]},
                {className: "base_subtotal_refunded columnDataNumberTableIz", "targets": [15]},
                {className: "discount_amount columnDataNumberTableIz", "targets": [16]},
                {className: "shipping_tax_amount columnDataNumberTableIz", "targets": [17]},
                {className: "shipping_tax_refunded columnDataNumberTableIz", "targets": [18]},
                {className: "shipping_refunded columnDataNumberTableIz", "targets": [19]},
                {className: "total_qty_ordered columnDataNumberTableIz", "targets": [20]},
                {className: "total_refunded columnDataNumberTableIz", "targets": [21]},
                {className: "grand_total columnDataNumberTableIz", "targets": [22]}
            ],
            "columns": [
                {"data": "method"},
                {"data": "status"},
                {"data": "shipping_method"},
                {"data": "sku"},
                {"data": "customer_group_code"},
                {"data": "customer_email"},
                {"data": "product_type"},
                {"data": "order_currency_code"},
                {"data": "country"},
                {"data": "day_of_week"},
                {"data": "hour"},
                {"data": "created_at"},
                {"data": "quantity"},
                {"data": "shipping_amount"},
                {"data": "base_subtotal"},
                {"data": "base_subtotal_refunded"},
                {"data": "discount_amount"},
                {"data": "shipping_tax_amount"},
                {"data": "shipping_tax_refunded"},
                {"data": "shipping_refunded"},
                {"data": "total_qty_ordered"},
                {"data": "total_refunded"},
                {"data": "grand_total"}
            ],
            "oLanguage": {
                "sSearch": "Search every where: "
            },
            //bFilter: false,
            "autoWidth": true,
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();
                if (api.column(12)
                        .data()
                        .length == 0) {
                    // Return if not have data
                    for (_i = 12; _i < 22; _i++) {
                        $(api.column(_i).footer()).html(
                            ''
                        );
                    }

                    return;
                }
                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                for (_i = 12; _i < 22; _i++) {
                    // Total over all pages
                    total = api
                        .column(_i)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        });
                    total = intVal(total); // fix cho truong hop chi ra 1 ket qua
                    //console.log(total);
                    // Update footer
                    if (_i == 12 || _i == 20 || _i == 21)
                        $(api.column(_i).footer()).html(
                            +total.toFixed(0)
                        );
                    else
                        $(api.column(_i).footer()).html(
                            '$' + total.formatMoney(2, '.', ',')
                        );
                }
            }
        });

        //window.salesHistoryTable.on('draw', function () {
        //
        //});

        // change width when column visible/hide
        //_table.on('column-visibility.dt', function (e, settings, column, state) {
        //    //console.log(
        //    //    'Column ' + column + ' has changed to ' + (state ? 'visible' : 'hidden')
        //    //);
        //    var numCols = window.salesHistoryTable.columns().nodes().length;
        //    var numOfColsVisi = window.salesHistoryTable.columns(':visible').nodes().length;
        //    _width = numOfColsVisi * 200 + 'px';
        //    //console.log(_width);
        //    window.salesHistoryElemTable.css("width", _width);
        //});

        //IMPORTANCE: set variable data table to $scope:
        $scope.setSalesHistory(window.salesHistoryTable);
        $scope.$apply();
        /*init column*/
        $scope.dataTable.salesHistoryInitColumn(null);


        $('#salesHistoryTable_processing').text('');

        $('#salesHistoryTable tr').hover(function () {
            $(this).addClass('tableHover');
        }, function () {
            $(this).removeClass('tableHover');
        });

        $('#button').click(function () {
            table.row('.selected').remove().draw(false);
        });
    });
}]);

