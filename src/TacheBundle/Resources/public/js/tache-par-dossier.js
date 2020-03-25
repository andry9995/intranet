/**
 * Created by TEFY on 08/08/2016.
 */

$(function () {

    //jQGrid liste tache
    var dateList = [],
        erreur_list = [],
        is_tache_legale;
    $('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-site').chosen({
        no_results_text: "Aucun site trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-dossier').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader');

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    dossier_selector.val('').trigger('chosen:updated');

    var grid_tache = $('#js_tache_liste');
    var window_height = window.innerHeight;
    var grid_width = grid_tache.closest('.row').width() - 50;
    grid_tache.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: window_height - 300,
        width: grid_width,
        shrinkToFit: true,
        viewrecords: true,
        rownumbers: true,
        rowNum: 100,
        rowList: [100, 200, 500],
        pager: '#pager_liste_tache',
        caption: 'Liste des tâches ',
        hidegrid: false,
        colNames: ['Domaine', 'Tache_Parent_Id', 'Tache_Id', 'Tâche', 'Démarrage', 'Periode_Value', 'Période', 'Jalon', 'Légale',
            'Réaliser<br>avant (j)', 'Au + tard<br>avant (j)', 'Date Prévue', 'Entite_Value', 'Entité', 'Responsable_Scriptura', 'Responsable_Client',
            'Responsable', 'Date_List', 'Mois_Plus', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'tache-domaine', index: 'tache-domaine', width: 100, fixed: true, classes: 'js-tache-domaine'},
            {name: 'tache-parent-id', index: 'tache-parent-id', hidden: true, classes: 'js-tache-parent-id'},
            {name: 'tache-id', index: 'tache-id', hidden: true, classes: 'js-tache-id'},
            {
                name: 'nom', index: 'nom', width: 200, sorttype: function (cell) {
                return jqGridSortable(cell);
            }, classes: 'js-tache-nom'
            },
            {name: 'demarrage', index: 'demarrage', align: "center", width: 100, fixed: true, formatter: "date", sorttype: "date",
                formatoptions: { srcformat: "d/m/Y", newformat: "d/m/Y" }, classes: 'js-demarrage'},
            {name: 'periode-value', index: 'periode-value', hidden: true, classes: 'js-tache-periode-value'},
            {name: 'periode', index: 'periode', align: "center", width: 80, fixed: true, classes: 'js-tache-periode'},
            {name: 'jalon', index: 'jalon', align: "center", width: 60, fixed: true, formatter: 'checkbox', classes: 'js-tache-jalon'},
            {name: 'legale', index: 'legale', align: "center", width: 60, fixed: true, formatter: 'checkbox', classes: 'js-tache-legale'},
            {name: 'realiser-avant', index: 'realiser-avant', align: "center", width: 80, fixed: true, classes: 'js-realiser-avant'},
            {name: 'au-plus-tard', index: 'au-plus-tard', align: "center", width: 80, fixed: true, classes: 'js-au-plus-tard'},
            {name: 'date-prevue', index: 'date-prevue', align: "center", width: 120, fixed: true, formatter: "date", sorttype: "date",
                formatoptions: { srcformat: "d/m/Y", newformat: "d/m/Y" }, classes: 'js-date-prevue'},
            {name: 'entite-value', index: 'entite-value', hidden: true, classes: 'js-entite-value'},
            {name: 'entite', index: 'entite', align: "center", width: 110, fixed: true, classes: 'js-entite'},
            {name: 'responsable-scriptura', index: 'responsable-scriptura', hidden: true, classes: 'js-responsable-scriptura'},
            {name: 'responsable-client', index: 'responsable-client', hidden: true, classes: 'js-responsable-client'},
            {name: 'responsable', index: 'responsable', align: "center", width: 150, classes: 'js-responsable'},
            {name: 'date-liste', index: 'date-liste', hidden: true, classes: 'js-date-liste'},
            {name: 'mois-plus', index: 'mois-plus', hidden: true, classes: 'js-mois-plus'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false, classes: 'js-tache-action'}
        ],
        ajaxRowOptions: {async: true},
        rowattr: function(rowData, currentObj, rowId) {
            if (rowId.indexOf('legale') > -1) {
                return {"style": "background-color: #f2faf5;"}
            }
            return {};
        }
    });

    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        siteParClientMulti(client_selector, site_selector, loader_selector, function() {
            dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector)
        });
    });

    // Changement site
    $(document).on('change', '#site', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector);
    });

    //Affichage Tache par dossier
    $(document).on('click', '#btn-tache-dossier', function(event) {
        event.preventDefault();
        var dossier_id = $('#dossier').val();

        if (dossier_id !== '') {
            reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_dossier', {dossier: dossier_id}));
        } else {
            $("#js_tache_liste").jqGrid('clearGridData');
        }
    });

    //Ajouter une tache
    $(document).on('click', '#btn-add-tache', function (event) {
        event.preventDefault();
        var client = $('#client').val();
        var dossier = $('#dossier').val();

        if (dossier && dossier !== "") {
            modalAddTache(client);
        } else {
            show_info("Erreur", "Séléctionner un dossier.", "error");
        }
    });

    //Modifier une tache Existante
    $(document).on('click', '.js-edit-tache-dossier', function(event) {
        event.preventDefault();

        var client = $('#client').val();
        var row = $(this).closest('tr');
        var rowId = row.attr('id');
        var jalon = $('#add-tache-jalon');
        var legale = $('#add-tache-legale');

        if (client != "") {
            is_tache_legale = (rowId.indexOf('legale') >= 0);
            var responsable_client_elt = $('#add-tache-responsable-client');
            var responsable_scriptura_elt = $('#add-tache-responsable-scriptura');
            var demarrage = $('#add-tache-demarrage');

            /** Clear Champs */
            $('#tache-domaine').empty();
            $('#tache-generale').val('');
            $('#multi-date-container').empty();
            $('#add-tache-mois-plus').val('');
            responsable_client_elt.val('');
            responsable_scriptura_elt.val('');
            $('#add-tache-jalon').iCheck('uncheck');
            $('#add-tache-legale').iCheck('uncheck');

            $('#add-tache-id').val(row.find('.js-tache-id').text());
            demarrage.val(row.find('.js-demarrage').text());
            $('#add-tache-periode').val(row.find('.js-tache-periode-value').text());
            $('#add-tache-au-plus-tard').val(row.find('.js-au-plus-tard').text());
            $('#add-tache-realisation').val(row.find('.js-realiser-avant').text());
            $('#add-tache-entite').val(row.find('.js-entite-value').text());

            var responsable_client_value = row.find('.js-responsable-client').text();
            var responsable_scriptura_value = row.find('.js-responsable-scriptura').text();

            if (row.find('.js-tache-jalon').find('input[type="checkbox"]').is(':checked')) {
                jalon.iCheck('check');
            } else {
                jalon.iCheck('uncheck');
            }

            modalAddTache(client, true, function() {
                responsable_client_elt.val(responsable_client_value);
                responsable_scriptura_elt.val(responsable_scriptura_value);

                if (responsable_client_elt.val() !== ""  && responsable_client_elt.val() !== null) {
                    $('#responsable-client-group').removeClass('hidden');
                } else {
                    $('#responsable-client-group').addClass('hidden');
                }
                if (responsable_scriptura_elt.val() !== "" && responsable_scriptura_elt.val() !== null) {
                    $('#responsable-scriptura-group').removeClass('hidden');
                } else {
                    $('#responsable-scriptura-group').addClass('hidden');
                }
            });

            if (is_tache_legale) {
                $('.libre-group').addClass('hidden');
                $('.legale-group').removeClass('hidden');
                $('#tache-legale-nom').val(row.find('.js-tache-nom').text());
                legale.iCheck('check');
            } else {
                $('.libre-group').removeClass('hidden');
                $('.legale-group').addClass('hidden');

                $('#tache-generale').val(row.find('.js-tache-parent-id').text());
                $('#tache-domaine').html('Domaine: <span class="alert alert-info padding-sm">' + row.find('.js-tache-domaine').text() + '</span>');

                $('#add-tache-mois-plus').val(row.find('.js-mois-plus').text());
                legale.iCheck('uncheck');

                dateList = row.find('.js-date-liste').text().trim().split(',');
                console.log(dateList);
                showDateList(dateList);
            }
        } else {
            show_info("Erreur", "Séléctionner un client.", "error");
        }
    });

    //Choix tâche
    $(document).on('change', '#tache-generale', function (event) {
        event.preventDefault();
        var tache_id = $(this).val();

        $.ajax({
            url: Routing.generate('tache_get_one', {tache: tache_id}),
            success: function (data) {
                data = $.parseJSON(data);

                $('#tache-domaine').html('Domaine: <span class="alert alert-info padding-sm">' + data.tacheDomaine.domaine + '</span>');

                if (data.general) {
                    $('#add-tache-general').val(1);
                } else {
                    $('#add-tache-general').val(2);
                }

                if (data.legale) {
                    $('#add-tache-legale').iCheck('check');
                } else {
                    $('#add-tache-legale').iCheck('uncheck');
                }

                if (data.jalon) {
                    $('#add-tache-jalon').iCheck('check');
                } else {
                    $('#add-tache-jalon').iCheck('uncheck');
                }
            }
        });
    });

    //Choix Entité Responsable
    $(document).on('change', '#add-tache-entite', function (event) {
        event.preventDefault();
        var entite = $(this).val();
        if (entite === '1') {
            $('#responsable-client-group').addClass('hidden');
            $('#add-tache-responsable-client').val('');
            $('#responsable-scriptura-group').removeClass('hidden');
        } else if (entite === '2') {
            $('#responsable-client-group').removeClass('hidden');
            $('#add-tache-responsable-scriptura').val('');
            $('#responsable-scriptura-group').addClass('hidden');
        } else {
            $('#responsable-client-group').addClass('hidden');
            $('#add-tache-responsable-client').val('');
            $('#responsable-scriptura-group').addClass('hidden');
            $('#add-tache-responsable-scriptura').val('');
        }


    });
    $(document).on('click', '#add-tache-select-date', function (event) {
        event.preventDefault();
        event.stopPropagation();
    });

    //Choix Période
    $(document).on('change', '#add-tache-periode', function (event) {
        event.preventDefault();
        $(document).find('#multi-date-container').empty();
        dateList = [];
    });

    //Choix date
    $('#add-tache-select-date').datepicker({
        language: 'fr',
        daysOfWeekHighlighted: '0,6',
        todayHighlight: true
    })
        .on('changeDate', function (ev) {
            $('#add-tache-select-date').datepicker('hide');
            if ($('#add-tache-periode').val() != "") {
                var periode = $('#add-tache-periode').val();
                var item_number_max = parseInt(periode);
                if (item_number_max === 0) {
                    item_number_max = 1;
                }
                if (item_number_max === 0) {
                    item_number_max = 1;
                }
                var item_number = $(document).find('#multi-date-container .multi-date-item').length;

                if (item_number < item_number_max) {
                    dateList.push(moment(ev.date).format('YYYY-MM-DD'));
                    dateList.sort(function (a, b) {
                        if (a < b)
                            return -1;
                        if (a > b)
                            return 1;
                        return 0;
                    });

                    showDateList(dateList);

                } else {
                    show_info("Période", "Le nombre des dates est déjà égal à la période séléctionnée.", 'warning', 3000);
                }
            } else {
                show_info("Période", "Choisissez d'abord une période", 'warning', 3000);
                $('#add-tache-periode').focus();
            }

        });

    //Supprimer une date
    $(document).on('click', '.multi-date-item .close-item', function (event) {
        event.preventDefault();
        var item = $(this).closest('.multi-date-item');
        if (item.length > 0) {
            var value = item.attr('data-date');
            var index = dateList.indexOf(value);
            if (index > -1) {
                dateList.splice(index, 1);
                showDateList(dateList);
                $(this).closest('.multi-date-item').remove();
            }
        }
    });


    //Enregistrer une tache
    $('#btn-add-tache-save').on('click', function (event) {
        event.preventDefault();
        erreur_list = validateFormTacheClientDossier(is_tache_legale);
        if (erreur_list.length === 0) {
            $('#add-tache-error')
                .addClass('hidden')
                .empty();

            var id = $('#add-tache-id').val();
            var tache_id = $('#tache-generale').val();
            var demarrage = $('#add-tache-demarrage').val().trim();
            var periode = $('#add-tache-periode').val();
            var date_list = dateList;
            var mois_plus = $('#add-tache-mois-plus').val().trim();
            var plus_tard = $('#add-tache-au-plus-tard').val().trim();
            var realiser_avant = $('#add-tache-realisation').val().trim();
            var entite = $('#add-tache-entite').val();
            var resp_scriptura_id = $('#add-tache-responsable-scriptura').val();
            var resp_client_id = $('#add-tache-responsable-client').val();
            var jalon = $('#add-tache-jalon').is(':checked') ? 1 : 0;
            var legale = $('#add-tache-legale').is(':checked') ? 1 : 0;
            var dossier_id = $('#dossier').val();

            $.ajax({
                url: Routing.generate('tache_save_tache_par_dossier'),
                type: 'POST',
                data: {
                    id: id,
                    tache_id: tache_id,
                    demarrage: demarrage,
                    periode: periode,
                    date_list: date_list,
                    mois_plus: mois_plus,
                    plus_tard: plus_tard,
                    realiser_avant: realiser_avant,
                    entite: entite,
                    resp_scriptura_id: resp_scriptura_id,
                    resp_client_id: resp_client_id,
                    jalon: jalon,
                    legale: legale,
                    dossier_id: dossier_id,
                    is_tache_legale: is_tache_legale ? 1 : 0
                },
                success: function (data) {
                    data = $.parseJSON(data);
                    if (data.erreur === false) {
                        show_info('Tâche', 'Tâche enregistrée avec succès.', 'success', 1000);
                        $('#tache-modal').modal('hide');
                        reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_dossier', {dossier: dossier_id}));
                    } else {
                        show_info('Erreur', data.erreur_text, 'error', 1000);
                    }
                }
            });
        } else {
            var erreur_container = $('#add-tache-error');
            erreur_container
                .empty()
                .removeClass('hidden')
                .append('<ul></ul>');
            erreur_list.map(function(erreur) {
                erreur_container.find('ul').append('<li>' + erreur + '</li>');
            });
        }

    });

    // Supprimer une tache
    $(document).on('click', '.js-delete-tache-dossier', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_tache_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_delete_tache_par_dossier'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    $(document).on('click', '#btn-import-tache-client', function(event) {
        event.preventDefault();
        var dossier = $('#dossier').val();

        if (dossier && dossier != "") {
            swal({
                title: 'Importer tâche client dans ce dossier ?',
                text: "S'il y des tâches identiques, elles ne seront pas écrasées.",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, Importer!',
                cancelButtonText: 'Non, Annuler!',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: true,
                width: '600px',
                reverseButtons: true,
                showCloseButton: true,
                showLoaderOnConfirm: true,
                animation: false,
                customClass: 'animated fadeInDown',
                preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                       $.ajax({
                           url: Routing.generate('tache_importer_tache_client_to_dossier', {dossier: dossier}),
                           type: 'POST'
                       }).done(function(data) {
                           data = $.parseJSON(data);
                           reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_dossier', {dossier: dossier}));
                           resolve();
                       }).fail(function(err) {
                           console.log(err);
                           reject();
                       });
                    });
                }
            }).then(function() {
                swal({
                    title: 'Terminé',
                    text: 'Les taches sont importées.',
                    type: 'success',
                    timer: 3000
                }).catch(swal.noop)
            }, function(dismiss) {
                // dismiss can be 'cancel', 'overlay',
                // 'close', and 'timer'

            });
        } else {
                show_info("Erreur", "Séléctionner un dossier.", "error");
        }
    });
});
