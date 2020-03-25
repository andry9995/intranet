/**
 * Stocks Images
 */

$(function() {

    /*
     * Groupe options
     */
    $('#groupe-stocks').chosen({
        no_results_text: "Aucun groupe trouvé:",
        search_contains: true,
        width: '100%'
    });

    /*
     * Dossiers options
     */
    $('.chosen-select-dossier-stocks').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    /*
     * Clients options
     */
    $('.chosen-select-client-stocks').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });


    /*
     * Changement groupe
     */
    $(document).on('change', '#groupe-stocks', function(event) {

        event.preventDefault();
        event.stopPropagation();

        var url = Routing.generate('revision_clients_by_responsable', {
            responsable: $(this).val(),
        });

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            async: true,
            success: function(clients) {

                instance_or_clear_grid()

                $("#dossier-stocks option").remove();
                $("#client-stocks option").remove();
                $("#client-stocks").append('<option value="0">Tous</option>');

                clients.sort().forEach(function(c) {
                    $("#client-stocks").append('<option value="' + c.id + '">' + c.nom + '</option>');
                });

                $("#client-stocks").val('').trigger('chosen:updated');

            }
        });

    });

    /*
     * Changement client
     */
    $(document).on('change', '#client-stocks', function(event) {
        event.preventDefault();
        event.stopPropagation();
        instance_or_clear_grid();
        url = Routing.generate('reception_doublon_dossier');
        var idata = {};
        idata['client'] = $(this).val();
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {
                "idata": JSON.stringify(idata)
            },
            async: true,
            success: function(data) {

                $("#dossier-stocks option").remove();
                $("#dossier-stocks").append('<option value="0">Tous</option>');
                
                data.dossiers.forEach(function(d) {
                    $("#dossier-stocks").append('<option value="' + d.id + '">' + d.nom + '</option>');
                });
                
                $("#dossier-stocks").val('').trigger('chosen:updated');
            }
        });
    });

    /*
     * Changement dossier
     */
    $(document).on('change', '#dossier-stocks', function(event) {

        instance_or_clear_grid();

        if ($('#dossier-stocks').val() != 0) {
            event.preventDefault();
            event.stopPropagation();
            $('#loader').show();
            url = Routing.generate('revision_un_rev');
            $.ajax({
                url: url,
                type: "POST",
                dataType: "json",
                data: {
                    "did": $(this).val()
                },
                async: true,
                success: function(data) {

                    $('#exercice-stocks').html('');

                    data.forEach(function(d) {
                        $('#exercice-stocks').append('<option value="' + d + '">' + d + '</option>');
                    });

                    $('#loader').hide();
                }
            });
        } else {

            if ($('#dossier-stocks').val() == 0) {

                $('#exercice-stocks').html('');

                var current_year_m_1 = (new Date()).getFullYear() - 1;
                var current_year_m_2 = (new Date()).getFullYear() - 2;
                var current_year = (new Date()).getFullYear();
                var current_year_1 = (new Date()).getFullYear() + 1;
                var current_year_2 = (new Date()).getFullYear() + 2;

                $('#exercice-stocks').append('<option value="' + current_year_m_2 + '">' + current_year_m_2 + '</option>');
                $('#exercice-stocks').append('<option value="' + current_year_m_1 + '">' + current_year_m_1 + '</option>');
                $('#exercice-stocks').append('<option value="' + current_year + '">' + current_year + '</option>');
                $('#exercice-stocks').append('<option value="' + current_year_1 + '">' + current_year_1 + '</option>');
                $('#exercice-stocks').append('<option value="' + current_year_2 + '">' + current_year_2 + '</option>');
            }

        }

    });

    /*
     * Reception formatter
     */
    function cell_reception_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-reception'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-reception' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }

    /*
     * Picdata formatter
     */
    function cell_picdata_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-picdata'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-picdata' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Separation formatter
     */
    function cell_separation_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-separation'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-separation' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Saisies formatter
     */
    function cell_saisies_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-saisies'];
        if (!color) {
            color = 'transparent';
        }
        var id = options.rowId;

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-saisies' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Ctrl Saisie formatter
     */
    function cell_ctrl_saisie_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-ctrl-saisie'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-ctrl-saisie' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Imputation formatter
     */
    function cell_imputation_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-imputation'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-imputation' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Ctrl Imputation formatter
     */
    function cell_ctrl_imputation_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-ctrl-imputation'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-ctrl-imputation' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }
    
    /*
     * Banques Rb1 formatter
     */
    function cell_banques_rb1_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-banques-rb1'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-banques-rb1' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }

    /*
     * Banques Rb2 formatter
     */
    function cell_banques_rb2_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-banques-rb2'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-banques-rb2' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }

    /*
     * Banques Ob formatter
     */
    function cell_banques_ob_formatter(cell_value, options, row_object) {

        var new_val = '';
        var color = row_object['couleur-banques-ob'];
        var id = options.rowId;

        if (!color) {
            color = 'transparent';
        }

        if (cell_value == 0) {
            new_val = '<p class="transparent">0</p>';
        }
        else{
            new_val = '<p style="margin: 0 0 0 17px;">' + cell_value + '<i id="qtip-banques-ob' + id + '" class="fa fa-circle badge-priorite" style="color:' + color + '; float:right;"></i></p>';
        }

        return new_val;
    }

    /*
     * Total formatter
     */
    function cell_total_formatter(cell_value, options, row_object) {
        var new_val = row_object['picdata'] + row_object['reception'] + row_object['separation'] + row_object['saisies'] + row_object['ctrl-saisie'] + row_object['imputation'] + row_object['ctrl-imputation'] + row_object['banques-rb1'] + row_object['banques-rb2'] + row_object['banques-ob'];
        return new_val;
    }

    /*
     * Total attr
     */
    function total_attr(rowId, val, rawObject, cm, rdata) {
        if (val == 0) {
             return ' style="color:transparent"';
        }
    }

    /*
     * Create jqgrid
     */
    function instance_or_clear_grid() {

        var client_dossier_label = '';

        if ($('#client-stocks').val() == 0) {
            client_dossier_label = 'Clients';
        } else {
            client_dossier_label = 'Dossiers';
        }

        var options = {
            datatype: 'local',
            height: 0,
            autowidth: true,
            loadonce: true,
            shrinkToFit: false,
            rownumbers: false,
            altRows: false,
            colNames: [client_dossier_label, 'Total', 'Picdata', 'Réception', 'Séparation', 'Saisies', 'Ctrl saisie', 'Imputation', 'Ctrl imputation', 'Banques RB1', 'Banques RB2', 'Banques OB', 'couleur-picdata', 'couleur-reception', 'couleur-separation', 'couleur-saisies', 'couleur-ctrl-saisie', 'couleur-imputation', 'couleur-ctrl-imputation', 'couleur-banques-rb1', 'couleur-banques-rb2', 'couleur-banques-ob', 'dossier-picdata', 'dossier-reception', 'dossier-separation', 'dossier-saisies', 'dossier-ctrl-saisie', 'dossier-imputation', 'dossier-ctrl-imputation', 'dossier-banques-rb1', 'dossier-banques-rb2', 'dossier-banques-ob'],
            colModel: [{
                name: 'client-dossier',
                index: 'client-dossier',
                align: 'left',
                editable: false,
                sortable: true,
                width: 160,
                title: false,
                classes: 'js-client-dossier',
            }, {
                name: 'total',
                index: 'total',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-total',
                formatter: cell_total_formatter,
                cellattr: total_attr
            }, {
                name: 'picdata',
                index: 'picdata',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-picdata',
                formatter: cell_picdata_formatter
            }, {
                name: 'reception',
                index: 'reception',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-reception',
                formatter: cell_reception_formatter
            }, {
                name: 'separation',
                index: 'separation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-separation',
                formatter: cell_separation_formatter
            }, {
                name: 'saisies',
                index: 'saisies',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-saisies',
                formatter: cell_saisies_formatter
            }, {
                name: 'ctrl-saisie',
                index: 'ctrl-saisie',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-ctrl-saisie',
                formatter: cell_ctrl_saisie_formatter
            }, {
                name: 'imputation',
                index: 'imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-imputation',
                formatter: cell_imputation_formatter
            }, {
                name: 'ctrl-imputation',
                index: 'ctrl-imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-ctrl-imputation',
                formatter: cell_ctrl_imputation_formatter
            }, {
                name: 'banques-rb1',
                index: 'banques-rb1',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-banques-rb1',
                formatter: cell_banques_rb1_formatter
            }, {
                name: 'banques-rb2',
                index: 'banques-rb2',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-banques-rb2',
                formatter: cell_banques_rb2_formatter
            }, {
                name: 'banques-ob',
                index: 'banques-ob',
                align: 'center',
                editable: false,
                sortable: false,
                width: 100,
                title: false,
                classes: 'js-banques-ob',
                formatter: cell_banques_ob_formatter
            }, {
                name: 'couleur-picdata',
                index: 'couleur-picdata',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                classes: 'js-couleur-picdata'
            }, {
                name: 'couleur-reception',
                index: 'couleur-reception',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                classes: 'js-couleur-reception'
            }, {
                name: 'couleur-separation',
                index: 'couleur-separation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-couleur-separation'
            }, {
                name: 'couleur-saisies',
                index: 'couleur-saisies',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-couleur-saisies'
            }, {
                name: 'couleur-ctrl-saisie',
                index: 'couleur-ctrl-saisie',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-couleur-ctrl-saisie'
            }, {
                name: 'couleur-imputation',
                index: 'couleur-imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'couleur-js-imputation'
            }, {
                name: 'couleur-ctrl-imputation',
                index: 'couleur-ctrl-imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-couleur-ctrl-imputation'
            }, {
                name: 'couleur-banques-rb1',
                index: 'couleur-banques-rb1',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'couleur-js-banques-rb1'
            }, {
                name: 'couleur-banques-rb2',
                index: 'couleur-banques-rb2',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'couleur-js-banques-rb2'
            }, {
                name: 'couleur-banques-ob',
                index: 'couleur-banques-ob',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-couleur-banques-ob'
            }, {
                name: 'dossier-picdata',
                index: 'dossier-picdata',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                classes: 'js-dossier-picdata'
            }, {
                name: 'dossier-reception',
                index: 'dossier-reception',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                classes: 'js-dossier-reception'
            }, {
                name: 'dossier-separation',
                index: 'dossier-separation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-dossier-separation'
            }, {
                name: 'dossier-saisies',
                index: 'dossier-saisies',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-dossier-saisies'
            }, {
                name: 'dossier-ctrl-saisie',
                index: 'dossier-ctrl-saisie',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-dossier-ctrl-saisie'
            }, {
                name: 'dossier-imputation',
                index: 'dossier-imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'dossier-js-imputation'
            }, {
                name: 'dossier-ctrl-imputation',
                index: 'dossier-ctrl-imputation',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-dossier-ctrl-imputation'
            }, {
                name: 'dossier-banques-rb1',
                index: 'dossier-banques-rb1',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'dossier-js-banques-rb1'
            }, {
                name: 'dossier-banques-rb2',
                index: 'dossier-banques-rb2',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'dossier-js-banques-rb2'
            }, {
                name: 'dossier-banques-ob',
                index: 'dossier-banques-ob',
                align: 'center',
                editable: false,
                sortable: false,
                width: 10,
                title: false,
                classes: 'js-dossier-banques-ob'
            }],
            viewrecords: true,
            hidegrid: true,
        };

        var tableau_grid = $('#tableau_stocks');

        if (tableau_grid[0].grid == undefined) {
            tableau_grid.jqGrid(options);
        } else {
            delete tableau_grid;
            $('#tableau_stocks').GridUnload('#tableau_stocks');
            tableau_grid = $('#tableau_stocks');
            tableau_grid.jqGrid(options);
        }

        tableau_grid.jqGrid('hideCol', ["couleur-picdata", "couleur-reception", "couleur-separation", "couleur-saisies", "couleur-ctrl-saisie", "couleur-imputation", "couleur-ctrl-imputation", "couleur-banques-rb1", "couleur-banques-rb2", "couleur-banques-ob", "dossier-picdata", "dossier-reception", "dossier-separation", "dossier-saisies", "dossier-ctrl-saisie", "dossier-imputation", "dossier-ctrl-imputation", "dossier-banques-rb1", "dossier-banques-rb2", "dossier-banques-ob"]);

        return tableau_grid;

    }

    /*
     * Create tooltip using qtip
     */
    function qtip_initialize(selector,row_id) {
        $("#qtip-" + selector + row_id).qtip({
            content: {
                text: function(event, api) {

                    var rowKey = $("#tableau_stocks").jqGrid('getGridParam', "selrow");

                    var dossier_value = $("#tableau_stocks").getCell(rowKey, "dossier-" + selector);

                    var value = dossier_value.split(',');

                    var tr = '';

                    value.forEach(function(item) {

                        var dossier = item.split('*');

                        tr += '<tr><td class="col-sm-4">' + dossier[0] + '</td><td class="col-sm-4"><i class="fa fa-circle" style="color: '+ dossier[1] +'"></i></td><td class="col-sm-4">' + dossier[2] + '</td></tr>';
                    });

                    var modalbody = '<div class="panel panel-default"><div class="panel-heading"><h3>Priorité</h3></div><div class="panel-body"><table class="table"><tr><th class="col-sm-4">Dossiers</th><th class="col-sm-4">Priorité</th><th class="col-sm-4">Total</th></tr>';
                    modalbody += tr;
                    modalbody += '</table></div></div>';

                    return modalbody;
                }
            },
            position: {
                corner: {
                    tooltip: 'bottomMiddle',
                    target: 'topMiddle'
                }
            },
            show: 'click',
            style: {
                classes: 'qtip-light qtip-shadow'
            }
        });
    }


    function go_stocks() {

        var groupe = $("#groupe-stocks").val();
        var client = $("#client-stocks").val();
        var dossier = $("#dossier-stocks").val();
        var exercice = $("#exercice-stocks").val();

        if (client == '' || dossier == '' || exercice == '' || groupe == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {

            var tableau_grid = instance_or_clear_grid();

            tableau_grid.jqGrid('setGridParam', {
                url: Routing.generate('revision_stocks_images', {
                    groupe: groupe,
                    client: client,
                    dossier: dossier,
                    exercice: exercice,
                }),
                datatype: 'json',
                loadComplete: function() {
                    prepare_tooltip();
                },
                gridComplete: function() {
                    total();
                },

            }).trigger('reloadGrid', [{
                page: 1,
                current: true
            }]);

            var window_height = window.innerHeight - 100;

            if (window_height < 400) {
                tableau_grid.jqGrid('setGridHeight', 400);
            } else {
                tableau_grid.jqGrid('setGridHeight', window_height);
            }

        }
    }

    $('#stocks-nav').on('click',function() {
       go_stocks();
    })


    /*
     * Go button
     */
    $('#btn_get_images_stocks').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        go_stocks();
    });

    /*
     * Get value from <p> selector in cell
     */
    function get_value(p) {
        
        var value = '';

        var p_array = p.split('');

        for(var i = 31; i < p_array.length; i++){
            if (p_array[i] != '<') {
                value += p_array[i];
            }
            else{
                break;
            }
        }

        if (value == '') {
            value = 0;
        }

        return parseInt(value);

    }

    /*
     * Responsive jqgrid
     */
    $(window).resize(function() {
        var tableau_grid = $('#tableau_stocks');
        var window_height = window.innerHeight - 100;
        setTimeout(function() {
            tableau_grid.jqGrid("setGridWidth", tableau_grid.closest(".panel-body").width());

            var width = tableau_grid.closest(".panel-body").width();


            tableau_grid.jqGrid("setGridWidth", width);

            if (window_height < 400) {
                tableau_grid.jqGrid('setGridHeight', 400);
            } else {
                tableau_grid.jqGrid('setGridHeight', window_height);
            }

        }, 600);


    });

    /*
     * Display Tooltip
     */
    function prepare_tooltip() {

        var rows = $("#tableau_stocks").getDataIDs();

        for (var i = 0; i < rows.length; i++) {

            /*picdata*/
            var dossier_picdata_value = $("#tableau_stocks").getCell(rows[i], "dossier-picdata");
            
            if (dossier_picdata_value && dossier_picdata_value != '') {
                qtip_initialize('picdata',rows[i]);
            }

            /*reception*/
            var dossier_reception_value = $("#tableau_stocks").getCell(rows[i], "dossier-reception");

            if (dossier_reception_value && dossier_reception_value != '') {
                qtip_initialize('reception',rows[i]);
            }

            /*separation*/
            var dossier_separation_value = $("#tableau_stocks").getCell(rows[i], "dossier-separation");

            if (dossier_separation_value && dossier_separation_value != '') {
                qtip_initialize('separation',rows[i]);
            }

            /*saisies*/
            var dossier_saisies_value = $("#tableau_stocks").getCell(rows[i], "dossier-saisies");

            if (dossier_saisies_value && dossier_saisies_value != '') {
                qtip_initialize('saisies',rows[i]);
            }

            /*ctrl-saisie*/
            var dossier_ctrl_saisie_value = $("#tableau_stocks").getCell(rows[i], "dossier-ctrl-saisie");

            if (dossier_ctrl_saisie_value && dossier_ctrl_saisie_value != '') {
                qtip_initialize('ctrl-saisie',rows[i]);
            }

            /*imputation*/
            var dossier_imputation_value = $("#tableau_stocks").getCell(rows[i], "dossier-imputation");

            if (dossier_imputation_value && dossier_imputation_value != '') {
                qtip_initialize('imputation',rows[i]);
            }

            /*ctrl-imputation*/
            var dossier_ctrl_imputation_value = $("#tableau_stocks").getCell(rows[i], "dossier-ctrl-imputation");

            if (dossier_ctrl_imputation_value && dossier_ctrl_imputation_value != '') {
                qtip_initialize('ctrl-imputation',rows[i]);
            }

            /*banques-rb1*/
            var dossier_banques_rb1_value = $("#tableau_stocks").getCell(rows[i], "dossier-banques-rb1");

            if (dossier_banques_rb1_value && dossier_banques_rb1_value != '') {
                qtip_initialize('banques-rb1',rows[i]);
            }

            /*banques-rb2*/
            var dossier_banques_rb2_value = $("#tableau_stocks").getCell(rows[i], "dossier-banques-rb2");

            if (dossier_banques_rb2_value && dossier_banques_rb2_value != '') {
                qtip_initialize('banques-rb2',rows[i]);
            }

            /*banques-ob*/
            var dossier_banques_ob_value = $("#tableau_stocks").getCell(rows[i], "dossier-banques-ob");

            if (dossier_banques_ob_value && dossier_banques_ob_value != '') {
                qtip_initialize('banques-ob',rows[i]);
            }
        }
    }

    /*
     * Total row
     */
    function total() {
        
        var rows = $("#tableau_stocks").getDataIDs();
        var exist = false;
        var sum_picdata = 0;
        var sum_reception = 0;
        var sum_separation = 0;
        var sum_saisies = 0;
        var sum_ctrl_saisie = 0;
        var sum_imputation = 0;
        var sum_ctrl_imputation = 0;
        var sum_banques_rb1 = 0;
        var sum_banques_rb2 = 0;
        var sum_banques_ob = 0;

        var myData = $("#tableau_stocks").jqGrid('getRowData');

        var rows = $("#tableau_stocks").getDataIDs();

        for (var i = 0; i < rows.length; i++) {

            var dossier = $("#tableau_stocks").getCell(rows[i], "client-dossier");

            if (dossier == 'Total') {
                $("#tableau_stocks").jqGrid('setRowData', rows[i], false, {
                    color: '#000',
                    weightfont: 'bold',
                    //background: '#d2d2d2',
                });
                exist = true;

                if (i != 0) {
                    $('#tableau_stocks').jqGrid('delRowData',rows[i]);
                }

            }

            var picdata_value = get_value($("#tableau_stocks").getCell(rows[i], "picdata"));
            var reception_value = get_value($("#tableau_stocks").getCell(rows[i], "reception"));
            var separation_value = get_value($("#tableau_stocks").getCell(rows[i], "separation"));
            var saisies_value = get_value($("#tableau_stocks").getCell(rows[i], "saisies"));
            var ctrl_saisie_value = get_value($("#tableau_stocks").getCell(rows[i], "ctrl-saisie"));
            var imputation_value = get_value($("#tableau_stocks").getCell(rows[i], "imputation"));
            var ctrl_imputation_value = get_value($("#tableau_stocks").getCell(rows[i], "ctrl-imputation"));
            var banques_rb1_value = get_value($("#tableau_stocks").getCell(rows[i], "banques-rb1"));
            var banques_rb2_value = get_value($("#tableau_stocks").getCell(rows[i], "banques-rb2"));
            var banques_ob_value = get_value($("#tableau_stocks").getCell(rows[i], "banques-ob"));

            sum_picdata += parseInt(picdata_value);
            sum_reception += parseInt(reception_value);
            sum_separation += parseInt(separation_value);
            sum_saisies += parseInt(saisies_value);
            sum_ctrl_saisie += parseInt(ctrl_saisie_value);
            sum_imputation += parseInt(imputation_value);
            sum_ctrl_imputation += parseInt(ctrl_imputation_value);
            sum_banques_rb1 += parseInt(banques_rb1_value);
            sum_banques_rb2 += parseInt(banques_rb2_value);
            sum_banques_ob += parseInt(banques_ob_value);

            if (i == rows.length - 1 && rows.length > 1) {

                var total = {
                    'client-dossier': 'Total',
                    'total': sum_picdata + sum_reception + sum_separation + sum_saisies + sum_ctrl_saisie + sum_imputation + sum_banques_rb1 + sum_banques_rb2 + sum_banques_ob,
                    'picdata': sum_picdata,
                    'reception': sum_reception,
                    'separation': sum_separation,
                    'saisies': sum_saisies,
                    'ctrl-saisie': sum_ctrl_saisie,
                    'imputation': sum_imputation,
                    'ctrl-imputation': sum_ctrl_imputation,
                    'banques-rb1': sum_banques_rb1,
                    'banques-rb2': sum_banques_rb2,
                    'banques-ob': sum_banques_ob,
                };

                if (!exist) {
                    $("#tableau_stocks").addRowData("total-row-stocks", total, 'first');
                }
            }
        }
    }

});