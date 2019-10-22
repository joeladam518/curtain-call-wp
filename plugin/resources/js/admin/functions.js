export function ccwpRemoveCastCrewFromProduction() {
    var $this = $(this);
    var target_div  = $this.data('target');
    $('#' + target_div).remove();
}

export function ccwpAddCastToProduction() {
    var $cast_div_wrap = $('#ccwp-production-cast-wrap');

    var production_id = $('#post_ID').val();

    var castcrew_id = $('#ccwp-add-cast-to-production-select').val();
    var castcrew_title = $('#ccwp-add-cast-to-production-select option[value="'+ castcrew_id +'"]').text();

    var form_group = '';
    form_group += '<div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-cast-member-'+ castcrew_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_cast_to_production['+ castcrew_id +'][cast_and_crew_id]" value="'+ castcrew_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_cast_to_production['+ castcrew_id +'][production_id]" value="'+ production_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_cast_to_production['+ castcrew_id +'][type]" value="cast">';
    form_group += '<div class="ccwp-show">';
    form_group += '<div class="ccwp-castcrew-name">'+ castcrew_title +'</div>';
    form_group += '<input type="text" name="ccwp_add_cast_to_production['+ castcrew_id +'][role]" placeholder="role" value="">';
    form_group += '<input type="text" name="ccwp_add_cast_to_production['+ castcrew_id +'][custom_order]" placeholder="custom order" value="">';
    form_group += '<button type="button" class="ccwp-remove-castcrew-from-production" data-target="ccwp-production-cast-member-'+ castcrew_id +'">Delete</button>';
    form_group += '</div>';
    form_group += '</div>';

    $cast_div_wrap.append(form_group);

    $('.ccwp-remove-castcrew-from-production').click(ccwpRemoveCastCrewFromProduction);
}

export function ccwpAddCrewToProduction() {
    var $crew_div_wrap = $('#ccwp-production-crew-wrap');

    var production_id = $('#post_ID').val();

    var castcrew_id = $('#ccwp-add-crew-to-production-select').val();
    var castcrew_title = $('#ccwp-add-crew-to-production-select option[value="'+ castcrew_id +'"]').text();

    var form_group = '';
    form_group += '<div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-crew-member-'+ castcrew_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_crew_to_production['+ castcrew_id +'][cast_and_crew_id]" value="'+ castcrew_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_crew_to_production['+ castcrew_id +'][production_id]" value="'+ production_id +'">';
    form_group += '<input type="hidden" name="ccwp_add_crew_to_production['+ castcrew_id +'][type]" value="crew">';
    form_group += '<div class="ccwp-show">';
    form_group += '<div class="ccwp-castcrew-name">'+ castcrew_title +'</div>';
    form_group += '<input type="text" name="ccwp_add_crew_to_production['+ castcrew_id +'][role]" placeholder="role" value="">';
    form_group += '<input type="text" name="ccwp_add_crew_to_production['+ castcrew_id +'][custom_order]" placeholder="custom order" value="">';
    form_group += '<button type="button" class="ccwp-remove-castcrew-from-production" data-target="ccwp-production-crew-member-'+ castcrew_id +'">Delete</button>';
    form_group += '</div>';
    form_group += '</div>';

    $crew_div_wrap.append(form_group);

    $('.ccwp-remove-castcrew-from-production').click(ccwpRemoveCastCrewFromProduction);
}
