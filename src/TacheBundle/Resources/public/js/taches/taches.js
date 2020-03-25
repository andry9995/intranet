/**
 * Created by SITRAKA on 31/10/2018.
 */
var cl_t_taches_edited = 'taches_edited';
$(document).ready(function(){
    set_accordion();
    $(document).on('click','.cl_taches_edit',function(){
        var action = parseInt($(this).attr('data-action')),
            regime = 0,
            taches = 0,
            type = 0;

        if (action !== 1)
        {
            $('.'+cl_t_taches_edited).removeClass(cl_t_taches_edited);
            $(this).addClass(cl_t_taches_edited);
        }
        if (action === 0)
        {
            regime = parseInt($(this).closest('.js_regime').attr('data-id'));
            type = parseInt($(this).closest('.js_regime').attr('data-type'));
            if (!$(this).hasClass('js_add')) taches = parseInt($(this).closest('.panel').attr('data-id'));
        }
        else if (action === 1)
        {
            regime = parseInt($(this).attr('data-regime'));
            taches = parseInt($(this).attr('data-taches'));
            type = parseInt($(this).attr('data-type'));
            var fiscale = parseInt($('#js_prest_fiscal_tache').val().trim());
            var nom = $('#id_taches_nom').val().trim();

            if (nom === '')
            {
                show_info('Non vide','Veuillez remplir le nom de la tache','error');
                $('#id_taches_nom').closest('.form-group').addClass('has-error');
                return;
            }

            if (fiscale === -1)
            {
                show_info('Non vide','Veuillez remplir la prestation fiscale liée à la tâche','error');
                $('#js_prest_fiscal_tache').closest('.form-group').addClass('has-error');
                return;
            }
            $('#id_taches_nom').closest('.form-group').removeClass('has-error');
            $('#js_prest_fiscal_tache').closest('.form-group').removeClass('has-error');
        }
        else if (action === 2)
        {
            regime = parseInt($(this).closest('.js_regime').attr('data-id'));
            taches = parseInt($(this).closest('.panel').attr('data-id'));
        }

        edit_taches(action, regime, taches, type);
    });
});

function set_accordion()
{
    $('#id_rf_accordion').on('show.bs.collapse', function (e) {
        if ($(e.target).closest('.panel').hasClass('js_regime'))
        {
            $(e.target).closest('.panel').find('.cl_taches_edit').removeClass('hidden');
            $(e.target).closest('.panel').find('.cl_edit_taches_group').removeClass('hidden');
            charger_taches($(e.target).closest('.panel'));
        }
        else if ($(e.target).closest('.panel').hasClass('js_taches'))
        {
            $(e.target).closest('.panel').find('.cl_taches_item_edit').removeClass('hidden');
            $(e.target).closest('.panel').find('.cl_taches_edit').each(function(){
                if (!$(this).hasClass('js_add')) $(this).removeClass('hidden');
            });
            charger_taches_item($(e.target).closest('.panel'));
        }
    }).on('hide.bs.collapse', function (e) {
        if ($(e.target).closest('.panel').hasClass('js_regime'))
        {
            $(e.target).closest('.panel').find('.cl_taches_edit').addClass('hidden');
            $(e.target).closest('.panel').find('.cl_edit_taches_group').addClass('hidden');
        }
        else if ($(e.target).closest('.panel').hasClass('js_taches'))
        {
            $(e.target).closest('.panel').find('.cl_taches_item_edit').addClass('hidden');
            $(e.target).closest('.panel').find('.cl_taches_edit').each(function(){
                if (!$(this).hasClass('js_add')) $(this).addClass('hidden');
            });
        }
    });
}

function charger_taches(div)
{
    close_modal();
    var regime = div.attr('data-id'),
        type = div.attr('data-type');
    $.ajax({
        data: {
            regime: regime,
            type: type
        },
        url: Routing.generate('taches'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            div.find('.cl_taches_container').html(data);
            activer_qtip();
        }
    });
}

function edit_taches(action, regime, taches, type)
{
    taches = typeof taches !== 'undefined' ? taches : 0;
    type = typeof type !== 'undefined' ? type : 0;
    var is_tva = false,
        nom = '',
        fiscale = -1;

    if (action === 1)
    {
        is_tva = $('#id_taches_is_tva').is(':checked') ? 1 : 0;
        nom = $('#id_taches_nom').val().trim();
        fiscale = parseInt($('#js_prest_fiscal_tache').val().trim());
    }

    $.ajax({
        data: {
            action: action,
            regime: regime,
            taches: taches,
            is_tva: is_tva,
            nom: nom,
            type: type,
            fiscale: fiscale
        },
        url: Routing.generate('taches_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 0) show_modal(data,'Ajout Tache');
            else if (action === 2) $('.'+cl_t_taches_edited).closest('.js_taches').remove();
            else charger_taches($('.'+cl_t_taches_edited).closest('.js_regime'));
        }
    });
}
