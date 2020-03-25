var mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];





$(function () {
    var timeout = 120000;
    var collab2Initialized = false;
    $('.loader').hide();




    /*Enregistrer les lots à télécharger*/
    $(document).on('click','#btn-save-download', function(){
        saveLotATelecharger(0);
        refreshTirage();
    });

    /*Enregistrer les lots EN COURS téléchargement*/
    $(document).on('click','#btn_save_tir_encours', function(){
        saveLotATelecharger(1);
        $('#btn_save_tir_encours').removeClass('btn-primary');
        $('#btn_save_tir_encours').addClass('btn-white');
    });

    $( ".resizable" ).resizable({
        handles:'s',
    });
    $('#contenu-lotaTelecharger').height(125);

    makeContextMenu('.lot-tirage .contenue-a-tirer .lot', 0);
    makeContextMenu('.lot-en-cours-telechargement .lot', 1);

    $('.lot-en-cours-telechargement').height($(window).height() - 455);
    $('.lot-fini-download').height($(window).height() - 500);
    $('.liste-client-gauche').height($(window).height() - 200);

    $(window).on('resize', function(){
        var win = $(this); //this = window
        $('.lot-en-cours-telechargement').height($(window).height() - 455);
        $('.lot-fini-download').height($(window).height() - 500);
        $('.liste-client-gauche').height($(window).height() - 200);
    });
    /*if ( $.fn.dataTable.isDataTable('#table-collab-niv1') ) {
        var _table = $('#table-collab-niv1').DataTable();
        _table.destroy();
    }*/
    /*$('#table-collab-niv1').DataTable({
        fixedHeader: false,
        scrollY: 400,
        paging: true,
        info: true,
        language: {
            search: "Chercher",
            zeroRecords: "Aucune donnée trouvée."
        },
        "columnDefs": [
            {
                'sortable': false,
                'targets': [0, 1, 2, 3, 4, 5, 6]
            },
            { "width": "90px", "targets": 1 },
            { "width": "60px", "targets": 3 },
            { "width": "70px", "targets": 6 },
        ]

    });*/

    $(document).on('click', '#btn-stop-download', function() {
        $.ajax({
            url: Routing.generate('reception_fermer_download'),
            type: 'GET',
            data: {},
            success: function (data) {
                show_info('Download', 'Arrêt tirage des images','success', 2000 );
            }
        });

    });




    $(document).on('click', '#btn-run-download', function() {
        $.ajax({
            url: Routing.generate('reception_lancer_download'),
            type: 'GET',
            data: {},
            success: function (data) {
                show_info('Download', 'Téléchargement est lancé','success', 2000 );
            }
        });
    });

    $(document).on('click', '#btn-mode-default', function(){
        $(this).addClass('active');
        $('#btn-mode-table').removeClass('active');
        $('#modeAffichageHidden').val(0);
        // var dateDown = document.getElementById('date-download').value;
        // getListeLotTirer($('#date-download').val(), 0);
        $('.tableau-lot-telecharger').addClass('hidden');
        $('.ligne-lot-telecharger').removeClass('hidden');

    });

    $(document).on('click', '#btn-mode-table', function(){
        $(this).addClass('active');
        $('#btn-mode-default').removeClass('active');
        $('#modeAffichageHidden').val(1);
        // var dateDown = document.getElementById('date-download').value;
        // getListeLotTirer($('#date-download').val(), 1);
        $('.tableau-lot-telecharger').removeClass('hidden');
        $('.ligne-lot-telecharger').addClass('hidden');

    });

    var updateOutput = function (e) {
        var list = e.length ? e : $(e.target),
            output = list.data('output');
        /*if (window.JSON) {
            output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
        } else {
            output.val('JSON browser support required for this demo.');
        }*/
    };

    $('#nestable').nestable({
        group: 1,
        maxDepth:1,
    }).on('change', updateOutput);


    refreshListeClient();
    //$('.lot-tirage').height($(window).height() - 250);
    //$('.lot-tirage').height($(window).height() - 250);
    $('[data-toggle="tooltip"]').tooltip({placement : 'left'});
    $('#btn_refresh_tir').on('click', function() {
        refreshTirage();
    });


    $('.navbar-minimalize').on('click', function() {
        setTimeout(function() {
            $('#table-collab-niv1').DataTable().draw();
            if (collab2Initialized) {
                $('#table-collab-niv2').DataTable().draw();
            }
        }, 500);
    });


    //makeLotDraggable();


    $('#btn_refresh_tir_encours').on('click', function(){
        refreshTirageEnCours();
    });



    $(document).on('click', '#findCabinetDossierEnCours', function(){

        var cab = $('#idClientFindEnCours :selected').text();
        var dos = $('#idDossierFindEnCours :selected').text();
        $('.lot-tirage-encours').each(function () {
            $(this).find('.infos-recherche-encours').addClass('hidden');
        });
        if (cab != '') {

            if (dos != '') {
                $('.lot-tirage-encours').find('.lot[data-client=' + cab + '][data-dossier=' + dos + ']').each(function () {
                    $(this).find('.infos-recherche-encours').removeClass('hidden');
                });
            }
            else
            {
                $('.lot-tirage-encours').find('.lot[data-client=' + cab + ']').each(function () {
                    $(this).find('.infos-recherche-encours').removeClass('hidden');
                });
            }
        }
    });

    //On change client
    $(document).on('change','#idClientFind', function(){
        var _client = $(this).val();

        var _dossier = $.parseJSON($('#idInputDossierHide').val());
        var html = "<option></option>";
        var i = 0;
        $.each(_dossier, function(index, value){
            if (index == _client) {
                for (i = 0; i < value.length; i++) {
                    html += "<option>";
                    html += value[i];
                    html += "</option>";
                }

            }
        });
        $('#idDossierFind').html(html);
    });


    //On change client afficher dossier
    $(document).on('change','#idClientFindEnCours', function(){
        var _client = $(this).val();
        var _dossier = $.parseJSON($('#idInputDossierEnCours').val());

        var i = 0;
        var html="<option></option>";
        $.each(_dossier, function(index, value){
            if (index == _client) {
                for (i = 0; i < value.length; i++) {
                    html += "<option>";
                    html += value[i];
                    html += "</option>";
                }
            }
        });
        $('#idDossierFindEnCours').html(html);
    });


    $(document).on('click','#findCabinetDossier', function(){
        var cab = $('#idClientFind :selected').text();
        var dos = $('#idDossierFind :selected').text();
        $('.lot-tirage').each(function () {
            $(this).find('.infos-recherche').addClass('hidden');
        });

        $('#lot-numeroter').find('option').each(function () {
            $(this).attr('data-class', 'hidden');
        });

        if (cab != '') {

            if (dos!='') {
                $('.lot-tirage').find('.lot[data-client=' + cab + '][data-dossier=' + dos + ']').each(function () {
                    $(this).find('.infos-recherche').removeClass('hidden');
                });

                $('#lot-numeroter').find('option[data-client=' + cab + '][data-dossier=' + dos + ']').each(function () {
                    $(this).attr('data-class', '')
                });
            }
            else
            {
                $('.lot-tirage').find('.lot[data-client=' + cab + ']').each(function () {
                    $(this).find('.infos-recherche').removeClass('hidden');
                });
                $('#lot-numeroter').find('option[data-client=' + cab + ']').each(function () {
                    $(this).attr('data-class', '')
                });
            }
        }
    });

    $(document).on('click', '#clearRecherche', function(){
        $('.lot-tirage').each(function(){
            $(this).find('.infos-recherche').addClass('hidden');
        });
        $('#idClientFind').val('');
        $('#idDossierFind').html('');
    });



});


