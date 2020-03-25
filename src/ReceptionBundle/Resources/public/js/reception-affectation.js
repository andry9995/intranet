var mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];





$(function () {
    var timeout = 120000;
    var collab2Initialized = false;
    $('.loader').hide();
    refreshReception();
    //Reload Page
    //    --- Timer ---
    // setInterval(function(){
    //     var url = Routing.generate('reception_affectation', { json: 1 });
    //     $('#niv1-loader').show();
    //     $('#niv2-loader').show();
    //     fetch(url, {
    //         credentials: 'include'
    //     }).then(function(response) {
    //         return response.json();
    //     }).then(function(data) {
    //         var erreur = data.erreur;
    //             if (erreur === false) {
    //                 var nb_lot_niv1 = data.nb_lot_niv1;
    //                 var nb_image_niv1 = data.nb_image_niv1;
    //                 var nb_lot_niv2 = data.nb_lot_niv2;
    //                 var nb_image_niv2 = data.nb_image_niv2;
    //                 var liste_jour = data.liste_jour;
    //                 var panier_niv1 = data.panier_niv1;
    //                 var panier_niv2 = data.panier_niv2;
    //                 var lot_N1 = data.lot_N1;
    //                 var lot_N2 = data.lot_N2;
    //                 var date_header = [];
    //
    //                 $.each(liste_jour, function(index, item) {
    //                     date_header.push(moment(item.date));
    //                 });
    //
    //                 $(document).find('#nb-lot-niv1')
    //                     .text(numeral(nb_lot_niv1).format('0,0'));
    //                 $(document).find('#nb-image-niv1')
    //                     .text(numeral(nb_image_niv1).format('0,0'));
    //                 $(document).find('#nb-lot-niv2')
    //                     .text(numeral(nb_lot_niv2).format('0,0'));
    //                 $(document).find('#nb-image-niv2')
    //                     .text(numeral(nb_image_niv2).format('0,0'));
    //                 refreshLotReception(lot_N1, 1);
    //                 dispatchPanierReception(panier_niv1, date_header, 1);
    //                 refreshLotReception(lot_N2, 2);
    //                 dispatchPanierReception(panier_niv2, date_header, 2);
    //                 updateTooltip();
    //                 makeLotDraggable();
    //                 refreshPanier();
    //                 $('#niv1-loader').hide();
    //                 $('#niv2-loader').hide();
    //             }
    //     });
    // }, timeout);

    $(document).on ('click', '.lot-filter', function() {

        var txt = $(this).find('a').text(),
            lis = $(this).parent().find('li'),
            libelle = $(this).closest('.input-group-btn').find('.dropdown-toggle'),
            dataType = $(this).attr('datatype'),
            encours = $('.encours'),
            attentes = $('.attente'),
            etape = 'N1',
            nbImageAttente = 0,
            nbLotAttente = 0,
            nbImageEncours = 0,
            nbLotEncours = 0
        ;

        if($(this).hasClass('N2')){
            encours = $('.encours-N2');
            attentes = $('.attente-N2');
            etape = 'N2';
        }


        lis.removeClass('active');
        $(this).addClass('active');

        libelle.text(txt);

        var nbAttente, nbEncours;

        switch (parseInt(dataType)){
            case 0:
                attentes.each(function(){
                    $(this).removeClass('hidden');
                });
                encours.each(function(){
                    if(!$(this).hasClass('hidden')){
                        $(this).addClass('hidden');
                    }
                });

                nbAttente = selectLotAll(this, etape, false);
                nbImageAttente = nbAttente['nbImage'];
                nbLotAttente = nbAttente['nbLot'];
                break;

            case 1:
                encours.each(function(){
                    $(this).removeClass('hidden');
                });
                attentes.each(function(){
                    if(!$(this).hasClass('hidden')){
                        $(this).addClass('hidden');
                    }
                });
                nbEncours = selectLotEncoursAll(this, etape);
                nbImageEncours = nbEncours['nbImage'];
                nbLotEncours = nbEncours['nbLot'];
                break;

            default:
                encours.each(function(){
                    $(this).removeClass('hidden');
                });
                attentes.each(function(){
                    $(this).removeClass('hidden');
                });

                nbAttente = selectLotAll(this, etape,false);
                nbImageAttente = nbAttente['nbImage'];
                nbLotAttente = nbAttente['nbLot'];

                nbEncours = selectLotEncoursAll(this, etape);
                nbImageEncours = nbEncours['nbImage'];
                nbLotEncours = nbEncours['nbLot'];


                break;
        }

        $('#nb-image-'+etape).html(numeral(nbImageEncours+nbImageAttente).format('0,0'));
        $('#nb-lot-'+etape).html(numeral(nbLotEncours+nbLotAttente).format('0,0'));
    });

    function refreshReception()
    {
        var url = Routing.generate('reception_affectation', { json: 1 });
        $('#niv1-loader').show();
        $('#niv2-loader').show();
        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var erreur = data.erreur;
            if (erreur === false) {
                // var nb_lot_niv1 = data.nb_lot_niv1;
                // var nb_image_niv1 = data.nb_image_niv1;
                // var nb_lot_niv2 = data.nb_lot_niv2;
                // var nb_image_niv2 = data.nb_image_niv2;

                var liste_jour = data.liste_jour;
                var panier_niv1 = data.panier_niv1;
                var panier_niv2 = data.panier_niv2;
                var lot_N1 = data.lot_N1;
                var lot_encours_N1 = data.lot_encours_N1;
                var lot_N2 = data.lot_N2;
                var lot_encours_N2 = data.lot_encours_N2;
                var date_header = [];

                $.each(liste_jour, function(index, item) {
                    date_header.push(moment(item.date));
                });

                // $(document).find('#nb-lot-N1')
                //     .text(numeral(nb_lot_niv1).format('0,0'));
                // $(document).find('#nb-image-N1')
                //     .text(numeral(nb_image_niv1).format('0,0'));
                // $(document).find('#nb-lot-N2')
                //     .text(numeral(nb_lot_niv2).format('0,0'));
                // $(document).find('#nb-image-N2')
                //     .text(numeral(nb_image_niv2).format('0,0'));

                refreshLotReception(lot_N1, lot_encours_N1, 'N1');
                dispatchPanierReception(panier_niv1, date_header, 1);
                refreshLotReception(lot_N2, lot_encours_N2, 'N2');
                dispatchPanierReception(panier_niv2, date_header, 2);
                updateTooltip();
                makeLotDraggable();
                refreshPanier();
                $('#niv1-loader').hide();
                $('#niv2-loader').hide();
            }
        });
    }

    $('#btn_refresh_dec').on('click', function() {
        $('#btn_refresh_dec').hide();
        refreshReception();
        $('#btn_refresh_dec').show();
    });

    $('#btn_refresh_sep').on('click', function() {
        $('#btn_refresh_sep').hide();
        refreshReception();
        $('#btn_refresh_sep').show();
    });

    $('.navbar-minimalize').on('click', function() {
        setTimeout(function() {
            $('#table-collab-niv1').DataTable().draw();
            if (collab2Initialized) {
                $('#table-collab-niv2').DataTable().draw();
            }
        }, 500);
    });

    $('#table-collab-niv1').DataTable({
        fixedHeader: true,
        scrollY: 400,
        paging: false,
        info: false,
        language: {
            search: "Chercher",
            zeroRecords: "Aucune donnée trouvée."
        },
        "columnDefs": [
            {
                'sortable': false,
                'targets': [1, 2, 3, 4, 5, 6, 7]
            },
            { "width": "90px", "targets": 1 }
        ]
    });

    $('[href="#tab-N1"]').on('click', function () {
        //setTimeout(function() {
            $('#table-collab-niv1').DataTable().draw();
        //}, 10);
    });

    $('[href="#tab-N2"]').on('click', function () {
        if (!collab2Initialized) {
            setTimeout(function () {
                $('#table-collab-niv2').DataTable({
                    fixedHeader: true,
                    scrollY: 400,
                    paging: false,
                    info: false,
                    language: {
                        search: "Chercher",
                        zeroRecords: "Aucune donnée trouvée."
                    },
                    "columnDefs": [
                        {
                            'sortable': false,
                            'targets': [1, 2, 3, 4, 5, 6, 7]
                        },
                        {"width": "90px", "targets": 1}
                    ]
                });
                collab2Initialized = true;
            }, 10);

        } else {
            setTimeout(function() {
                $('#table-collab-niv2').DataTable().draw();
            }, 10);
        }
    });

    updateTooltip();

    makeLotDraggable();
    refreshPanier();

    $(document).on('click', '.lot-panier-switch', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var container = $(this).closest('td').find('.lot-panier-container');
        container.find('.lot-info')
            .toggleClass('hidden');
        container.find('.lot-detail')
            .toggleClass('hidden');
    });

});

