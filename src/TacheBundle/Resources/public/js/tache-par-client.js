/**
 * Created by TEFY on 08/08/2016.
 */

$(function () {

    //jQGrid liste tache
    var dateList = [];
    var erreur_list = [];

    $('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true
    });

    $('#js_tache_liste').jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        autowidth: true,
        height: 500,
        shrinkToFit: true,
        viewrecords: true,
        rownumbers: true,
        rowNum: 100,
        rowList: [100, 200, 500],
        pager: '#pager_liste_tache',
        caption: 'Liste des tâches ',
        hidegrid: false,
        colNames: ['Domaine', 'Tache_Parent_Id', 'Tache_Id', 'Tâche', 'Démarrage', 'Periode_Value', 'Période', 'Jalon', 'Légale',
            'Réaliser avant (j)', 'Au + tard avant (j)', 'Entite_Value', 'Entité', 'Responsable_Scriptura', 'Responsable_Client',
            'Responsable', 'Date_List', 'Mois_Plus', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'tache-domaine', index: 'tache-domaine', classes: 'js-tache-domaine'},
            {name: 'tache-parent-id', index: 'tache-parent-id', hidden: true, classes: 'js-tache-parent-id'},
            {name: 'tache-id', index: 'tache-id', hidden: true, classes: 'js-tache-id'},
            {
                name: 'nom', index: 'nom', sorttype: function (cell) {
                return jqGridSortable(cell);
            }, classes: 'js-tache-nom'
            },
            {name: 'periode-demarrage', index: 'periode-demarrage', align: "center", width: 80, classes: 'js-tache-demarrage'},
            {name: 'periode-value', index: 'periode-value', hidden: true, classes: 'js-tache-periode-value'},
            {name: 'periode', index: 'periode', align: "center", width: 80, classes: 'js-tache-periode'},
            {name: 'jalon', index: 'jalon', align: "center", width: 60, formatter: 'checkbox', classes: 'js-tache-jalon'},
            {name: 'legale', index: 'legale', align: "center", width: 60, formatter: 'checkbox', classes: 'js-tache-legale'},
            {name: 'realiser-avant', index: 'realiser-avant', align: "center", width: 110, classes: 'js-realiser-avant'},
            {name: 'au-plus-tard', index: 'au-plus-tard', align: "center", width: 110, classes: 'js-au-plus-tard'},
            {name: 'entite-value', index: 'entite-value', hidden: true, classes: 'js-entite-value'},
            {name: 'entite', index: 'entite', align: "center", width: 80, classes: 'js-entite'},
            {name: 'responsable-scriptura', index: 'responsable-scriptura', hidden: true, classes: 'js-responsable-scriptura'},
            {name: 'responsable-client', index: 'responsable-client', hidden: true, classes: 'js-responsable-client'},
            {name: 'responsable', index: 'responsable', align: "center", classes: 'js-responsable'},
            {name: 'date-liste', index: 'date-liste', hidden: true, classes: 'js-date-liste'},
            {name: 'mois-plus', index: 'mois-plus', hidden: true, classes: 'js-mois-plus'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false, classes: 'js-tache-action'}
        ],
        ajaxRowOptions: {async: true}
    });


    // Chargement liste tache d'un client
    $(document).on('click', '#btn-show-tache-client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var client_id = $('#client').val();

        if (client_id !== '') {
            reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_client', {client: client_id}));
        } else {
            $("#js_tache_liste").jqGrid('clearGridData');
        }
    });

    //Ajouter une tache
    $(document).on('click', '#btn-add-tache', function (event) {
        event.preventDefault();
        var client = $('#client').val();
        if (client !== "") {
            modalAddTache(client);
        } else {
            show_info("Erreur", "Séléctionner un client.", "error");
        }
    });

    //Modifier une tache Existante
    $(document).on('click', '.js-edit-tache-client', function(event) {
        event.preventDefault();

        var client = $('#client').val();
        if (client !== "") {
            var row = $(this).closest('tr');

            $('#tache-generale').val(row.find('.js-tache-parent-id').text());
            $('#tache-domaine').html('Domaine: <span class="alert alert-info padding-sm">' + row.find('.js-tache-domaine').text() + '</span>');
            $('#add-tache-id').val(row.find('.js-tache-id').text());
            $('#add-tache-demarrage').val(row.find('.js-tache-demarrage').text());
            $('#add-tache-periode').val(row.find('.js-tache-periode-value').text());
            $('#add-tache-mois-plus').val(row.find('.js-mois-plus').text());
            $('#add-tache-au-plus-tard').val(row.find('.js-au-plus-tard').text());
            $('#add-tache-realisation').val(row.find('.js-realiser-avant').text());
            $('#add-tache-entite').val(row.find('.js-entite-value').text());

            var responsable_client_elt = $('#add-tache-responsable-client');
            var responsable_scriptura_elt = $('#add-tache-responsable-scriptura');
            var responsable_client_value = row.find('.js-responsable-client').text();
            var responsable_scriptura_value = row.find('.js-responsable-scriptura').text();


            var jalon = $('#add-tache-jalon');
            var legale = $('#add-tache-legale');

            if (row.find('.js-tache-jalon').find('input[type="checkbox"]').is(':checked')) {
                jalon.iCheck('check');
            } else {
                jalon.iCheck('uncheck');
            }

            if (row.find('.js-tache-legale').find('input[type="checkbox"]').is(':checked')) {
                legale.iCheck('check');
            } else {
                legale.iCheck('uncheck');
            }

            dateList = row.find('.js-date-liste').text().split(',');
            showDateList(dateList);
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
            var add_tache_periode = $('#add-tache-periode');
            $('#add-tache-select-date').datepicker('hide');
            if (add_tache_periode.val() !== "") {
                var periode = add_tache_periode.val();
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
                add_tache_periode.focus();
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
        erreur_list = validateFormTacheClientDossier();
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
            var client_id = $('#client').val();

            $.ajax({
                url: Routing.generate('tache_save_tache_par_client'),
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
                    client_id: client_id
                },
                success: function (data) {
                    data = $.parseJSON(data);
                    if (data.erreur === false) {
                        show_info('Tâche', 'Tâche enregistrée avec succès.', 'success', 1000);
                        $('#tache-modal').modal('hide');
                        reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_client', {client: client_id}));
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
    $(document).on('click', '.js-delete-tache-client', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_tache_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_delete_tache_par_client'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });
});