function refreshTirage()
{
    var url = Routing.generate('reception_tirage', { json: 1 });
    $('#niv1-loader').show();

    $.ajax({
        url: url,
        type: 'GET',
        data: {},
        success: function (data) {
            $('.lot-tirage').html(data);
            $('#nb-lot-niv1').html($('#idNbLot').val());
            $('#nb-image-niv1').html($('#idNbImage').val());

            var _client = $.parseJSON($('#idInputClientHide').val());
            var html = '<option selected="selected"></option>';

            $.each(_client, function(index, value){
                html += '<option value="' + value + '">' + value + '</option>';
            });
            $('#idClientFind').html(html);
            $('#idDossierFind').html('<option></option>');
            /**/
            var _dossier = $.parseJSON($('#idListeDossierN1').val());
            //console.log(_dossier)
            $.ajax({
                url: Routing.generate('reception_tirage_listeClientDossier'),
                type: 'GET',
                data: { 'dossier-list': _dossier, },
                success:function (data1) {

                    $('#liste-client-N1').html(data1);
                    $('#niv1-loader').hide();
                    refreshListeClient();
                }
            });
        }
    });
}

function refreshTirageEnCours()
{
    $('#niv1-loader-enCours').removeClass('hidden');
    $.ajax({
        url: Routing.generate('reception_download_en_cours'),
        type: 'GET',
        data: {},
        success: function (data) {
            $('.lot-en-cours-telechargement').html(data);
            $('#nb-lot-encours').html($('#idNbLotEnCours').val())
            $('#nb-image-encours').html($('#idNbImageEnCours').val())
            $('[data-toggle="tooltip"]').tooltip({ placement : 'left', html:true});
            $('#niv1-loader-enCours').addClass('hidden');
        }
    });
}

$('#date-download').datepicker({
    todayBtn: "linked",
    keyboardNavigation: false,
    forceParse: false,
    calendarWeeks: true,
    autoclose: true,
    format:'dd/mm/yyyy',
    language: 'fr',
    startView: 1,
});
/* Afficher tous les lots Niv. 1*/
$(document).on('click', '#btn-select-all-N1', function () {
    selectLotAll(this, '1');
});

/* Afficher les lots téléchargés */
$(document).on('click', '#btn_refresh_tir_fini', function () {
    var dateDown = document.getElementById('date-download').value;
    var mode = $('#modeAffichageHidden').val();
    getListeLotTirer($('#date-download').val(), mode);
});

/* Afficher tous les lots Niv. 2*/
$(document).on('click', '#btn-select-all-N2', function () {
    selectLotAll(this, '2');
});

/* Selection client dans la liste Niv. 1*/
$(document).on('click', '.liste-client-item-N1', function () {
    selectLotClient(this, '1');
});

