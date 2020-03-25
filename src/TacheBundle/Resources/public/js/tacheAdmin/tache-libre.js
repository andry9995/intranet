/**
 * Created by SITRAKA on 05/09/2018.
 */

/*
 id_tache_libre_periode
 id_tache_libre_demarrage
 id_tache_libre_a_partir_de
 id_tache_libre_date_calcul
 id_tache_libre_a_jour
 id_tache_libre_jour_semaine
 */

$(document).ready(function(){
    $(document).on('change','#id_tache_libre_periode',function(){
        lb_change_periode();
    });

    $(document).on('change','#id_tache_libre_a_partir_de',function(){
        lb_change_a_partir_de();
    });

    $(document).on('click','.cl_tache_lb_resp',function(){
        if (!$(this).hasClass('btn-primary'))
        {
            $(this).closest('.cl_tache_libre_container').find('.cl_tache_lb_resp')
                .removeClass('btn-primary').addClass('btn-white');
            $(this).removeClass('btn-white').addClass('btn-primary');
        }
    });

    $(document).on('click','#id_save_tlb_actions',function(){
        save_tlb_actions(1);
    });

    $(document).on('click','#id_delete_tlb',function(){
        save_tlb_actions(2);
    });
});

function lb_change_periode()
{
    var periode = parseInt($('#id_tache_libre_periode').val());
    $('.container').addClass('hidden');
    /*0=ponctuel,1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel*/
    if (periode === 0)
    {
        $('#id_tache_libre_date_calcul').closest('.container').removeClass('hidden');
    }
    else if (periode !== -1)
    {
        $('#id_tache_libre_periode').closest('.container').removeClass('hidden');
        $('#id_tache_libre_demarrage').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_partir_de').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_jour').closest('.container').removeClass('hidden');
        $('#id_tache_libre_jour_semaine').closest('.container').removeClass('hidden');
        lb_change_a_partir_de();
    }
}

function lb_change_a_partir_de()
{
    var a_partir_de = parseInt($('#id_tache_libre_a_partir_de').val());
    if (a_partir_de === 3) $('#id_tache_libre_date_calcul').closest('.container').removeClass('hidden');
    else $('#id_tache_libre_date_calcul').closest('.container').addClass('hidden');
}

function save_tlb_actions(action)
{
    var periode = parseInt($('#id_tache_libre_periode').val()),
        demarage = $('#id_tache_libre_demarrage').val().trim(),
        a_partir_de = parseInt($('#id_tache_libre_a_partir_de').val()),
        date_calcul = $('#id_tache_libre_date_calcul').val().trim(),
        jour = parseInt($('#id_tache_libre_a_jour').val()),
        jour_semaine = parseInt($('#id_tache_libre_jour_semaine').val()),
        responsable = parseInt($('.cl_tache_libre_container').find('.btn-primary').attr('data-value')),
        div_container = $('#id_tache_libre_edited'),
        tache = parseInt(div_container.attr('data-id')),
        tache_entity = parseInt(div_container.attr('data-id_old')),
        client = parseInt(div_container.attr('data-client')),
        dossier = parseInt(div_container.attr('data-dossier')),
        heriter = ($('#id_heriter_client').length > 0) ? ($('#id_heriter_client').is(':checked') ? 1 : 0) : 0;

    if (action !== 2)
    {
        if (heriter === 0)
        {
            if (periode === -1)
            {
                show_info('Notice','Choisir la période','error');
                $('#id_tache_libre_periode').closest('.form-group').addClass('has-error');
                return;
            }
            $('#id_tache_libre_periode').closest('.form-group').removeClass('has-error');

            if (isNaN(responsable))
            {
                show_info('Notice','Choisir le responsable','error');
                return;
            }

            var error = false,
                periode_annuels = [1,2,3,4,6,12];
            if (periode === 0)
            {
                if (date_calcul === '')
                {
                    show_info('Notice','Choisir la date de la tache','error');
                    $('#id_tache_libre_date_calcul').closest('.form-group').addClass('has-error');
                    error = true;
                }
                else $('#id_tache_libre_date_calcul').closest('.form-group').removeClass('has-error');
                jour = 0;
            }
            else if (periode_annuels.in_array(periode))
            {
                if (a_partir_de === -1)
                {
                    show_info('Notice','Choisir la date de la tache','error');
                    $('#id_tache_libre_a_partir_de').closest('.form-group').addClass('has-error');
                    error = true;
                }
                else $('#id_tache_libre_a_partir_de').closest('.form-group').removeClass('has-error');

                if (a_partir_de === 3 && date_calcul === '')
                {
                    show_info('Notice','Choisir la date de calcul','error');
                    $('#id_tache_libre_date_calcul').closest('.form-group').addClass('has-error');
                    error = true;
                }
                else $('#id_tache_libre_date_calcul').closest('.form-group').removeClass('has-error');

                if (isNaN(jour) || jour < 1 || jour > 31)
                {
                    show_info('Notice','Verifier le JOUR de la tache','error');
                    $('#id_tache_libre_a_jour').closest('.form-group').addClass('has-error');
                    error = true;
                }
                else $('#id_tache_libre_a_jour').closest('.form-group').removeClass('has-error');
            }

            if (error)
            {
                show_info('Notice','Bien remplir les champs','warning');
                return;
            }
        }
    }

    $.ajax({
        data: {
            tache: tache,
            tache_entity: tache_entity,
            client: client,
            dossier: dossier,
            periode: periode,
            demarage: demarage,
            a_partir_de: a_partir_de,
            date_calcul: date_calcul,
            jour: jour,
            jour_semaine: jour_semaine,
            responsable: responsable,
            action: action,
            jalon: $('#id_tache_libre_jalon').is(':checked') ? 1 : 0,
            heriter: heriter
        },
        url: Routing.generate('tache_admin_save_tache_entity_lb'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            var result = parseInt(data);
            if (result < 0)
            {
                show_info('ERREUR FORMAT','Verifier le format des dates','error');
                return;
            }

            $('.'+class_cell_edit).find('input[type="checkbox"]').prop('checked', (result === 1));
            close_modal();
            show_info('SUCCES','Modifications bien enregistrées avec succès');
        }
    });
}
