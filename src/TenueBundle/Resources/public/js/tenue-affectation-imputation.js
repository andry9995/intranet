var mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

$('.loader').hide();

$(function () {
    var timeout = 120000;

    //Reload Page
    //    --- Timer ---
    setInterval(function(){
        var url = Routing.generate('tenue_affectation_imputation', { json: 1 });
        $('.loader').show();

        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var erreur = data.erreur;
            if (erreur === false) {
                // var nb_lot_IMP = data.nb_lot_IMP,
                //     nb_image_IMP = data.nb_image_IMP,
                //     nb_lot_CTRL = data.nb_lot_CTRL,
                //     nb_image_CTRL = data.nb_image_CTRL,
                var liste_jour = data.liste_jour,
                    panier_IMP = data.panier_IMP,
                    panier_CTRL = data.panier_CTRL,
                    lot_IMP = data.lot_IMP,
                    lot_IMP_encours = data.lot_IMP_encours,
                    lot_CTRL = data.lot_CTRL,
                    lot_CTRL_encours = data.lot_CTRL_encours,
                    date_header = [];
                $.each(liste_jour, function(index, item) {
                    date_header.push(moment(item.date));
                });

                // $(document).find('#nb-lot-IMP')
                //     .text(numeral(nb_lot_IMP).format('0,0'));
                // $(document).find('#nb-image-IMP')
                //     .text(numeral(nb_image_IMP).format('0,0'));
                // $(document).find('#nb-lot-CTRL_IMP')
                //     .text(numeral(nb_lot_CTRL).format('0,0'));
                // $(document).find('#nb-image-CTRL_IMP')
                //     .text(numeral(nb_image_CTRL).format('0,0'));

                refreshLotImputation(lot_IMP, lot_IMP_encours,'IMP');
                refreshLotImputation(lot_CTRL, lot_CTRL_encours, 'CTRL');

                dispatchPanierImputation(panier_IMP, date_header, 'S1');
                dispatchPanierImputation(panier_CTRL, date_header, 'CTRL');
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

    $('#table-collab-IMP').DataTable({
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

    $('[href="#tab-CTRL_IMP"]').one('click', function () {
        setTimeout(function () {
            $('#table-collab-CTRL_IMP').DataTable({
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


    $(document).on ('click', '.lot-filter', function() {

        var txt = $(this).find('a').text(),
            lis = $(this).parent().find('li'),
            libelle = $(this).closest('.input-group-btn').find('.dropdown-toggle'),
            dataType = $(this).attr('datatype'),
            encours = $('.encours'),
            attentes = $('.attente'),
            etape = 'IMP',
            nbImageAttente = 0,
            nbLotAttente = 0,
            nbImageEncours = 0,
            nbLotEncours = 0
        ;

        if($(this).hasClass('CTRL_IMP')){
            encours = $('.encours-CTRL_IMP');
            attentes = $('.attente-CTRL_IMP');
            etape = 'CTRL_IMP';
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

/* Selection client dans la liste Imputation*/
$(document).on('click', '.liste-client-item-IMP', function () {
    selectLotClient(this, 'IMP');
});

$(document).on('click', '.liste-client-item-encours-IMP', function () {
    selectLotEncoursClient(this, 'IMP');
});

/* Selection client dans la liste Contrôle Imputation*/
$(document).on('click', '.liste-client-item-CTRL_IMP', function () {
    selectLotClient(this, 'CTRL_IMP');
});

$(document).on('click', '.liste-client-item-encours-CTRL_IMP', function () {
    selectLotEncoursClient(this, 'CTRL_IMP_IMP');
});

/* Selection dossier dans la liste Imputation*/
$(document).on('click', '.liste-dossier-item-IMP', function () {
    selectLotDossier(this, 'IMP');
});

$(document).on('click', '.liste-dossier-item-encours-IMP', function () {
    selectLotEncoursDossier(this, 'IMP');
});


/* Selection dossier dans la liste Contrôle Imputation*/
$(document).on('click', '.liste-dossier-item-CTRL_IMP', function () {
    selectLotDossier(this, 'CTRL_IMP');
});

$(document).on('click', '.liste-dossier-item-encours-CTRL_IMP', function () {
    selectLotEncoursDossier(this, 'CTRL_IMP');
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
        });
        the_lot += '</div>';
        lot_categ_detail_container.append(the_lot);
        loader.hide();
        makeTooltipLotImputation(lot_categ_detail_container, false);
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

function refreshLotImputation(lot, lotencours,etape) {
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
            operateur = item.operateur,
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
        the_lot += ' data-operateur="' + operateur + '"';
        the_lot += ' data-image="' + nb_image +'">' + nb_image;
        the_lot += '</div>';

        container.append(the_lot);
    });
}
function dispatchPanierImputation(panier, date_header, etape) {
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

    var nbLot = $('#tab-' + etape + ' .liste-lot-encours').find('.lot').size();

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
    makeTooltipImputation('.liste-lot', false);
    makeTooltipImputation('.liste-lot-encours', false);
    makeTooltipImputation('#table-collab-IMP', true);
    makeTooltipImputation('#table-collab-CTRL_IMP', true);
}

function makeTooltipImputation(container, isPanier) {
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
                    tache = $(this).attr('data-tache');

                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Clôture</th><td class="col-sm-9">' + cloture + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Catégorie</th><td class="col-sm-9" >' + categorie + '</td></tr>';
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

function makeTooltipLotImputation(container, isPanier) {
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
                $.ajax({
                    url: Routing.generate('imputation_add_to_panier', {
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
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach().addClass('dist')
                                .appendTo(lot_detail);

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

                if (operateur_id != old_operateur_id || date_panier != old_date_panier) {
                    $.ajax({
                        url: Routing.generate('imputation_move_to_panier', {
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
                            var lot_detail = panier.find('.lot-detail');
                            ui.draggable.detach()
                                .removeAttr('data-categorie-id')
                                .addClass('dist')
                                .appendTo(lot_detail);

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
        url: Routing.generate('imputation_return_from_panier', {
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
        var pourc = ((total_image / capa) * 100).toFixed(2);
        var progress_class = 'progress-bar-danger';
        if (pourc <= 70) {
            //progress_class = 'progress-bar-warning';
            progress_style = 'background-color:#17a688';
        } else if (pourc > 70 && pourc <= 90) {
            //progress_class = 'progress-bar-success';
            progress_style = 'background-color:#e6d454';
        } else {
            //progress_class = '';
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

    setNbImageLot('IMP');
    setNbImageLot('CTRL_IMP');

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

    var liste_IMP = [],
        liste_IMP_encours = [],
        liste_CTRL = [],
        liste_CTRL_encours = [],
        liste_client_IMP = [],
        liste_client_IMP_encours = [],
        liste_client_CTRL = [],
        liste_client_CTRL_encours = [];

    var clt,cltencours,ds,dsencours;

    // LISTE CLIENTS Imputation
    $(document).find('.liste-dossier-item-IMP').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');


        if (liste_client_IMP.indexOf(client) < 0) {
            liste_client_IMP.push(client);
            clt = new Client(client, []);
            liste_IMP.push(clt);
        }

        if (clt.item.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0;
        $(document).find('#tab-IMP .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
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
            .find('.liste-client-item-IMP .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });


    $(document).find('.liste-dossier-item-encours-IMP').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');


        if (liste_client_IMP_encours.indexOf(client) < 0) {
            liste_client_IMP_encours.push(client);
            cltencours = new Client(client, []);
            liste_IMP_encours.push(cltencours);
        }

        if (cltencours.item.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0;
        $(document).find('#tab-IMP .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    nbimage += Number($(element).attr('data-image'));
                    cltencours.nb_image += Number($(element).attr('data-image'));

                    var order = Number($(element).attr('data-order'));
                    var color = $(element).css('background-color');

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
            .find('.liste-client-item-IMP .liste-dossier-nb-image')
            .css('background-color', cltencours.color);
    });


    // LISTE CLIENTS Contrôle Imputation
    $(document).find('.liste-dossier-item-CTRL_IMP').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_CTRL.indexOf(client) < 0) {
            liste_client_CTRL.push(client);
            clt = new Client(client, []);
            liste_CTRL.push(clt);
        }

        if (clt.dossier.indexOf(dossier) < 0) {
            clt.item.push(dossier);
            ds = new Dossier(dossier);
            clt.dossier.push(ds);
        }

        var nbimage = 0;

        $(document).find('#tab-CTRL_IMP .liste-lot .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
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
            .find('.liste-client-item-CTRL_IMP .liste-dossier-nb-image')
            .css('background-color', clt.color);
    });



    $(document).find('.liste-dossier-item-encours-CTRL_IMP').each(function (index, item) {
        var client = $(item).attr('data-client');
        var dossier = $(item).attr('data-dossier');

        if (liste_client_CTRL_encours.indexOf(client) < 0) {
            liste_client_CTRL_encours.push(client);
            cltencours = new Client(client, []);
            liste_CTRL_encours.push(cltencours);
        }

        if (cltencours.dossier.indexOf(dossier) < 0) {
            cltencours.item.push(dossier);
            dsencours = new Dossier(dossier);
            cltencours.dossier.push(dsencours);
        }

        var nbimage = 0;

        $(document).find('#tab-CTRL_IMP .liste-lot-encours .lot[data-client="' + client + '"][data-dossier="' + dossier + '"]')
            .each(function (num, element) {
                if (!isNaN($(element).attr('data-image'))) {
                    var order = Number($(element).attr('data-order'));
                    var color = $(element).css('background-color');

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
            .find('.liste-client-item-CTRL_IMP .liste-dossier-nb-image')
            .css('background-color', cltencours.color);
    });

    // Afficher nombre d'image par client
    $.each(liste_IMP, function(index, item) {
        $(document).find('.liste-client-item-IMP[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_IMP_encours, function(index, item) {
        $(document).find('.liste-client-item-encours-IMP[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_CTRL, function(index, item) {
        $(document).find('.liste-client-item-CTRL_IMP[data-client="' + item.nom + '"')
            .find('.liste-dossier-nb-image')
            .text(item.nb_image);
    });

    $.each(liste_CTRL_encours, function(index, item) {
        $(document).find('.liste-client-item-encours-CTRL_IMP[data-client="' + item.nom + '"')
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
                    var date_panier = lot.attr('data-date-panier-org');

                    RetourLot(operateur, lot_id, date_panier, etape, function () {
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