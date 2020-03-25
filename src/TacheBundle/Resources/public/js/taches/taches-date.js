/**
 * Created by SITRAKA on 09/11/2018.
 */
var cl_t_dates_edited = 'taches_date_edited';

$(document).ready(function(){
    $(document).on('click','.cl_taches_date_edit',function(){
        var action = parseInt($(this).attr('data-action'));
        if (action !== 1) $('.'+cl_t_dates_edited).removeClass(cl_t_dates_edited);
        var taches_action = 0,
            taches_date = 0,
            dossier = 0;

        if (action === 0)
        {
            taches_action = parseInt($(this).hasClass('js_add') ? $(this).closest('tr').attr('data-id') : $(this).closest('tr.footable-row-detail').prev('.footable-detail-show').attr('data-id'));
            taches_date = $(this).hasClass('js_add') ? 0 : $(this).closest('td').attr('data-id');
            if ($(this).hasClass('js_add')) $(this).closest('tr').addClass(cl_t_dates_edited);
            else $(this).closest('tr.footable-row-detail').prev('tr.footable-detail-show').addClass(cl_t_dates_edited);
            dossier = parseInt($(this).closest('table.footable').attr('data-dossier'));
        }
        else if (action === 1)
        {
            var is_formule = $('#radio-formule').is(':checked'),
                formule = $('#id_formule').val().trim();
            taches_action = parseInt($(this).attr('data-taches_action'));
            taches_date = parseInt($(this).attr('data-taches_date'));
            dossier = parseInt($(this).attr('data-dossier'));

            if (is_formule && formule === '')
            {
                show_info('ERREUR','Formule vide','error');
                $('#id_formule').closest('.form-group').addClass('has-error');
                return;
            }
            else $('#id_formule').closest('.form-group').removeClass('has-error');
        }
        else if (action === 2)
        {
            taches_action = 0;
            taches_date = $(this).closest('td').attr('data-id');
            $(this).closest('tr.footable-row-detail').prev('tr.footable-detail-show').addClass(cl_t_dates_edited);
        }
        edit_taches_date(action, taches_action,taches_date,dossier);
    });

    $(document).on('click','.cl_cloture',function(){
        if ($(this).hasClass('active')) return;
        if ($(this).hasClass('btn-primary')) $(this).removeClass('btn-primary').addClass('btn-white');
        else $(this).addClass('btn-primary').removeClass('btn-white');
    });

    $(document).on('change','input[name="radio-type"]',function(){
        if (parseInt($(this).val()) === 0) $('#id_formule').removeClass('hidden');
        else $('#id_formule').addClass('hidden');
    });
});

function edit_taches_date(action, taches_action, taches_date, dossier)
{
    var clotures = [],
        is_formule = false,
        formule = '';

    if (action === 1)
    {
        is_formule = $('#radio-formule').is(':checked');
        formule = $('#id_formule').val().trim();

        $('#id_clotures').find('.btn-primary').each(function(){
            clotures.push(parseInt($(this).attr('data-value')));
        });

        if (clotures.length === 0)
        {
            show_info('Erreur','Choisir les clotures','error');
            return;
        }
    }

    taches_date = typeof taches_date !== 'undefined' ? taches_date : 0;
    dossier = typeof dossier !== 'undefined' ? dossier : 0;

    $.ajax({
        data: {
            action: action,
            taches_action: taches_action,
            taches_date: taches_date,
            clotures: JSON.stringify(clotures),
            formule: formule,
            is_infoperdos: is_formule ? 0 : 1,
            dossier: dossier
        },
        url: Routing.generate('taches_date_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 0)
            {
                show_modal(data,'Ajout Date');
                $('.cl_aide_date').qtip({
                    content: {
                        text: function (event, api) {
                            var modalbody = '<table class="table">';
                            modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">J</th><td class="col-sm-9">Jour</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">Jo</th><td class="col-sm-9">Jour&nbsp;ouvrable</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">M</th><td class="col-sm-9">Mois</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">De</th><td class="col-sm-9">D&eacute;but&nbsp;exercice</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">Cl</th><td class="col-sm-9">Cloture</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">Date</th><td class="col-sm-9">[15/03]:15&nbsp;mars</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;"></th><td class="col-sm-9"></td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;">Ex</th><td class="col-sm-9">[15/06]: 15 juin</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;"></th><td class="col-sm-9">[01/05] + 2Jo: 2ème jour ouvrable après le 1er Mai</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;"></th><td class="col-sm-9">Cl + 90J: 90ème jour après Cloture</td></tr>';
                            modalbody += '<tr><th style="text-align: left !important;"></th><td class="col-sm-9">{10Jo}: Tous les dixième jour ouvrable du mois</td></tr>';
                            modalbody += '</table>';

                            return modalbody;
                        }
                    },
                    position: {my: 'top center', at: 'bottom left'},
                    style: {
                        classes: 'qtip-dark qtip-shadow'
                    }
                });
            }
            else
            {
                charger_taches_dates();
                if (dossier !== 0)
                {
                    set_val_check(parseInt(data) === 1);
                }
            }
        }
    });
}

function charger_taches_dates()
{
    var el = $('.'+cl_t_dates_edited),
        taches_action = el.attr('data-id'),
        dossier = el.closest('.footable').attr('data-dossier');

    close_modal();
    $.ajax({
        data: {
            taches_action: taches_action,
            dossier: dossier
        },
        url: Routing.generate('taches_dates'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('.'+cl_t_dates_edited).next('tr.footable-row-detail').find('.footable-row-detail-value').html(data);
        }
    });
}
