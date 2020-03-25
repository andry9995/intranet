/**
 * Created by TEFY on 02/05/2016.
 */

$(function () {
    $('#js_poste_liste').jqGrid({
        url: Routing.generate('parametre_poste_liste'),
        datatype: 'json',
        loadonce: true,
        sortable: true,
        autowidth: true,
        height: 500,
        shrinkToFit: true,
        viewrecords: true,
        rownumbers: true,
        rowNum: 500,
        rowList: [10, 20, 30, 50, 100, 500, 1000],
        pager: '#pager_liste_poste',
        caption: 'Liste des postes',
        hidegrid: false,
        colNames: ['Id_Entite', 'Entité', 'Id_Departement', 'Département', 'Id_Service', 'Service',
            'Id_Cellule', 'Cellule', 'Poste', 'Capacité', 'Description', 'Utilisateur', 'Date création', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'id-entite', index: 'id-entite', classes: 'js-poste-id-entite', hidden: true},
            {name: 'entite', index: 'entite', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-entite'},
            {name: 'id-departement', index: 'id-departement', classes: 'js-poste-id-departement', hidden: true},
            {name: 'departement', index: 'departement', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-departement'},
            {name: 'id-service', index: 'id-service', sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-id-service', hidden: true},
            {name: 'service', index: 'service', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-service'},
            {name: 'id-cellule', index: 'id-cellule', classes: 'js-poste-id-cellule', hidden:true},
            {name: 'cellule', index: 'cellule', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-cellule'},
            {name: 'nom', index: 'nom', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-nom'},
            {name: 'capacite', index: 'capacite', width: 80, fixed: true, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-capacite'},
            {name: 'description', index: 'description', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-description'},
            {name: 'utilisateur', index: 'utilisateur', width: 150, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-poste-utilisateur'},
            {
                name: 'date_creation', index: 'date_creation', width: 80, align: "center",
                sorttype: "date", formatter: "date", "datefmt": "d-m-Y", "formatoptions": {"newformat": "d-m-Y"},
                classes: 'js-poste-date-creation'
            },
            {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-poste-action'}
        ]
    });


    // Enregistrement modif/ajout Poste
    $(document).on('click', '#btn-add-poste-save', function (event) {
        event.preventDefault();
        $(document).find('#add-poste-erreur').addClass('hidden');
        var nom = $(document).find('#add-poste-nom').val().trim();
        var description = $(document).find('#add-poste-description').val().trim();
        var cellule = $(document).find('#add-poste-cellule').val().trim();
        var capacite = $(document).find('#add-poste-capacite').val().trim() != '' ?
            $(document).find('#add-poste-capacite').val().trim() : 0;
        var id = $(document).find('#add-poste-id').val();

        if (nom != '' && cellule != '0') {
            $.ajax({
                url: Routing.generate('parametre_poste'),
                type: 'POST',
                data: {
                    id: id,
                    nom: nom,
                    description: description,
                    cellule: cellule,
                    capacite: capacite
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur === true) {
                        $('#add-poste-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-poste-erreur').addClass('hidden');
                        $('#add-poste-nom').val('');
                        $('#add-poste-description').val('');
                        $('#poste-modal').modal('hide');

                        $("#js_poste_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        } else {
            $('#add-poste-erreur').removeClass('hidden');
            if (nom == '') {
                $('#add-poste-erreur').text('Le nom du poste est obligatoire');
            } else if (cellule == '0') {
                $('#add-poste-erreur').text('Sélectionner une cellule dans la liste');
            }
        }
    });

    // Ajouter un Poste
    $(document).on('click', '#btn-add-poste', function (event) {
        event.preventDefault();
        $(document).find('#modal-poste-title').text('Ajouter un poste');
        $(document).find('#add-poste-id').val('');
        $(document).find('#poste-modal').modal();
        modalDraggable();
    });

    // Modifier un Poste
    $(document).on('click', '.js-edit-poste', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#poste-modal').modal();
        modalDraggable();
        $(document).find('#add-poste-id').val('');
        $(document).find('#add-poste-id').val(rowid);
        $(document).find('#add-poste-nom').val(row.find('.js-poste-nom').text());
        $(document).find('#add-poste-description').val(row.find('.js-poste-description').text());
        $(document).find('#add-poste-entite').val(row.find('.js-poste-id-entite').text());
        $(document).find('#add-poste-capacite').val(row.find('.js-poste-capacite').text());
        listeDepartement('#add-poste-entite', row.find('.js-poste-id-departement').text(), null, function() {
            $(document).find('#add-poste-departement').val(row.find('.js-poste-id-departement').text());
            listeService('#add-poste-departement', row.find('.js-poste-id-service').text(), null, function() {
                listeCellule('#add-poste-service', row.find('.js-poste-id-cellule').text(), null)
            });
        });

        $(document).find('#modal-poste-title').text('Modifier un poste');
    });

    // Supprimer un poste
    $(document).on('click', '.js-delete-poste', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_poste_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_poste'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?',
        });
    });

    //Liste des département d'une entité
    $(document).on('change', '#add-poste-entite', function (event) {
        listeDepartement(this, '0', event);
    });

    function listeDepartement(entite_selector, default_value, event, callback) {
        if (typeof event !== 'undefined' && event != null) {
            event.preventDefault();
            event.stopPropagation();
        }

        if ($(entite_selector).val() != '0') {
            var id = $(entite_selector).val();

            $.ajax({
                url: Routing.generate('parametre_liste_departement_entite', {'id': id}),
                type: 'POST',
                data: {
                    id: id
                },
                success: function (data) {
                    data = $.parseJSON(data);

                    var departement = $(document).find('#add-poste-departement');
                    departement.empty();
                    departement.append('<option value="0">Séléctionner le département</option>');

                    $.each(data, function(key, val) {
                        departement.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    departement.val(default_value);

                    var service = $(document).find('#add-poste-service');
                    service.empty()
                        .append('<option value="0">Séléctionner le service</option>')
                        .val('0');

                    if (typeof callback !== 'undefined' && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }
    }

    //Liste des service d'un département
    $(document).on('change', '#add-poste-departement', function (event) {
        listeService(this, '0', event);
    });

    function listeService(departement_selector, default_value, event, callback) {
        if (typeof event !== 'undefined' && event != null) {
            event.preventDefault();
            event.stopPropagation();
        }

        if ($(departement_selector).val() != '0') {
            var id = $(departement_selector).val();

            $.ajax({
                url: Routing.generate('parametre_liste_service_departement', {'id': id}),
                type: 'POST',
                data: {
                    id: id
                },
                success: function (data) {
                    data = $.parseJSON(data);

                    var service = $(document).find('#add-poste-service');
                    service.empty();
                    service.append('<option value="0">Séléctionner le service</option>');

                    $.each(data, function(key, val) {
                        service.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    service.val(default_value);

                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }
    }

    //Liste des cellules d'un service
    $(document).on('change', '#add-poste-service', function (event) {
        listeCellule(this, '0', event);
    });

    function listeCellule(service_selector, default_value, event) {
        if (typeof event !== 'undefined' && event != null) {
            event.preventDefault();
            event.stopPropagation();
        }

        if ($(service_selector).val() != '0') {
            var id = $(service_selector).val();

            $.ajax({
                url: Routing.generate('parametre_liste_cellule_service', {'id': id}),
                type: 'POST',
                data: {
                    id: id
                },
                success: function (data) {
                    data = $.parseJSON(data);

                    var cellule = $(document).find('#add-poste-cellule');
                    cellule.empty();
                    cellule.append('<option value="0">Séléctionner la cellule</option>');

                    $.each(data, function(key, val) {
                        cellule.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    cellule.val(default_value);
                }
            });
        }
    }
});