/* Afficher tous les lots Niv. 2*/
$(document).on('click', '#btn-select-all-N2', function () {
    selectLotAll(this, 'N2');
});

$(document).on('click', '#btn-select-all-encours-N2', function () {
    selectLotEncoursAll(this, 'N2');
});

/* Selection client dans la liste Niv. 1*/
$(document).on('click', '.liste-client-item-N1', function () {
    selectLotClient(this, 'N1');
});

$(document).on('click', '.liste-client-item-encours-N1', function () {
    selectLotEncoursClient(this, 'N1');
});

/* Selection client dans la liste Niv. 2*/
$(document).on('click', '.liste-client-item-N2', function () {
    selectLotClient(this, 'N2');
});

$(document).on('click', '.liste-client-item-encours-N2', function () {
    selectLotEncoursClient(this, 'N2');
});

/* Selection dossier dans la liste Niv. 1*/
$(document).on('click', '.liste-dossier-item-N1', function () {
    selectLotDossier(this, 'N1');
});

$(document).on('click', '.liste-dossier-item-encours-N1', function () {
    selectLotEncoursDossier(this, 'N1');
});

/* Selection dossier dans la liste Niv. 1*/
$(document).on('click', '.liste-dossier-item-N2', function () {
    selectLotDossier(this, 'N2');
});

