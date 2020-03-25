/**
 * Details 
 */

 $(function() {

 	$('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('.chosen-select-site').chosen({
        no_results_text: "Aucun site trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('#dossier').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('#btn_nb').on('click',function () {
        $('#js-filtre-nb').modal('show');
    })

    $(document).on('change', '#js_filtre_periode', function()
    {
        if($(this).val() === '6'){
            $('#js-filtre-fourchette').modal('show');
        }
        else{
            $('#js_debut_date').val('');
            $('#js_fin_date').val('');
        }


    });

    $('#btn-annuler').on('click',function() {
        annuler();
    })

    $('#btn-valider').on('click',function() {
        $('#js-filtre-fourchette').modal('hide');
    })

    $('#btn-annuler-nb').on('click',function() {
        $("#filtre_nb").val('0').trigger('chosen:updated');
        $("#operateur_nb").val('0').trigger('chosen:updated');
        $("#value_nb").val('').trigger('chosen:updated');
    })

    $('#btn-valider-nb').on('click',function() {
        $('#js-filtre-nb').modal('hide');
    })

    function annuler() {
        $("#js_filtre_periode").val('5').trigger('chosen:updated');
        $('#js_debut_date').val('');
        $('#js_fin_date').val('');
        
    }

    $('#btn-valider-nb').on('click',function() {
        $("#myModal5").modal('hide');
        go();
    })

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        categorie_selector = $('#categorie'),
        exercice_selector = $('#exercice'),
        loader_selector = $('#loader');

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');

    $('#js_debut_date').datepicker({
        format: 'dd-mm-yyyy',
        language: 'fr',
        autoclose: true,
        todayHighlight: true
    });

    $('#js_fin_date').datepicker({
        format: 'dd-mm-yyyy',
        language: 'fr',
        autoclose: true,
        todayHighlight: true
    });

    // Changement client
    $(document).on('change', '#client', function(event) {
        event.preventDefault();
        event.stopPropagation();
        instance_or_clear_grid();
        $("#dossier option").remove();
        $("#dossier").val('').trigger('chosen:updated');
        $('#exercice').trigger('change');
    });

    /*Changement exrcice*/
    $(document).on('change','#exercice',function(event) {
        event.preventDefault();
        event.stopPropagation();

        url = Routing.generate('revision_dossier_client',{
            client: $('#client').val(),
            exercice: $('#exercice').val()
        });

        $.ajax({
            url: url,
            type: "GET",
            datatype: "json",
            async: true,
            success: function(data) {
                instance_or_clear_grid(); 
                $("#dossier option").remove();

                if (data.length == 0 && $('#client').val() != 0 ) {
                    $('#btn_get_images').attr('disabled','disabled');
                    $('#dossier').attr('data-placeholder','Aucun dossier').trigger('chosen:updated');
                    $('#dossier').attr('disabled','disabled').trigger('chosen:updated');
                    $('#btn_export_details').attr('disabled','disabled');
                    
                    show_info('Aucun dossier', 'Veuillez Verifiez l\'exercice', 'info');
                    return false;
                }
                else{

                    $('#btn_get_images').removeAttr('disabled');
                    $('#dossier').attr('data-placeholder','Séléctionner un dossier').trigger('chosen:updated');
                    $('#dossier').removeAttr('disabled').trigger('chosen:updated');
                    $('#btn_export_details').removeAttr('disabled');

                    if (data.length > 1) {
                        $("#dossier").append('<option value="0">Tous</option>');
                    }
                  
                    data.forEach(function(d) {
                        $("#dossier").append('<option value="' + d.id + '">' + d.nom + '</option>');
                    });

                    if (data.length == 1) {
                        $("#dossier").val(data[0].id).trigger('chosen:updated');
                    }
                    else{
                        $("#dossier").val('0').trigger('chosen:updated');
                    }

                }

                
            }
        });

    })

    function isNumber(value) {

        if (typeof value == "string") {

            if (value.includes('-') || value == '' || value == 'NaN' || value == '""') {
                return false;
            }
            else{
                
                return Number(value);
            }
        }
        else{
            return Number(value);
        }
    }

    function cell_number_formatter(cell_value, options, row_object) {

        if (cell_value == undefined) {
            return '';
        }
        
        var new_value = cell_value;

        if (isNumber(cell_value)) {
            
            new_value = isNumber(cell_value).toLocaleString();

        }

        return new_value;
    }

    function instance_or_clear_grid() {
        var window_height = window.innerHeight;

        if ($('#js_filtre_typedate').val()){
            var shrinkToFit = false;
            // var colNames = ['Dossiers', 'Exercice', 'Clients', 'Total images', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16', 'm17', 'm18', 'm19', 'm20', 'm21', 'm22', 'm23', 'm24', 'totalN', 'totalNPrev'];
            
            var colNames = ['Dossiers', 'Exercice', 'Clients', 'Total images', '< m', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16', 'm17', 'm18', 'm19', 'm20', 'm21', 'm22', 'm23', 'm24', 'totalN', 'totalNPrev'];
            
            var colModel = [{
                    name: 'dossier',
                    index: 'dossier',
                    align: 'left',
                    editable: false,
                    sortable: false,
                    width: 125,
                    classes: 'js-dossier'
                }, {
                    name: 'exercice',
                    index: 'exercice',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 125,
                    classes: 'js-exercice'
                }, {
                    name: 'client',
                    index: 'client',
                    align: 'left',
                    editable: false,
                    sortable: false,
                    width: 125,
                    classes: 'js-client'
                }, {
                    name: 'total',
                    index: 'total',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 100,
                    classes: 'js-total',
                    formatter: cell_number_formatter
                }, {
                    name    : 'm+24',
                    index   : 'm+24',
                    align   : 'center',
                    editable: false,
                    sortable: false,
                    width   : 100,
                    classes : 'js-m-25',
                    // formatter: cell_number_formatter
                }, {
                    name: 'm',
                    index: 'm',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+1',
                    index: 'm+1',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-1',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+2',
                    index: 'm+2',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-2',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+3',
                    index: 'm+3',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-3',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+4',
                    index: 'm+4',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-4',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+5',
                    index: 'm+5',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-5',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+6',
                    index: 'm+6',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-6',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+7',
                    index: 'm+7',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-7',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+8',
                    index: 'm+8',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-8',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+9',
                    index: 'm+9',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-9',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+10',
                    index: 'm+10',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-10',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+11',
                    index: 'm+11',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-11',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+12',
                    index: 'm+12',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-12',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+13',
                    index: 'm+13',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-13',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+14',
                    index: 'm+14',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-14',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+15',
                    index: 'm+15',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-15',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+16',
                    index: 'm+16',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-16',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+17',
                    index: 'm+17',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-17',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+18',
                    index: 'm+18',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-18',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+19',
                    index: 'm+19',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-19',
                    formatter: cell_number_formatter
                }
                , {
                    name: 'm+20',
                    index: 'm+20',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-20',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+21',
                    index: 'm+21',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-21',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+22',
                    index: 'm+22',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-22',
                    formatter: cell_number_formatter
                }, {
                    name: 'm+23',
                    index: 'm+23',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 65,
                    classes: 'js-m-23',
                    formatter: cell_number_formatter
                }, {
                    name: 'totalN',
                    index: 'totalN',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 1,
                    classes: 'js-totalN'
                }, {
                    name: 'totalNPrev',
                    index: 'totalNPrev',
                    align: 'center',
                    editable: false,
                    sortable: false,
                    width: 1,
                    classes: 'js-totalNPrev'
                },

            ];

        }

        var options = {
            datatype: 'local',
            height: 0,
            autowidth: true,
            loadonce: true,
            shrinkToFit: shrinkToFit,
            rownumbers: false,
            altRows: false,
            colNames: colNames,
            colModel: colModel,
            viewrecords: true,
            hidegrid: true,
        };

        var tableau_grid = $('#js_tableau_images_recues');

        if (tableau_grid[0].grid == undefined) {

            tableau_grid.jqGrid(options);

        } else {
            delete tableau_grid;
            $('#js_tableau_images_recues').GridUnload('#js_tableau_images_recues');
            tableau_grid = $('#js_tableau_images_recues');
            tableau_grid.jqGrid(options);
        }

        $('.total-n-n1').html('');

        tableau_grid.jqGrid('hideCol', ["totalN", "totalNPrev"]);

        return tableau_grid;
    }

    function go() {
        
        var select_periode = $("#js_filtre_periode").find('option:selected').html();
        var client = $('#client').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val(),
            periode = $('#js_filtre_periode').val(),
            site = $('#site').val();
            typedate = $('#js_filtre_typedate').val();
            analyse = $('#js_filtre_analyse').val();
            filtre_nb = $('#filtre_nb').val();
            operateur_nb = $('#operateur_nb').val();
            value_nb = $('#value_nb').val();

            if (!value_nb || value_nb == '' || value_nb == 'undefined') {
                value_nb = 0;
            }

        if (client == '' || dossier == '' || periode == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {
            if (periode == "6") {
                var periodeDeb = $("#js_debut_date").val(),
                    periodeFin = $("#js_fin_date").val();
                if (periodeDeb == '' || periodeFin == '') {
                    show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
                    return false;
                }
                var perioDeb = periodeDeb.split("-"),
                    perioFin = periodeFin.split("-");
                var dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0],
                    dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
            } else {
                var dateDeb = 0,
                    dateFin = 0;
            }

            var tableau_grid = instance_or_clear_grid();

            var rowsToColor = [];

            var url = Routing.generate('revision_liste_images_recues', {
                    client: client,
                    dossier: dossier,
                    exercice: exercice,
                    periode: periode,
                    perioddeb: dateDeb,
                    periodfin: dateFin,
                    typedate: typedate,
                    analyse: analyse,
                    tab: 1,
                    filtre_nb: filtre_nb,
                    operateur_nb: operateur_nb,
                    value_nb: value_nb
                });

            $.ajax({
                url: url,
                type: "GET",
                datatype: "json",
                async: true,
                success: function(response) {

                    if (response.count&& response.percent) {

                        $('#dossiers-filter').removeClass('hidden');

                        $('#count-dossiers-filter').html(response.count);

                        $('#percent-dossiers-filter').html(response.percent);
                    }

                    else{
                        $('#dossiers-filter').addClass('hidden');
                    }


                    var tableau_grid = instance_or_clear_grid();

                    tableau_grid.jqGrid('setGridParam', {
                        //url: url,
                        //datatype: 'json',
                        sortname: 'dossier',
                        sortorder: 'asc',
                        data: response.data,
                        loadComplete: function() {

                            resize_tab_details();

                            if ($('#client').val() != 0) {
                                tableau_grid.jqGrid('hideCol', ["client"]);
                            }

                            var border_right = "2px solid #1cb394";

                            $('tr#total-n-n1-row > td.js-m-23').attr('style','border-right:2px solid #1cb394;')

                            $('tr#total-row > td.js-m-23').attr('style','border-right:2px solid #1cb394;')

                        },
                        gridComplete: function() {
                            var rows = $("#js_tableau_images_recues").getDataIDs();
                            var total = 0;
                            var total1 = 0;
                            var exist = false;
                            var rowid = -1;

                            var m = 0;
                            var m1 = 0;
                            var m2 = 0;
                            var m3 = 0;
                            var m4 = 0;
                            var m5 = 0;
                            var m6 = 0;
                            var m7 = 0;
                            var m8 = 0;
                            var m9 = 0;
                            var m10 = 0;
                            var m11 = 0;
                            var m12 = 0;
                            var m13 = 0;
                            var m14 = 0;
                            var m15 = 0;
                            var m16 = 0;
                            var m17 = 0;
                            var m18 = 0;
                            var m19 = 0;
                            var m20 = 0;
                            var m21 = 0;
                            var m22 = 0;
                            var m23 = 0;
                            var m24 = 0;

                            var m_ = 0;
                            var m_1 = 0;
                            var m_2 = 0;
                            var m_3 = 0;
                            var m_4 = 0;
                            var m_5 = 0;
                            var m_6 = 0;
                            var m_7 = 0;
                            var m_8 = 0;
                            var m_9 = 0;
                            var m_10 = 0;
                            var m_11 = 0;
                            var m_12 = 0;
                            var m_13 = 0;
                            var m_14 = 0;
                            var m_15 = 0;
                            var m_16 = 0;
                            var m_17 = 0;
                            var m_18 = 0;
                            var m_19 = 0;
                            var m_20 = 0;
                            var m_21 = 0;
                            var m_22 = 0;
                            var m_23 = 0;
                            var m_24 = 0;

                            for (var i = 0; i < rows.length; i++) {

                                var dossier = $("#js_tableau_images_recues").getCell(rows[i], "dossier");

                                var exercice = $("#js_tableau_images_recues").getCell(rows[i], "exercice");

                                if (exercice == "" && dossier != 'Total N' && dossier != 'Total N -1') {
                                    $("#js_tableau_images_recues").jqGrid('setRowData', rows[i], false, {
                                        'color': '#000',
                                        'background': '#cecece',
                                        'font-weight': 'bold',
                                    });

                                    $("#js_tableau_images_recues").setCell (rows[i],'dossier',dossier,{color:'transparent'});
                                }


                                if (dossier == "") {
                                    $("#js_tableau_images_recues").jqGrid('setRowData', rows[i], false, {
                                        'color': '#000',
                                        'background': '#cecece',
                                        'font-weight': 'bold',
                                    });
                                } else {

                                    if (dossier == 'Total N') {
                                        exist = true;
                                    }

                                    if (dossier == 'Total N -1') {
                                        exist = true;
                                    }

                                    if ($("#js_tableau_images_recues").getCell(rows[i], "exercice") == "N") {
                                        total += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "total")));
                                        m += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m")));
                                        m1 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+1")));
                                        m2 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+2")));
                                        m3 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+3")));
                                        m4 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+4")));
                                        m5 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+5")));
                                        m6 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+6")));
                                        m7 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+7")));
                                        m8 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+8")));
                                        m9 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+9")));
                                        m10 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+10")));
                                        m11 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+11")));
                                        m12 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+12")));
                                        m13 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+13")));
                                        m14 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+14")));
                                        m15 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+15")));
                                        m16 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+16")));
                                        m17 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+17")));
                                        m18 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+18")));
                                        m19 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+19")));
                                        m20 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+20")));
                                        m21 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+21")));
                                        m22 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+22")));
                                        m23 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+23")));
                                        m24 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+24")));
                                    } else {
                                        if ($("#js_tableau_images_recues").getCell(rows[i], "exercice") == "N - 1") {
                                            total1 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "total")));
                                            m_ += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m")));
                                            m_1 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+1")));
                                            m_2 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+2")));
                                            m_3 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+3")));
                                            m_4 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+4")));
                                            m_5 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+5")));
                                            m_6 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+6")));
                                            m_7 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+7")));
                                            m_8 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+8")));
                                            m_9 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+9")));
                                            m_10 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+10")));
                                            m_11 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+11")));
                                            
                                            m_12 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+12")));
                                            m_13 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+13")));
                                            m_14 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+14")));
                                            m_15 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+15")));
                                            m_16 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+16")));
                                            m_17 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+17")));
                                            m_18 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+18")));
                                            m_19 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+19")));
                                            m_20 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+20")));
                                            m_21 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+21")));
                                            m_22 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+22")));
                                            m_23 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+23")));
                                            m_24 += parseInt(not_null($("#js_tableau_images_recues").getCell(rows[i], "m+24")));

                                        }
                                    }

                                }

                            }

                            if (!exist && rows.length > 5) {

                                    var value1 = {
                                        'dossier': 'Total N -1',
                                        'exercice': '',
                                        'client': '',
                                        'total': total1,
                                        'm+24': m_24,
                                        'm': m_,
                                        'm+1': m_1,
                                        'm+2': m_2,
                                        'm+3': m_3,
                                        'm+4': m_4,
                                        'm+5': m_5,
                                        'm+6': m_6,
                                        'm+7': m_7,
                                        'm+8': m_8,
                                        'm+9': m_9,
                                        'm+10': m_10,
                                        'm+11': m_11,
                                        'm+12': m_12,
                                        'm+13': m_13,
                                        'm+14': m_14,
                                        'm+15': m_15,
                                        'm+16': m_16,
                                        'm+17': m_17,
                                        'm+18': m_18,
                                        'm+19': m_19,
                                        'm+20': m_20,
                                        'm+21': m_21,
                                        'm+22': m_22,
                                        'm+23': m_23,
                                        'totalN': '',
                                        'totalNPrev': '',
                                    }

                                jQuery("#js_tableau_images_recues").addRowData("total-row", value1, 'first');

                                    var value = {
                                        'dossier': 'Total N',
                                        'exercice': '',
                                        'client': '',
                                        'total': total,
                                        'm': m,
                                        'm+24': m24,
                                        'm+1': m1,
                                        'm+2': m2,
                                        'm+3': m3,
                                        'm+4': m4,
                                        'm+5': m5,
                                        'm+6': m6,
                                        'm+7': m7,
                                        'm+8': m8,
                                        'm+9': m9,
                                        'm+10': m10,
                                        'm+11': m11,
                                        'm+12': m12,
                                        'm+13': m13,
                                        'm+14': m14,
                                        'm+15': m15,
                                        'm+16': m16,
                                        'm+17': m17,
                                        'm+18': m18,
                                        'm+19': m19,
                                        'm+20': m20,
                                        'm+21': m21,
                                        'm+22': m22,
                                        'm+23': m23,
                                        'totalN': '',
                                        'totalNPrev': '',
                                    }

                                jQuery("#js_tableau_images_recues").addRowData("total-n-n1-row", value, 'first');
                            }

                        }
                    }).trigger('reloadGrid', [{
                        page: 1,
                        current: true
                    }]);
                }
            });




            var window_height = window.innerHeight - 300;

            if (window_height < 400) {
                tableau_grid.jqGrid('setGridHeight', 400);
            } else {
                tableau_grid.jqGrid('setGridHeight', window_height);
            }
        }
    }

    $('#btn_get_images').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        go();
    });

    function not_null(value) {

        if (!value || value == '' || value == undefined || value == null) {
            return 0;
        }
        else{
            if (isNumber(value)) {
                return value
            }
            else{

                value = value.replace(/\s/g, '');

                return Number(value);
            }
        }


    }

    $('#image-recue-nav').on('click',function() {
       resize_tab_details()
    })

    function resize_tab_details() {
        setTimeout(function() {
            var tableau_grid = $('#js_tableau_images_recues');
            var window_height = window.innerHeight - 300;

            var width = tableau_grid.closest(".panel-body").width() - 15 ;

            tableau_grid.jqGrid("setGridWidth", width);

            if (window_height < 400) {
                tableau_grid.jqGrid('setGridHeight', 400);
            } else {
                tableau_grid.jqGrid('setGridHeight', window_height);
            }

        }, 600);
    } 

    $(window).resize(function() {
        
        resize_tab_details();

    });

    //END
 })