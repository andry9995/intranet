$(function() {
    var lastsel_legale,
        lastsel_legale_action,
        tache_legale_grid = $('#js_tache_legale_liste'),
        tache_legale_action_grid = $('#js_tache_libre_legale_action'),
        tache_legale_action_modal = $('#tache-legale-action-modal'),
        tache_legale_action_date_modal = $('#tache-legale-action-date-modal');


    /** TACHES LIBRES */
    tache_legale_grid.jqGrid({
        url: Routing.generate('tache_legale_liste'),
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
        pager: '#pager_liste_tache_legale',
        caption: 'Liste des tâches Légales',
        hidegrid: false,
        colNames: ['Tâche', 'Régimes Fiscaux', 'Formes Activités', 'Formes Juridiques', 'Date Clôture', 'Evénements<br>Déclencheurs', 'Périodicité<br>Déclaration', 'Actions<br>à Faire', '<span class="fa fa-bookmark-o" style="display:inline-block"/>'],
        colModel: [
            {name: 't-libre-nom', index: 't-libre-nom', editable: false, width: 200, classes: 'js-t-libre-nom'},
            {name: 't-libre-regime', index: 't-libre-regime', editable: false, width: 100, align: 'center', classes: 'js-t-libre-regime'},
            {name: 't-libre-activite', index: 't-libre-activite', editable: false, width: 100, align: 'center', classes: 'js-t-libre-active'},
            {name: 't-libre-entreprise', index: 't-libre-entreprise', editable: false, width: 100, align: 'center', classes: 'js-t-libre-entreprise'},
            {name: 't-libre-cloture', index: 't-libre-cloture', editable: false, width: 80, align: 'center', classes: 'js-t-libre-cloture'},
            {name: 't-libre-evenement', index: 't-libre-evenement', editable: false, width: 100, align: 'center', classes: 'js-t-libre-evenement'},
            {name: 't-libre-periode', index: 't-libre-periode', editable: false, width: 80, align: 'center', classes: 'js-t-libre-periode'},
            {name: 't-libre-action', index: 't-libre-action', editable: false, width: 60, align: 'center', classes: 'js-t-libre-action pointer'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false, classes: 'js-tache-action'}
        ],
        onSelectRow: function (id) {
            if (id) {
                tache_legale_grid.restoreRow(lastsel_legale);
                tache_legale_grid.editRow(id, true);
                lastsel_legale = id;
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-tache-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !(cell_action || item_action);

        },
        loadComplete: function() {
            if (tache_legale_grid.closest('.ui-jqgrid').find('#btn-add-tache-legale').length === 0) {
                tache_legale_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                    '<button id="btn-add-tache-legale" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
            }
        },
        ajaxRowOptions: {async: true}
    });


    /** POPUP AJOUT TACHE LEGALE */
    $(document).on('click', '#btn-add-tache-legale', function() {
        initTacheLegaleFields(false);
        $('#tache-legale-modal').modal('show');
        modalDraggable();
    });

    /** Enregistrer tâche légale */
    $(document).on('click', '#btn-add-tache-legale-save', function(event) {
        event.preventDefault();
        var url = Routing.generate('tache_legale_add');
        var id = $('#add-tache-legale-id').val().trim();
        if (id !== '') {
            url = Routing.generate('tache_legale_edit', { tache: id });
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                nom: $('#add-tache-legale-nom').val(),
                regime: $('#add-tache-legale-regime').val(),
                activite: $('#add-tache-legale-activite').val(),
                entreprise: $('#add-tache-legale-forme-juridiq').val(),
                cloture: $('#add-tache-legale-cloture').val(),
                evenement: $('#add-tache-legale-evenement').val(),
                periode: $('#add-tache-legale-periode').val()
            },
            success: function(data) {
                data = $.parseJSON(data);
                if (data.erreur == true) {
                    show_info('Erreur', data.erreur_text, 'error', 10000)
                } else {
                    reloadGrid(tache_legale_grid, Routing.generate('tache_legale_liste'));
                    $('#tache-legale-modal').modal('hide');
                    modalDraggable();
                }
            },
            error: function() {
                alert("Une erreur est survenue");
            }
        })
    });

    /** SUPPRIMER UNE TACHE LEGALE */
    $(document).on('click', '.js-delete-tache-legale', function(event) {
       event.preventDefault();
       event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');

        tache_legale_grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_legale_remove', { tache: rowid }),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    /** MODIFIER UNE TACHE LEGALE */
    $(document).on('click', '.js-edit-tache-legale', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var rowid = $(this).closest('tr').attr('id');
        initTacheLegaleFields(true, rowid);
    });

    /** Width Jqgrid dans tabs */
    $(document).on("click", ".jqgrid-tabs a", function () {
        tache_legale_grid.jqGrid("setGridWidth", tache_legale_grid.closest(".panel-body").width());
    });

    /** ACTIVER CHOOSEN ON MODAL SHOW */
    $('#tache-legale-modal').on('shown.bs.modal', function() {
        $('.chosen-select', this).chosen('destroy').chosen({width: '100%'});
    });


    /** ACTIONS A FAIRE POUR UNE TACHE LEGALE */
    $(document).on('click', '.js-t-libre-action', function() {
        var rowid = $(this).closest('tr').attr('id'),
            tache = $(this).closest('tr').find('.js-t-libre-nom').text();
        $('#add-tache-legale-action-id').val(rowid);
        $('#modal-tache-legale-action-nom').text('Tache: ' + tache);
        tache_legale_action_modal.modal('show');
        modalDraggable();
    });

    /** Width modal actions tache légale */
    tache_legale_action_modal.on('show.bs.modal', function() {

        var window_width = window.innerWidth,
            window_height = window.innerHeight;
        $('#tache-legale-action-modal')
            .find('.modal-dialog')
            .width(window_width)
            .css({'top': 0, "margin-top": 0});
    });

    /** Activer jqGrid actions tache légale */
    tache_legale_action_modal.on('shown.bs.modal', function() {
        tache_legale_action_grid.jqGrid('GridUnload');
        tache_legale_action_grid = $('#js_tache_libre_legale_action');
        var tache = $('#add-tache-legale-action-id').val();
        /** TACHES LEGALES ACTIONS */
        tache_legale_action_grid.jqGrid({
            url: Routing.generate('tache_legale_action_liste', {tache: tache}),
            datatype: 'json',
            loadonce: true,
            sortable: true,
            autowidth: true,
            height: 350,
            shrinkToFit: true,
            viewrecords: true,
            rownumbers: true,
            rowNum: 100,
            rowList: [50, 100, 200],
            pager: '#pager_liste_tache_legale_action',
            caption: '&nbsp;',
            hidegrid: false,
            colNames: ['A faire', 'Date', 'Nom cerfa', 'Commentaires 1', 'Commentaires 2', 'Commentaires 3', 'Commentaires 4', '<span class="fa fa-bookmark-o" style="display:inline-block"/>'],
            colModel: [
                {name: 't-legale-action-nom', index: 't-legale-action-nom', editable: true, edittype: 'select',
                    editoptions: { dataUrl: Routing.generate('tache_legale_liste_actions'), dataInit: function(elem) { $(elem).css('width', '100%'); }},
                    width: 200, classes: 'js-t-legale-action-nom'},
                {name: 't-legale-action-date', index: 't-legale-action-date', editable: true, width: 300, edittype: "textarea",
                    editoptions: { rows: 4, dataInit : function (elem) {$(elem).css('width', '100%')} },
                    classes: 'js-t-legale-action-date pointer'},
                {name: 't-legale-action-cerfa', index: 't-legale-action-cerfa', editable: true, width: 100, align: 'center', classes: 'js-t-legale-action-cerfa'},
                {name: 't-legale-action-com1', index: 't-legale-action-com1', editable: true, width: 200, edittype: "textarea",
                    editoptions: { rows: 4, dataInit : function (elem) {$(elem).css('width', '100%')} },
                    classes: 'js-t-legale-action-com1'},
                {name: 't-legale-action-com2', index: 't-legale-action-com2', editable: true, width: 200, edittype: "textarea",
                    editoptions: { rows: 4, dataInit : function (elem) {$(elem).css('width', '100%')} }, classes: 'js-t-legale-action-com2'},
                {name: 't-legale-action-com3', index: 't-legale-action-com3', editable: true, width: 200, edittype: "textarea",
                    editoptions: { rows: 4, dataInit : function (elem) {$(elem).css('width', '100%')} }, classes: 'js-t-legale-action-com3'},
                {name: 't-legale-action-com4', index: 't-legale-action-com4', editable: true, width: 200, edittype: "textarea",
                    editoptions: { rows: 4, dataInit : function (elem) {$(elem).css('width', '100%')} }, classes: 'js-t-legale-action-com4'},
                {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false,
                    editoptions: {defaultValue: '<i class="fa fa-save icon-action js-save-button js-save-tache-legale-action" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-legale-action" title="Supprimer"></i>'},
                    classes: 'js-tache-legale-action-action'}
            ],
            onSelectRow: function (id) {
                if (id) {
                    tache_legale_action_grid.restoreRow(lastsel_legale_action);
                    tache_legale_action_grid.editRow(id, true);
                    lastsel_legale_action = id;
                }
            },
            beforeSelectRow: function (rowid, e) {
                var target = $(e.target);
                var cell_action = target.hasClass('js-tache-legale-action-action');
                var item_action = (target.closest('td').children('.icon-action').length > 0);

                return !(cell_action || item_action);

            },
            loadComplete: function() {
                if (tache_legale_action_grid.closest('.ui-jqgrid').find('#btn-add-tache-legale-action').length === 0) {
                    tache_legale_action_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                        '<button id="btn-add-tache-legale-action" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
                }
            },
            ajaxRowOptions: {async: true}
        });
    });

    $(document).on('click', '#btn-add-tache-legale-action', function() {
        event.preventDefault();
        tache_legale_action_grid.jqGrid('addRow', {
            rowID: "new_row",
            initData: {'t-legale-action-nom': 'TEST1'},
            position: "first",
            useDefValues: true,
            useFormatter: true,
            addRowParams: {extraparam:{}}
        });
        $("#" + "new_row", tache_legale_action_grid).effect("highlight", 20000);
    });

    /** Enregistrer Actions Taches Légales */
    $(document).on('click', '.js-save-tache-legale-action', function() {
        event.preventDefault();
        event.stopPropagation();
        var tache = $('#add-tache-legale-action-id').val();

        tache_legale_action_grid.setGridParam({ editurl: Routing.generate('tache_legale_action_edit', {tache: tache}) });
        tache_legale_action_grid.jqGrid('saveRow', lastsel_legale_action, {
            aftersavefunc: function() {
                reloadGrid(tache_legale_action_grid, Routing.generate('tache_legale_action_liste', {tache: tache}));
            }
        });
    });


    /** SUPPRIMER UNE ACTION TACHE LEGALE */
    $(document).on('click', '.js-delete-tache-legale-action', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');

        tache_legale_action_grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_legale_action_remove', { tache_id: rowid }),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    function initTacheLegaleFields(edit, id) {
        if (typeof edit !== 'undefined' && edit === true) {
            $.ajax({
                url: Routing.generate('tache_legale_one', { tache: id }),
                type: 'GET',
                success: function(data) {
                    data = $.parseJSON(data);
                    console.log(data);
                    $('#tache-legale-modal').modal('show');
                    $('#add-tache-legale-id').val(id);
                    $('#add-tache-legale-nom').val(data.nom);
                    $('#add-tache-legale-regime').val(data.regimeFiscal).trigger('chosen:updated');
                    $('#add-tache-legale-activite').val(data.formeActivite).trigger('chosen:updated');
                    $('#add-tache-legale-forme-juridiq').val(data.formeJuridique).trigger('chosen:updated');
                    $('#add-tache-legale-cloture').val(data.dateCloture).trigger('chosen:updated');
                    $('#add-tache-legale-evenement').val(data.evenementDeclencheur);
                    $('#add-tache-legale-periode').val(data.periodeDeclaration);
                }
            })
        } else {
            $('#add-tache-legale-id').val('');
            $('#add-tache-legale-nom').val('');
            $('#add-tache-legale-regime').val('').trigger('chosen:updated');
            $('#add-tache-legale-activite').val('').trigger('chosen:updated');
            $('#add-tache-legale-forme-juridiq').val('').trigger('chosen:updated');
            $('#add-tache-legale-cloture').val('').trigger('chosen:updated');
            $('#add-tache-legale-evenement').val('');
            $('#add-tache-legale-periode').val('1');
        }
    }
});