$(document).on('click', '.liste-dossier-item-encours-N2', function () {
    selectLotEncoursDossier(this, 'N2');
});

function refreshLotReception(lots, lotencours, niveau) {
    var container = $('#tab-' + niveau + ' .liste-lot'),
        containerEncours = $('#tab-'+niveau+ '.liste-lot-encours');

    container.empty();
    containerEncours.empty();

    $.each(lots, function(index, item) {
       var client = item.client,
           site = item.site,
           dossier = item.dossier,
           cloture = typeof mois[item.cloture] !== 'undefined' ? mois[item.cloture] : 'décembre',
           date_scan = moment(item.date_scan).format('Y-MM-DD'),
           lot = item.id,
           priorite = item.priorite !== null ? moment(item.priorite).format('DD/MM/Y') : '',
           order = item.order,
           tache = item.tache,
           nb_image = item.nbimage,
           color= item.color;

        var the_lot = '<div class="lot"';
        the_lot += 'style="background-color:' + color + '"';
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

        container.append(the_lot);
    });


    $.each(lotencours, function(index, item) {
        var client = item.client,
            site = item.site,
            dossier = item.dossier,
            cloture = typeof mois[item.cloture] !== 'undefined' ? mois[item.cloture] : 'décembre',
            date_scan = moment(item.date_scan).format('Y-MM-DD'),
            lot = item.id,
            priorite = item.priorite !== null ? moment(item.priorite).format('DD/MM/Y') : '',
            order = item.order,
            tache = item.tache,
            nb_image = item.nbimage,
            operateur = item.operateur,
            operateur_id = item.operateur_id,
            color= item.color;

        var the_lot = '<div class="lot"';
        the_lot += 'style="background-color:' + color + '"';
        the_lot += ' data-lot="' + lot +'"';
        the_lot += ' data-client="' + client +'"';
        the_lot += ' data-site="' + site +'"';
        the_lot += ' data-dossier="' + dossier +'"';
        the_lot += ' data-cloture="' + cloture +'"';
        the_lot += ' data-datescan="' + date_scan + '"';
        the_lot += ' data-priorite="' + priorite + '"';
        the_lot += ' data-order="' + order + '"';
        the_lot += ' data-tache="' + tache + '"';
        the_lot += ' data-operateur="' + operateur + '"';
        the_lot += ' data-operateur-id="' + operateur_id + '"';
        the_lot += ' data-image="' + nb_image +'">' + nb_image;
        the_lot += '</div>';

        containerEncours.append(the_lot);
    });
}
function dispatchPanierReception(panier, date_header, niveau) {
    $('#table-collab-niv' + niveau + ' .lot-detail').empty();
    console.log(panier);
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

function setNbImageLot(etape){
    var lots = $('#tab-'+etape).find('.liste-lot.noselect .lot'),
        nblot = lots.size(),
        nbImage = 0
    ;

    lots.each(function(){
       nbImage += parseInt($(this).attr('data-image'));
    });

    $('#nb-image-'+etape).html(numeral(nbImage).format('0,0'));
    $('#nb-lot-'+etape).html(numeral(nblot).format('0,0'));

    return {'nbImage': nbImage, 'nbLot': nblot};

}

function selectLotAll(selector, niveau, first) {
    $(document).find('#liste-client-' + niveau)
        .find('.liste-dossier-item-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-' + niveau)
        .find('.liste-client-item-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-' + niveau + ' .panel-collapse.collapse')
        .collapse('hide');

    if(!first)
        $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot .lot')
        .removeClass('hidden');

    var nbImage = 0;

    $('.liste-client-item-'+niveau).each(function(){
        nbImage += parseInt($(this).find('.liste-dossier-nb-image').html());

    });

    var nbLot = $('.liste-lot.attente').find('.lot').size();

    return {nbImage: nbImage, nbLot: nbLot};
}

function selectLotEncoursAll(selector, niveau) {
    $(document).find('#liste-client-encours-' + niveau)
        .find('.liste-dossier-item-encours-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + niveau)
        .find('.liste-client-item-encours-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + niveau + ' .panel-collapse.collapse')
        .collapse('hide');
    $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot-encours .lot')
        .removeClass('hidden');

    var nbImage = 0;

    $('.liste-client-item-encours-'+niveau).each(function(){
        nbImage += parseInt($(this).find('.liste-dossier-nb-image').html());

    });

    var nbLot = $('.liste-lot-encours').find('.lot').size();

    return {nbImage: nbImage, nbLot: nbLot};
}

function selectLotClient(selector, niveau) {
    var client = $(selector).attr('data-client');

    $(document).find('#btn-select-all-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-' + niveau)
        .find('.liste-dossier-item-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-' + niveau)
        .find('.liste-client-item-' + niveau)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot .lot')
        .addClass('hidden');
    $(document).find('#tab-' + niveau + ' .liste-lot .lot[data-client="' + client + '"]')
        .removeClass('hidden');
}

function selectLotEncoursClient(selector, niveau) {
    var client = $(selector).attr('data-client');

    $(document).find('#btn-select-all-encours-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + niveau)
        .find('.liste-dossier-item-encours-' + niveau)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + niveau)
        .find('.liste-client-item-encours-' + niveau)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot-encours .lot')
        .addClass('hidden');
    $(document).find('#tab-' + niveau + ' .liste-lot-encours .lot[data-client="' + client + '"]')
        .removeClass('hidden');
}

function selectLotDossier(selector, niveau) {
    var client = $(selector).attr('data-client');
    var dossier = $(selector).attr('data-dossier');

    $(document).find('#liste-client-' + niveau)
        .find('.liste-dossier-item-' + niveau)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot .lot')
        .addClass('hidden');
    $(document).find('#tab-' + niveau + ' .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
        .removeClass('hidden');
}


function selectLotEncoursDossier(selector, niveau) {
    var client = $(selector).attr('data-client');
    var dossier = $(selector).attr('data-dossier');

    $(document).find('#liste-client-encours-' + niveau)
        .find('.liste-dossier-item-encours-' + niveau)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + niveau + ' .liste-lot-encours .lot')
        .addClass('hidden');
    $(document).find('#tab-' + niveau + ' .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
        .removeClass('hidden');
}


function updateTooltip() {
    makeTooltipLotReception('.liste-lot', false);
    makeTooltipLotReception('.liste-lot-encours', false);
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
                    tache = $(this).attr('data-tache'),
                    operateur = $(this).attr('data-operateur')
                ;


                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Cloture</th><td class="col-sm-9">' + cloture + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Echéance</th><td class="col-sm-9" >' + priorite + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Tâche</th><td class="col-sm-9" >' + tache + '</td></tr>';
                if(operateur !== undefined){
                    modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Operateur</th><td class="col-sm-9" >' + operateur + '</td></tr>';
                }
                modalbody += '</table>';

                return modalbody;
            }
        },
        position: position,
        show:  'click',
        style: {
            classes: 'qtip-dark qtip-shadow'
        }
    });
}

