/**
 * Created by SITRAKA on 22/11/2018.
 */
$(document).ready(function(){

});

function hide_taches_date_edit()
{
    $('.footable-even').each(function(){
        if ($(this).hasClass('footable-detail-show'))
        {
            $(this).find('.cl_taches_action_edit').removeClass('hidden');
            $(this).find('.cl_taches_date_edit').each(function(){
                if ($(this).hasClass('js_add')) $(this).removeClass('hidden');
            });
        }
        else
        {
            $(this).find('.cl_taches_action_edit').addClass('hidden');
            $(this).find('.cl_taches_date_edit').each(function() {
                if ($(this).hasClass('js_add')) $(this).addClass('hidden');
            });
        }
    });
    $('.footable-odd').each(function(){
        if ($(this).hasClass('footable-detail-show'))
        {
            $(this).find('.cl_taches_action_edit').removeClass('hidden');
            $(this).find('.cl_taches_date_edit').each(function(){
                if ($(this).hasClass('js_add')) $(this).removeClass('hidden');
            });
        }
        else
        {
            $(this).find('.cl_taches_action_edit').addClass('hidden');
            $(this).find('.cl_taches_date_edit').each(function() {
                if ($(this).hasClass('js_add')) $(this).addClass('hidden');
            });
        }
    });
}

