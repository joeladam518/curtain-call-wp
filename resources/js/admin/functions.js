import $ from 'jquery';

/**
 * @param {string|number} production_id
 * @param {string|number} castcrew_id
 * @param {string} type
 * @param {string} name
 * @returns {string}
 */
export function generateFormGroupHtml({production_id, castcrew_id, type = '', name = ''}) {
    castcrew_id = parseInt(castcrew_id, 10);
    production_id = parseInt(production_id, 10);
    let passed_validation = true;
    if (!production_id) {
        passed_validation = false;
    }
    if (!castcrew_id) {
        passed_validation = false;
    }
    if (type !== 'cast' && type !== 'crew') {
        passed_validation = false;
    }
    if (!passed_validation) {
        return '';
    }

    const input_name = `ccwp_add_${type}_to_production`;
    const form_group_id = `ccwp-production-${type}-${castcrew_id}`;

    return [
        `<div class="form-group ccwp-production-castcrew-form-group" id="${form_group_id}">`,
            `<input type="hidden" name="${input_name}[${castcrew_id}][cast_and_crew_id]" value="${castcrew_id}">`,
            `<input type="hidden" name="${input_name}[${castcrew_id}][production_id]" value="${production_id}">`,
            `<input type="hidden" name="${input_name}[${castcrew_id}][type]" value="cast">`,
            '<div class="ccwp-row">',
                '<div class="ccwp-col name-col">',
                    `<div class="ccwp-castcrew-name">${name}</div>`,
                '</div>',
                '<div class="ccwp-col role-col">',
                    `<input type="text" name="${input_name}[${castcrew_id}][role]" placeholder="role" value="">`,
                '</div>',
                '<div class="ccwp-col billing-col">',
                    `<input type="text" name="${input_name}[${castcrew_id}][custom_order]" placeholder="custom order" value="">`,
                '</div>',
                '<div class="ccwp-col action-col">',
                    `<button type="button" class="button ccwp-production-castcrew-remove-btn" data-target="${form_group_id}">Delete</button>`,
                '</div>',
            '</div>',
        '</div>',
    ].join('\n');
}

/**
 * @returns {void}
 */
export function removeProductionCastCrew() {
    const target_div  = $(this).data('target');
    $('#' + target_div).remove();
}

/**
 * @returns {void}
 */
export function addProductionCast() {
    const production_id = $('#post_ID').val();
    const castcrew_id = $('#ccwp-production-cast-select').val();
    const castcrew_name = $(`#ccwp-production-cast-select option[value="${castcrew_id}"]`).text();

    const form_group_html = generateFormGroupHtml({
        production_id: production_id,
        castcrew_id: castcrew_id,
        type: 'cast',
        name: castcrew_name
    });

    $('#ccwp-production-cast-wrap').append(form_group_html);

    $('.ccwp-production-castcrew-remove-btn')
        .off('click')
        .on('click', removeProductionCastCrew);
}

/**
 * @returns {void}
 */
export function addProductionCrew() {
    const production_id = $('#post_ID').val();
    const castcrew_id = $('#ccwp-production-crew-select').val();
    const castcrew_name = $(`#ccwp-production-crew-select option[value="${castcrew_id}"]`).text();

    const form_group_html = generateFormGroupHtml({
        production_id: production_id,
        castcrew_id: castcrew_id,
        type: 'crew',
        name: castcrew_name
    });

    $('#ccwp-production-crew-wrap').append(form_group_html);

    $('.ccwp-production-castcrew-remove-btn')
        .off('click')
        .on('click', removeProductionCastCrew);
}
