/**
 * Created by SITRAKA on 04/10/2018.
 */
/**
 * Created by TEFY on 10/11/2016.
 */
$(function() {
    var lastsel_tache_action;
    var tache_action_grid = $('#js_tache_liste_action');

    //Liste Domaines Taches
    tache_action_grid.jqGrid({
        url: Routing.generate('tache_liste_action'),
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
        pager: '#pager_tache_liste_action',
        caption: "LISTE ACTIONS",
        hidegrid: false,
        editurl: Routing.generate('tache_liste_action_edit'),
        colNames: ['Action','Code', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'liste-action-nom', index: 'liste-action-nom', editable: true,
                editoptions: {defaultValue: ''}, classes: 'js-liste-action-nom'},
            {name: 'liste-action-code', index: 'liste-action-code', editable: true,
                editoptions: {defaultValue: ''}, classes: 'js-liste-action-code'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false,
                editoptions: {defaultValue: '<i class="fa fa-save icon-action js-save-button js-save-domaine" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-domaine" title="Supprimer"></i>'},
                classes: 'js-domaine-action'}
        ],
        onSelectRow: function (id) {
            if (id) {
                tache_action_grid.restoreRow(lastsel_tache_action);
                tache_action_grid.editRow(id, true);
                lastsel_tache_action = id;
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-activite-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !(cell_action || item_action);
        },
        loadComplete: function() {
            if (tache_action_grid.closest('.ui-jqgrid').find('#btn-add-domaine').length === 0) {
                tache_action_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                    '<button id="btn-add-liste-action" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
            }
        },
        ajaxRowOptions: {async: true}
    });


    // Enregistrement modif Domaine
    $(document).on('click', '.js-save-liste-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        tache_action_grid.jqGrid('saveRow', lastsel_tache_action, {
            "aftersavefunc": function() {
                reloadGrid(tache_action_grid, Routing.generate('tache_liste_action'));
            }
        });
    });

    // Ajouter nouveau domaine
    $(document).on('click', '#btn-add-liste-action', function(event) {
        event.preventDefault();
        tache_action_grid.jqGrid('addRow', {
            rowID: "new_row",
            initData: {},
            position: "first",
            useDefValues: true,
            useFormatter: true,
            addRowParams: {extraparam:{}}
        });
        $("#" + "new_row", "#js_tache_liste_action").effect("highlight", 20000);
    });

    // Supprimer un domaine
    $(document).on('click', '.js-delete-liste-action', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        tache_action_grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_liste_action_edit_remove'),
            top: 200,
            left: 400,
            width: 600,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Attention !!!\r\n\r\nLes taches Parametrées avec cette action serront supprimées\r\n\r\nVoulez-vous continuez ?'
        });
    });
});
