// import { addCastCrewToAProduction, addProductionToACastCrew } from './ccwp_admin_relator_functions';
(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
    **/
    
    var ccwpRemoveCastCrewFromProduction = function(e) {
        var $this = $(this);
        var target_div  = $this.data('target');
        $('#' + target_div).remove();
    }
    
    var ccwpAddCastToProduction = function(e) {
        
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
    
    var ccwpAddCrewToProduction = function(e) {
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
            })
        }
        
        if ($('#ccwp_add_cast_and_crew_to_production').length) {
            $('#ccwp-add-cast-to-production-button').click(ccwpAddCastToProduction);
            $('#ccwp-add-crew-to-production-button').click(ccwpAddCrewToProduction);
            $('.ccwp-remove-castcrew-from-production').click(ccwpRemoveCastCrewFromProduction);
        }
    });

})( jQuery );
