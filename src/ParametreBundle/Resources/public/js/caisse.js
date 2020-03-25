$(function () {

    var window_height = window.innerHeight;
    $('#liste-container').height(window_height - 140);

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader'),
        lastsel_pcc, lastsel_tva, lastsel_type, lastsel_resultat, lastsel_tva_caisse,
        pcc_grid = $('#table-pcc-list'),
        tva_grid = $('#table-tva-list'),
        type_grid = $('#table-type-list'),
        resultat_grid = $('#table-resultat-list'),
        tva_caisse_grid = $('#table-tva-caisse-list');

    $(document).bind("ajaxSend", function(){
        loader_selector.show();
    }).bind("ajaxComplete", function(){
        loader_selector.hide();
    });

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    dossier_selector.val('').trigger('chosen:updated');

    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        siteParClientMulti(client_selector, site_selector, loader_selector, function() {
            dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, function() {
                loader_selector.hide();
            })
        });
    });

    pcc_grid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: window_height - 320,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        caption: 'PCC',
        colNames: [
            'Compte', 'Type', 'Nature', 'Action'
        ],
        colModel: [
            {
                name: 'db-pcc',
                index: 'db-pcc',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js-db-pcc'
            },
            {
                name: 'db-nature',
                index: 'db-nature',
                align: 'center',
                editable: true,
                sortable: true,
                width: 200,
                classes: 'js-db-nature',
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('parametre_caisse_nature'),
                    dataInit: function (elem) {
                        $(elem).width(180);
                    }
                }
            },
            {
                name: 'db-type',
                index: 'db-type',
                align: 'center',
                editable: true,
                sortable: true,
                width: 200,
                classes: 'js-db-type',
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('parametre_caisse_type'),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossier').val()
                        }
                    },
                    dataInit: function (elem) {
                        $(elem).width(180);
                    }
                }
            },
            {
                name: 'db-action',
                index: 'db-action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                fixed: true,
                classes: 'js-db-action',
                editoptions: {defaultValue: '<i class="fa fa-save icon-action db-action" title="Enregistrer"></i>'}
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_pcc) {
                pcc_grid.restoreRow(lastsel_pcc);
                lastsel_pcc = id;
            }
            pcc_grid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {

            var target = $(e.target);
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            return !item_action;
        }
    });


    type_grid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: window_height - 320,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        caption: 'Nature Caisse',
        colNames: [
            'Libelle', 'Code', 'Action'
        ],
        colModel: [
            {
                name: 'db-type-lib',
                index: 'db-type-lib',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                classes: 'db-type-lib'
            },
            {
                name: 'db-type-code',
                index: 'db-type-code',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                classes: 'db-type-code'
            },

            {
                name: 'db-type-action',
                index: 'db-type-action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                fixed: true,
                classes: 'js-db-tva-action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action js-db-type-action" title="Enregistrer"></i>'}
            }

        ],

        onSelectRow: function (id) {
            if (id && id !== lastsel_type) {
                type_grid.restoreRow(lastsel_type);
                lastsel_type = id;
            }
            type_grid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function () {
            if ($('#btn-add-type').length === 0) {
                type_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-type" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }
        }
    });

    resultat_grid.jqGrid({

        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: window_height - 320,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        caption: 'Résultat',
        colNames: [
            'Nature', 'Pcc', 'Action'
        ],
        colModel: [

            {
                name: 'db-resultat-nature',
                index: 'db-resultat-nature',
                align: 'center',
                editable: false,
                sortable: true,
                width: 200,
                classes: 'js-db-resultat-nature'
            },
            {
                name: 'db-resultat-pcc',
                index: 'db-resultat-pcc',
                align: 'center',
                editable: true,
                sortable: true,
                width: 200,
                classes: 'js-resultat-pcc',
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('parametre_caisse_pcc_resultat'),
                    postData: function (rowid, value, cmName) {
                        return {
                            caissenatureid: rowid,
                            dossierid: $('#dossier').val()
                        }
                    },
                    dataInit: function (elem) {
                        $(elem).width(180);
                    }
                }
            },
            {
                name: 'db-resultat-action',
                index: 'db-resultat-action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                fixed: true,
                classes: 'js-db-resultat-action',
                editoptions: {defaultValue: '<i class="fa fa-save icon-action db-resultat-action" title="Enregistrer"></i>'}
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_resultat) {
                resultat_grid.restoreRow(lastsel_resultat);
                lastsel_resultat = id;
            }
            resultat_grid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {

            var target = $(e.target);
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            return !item_action;
        }

    });

    initTvaGrid(tva_grid, 0);
    initTvaGrid(tva_caisse_grid, 1);

    $('.btn-save-contre-partie').on('click', function(){

        var typecaisse = 0;
        var pcc = $('#pcc-contrepartie').val();
        if($(this).attr('id') === 'btn-save-contre-partie-caisse'){
            typecaisse = 1;
            pcc = $('#pcc-contrepartie-caisse').val();
        }

        var url = Routing.generate('parametre_caisse_contre_partie_edit', {typecaisse: typecaisse});
        $.ajax({
            url: url,
            data: { dossierid: $('#dossier').val(), pcc: pcc},
            type: 'POST',
            success: function (data) {
                show_info('', data['message'], data['type']);
            }
        })
    });

    $('#btn-show-pcc').on('click', function () {
        getListe();
    });

    $(document).on('click', '.js-db-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        pcc_grid.jqGrid('saveRow', lastsel_pcc, {
            "aftersavefunc": function(rowid, response) {
                if(response.responseJSON){
                    var jsonResponse = response.responseJSON;
                    if(jsonResponse['type'] === 'warning'){
                        var tr = $('#'+rowid);
                        tr.find('.js-db-nature').html('');
                        tr.find('.js-db-type').html('');
                        show_info('Attention', 'Condition déjà existant pour un autre pcc', jsonResponse['type']);
                    }
                }
            }
        });
    });


    $(document).on('click', '#btn-add-type', function(event){
        event.preventDefault();
        event.stopPropagation();

        if(canAddRow(type_grid)) {
            event.preventDefault();
            type_grid.jqGrid('addRow', {
                rowID: "new_row",
                initData: {},
                position: "first",
                useDefValues: true,
                useFormatter: true,
                addRowParams: {}
            });
            $("#" + "new_row", "#table-type-list").effect("highlight", 20000);
        }

    });

    $(document).on('click', '.js-db-type-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        type_grid.jqGrid('saveRow', lastsel_type, {
            "aftersavefunc": function(rowid, response) {
                var dossier_id = $('#dossier').val();

                var urlpcc = Routing.generate('parametre_caisse_tdpcc', {dossierid: dossier_id});
                var editurlpcc = Routing.generate('parametre_caisse_tdpcc_edit');

                pcc_grid.jqGrid('setGridParam', {url: urlpcc, editurl: editurlpcc, datatype: 'json'})
                    .trigger('reloadGrid', {fromServer: true, page: 1});

            }
        });
    });

    $(document).on('click', '.js-db-resultat-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        resultat_grid.jqGrid('saveRow', lastsel_resultat, {
            "aftersavefunc": function(rowid, response) {
            }
        });
    });


    $(document).on('click', '.js-db-tva-caisse-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        tva_caisse_grid.jqGrid('saveRow', lastsel_tva_caisse, {
            "aftersavefunc": function(rowid, response) {
            }
        });
    });

    $(document).on('click', '.js-db-tva-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        tva_grid.jqGrid('saveRow', lastsel_tva, {
            "aftersavefunc": function(rowid, response) {
            }
        });
    });

    function getListe() {

        var dossier_id = $('#dossier').val();

        var urlpcc = Routing.generate('parametre_caisse_tdpcc', {dossierid: dossier_id});
        var editurlpcc = Routing.generate('parametre_caisse_tdpcc_edit');

        pcc_grid.jqGrid('setGridParam', {url: urlpcc, editurl: editurlpcc, datatype: 'json'})
            .trigger('reloadGrid', {fromServer: true, page: 1});

        var urltype = Routing.generate('parametre_caisse_tdtype', {dossierid: dossier_id});
        var editurltype = Routing.generate('parametre_caisse_tdtype_edit', {dossierid: dossier_id});

        type_grid.jqGrid('setGridParam', {url: urltype, editurl: editurltype, datatype: 'json'})
            .trigger('reloadGrid', {fromServer: true, page: 1});

        //Chargement contrepartie vente
        var urlcontrepartie = Routing.generate('parametre_caisse_contre_partie', {typecaisse: 0, dossierid: dossier_id});
        var contrepartie = $('#pcc-contrepartie');
        contrepartie.html('<option value="-1"></option>');
        $.ajax({
            url:  urlcontrepartie,
            type: 'GET',
            datatype: 'html',
            success: function(data){
                contrepartie.append(data);
            }
        });


        //Chargement contrepartie caisse
        var urlcontrepartiecaisse = Routing.generate('parametre_caisse_contre_partie', {typecaisse: 1 ,dossierid: dossier_id});
        var contrepartiecaisse = $('#pcc-contrepartie-caisse');
        contrepartiecaisse.html('<option value="-1"></option>');
        $.ajax({
            url:  urlcontrepartiecaisse,
            type: 'GET',
            datatype: 'html',
            success: function(data){
                contrepartiecaisse.append(data);
            }
        });


        var urlresultat = Routing.generate('parametre_caisse_tdresultat', {dossierid: dossier_id});
        var editurlresultat = Routing.generate('parametre_caisse_tdresultat_edit', {dossierid: dossier_id});

        resultat_grid.jqGrid('setGridParam', {url: urlresultat, editurl: editurlresultat, datatype: 'json'})
            .trigger('reloadGrid', {fromServer: true, page: 1});


        var urltva = Routing.generate('parametre_caisse_tdtvacaisse', {typecaisse: 0, dossierid: dossier_id});
        var editurltva = Routing.generate('parametre_caisse_tdtvacaisse_edit', {typecaisse: 0, dossierid: dossier_id});

        tva_grid.jqGrid('setGridParam', {url: urltva, editurl: editurltva, datatype: 'json'})
            .trigger('reloadGrid', {fromServer: true, page: 1});

        var urltvacaisse = Routing.generate('parametre_caisse_tdtvacaisse', {typecaisse: 1, dossierid: dossier_id});
        var editurltvacaisse= Routing.generate('parametre_caisse_tdtvacaisse_edit', {typecaisse: 1, dossierid: dossier_id});

        tva_caisse_grid.jqGrid('setGridParam', {url: urltvacaisse, editurl: editurltvacaisse, datatype: 'json'})
            .trigger('reloadGrid', {fromServer: true, page: 1});

    }

    function canAddRow(jqGrid) {
        var canAdd = true;
        var rows = jqGrid.find('tr');

        rows.each(function () {
            if ($(this).attr('id') === 'new_row') {
                canAdd = false;
            }
        });
        return canAdd;
    }

    function initTvaGrid(grid, typecaisse){

        var  caption = 'TVA Caisse';
        var i = '<i class="fa fa-save icon-action db-tva-caisse-action" title="Enregistrer"></i>';
        var c = 'js-db-tva-caisse-action';
        var n = 'db-tva-caisse-action';

        if(parseInt(typecaisse) === 0){
            caption = 'TVA Vente Comptoir';
            i = '<i class="fa fa-save icon-action db-tva-action" title="Enregistrer"></i>';
            c = 'js-db-tva-action';
            n = 'db-tva-action';
        }

        grid.jqGrid({

            datatype: 'json',
            loadonce: true,
            sortable: true,
            height: window_height - 320,
            shrinkToFit: true,
            viewrecords: true,
            hidegrid: false,
            caption: caption,
            colNames: [
                'Taux', 'Pcc', 'Action'
            ],
            colModel: [

                {
                    name: 'db-taux-caisse',
                    index: 'db-taux-caisse',
                    align: 'center',
                    editable: false,
                    sortable: true,
                    width: 100,
                    classes: 'js-db-taux-caisse'
                },
                {
                    name: 'db-tva-caisse-pcc',
                    index: 'db-tva-caisse-pcc',
                    align: 'center',
                    editable: true,
                    sortable: true,
                    width: 140,
                    classes: 'js-tva-caisse-pcc',
                    edittype: 'select',
                    editoptions: {
                        dataUrl: Routing.generate('parametre_caisse_pcc_tva', {typecaisse: typecaisse}),
                        postData: function (rowid, value, cmName) {
                            return {
                                dossierid: $('#dossier').val()
                            }
                        },
                        dataInit: function (elem) {
                            $(elem).width(110);
                        }
                    }
                },
                {
                    name: n,
                    index: n,
                    align: 'center',
                    editable: false,
                    sortable: true,
                    width: 100,
                    fixed: true,
                    classes: c,
                    editoptions: {defaultValue: i}
                }

            ],
            onSelectRow: function (id) {
                if(parseInt(parseInt(typecaisse) === 1)) {
                    if (id && id !== lastsel_tva_caisse) {
                        grid.restoreRow(lastsel_tva_caisse);
                        lastsel_tva_caisse = id;
                    }
                    grid.editRow(id, false);
                }
                else{
                    if (id && id !== lastsel_tva) {
                        grid.restoreRow(lastsel_tva);
                        lastsel_tva = id;
                    }
                    grid.editRow(id, false);
                }
            },

            beforeSelectRow: function (rowid, e) {
                var target = $(e.target);
                var item_action = (target.closest('td').children('.icon-action').length > 0);
                return !item_action;
            }

        });
    }

});