function makeLotDraggable(container) {
    if (typeof container === 'undefined') {
        // $(document).find('.lot').draggable({
        //     addClasses: false,
        //     containment: 'body',
        //     cursor: 'move',
        //     helper: 'clone',
        //     zIndex: 99999
        // });
        $('.lot').each(function(){
            var candDragg = true;

            if($(this).closest('.ibox-content').hasClass('liste-lot-encours')){
                candDragg = false;
            }

            if(candDragg){
                $(this).draggable({
                    addClasses: false,
                    containment: 'body',
                    cursor: 'move',
                    helper: 'clone',
                    zIndex: 99999
                });
            }
        })

    } else {
        // $('#' + container).find('.lot').draggable({
        //     addClasses: false,
        //     containment: 'body',
        //     cursor: 'move',
        //     helper: 'clone',
        //     zIndex: 99999
        // });

        container.find('.lot').each(function () {
            var candDragg = true;

            if($(this).closest('.ibox-content').hasClass('liste-lot-encours')){
                candDragg = false;
            }

            if(candDragg){
                $(this).draggable({
                    addClasses: false,
                    containment: 'body',
                    cursor: 'move',
                    helper: 'clone',
                    zIndex: 99999
                });
            }
        })
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
                status = panier.closest('div.tab-pane').attr('id') === 'tab-N1' ? 1 : 3;
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
                var status = panier.closest('div.tab-pane').attr('id') === 'tab-N1' ? 1 : 3;
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
        if (pourc <= 70) {
            progress_style = 'background-color:#17a688';
        } else if (pourc > 70 && pourc <= 90) {
            //progress_class = 'progress-bar-success';
            progress_style = 'background-color:#e6d454';
        } else{
            progress_style = 'background-color:red';
        }
        /*panier.find('.progress>div')
            .attr('style', 'width: ' + pourc + '%'; progress_style)
            .removeClass()
            .addClass('progress-bar ' + progress_class);*/
        panier.find('.progress>div')
            .attr('style', 'width: ' + pourc + '%;' + progress_style);
        panier.attr('data-image', total_image);

        panier.find('.panier-percentage .percentage').text(pourc + '%');
        panier.find('.panier-percentage .nb-image').text(total_image);
    });
    refreshListeClient();
    makeContextMenu('.lot.dist');


    setNbImageLot('N1');
    setNbImageLot('N2');
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

    var liste_N1 = [],
        liste_encours_N1 = [],
        liste_N2 = [],
        liste_encours_N2 = [];



    var liste_client_N1 = [],
        liste_client_encours_N1 = [],
        liste_client_encours_N2 = [],
        liste_client_N2 = [];

    var clt,
        cltencours,
        ds,
        dsencours
    ;

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
        $(document).find('#tab-N1 .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
            .css('background-color', ds.color);
        $(item).closest('.panel')
            .find('.liste-client-item-N1 .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });

    $(document).find('.liste-dossier-item-encours-N1').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');


        if (liste_client_encours_N1.indexOf(client) < 0) {
            liste_client_encours_N1.push(client);
            cltencours = new Client(client, []);
            liste_encours_N1.push(cltencours);
        }

        if (cltencours.item.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0,
            order,
            color;
        $(document).find('#tab-N1 .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    cltencours.nb_image += Number($(element).attr('data-image'));

                    order = Number($(element).attr('data-order'));
                    color = $(element).css('background-color');

                    if (order < cltencours.order) {
                        cltencours.order = order;
                        cltencours.color = color;
                    }

                    if (order < dsencours.order) {
                        dsencours.order = order;
                        dsencours.color = color;
                    }
                }
            });

        $(item).find('.liste-dossier-nb-image')
            .text(nbimage)
            .css('background-color', dsencours.color);
        $(item).closest('.panel')
            .find('.liste-client-item-encours-N1 .liste-dossier-nb-image')
            .css('background-color', cltencours.color);
    });


    $(document).find('.liste-dossier-item-N2').each(function (index, item) {
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

        $(document).find('#tab-N2 .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
    });

    $(document).find('.liste-dossier-item-encours-N2').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_encours_N2.indexOf(client) < 0) {
            liste_client_encours_N2.push(client);
            cltencours = new Client(client, []);
            liste_encours_N2.push(cltencours);
        }

        if (cltencours.dossier.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0,
            order,
            color;

        $(document).find('#tab-N2 .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    cltencours.nb_image += Number($(element).attr('data-image'));

                    order = Number($(element).attr('data-order'));
                    color = $(element).css('background-color');

                    if (order < cltencours.order) {
                        cltencours.order = order;
                        cltencours.color = color;
                    }

                    if (order < dsencours.order) {
                        dsencours.order = order;
                        dsencours.color = color;
                    }
                }
            });

        $(item).find('.liste-dossier-nb-image')
            .text(nbimage)
            .css("background-color", dsencours.color);
        $(item).closest('.panel')
            .find('.liste-client-item-encours-N2 .liste-dossier-nb-image')
            .css("background-color", cltencours.color);
    });

    $.each(liste_N1, function(index, item) {
        $(document).find('.liste-client-item-N1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_encours_N1, function(index, item) {
        $(document).find('.liste-client-item-encours-N1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_N2, function(index, item) {
        $(document).find('.liste-client-item-N2[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_encours_N2, function(index, item) {
        $(document).find('.liste-client-item-encours-N2[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });
}

function makeContextMenu(selector) {
    $.contextMenu({
        selector: selector,
        autoHide: true,
        items: {
            retour: {
                name: "Retourner le lot",
                callback: function (key, opt) {
                    var lot = opt.$trigger;
                    var lot_container = lot.closest('.tab-pane').find('.liste-lot');
                    var status = lot_container.attr('id') === 'tab-N1' ? 0 : 2;
                    var lot_id = lot.attr('data-lot');
                    var panier_id = lot.attr('data-panier');

                    RetourLot(lot_id, panier_id, status, function () {
                        lot.detach().removeClass('dist')
                            .removeAttr('data-panier')
                            .prependTo(lot_container);
                    });
                },
                icon: function (opt, $itemElement, itemKey, item) {
                    $itemElement.html('<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span> ' + item.name);
                    return 'context-menu-icon-updated';
                }
            }
        }
    });
}