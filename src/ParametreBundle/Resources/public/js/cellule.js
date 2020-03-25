/**
 * Created by TEFY on 02/05/2016.
 */

$(function () {
    $('#js_cellule_liste').jqGrid({
        url: Routing.generate('parametre_cellule_liste'),
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
        pager: '#pager_liste_cellule',
        caption: 'Liste des cellules',
        hidegrid: false,
        colNames: ['Id_Entite', 'Entité', 'Id_Departement', 'Département', 'Id_Service', 'Service', 'Id_CodeCellule', 'Cellule', 'Description', 'Utilisateur', 'Date création', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'id-entite', index: 'id-entite', classes: 'js-cellule-id-entite', hidden: true},
            {name: 'entite', index: 'entite', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-cellule-entite'},
            {name: 'id-departement', index: 'id-departement', classes: 'js-cellule-id-departement', hidden: true},
            {name: 'departement', index: 'departement', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-cellule-departement'},
            {name: 'id-service', index: 'id-service', classes: 'js-cellule-id-service', hidden: true},
            {name: 'service', index: 'service', width: 150, classes: 'js-cellule-service'},
            {name: 'id-code-cellule', index: 'id-code-cellule', width: 150, classes: 'js-cellule-id-code-cellule', hidden: true},
            {name: 'cellule', index: 'cellule', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-cellule-nom'},
            {name: 'description', index: 'description', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-cellule-description'},
            {name: 'utilisateur', index: 'utilisateur', width: 150, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-cellule-utilisateur'},
            {
                name: 'date_creation', index: 'date_creation', width: 80, align: "center",
                sorttype: "date", formatter: "date", "datefmt": "d-m-Y", "formatoptions": {"newformat": "d-m-Y"},
                classes: 'js-cellule-date-creation'
            },
            {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-cellule-action'}
        ]
    });


    // Enregistrement modif/ajout Cellule
    $(document).on('click', '#btn-add-cellule-save', function (event) {
        event.preventDefault();
        $(document).find('#add-cellule-erreur').addClass('hidden');
        var nom = $(document).find('#add-cellule-nom').val().trim();
        var code = $(document).find('#add-cellule-code').val().trim();
        var description = $(document).find('#add-cellule-description').val().trim();
        var service = $(document).find('#add-cellule-service').val().trim();
        var id = $(document).find('#add-cellule-id').val();

        if (nom != '' && service != '0' && code != '0') {
            $.ajax({
                url: Routing.generate('parametre_cellule'),
                type: 'POST',
                data: {
                    id: id,
                    nom: nom,
                    code: code,
                    description: description,
                    service: service
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur == true) {
                        $('#add-cellule-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-cellule-erreur').addClass('hidden');
                        $('#add-cellule-nom').val('');
                        $('#add-cellule-description').val('');
                        $('#cellule-modal').modal('hide');

                        $("#js_cellule_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        } else {
            $('#add-cellule-erreur').removeClass('hidden');
            if (nom == '') {
                $('#add-cellule-erreur').text('Le nom de la cellule est obligatoire');
            } else if (service == '0') {
                $('#add-cellule-erreur').text('Sélectionner un service dans la liste');
            } else if (code == '0') {
                $('#add-cellule-erreur').text('Sélectionner un code cellule dans la liste');
            }
        }
    });

    // Ajouter un Service
    $(document).on('click', '#btn-add-cellule', function (event) {
        event.preventDefault();
        $(document).find('#modal-cellule-title').text('Ajouter une cellule');
        $(document).find('#add-cellule-id').val('');
        $(document).find('#cellule-modal').modal();
        modalDraggable();
    });

    // Modifier une cellule
    $(document).on('click', '.js-edit-cellule', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#cellule-modal').modal();
        modalDraggable();
        $(document).find('#add-cellule-id').val('');
        $(document).find('#add-cellule-id').val(rowid);
        $(document).find('#add-cellule-nom').val(row.find('.js-cellule-nom').text());
        $(document).find('#add-cellule-description').val(row.find('.js-cellule-description').text())
        $(document).find('#add-cellule-code').val(row.find('.js-cellule-id-code-cellule').text());
        $(document).find('#add-cellule-entite').val(row.find('.js-cellule-id-entite').text());
        listeDepartement('#add-cellule-entite', row.find('.js-cellule-id-departement').text(), null, function() {
            $(document).find('#add-cellule-departement').val(row.find('.js-cellule-id-departement').text());
            listeService('#add-cellule-departement', row.find('.js-cellule-id-service').text());
        });

        $(document).find('#modal-cellule-title').text('Modifier une cellule');
    });

    // Supprimer une cellule
    $(document).on('click', '.js-delete-cellule', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_cellule_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_cellule'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    //Liste des département d'une entité
    $(document).on('change', '#add-cellule-entite', function (event) {
        listeDepartement(this, '0', event);
    });

    function listeDepartement(selector, default_value, event, callback) {
        if (typeof event !== 'undefined' && event != null) {
            event.preventDefault();
            event.stopPropagation();
        }

        if ($(selector).val() != '0') {
            var id = $(selector).val();

            $.ajax({
                url: Routing.generate('parametre_liste_departement_entite', {'id': id}),
                type: 'POST',
                data: {
                    id: id
                },
                success: function (data) {
                    data = $.parseJSON(data);

                    var departement = $(document).find('#add-cellule-departement');
                    departement.empty();
                    departement.append('<option value="0">Séléctionner le département</option>');

                    $.each(data, function(key, val) {
                        departement.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    departement.val(default_value);

                    var service = $(document).find('#add-cellule-service');
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
    $(document).on('change', '#add-cellule-departement', function (event) {
        listeService(this, '0', event);
    });

    function listeService(departement_selector, default_value, event) {
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

                    var service = $(document).find('#add-cellule-service');
                    service.empty();
                    service.append('<option value="0">Séléctionner le service</option>');

                    $.each(data, function(key, val) {
                        service.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    service.val(default_value);
                }
            });
        }
    }
});
