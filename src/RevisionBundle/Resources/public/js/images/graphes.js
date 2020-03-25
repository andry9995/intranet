/**
 * Graphes
 */

 $(function() {

 	function _export(type) {
 		var graphe = $('#graphe');

 		if (graphe.highcharts() == undefined) {
 			show_info("Echec d\'exportation", "Graphe vide", "error");
            return false;
 		}
 		else{
 			switch(type) {
 				case 0:
 					graphe.highcharts()
            			  .print();
 					break;
 				case 1:
 					graphe.highcharts()
            			  .exportChart();
 					break;
 				case 2:
 					graphe.highcharts()
            			  .exportChart({
					            type: 'jpg',
					        });
 					break;
 				case 3:
 					graphe.highcharts()
            			  .exportChart({
					            type: 'application/pdf',
					        });
 					break;
 				case 4:
 					graphe.highcharts()
            			  .downloadCSV();
 					break;
 				case 5:
 					graphe.highcharts()
            			  .downloadXLS();
 					break;
 			}
 		}
 	}


 	$('#export-print').click(function () {
        _export(0);
    }); 

    $('#export-png').click(function () {
        _export(1);
    });

    $('#export-jpeg').click(function () {
        _export(2);
            
    });  

    $('#export-pdf').click(function () {
        _export(3);
            
    }); 

    $('#export-csv').click(function () {
        _export(4);
    }); 

    $('#export-xls').click(function () {
        _export(5);
    }); 

 	$(document).on('change', '#js_filtre_periode_graphe', function()
    {
        if($(this).val() === '6'){
            $('#js-filtre-fourchette-graphe').modal('show');
        }
        else{
            $('#js_debut_date_graphe').val('');
            $('#js_fin_date_graphe').val('');
        }

    });

    $('#btn-annuler-graphe').on('click',function() {
        annuler_graphe();
    })

    $('#btn-valider-graphe').on('click',function() {
        $('#js-filtre-fourchette-graphe').modal('hide');
    })

    function annuler_graphe() {
        $("#js_filtre_periode_graphe").val('5').trigger('chosen:updated');
        $('#js_debut_date_graphe').val('');
        $('#js_fin_date_graphe').val('');
        
    }

    $('.chosen-select-client-graphe').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('.chosen-select-site-graphe').chosen({
        no_results_text: "Aucun site trouvé:",
        search_contains: true,
        width: '100%'
    });

    $('#dossier-graphe').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    var client_selector = $('#client-graphe'),
        site_selector = $('#site-graphe'),
        dossier_selector = $('#dossier-graphe'),
        categorie_selector = $('#categorie'),
        exercice_selector = $('#exercice-graphe'),
        loader_selector = $('#loader');

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');

    // Show fourchette
    $("#js_filtre_periode_graphe").change(function() {

        if ($(this).val() == "6") {
            $("#js_filtre_fourchette_graphe").show();
            $("#js_filtre_fourchette_graphe").css({
                display: "block"
            });
        } else {
            $("#js_filtre_fourchette_graphe").hide();
        }
    });

    $('#js_debut_date_graphe').datepicker({
        format: 'dd-mm-yyyy',
        language: 'fr',
        autoclose: true,
        todayHighlight: true
    });
    $('#js_fin_date_graphe').datepicker({
        format: 'dd-mm-yyyy',
        language: 'fr',
        autoclose: true,
        todayHighlight: true
    });

    // Changement client
    $(document).on('change', '#client-graphe', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $("#dossier-graphe option").remove();
        $("#dossier-graphe").val('').trigger('chosen:updated');

        //$("#exercice").val('0').trigger('chosen:updated');

        $('#exercice-graphe').trigger('change');
    });

    $(document).on('change','#exercice-graphe',function(event) {
        event.preventDefault();
        event.stopPropagation();

        url = Routing.generate('revision_dossier_client',{
            client: $('#client-graphe').val(),
            exercice: $('#exercice-graphe').val()
        });

        $.ajax({
            url: url,
            type: "GET",
            datatype: "json",
            async: true,
            success: function(data) {
                $("#dossier-graphe option").remove();

                if (data.length == 0 && $('#client-graphe').val() != 0 ) {
                	$('#btn_get_images_graphe').attr('disabled','disabled');
                    $('#dossier-graphe').attr('data-placeholder','Aucun dossier').trigger('chosen:updated');
                    $('#dossier-graphe').attr('disabled','disabled').trigger('chosen:updated');
                    
                    show_info('Aucun dossier', 'Veuillez Verifiez l\'exercice', 'info');
                    return false;
                }

                else{

                	$('#btn_get_images_graphe').removeAttr('disabled');
                    $('#dossier-graphe').attr('data-placeholder','Séléctionner un dossier').trigger('chosen:updated');
                    $('#dossier-graphe').removeAttr('disabled').trigger('chosen:updated');

                    if (data.length > 1) {
	                	$("#dossier-graphe").append('<option value="0">Tous</option>');
                    }

	                data.forEach(function(d) {
	                    $("#dossier-graphe").append('<option value="' + d.id + '">' + d.nom + '</option>');
	                });

	                if (data.length == 1) {
                        $("#dossier-graphe").val(data[0].id).trigger('chosen:updated');
	                }
	                else{
	                	$("#dossier-graphe").val('0').trigger('chosen:updated');
	                	
	                }

                }

            }
        });

    })


    $('#btn_get_images_graphe').on('click', function(event) {

        event.preventDefault();
        event.stopPropagation();

        go_graphe();
    });

    function go_graphe() {
    
	    var select_periode = $("#js_filtre_periode_graphe").find('option:selected').html();
	    var client = $('#client-graphe').val(),
	        dossier = $('#dossier-graphe').val(),
	        exercice = $('#exercice-graphe').val(),
	        periode = $('#js_filtre_periode_graphe').val(),
	        site = $('#site-graphe').val();
	    typedate = $('#js_filtre_typedate_graphe').val();
	    analyse = $('#js_filtre_analyse_graphe').val();

	    if (client == '' || dossier == '' || periode == '') {
	        show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
	        return false;
	    } else {
	        if (periode == "6") {
	            var periodeDeb = $("#js_debut_date_graphe").val(),
	                periodeFin = $("#js_fin_date_graphe").val();
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

	        var url = Routing.generate('revision_liste_images_recues', {
	            client: client,
	            /*site : site,*/
	            dossier: dossier,
	            exercice: exercice,
	            periode: periode,
	            perioddeb: dateDeb,
	            periodfin: dateFin,
	            typedate: typedate,
	            analyse: analyse,
	            tab: 2
	        });

	        $.ajax({
	            url: url,
	            type: "GET",
	            dataType: "json",
	            success: function(response) {

	                var chart = instance_chart();

	                var data = response['courbe'];

	                var analyse = response['analyse'];

	                chart.addSeries({
	                    name: "Images N-2",
	                    data: data[2].data
	                });
	                chart.addSeries({
	                    name: "Images N-1",
	                    data: data[1].data
	                });
	                chart.addSeries({
	                    name: "Images N",
	                    data: data[0].data
	                });

	                if (analyse == 1) {

	                    chart.addSeries({
	                        type: 'line',
	                        name: "Tendance images N-2 et N-1",
	                        marker: {
	                            enabled: false
	                        },
	                        data: (function() {
	                            return fitOneDimensionalData(arrayAddition(data[2].data, data[1].data));
	                        })()
	                    });
	                    /*chart.addSeries({
	                              type: 'line',
	                              name: "Tendance images N-1",
	                              marker: {
	                                    enabled: false
	                              },
	                              data: (function() {
	                                    return fitOneDimensionalData(data[1].data);
	                              })()
	                            });*/
	                    chart.addSeries({
	                        type: 'line',
	                        name: "Tendance images N",
	                        marker: {
	                            enabled: false
	                        },
	                        data: (function() {

	                            exercice_selector = $('#exercice-graphe')

	                            return fitOneDimensionalDataTendance(data[0].data,exercice_selector.val());

	                        })()
	                    });
	                }
	            }
	        });
	    }
	}

	function instance_chart() {

	    var chart = Highcharts.chart('graphe', {

	        title: {
	            text: 'Graphe des images'
	        },

	        yAxis: {
	            min: 0,
	            title: {
	                text: 'Nombres d\'images ( k = mille)'
	            }
	        },
	        xAxis: {
	            categories: ['Antérieur','Juin N-1', 'Juil N-1', 'Aout N-1', 'Sept N-1', 'Oct N-1', 'Nov N-1', 'Dec N-1', 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sept', 'Oct', 'Nov', 'Dec', 'Jan N+1', 'Fev N+1', 'Mar N+1', 'Avr N+1', 'Mai N+1', 'Juin N+1','Exterieur'],
	        },
	        legend: {
	            layout: 'vertical',
	            align: 'right',
	            verticalAlign: 'middle'
	        },

	        plotOptions: {
	            series: {
	                label: {
	                    enabled: false
	                },
	            },
	        },
	        responsive: {
	            rules: [{
	                condition: {
	                    maxWidth: 500
	                },
	                chartOptions: {
	                    legend: {
	                        layout: 'horizontal',
	                        align: 'center',
	                        verticalAlign: 'bottom'
	                    }
	                }
	            }]
	        },
	        credits: false,
	        legend: {
	            layout: 'vertical',
	            align: 'right',
	            verticalAlign: 'middle',
	            borderWidth: 0
	        },
	        colors: ["#23c300", "#82807a", "#0062e3", "#f8ac59", "#0062e3"],
	        exporting: {
	            buttons: {
	                contextButton: {
	                    menuItems: [
	                        'printChart',
	                        'separator',
	                        'downloadPNG',
	                        'downloadJPEG',
	                        'downloadPDF',
	                        'separator',
	                        'downloadCSV',
	                        'downloadXLS',
	                    ]
	                }
	            }
	        },
	        lang: {
	            printChart: '<i class="fa fa-print" aria-hidden="true"></i> Imprimer',
	            downloadPNG: '<i class="fa fa-download" aria-hidden="true"></i> En format PNG',
	            downloadJPEG: '<i class="fa fa-download" aria-hidden="true"></i> En format JPEG',
	            downloadPDF: '<i class="fa fa-download" aria-hidden="true"></i> En format PDF',
	            //downloadSVG: '<i class="fa fa-download" aria-hidden="true"></i> Format SVG',
	            downloadCSV: '<i class="fa fa-file" aria-hidden="true"></i> Exporter en CSV',
	            downloadXLS: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Exporter en XLS',
	            //viewData: 'Afficher le Tableau des données',
	            //openInCloud: 'Ouvrir dans Highcharts Clouds',
	            contextButtonTitle: 'Menu'
	        }

	    });

	    return chart;
	}

	function fitOneDimensionalDataTendance(source_data,exercice) {
	    var trend_source_data = [];

	    $continu = false;

	    var now = new Date();
	    var current_year = now.getFullYear();
	    var current_month = now.getMonth();

	    for (var i = source_data.length; i-- > 0;) {

	        if (current_year == exercice) {
	            if (i < current_month + 9) {
	                trend_source_data[i] = [i, source_data[i]];
	            }

	        }
	        else{
	            trend_source_data[i] = [i, source_data[i]];
	        }


	    }
	    var regression_data = fitData(trend_source_data).data;
	    var trend_line_data = [];
	    for (i = regression_data.length; i-- > 0;) {
	        trend_line_data[i] = Math.round(regression_data[i][1]);
	    }
	    return trend_line_data;
	}

	function fitOneDimensionalData(source_data) {
	    var trend_source_data = [];

	    $continu = false;

	    for (var i = source_data.length; i-- > 0;) {
	        trend_source_data[i] = [i, source_data[i]];
	    }
	    var regression_data = fitData(trend_source_data).data;
	    var trend_line_data = [];
	    for (i = regression_data.length; i-- > 0;) {
	        trend_line_data[i] = Math.round(regression_data[i][1]);
	    }
	    return trend_line_data;
	}

	function arrayAddition(array1, array2) {
	    var result = [];

	    for (var i = 0; i < array1.length; i++) {
	        result[i] = array1[i] + array2[i];
	    }

	    return result;
	}

    //END
 })