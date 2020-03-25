/**
 * Created by SITRAKA on 23/11/2018.
 */
$(document).ready(function(){
    $(document).on('click','.cl_taches_entity_edit',function(){
        save_taches_entitys($(this));
    });
});

function save_taches_entitys(btn)
{
    var dossier = parseInt(btn.attr('data-dossier')),
        taches_dates = [],
        error = false;

    btn.closest('.row').find('.cl_chk_tache_action').each(function(){
        var tr = $(this).closest('tr'),
            taches_date = tr.find('.cl_id_taches_date').attr('data-id'),
            checked = $(this).is(':checked') ? 1 : 0,
            jour_add = parseInt(tr.find('.cl_jour_additif').val()),
            responsable = parseInt(tr.find('.cl_tache_resp_container').find('span.btn-primary').attr('data-value')),
            message = '',
            champFiscale = tr.find('.row').attr('data-champs');

        if (isNaN(responsable))
        {
            message += 'Choisir le responsable<br>';
            responsable = 0;
        }
        if (isNaN(jour_add))
        {
            message += 'Le jour doit être un nombre';
            jour_add = 0;
        }

        if (message !== '' && checked === 1)
        {
            show_info('error',message,'error');
            tr.addClass('danger');
            error = true;
        }
        else tr.removeClass('danger');
        taches_dates.push({ id:taches_date, s:checked, r:responsable, j:jour_add, chmp: champFiscale });
    });

    if (error) return;

    $.ajax({
        data: {
            dossier: dossier,
            taches_dates: taches_dates,
            taches: parseInt($('#id_taches_item_container_pop_up').attr('data-taches'))
        },
        url: Routing.generate('taches_param_save_entity'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (parseInt(data) === 1 || parseInt(data) === 0)
            {
                show_info('SUCCES','Modification bien enregistrée avec succes');
                var num_modal = parseInt($('#modal-ui').attr('data-id'));
                for (var i = num_modal - 1; i >= 0; i--)
                    $('#modal-ui-'+i).remove();
                set_val_check(parseInt(data) === 1);
            }
            else show_info('REESSAYER','Une erreur c est produite pendant l enregistrement','error');
        }
    });
}