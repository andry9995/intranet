$(function () {
    var lastsel;
    //Listes des unités de comptage
    $(document).on('click', '#btn-add-unite', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $('#unite-modal').modal();
        modalDraggable()


        //Liste unité dans jqGrid
        $('#js_unite_liste').jqGrid({
            url: Routing.generate('unite_comptage_liste'),
            datatype: 'json',
            loadonce: true,
            sortable: true,
            autowidth: true,
            height: 300,
            viewrecords: true,
            rownumbers: true,
            rowNum: 100,
            rowList: [100, 200, 500],
            pager: '#pager_liste_unite',
            caption: 'Liste des unités de comptage',
            hidegrid: false,
            editurl: Routing.generate('unite_comptage_edit'),
            colNames: ['Unité', 'Code', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
            colModel: [
                {name: 'unite', index: 'unite', editable: true, width: 430, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-unite-unite'},
                {name: 'code', index: 'code', editable: true, width: 300, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-unite-code'},
                {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-tache-action'}
            ],
            onSelectRow: function(id) {
                if(id){
                    $('#js_unite_liste').restoreRow(lastsel);
                    $('#js_unite_liste').editRow(id,true);
                    lastsel=id;
                }
            },
            beforeSelectRow: function(rowid, e) {
                var target = $(e.target);
                var cell_action = target.hasClass('js-unite-action');
                var item_action = (target.closest('td').children('.icon-action').length > 0);
                if (cell_action || item_action) {
                    return false;
                }
                return true;
            },
            ajaxRowOptions: { async: true }
        });
    });

    // Enregistrement modif Unité
    $(document).on('click', '.js-save-unite', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('#js_unite_liste').jqGrid('saveRow', lastsel);
    });

    //Enregister nouvelle unité
    $(document).on('click', '#btn-add-unite-save', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var code = $('#add-unite-code').val().trim();
        code = code.replace(/\s/g, '_');
        var unite = $('#add-unite-unite').val().trim();

        // TODO ADD UNITE COMPTAGE
        $.ajax({
            url: Routing.generate('unite_comptage_add'),
            type: 'POST',
            data: {
                code: code,
                unite: unite
            },
            success: function (data) {
                var response = $.parseJSON(data);
                if (response.erreur == true) {
                    $('#add-unite-erreur')
                        .removeClass('hidden')
                        .text(response.erreur_text);
                } else {
                    $('#add-unite-erreur').addClass('hidden');
                    $('#add-unite-code').val('');
                    $('#add-unite-unite').val('');

                    $("#js_unite_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                }
            }
        })
    });

    //Enregistrer modification unité
    $(document).on('click', '.js-save-unite', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $('#js_tache_liste').jqGrid('saveRow', lastsel);
    });

    // Supprimer une unité
    $(document).on('click', '.js-delete-unite', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_unite_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('unite_comptage_delete'),
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