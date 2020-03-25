var mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

$('.loader').hide();

$(function () {
    var timeout = 120000;

    //Reload Page
    //    --- Timer ---
    /*setInterval(function(){

    }, timeout);*/
    // refreshSaisie();


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

    $('#btn_refresh_sai1').on('click', function() {
        $('#btn_refresh_sai1').hide();
        refreshSaisie();
        $('#btn_refresh_sai1').show();
    });

    $('#btn_refresh_sai2').on('click', function() {
        $('#btn_refresh_sai2').hide();
        refreshSaisie();
        $('#btn_refresh_sai2').show();
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
        }, 10);
    });

    $('[href="#tab-CTRL"]').one('click', function () {
        setTimeout(function () {
            $('#table-collab-ctrl').DataTable({
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
        }, 10);
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


    $(document).on ('click', '.lot-filter', function() {

        var txt = $(this).find('a').text(),
            lis = $(this).parent().find('li'),
            libelle = $(this).closest('.input-group-btn').find('.dropdown-toggle'),
            dataType = $(this).attr('datatype'),
            encours = $('.encours'),
            attentes = $('.attente'),
            etape = 'S1',
            nbImageAttente = 0,
            nbLotAttente = 0,
            nbImageEncours = 0,
            nbLotEncours = 0
        ;

        if($(this).hasClass('S2')){
            encours = $('.encours-S2');
            attentes = $('.attente-S2');
            etape = 'S2';
        }
        else if($(this).hasClass('CTRL')){
            encours = $('.encours-CTRL');
            attentes = $('.attente-CTRL');
            etape = 'CTRL';
        }


        lis.removeClass('active');
        $(this).addClass('active');

        libelle.text(txt);

        var nbAttente,nbEncours;


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

                 nbAttente = selectLotAll(this, etape, false);
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

});

function refreshSaisie()
{
    var url = Routing.generate('tenue_affectation_saisie', { json: 1 });
    $('.loader').show();

    fetch(url, {
        credentials: 'include'
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        var erreur = data.erreur;
        if (erreur === false) {
            // var nb_lot_S1 = data.nb_lot_S1,
            //     nb_image_S1 = data.nb_image_S1,
            //     nb_lot_S2 = data.nb_lot_S2,
            //     nb_image_S2 = data.nb_image_S2,
            //     nb_lot_CTRL = data.nb_lot_CTRL,
            //     nb_image_CTRL = data.nb_image_CTRL,
            var liste_jour = data.liste_jour,
                panier_S1 = data.panier_S1,
                panier_S2 = data.panier_S2,
                panier_CTRL = data.panier_CTRL,
                lot_S1 = data.lot_S1,
                lot_S1_encours = data.lot_S1_encours,
                lot_S2 = data.lot_S2,
                lot_S2_encours = data.lot_S2_encours,
                lot_CTRL = data.lot_CTRL,
                lot_CTRL_encours = data.lot_CTRL_encours,
                date_header = [];
            $.each(liste_jour, function(index, item) {
                date_header.push(moment(item.date));
            });

            // $(document).find('#nb-lot-S1')
            //     .text(numeral(nb_lot_S1).format('0,0'));
            // $(document).find('#nb-image-S1')
            //     .text(numeral(nb_image_S1).format('0,0'));
            // $(document).find('#nb-lot-S2')
            //     .text(numeral(nb_lot_S2).format('0,0'));
            // $(document).find('#nb-image-S2')
            //     .text(numeral(nb_image_S2).format('0,0'));
            // $(document).find('#nb-lot-CTRL')
            //     .text(numeral(nb_lot_CTRL).format('0,0'));
            // $(document).find('#nb-image-CTRL')
            //     .text(numeral(nb_image_CTRL).format('0,0'));

            refreshLotSaisie(lot_S1, lot_S1_encours,'S1');
            refreshLotSaisie(lot_S2, lot_S2_encours, 'S2');
            refreshLotSaisie(lot_CTRL,lot_CTRL_encours ,'CTRL');

            dispatchPanierSaisie(panier_S1, date_header, 'S1');
            dispatchPanierSaisie(panier_S2, date_header, 'S2');
            dispatchPanierSaisie(panier_CTRL, date_header, 'CTRL');
            updateTooltip();
            makeLotDraggable();
            refreshPanier();
            $('.loader').hide();
        }
    }).then(function(err) {
        $('.loader').hide();
    });
}


/* Selection client dans la liste Saisie 1*/
$(document).on('click', '.liste-client-item-S1', function () {
    selectLotClient(this, 'S1');
});

$(document).on('click', '.liste-client-item-encours-S1', function () {
    selectLotEncoursClient(this, 'S1');
});

/* Selection client dans la liste Saisie 2*/
$(document).on('click', '.liste-client-item-S2', function () {
    selectLotClient(this, 'S2');
});

$(document).on('click', '.liste-client-item-encours-S2', function () {
    selectLotEncoursClient(this, 'S2');
});

/* Selection client dans la liste Contrôle Saisie*/
$(document).on('click', '.liste-client-item-CTRL', function () {
    selectLotClient(this, 'CTRL');
});

$(document).on('click', '.liste-client-item-encours-CTRL', function () {
    selectLotEncoursClient(this, 'CTRL');
});

/* Selection dossier dans la liste Saisie 1*/
$(document).on('click', '.liste-dossier-item-S1', function () {
    selectLotDossier(this, 'S1');
});

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

/* Selection dossier dans la liste Contrôle Saisie*/
$(document).on('click', '.liste-dossier-item-CTRL', function () {
    selectLotDossier(this, 'CTRL');
});

$(document).on('click', '.liste-dossier-item-encours-CTRL', function () {
    selectLotEncoursDossier(this, 'CTRL');
});

// Affichage Lot par catégorie
$(document).on('dblclick', '.lot:not(.dist):not(.par-categ)', function(event) {
    event.preventDefault();
    event.stopPropagation();
    var lot_container = $(this)
        .closest('.tab-pane')
        .find('.liste-lot');
    var lot_categ_container = $(this)
        .closest('.tab-pane')
        .find('.liste-lot-categorie');
    var lot_categ_detail_container = lot_categ_container.find('.lot-par-categ-detail');
    if (lot_categ_container.length > 0) {
        lot_categ_detail_container.empty();
    }
    var loader = $(this)
        .closest('.tab-pane')
        .find('.loader');

    lot_container.addClass('hidden');
    lot_categ_container.removeClass('hidden');
    loader.show();

    var etape = $(this)
        .closest('.tab-pane')
        .attr('data-etape'),
        lot = $(this).attr('data-lot'),
        client = $(this).attr('data-client'),
        site = $(this).attr('data-site'),
        dossier = $(this).attr('data-dossier'),
        cloture = $(this).attr('data-cloture'),
        date_scan = $(this).attr('data-datescan'),
        priorite = $(this).attr('data-priorite'),
        tache = $(this).attr('data-tache'),
        order = $(this).attr('data-order');
    var url = Routing.generate('tenue_show_par_categorie', { lot: lot, etape: etape });

    fetch(url, {
        credentials: 'include'
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        var categorie = data.categorie;
        var the_lot = '<div class="row">';

        $.each(categorie, function(index, item) {
            if (item.categorie_id !==16)
            {
                the_lot += '<div class="col-sm-2">';
                the_lot += '<h5>' + item.categorie + '</h5>';
                the_lot += '<div class="lot par-categ"';
                the_lot += ' style="background-color:' + item.color + '"';
                the_lot += ' data-lot="' + lot +'"';
                the_lot += ' data-client="' + client +'"';
                the_lot += ' data-site="' + site +'"';
                the_lot += ' data-dossier="' + dossier +'"';
                the_lot += ' data-cloture="' + cloture +'"';
                the_lot += ' data-datescan="' + date_scan + '"';
                the_lot += ' data-categorie="' + item.categorie + '"';
                the_lot += ' data-categorie-id="' + item.categorie_id + '"';
                the_lot += ' data-priorite="' + priorite + '"';
                the_lot += ' data-tache="' + tache + '"';
                the_lot += ' data-order="' + order + '"';
                the_lot += ' data-image="' + item.nbimage +'">' + item.nbimage;
                the_lot += '</div></div>';
            }

        });
        the_lot += '</div>';
        lot_categ_detail_container.append(the_lot);
        loader.hide();
        makeTooltipLotSaisie(lot_categ_detail_container, false);
        makeLotDraggable(lot_categ_detail_container);
    }).catch(function(err) {
        loader.hide();
        console.log(err);
    });
});

//Fermeture affichage Lot par categorie
$(document).on('click', '.btn-return-to-lot', function(event) {
    event.preventDefault();
    event.stopPropagation();

    var lot_container = $(this)
        .closest('.tab-pane')
        .find('.liste-lot');
    var lot_categ_container = $(this)
        .closest('.tab-pane')
        .find('.liste-lot-categorie');
    lot_container.removeClass('hidden');
    lot_categ_container.addClass('hidden');
});

function refreshLotSaisie(lot, lotencours,etape) {
    var container = $('#tab-' + etape + ' .liste-lot'),
        containerEncours = $('#tab-' + etape + ' .liste-lot-encours');

    container.empty();
    containerEncours.empty();

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
        the_lot += ' data-datescan="' + date_scan + '"';
        the_lot += ' data-categorie="' + categorie + '"';
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
            date_scan = moment(item.date_scan.date).format('Y-MM-DD'),
            lot = item.lot_id,
            nb_image = item.nbimage,
            categorie = item.categorie,
            priorite = item.priorite != null ? moment(item.priorite.date).format('Y-MM-DD') : '',
            order = item.order,
            tache = item.tache,
            color = item.color,
            operateur = item.operateur
        ;

        var the_lot = '<div class="lot"';
        the_lot += ' style="background-color:' + color + '"';
        the_lot += ' data-lot="' + lot +'"';
        the_lot += ' data-client="' + client +'"';
        the_lot += ' data-site="' + site +'"';
        the_lot += ' data-dossier="' + dossier +'"';
        the_lot += ' data-cloture="' + cloture +'"';
        the_lot += ' data-datescan="' + date_scan + '"';
        the_lot += ' data-categorie="' + categorie + '"';
        the_lot += ' data-priorite="' + priorite + '"';
        the_lot += ' data-order="' + order + '"';
        the_lot += ' data-tache="' + tache + '"';
        the_lot += ' data-operateur="' + operateur + '"';
        the_lot += ' data-image="' + nb_image +'">' + nb_image;
        the_lot += '</div>';

        container.append(the_lot);
    });

}
function dispatchPanierSaisie(panier, date_header, etape) {
    $('#table-collab-' + etape + ' .lot-detail').empty();
    $.each(panier, function(index, item) {
        //console.log(item.date_panier.date, moment(item.date_panier.date));

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
            the_lot += ' data-datescan="' + date_scan + '"';
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

function selectLotAll(selector, etape, first) {
    $(document).find('#liste-client-' + etape)
        .find('.liste-dossier-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape)
        .find('.liste-client-item-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-' + etape + ' .panel-collapse.collapse')
        .collapse('hide');

    if(!first)
        $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot .lot')
        .removeClass('hidden');

    var nbImage = 0;

    $('.liste-client-item-'+etape).each(function(){
        nbImage += parseInt($(this).find('.liste-dossier-nb-image').html());

    });

    var nbLot = $('#tab-' + etape + ' .liste-lot').find('.lot').size();

    return {nbImage: nbImage, nbLot: nbLot};
}


function selectLotEncoursAll(selector, etape) {
    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-dossier-item-encours-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape)
        .find('.liste-client-item-encours-' + etape)
        .removeClass('active');

    $(document).find('#liste-client-encours-' + etape + ' .panel-collapse.collapse')
        .collapse('hide');
    $(selector).addClass('active');

    $(document).find('#tab-' + etape + ' .liste-lot-encours .lot')
        .removeClass('hidden');


    var nbImage = 0;

    $('.liste-client-item-encours-'+etape).each(function(){
        nbImage += parseInt($(this).find('.liste-dossier-nb-image').html());

    });

    var nbLot = $('#tab-' + etape +' .liste-lot-encours').find('.lot').size();

    return {nbImage: nbImage, nbLot: nbLot};
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
    makeTooltipLotSaisie('#table-collab-CTRL', true);
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
                    datescan = moment($(this).attr('data-datescan')).format('DD/MM/Y'),
                    categorie = $(this).attr('data-categorie'),
                    priorite = $(this).attr('data-priorite'),
                    tache = $(this).attr('data-tache'),
                    operateur = $(this).attr('data-operateur')
                ;

                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Clôture</th><td class="col-sm-9">' + cloture + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Catégorie</th><td class="col-sm-9" >' + categorie + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Echéance</th><td class="col-sm-9" >' + priorite + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Tâche</th><td class="col-sm-9" >' + tache + '</td></tr>';

                if(operateur !== undefined)
                    modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Operateur</th><td class="col-sm-9" >' + operateur + '</td></tr>';

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
    console.log(container);
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
            //PARTAGE DEPUIS LISTE LOT
            if (!ui.draggable.hasClass('dist')) {
                var panier = $(this),
                    lot_id = ui.draggable.attr('data-lot'),
                    categorie_id = typeof ui.draggable.attr('data-categorie-id') === 'undefined' ? 0 : ui.draggable.attr('data-categorie-id'),
                    etape = $(this).closest('.tab-pane').attr('data-etape'),
                    operateur_id = panier.closest('tr').attr('data-operateur'),
                    date_panier = panier.closest('td').attr('data-date-panier');

                var classy = '.cl_' + operateur_id + '_' + date_panier;

                $.ajax({
                    url: Routing.generate('saisie_add_to_panier', {
                        'operateur': operateur_id,
                        'lot': lot_id,
                        'categorie': categorie_id,
                        'etape': etape
                    }),
                    type: 'POST',
                    data: {
                        date_panier: date_panier
                    },
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (data.erreur === false) {
                            var class_panier = '.OS_2';
                            var lot_detail = panier.find('.lot-detail');
                            if (etape === 'OS_2')
                                class_panier = '.OS_1';

                            if (etape === 'OS_2')
                                ui.draggable.detach().addClass('dist lot_saisie2')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);
                            else if (etape === 'OS_1')
                                ui.draggable.detach().addClass('dist lot_saisie1')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);
                            else
                                ui.draggable.detach().addClass('dist lot_control')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);
                            $(classy).find('.lot-detail').html(lot_detail.html());

                            if (etape === 'OS_2') {
                                $(classy).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie1'))
                                    {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie1');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie2');
                                                $(this).addClass('hidden');
                                            }
                                        });
                                    }
                                });
                            }
                            else if (etape === 'OS_1')
                            {
                                $(classy).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie2')) {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie2');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie1');
                                                $(this).addClass('hidden');
                                            }
                                        });

                                    }
                                });
                            }
                            makeLotDraggable();
                            refreshPanier();
                        }
                    }
                });
            } else {
                // DEPLACEMENT D'UN LOT D'UN PANIER VERS UN AUTRE PANIER
                var panier = $(this),
                lot_id = ui.draggable.attr('data-lot'),
                operateur_id = panier.closest('tr').attr('data-operateur'),
                old_operateur_id = ui.draggable.closest('tr').attr('data-operateur'),
                etape = $(this).closest('.tab-pane').attr('data-etape'),
                categorie_id = typeof ui.draggable.attr('data-categorie-id') === 'undefined' ? 0 : ui.draggable.attr('data-categorie-id'),
                date_panier = $(this).closest('td').attr('data-date-panier'),
                date_panier_org = ui.draggable.attr('data-date-panier-org'),
                old_date_panier = ui.draggable.closest('td').attr('data-date-panier');

                var classyOld = '.cl_' + old_operateur_id + '_' + old_date_panier;

                var classy = '.cl_' + operateur_id + '_' + date_panier;

                if (operateur_id != old_operateur_id || date_panier != old_date_panier) {
                    $.ajax({
                        url: Routing.generate('saisie_move_to_panier', {
                            'operateur': operateur_id,
                            'oldoperateur': old_operateur_id,
                            'lot': lot_id,
                            'categorie': categorie_id,
                            'etape': etape
                        }),
                        type: 'POST',
                        data: {
                            date_panier: date_panier,
                            date_panier_org: date_panier_org,
                        },
                        success: function (data) {
                            data = $.parseJSON(data);
                            console.log('succes be');
                            var lot_detail = panier.find('.lot-detail');


                            if (etape === 'OS_2')
                                ui.draggable.detach()
                                    .removeAttr('data-categorie-id')
                                    .addClass('dist lot_saisie2')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);
                            else if (etape === 'OS_1')
                                ui.draggable.detach()
                                    .removeAttr('data-categorie-id')
                                    .addClass('dist lot_saisie1')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);
                            else
                                ui.draggable.detach()
                                    .removeAttr('data-categorie-id')
                                    .addClass('dist lot_control')
                                    .attr('data-date-panier-org', date_panier)
                                    .appendTo(lot_detail);

                            $(classy).find('.lot-detail').html(lot_detail.html());
                            if (etape === 'OS_2') {
                                $(classy).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie1'))
                                    {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie1');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie2');
                                                $(this).addClass('hidden');
                                            }
                                        });
                                    }
                                });
                            }
                            else if (etape === 'OS_1')
                            {
                                $(classy).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie2')) {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie2');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie1');
                                                $(this).addClass('hidden');
                                            }
                                        });
                                    }
                                });
                            }

                            //Modifier contenu panier saisie1/saisie 2
                            if (etape === 'OS_2') {
                                $(classyOld).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie1'))
                                    {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie1');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie2');
                                                $(this).addClass('hidden');
                                            }
                                        });
                                    }
                                });
                            }
                            else if (etape === 'OS_1')
                            {
                                $(classyOld).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie2')) {
                                        $(this).find('.lot').each(function () {
                                            if ($(this).hasClass('hidden')) {
                                                $(this).removeClass('hidden');
                                                $(this).addClass('lot_saisie2');
                                            }
                                            else {
                                                $(this).removeClass('lot_saisie1');
                                                $(this).addClass('hidden');
                                            }
                                        });

                                    }
                                });
                            }

                            //Supprimer element si lot déplacé
                            if (etape === 'OS_2') {
                                $(classyOld).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie1'))
                                    {
                                        $(this).find('.lot').each(function () {

                                            if ( parseInt($(this).attr('data-lot')) === parseInt(lot_id))
                                            {
                                                $(this).remove();
                                            }

                                        });
                                    }
                                });
                            }
                            else if (etape === 'OS_1')
                            {
                                $(classyOld).find('.lot-detail').each(function () {
                                    if ($(this).hasClass('lot_saisie2'))
                                    {
                                        $(this).find('.lot').each(function () {
                                            if ( parseInt($(this).attr('data-lot')) === parseInt(lot_id))
                                            {
                                                $(this).remove();
                                            }
                                        });
                                    }
                                });
                            }
                            makeLotDraggable();
                            refreshPanier();
                        }
                    });
                }
            }
        }
    });
}

