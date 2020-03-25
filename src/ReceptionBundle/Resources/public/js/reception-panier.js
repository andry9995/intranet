/**
 * Created by TEFY on 16/06/2016.
 */
var timeout = 120000;

$(function () {
    //Reload Page
    //    --- Timer ---
    var url = Routing.generate('reception_panier', { json: 1 });
    // setInterval(function() {
    //     fetch(url, {
    //         credentials: 'include'
    //     }).then(function(response) {
    //         return response.json();
    //     }).then(function(data) {
    //         var erreur = data.erreur;
    //         if (erreur === false) {
    //             var current_date = moment(data.current_date.date);
    //             $('.current-date').text(current_date.format('DD MMMM'));
    //             $('.current-hour').text(current_date.format('HH:mm'));
    //
    //             var nb_img_N1 = data.imageN1;
    //             var nb_lot_N1 = data.lotN1.length;
    //             var nb_img_N2 = data.imageN2;
    //             var nb_lot_N2 = data.lotN2.length;
    //             $('#panier-lotN1').text(nb_lot_N1);
    //             $('#panier-imageN1').text(nb_img_N1);
    //             $('#panier-lotN2').text(nb_lot_N2);
    //             $('#panier-imageN2').text(nb_img_N2);
    //
    //             var nb_fini_img_N1 = data.image_finiN1e;
    //             var nb_fini_lot_N1 = data.lot_finiN1.length;
    //             var nb_fini_img_N2 = data.image_finiN2;
    //             var nb_fini_lot_N2 = data.lot_finiN2.length;
    //
    //             $('#fini-lotN1').text(nb_fini_lot_N1);
    //             $('#fini-imageN1').text(nb_fini_img_N1);
    //             $('#fini-lotN2').text(nb_fini_lot_N2);
    //             $('#fini-imageN2').text(nb_fini_img_N2);
    //
    //             var table_traitement = $('#table-panier-reception').DataTable();
    //             var table_fini = $('#table-fini-reception').DataTable();
    //             table_traitement.clear().draw();
    //             table_fini.clear().draw();
    //
    //             var lotN1 = data.lotN1;
    //             var lotN2 = data.lotN2;
    //             $.each(lotN1, function(index, item) {
    //                 var client = item.client;
    //                 var dossier = item.dossier;
    //                 var exercice = item.exercice;
    //                 var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
    //                 var lot = item.lot;
    //                 var lot_id = item.lot_id;
    //                 var nb_image = item.nbimage;
    //                 var user_id = item.operateur_id;
    //                 var etapeN1 = item.etape_traitement_id;
    //
    //                 var niveau = 'Niv. 1 <span class="pull-right"><i class="fa fa-desktop"></i></span>';
    //                 var row = table_traitement.row.add([
    //                     client,
    //                     dossier,
    //                     exercice,
    //                     date_scan,
    //                     lot,
    //                     nb_image,
    //                     niveau
    //                 ]).draw().node();
    //                 $(row).attr('data-client', client);
    //                 $(row).attr('data-dossier', dossier);
    //                 $(row).attr('data-exercice', exercice);
    //                 $(row).attr('data-date-scan', moment(item.datescan.date).format('YYYY-MM-DD'));
    //                 $(row).attr('data-lot', lot);
    //                 $(row).attr('data-nb-image', nb_image);
    //                 $(row).attr('data-lot-id', lot_id);
    //                 $(row).attr('data-user-id', user_id);
    //                 $(row).attr('data-traitement-id', etapeN1);
    //                 $(row).attr('data-app', 'DEC_NIV_1');
    //             });
    //             $.each(lotN2, function(index, item) {
    //                 var client = item.client;
    //                 var dossier = item.dossier;
    //                 var exercice = item.exercice;
    //                 var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
    //                 var lot = item.lot;
    //                 var lot_id = item.lot_id;
    //                 var nb_image = item.nbimage;
    //                 var user_id = item.operateur_id;
    //                 var etapeN2 = item.etape_traitement_id;
    //
    //                 var niveau = 'Niv. 2 <span class="pull-right"><i class="fa fa-object-ungroup"></i></span>';
    //                 var row = table_traitement.row.add([
    //                     client,
    //                     dossier,
    //                     exercice,
    //                     date_scan,
    //                     lot,
    //                     nb_image,
    //                     niveau
    //                 ]).draw().node();
    //                 $(row).attr('data-client', client);
    //                 $(row).attr('data-dossier', dossier);
    //                 $(row).attr('data-exercice', exercice);
    //                 $(row).attr('data-date-scan', moment(item.datescan.date).format('YYYY-MM-DD'));
    //                 $(row).attr('data-lot', lot);
    //                 $(row).attr('data-nb-image', nb_image);
    //                 $(row).attr('data-lot-id', lot_id);
    //                 $(row).attr('data-user-id', user_id);
    //                 $(row).attr('data-traitement-id', etapeN2);
    //                 $(row).attr('data-app', 'DEC_NIV_2');
    //             });
    //
    //             var lot_finiN1 = data.lot_finiN1;
    //             var lot_finiN2 = data.lot_finiN2;
    //
    //             $.each(lot_finiN1, function(index, item) {
    //                 var client = item.client;
    //                 var dossier = item.dossier;
    //                 var exercice = item.exercice;
    //                 var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
    //                 var lot = item.lot;
    //                 var nb_image = item.nbimage;
    //                 var niveau = 'Niv. 1';
    //                 table_fini.row.add([
    //                     client,
    //                     dossier,
    //                     exercice,
    //                     date_scan,
    //                     lot,
    //                     nb_image,
    //                     niveau
    //                 ]).draw();
    //             });
    //
    //             $.each(lot_finiN2, function(index, item) {
    //                 var client = item.client;
    //                 var dossier = item.dossier;
    //                 var exercice = item.exercice;
    //                 var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
    //                 var lot = item.lot;
    //                 var nb_image = item.nbimage;
    //                 var niveau = 'Niv. 2';
    //                 table_fini.row.add([
    //                     client,
    //                     dossier,
    //                     exercice,
    //                     date_scan,
    //                     lot,
    //                     nb_image,
    //                     niveau
    //                 ]).draw();
    //             });
    //         }
    //     }).catch(function(err) {
    //         console.log(err);
    //     });
    // }, timeout);

    function refreshPanier()
    {
        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var erreur = data.erreur;
            if (erreur === false) {
                var current_date = moment(data.current_date.date);
                $('.current-date').text(current_date.format('DD MMMM'));
                $('.current-hour').text(current_date.format('HH:mm'));

                var nb_img_N1 = data.imageN1;
                var nb_lot_N1 = data.lotN1.length;
                var nb_img_N2 = data.imageN2;
                var nb_lot_N2 = data.lotN2.length;
                $('#panier-lotN1').text(nb_lot_N1);
                $('#panier-imageN1').text(nb_img_N1);
                $('#panier-lotN2').text(nb_lot_N2);
                $('#panier-imageN2').text(nb_img_N2);

                var nb_fini_img_N1 = data.image_finiN1e;
                var nb_fini_lot_N1 = data.lot_finiN1.length;
                var nb_fini_img_N2 = data.image_finiN2;
                var nb_fini_lot_N2 = data.lot_finiN2.length;

                $('#fini-lotN1').text(nb_fini_lot_N1);
                $('#fini-imageN1').text(nb_fini_img_N1);
                $('#fini-lotN2').text(nb_fini_lot_N2);
                $('#fini-imageN2').text(nb_fini_img_N2);

                var table_traitement = $('#table-panier-reception').DataTable();
                var table_fini = $('#table-fini-reception').DataTable();
                table_traitement.clear().draw();
                table_fini.clear().draw();

                var lotN1 = data.lotN1;
                var lotN2 = data.lotN2;
                $.each(lotN1, function(index, item) {
                    var client = item.client;
                    var dossier = item.dossier;
                    var exercice = item.exercice;
                    var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
                    var lot = item.lot;
                    var lot_id = item.lot_id;
                    var nb_image = item.nbimage;
                    var user_id = item.operateur_id;
                    var etapeN1 = item.etape_traitement_id;

                    var niveau = 'Niv. 1 <span class="pull-right"><i class="fa fa-desktop"></i></span>';
                    var row = table_traitement.row.add([
                        client,
                        dossier,
                        exercice,
                        date_scan,
                        lot,
                        nb_image,
                        niveau
                    ]).draw().node();
                    $(row).attr('data-client', client);
                    $(row).attr('data-dossier', dossier);
                    $(row).attr('data-exercice', exercice);
                    $(row).attr('data-date-scan', moment(item.datescan.date).format('YYYY-MM-DD'));
                    $(row).attr('data-lot', lot);
                    $(row).attr('data-nb-image', nb_image);
                    $(row).attr('data-lot-id', lot_id);
                    $(row).attr('data-user-id', user_id);
                    $(row).attr('data-traitement-id', etapeN1);
                    $(row).attr('data-app', 'DEC_NIV_1');
                });
                $.each(lotN2, function(index, item) {
                    var client = item.client;
                    var dossier = item.dossier;
                    var exercice = item.exercice;
                    var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
                    var lot = item.lot;
                    var lot_id = item.lot_id;
                    var nb_image = item.nbimage;
                    var user_id = item.operateur_id;
                    var etapeN2 = item.etape_traitement_id;

                    var niveau = 'Niv. 2 <span class="pull-right"><i class="fa fa-object-ungroup"></i></span>';
                    var row = table_traitement.row.add([
                        client,
                        dossier,
                        exercice,
                        date_scan,
                        lot,
                        nb_image,
                        niveau
                    ]).draw().node();
                    $(row).attr('data-client', client);
                    $(row).attr('data-dossier', dossier);
                    $(row).attr('data-exercice', exercice);
                    $(row).attr('data-date-scan', moment(item.datescan.date).format('YYYY-MM-DD'));
                    $(row).attr('data-lot', lot);
                    $(row).attr('data-nb-image', nb_image);
                    $(row).attr('data-lot-id', lot_id);
                    $(row).attr('data-user-id', user_id);
                    $(row).attr('data-traitement-id', etapeN2);
                    $(row).attr('data-app', 'DEC_NIV_2');
                });

                var lot_finiN1 = data.lot_finiN1;
                var lot_finiN2 = data.lot_finiN2;

                $.each(lot_finiN1, function(index, item) {
                    var client = item.client;
                    var dossier = item.dossier;
                    var exercice = item.exercice;
                    var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
                    var lot = item.lot;
                    var nb_image = item.nbimage;
                    var niveau = 'Niv. 1';
                    table_fini.row.add([
                        client,
                        dossier,
                        exercice,
                        date_scan,
                        lot,
                        nb_image,
                        niveau
                    ]).draw();
                });

                $.each(lot_finiN2, function(index, item) {
                    var client = item.client;
                    var dossier = item.dossier;
                    var exercice = item.exercice;
                    var date_scan = moment(item.datescan.date).format('DD-MM-YYYY');
                    var lot = item.lot;
                    var nb_image = item.nbimage;
                    var niveau = 'Niv. 2';
                    table_fini.row.add([
                        client,
                        dossier,
                        exercice,
                        date_scan,
                        lot,
                        nb_image,
                        niveau
                    ]).draw();
                });
            }
        }).catch(function(err) {
            console.log(err);
        });
    }

    $('#table-panier-reception').DataTable({
        fixedHeader: true,
        scrollY: 100,
        searching: false,
        paging: false,
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
            {"className": "text-center"},
            {"className": "text-center server-app-launch pointer"}
        ]
    }).draw();

    $('#table-fini-reception').DataTable({
        fixedHeader: true,
        scrollY: 100,
        searching: false,
        paging: false,
        info: false,
        language: {
            search: "Chercher",
            zeroRecords: "Aucune donnée trouvée."
        },
        "columnDefs": [
            {"className": "text-center", "targets": [2,3,4,5,6]}
        ]
    }).draw();

    setTimeout(function () {
        $('#table-panier-reception').DataTable().draw();
        $('#table-fini-reception').DataTable().draw();
    }, 3000);


    //Lancement EXE
    $(document).on('click', 'td.server-app-launch', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var code_app = $(this).closest('tr').attr('data-app');
        var client = $(this).closest('tr').attr('data-client');
        var dossier = $(this).closest('tr').attr('data-dossier');
        var exercice = $(this).closest('tr').attr('data-exercice');
        var date_scan = $(this).closest('tr').attr('data-date-scan');
        var lot = $(this).closest('tr').attr('data-lot');
        var nb_image = $(this).closest('tr').attr('data-nb-image');
        var lot_id = $(this).closest('tr').attr('data-lot-id');
        var user_id = $(this).closest('tr').attr('data-user-id');
        var traitement_id = $(this).closest('tr').attr('data-traitement-id');

        var parametre = client + '|' + dossier + '|' + exercice + '|' + date_scan + '|' + lot + '|' + nb_image + '|' + lot_id + '|' + user_id + '|' + traitement_id;

        $.ajax({
            url: Routing.generate('application_exe_server', { code_app: code_app }),
            type: 'POST',
            data: {
                parametre: parametre
            },
            success: function(data) {
                if (data.erreur === true) {
                    show_info("Erreur",data.erreur_text,"error")
                }
            }
        })
    });

    $(document).on('click', '#btn_correction_separation', function(){
        var user_id = $(this).attr('data-user-id');
        $.ajax({
            url: Routing.generate('reception_correction_separation'),
            type: 'GET',
            success: function(data){
                if (data.erreur === true) {
                    show_info("Erreur",data.erreur_text,"error")
                }
            }
        });
    });

    $(document).on('click', '#btn_controle_separation', function(){
        $.ajax({
            url: Routing.generate('reception_verif_separation'),
            type: 'GET',
            success: function(data){

                if (data.erreur === true) {
                    show_info("Erreur",data.erreur_text,"error")
                }
            }
        });
    });

    $(document).on('click', '#btn_controle_decoupage', function(){
        $.ajax({
            url: Routing.generate('reception_verif_decoupage'),
            type: 'GET',
            success: function(data){

                if (data.erreur === true) {
                    show_info("Erreur",data.erreur_text,"error")
                }
            }
        });
    });

    $(document).on('click', '#btn_parametrage_separation', function(){
        $.ajax({
            url: Routing.generate('reception_parametrage_separation'),
            type: 'GET',
            success: function(data){
                var animated = 'bounceInRight',
                    titre = '<span>Paramétrage Séparation</span>';
                show_modal(data, titre, animated);
            }
        });
    });

    $(document).on('click', '.btn_save_a_controler', function(){
        var datas = [];
        $('.js_param_separation_body').find('tr td').each(function(){
            var _this = $(this).find('.input_chk_sep');
            var state = _this.prop('checked');
            datas.push({
                id: _this.attr('data-id'),
                state: state
            });
        });
        
        $.ajax({
            url: Routing.generate('reception_save_parametrage_separation'),
            type: 'POST',
            data:  {
                datas: datas
            },
            success: function(data){
                show_info('Succés','Modification enregistrée avec succès');
            }
        });
    });
});
