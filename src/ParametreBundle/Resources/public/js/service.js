/**
 * Created by TEFY on 02/05/2016.
 */

$(function () {
    var lastsel;
    $('#js_service_liste').jqGrid({
        url: Routing.generate('parametre_service_liste'),
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
        pager: '#pager_liste_service',
        caption: 'Liste des services',
        hidegrid: false,
        colNames: ['Id_Entite', 'Entité', 'Id_Departement', 'Département', 'Service', 'Description', 'Utilisateur', 'Date création', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'id-entite', index: 'id-entite', classes: 'js-service-id-entite', hidden: true},
            {name: 'entite', index: 'entite', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-service-entite'},
            {name: 'id-departement', index: 'id-departement', classes: 'js-service-id-departement', hidden: true},
            {name: 'departement', index: 'departement', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-service-departement'},
            {name: 'service', index: 'service', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-service-nom'},
            {name: 'description', index: 'description', width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-service-description'},
            {name: 'utilisateur', index: 'utilisateur', width: 150, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-service-utilisateur'},
            {
                name: 'date_creation', index: 'date_creation', width: 80, align: "center",
                sorttype: "date", formatter: "date", "datefmt": "d-m-Y", "formatoptions": {"newformat": "d-m-Y"},
                classes: 'js-service-date-creation'
            },
            {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-service-action'}
        ],
        onSelectRow: function(id) {
            if(id){
                $('#js_service_liste').restoreRow(lastsel);
                $('#js_service_liste').editRow(id,true);
                lastsel=id;
            }
        },
        beforeSelectRow: function(rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-service-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            if (cell_action || item_action) {
                return false;
            }
            return true;
        }
    });


    // Enregistrement modif/ajout Service
    $(document).on('click', '#btn-add-service-save', function (event) {
        event.preventDefault();
        $(document).find('#add-service-erreur').addClass('hidden');
        var nom = $(document).find('#add-service-nom').val().trim();
        var description = $(document).find('#add-service-description').val().trim();
        var departement = $(document).find('#add-service-departement').val().trim();
        var id = $(document).find('#add-service-id').val();

        //console.log(nom + '-' + description + '-' + entite + '-' + departement + '-' + id); return;
        if (nom != '' && departement != '0') {
            $.ajax({
                url: Routing.generate('parametre_service'),
                type: 'POST',
                data: {
                    id: id,
                    nom: nom,
                    description: description,
                    departement: departement
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur == true) {
                        $('#add-service-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-service-erreur').addClass('hidden');
                        $('#add-service-nom').val('');
                        $('#add-service-description').val('');
                        $('#service-modal').modal('hide');

                        $("#js_service_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        } else {
            $('#add-service-erreur').removeClass('hidden');
            if (nom == '') {
                $('#add-service-erreur').text('Le nom du service est obligatoire');
            } else if (departement == '0') {
                $('#add-service-erreur').text('Sélectionner un département dans la liste');
            }
        }
    });

    // Ajouter un Service
    $(document).on('click', '#btn-add-service', function (event) {
        event.preventDefault();
        $(document).find('#modal-service-title').text('Ajouter un service');
        $(document).find('#add-service-id').val('');
        $(document).find('#service-modal').modal();
        modalDraggable();
    });

    // Modifier un service
    $(document).on('click', '.js-edit-service', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#service-modal').modal();
        modalDraggable();
        $(document).find('#add-service-id').val('');
        $(document).find('#add-service-id').val(rowid);
        $(document).find('#add-service-nom').val(row.find('.js-service-nom').text());
        $(document).find('#add-service-description').val(row.find('.js-service-description').text());
        $(document).find('#add-service-entite').val(row.find('.js-service-id-entite').text());
        listeDepartement('#add-service-entite', row.find('.js-service-id-departement').text());
        $(document).find('#modal-service-title').text('Modifier un service');
    });

    // Supprimer un service
    $(document).on('click', '.js-delete-service', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_service_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_service'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    //Liste des départements d'une entité
    $(document).on('change', '#add-service-entite', function (event) {
        listeDepartement(this, '0', event);
    });

    function listeDepartement(entite_selector, default_value, event) {
        if (typeof event !== 'undefined') {
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

                    var departement = $(document).find('#add-service-departement');
                    departement.empty();
                    departement.append('<option value="0">Séléctionner le département</option>');

                    $.each(data, function(key, val) {
                        departement.append('<option value="' + val.id + '">' + val.nom + '</option>');
                    });

                    departement.val(default_value);
                }
            });
        }
    }
});