function RetourLot(operateur, lot_id, date_panier, etape, callback) {

    $.ajax({
        url: Routing.generate('saisie_return_from_panier', {
            'operateur': operateur,
            'lot': lot_id,
            'etape': etape
        }),
        data: {
            date_panier: date_panier
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
        var progress_class = 'progress-bar-danger';
        var pourc = ((total_image / capa) * 100).toFixed(2);

        if (pourc <= 70) {
            //progress_class = 'progress-bar-warning';
            progress_style = 'background-color:#17a688';
        } else if (pourc > 70 && pourc <= 90) {
            //progress_class = 'progress-bar-success';
            progress_style = 'background-color:#e6d454';
        } else {
            progress_style = 'background-color:red';
        }

        /*panier.find('.progress>div')
            .attr('style', 'width: ' + total_image + '%')
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

    setNbImageLot('S1');
    setNbImageLot('S2');
    setNbImageLot('CTRL');
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

    var liste_S1 = [];
    var liste_S1_encours = [];
    var liste_S2 = [];
    var liste_S2_encours = [];
    var liste_controle = [];
    var liste_controle_encours = [];

    var liste_client_S1 = [];
    var liste_client_S1_encours = [];
    var liste_client_S2 = [];
    var liste_client_S2_encours = [];
    var liste_client_controle = [];
    var liste_client_controle_encours = [];

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

                    var order = Number($(element).attr('data-order')),
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


    $(document).find('.liste-dossier-item-encours-S1').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');


        if (liste_client_S1_encours.indexOf(client) < 0) {
            liste_client_S1_encours.push(client);
            cltencours = new Client(client, []);
            liste_S1_encours.push(cltencours);
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

        if (liste_client_S2_encours.indexOf(client) < 0) {
            liste_client_S2_encours.push(client);
            cltencours = new Client(client, []);
            liste_S2_encours.push(cltencours);
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
            .css('background-color', ds.color);
        $(item).closest('.panel')
            .find('.liste-client-item-encours-S2 .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });


    // LISTE CLIENTS Contrôle Saisie
    $(document).find('.liste-dossier-item-CTRL').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_controle.indexOf(client) < 0) {
            liste_client_controle.push(client);
            clt = new Client(client, []);
            liste_controle.push(clt);
        }

        if (clt.dossier.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0;

        $(document).find('#tab-CTRL .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    clt.nb_image += Number($(element).attr('data-image'));
                    var order = Number($(element).attr('data-order')),
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
            .find('.liste-client-item-CTRL .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });


    $(document).find('.liste-dossier-item-encours-CTRL').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_controle_encours.indexOf(client) < 0) {
            liste_client_controle_encours.push(client);
            cltencours = new Client(client, []);
            liste_controle_encours.push(cltencours);
        }

        if (cltencours.dossier.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(ds);
        }

        var nbimage = 0;

        $(document).find('#tab-CTRL .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
            .css('background-color', ds.color);
        $(item).closest('.panel')
            .find('.liste-client-item-encours-CTRL .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });

    // Afficher nombre d'image par client
    $.each(liste_S1, function(index, item) {
        $(document).find('.liste-client-item-S1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_S1_encours, function(index, item) {
        $(document).find('.liste-client-item-encours-S1[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });


    $.each(liste_S2, function(index, item) {
        $(document).find('.liste-client-item-S2[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });


    $.each(liste_S2_encours, function(index, item) {
        $(document).find('.liste-client-item-encours-S2[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_controle, function(index, item) {
        $(document).find('.liste-client-item-CTRL[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);

    });


    $.each(liste_controle_encours, function(index, item) {
        $(document).find('.liste-client-item-encours-CTRL[data-client="' + item.nom + '"')
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
                    var etape = lot.closest('.tab-pane').attr('data-etape');
                    var lot_id = lot.attr('data-lot');
                    var operateur = lot.closest('tr').attr('data-operateur');
                    //var date_panier = lot.closest('td').attr('data-date-panier'); //lot.attr('data-date-panier-org');
                    var classy = '.cl_' + operateur + '_' + lot.closest('td').attr('data-date-panier-org');
                    var lot_container2 = lot.closest('td').find('.lot-panier-container');
                    var lot_container_detail = lot_container2.closest('div').find('.lot-detail');
                    var lot_detail = lot_container_detail.closest('div').find('.lot');
                    var date_panier = lot_detail.attr('data-date-panier-org');
                    RetourLot(operateur, lot_id, date_panier, etape, function () {

                        lot.detach().removeClass('dist').removeClass('lot_saisie1').removeClass('lot_saisie2')
                            .removeAttr('data-panier')
                            .removeAttr('data-date-panier-org')
                            .prependTo(lot_container);

                        if (etape === 'OS_2') {
                            $(classy).find('.lot-detail').each(function () {
                                if ($(this).hasClass('lot_saisie1'))
                                {
                                    $(this).find('.lot').each(function () {

                                        if ($(this).hasClass('hidden')) {
                                            if ( parseInt($(this).attr('data-lot')) === parseInt(lot_id))
                                            {
                                                $(this).remove();
                                            }
                                        }
                                    });
                                }
                            });
                        }
                        else if (etape === 'OS_1')
                        {
                            $(classy).find('.lot-detail').each(function () {
                                if ($(this).hasClass('lot_saisie2'))
                                {
                                    $(this).find('.lot').each(function () {

                                        if ($(this).hasClass('hidden')) {
                                            if ( parseInt($(this).attr('data-lot')) === parseInt(lot_id))
                                            {
                                                $(this).remove();
                                            }
                                        }
                                    });
                                }
                            });
                        }

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