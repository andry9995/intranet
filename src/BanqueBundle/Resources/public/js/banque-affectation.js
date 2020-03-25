var mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

$('.loader').hide();

$(function () {
    // var timeout = 120000;
    var timeout = 1200000000;

    //Reload Page
    //    --- Timer ---
    setInterval(function(){
        var url = Routing.generate('banque_affectation', { json: 1 });
        $('.loader').show();

        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var erreur = data.erreur;
            if (erreur === false) {
                var nb_lot_S1 = data.nb_lot_S1,
                    nb_image_S1 = data.nb_image_S1,
                    nb_lot_S2 = data.nb_lot_S2,
                    nb_image_S2 = data.nb_image_S2,
                    liste_jour = data.liste_jour,
                    panier_S1 = data.panier_S1,
                    panier_S2 = data.panier_S2,
                    lot_S1 = data.lot_S1,
                    lot_S2 = data.lot_S2,
                    date_header = [];
                $.each(liste_jour, function(index, item) {
                    date_header.push(moment(item.date));
                });
                $(document).find('#nb-lot-S1')
                    .text(numeral(nb_lot_S1).format('0,0'));
                $(document).find('#nb-image-S1')
                    .text(numeral(nb_image_S1).format('0,0'));
                $(document).find('#nb-lot-S2')
                    .text(numeral(nb_lot_S2).format('0,0'));
                $(document).find('#nb-image-S2')
                    .text(numeral(nb_image_S2).format('0,0'));


                refreshLotSaisie(lot_S1, 'S1');
                refreshLotSaisie(lot_S2, 'S2');


                dispatchPanierSaisie(panier_S1, date_header, 'S1');
                dispatchPanierSaisie(panier_S2, date_header, 'S2');

                updateTooltip();
                makeLotDraggable();
                refreshPanier();
                $('.loader').hide();
            }
        }).then(function(err) {
            console.log(err);
            $('.loader').hide();
        });
    }, timeout);

    $('#table-collab-S1').DataTable({
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

    $('[href="#tab-S2"]').one('click', function () {
        setTimeout(function () {
            $('#table-collab-S2').DataTable({
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
            })
        }, 0);
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



    /* Selection client dans la liste Saisie 1*/
    $(document).on('click', '.liste-client-item-S1', function () {
        selectLotClient(this, 'S1');
    });

    $(document).on('click', '.liste-client-item-encours-S1', function(){
        selectLotEncoursClient(this, 'S1');
    });

    /* Selection client dans la liste Saisie 2*/
    $(document).on('click', '.liste-client-item-S2', function () {
        selectLotClient(this, 'S2');
    });

    $(document).on('click', '.liste-client-item-encours-S2', function () {
        selectLotEncoursClient(this, 'S2');
    });

    /* Selection dossier dans la liste Saisie 1*/
    $(document).on('click', '.liste-dossier-item-S1', function () {
        selectLotDossier(this, 'S1');
    });

    /* Selection dossier dans la liste Saisie 1*/
    $(document).on('click', '.liste-dossier-item-encours-S1', function () {
        selectLotEncoursDossier(this, 'S1');
    });

    /* Selection dossier dans la liste Saisie 2*/
    $(document).on('click', '.liste-dossier-item-S2', function () {
        selectLotDossier(this, 'S2');
    });

    $(document).on('click', '.liste-dossier-item-encours-S2', function () {
        selectLotEncoursDossier(this, 'S2');
    });

    $(document).on('ifChanged', '#chk-lot-rb', function(event){
        event.stopPropagation();
        event.preventDefault();

        var noSelects = $('.liste-lot.noselect').find('.lot[data-souscategorie-id=10]'),
            encoursNoSelects = $('.liste-lot-encours.noselect').find('.lot[data-souscategorie-id=10]'),
            dists = $('.lot-panier-container .lot-detail').find('.lot[data-souscategorie-id=10]');
        if(!$(this).is(':checked')){

            noSelects.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            });
            encoursNoSelects.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            });
            dists.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            })
        }
        else{
            noSelects.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            });
            encoursNoSelects.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            });

            dists.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            });
        }

        updateNbImage($(this));
        refreshPanier();
    });


    $(document).on('ifChanged', '#chk-lot-ob', function(event){
        event.stopPropagation();
        event.preventDefault();

        var noSelects = $('.liste-lot.noselect').find('.lot[data-souscategorie-id!=10]'),
            encoursNoSelects = $('.liste-lot-encours.noselect').find('.lot[data-souscategorie-id!=10]'),
            dists = $('.lot-panier-container .lot-detail').find('.lot[data-souscategorie-id!=10]');
        if(!$(this).is(':checked')){

            noSelects.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            });
            encoursNoSelects.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            });

            dists.each(function () {
                if(!$(this).hasClass('hidden')){
                    $(this).addClass('hidden');
                }
            });
        }
        else{
            noSelects.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            });

            encoursNoSelects.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            });

            dists.each(function () {
                if($(this).hasClass('hidden')){
                    $(this).removeClass('hidden');
                }
            })
        }

        updateNbImage($(this));
        refreshPanier();
    });

    $(document).on ('click', '.lot-filter', function() {

        var txt = $(this).find('a').text(),
            lis = $(this).parent().find('li'),
            libelle = $(this).closest('.input-group-btn').find('.dropdown-toggle'),
            dataType = $(this).attr('datatype'),
            encours = $('.encours'),
            attentes = $('.attente'),
            etape = 'S1',
            nbImageAttente = 0,
            nbImageEncours = 0
            ;

        if($(this).hasClass('S2')){
            encours = $('.encours-S2');
            attentes = $('.attente-S2');
            etape = 'S2';
        }


        lis.removeClass('active');
        $(this).addClass('active');

        libelle.text(txt);

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

                nbImageAttente = selectLotAll(this, etape);
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
                nbImageEncours = selectLotEncoursAll(this, etape);
                break;

            default:
                encours.each(function(){
                    $(this).removeClass('hidden');
                });
                attentes.each(function(){
                    $(this).removeClass('hidden');
                });

                nbImageEncours = selectLotAll(this, etape);
                nbImageAttente = selectLotEncoursAll(this, etape);
                break;
        }

        $('#nb-image-'+etape).html(nbImageEncours+nbImageAttente);
    });

});


