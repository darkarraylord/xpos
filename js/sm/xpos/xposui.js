(function ($) {

// Document Ready

    $(document).ready(function () {

        $('#config-button').click(function () {
            if ($('#config-button').hasClass("hide")) {
                $('#config-panel').animate({
                        width: "80px",
                        opacity: "1"
                    }
                );
                $('#config-button').removeClass('hide').addClass('show');
                $('#sidebar').show(0);
                $('.xpos-wrapper').addClass("wrapper-active");
                $('#i_search').css('width','190px');
            } else {
                $('#config-panel').animate({
                    width: "0%",
                    opacity: "1"
                });
                $('#config-button').removeClass('show').addClass('hide');
                $('#sidebar').hide(0);
                $('.xpos-wrapper').removeClass("wrapper-active");
                $('#i_search').css('width','265px');
            }

        })

        $('#category-selected i').click(function () {
            if ($('#category-selected').hasClass("hide")) {
                jQuery('#category-list').slideDown('slow');
                jQuery('#category-selected').removeClass('hide').addClass('show');
            } else {
                jQuery('#category-list').slideUp('slow');
                jQuery('#category-selected').removeClass('show').addClass('hide');
            }
        })
        $('#category-info-close a').click(function () {
            $("#category-info").hide('slide', 1000);
            setTimeout(function () {
                $('#category-select').show();
            }, 1000);
        })

        //$('select,input[type=radio],input[type=checkbox]').uniform({selectAutoWidth: false});

    });

})(jQuery);

