import {
    ccwpRemoveCastCrewFromProduction,
    ccwpAddCastToProduction,
    ccwpAddCrewToProduction,
} from './functions.js';

import 'jquery-ui/ui/widgets/datepicker.js';
//const Pikaday = require('pikaday');

$(function() {
    // Disable post title input box for production posts
    if ($('body.post-type-ccwp_production input[name="post_title"]').length){
        $('body.post-type-ccwp_production input[name="post_title"]').prop('disabled', true);
        $('body.post-type-ccwp_production input[name="post_title"]').after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Production details section.</p>')
    }
    // Disable post title input box for cast and crew posts
    if ($('body.post-type-ccwp_cast_and_crew input[name="post_title"]').length){
        $('body.post-type-ccwp_cast_and_crew input[name="post_title"]').prop('disabled', true);
        $('body.post-type-ccwp_cast_and_crew input[name="post_title"]').after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Cast and Crew details section.</p>')
    }

    // Inject jQuery UI datepicker into admin forms
    if ($('.ccwp_datepicker_input').length) {
        $('.ccwp_datepicker_input').each(function(index, ele) {
            $(ele).datepicker({
                dateFormat: 'mm/dd/yy',
                beforeShow: function(input, inst) {
                    $('#ui-datepicker-div').addClass('ccwp_datepicker');
                },
            });
            // let picker = new Pikaday({
            //     field: ele,
            //     format: 'M/D/YYYY',
            // });
        });
    }

    if ($('#ccwp_add_cast_and_crew_to_production').length) {
        $('#ccwp-add-cast-to-production-button').click(ccwpAddCastToProduction);
        $('#ccwp-add-crew-to-production-button').click(ccwpAddCrewToProduction);
        $('.ccwp-remove-castcrew-from-production').click(ccwpRemoveCastCrewFromProduction);
    }
});
