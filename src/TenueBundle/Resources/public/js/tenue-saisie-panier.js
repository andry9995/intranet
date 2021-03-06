/**
 * Created by TEFY on 16/06/2016.
 */
var timeout = 120000;

$(function () {
    refreshPanierSaisie();
    // setInterval(function() {
    //
    // }, timeout);

    function refreshPanierSaisie()
    {
        var url = Routing.generate('tenue_saisie_panier', {json: 1});
        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var table = $('#table-panier-saisie').DataTable(),
                paniers = data.panier_OS;
            table.clear().draw();
            paniers.forEach(function(panier) {
                var traitement = '',
                    client = panier.client,
                    dossier = panier.dossier,
                    exercice = panier.exercice,
                    datescan = moment(panier.datescan.date).format('DD/MM/Y'),
                    datescan2 = moment(panier.datescan.date).format('Y-MM-DD'),
                    lot = panier.lot,
                    categorie = panier.categorie,
                    categorie_id = panier.categorie_id,
                    nbimage = panier.nbimage,
                    etape_libelle = panier.etape_libelle,
                    lot_id = panier.lot_id,
                    operateur_id = panier.operateur_id,
                    traitement_id = panier.etape_id;
                etape_code = panier.etape_code;
                if (panier.etape_code === 'OS_1') {
                    traitement = '<span class="pull-right"><i class="fa fa-desktop"></i></span>';
                } else if (panier.etape_code === 'OS_2') {
                    traitement = '<span class="pull-right"><i class="fa fa-object-ungroup"></i></span>';
                } else if (panier.etape_code === 'CTRL_OS') {
                    traitement = '<span class="pull-right"><i class="fa fa-pause"></i></span>';
                }

                var index = table.row.add([
                    client,
                    dossier,
                    exercice,
                    datescan,
                    lot,
                    categorie,
                    nbimage,
                    etape_libelle + traitement
                ]).index();
                table.rows(index).nodes().to$()
                    .attr('data-client', client)
                    .attr('data-dossier', dossier)
                    .attr('data-exercice', exercice)
                    .attr('data-date-scan', datescan2)
                    .attr('data-lot', lot)
                    .attr('data-nb-image', nbimage)
                    .attr('data-lot-id', lot_id)
                    .attr('data-user-id', operateur_id)
                    .attr('data-traitement-id', traitement_id)
                    .attr('data-categorie-id', categorie_id)
                    .attr('data-app', etape_code);
            });
            setTimeout(function() {
                table.draw();
            }, 100);
        });
    }

    $('#table-panier-saisie').DataTable({
        fixedHeader: true,
        scrollY: 100,
        paging: false,
        searching: false,
        info: false,
        language: {
            search: "Chercher",
            zeroRecords: "Aucune donnée trouvée."
        },
        "columns": [
            null,
            null,
            {"className": "text-center"},
            {"className": "text-center"},
            {"className": "text-center"},
            null,
            {"className": "text-center"},
            {"className": "text-center server-app-launch pointer"}
        ]
    });

    $('#table-fini-saisie').DataTable({
        fixedHeader: true,
        scrollY: 100,
        paging: false,
        searching: false,
        info: false,
        language: {
            search: "Chercher",
            zeroRecords: "Aucune donnée trouvée."
        },
        "columnDefs": [
            {"className": "text-center", "targets": [2,3,4,6,7]}
        ]
    });

    setTimeout(function () {
        $('#table-panier-saisie').DataTable().draw();
        $('#table-fini-saisie').DataTable().draw();
    }, 2000);
});