/* Selection client dans la liste Niv. 2*/
$(document).on('click', '.liste-client-item-N2', function () {
    selectLotClient(this, '2');
});

/* Selection dossier dans la liste Niv. 1*/
$(document).on('click', '.liste-dossier-item-N1', function () {
    selectLotDossier(this, '1');
});

/* Selection dossier dans la liste Niv. 1*/
$(document).on('click', '.liste-dossier-item-N2', function () {
    selectLotDossier(this, '2');
});




/*Enregistrer lots prêt à télécharger */
//iNiveau: 0 :  enregistrer les lots en attente de téléchargement
//iNiveau: 1 :  enregistrer les lots déjà en cours de téléchargement (dans le panier ou table: lot_a_telecharger)
function saveLotATelecharger(iNiveau)
{
    // $.ajax({url: Routing.generate('reception_download_en_cours_vider'),
    //     type: 'POST',
    //     success: function (data) {
    //
    //     }
    // });
    var variable = [];
    if (iNiveau == 0) {
        $('.contenue-a-tirer').find('.ligne-lot-a-telecharger').each(function () {
            variable.push({
                cabinet: $(this).attr('data-client'),
                dossier: $(this).attr('data-dossier'),
                exercice: $(this).attr('data-exercice'),
                dateScan: $(this).attr('data-datescan'),
                lotId: $(this).attr('data-lot'),
                lot: $(this).attr('data-lot2'),
                nbImage: $(this).attr('data-image'),
                status: 0
            });
        });
        if (variable.length > 0) {
            $.ajax({
                url: Routing.generate('reception_download_save'),
                type: 'POST',
                data: {
                    variable: JSON.stringify(variable),
                    niveau: iNiveau,
                },
                success: function (data) {
                    $.ajax({
                        url: Routing.generate('reception_download_en_cours'),
                        type: 'GET',
                        data: {},
                        success: function (data) {
                            $('.lot-en-cours-telechargement').html(data);
                            $('#nb-lot-encours').html($('#idNbLotEnCours').val());
                            $('#nb-image-encours').html($('#idNbImageEnCours').val());
                            $('.contenue-a-tirer').html('');
                            show_info('Enregistrement', 'Envoie des lots à télécharger avec succès', 'success', 2000);
                        }
                    });
                }
            });
        }
    }
    else
    { //Enregistrer les lots en cours de téléchargement
        $.ajax({
            url: Routing.generate('reception_fermer_download'),
            type: 'GET',
            data: {},
            success: function (data) {
                $('.lot-tirage-encours').find('.lot').each(function () {
                    variable.push({
                        cabinet: $(this).attr('data-client'),
                        dossier: $(this).attr('data-dossier'),
                        exercice: $(this).attr('data-exercice'),
                        dateScan: $(this).attr('data-datescan'),
                        lotId: $(this).attr('data-lot'),
                        lot: $(this).attr('data-lot2'),
                        nbImage: $(this).attr('data-image'),
                        status: 0
                    });
                });
                if (variable.length > 0) {
                    $.ajax({
                        url: Routing.generate('reception_download_save'),
                        type: 'POST',
                        data: {
                            variable: JSON.stringify(variable),
                            niveau: iNiveau,
                        },
                        success: function (data1) {
                            $.ajax({
                                url: Routing.generate('reception_lancer_download'),
                                type: 'GET',
                                data: {},
                                success: function (data2) {
                                    show_info('Enregistrement', 'Enregistrement priorité avec succès', 'success', 2000);
                                }
                            });

                            /*$.ajax({
                                url: Routing.generate('reception_download_en_cours'),
                                type: 'GET',
                                data: {},
                                success: function (data) {

                                    $('.lot-en-cours-telechargement').html(data);
                                    $('#nb-lot-encours').html($('#idNbLotEnCours').val())
                                    $('#nb-image-encours').html($('#idNbImageEnCours').val())
                                    show_info('Enregistrement', 'Envoye des lots à télécharger avec succès', 'success', 2000);
                                }
                            });*/
                        }
                    });
                }
            }
        });
    }

}

