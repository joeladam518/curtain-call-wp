import $ from 'jquery';
import { addProductionCast, addProductionCrew, removeProductionCastCrew } from './functions.js';
import Pikaday from 'pikaday';
import 'select2';

$(function() {
    const $castcrew_post_title_input = $('body.post-type-ccwp_cast_and_crew input[name="post_title"]');
    const $production_post_title_input = $('body.post-type-ccwp_production input[name="post_title"]');
    const $date_picker_input = $('.ccwp_datepicker_input');
    const $select_box = $('.ccwp-admin-select-box');

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
    if ($date_picker_input.length) {
        $date_picker_input.each(function(index, ele) {
            const picker = new Pikaday({
                field: ele,
                format: 'M/D/YYYY',
            });
        });
    }

    if ($select_box.length) {
        $select_box.select2({
            width: 'resolve',
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
