/**
 * Created by SITRAKA on 16/11/2018.
 */
var cl_t_group_edited = 'taches_group_edited';
$(document).ready(function(){
    $(document).on('click','.cl_edit_taches_group',function(){
        var action = parseInt($(this).attr('data-action')),
            taches_group = 0;

        if (action !== 1)
        {
            $('.'+cl_t_group_edited).removeClass(cl_t_group_edited);
            $(this).closest('.js_regime').addClass(cl_t_group_edited);
        }

        if (action === 0)
        {
            if (!$(this).hasClass('js_add')) taches_group = parseInt($(this).closest('.js_regime').attr('data-id'));
        }
        else if (action === 1)
        {
            var nom = $('#id_taches_group_nom').val().trim();
            if (nom === '')
            {
                show_info('Non vide','Veuillez remplir le nom du groupe','error');
                $('#id_taches_group_nom').closest('.form-group').addClass('has-error');
                return;
            }
            $('#id_taches_group_nom').closest('.form-group').removeClass('has-error');

            taches_group = parseInt($(this).attr('data-id'));
        }
        else if (action === 2)
        {
            taches_group = parseInt($(this).closest('.js_regime').attr('data-id'));
        }

        edit_taches_group(action,taches_group);
    });
});

function edit_taches_group(action, taches_group)
{
    var regimes = [];
    if (action === 1) regimes = $('#id_taches_group_regime').val();

    $.ajax({
        data: {
            action: action,
            taches_group: taches_group,
            nom: $('#id_taches_group_nom').val(),
            regimes: regimes
        },
        url: Routing.generate('taches_group_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 0)
            {
                show_modal(data,'Ajout Groupe');
                var config = {
                    '.chosen-select'           : {},
                    '.chosen-select-deselect'  : {allow_single_deselect:true},
                    '.chosen-select-no-single' : {disable_search_threshold:10},
                    '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                    '.chosen-select-width'     : {width:"95%"}
                };
                for (var selector in config) {
                    $(selector).chosen(config[selector]);
                }
            }
            else
            {
                charger_taches_group();
            }
        }
    });
}

function charger_taches_group()
{
    close_modal();
    $.ajax({
        data: { },
        url: Routing.generate('taches_groups'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('#tab-taches').find('.panel-body').html(data);
            set_accordion();
        }
    });
}