function getListeLotTirer(dateDown, modeAffichage)
{
    $.ajax({url: Routing.generate('reception_lot_telecharger'),
        type: 'POST',
        data: {
            dateDown: dateDown
        },
        success: function (data) {

        //data = $.parseJSON(data);
            if (data.erreur === false) {
                /*var lot_detail = panier.find('.lot-detail');
                ui.draggable.detach().addClass('dist')
                    .attr('data-panier', data.panier_id)
                    .appendTo(lot_detail);

                refreshPanier();*/
            }
            var nbLot_tir = 0;
            var nbImage_tir = 0;
            var html = '';

            $.each(data, function( index, value ) {
                nbLot_tir += 1;
                nbImage_tir += value.nbImage;

                html += "<div style='background-color:  #1AB394'";
                html += " data-download=" + value.download;
                html += " data-client=" + value.nomClient;

                html += " data-dossier=" + value.nomDossier;
                html += " data-datescan=" + value.date_scan;
                html += " data-nbimage=" + value.nbImage;
                html += " data-lot=" + value.lot;
                html += " data-lotid=" + value.lot_id;
                html += " data-exercice=" + value.exercice;
                html += ' title="' + value.nomClient + '/' + value.nomDossier + '/' + value.exercice + '/' + value.date_scan + '/' + value.lot + '"';
                if (modeAffichage == 0) {
                    html += " class='lot ligne-lot-telecharger'>" + value.nbImage;
                }
                else{
                    html += " class='lot ligne-lot-telecharger hidden'>" + value.nbImage;
                }
                html += "&nbsp;<i class='infos-recherche-fini-tirage hidden fa fa-star fa-spin' style='position:absolute;  top:0px;color:white '></i>";
                html += "</div>";

                /* Affichage en ligne */

                if (modeAffichage == 0) {
                    html += '<ol class="dd-list tableau-lot-telecharger hidden">';
                }
                else{
                    html += '<ol class="dd-list tableau-lot-telecharger">';
                }
                html += '<li class="dd-item">';
                html += '<div class="dd-handle" style="padding-left: 0px; padding-top:0px;padding-bottom: 0px;">';
                html += '<span class="label lot-a-retelecharger label-primary" title="A re-télécharger" data-lotid="' + value.lot_id + '" id="idARetelecharger"><i class="fa fa-download"></i></span>';
                html += '<span class="label label-default" title="Téléchargé le">' + value.download.substr(10, 9) + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Cabinet">' + value.nomClient + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Dossier">' + value.nomDossier + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Exercice">' + value.exercice + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Date de scan">' + value.date_scan + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Lot">' + value.lot + '</span>';
                html += '<span class="">/</span>';
                html += '<span class="label label-default" title="Nombre image">' + value.nbImage + '</span>';
                html += '</div></li>';


                html += '</ol>';

            });

            $('.lot-fini-download').html(html);
            $('#nb-lot-tir').html(nbLot_tir);
            $('#nb-image-tir').html(nbImage_tir);
            makeContextMenu_a_retelecharger('.lot-fini-download .ligne-lot-telecharger');
            makeContextMenu_a_retelecharger('.lot-fini-download .dd-item .lot-a-retelecharger');
        }

    });

}

function makeContextMenu_a_retelecharger(selector) {
    $.contextMenu({
        selector: selector,
        autoHide: true,
        items: {
            retour: {
                name: "Re-télécharger",
                callback: function (key, opt) {
                    var lot = opt.$trigger;
                    var lot_id = lot.attr('data-lotid');
                    retelechargerLot(lot_id);
                },
                icon: function (opt, $itemElement, itemKey, item) {
                    $itemElement.html('<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span> ' + item.name);
                    return 'context-menu-icon-updated';
                }
            }
        }
    });
}

function retelechargerLot(lot_id)
{
    $.ajax({
        url: Routing.generate('reception_a_retelecharger'),
        type: 'POST',
        data: {'lot_id': lot_id
        },
        success: function (data) {
            var mode = $('#modeAffichageHidden').val();
            refreshTirage();
            getListeLotTirer($('#date-download').val(), mode);
        }
    });
}

