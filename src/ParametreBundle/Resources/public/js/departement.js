/**
 * Created by TEFY on 02/05/2016.
 */

$(function () {
    var lastsel;
    $('#js_departement_liste').jqGrid({
        url: Routing.generate('parametre_departement_liste'),
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
        pager: '#pager_liste_departement',
        caption: 'Liste des départements',
        hidegrid: false,
        editurl: Routing.generate('parametre_departement_edit'),
        colNames: ['Id_Entite', 'Entité', 'Département', 'Utilisateur', 'Date création', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'id-entite', index: 'id-entite', classes: 'js-departement-id-entite', hidden: true},
            {name: 'entite', index: 'entite', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { dataUrl: Routing.generate('paremetre_entite_liste_simple', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(150);
                }}, classes: 'js-departement-entite'},
            {name: 'departement', index: 'departement', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-departement-nom'},
            {name: 'utilisateur', index: 'utilisateur', width: 150, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-departement-utilisateur'},
            {
                name: 'date_creation', index: 'date_creation', width: 80, align: "center",
                sorttype: "date", formatter: "date", "datefmt": "d-m-Y", "formatoptions": {"newformat": "d-m-Y"},
                classes: 'js-departement-date-creation'
            },
            {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-departement-action'}
        ],
        onSelectRow: function(id) {
            if(id){
                $('#js_departement_liste').restoreRow(lastsel);
                $('#js_departement_liste').editRow(id,true);
                lastsel=id;
            }
        },
        beforeSelectRow: function(rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-departement-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            if (cell_action || item_action) {
                return false;
            }
            return true;
        }
    });


    // Enregistrement modif Departement
    $(document).on('click', '.js-save-departement', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('#js_departement_liste').jqGrid('saveRow', lastsel);
    });

    // Enregistrement ajout Département
    $(document).on('click', '#btn-add-departement-save', function (event) {
        event.preventDefault();
        $(document).find('#add-departement-erreur').addClass('hidden');
        var nom = $(document).find('#add-departement-nom').val().trim();
        var entite = $(document).find('#add-departement-entite').val().trim();
        var id = $(document).find('#add-departement-id').val();
        if (nom != '') {
            $.ajax({
                url: Routing.generate('parametre_departement'),
                type: 'POST',
                data: {
                    id: id,
                    nom: nom,
                    entite: entite
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur == true) {
                        $('#add-departement-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-departement-erreur').addClass('hidden');
                        $('#add-departement-nom').val('');
                        $('#departement-modal').modal('hide');

                        $("#js_departement_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        }
    });

    // Ajouter un département
    $(document).on('click', '#btn-add-departement', function (event) {
        event.preventDefault();
        $(document).find('#modal-departement-title').text('Ajouter un département');
        $(document).find('#add-departement-id').val('');
        $(document).find('#add-departement-nom').val('');
        $(document).find('#add-departement-entite').val('0');
        $(document).find('#departement-modal').modal();
        modalDraggable();
    });

    // Modifier un département
    $(document).on('click', '.js-edit-departement', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#add-departement-id').val('');
        $(document).find('#add-departement-id').val(rowid);
        $(document).find('#add-departement-nom').val(row.find('.js-departement-nom').text());
        $(document).find('#add-departement-entite').val(row.find('.js-departement-id-entite').text());
        $(document).find('#modal-departement-title').text('Modifier un département');
        $(document).find('#departement-modal').modal();
        modalDraggable();
    });

    // Supprimer un département
    $(document).on('click', '.js-delete-departement', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_departement_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_departement'),
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
