$(function () {
    var lastsel_libre;
    var domaine_grid = $('#js_domaine_liste');
    var tache_libre_grid = $('#js_tache_libre_liste');
    var categorie_grid = $('#js_categorie_liste');
    var activite_grid = $('#js_activite_liste');
    var regime_grid = $('#js_regime_liste');
    var tache_action_grid = $('#js_tache_liste_action');

    /** TACHES LIBRES */
    tache_libre_grid.jqGrid({
        url: Routing.generate('tache_libre_liste'),
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
        pager: '#pager_liste_tache_libre',
        caption: 'Liste des tâches Libres',
        hidegrid: false,
        colNames: ['Domaine_Id', 'Domaine', 'Tâche', 'Description', 'Jalon_Value', 'Jalon', 'Tâche Préc.', 'Tâche Suiv.', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'domaine-id', index: 'domaine-id', hidden: true, classes: 'js-tache-domaine-id'},
            { name: 'domaine', index: 'domaine', editable: true, width: 100, sorttype: function (cell) { return jqGridSortable(cell); },
                edittype: "select", editoptions: { dataUrl: Routing.generate('tache_domaine_liste', {json: 0}), dataInit: function (elem) { $(elem).width(100); }
            }, classes: 'js-tache-domaine' },
            { name: 'nom', index: 'nom', editable: true, width: 150, sorttype: function (cell) { return jqGridSortable(cell); }, classes: 'js-tache-nom'},
            { name: 'description', index: 'description', editable: true, width: 200, sorttype: function (cell) { return jqGridSortable(cell); },
                edittype: 'textarea', editoptions: {rows: '4', cols: '29'}, classes: 'js-tache-description'},
            {name: 'jalon-value', index: 'jalon-value', hidden: true, classes: 'js-tache-jalon-value'},
            { name: 'jalon', index: 'jalon', editable: true, edittype: "checkbox", editoptions: {value: "1:0"}, width: 80, align: "center", formatter: 'checkbox'},
            { name: 'precedent', index: 'precedent', editable: false, width: 150, align: "center", classes: 'js-tache-precedent pointer'},
            { name: 'suivant', index: 'suivant', editable: false, width: 150, align: "center", classes: 'js-tache-suivant pointer'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: "center", sortable: false, classes: 'js-tache-action'}
        ],
        onSelectRow: function (id) {
            if (id) {
                tache_libre_grid.restoreRow(lastsel_libre);
                tache_libre_grid.editRow(id, true);
                lastsel_libre = id;
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-tache-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            var tache_prec_suiv_action = (target.closest('td').children('.js-edit-tache-prec-suiv').length > 0);

            return !(cell_action || item_action || tache_prec_suiv_action);

        },
        loadComplete: function() {
            if (tache_libre_grid.closest('.ui-jqgrid').find('#btn-add-tache-libre').length === 0) {
                tache_libre_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px; margin-right: 20px;">' +
                    '<button id="btn-add-tache-libre" class="btn btn-outline btn-primary btn-xs">Ajouter</button></div>');
            }
        },
        ajaxRowOptions: {async: true}
    });

    // Enregistrement modif Tâche Libre
    $(document).on('click', '.js-save-tache', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var rowid = $(this).closest('tr').attr('id');
        tache_libre_grid.setGridParam({ editurl: Routing.generate('tache_libre_edit', {tache: rowid}) });
        tache_libre_grid.jqGrid('saveRow', lastsel_libre, {
            aftersavefunc: function() {
                reloadGrid(tache_libre_grid, Routing.generate('tache_libre_liste'));
            }
        });
    });

    // Enregistrement ajout Tâche Libre
    $(document).on('click', '#btn-add-tache-save', function (event) {
        event.preventDefault();
        $(document).find('#add-tache-erreur').addClass('hidden');
        var tache_domaine = $('#add-tache-domaine');
        var domaine = tache_domaine.val();
        var tache_nom = $('#add-tache-nom');
        var nom = tache_nom.val().trim();
        var tache_description = $('#add-tache-description');
        var description = tache_description.val().trim();
        description = description.replace(/<br\s*\/?>/mg, "\n");
        var tache_jalon = $('#add-tache-jalon');
        var jalon = tache_jalon.prop('checked') === true ? 1 : 0;
        var erreur_list = validateFormTacheLibre();
        var tache_erreur = $('#add-tache-erreur');
        if (erreur_list.length === 0) {
            tache_erreur
                .addClass('hidden')
                .empty();
            $.ajax({
                url: Routing.generate('tache_libre_add'),
                type: 'POST',
                data: {
                    domaine: domaine,
                    nom: nom,
                    description: description,
                    jalon: jalon
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur === true) {
                        tache_erreur
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        tache_erreur.addClass('hidden');
                        tache_domaine.val('0');
                        tache_nom.val('');
                        tache_description.val('');
                        tache_jalon
                            .prop('checked', false)
                            .closest('div').removeClass('checked');
                        $('#tache-modal').modal('hide');

                        tache_libre_grid.setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');
                    }
                }
            });
        } else {
            tache_erreur
                .removeClass('hidden')
                .empty()
                .append('<ul></ul>');
            erreur_list.map(function(erreur) {
                tache_erreur.find('ul').append('<li>' + erreur + '</li>');
            });
        }
    });

    // Ajouter une tache
    $(document).on('click', '#btn-add-tache-libre', function (event) {
        event.preventDefault();
        clearFieldsForAddTache();
        var tache_modal = $('#tache-modal');
        $(document).find('#modal-tache-title')
            .text('Ajouter une tâche');
        tache_modal
            .find('.has-error')
            .removeClass('has-error');
        $('#add-tache-error').addClass('hidden')
            .empty();
        tache_modal.modal();

        modalDraggable();
    });


    /** SURRPIMER TACHE LIBRE */
    $(document).on('click', '.js-delete-tache', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        var jq_table_id = $(this).closest('.js-jqgrid-table').attr('id');

        $('#' + jq_table_id).jqGrid('delGridRow', rowid, {
            url: Routing.generate('tache_libre_remove', { tache: rowid }),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    /** Ajouter/Modifier Tâche Précédente - Suivante */
    $(document).on('click', '.js-edit-tache-prec-suiv', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('#tache-prec-item').empty();
        $('#tache-suiv-item').empty();

        var rowid = $(this).closest('tr').attr('id');
        var tache = $(this).closest('tr').find('.js-tache-nom').text();
        $('#tache-principale-nom')
            .text(tache)
            .attr('data-tache', rowid);
        $('#tache-prev-suiv').modal('show');
        modalDraggable();

        $.ajax({
            url: Routing.generate('tache_liste_simple', {json: 1}),
            type: 'POST',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);

                var option = '<option value=""></option>';
                $('#select-tache-prec')
                    .empty()
                    .append(option);
                $('#select-tache-suiv')
                    .empty()
                    .append(option);

                $.each(data, function (index, item) {
                    option = '<option value="' + item.id + '">' + item.nom + '</option>';
                    $('#select-tache-prec').append(option);
                    $('#select-tache-suiv').append(option);
                });
            }
        });

        $.ajax({
            url: Routing.generate('tache_liste_prec_suiv', {principale: rowid}),
            type: 'POST',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);
                // console.log(data);
                var tache_prec = data.tache_prec;
                var tache_suiv = data.tache_suiv;

                // Taches précédentes
                $.each(tache_prec, function (index, item) {
                    var prec_item = '<li data-item-id="' + item.id + '" class="success-element tache-item">';
                    prec_item += '<div>';
                    prec_item += '<span class="badge badge-info tache-item-order">' + (index + 1) + ' </span>';
                    prec_item += '<span class="tache-item-nom">' + item.tachePrecedente.nom + '</span>';
                    prec_item += '<i title="Supprimer" class="fa fa-close delete-prec-suiv-item pull-right"></i>';
                    prec_item += '</div>';
                    prec_item += '</li>';
                    $('#tache-prec-item').append(prec_item);
                });

                // Taches suivantes
                $.each(tache_suiv, function (index, item) {
                    var suiv_item = '<li data-item-id="' + item.id + '" class="success-element tache-item">';
                    suiv_item += '<div>';
                    suiv_item += '<span class="badge badge-info tache-item-order">' + (index + 1) + ' </span>';
                    suiv_item += '<span class="tache-item-nom">' + item.tacheSuivante.nom + '</span>';
                    suiv_item += '<i title="Supprimer" class="fa fa-close delete-prec-suiv-item pull-right"></i>';
                    suiv_item += '</div>';
                    suiv_item += '</li>';
                    $('#tache-suiv-item').append(suiv_item);
                });
            }
        });
    });

    $(document).find('.sortable-list').sortable({
        connectWith: '.connectList',
        update: function (event, ui) {
            var container = $(this);
            var taches = [];
            var type = 0; // Tache précédente
            if (container.attr('id') === 'tache-suiv-item')
            {
                type = 1; // Tache suivante
            }
            container.find('.tache-item').each(function (index, item) {
                $(item).find('.tache-item-order').text(index + 1);
                var id = $(item).attr('data-item-id');
                taches.push(id);
            });

            $.ajax({
                url: Routing.generate('tache_reorder_prec_suiv', { type: type }),
                type: 'POST',
                data: {
                    taches: taches
                },
                success: function() {

                }
            });

        }
    }).disableSelection();

    /** Ajouter une tache précédente */
    $(document).on('click', '#btn-add-tache-prec', function () {
        var select_tache_prec = $('#select-tache-prec');
        if (select_tache_prec.val() !== "") {
            var item_number = $('#tache-prec-item').children('li').length + 1;
            var item_label = select_tache_prec.find('option:selected').text();
            var item_id = select_tache_prec.val();
            var tache_principale = $('#tache-principale-nom').attr('data-tache');

            $.ajax({
                url: Routing.generate('tache_add_precedente', {principale: tache_principale, precedente: item_id}),
                type: 'POST',
                data: {},
                success: function (data) {
                    data = $.parseJSON(data);

                    if (data.erreur === false) {
                        var item = '<li data-item-id="' + data.id +'" class="success-element tache-item">';
                        item += '<div>';
                        item += '<span class="badge badge-info tache-item-order">' + item_number + '</span>';
                        item += ' <span class="tache-item-nom">' + item_label + '</span>';
                        item += '</div>';
                        item += '</li>';

                        $('#tache-prec-item').append(item);
                    } else {
                        show_info('Erreur', data.erreur_text, 'error');
                    }
                }
            });
        }
    });

    /* Ajouter une tache suivante */
    $(document).on('click', '#btn-add-tache-suiv', function () {
        if ($('#select-tache-suiv').val() !== "") {

            var item_number = $('#tache-suiv-item').children('li').length + 1;
            var item_label = $('#select-tache-suiv option:selected').text();
            var item_id = $('#select-tache-suiv').val();
            var tache_principale = $('#tache-principale-nom').attr('data-tache');

            $.ajax({
                url: Routing.generate('tache_add_suivante', {principale: tache_principale, suivante: item_id}),
                type: 'POST',
                data: {},
                success: function (data) {
                    data = $.parseJSON(data);

                    if (data.erreur === false) {
                        var item = '<li data-item-id="' + data.id +'" class="success-element tache-item">';
                        item += '<div>';
                        item += '<span class="badge badge-info tache-item-order">' + item_number + '</span>';
                        item += ' <span class="tache-item-nom">' + item_label + '</span>';
                        item += '</div>';
                        item += '</li>';

                        $('#tache-suiv-item').append(item);
                    } else {
                        show_info('Erreur', data.erreur_text, 'error');
                    }
                }
            });
        }
    });

    /** Supprimer une tache Suivante/Précédente */
    $(document).on('click', '.delete-prec-suiv-item', function () {
        var button = $(this);
        var container = $(this).closest('.tache-prec-suiv-container');

        if (container.length > 0) {
            var type = -1;
            if (container.attr('id') ==='tache-prec-container') {
                type = 0;
            } else if (container.attr('id') === 'tache-suiv-container') {
                type = 1;
            }
            var tache_id = button.closest('.tache-item').attr('data-item-id');

            if (type !== -1 && typeof tache_id !== 'undefined') {
                $.ajax({
                    url: Routing.generate('tache_remove_prec_suiv', {id: tache_id, type: type}),
                    type: 'POST',
                    data: {},
                    success: function (data) {
                        data = $.parseJSON(data);

                        if (data.erreur === false) {
                            button.closest('li.tache-item').remove();
                        } else {
                            show_info('Erreur', data.erreur_text, 'error');
                        }
                    }
                });
            }
        }
    });

    /** Width Jqgrid dans tabs */
    $(document).on("click", ".jqgrid-tabs a", function () {
        domaine_grid.jqGrid("setGridWidth", domaine_grid.closest(".panel-body").width());
        tache_libre_grid.jqGrid("setGridWidth", tache_libre_grid.closest(".panel-body").width());
        categorie_grid.jqGrid("setGridWidth", categorie_grid.closest(".panel-body").width());
        activite_grid.jqGrid("setGridWidth", activite_grid.closest(".panel-body").width());
        regime_grid.jqGrid("setGridWidth", regime_grid.closest(".panel-body").width());
        tache_action_grid.jqGrid("setGridWidth", tache_action_grid.closest(".panel-body").width());
    });

    function clearFieldsForAddTache() {
        $(document).find('#add-tache-id').val('');
        $(document).find('#add-tache-domaine').val('');
        $(document).find('#add-tache-nom').val('');
        $(document).find('#add-tache-description').val('');
        $(document).find('#add-tache-jalon').iCheck('uncheck');
    }
});