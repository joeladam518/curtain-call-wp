import jQuery from 'jquery';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
var CastCrewType;
(function (CastCrewType) {
    CastCrewType["Cast"] = "cast";
    CastCrewType["Crew"] = "crew";
})(CastCrewType || (CastCrewType = {}));
(function ($) {
    function generateFormGroupHtml(type, castcrewId, productionId, name = '') {
        castcrewId = parseInt(castcrewId, 10);
        productionId = parseInt(productionId, 10);
        if (!productionId || !castcrewId || (type !== CastCrewType.Cast && type !== CastCrewType.Crew)) {
            return '';
        }
        const formGroupId = `ccwp-production-${type}-${castcrewId}`;
        const inputName = `ccwp_add_${type}_to_production`;
        return [
            `<div class="form-group ccwp-production-castcrew-form-group" id="${formGroupId}">`,
            `<input type="hidden" name="${inputName}[${castcrewId}][cast_and_crew_id]" value="${castcrewId}">`,
            `<input type="hidden" name="${inputName}[${castcrewId}][production_id]" value="${productionId}">`,
            `<input type="hidden" name="${inputName}[${castcrewId}][type]" value="cast">`,
            '<div class="ccwp-row">',
            '<div class="ccwp-col name-col">',
            `<div class="ccwp-castcrew-name">${name || ''}</div>`,
            '</div>',
            '<div class="ccwp-col role-col">',
            `<input type="text" name="${inputName}[${castcrewId}][role]" placeholder="role" value="">`,
            '</div>',
            '<div class="ccwp-col billing-col">',
            `<input type="text" name="${inputName}[${castcrewId}][custom_order]" placeholder="custom order" value="">`,
            '</div>',
            '<div class="ccwp-col action-col">',
            `<button type="button" class="button ccwp-production-castcrew-remove-btn" data-target="${formGroupId}">Delete</button>`,
            '</div>',
            '</div>',
            '</div>',
        ].join('\n');
    }
    function addProductionCastCrew(type) {
        if (type !== CastCrewType.Cast && type !== CastCrewType.Crew) {
            throw new TypeError('Invalid value for type');
        }
        const productionId = $('#post_ID').val();
        const castcrewId = $(`#ccwp-production-${type}-select`).val();
        const name = $(`#ccwp-production-${type}-select option[value="${castcrewId}"]`).text();
        $(`#ccwp-production-${type}-wrap`).append(generateFormGroupHtml(type, castcrewId, productionId, name));
        $('.ccwp-production-castcrew-remove-btn')
            .off('click')
            .on('click', removeCastCrewFormGroup);
    }
    /**
     * @returns {void}
     */
    function disableThePostTitle() {
        const $castcrewTitleInput = $('body.post-type-ccwp_cast_and_crew input[name="post_title"]');
        if ($castcrewTitleInput.length) {
            $castcrewTitleInput
                .prop('disabled', true)
                .after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Cast and Crew details section.</p>');
        }
        const $productionTitleInput = $('body.post-type-ccwp_production input[name="post_title"]');
        if ($productionTitleInput.length) {
            $productionTitleInput
                .prop('disabled', true)
                .after('<p class="ccwp-help-text" style="margin-left:10px;">You can change the post title by editing the Production details section.</p>');
        }
    }
    function removeCastCrewFormGroup() {
        $('#' + $(this).data('target')).remove();
    }
    $(function () {
        disableThePostTitle();
        // Inject datepicker into admin forms
        const $datepickerInput = $('.ccwp_datepicker_input');
        if ($datepickerInput.length) {
            $datepickerInput.each(function (index, ele) {
                const picker = new Pikaday({
                    field: ele,
                    format: 'M/D/YYYY',
                });
            });
        }
        const $castCrewSelectBox = $('.ccwp-admin-select-box');
        if ($castCrewSelectBox.length) {
            $castCrewSelectBox.each(function (index, ele) {
                // select2 is expected to be provided by WP/admin; cast to any to avoid TS typing needs
                $(ele).select2({
                    width: 'resolve',
                });
            });
        }
        if ($('#ccwp_add_cast_and_crew_to_production').length) {
            $('#ccwp-production-cast-add-btn').on('click', () => {
                addProductionCastCrew(CastCrewType.Cast);
            });
            $('#ccwp-production-crew-add-btn').on('click', () => {
                addProductionCastCrew(CastCrewType.Crew);
            });
            $('.ccwp-production-castcrew-remove-btn').on('click', removeCastCrewFormGroup);
        }
    });
})(jQuery);
//# sourceMappingURL=metaboxes.js.map