function dispatchPanierReception(panier, date_header, niveau) {
    $('#table-collab-niv' + niveau + ' .lot-detail').empty();

    $.each(panier, function(index, item) {
        var operateur = item.operateur_id,
            client = item.client,
            site = item.site,
            dossier = item.dossier,
            cloture = typeof mois[item.cloture] !== 'undefined' ? mois[item.cloture] : 'décembre',
            panier = item.id,
            date_panier = moment(item.date_panier.date);
        if (date_panier.isBefore(date_header[0])) {
            date_panier = date_header[0];
        }
        if (date_panier.isAfter(date_header[5])) {
            date_panier = date_header[5];
        }

        date_panier = date_panier.format('Y-MM-DD');

        var lot = item.lot_id,
            date_scan = moment(item.date_scan.date).format('Y-MM-DD'),
            priorite = item.priorite !== null ? moment(item.priorite.date).format('DD/MM/Y') : '',
            order = item.order,
            tache = item.tache,
            nb_image = item.nb_image,
            color= item.color;

        var row = $('#table-collab-niv' + niveau + ' tr[data-operateur="' + operateur + '"]');

        if (row.length > 0) {
            var cell = row.find('td[data-date-panier="' + date_panier + '"] .lot-detail');
            var the_lot = '<div class="lot dist" data-panier="' + panier + '"';
            the_lot += ' style="background-color:' + color + '"';
            the_lot += ' data-lot="' + lot +'"';
            the_lot += ' data-client="' + client +'"';
            the_lot += ' data-site="' + site +'"';
            the_lot += ' data-dossier="' + dossier +'"';
            the_lot += ' data-cloture="' + cloture +'"';
            the_lot += ' data-datescan="' + date_scan + '"';
            the_lot += ' data-priorite="' + priorite + '"';
            the_lot += ' data-order="' + order + '"';
            the_lot += ' data-tache="' + tache + '"';
            the_lot += ' data-image="' + nb_image +'">' + nb_image;
            the_lot += '</div>';
            cell.append(the_lot);
        }
    });
}
function selectLotAll(selector, niveau) {
    $(document).find('#liste-client-N' + niveau)
        .find('.liste-dossier-item-N' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-N' + niveau)
        .find('.liste-client-item-N' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-N' + niveau + ' .panel-collapse.collapse')
        .collapse('hide');
    $(selector).addClass('active');

    $(document).find('#tab-niv-' + niveau + ' .liste-lot .lot')
        .removeClass('hidden');
}

function selectLotClient(selector, niveau) {
    var client = $(selector).attr('data-client');

    $(document).find('#btn-select-all-N' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-N' + niveau)
        .find('.liste-dossier-item-N' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-N' + niveau)
        .find('.liste-client-item-N' + niveau)
        .removeClass('active');

    $(selector).addClass('active');

    var lot = $(document).find('.liste-lot-tirage .contenue-a-tirer .lot');
    lot.find('.infos-recherche').addClass("hidden");

    var findCli = $(document).find('.liste-lot-tirage .contenue-a-tirer .lot[data-client="' + client + '"]');
    findCli.find('.infos-recherche').removeClass('hidden');
    $('#lot-numeroter').find('option').attr('data-flag', '0');
    $('#lot-numeroter').find('option[data-client=' + client + ']').attr('data-flag', '1'); //Afficher flag

}

function selectLotDossier(selector, niveau) {
    var client = $(selector).attr('data-client');
    var dossier = $(selector).attr('data-dossier');

    $(document).find('#liste-client-N' + niveau)
        .find('.liste-dossier-item-N' + niveau)
        .removeClass('active');

    $(selector).addClass('active');


    var lot = $(document).find('.liste-lot-tirage .contenue-a-tirer .lot');
    lot.find('i').addClass("hidden");

    lot = $(document).find('.liste-lot-tirage .contenue-a-tirer .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]');
    lot.find('i').removeClass('hidden');

    $('#lot-numeroter').find('option').attr('data-flag', '0');
    $('#lot-numeroter').find('option[data-client=' + client + '][data-dossier="' + dossier + '"]').attr('data-flag', '1'); //Afficher flag
}

function updateTooltip() {
    makeTooltipLotReception('.liste-lot', false);
    makeTooltipLotReception('#table-collab-niv1', true);
    makeTooltipLotReception('#table-collab-niv2', true);
    $('*').qtip('hide');
}

function makeTooltipLotReception(container, isPanier) {
    var tooltip_parent = $(document);
    if (typeof container !== 'undefined') {
        tooltip_parent = $(document).find(container);
    }
    if (typeof isPanier === 'undefined') {
        isPanier = false;
    }
    var position = isPanier ? { my: 'bottom center', at: 'top center' } : { my: 'top center', at: 'bottom center' };

    tooltip_parent.find('.lot[data-image]').qtip({
        content: {
            text: function (event, api) {
                var client = $(this).attr('data-client'),
                    site = $(this).attr('data-site'),
                    dossier = $(this).attr('data-dossier'),
                    cloture = $(this).attr('data-cloture'),
                    datescan = moment($(this).attr('data-datescan')).format('DD/MM/Y'),
                    priorite = $(this).attr('data-priorite'),
                    tache = $(this).attr('data-tache');


                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Cloture</th><td class="col-sm-9">' + cloture + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Echéance</th><td class="col-sm-9" >' + priorite + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Tâche</th><td class="col-sm-9" >' + tache + '</td></tr>';
                modalbody += '</table>';

                return modalbody;
            }
        },
        position: position,
        show:'click',
        style: {
            classes: 'qtip-dark qtip-shadow'
        }
    });
}

function makeLotDraggable(container) {
    if (typeof container === 'undefined') {
        $(document).find('.lot').draggable({
            addClasses: false,
            containment: 'body',
            cursor: 'move',
            helper: 'clone',
            zIndex: 99999
        });
    } else {
        $('#' + container).find('.lot').draggable({
            addClasses: false,
            containment: 'body',
            cursor: 'move',
            helper: 'clone',
            zIndex: 99999
        });
    }

    makePanierDroppable();
}

/* Enregister lot partagé dans base */
function makePanierDroppable() {
    $(document).find('.lot-panier-container').droppable({
        accept: '.lot',
        addClasses: false,
        hoverClass: "lot-panier-container-hover",
        drop: function (event, ui) {
            //PARTAGE DEPUIS LISTE LOT
            if (!ui.draggable.hasClass('dist')) {
                panier = $(this);
                lot_id = ui.draggable.attr('data-lot');
                operateur_id = panier.closest('tr').attr('data-operateur');
                status = panier.closest('div.tab-pane').attr('id') === 'tab-niv-1' ? 1 : 3;
                date_panier = panier.closest('td').attr('data-date-panier');
                $.ajax({
                    url: Routing.generate('reception_add_to_panier', {
                        'operateur': operateur_id,
                        'lot': lot_id,
                        'status': status
                    }),
                    type: 'POST',
                    data: {
                        date_panier: date_panier
                    },
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (data.erreur === false) {
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach().addClass('dist')
                                .attr('data-panier', data.panier_id)
                                .appendTo(lot_detail);

                            refreshPanier();
                        }
                    }
                });
            } else {
                // DEPLACEMENT D'UN LOT D'UN PANIER VERS UN AUTRE PANIER
                var panier = $(this);
                var lot_id = ui.draggable.attr('data-lot');
                var panier_id = ui.draggable.attr('data-panier');
                var operateur_id = panier.closest('tr').attr('data-operateur');
                var old_operateur_id = ui.draggable.closest('tr').attr('data-operateur');
                var status = panier.closest('div.tab-pane').attr('id') === 'tab-niv-1' ? 1 : 3;
                var date_panier = panier.closest('td').attr('data-date-panier');
                var old_date_panier = ui.draggable.closest('td').attr('data-date-panier');

                if (operateur_id != old_operateur_id || date_panier != old_date_panier) {
                    $.ajax({
                        url: Routing.generate('reception_move_to_panier', {
                            'operateur': operateur_id,
                            'lot': lot_id,
                            'status': status,
                            'panier': panier_id
                        }),
                        type: 'POST',
                        data: {
                            date_panier: date_panier
                        },
                        success: function (data) {
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach().addClass('dist').appendTo(lot_detail);

                            refreshPanier();
                        }
                    });
                }
            }

        }
    });
}

function RetourLot(lot_id, panier_id, status, callback) {
    $.ajax({
        url: Routing.generate('reception_return_from_panier', {
            'panier': panier_id,
            'lot': lot_id,
            'status': status
        }),
        type: 'POST',
        data: {},
        success: function (data) {
            data = $.parseJSON(data);
            if (data.erreur === false) {
                callback();
            }

            refreshPanier();
        }
    });
}

function refreshPanier() {
    $(document).find('td[data-date-panier]').each(function () {
        var panier = $(this).find('.lot-panier-container');
        var lot_detail = panier.find('.lot-detail');

        var total_image = 0;
        lot_detail.find('.lot').each(function () {
            if (!isNaN(Number($(this).attr('data-image')))) {
                total_image += Number($(this).attr('data-image'));
            }
        });



        //Maka capacite par Ben
        var capa = 0, coef = 0, reelle = 0;

        $(this).closest('tr').find('.label-capacite').each(function(){
            var _type = parseInt($(this).attr('data-type'));
            if (_type===0)
                capa = parseFloat($(this).attr('data-value'));
            else if (_type===1)
                coef = parseFloat($(this).attr('data-value'));
            else if (_type===2)
                reelle = parseFloat($(this).attr('data-value'));
        });
        //alert(capa);
        var progress_class = 'progress-bar-danger';
        var pourc = ((total_image / capa) * 100).toFixed(2);
        if (pourc > 25 && pourc <= 50) {
            progress_class = 'progress-bar-warning';
        } else if (pourc > 50 && pourc <= 75) {
            progress_class = 'progress-bar-success';
        } else if (pourc > 75) {
            progress_class = '';
        }
        panier.find('.progress>div')
            .attr('style', 'width: ' + pourc + '%')
            .removeClass()
            .addClass('progress-bar ' + progress_class);
        panier.attr('data-image', total_image);

        panier.find('.panier-percentage .percentage').text(pourc + '%');
        panier.find('.panier-percentage .nb-image').text(total_image);
    });
    refreshListeClient();
    makeContextMenu('.lot.dist');
}

function Dossier(nom) {
    this.nom = nom;
    this.order = 9999;
    this.color = "#fff";
}

function Client(nom, dossier) {
    this.nom = nom;
    this.dossier = [];
    this.item = [];
    this.order = 9999;
    this.nb_image = 0;
    this.color = "#fff";
}

function refreshListeClient() {

    var liste_N1 = [];
    var liste_N2 = [];

    var liste_client_N1 = [];
    var liste_client_N2 = [];

    var clt;
    var ds;

    $(document).find('.liste-dossier-item-N1').each(function (index, item) {

        var client = $(item).attr('data-client');

        var dossier = $(item).attr('data-dossier');


        if (liste_client_N1.indexOf(client) < 0) {
            liste_client_N1.push(client);
            clt = new Client(client, []);
            liste_N1.push(clt);
        }

        if (clt.item.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0,
            order,
            color;
        $(document).find('.lot-tirage .contenue-a-tirer .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    clt.nb_image += Number($(element).attr('data-image'));

                    //order = Number($(element).attr('data-order'));
                    color = $(element).css('background-color');

                    clt.color = color;
                    ds.color = color;
                    /*if (order < clt.order) {
                        clt.order = order;
                        clt.color = color;
                    }*/

                    /*if (order < ds.order) {
                        ds.order = order;
                        ds.color = color;
                    }*/
                }
            });


        $(item).find('.liste-dossier-nb-image')
            .text(nbimage)
            .css('background-color', ds.color);


        var findClient = $(item).closest('.panel').find('.liste-client-item-N1');
        if (findClient.length > 0) {
            var findClient2 = findClient.find('.liste-dossier-nb-image');
            if (findClient2.length>0)
                findClient2.css('background-color', clt.color);
        }

    });

    /*$(document).find('.liste-dossier-item-N2').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_N2.indexOf(client) < 0) {
            liste_client_N2.push(client);
            clt = new Client(client, []);
            liste_N2.push(clt);
        }

        if (clt.dossier.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0,
            order,
            color;

        $(document).find('#tab-niv-2 .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    clt.nb_image += Number($(element).attr('data-image'));

                    order = Number($(element).attr('data-order'));
                    color = $(element).css('background-color');

                    if (order < clt.order) {
                        clt.order = order;
                        clt.color = color;
                    }

                    if (order < ds.order) {
                        ds.order = order;
                        ds.color = color;
                    }
                }
            });

        $(item).find('.liste-dossier-nb-image')
            .text(nbimage)
            .css("background-color", ds.color);
        $(item).closest('.panel')
            .find('.liste-client-item-N2 .liste-dossier-nb-image')
            .css("background-color", clt.color);
    });*/

    $.each(liste_N1, function(index, item) {
        $(document).find('.liste-client-item-N1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    // $.each(liste_N2, function(index, item) {
    //     $(document).find('.liste-client-item-N2[data-client="' + item.nom + '"')
    //         .find('.liste-dossier-nb-image')
    //         .text(item.nb_image);
    // });
}

function makeContextMenu(selector, iNiveau) {
    $.contextMenu({
        selector: selector,
        autoHide: true,
        items: {
            retour: {
                name: "Prioriser ce lot",
                callback: function (key, opt) {

                    var lot = opt.$trigger;


                    var lot_id = lot.attr('data-lot');

                    //
                    // RetourLot(lot_id, panier_id, status, function () {
                    //     lot.detach().removeClass('dist')
                    //         .removeAttr('data-panier')
                    //         .prependTo(lot_container);
                    // });

                    makeLotPriority(lot_id, iNiveau);
                },
                icon: function (opt, $itemElement, itemKey, item) {
                    $itemElement.html('<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span> ' + item.name);
                    return 'context-menu-icon-updated';
                }
            }
        }
    });
}

$(document).on('click', '#btn-update-extension', function() {

    $.ajax({
        url: Routing.generate('reception_update_file_extension'),
        type: 'GET',
        data: {},
        success: function (data) {
            show_modal(data, 'Modification extension fichier', 'rotateInDownRight', 'default');
        }
    });

});

$(document).on('click', '#save-extension', function() {
    if ($('#file-name').val() === undefined | $('#file-name').val() === '')
    {
        show_info('Modification extension', 'Veuillez saisir le nom du fichier!!!','error', 1000 );
        return;
    }
    if ($('#file-nb-page').val() === undefined | $('#file-nb-page').val()=== '') {
        show_info('Modification extension', 'Veuillez définir le nombre de page SVP!!!','error', 1000 );
        return;
    }
    if (parseInt($('#file-nb-page').val()) <= 0)
    {
        show_info('Modification extension', 'Nombre de page doit être positif!!!','error', 1000 );
        return;
    }
    $.ajax({
        url: Routing.generate('reception_save_file_extension'),
        type: 'GET',
        data: {
            'nomFichier': $('#file-name').val(),
            'nbpage': $('#file-nb-page').val(),
        },
        success: function (data) {
            if (data === 1)
            {
                $('#infos_modif_ext').removeClass('hidden');
                $('#image_modifiee').html($('#file-name').val() + ' avec nombre page ' + $('#file-nb-page').val());
                $('#file-name').val('');
                $('#file-nb-page').val('');
                show_info('Modification extension', 'Extension modifiée avec succès', 'success', 1000);
            }
            else
                show_info('Modification extension', 'Ce fichier n\'existe pas', 'error', 1000);
        }
    });
});


/*
<option data-index="{{ iCpt }}" style="background-color:{{ lot.color }} " data-lot="{{ lot.id }}"
                data-image="{{ lot.nbimage }}"
                data-client="{{ lot.client }}"
                data-site="{{ lot.site }}"
                data-dossier="{{ lot.dossier }}"
                data-datescan="{{ lot.date_scan }}"
                data-priorite="{{ lot.priorite }}"
                data-order="{{ lot.order }}"
                data-tache="{{ lot.tache }}"
                data-exercice="{{ lot.exercice }}"
                data-lot2="{{ lot.lot }}">{{ lot.nbimage }}&nbsp;
        </option>
 */
//iNiveau : 0 lot en attente download; 1: lot en cours de téléchargement
function makeLotPriority(lot_id, iNiveau) {
    if (iNiveau == 0) {
        var lotFind = $('#lot-numeroter').find('option[data-lot="' + lot_id + '"]');

        var html = '<option ';
        html += ' data-color="' + lotFind.attr('data-color') + '"';
        html += ' data-lot="' + lotFind.attr('data-lot') + '"';
        html += ' data-image="' + lotFind.attr('data-image') + '"';
        html += ' data-client="' + lotFind.attr('data-client') + '"';
        html += ' data-site="' + lotFind.attr('data-site') + '"';
        html += ' data-dossier="' + lotFind.attr('data-dossier') + '"';
        html += ' data-datescan="' + lotFind.attr('data-datescan') + '"';
        html += ' data-priorite="' + lotFind.attr('data-priorite') + '"';
        html += ' data-order="' + lotFind.attr('data-order') + '"';
        html += ' data-tache="' + lotFind.attr('data-tache') + '"';
        html += ' data-exercice="' + lotFind.attr('data-exercice') + '"';
        html += ' data-lot2="' + lotFind.attr('data-lot2') + '"';
        html += ' data-flag="' + lotFind.attr('data-flag') + '">';
        html += lotFind.attr('data-image');
        html += '</option>';

        lotFind.remove();
        $('#lot-numeroter').prepend(html);
        var htmlSelect = '<select id="lot-numeroter" class="hidden">' + $('#lot-numeroter').html() + '</select>';

        /*================== Recharger lot ==================================*/

        var htmldiv = '<div class="row contenue-a-tirer" style="padding-left: 10px">';
        $("#lot-numeroter option").each(function () {

            htmldiv += '<div class="lot ligne-lot-a-telecharger" ';
            htmldiv += 'style="background-color:' + $(this).attr('data-color') + '"';
            htmldiv += ' data-lot="' + $(this).attr('data-lot') + '"';
            htmldiv += ' data-image="' + $(this).attr('data-image') + '"';
            htmldiv += ' data-client="' + $(this).attr('data-client') + '"';
            htmldiv += ' data-site="' + $(this).attr('data-site') + '"';
            htmldiv += ' data-dossier="' + $(this).attr('data-dossier') + '"';
            htmldiv += ' data-datescan="' + $(this).attr('data-datescan') + '"';
            htmldiv += ' data-priorite="' + $(this).attr('data-priorite') + '"';
            htmldiv += ' data-order="' + $(this).attr('data-order') + '"';
            htmldiv += ' data-tache="' + $(this).attr('data-tache') + '"';
            htmldiv += ' data-exercice="' + $(this).attr('data-exercice') + '"';
            htmldiv += ' data-lot2="' + $(this).attr('data-lot2') + '">';
            htmldiv += $(this).attr('data-image');

            if ($(this).attr('data-flag').trim() == '0') {
                htmldiv += '&nbsp;<i class="infos-recherche hidden fa fa-star fa-spin" style="position:absolute;  top:0px;color: yellow "></i>';
            }
            else {
                htmldiv += '&nbsp;<i class="infos-recherche fa fa-star fa-spin" style="position:absolute;  top:0px;color: yellow "></i>';
            }
            htmldiv += '</div>';

        });
        htmldiv += htmlSelect;
        htmldiv += '</div>';
        $('.lot-tirage').html(htmldiv);
    }
    else
    {
        /*Priorité dans lot en cours de téléchargement */
        $('#btn_save_tir_encours').removeClass('btn-white');
        $('#btn_save_tir_encours').addClass('btn-primary');

        var lotFind = $('#lot-numeroter-enCours').find('option[data-lot="' + lot_id + '"]');

        var html = '<option ';

        html += ' data-lot="' + lotFind.attr('data-lot') + '"';
        html += ' data-image="' + lotFind.attr('data-image') + '"';
        html += ' data-client="' + lotFind.attr('data-client') + '"';
        html += ' data-dossier="' + lotFind.attr('data-dossier') + '"';
        html += ' data-datescan="' + lotFind.attr('data-datescan') + '"';
        html += ' data-exercice="' + lotFind.attr('data-exercice') + '"';
        html += ' data-lot2="' + lotFind.attr('data-lot2') + '"';
        html += ' data-flag="' + lotFind.attr('data-flag') + '"';
        html += ' title="' + lotFind.attr('title') + '">';
        html += lotFind.attr('data-image');
        html += '</option>';

        lotFind.remove();
        $('#lot-numeroter-enCours').prepend(html);
        var htmlSelect = '<select id="lot-numeroter-enCours" class="hidden">' + $('#lot-numeroter-enCours').html() + '</select>';

        /*================== Recharger lot ==================================*/

        var htmldiv = '<div class="lot-tirage-encours">';
        $('#lot-numeroter-enCours option').each(function () {
            htmldiv += '<div class="lot" ';
            htmldiv += 'style="background-color:#f7ac59"';
            htmldiv += ' data-lot="' + $(this).attr('data-lot') + '"';
            htmldiv += ' data-image="' + $(this).attr('data-image') + '"';
            htmldiv += ' data-client="' + $(this).attr('data-client') + '"';
            htmldiv += ' data-dossier="' + $(this).attr('data-dossier') + '"';
            htmldiv += ' data-datescan="' + $(this).attr('data-datescan') + '"';
            htmldiv += ' data-exercice="' + $(this).attr('data-exercice') + '"';

            htmldiv += ' data-lot2="' + $(this).attr('data-lot2') + '"';
            htmldiv += ' data-toggle="tooltip" data-flag="';
            htmldiv += ' title="' + $(this).attr('title') + '">';
            htmldiv += $(this).attr('data-image');

            if ($(this).attr('data-flag').trim() == '0') {
                htmldiv += '&nbsp;<i class="infos-recherche-encours hidden fa fa-star fa-spin" style="position:absolute;  top:0px;color: #0E0000 "></i>';
            }
            else {
                htmldiv += '&nbsp;<i class="infos-recherche-encours fa fa-star fa-spin" style="position:absolute;  top:0px;color: #0E0000 "></i>';
            }
            htmldiv += '</div>';
        });
        htmldiv += htmlSelect;
        htmldiv += '</div>';

        $('.lot-tirage-encours').html(htmldiv);
    }
}