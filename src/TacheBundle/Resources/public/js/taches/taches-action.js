/**
 * Created by SITRAKA on 08/11/2018.
 */
$(document).ready(function(){
    $(document).on('click','.cl_taches_action_edit',function(){
        var action = parseInt($(this).attr('data-action')),
            taches_item = 0,
            taches_action = 0,
            tache_liste_action = 0;

        if (action !== 1) $('.'+cl_t_item_edited).removeClass(cl_t_item_edited);
        $(this).closest('.panel').addClass(cl_t_item_edited);
        if (action === 0) taches_item = parseInt($(this).closest('.footable').attr('data-id'));
        else if (action === 1)
        {
            taches_item = parseInt($(this).attr('data-taches_item'));
            taches_action = parseInt($(this).attr('data-taches_action'));
            tache_liste_action = parseInt($('#id_action').val());

            if (tache_liste_action === 0)
            {
                show_info('ERREUR','Action vide','error');
                $('#id_action').closest('.form-group').addClass('has-error');
                return;
            }
            else $('#id_action').closest('.form-group').removeClass('has-error');
        }
        if (action === 2)
        {
            taches_action = parseInt($(this).closest('tr').attr('data-id'));
        }
        edit_taches_action(action, taches_item, taches_action);
    });

    $(document).on('change','.cl_code_taches_action',function(){
        var tache_action = $(this).closest('tr').attr('data-id'),
            code = $(this).val().trim().toUpperCase();

        $.ajax({
            data: {
                tache_action: tache_action,
                code: code
            },
            url: Routing.generate('taches_action_code'),
            type: 'POST',
            dataType: 'html',
            success: function(data){
                show_info('Succès','Modification bien enregistrée');
            }
        });
    });

    $(document).on('change','.cl_code_name_taches_action',function(){
        var tache_action = $(this).closest('tr').attr('data-id'),
            libelle = $(this).val().trim().toUpperCase();

        $.ajax({
            data: {
                tache_action: tache_action,
                libelle: libelle
            },
            url: Routing.generate('taches_action_libelle'),
            type: 'POST',
            dataType: 'html',
            success: function(data){
                show_info('Succès','Modification bien enregistrée');
            }
        });
    });

    $(document).on('change','.cl_code_descri_taches_action',function(){
        var tache_liste_action = $(this).attr('data-id'),
            libelle = $(this).val().trim();

        $.ajax({
            data: {
                tache_liste_action: tache_liste_action,
                libelle: libelle
            },
            url: Routing.generate('taches_action_description'),
            type: 'POST',
            dataType: 'html',
            success: function(data){
                show_info('Succès','Modification bien enregistrée');
            }
        });
    });
});

function edit_taches_action(action, taches_item, tache_action)
{
    tache_action = typeof tache_action !== 'undefined' ? tache_action : 0;
    $.ajax({
        data: {
            action: action,
            taches_item: taches_item,
            tache_action: tache_action,
            tache_liste_action: parseInt($('#id_action').val())
        },
        url: Routing.generate('taches_action_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 0) show_modal(data,'Ajout Action');
            else charger_taches_item($('.'+cl_t_item_edited));
        }
    });
}

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