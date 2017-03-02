/**
 * Created by vjcspy on 11/6/15.
 */
xApp.controller('salesHistoryCtrl', ['$scope', function ($scope) {
    $scope.dataTable = {
        salesHistory: null, //IMPORTANCE: Link data table in directive
        salesHistoryInitColumn: null // IMPORTANCE: Link function in directive
    };

    $scope.setSalesHistory = function (dbTable) {
        $scope.dataTable.salesHistory = dbTable;
    };

    $(document).ready(function () {

        window.salesHistoryElemTable = _table = $('#salesHistoryTable');
        window.salesHistoryTable = table = _table.DataTable({
            "processing": true,
            "scrollX": true,
            "scrollY": "600px",
            "paging": false,
            "serverSide": true,
            "bSort": false,
            scrollCollapse: true,
            responsive: true,
            "ajax": window.urlInventoryDataTable,
            "columnDefs": [ //FIXME:MUST HAVE TO FILTER BY CLASS
                {className: "name columnDataTableIz", "targets": [0]},
                {className: "sku columnDataTableIz", "targets": [1]},
                {className: "price columnDataNumberTableIz", "targets": [2]},
                {className: "sum_qty columnDataNumberTableIz", "targets": [3]},
                {className: "sum_total columnDataNumberTableIz", "targets": [4]},
                {className: "sum_invoiced columnDataNumberTableIz", "targets": [5]},
                {className: "sum_refunded columnDataNumberTableIz", "targets": [6]},
                {className: "cost columnDataNumberTableIz", "targets": [7]},
                {className: "discount_amount columnDataNumberTableIz", "targets": [8]},
                {className: "item_qty columnDataNumberTableIz", "targets": [9]},
                {className: "esitmation_data columnDateTableIz", "targets": [10]},
                {className: "created_at columnDataTableIz", "targets": [11]}
            ],
            "columns": [
                {"data": "name"},
                {"data": "sku"},
                {"data": "price"},
                {"data": "sum_qty"},
                {"data": "sum_total"},
                {"data": "sum_invoiced"},
                {"data": "sum_refunded"},
                {"data": "cost"},
                {"data": "discount_amount"},
                {"data": "item_qty"},
                {"data": "esitmation_data"},
                {"data": "created_at"}
            ],
            "oLanguage": {
                "sSearch": "Search every where: "
            },
            //bFilter: false,
            "autoWidth": true,
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();
                if (api.column(2)
                        .data()
                        .length == 0) {
                    // Return if not have data
                    for (_i = 2; _i < 10; _i++) {
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

                for (_i = 2; _i < 10; _i++) {
                    // Total over all pages
                    total = api
                        .column(_i)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        });
                    total = intVal(total); // fix cho truong hop chi ra 1 ket qua
                    // Update footer
                    if (_i == 3 || _i == 9)
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

