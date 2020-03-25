/**
 * Created by TEFY on 10/11/2016.
 */
$(function() {
    var lastsel_domaine;
    var lastsel_categorie;
    var lastsel_activite;
    var lastsel_regime;
    var domaine_grid = $('#js_domaine_liste');
    var categorie_grid = $('#js_categorie_liste');
    var activite_grid = $('#js_activite_liste');
    var regime_grid = $('#js_regime_liste');

    //Liste Domaines Taches
    domaine_grid.jqGrid({
        url: Routing.generate('tache_domaine'),
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
        pager: '#pager_liste_domaine',
        caption: "DOMAINES DES TACHES",
        hidegrid: false,
        editurl: Routing.generate('tache_domaine_edit'),
        colNames: ['Domaine', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'domaine-nom', index: 'domaine-nom', editable: true,
                editoptions: {defaultValue: ''}, classes: 'js-domaine-nom'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false,
                editoptions: {defaultValue: '<i class="fa fa-save icon-action js-save-button js-save-domaine" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-domaine" title="Supprimer"></i>'},
                classes: 'js-domaine-action'}
        ],
        onSelectRow: function (id) {
            if (id) {
                domaine_grid.restoreRow(lastsel_domaine);
                domaine_grid.editRow(id, true);
                lastsel_domaine = id;
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-activite-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !(cell_action || item_action);
        },
        loadComplete: function() {
            if (domaine_grid.closest('.ui-jqgrid').find('#btn-add-domaine').length === 0) {
                domaine_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                    '<button id="btn-add-domaine" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
            }
        },
        ajaxRowOptions: {async: true}
    });

    // Enregistrement modif Domaine
    $(document).on('click', '.js-save-domaine', function (event) {
        event.preventDefault();
        event.stopPropagation();
        domaine_grid.jqGrid('saveRow', lastsel_domaine, {
            "aftersavefunc": function() {
                reloadGrid(domaine_grid, Routing.generate('tache_domaine'));
            }
        });
    });

    // Ajouter nouveau domaine
    $(document).on('click', '#btn-add-domaine', function(event) {
        event.preventDefault();
        domaine_grid.jqGrid('addRow', {
            rowID: "new_row",
            initData: {},
            position: "first",
            useDefValues: true,
            useFormatter: true,
            addRowParams: {extraparam:{}}
        });
        $("#" + "new_row", "#js_domaine_liste").effect("highlight", 20000);
    });

    // Supprimer un domaine
    $(document).on('click', '.js-delete-domaine', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        domaine_grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_domaine_remove'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    //Liste Regime Fiscal
    regime_grid.jqGrid({
        url: Routing.generate('tache_regime_fiscal'),
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
        pager: '#pager_liste_regime',
        caption: "Liste des regimes fiscaux",
        hidegrid: false,
        editurl: Routing.generate('tache_regime_fiscal_edit'),
        colNames: ['Libelle', 'Actif', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'regime-libelle', index: 'regime-libelle', editable: true, width: 100,
                editoptions: {defaultValue: ''}, classes: 'js-regime-libelle'},
            {name: 'regime-status', index: 'regime-status', align: 'center', editable: true, width: 80, formatter: 'checkbox',
                edittype: 'checkbox', editoptions: {defaultValue: "1", value: "1:0"}, classes: 'js-regime-status'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false,
                editoptions: {defaultValue: '<i class="fa fa-save icon-action js-save-button js-save-regime" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-regime" title="Supprimer"></i>'},
                classes: 'js-regime-action'}
        ],
        onSelectRow: function (id) {
            if (id) {
                regime_grid.restoreRow(lastsel_regime);
                regime_grid.editRow(id, true);
                lastsel_regime = id;
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-regime-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !(cell_action || item_action);
        },
        loadComplete: function() {
            if (regime_grid.closest('.ui-jqgrid').find('#btn-add-regime').length === 0) {
                regime_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                    '<button id="btn-add-regime" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
            }
        },
        ajaxRowOptions: {async: true}
    });

    // Enregistrement modif Regime Fiscal
    $(document).on('click', '.js-save-regime', function (event) {
        event.preventDefault();
        event.stopPropagation();
        regime_grid.jqGrid('saveRow', lastsel_regime, {
            "aftersavefunc": function() {
                reloadGrid(regime_grid, Routing.generate('tache_regime_fiscal_edit'));
            }
        });
    });

    // Ajouter nouveau Regime Fiscal
    $(document).on('click', '#btn-add-regime', function(event) {
        event.preventDefault();
        regime_grid.jqGrid('addRow', {
            rowID: "new_row",
            initData: {},
            position: "first",
            useDefValues: true,
            useFormatter: true,
            addRowParams: {extraparam:{}}
        });
        $("#" + "new_row", "#js_regime_liste").effect("highlight", 20000);
    });

    // Supprimer un Regime Fiscal
    $(document).on('click', '.js-delete-regime', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        regime_grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_regime_fiscal_remove'),
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
