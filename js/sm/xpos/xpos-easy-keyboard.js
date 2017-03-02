/*
 * huypq
 * 6:08PM 07/21/2015
 */

;(function(window, undefined, $) {
    var config = {
        htmlElementId: {
            searchBox: 'search-box',
            productPool: 'product-info',
            productOptionForm: 'product-option'
        },
        text: {
            barcodeModeNotice: 'Ready to scan barcode...',
            standardModeNotice: ''
        },
        className: {
            itemActive: 'active'
        }
    };

    $(document).ready(function() {
        var productPool = document.getElementById(config.htmlElementId.productPool),
            currentProductItem = productPool.children[0];

        //add to global
        window.CURRENT_PRODUCT_ITEM = currentProductItem;
        window.PRODUCT_POOL = productPool;

        init(document);
    });

    function init(area) {
        /*
         * Temporary
         * Number of amount of product items on a row
         * @risk: must to change this approach when apply responsive design
         */
        window.PRODUCT_ITEMS_ON_ROW_NUMBER = 4;

        if (window.KeyCode === undefined) {
            window.KeyCode = {
                BACKSPACE: 8,
                COMMA: 188,
                DELETE: 46,
                DOWN: 40,
                END: 35,
                ENTER: 13,
                ESCAPE: 27,
                HOME: 36,
                LEFT: 37,
                NUMPAD_ADD: 107,
                NUMPAD_DECIMAL: 110,
                NUMPAD_DIVIDE: 111,
                NUMPAD_ENTER: 108,
                NUMPAD_MULTIPLY: 106,
                NUMPAD_SUBTRACT: 109,
                PAGE_DOWN: 34,
                PAGE_UP: 33,
                PERIOD: 190,
                RIGHT: 39,
                SOFT_BACKSLASH: 192,
                SPACE: 32,
                TAB: 9,
                UP: 38
            }
        }

        /*
         * Initialize Event Listeners
         */
        initEvents(area);
    }

    function initEvents(area) {
        document.getElementById(config.htmlElementId.productOptionForm).addEventListener('keydown', function(event) {
            event.stopPropagation();
        });

        //keyboard navigation events
        area.addEventListener('keydown', function(event) {
            /*
             * Temporary: Apply 'active' state according to current SmartOSC's approach
             * Remove state of 'active' of every product item elements
             * TODO: need to change this behavior
             */
            var keyCode = event.keyCode || event.which;

            /**
             * XPOS 2523
             */
            if (comboCtrlKeyHandler(event))
                return true;

            if (!window.PRODUCT_POOL.children[0]) {
                return false;
            } else {
                for (var i = 0; i < window.PRODUCT_POOL.children.length; i++) {
                    window.PRODUCT_POOL.children[i].className = '';
                }
            }

            /*
             * Temporary
             * @risk: wrong behavior if change children collection
             */
            window.CURRENT_PRODUCT_ITEM = (!!window.CURRENT_PRODUCT_ITEM) ? window.CURRENT_PRODUCT_ITEM : window.PRODUCT_POOL.children[0];
            window.CURRENT_PRODUCT_ITEM.className = '';

            var inputElements = document.getElementById(config.htmlElementId.productOptionForm).querySelectorAll('select');

            switch (keyCode) {
                case window.KeyCode.LEFT:  //left
                    navigateLeft();
                    break;
                case window.KeyCode.UP: //up
                    navigateUp();
                    break;
                case window.KeyCode.RIGHT: //right
                    navigateRight();
                    break;
                case window.KeyCode.DOWN: //down
                    navigateDown();
                    break;
                case window.KeyCode.SOFT_BACKSLASH: // '`'
                    event.preventDefault();
                    document.getElementById(config.htmlElementId.searchBox).focus();
                    break;
            }
        });
    }

    function comboCtrlKeyHandler(event, key) {
        if ((!!window.event && window.event.ctrlKey) || (!!event.ctrlKey)) {
            switch (String.fromCharCode(event.which || event.keyCode)) {
                case 'S':
                    event.preventDefault();
                    event.stopPropagation();

                    window.BARCODE_MODE = window.BARCODE_MODE ? false: true;

                    if (window.BARCODE_MODE) {
                        document.getElementById(config.htmlElementId.searchBox).value = config.text.barcodeModeNotice;
                    } else {
                        document.getElementById(config.htmlElementId.searchBox).value = config.text.standardModeNotice;
                    }
                    document.getElementById(config.htmlElementId.searchBox).select();

                    return true;
            }
        }
        return false;
    }


    function navigateLeft() {
        if (!!window.CURRENT_PRODUCT_ITEM.previousSibling) {
            window.CURRENT_PRODUCT_ITEM = window.CURRENT_PRODUCT_ITEM.previousSibling;
        } else {
            window.CURRENT_PRODUCT_ITEM = window.PRODUCT_POOL.children[0];
        }
        window.CURRENT_PRODUCT_ITEM.className = config.className.itemActive;
        window.CURRENT_PRODUCT_ITEM.querySelector('a[id^="product"]').focus();
    }

    function navigateUp() {
        for (var i = 0; i < window.PRODUCT_ITEMS_ON_ROW_NUMBER; i++) {
            if (!!window.CURRENT_PRODUCT_ITEM.previousSibling) {
                window.CURRENT_PRODUCT_ITEM = window.CURRENT_PRODUCT_ITEM.previousSibling;
            } else {
                window.CURRENT_PRODUCT_ITEM = window.PRODUCT_POOL.children[0];
                break;
            }
        }
        window.CURRENT_PRODUCT_ITEM.className = config.className.itemActive;
        window.CURRENT_PRODUCT_ITEM.querySelector('a[id^="product"]').focus();
    }

    function navigateRight() {
        if (!!window.CURRENT_PRODUCT_ITEM.nextSibling) {
            window.CURRENT_PRODUCT_ITEM = window.CURRENT_PRODUCT_ITEM.nextSibling;
        } else {
            window.CURRENT_PRODUCT_ITEM = window.PRODUCT_POOL.children[window.PRODUCT_POOL.children.length - 1];
        }
        window.CURRENT_PRODUCT_ITEM.className = config.className.itemActive;
        window.CURRENT_PRODUCT_ITEM.querySelector('a[id^="product"]').focus();
    }

    function navigateDown() {
        for (var i = 0; i < window.PRODUCT_ITEMS_ON_ROW_NUMBER; i++) {
            if (!!window.CURRENT_PRODUCT_ITEM.nextSibling) {
                window.CURRENT_PRODUCT_ITEM = window.CURRENT_PRODUCT_ITEM.nextSibling;
            } else {
                window.CURRENT_PRODUCT_ITEM = window.PRODUCT_POOL.children[window.PRODUCT_POOL.children.length - 1];
                break;
            }
        }
        window.CURRENT_PRODUCT_ITEM.className = config.className.itemActive;
        window.CURRENT_PRODUCT_ITEM.querySelector('a[id^="product"]').focus();
    }

})(window, undefined, jQuery);
