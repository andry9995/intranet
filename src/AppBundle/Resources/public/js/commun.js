/********************************
 *          EVENEMENTS
* ******************************/
var client_required = false;
$(document).ready(function(){
    $(document).on('click','.js_dp_exercice',function(){
        dp_exercice_change($(this));
    });
    $(document).on('click','.js_dp_trimestre',function(){
        dp_trimestre_change_status($(this));
    });
    $(document).on('click','.js_dp_mois',function(){
        dp_mois_change_status($(this));
    });

    /*$(document).on('click', '.menu-left-minimize', function () {
        $(window).trigger('resize');
    });*/
});


/********************************
 *          FONCTIONS
 * ******************************/
//charger site
function charger_site()
{
    var client = $('#client').val();
    if (client.trim() === '') client = 0;

    var lien = Routing.generate('app_sites')+'/0/'+client+'/1';

    $.ajax({
        data: {},
        url: lien,
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('#js_conteneur_site').html(data).change();
            charger_dossier();
        }
    });
}
//charger dossier a partir du site
function charger_dossier(tous)
{
    $('#js_conteneur_dossier').empty();

    tous = typeof tous !== 'undefined' ? tous : 0;
    var site = parseInt($('#site').val()),
        client = parseInt($('#client').val());
    if (isNaN(client)) client = 0;
    var lien = Routing.generate('app_dossiers')+'/0/'+site+'/'+tous+'/'+client;

    if (client === 0 && client_required)
    {
        show_info('Notice','Choisir un CLIENT','error');
        return;
    }

    $.ajax({
        data: {},
        url: lien,
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('#js_conteneur_dossier').html(data);
            if(typeof after_charged_dossier === 'function') after_charged_dossier();
        }
    });
}

//charger date_picker
function charger_date_picker()
{
    var dossier = parseInt($('#dossier').val()),
        lien = Routing.generate('app_date_picker')+'/'+dossier;

    $.ajax({
        data: {},
        url: lien,
        async:false,
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            $('#js_conteneur_date_picker').html(data);
            $("[data-toggle=popover]").popover({html:true});
        }
    });
}

function activer_qTip()
{
    $(document).find('.js_tooltip').qtip({
        content: {
            text: function (event, api) {
                return $(this).attr('data-tooltip');
            }
        },
        title: $(this).find('div').text().trim(),
        position: {
            my: 'top right', // Position my top left...
            at: 'bottom right' // at the bottom right of...

        },
        style: {
            classes: 'qtip-youtube'
        }
    });
}
//datepicker
function dp_class_exercice(active)
{
    active = typeof active !== 'undefined' ? active : true;
    return (active) ? 'success' : '';
}
function dp_class_trimestre()
{
    return 'success';
}
function dp_class_mois()
{
    return 'warning';
}
//exercice date picker change
function dp_exercice_change(th)
{
    if(!th.hasClass(dp_class_exercice())) th.addClass(dp_class_exercice());
    else
    {
        var nbr = 0;
        $('#date_picker th.js_dp_exercice').each(function(){
            if($(this).hasClass(dp_class_exercice())) nbr++;
        });

        if(nbr > 1) th.removeClass(dp_class_exercice());
    }
}
//trimestre
function dp_trimestre_change_status(th)
{
    if(!th.hasClass(dp_class_trimestre()))
    {
        th.addClass(dp_class_trimestre());
        th.parent().find('.js_dp_mois').addClass(dp_class_mois());
    }
    else
    {
        th.removeClass(dp_class_trimestre());
        th.parent().find('.js_dp_mois').removeClass(dp_class_mois());
    }
}
//mois
function dp_mois_change_status(td)
{
    if(td.hasClass(dp_class_mois())) td.removeClass(dp_class_mois());
    else td.addClass(dp_class_mois());

    var nbr = 0;
    $('#date_picker tr[data-trimestre="'+td.parent().attr('data-trimestre')+'"] td.js_dp_mois').each(function(){
        if($(this).hasClass(dp_class_mois())) nbr++;
    });

    if(nbr === 3) td.parent().find('th.js_dp_trimestre').addClass(dp_class_trimestre());
    else td.parent().find('th.js_dp_trimestre').removeClass(dp_class_trimestre());
}

//fonction get cloture MOIS
function get_clotureDossier()
{
    var result = 0,
        dossier_id = parseInt($('#dossier').val()),
        lien = Routing.generate('app_cloture_dossier')+'/'+dossier_id;
    $.ajax({
        data: {},
        url: lien,
        async:false,
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR){
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            result = parseInt(data.trim());
        }
    });

    return result;
}

function get_fin_mois(mois_param,annee_param)
{
    var date_new = new Date(annee_param,mois_param,1);
    date_new.setDate(date_new.getDate() - 1);
    return date_new;
}

function getMenuIntranet()
{
    var lien = $('.get-menu-intranet').attr('data');
}

function loadInfoPerdos(container, btnInfoPerdos, dossierid, exercice){
    $.ajax({
        url: Routing.generate('app_infoperdos'),
        type: 'GET',
        data: {
            dossierid: dossierid,
            exercice: exercice
        },
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        success: function(data){
            container.empty();
            container.html(data);


            btnInfoPerdos.removeClass('blink');

            if (parseInt($('#with-instruction').val()) === 1) {
                btnInfoPerdos.addClass('blink');
            }
        }
    })
}