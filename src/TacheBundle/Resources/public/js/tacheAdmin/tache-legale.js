/**
 * Created by SITRAKA on 05/09/2018.
 */
$(document).ready(function(){
    $(document).on('click','.cl_tache_resp',function(){
        if (!$(this).hasClass('btn-primary') && $(this).closest('tr').find('.cl_chk_tache_action').is(':checked'))
        {
            $(this).closest('.btn-group').find('.cl_tache_resp').removeClass('btn-primary').addClass('btn-white');
            $(this).removeClass('btn-white').addClass('btn-primary');
        }
    });

    $(document).on('change','.cl_chk_tache_action',function(){
        if (!$(this).is(':checked'))
        {
            var tr = $(this).closest('tr');
            tr.find('.cl_jour_additif').val('0');
            tr.find('.cl_tache_resp').removeClass('btn-primary').addClass('btn-white');
        }
    });

    $(document).on('click','#id_save_tlg_actions',function(){
        save_tlg_actions(1);
    });

    $(document).on('click','#id_delete_tlg',function(){
        save_tlg_actions(2);
    });
});

function save_tlg_actions(action)
{
    var taches = [],
        error = false,
        div_tache = $('#id_tache_legale_edited'),
        nb_cocher = 0,
        heriter = ($('#id_heriter_client').length > 0) ? ($('#id_heriter_client').is(':checked') ? 1 : 0) : 0;

    if (heriter === 0 && action === 1)
    {
        $('#id_table_tlg_actions tbody').find('.cl_chk_tache_action').each(function(){
            var tr = $(this).closest('tr'),
                responsable = parseInt(tr.find('.cl_tache_resp_container .btn-primary').attr('data-value')),
                cocher = $(this).is(':checked'),
                jour_add = parseInt(tr.find('.cl_jour_additif').val()),
                tache_action = tr.attr('data-id'),
                tache_action_old = tr.attr('data-id_old');

            if (cocher) nb_cocher++;

            if (cocher && (isNaN(responsable) || isNaN(jour_add)))
            {
                error = true;
                $(this).closest('tr').addClass('table-danger');
            }
            else $(this).closest('tr').removeClass('table-danger');

            taches.push({
                responsable: responsable,
                jour_add: jour_add,
                cocher: cocher ? 1 : 0,
                tache_action: tache_action,
                tache_action_old: tache_action_old
            });
        });
    }

    if (error)
    {
        show_info('Erreur','Pas de responsable ou jour non numerique','error');
        return;
    }

    $.ajax({
        data: {
            tache: JSON.stringify({
                tache_legale: div_tache.attr('data-id'),
                tache_legale_entity: div_tache.attr('data-id_old'),
                nb_cocher: nb_cocher,
                taches_action: taches,
                client: div_tache.attr('data-client'),
                dossier: div_tache.attr('data-dossier'),
                heriter: heriter,
                action: action
            })
        },
        url: Routing.generate('tache_admin_save_tache_entity_lg'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('.'+class_cell_edit).find('input[type="checkbox"]').prop('checked', (parseInt(data) === 1));
            close_modal();
            show_info('SUCCES','Modifications bien enregistrées avec succès');
            //$('#tache_test').html(data);return;
        }
    });
}
