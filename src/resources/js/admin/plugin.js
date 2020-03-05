import $ from 'jquery';
import { addProductionCast, addProductionCrew, removeProductionCastCrew } from './functions.js';
import Pikaday from 'pikaday';
import 'select2';

$(function() {
    const $castcrew_post_title_input = $('body.post-type-ccwp_cast_and_crew input[name="post_title"]');
    const $production_post_title_input = $('body.post-type-ccwp_production input[name="post_title"]');

    // Disable post title input box for production posts
    if ($production_post_title_input.length){
        $production_post_title_input
            .prop('disabled', true)
            .after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Production details section.</p>');
    }
    // Disable post title input box for cast and crew posts
    if ($castcrew_post_title_input.length){
        $castcrew_post_title_input
            .prop('disabled', true)
            .after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Cast and Crew details section.</p>');
    }

    // Inject datepicker into admin forms
    const $datepicker_input = $('.ccwp_datepicker_input');
    if ($datepicker_input.length) {
        $datepicker_input.each(function(index, ele) {
            const picker = new Pikaday({
                field: ele,
                format: 'M/D/YYYY',
            });
        });
    }

    const $admin_select_box = $('.ccwp-admin-select-box');
    if ($admin_select_box.length) {
        $admin_select_box.each(function (index, ele) {
            $(ele).select2({
                width: 'resolve',
            });
        });
    }

    if ($('#ccwp_add_cast_and_crew_to_production').length) {
        $('#ccwp-production-cast-add-btn')
            .on('click', addProductionCast);
        $('#ccwp-production-crew-add-btn')
            .on('click', addProductionCrew);
        $('.ccwp-production-castcrew-remove-btn')
            .on('click', removeProductionCastCrew);
    }
});