function updateNbImage(selector){
    var nbLot = 0, nbImage = 0;
    selector.closest('.ibox').find('.liste-lot.noselect').find('.lot').each(function(){
        if(!$(this).hasClass('hidden')){
            nbLot++;
            nbImage += parseInt($(this).html());
        }
    });
    $('#nb-lot-S1').html(nbLot);
    $('#nb-image-S1').html(nbImage);
}

function refreshLotSaisie(lot, etape) {
    var container = $('#tab-' + etape + ' .liste-lot');
    container.empty();
    $.each(lot, function(index, item) {
        var client = item.client,
            site = item.site,
            dossier = item.dossier,
            cloture = typeof mois[item.cloture] !== 'undefined' ? mois[item.cloture] : 'décembre',
            date_scan = moment(item.date_scan.date).format('Y-MM-DD'),
            lot = item.lot_id,
            nb_image = item.nbimage,
            categorie = item.categorie,
            priorite = item.priorite != null ? moment(item.priorite.date).format('Y-MM-DD') : '',
            order = item.order,
            tache = item.tache,
            color = item.color;

        var the_lot = '<div class="lot"';
        the_lot += ' style="background-color:' + color + '"';
        the_lot += ' data-lot="' + lot +'"';
        the_lot += ' data-client="' + client +'"';
        the_lot += ' data-site="' + site +'"';
        the_lot += ' data-dossier="' + dossier +'"';
        the_lot += ' data-cloture="' + cloture +'"';
        // the_lot += ' data-datescan="' + date_scan + '"';
        the_lot += ' data-categorie="' + categorie + '"';
        the_lot += ' data-priorite="' + priorite + '"';
        the_lot += ' data-order="' + order + '"';
        the_lot += ' data-tache="' + tache + '"';
        the_lot += ' data-image="' + nb_image +'">' + nb_image;
        the_lot += '</div>';

        container.append(the_lot);
    });
}
function dispatchPanierSaisie(panier, date_header, etape) {
    $('#table-collab-' + etape + ' .lot-detail').empty();
    $.each(panier, function(index, item) {
        console.log(item.date_panier.date, moment(item.date_panier.date));

        var lot = item.lot_id,
            nb_image = item.nbimage,
            client = item.client,
            site = item.site,
            dossier = item.dossier,
            cloture = item.cloture,
            date_scan = item.date_scan,
            date_panier = moment(item.date_panier.date),
            categorie = item.categorie,
            operateur = item.operateur_id,
            priorite = moment(item.priorite.date).format('DD/MM/Y'),
            order = item.order,
            tache = item.tache;

        if (date_panier.isBefore(date_header[0])) {
            date_panier = date_header[0];
        }
        if (date_panier.isAfter(date_header[5])) {
            date_panier = date_header[5];
        }
        date_panier = date_panier.format('Y-MM-DD');

        var row = $('#table-collab-' + etape + ' tr[data-operateur="' + operateur + '"]');

        if (row.length > 0) {
            var cell = row.find('td[data-date-panier="' + date_panier + '"] .lot-detail');
            var the_lot = '<div class="lot dist"';
            the_lot += ' style="background-color:' + color + '"';
            the_lot += ' data-lot="' + lot +'"';
            the_lot += ' data-client="' + client +'"';
            the_lot += ' data-site="' + site +'"';
            the_lot += ' data-dossier="' + dossier +'"';
            the_lot += ' data-cloture="' + cloture +'"';
            // the_lot += ' data-datescan="' + date_scan + '"';
            the_lot += ' data-categorie="' + categorie + '"';
            the_lot += ' data-priorite="' + priorite + '"';
            the_lot += ' data-order="' + order + '"';
            the_lot += ' data-tache="' + tache + '"';
            the_lot += ' data-image="' + nb_image +'">' + nb_image;
            the_lot += '</div>';
            cell.append(the_lot);
        }
    });
}
function selectLotAll(selector, etape) {
    $(document).find('#liste-client-' + etape)
        .find('.liste-dossier-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape)
        .find('.liste-client-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape + ' .panel-collapse.collapse')
        .collapse('hide');
    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot .lot')
        .removeClass('hidden');

    var nbImage = 0;
    $('.liste-client-item-'+etape).each(function(){
        var tmp = parseInt($(this).find('.liste-dossier-nb-image').html());
        nbImage += tmp;
    });

    return nbImage;
}

function selectLotEncoursAll(selector, etape) {
    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-dossier-encours-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-client-encours-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape + ' .panel-collapse.collapse')
        .collapse('hide');
    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot')
        .removeClass('hidden');

    var nbImage = 0;
    $('.liste-client-item-encours-'+etape).each(function(){
        var tmp = parseInt($(this).find('.liste-dossier-nb-image').html());
        nbImage += tmp;
    });

    return nbImage;
}

function selectLotClient(selector, etape) {
    var client = $(selector).attr('data-client');

    $(document).find('#btn-select-all-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape)
        .find('.liste-dossier-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape)
        .find('.liste-client-item-' + etape)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot .lot')
        .addClass('hidden');
    $(document).find('#tab-' + etape + ' .liste-lot .lot[data-client="' + client + '"]')
        .removeClass('hidden');
}

function selectLotEncoursClient(selector, etape) {
    var client = $(selector).attr('data-client');

    $(document).find('#btn-select-all-encours-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-dossier-item-encours-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-client-item-encours-' + etape)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot')
        .addClass('hidden');
    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot[data-client="' + client + '"]')
        .removeClass('hidden');
}

function selectLotDossier(selector, etape) {
    var client = $(selector).attr('data-client');
    var dossier = $(selector).attr('data-dossier');

    $(document).find('#liste-client-' + etape)
        .find('.liste-dossier-item-' + etape)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot .lot')
        .addClass('hidden');
    $(document).find('#tab-' + etape + ' .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
        .removeClass('hidden');
}


function selectLotEncoursDossier(selector, etape) {
    var client = $(selector).attr('data-client');
    var dossier = $(selector).attr('data-dossier');

    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-dossier-item-encours-' + etape)
        .removeClass('active');

    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot')
        .addClass('hidden');
    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
        .removeClass('hidden');
}

function updateTooltip() {
    makeTooltipLotSaisie('.liste-lot', false);
    makeTooltipLotSaisie('.liste-lot-encours', false);
    makeTooltipLotSaisie('#table-collab-S1', true);
    makeTooltipLotSaisie('#table-collab-S2', true);
}

function makeTooltipLotSaisie(container, isPanier) {
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
                    categorie = $(this).attr('data-categorie'),
                    souscategorie = $(this).attr('data-souscategorie'),
                    priorite = $(this).attr('data-priorite'),
                    tache = $(this).attr('data-tache'),
                    banque = $(this).attr('data-banque'),
                    numcompte = $(this).attr('data-num-compte'),
                    exercice = $(this).attr('data-exercice'),
                    operateur = $(this).attr('data-operateur')
                ;

                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Exercice</th><td class="col-sm-9">' + exercice + '</td></tr>';

                if(banque !== undefined && numcompte !== undefined) {
                    modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Banque</th><td class="col-sm-9">' + banque + '</td></tr>';
                    modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Num Compte</th><td class="col-sm-9">' + numcompte + '</td></tr>';
                }

                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Clôture</th><td class="col-sm-9">' + cloture + '</td></tr>';
                // modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Catégorie</th><td class="col-sm-9" >' + categorie + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Sous Catégorie</th><td class="col-sm-9" >' + souscategorie + '</td></tr>';
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
        style: {
            classes: 'qtip-dark qtip-shadow'
        },
        show: 'click'
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
        // container.find('.lot').draggable({
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

function makePanierDroppable() {
    $(document).find('.lot-panier-container').droppable({
        accept: '.lot',
        addClasses: false,
        hoverClass: "lot-panier-container-hover",
        drop: function (event, ui) {

            var panier = $(this),
                lot_id = ui.draggable.attr('data-lot'),
                dossier_id = ui.draggable.attr('data-dossier-id'),
                banquecompte_id = ui.draggable.attr('data-banquecompte-id'),
                etape = $(this).closest('.tab-pane').attr('data-etape'),
                operateur_id = panier.closest('tr').attr('data-operateur'),
                date_panier = panier.closest('td').attr('data-date-panier'),
                releve = true;

            if(dossier_id === undefined){
                dossier_id = '';
            }

            if(banquecompte_id === undefined){
                banquecompte_id = '';
            }

            if(parseInt(ui.draggable.attr('data-souscategorie-id')) !== 10){
                releve = false;
            }

            //PARTAGE DEPUIS LISTE LOT
            if (!ui.draggable.hasClass('dist')) {

                $.ajax({
                    url: Routing.generate('banque_add_to_panier'),
                    type: 'POST',
                    data: {
                        date_panier: date_panier,
                        is_releve: releve,
                        operateur: operateur_id,
                        lot: lot_id,
                        etape: etape,
                        dossier: dossier_id,
                        banque_compte: banquecompte_id
                    },
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (data.erreur === false) {
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach().addClass('dist')
                                .appendTo(lot_detail);

                            refreshPanier();
                        }
                    }
                });
            } else {
                // DEPLACEMENT D'UN LOT D'UN PANIER VERS UN AUTRE PANIER
                var
                    old_operateur_id = ui.draggable.closest('tr').attr('data-operateur'),
                    date_panier_org = ui.draggable.attr('data-date-panier-org'),
                    old_date_panier = ui.draggable.closest('td').attr('data-date-panier');


                if (operateur_id != old_operateur_id || date_panier != old_date_panier) {
                    $.ajax({
                        url: Routing.generate('banque_move_to_panier'),
                        type: 'POST',
                        data: {
                            date_panier: date_panier,
                            date_panier_org: date_panier_org,
                            operateur: operateur_id,
                            oldoperateur: old_operateur_id,
                            dossier: dossier_id,
                            etape: etape
                        },
                        success: function (data) {
                            data = $.parseJSON(data);
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach()
                                .removeAttr('data-categorie-id')
                                .addClass('dist')
                                .appendTo(lot_detail)
                                .attr('data-date-panier-org', date_panier);

                            refreshPanier();
                        }
                    });
                }
            }

        }
    });
}

function RetourLot(operateur, dossier_id, banquecompte_id,date_panier, etape, callback) {
    $.ajax({
        url: Routing.generate('banque_return_from_panier'),
        data: {
            operateur: operateur,
            dossier: dossier_id,
            banquecompte: banquecompte_id,
            date_panier: date_panier,
            etape: etape
        },
        type: 'POST',
        success: function (data) {
            data = $.parseJSON(data);
            if (data.erreur === false) {
                callback();
            }

            refreshPanier();
        }
    });
}

function refreshPanierOld() {
    $(document).find('td[data-date-panier]').each(function () {
        var panier = $(this).find('.lot-panier-container');
        var lot_detail = panier.find('.lot-detail');

        var total_image = 0;
        lot_detail.find('.lot').each(function () {
            if (!isNaN(Number($(this).attr('data-image')))) {
                total_image += Number($(this).attr('data-image'));
            }
        });
        var progress_class = 'progress-bar-danger';
        if (total_image > 25 && total_image <= 50) {
            progress_class = 'progress-bar-warning';
        } else if (total_image > 50 && total_image <= 75) {
            progress_class = 'progress-bar-success';
        } else if (total_image > 75) {
            progress_class = '';
        }



        panier.find('.progress>div')
            .attr('style', 'width: ' + total_image + '%')
            .removeClass()
            .addClass('progress-bar ' + progress_class);
        panier.attr('data-image', total_image);
        panier.find('.panier-percentage .percentage').text(total_image + '%');
        panier.find('.panier-percentage .nb-image').text(total_image);
    });
    refreshListeClient();
    makeContextMenu('.lot.dist');
}


function refreshPanier() {
    $(document).find('td[data-date-panier]').each(function () {
        var panier = $(this).find('.lot-panier-container'),
            lot_detail = panier.find('.lot-detail'),
            total_image = 0;

        lot_detail.find('.lot').each(function () {
            if (!isNaN(Number($(this).attr('data-image')))) {
                if(!$(this).hasClass('hidden'))
                    total_image += Number($(this).attr('data-image'));
            }
        });

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
        var progress_class = 'progress-bar-danger',
            pourc = 0,
            progress_style = '';

        if(capa !== 0)
            pourc =((total_image / capa) * 100).toFixed(2);

        if (pourc <= 70) {
            //progress_class = 'progress-bar-warning';
            progress_style = 'background-color:#17a688';
        } else if (pourc > 70 && pourc <= 90) {
            //progress_class = 'progress-bar-success';
            progress_style = 'background-color:#e6d454';
        } else {
            progress_style = 'background-color:red';
        }

        panier.find('.progress>div')
            .attr('style', 'width: ' + pourc + '%;' + progress_style);
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

function Client(nom) {
    this.nom = nom;
    this.dossier = [];
    this.item = [];
    this.order = 9999;
    this.nb_image = 0;
    this.color = "#fff";
}

function refreshListeClient() {

    var liste_S1 = [],
        liste_encours_S1 = [],
        liste_S2 = [],
        liste_encours_S2 = []
    ;

    var liste_client_S1 = [],
        liste_client_encours_S1 = [],
        liste_client_S2 = [],
        liste_client_encours_S2 = [];



    var clt;
    var cltencours;
    var ds;
    var dsencours;

    // LISTE CLIENTS Saisie 1
    $(document).find('.liste-dossier-item-S1').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');


        if (liste_client_S1.indexOf(client) < 0) {
            liste_client_S1.push(client);
            clt = new Client(client, []);
            liste_S1.push(clt);
        }

        if (clt.item.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0;
        $(document).find('#tab-S1 .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
            .find('.liste-client-item-S1 .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });

    // LISTE CLIENTS ENCOURS Saisie 1
    $(document).find('.liste-dossier-item-encours-S1').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_encours_S1.indexOf(client) < 0) {
            liste_client_encours_S1.push(client);
            cltencours = new Client(client, []);
            liste_encours_S1.push(cltencours);
        }

        if (cltencours.item.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0;
        $(document).find('#tab-S1 .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    cltencours.nb_image += Number($(element).attr('data-image'));

                    var order = Number($(element).attr('data-order')),
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
            .find('.liste-client-item-encours-S1 .liste-dossier-nb-image')
            .css('background-color', cltencours.color);
    });

    // LISTE CLIENTS Saisie 2
    $(document).find('.liste-dossier-item-S2').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_S2.indexOf(client) < 0) {
            liste_client_S2.push(client);
            clt = new Client(client, []);
            liste_S2.push(clt);
        }

        if (clt.dossier.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0;

        $(document).find('#tab-S2 .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
            .find('.liste-client-item-S2 .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });


    $(document).find('.liste-dossier-item-encours-S2').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_encours_S2.indexOf(client) < 0) {
            liste_client_encours_S2.push(client);
            cltencours = new Client(client, []);
            liste_encours_S2.push(cltencours);
        }

        if (cltencours.dossier.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0;

        $(document).find('#tab-S2 .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    cltencours.nb_image += Number($(element).attr('data-image'));

                    var order = Number($(element).attr('data-order')),
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
            .find('.liste-client-item-encours-S2 .liste-dossier-nb-image')
            .css('background-color', cltencours.color);
    });

    // Afficher nombre d'image par client
    $.each(liste_S1, function(index, item) {
        $(document).find('.liste-client-item-S1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    // Afficher nombre d'image encours par client
    $.each(liste_encours_S1, function(index, item) {
        $(document).find('.liste-client-item-encours-S1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_S2, function(index, item) {
        $(document).find('.liste-client-item-S2[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    // Afficher nombre d'image encours par client
    $.each(liste_encours_S2, function(index, item) {
        $(document).find('.liste-client-item-encours-S2[data-client="' + item.nom + '"')
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
                    var lot = opt.$trigger,
                        lot_container = lot.closest('.tab-pane').find('.liste-lot'),
                        etape = lot.closest('.tab-pane').attr('data-etape'),
                        dossier_id = lot.attr('data-dossier-id'),
                        banquecompte_id = lot.attr('data-banquecompte-id'),
                        operateur = lot.closest('tr').attr('data-operateur'),
                        date_panier = lot.attr('data-date-panier-org');

                    if(dossier_id === undefined){
                        dossier_id = '';
                    }
                    if(banquecompte_id === undefined){
                        banquecompte_id = '';
                    }

                    RetourLot(operateur, dossier_id, banquecompte_id,date_panier, etape, function () {
                        lot.detach().removeClass('dist')
                            .removeAttr('data-panier')
                            .removeAttr('data-date-panier-org')
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