$(function () {
    var lastsel;
    $('#js_entite_liste').jqGrid({
        url: Routing.generate('parametre_entite_liste'),
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
        pager: '#pager_liste_entite',
        caption: 'Liste des entités',
        hidegrid: false,
        editurl: Routing.generate('parametre_entite_edit'),
        colNames: ['Entité', 'Forme Juridique', 'Email', 'Téléphone', 'Adresse', 'Utilisateur', 'Date création', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'entite', index: 'entite', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-entite-nom'},
            {name: 'form_jur', index: 'form_jur', editable: true, width: 80, align: "center", sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select",formatter:'select', editoptions:{value:"SA:SA;SARL:SARL;SU:SU;EURL:EURL"},
                classes: 'js-entite-form-jur'},
            {name: 'email', index: 'email', editable: true, width: 80, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-entite-email'},
            {name: 'telephone', index: 'telephone', editable: true, width: 80, align: "center", classes: 'js-entite-telephone'},
            {name: 'adresse', index: 'adresse', editable: true, width: 200, align: "left", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-entite-adresse'},
            {name: 'utilisateur', index: 'utilisateur', width: 150, align: "center", sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-entite-utilisateur'},
            {
                name: 'date_creation', index: 'date_creation', width: 80, align: "center",
                sorttype: "date", formatter: "date", "datefmt": "d-m-Y", "formatoptions": {"newformat": "d-m-Y"},
                classes: 'js-entite-date-creation'
            },
            {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-entite-action'}
        ],
        onSelectRow: function(id) {
            if(id){
                $('#js_entite_liste').restoreRow(lastsel);
                $('#js_entite_liste').editRow(id,true);
                lastsel=id;
            }
        },
        beforeSelectRow: function(rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-entite-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            if (cell_action || item_action) {
                return false;
            }
            return true;
        }
    });

    // Enregistrement modif Entité
    $(document).on('click', '.js-save-entite', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('#js_entite_liste').jqGrid('saveRow', lastsel);
    });

    // Enregistrement modif/ajout Entité
    $(document).on('click', '#btn-add-entite-save', function (event) {
        event.preventDefault();
        $(document).find('#add-entite-erreur').addClass('hidden');
        var nom = $(document).find('#add-entite-nom').val().trim();
        var form_jur = $(document).find('#add-entite-form-juridique').val().trim();
        var adresse = $(document).find('#add-entite-adresse').val().trim();
        var email = $(document).find('#add-entite-email').val().trim();
        var telephone = $(document).find('#add-entite-telephone').val().trim();
        var id = $(document).find('#add-entite-id').val();
        if (nom != '') {
            $.ajax({
                url: Routing.generate('parametre_entite'),
                type: 'POST',
                data: {
                    id: id,
                    nom: nom,
                    form_jur: form_jur,
                    adresse: adresse,
                    email: email,
                    telephone: telephone
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur == true) {
                        $('#add-entite-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-entite-erreur').addClass('hidden');
                        $('#add-entite-nom').val('');
                        $('#add-entite-adresse').val('');
                        $('#add-entite-email').val('');
                        $('#add-entite-telephone').val('');
                        $('#entite-modal').modal('hide');

                        $("#js_entite_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        }
    });
    // Ajouter une entité
    $(document).on('click', '#btn-add-entite', function (event) {
        event.preventDefault();
        $(document).find('#modal-entite-title').text('Ajouter une entité');
        $(document).find('#add-entite-id').val('');
        $(document).find('#entite-modal').modal();
        modalDraggable();
    });

    // Modifier une entité
    $(document).on('click', '.js-edit-entite', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#add-entite-id').val('');
        $(document).find('#add-entite-id').val(rowid);
        $(document).find('#add-entite-nom').val(row.find('.js-entite-nom').text());
        $(document).find('#add-entite-form-juridique').val(row.find('.js-entite-form-jur').text());
        $(document).find('#add-entite-adresse').val(row.find('.js-entite-adresse').text());
        $(document).find('#add-entite-email').val(row.find('.js-entite-email').text());
        $(document).find('#add-entite-telephone').val(row.find('.js-entite-telephone').text());

        $(document).find('#modal-entite-title').text('Modifier une entité');
        $(document).find('#entite-modal').modal();
        modalDraggable();
    });


    // Supprimer une entité
    $(document).on('click', '.js-delete-entite', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_entite_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_entite'),
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