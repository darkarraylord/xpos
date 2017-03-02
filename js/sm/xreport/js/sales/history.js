/**
 * Created by vjcspy on 11/2/15.
 */
xApp.controller('salesHistoryCtrl', ['$scope', function ($scope) {
    $scope.dataTable = {
        salesHistory: null, //IMPORTANCE: Link data table trong directive
        salesHistoryInitColumn: null // IMPORTANCE: Link function trong directive
    };

    $scope.setSalesHistory = function (dbTable) {
        $scope.dataTable.salesHistory = dbTable;
    };
    $scope.format = function (d) {
        var rowProduct = '';
        var _s = '';
        jQuery.each(d.items, function (_k, _v) {
            rowProduct += '<tr class="">';
            rowProduct += '<td>' + _v.name + '</td>';
            rowProduct += '<td>' + _v.sku + '</td>';
            rowProduct += '<td>' + _v.qty + '</td>';
            rowProduct += '<td>' + _v.price + '</td>';
            rowProduct += '<td>' + _v.tax + '</td>';
            rowProduct += '<td>' + _v.price_incl_tax + '</td>';
            rowProduct += '</tr>';
        });
        _s += '<div class="panel panel-default">';
        _s += '<div class="panel-heading">';
        _s += '<strong>Order Detail</strong>';
        _s += '</div>';
        _s += '<div class="panel-body">';
        _s += '<table class="table" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
            '<tr>' +
            '<th>Name</th>' +
            '<th>sku</th>' +
            '<th>Qty</th>' +
            '<th>Price</th>' +
            '<th>Tax</th>' +
            '<th>Price incl.Tax</th>' +
            '</tr>' +
            '<tbody>' +
            rowProduct +
            '</tbody>' +
            '<tbody>' +
            '<tr class="border-solid" style="height: 40px;">' +
            '<td colspan="5"></td>' +
            '</tr>' +
            '</tbody>' +
            '<tbody>' +
            '<tr class="dotted_row" style="font-size: 12px;border-top-style: inherit">' +
            '<td class="bold" colspan="5"><strong>Sub total</strong></td>' +
            '<td class="currency total">' + '<strong>' + d.base_subtotal + '</strong>' + '</td>' +
            '</tr>' +
            '<tr class="dotted_row" style="font-size: 12px">' +
            '<td class="bold" colspan="5"><strong>Shipping amount</strong></td>' +
            '<td class="currency total">' + '<strong>' + d.shipping_amount + '</strong>' + '</td>' +
            '</tr>' +
            '<tr class="dotted_row" style="font-size: 12px">' +
            '<td class="bold" colspan="5"><strong>Tax</strong></td>' +
            '<td class="currency total">' + '<strong>' + d.base_tax_amount + '</strong>' + '</td>' +
            '</tr>' +
            '<tr class="dotted_row">' +
            '<td class="bold" colspan="5"><strong>Total</strong></td>' +
            '<td class="currency total">' + '<strong>' + d.grand_total + '</strong>' + '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        _s += '</div>';
        return _s;
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
            "serverSide": true,
            fixedColumns:   true,
            "ajax": window.urlSalesHistoryDataTable,
            "columnDefs": [ //FIXME:MUST HAVE TO FILTER BY CLASS
                {className: "increment_id columnDataNumberTableIz", "targets": [0]},
                {className: "created_at columnDataTableIz", "targets": [1]},
                {className: "status columnDataNumberTableIz", "targets": [2]},
                {className: "shipping_method columnDataTableIz", "targets": [3]},
                {className: "shipping_amount columnDataNumberTableIz", "targets": [4]},
                {className: "method columnDataTableIz", "targets": [5]},
                {className: "base_subtotal columnDataNumberTableIz", "targets": [6]},
                {className: "base_subtotal_refunded columnDataNumberTableIz", "targets": [7]},
                {className: "discount_amount columnDataNumberTableIz", "targets": [8]},
                {className: "shipping_tax_amount columnDataNumberTableIz", "targets": [9]},
                {className: "shipping_tax_refunded columnDataNumberTableIz", "targets": [10]},
                {className: "shipping_refunded columnDataNumberTableIz", "targets": [11]},
                {className: "total_qty_ordered columnDataNumberTableIz", "targets": [12]},
                {className: "total_refunded columnDataNumberTableIz", "targets": [13]},
                {className: "order_currency_code columnDataNumberTableIz", "targets": [14]},
                {className: "customer_email columnDataTableIz", "targets": [15]},
                {className: "store_name columnDataTableIz", "targets": [16]},
                {className: "sku columnDataTableIz", "targets": [17]}
            ],
            "columns": [
                {"data": "increment_id"},
                {"data": "created_at"},
                {"data": "status"},
                {"data": "shipping_method"},
                {"data": "shipping_amount"},
                {"data": "method"},
                {"data": "base_subtotal"},
                {"data": "base_subtotal_refunded"},
                {"data": "discount_amount"},
                {"data": "shipping_tax_amount"},
                {"data": "shipping_tax_refunded"},
                {"data": "shipping_refunded"},
                {"data": "total_qty_ordered"},
                {"data": "total_refunded"},
                {"data": "order_currency_code"},
                {"data": "customer_email"},
                {"data": "store_name"},
                {"data": "sku"}
            ],
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();
                if (api.column(4)
                        .data().length == 0) {
                    // Return if not have data
                    $(api.column(4).footer()).html(
                        ''
                    );
                    $(api.column(6).footer()).html(
                        ''
                    );
                    return;
                }
                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // Total over all pages
                total = api
                    .column(4)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    });

                // Total over this page
                pageTotal = api
                    .column(4, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                pageTotal6 = api
                    .column(6, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(4).footer()).html(
                    '$' + pageTotal.toFixed(2)
                );
                $(api.column(6).footer()).html(
                    '$' + pageTotal6.toFixed(2)
                );
            },
            "oLanguage": {
                "sSearch": "Search every where: "
            },
            //bFilter: false,
            //"autoWidth": true
        });

        //window.salesHistoryTable.on('draw', function () {
        //
        //});

        // change width when column visible/hide
        //_table.on('column-visibility.dt', function (e, settings, column, state) {
        //    console.log(
        //        'Column ' + column + ' has changed to ' + (state ? 'visible' : 'hidden')
        //    );
        //    var numCols = window.salesHistoryTable.columns().nodes().length;
        //    var numOfColsVisi = window.salesHistoryTable.columns(':visible').nodes().length;
        //    _width = numOfColsVisi * 200 + 'px';
        //    console.log(_width);
        //    window.salesHistoryElemTable.css("width", _width);
        //});

        //IMPORTANCE: set variable data table to $scope:
        $scope.setSalesHistory(window.salesHistoryTable);
        $scope.$apply();
        /*init column*/
        $scope.dataTable.salesHistoryInitColumn(null);


        $('#salesHistoryTable_processing').text('');

        _table.on('click', 'tr', function () {
            var tr = $(this);
            var row = table.row(tr);
            //var col = jQuery('td', this).eq(0).text();
            if (window.lastClickDb.lastTr != null) {
                //console.log(row.child());
                //if (row.child() != null) {
                window.lastClickDb.lastTr.removeClass('shown');
                window.lastClickDb.lastRow.child.hide();
                //}
            }

            window.lastClickDb.lastTr = tr;


            window.lastClickDb.lastRow = row;
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                row.child($scope.format(row.data())).show();
                row.child().addClass('rowHover animated fadeInDown');
                tr.addClass('shown');
            }
        });

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

