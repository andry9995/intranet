/**
 * Created by SITRAKA on 04/01/2019.
 */
var cl_taches_libre_date_edited = 'taches_date_edited';

$(document).ready(function(){
    $(document).on('click','.cl_edit_tache_libre_date',function(){
        edit_taches_libre_date($(this));
    });

    $(document).on('change','#id_tache_libre_periode',function(){
        lb_change_periode();
    });

    $(document).on('change','#id_tache_libre_a_partir_de',function(){
        lb_change_a_partir_de();
    });
});

function edit_taches_libre_date(span)
{
    var action = parseInt(span.attr('data-action')),
        taches_libre_date = 0;

    /** save */
    var periode = 0,
        demarage = '',
        a_partir_de = 0,
        date_calcul = '',
        jour = 0,
        responsable = 0,
        jalon = 0,
        mois_additif = parseInt($('#id_tache_libre_mois_add').val());

    if (isNaN(mois_additif)) mois_additif = 0;

    if (action === 0 && !span.hasClass('js_add') || action === 2)
    {
        $('.'+cl_taches_libre_date_edited).removeClass(cl_taches_libre_date_edited);
        taches_libre_date = parseInt(span.closest('tr').addClass(cl_taches_libre_date_edited).attr('data-id'));
    }
    else if (action === 1)
    {
        periode = parseInt($('#id_tache_libre_periode').val());
        demarage = $('#id_tache_libre_demarrage').val().trim();
        a_partir_de = parseInt($('#id_tache_libre_a_partir_de').val());
        date_calcul = $('#id_tache_libre_date_calcul').val().trim();
        jour = parseInt($('#id_tache_libre_a_jour').val());
        responsable = parseInt($('input[name="radio-responsable"]:checked').val());
        taches_libre_date = parseInt(span.attr('data-id'));
        jalon = $('#id_tache_libre_jalon').is(':checked') ? 1 : 0;

        if (periode === -1)
        {
            show_info('Notice','Choisir la période','error');
            $('#id_tache_libre_periode').closest('.form-group').addClass('has-error');
            return;
        }
        $('#id_tache_libre_periode').closest('.form-group').removeClass('has-error');

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

    $.ajax({
        data: {
            action: action,
            tache: parseInt($('#id_tache').attr('data-id')),
            client: parseInt($('#id_tache').attr('data-client')),
            dossier: parseInt($('#id_tache').attr('data-dossier')),
            taches_libre_date: taches_libre_date,
            periode: periode,
            demarage: demarage,
            a_partir_de: a_partir_de,
            date_calcul: date_calcul,
            jour: jour,
            responsable: responsable,
            jalon: jalon,
            mois_additif: mois_additif
        },
        url: Routing.generate('taches_libre_date_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 2)
            {
                $('.'+cl_taches_libre_date_edited).remove();
                change_taches_libre();
                return;
            }
            else if (action === 1)
            {
                if (taches_libre_date === 0)
                {
                    $('#id_table_taches_date').find('tbody').append(data);
                }
                else
                {
                    $(data).insertAfter($('.'+cl_taches_libre_date_edited));
                    $('.'+cl_taches_libre_date_edited).remove();
                }

                change_taches_libre();
                close_modal();
                return;
            }

            show_modal(data,'Parametrage de la tache');
            set_datepicker($('#id_tache_libre_demarrage'));
            set_datepicker($('#id_tache_libre_date_calcul'));
            lb_change_periode();
            //$('#taches_test_id').html(data);
        }
    });
}

function lb_change_periode()
{
    var periode = parseInt($('#id_tache_libre_periode').val());

    $('.container').addClass('hidden');
    /*0=ponctuel,1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel,5=quotidien*/
    if (periode === 0)
    {
        $('#id_tache_libre_date_calcul').closest('.container').removeClass('hidden');
    }
    else if (periode === 5){
        $('#id_tache_libre_date_calcul').closest('.container').addClass('hidden');
        $('#id_tache_libre_periode').closest('.container').addClass('hidden');
        $('#id_tache_libre_demarrage').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_partir_de').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_jour').closest('.container').addClass('hidden');
        $('#id_tache_libre_jour_semaine').closest('.container').addClass('hidden');
        $('#id_tache_libre_mois_add').closest('.container').addClass('hidden');
    }
    else if (periode !== -1)
    {
        $('#id_tache_libre_periode').closest('.container').removeClass('hidden');
        $('#id_tache_libre_demarrage').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_partir_de').closest('.container').removeClass('hidden');
        $('#id_tache_libre_a_jour').closest('.container').removeClass('hidden');
        $('#id_tache_libre_jour_semaine').closest('.container').removeClass('hidden');
        $('#id_tache_libre_mois_add').closest('.container').removeClass('hidden');
        lb_change_a_partir_de();
    }
}

function lb_change_a_partir_de()
{
    var a_partir_de = parseInt($('#id_tache_libre_a_partir_de').val());
    if (a_partir_de === 3) $('#id_tache_libre_date_calcul').closest('.container').removeClass('hidden');
    else $('#id_tache_libre_date_calcul').closest('.container').addClass('hidden');
}