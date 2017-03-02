/**
* huypq@smartosc.com
* 24 03 2015
*
* Helpful functions (optional functions) stand here!
*/
;(function(window, undefined, $) {
    jQuery(document).ready(function() {
        initEnvironmentEvents();
    });

    function initEnvironmentEvents() {
        window.addEventListener('online', function(event) {
            goOnline();
        });
        window.addEventListener('offline', function(event) {
            goOffline();
        });
    }

    /**
    * huypq@smartosc.com
    * 24/03/2015
    * Console log only if verbose log flag is set true (DEVELOPER MODE)
    */
    function prelog(thing, decoration) {
        if (window.VERBOSE_LOG) {
            if (decoration) {
                thing = '%c' + thing;
                console.log(thing, decoration);
            } else {
                console.log(thing);
            }

        }
        return window.VERBOSE_LOG;
    }

    /**
    * huypq@smartosc.com
    * 03/04/2015
    * Keep selected option of select HtmlElement in html aspect
    */
    function preserveSelectedOption(event, elem) {
        [].forEach.call(elem.options, function(_value, _index) {
            if (_index == elem.selectedIndex) {
                _value.setAttribute('selected', true);
            } else {
                _value.removeAttribute('selected');
            }
        });
    }

    //add to global scope
    window.VERBOSE_LOG = true;
    window.prelog = prelog;
    window.preserveSelectedOption = preserveSelectedOption;
})(window, undefined, jQuery);

function unFormatCurrency(price, format) {
    var decimalSymbol = format.decimalSymbol == undefined ? "," : format.decimalSymbol;
    var groupSymbol = format.groupSymbol == undefined ? "." : format.groupSymbol;

    return parseFloat(String(price).replace(RegExp('[' + groupSymbol + ']', 'g'), '').replace(decimalSymbol, '.'));
}

function enterToChange(element, event){
    if (event.keyCode == 13) {
        jQuery(element).trigger('change');
    }
}
/* XConfig class */
function XConfig(config) {
    this.config = config;
}

XConfig.prototype.getConfig = function (path) {
    return this.config[path];
};

XConfig.prototype.subtotalDisplayInclTax = function () {
    return this.getConfig('tax/sales_display/subtotal');
};

/* End XConfig class */
