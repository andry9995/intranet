/**
 * Repartitions
 */
 $(function() {

     function _export(type) {
         var graphe = $('#graphe-reputaion');

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


   $('#export-print-reputaion').click(function () {
        _export(0);
    }); 

    $('#export-png-reputaion').click(function () {
        _export(1);
    });

    $('#export-jpeg-reputaion').click(function () {
        _export(2);
    });  

    $('#export-pdf-reputaion').click(function () {
        _export(3);
    }); 

    $('#export-csv-reputaion').click(function () {
        _export(4);
    }); 

    $('#export-xls-reputaion').click(function () {
        _export(5);
    });

 	$('#reputaion-nav').on('click',function() {
        //resize_graphe_reputation();
    })

    $(window).resize(function() {
        //resize_graphe_reputation();
    });

    function resize_graphe_reputation() {

    	setTimeout(function() {

	        pie_chart = $('#graphe-reputaion');

	        //var width_chart = pie_chart.closest(".panel-body").width();

	        var width_chart = $("#master").width();

	        var isBig = $(window).width() > 900;
	        
	        var chart = pie_chart.highcharts();

	        pie_chart.css({
	            width: width_chart,
	        })
    	
    	},600);

    }

    $('#btn_get_images_reputation').on('click', function(event) {

        event.preventDefault();
        event.stopPropagation();

        go_reputation();


     });

    function go_reputation() {
    
	    var client = $('#client-reputation').val();

	    var exercice = $('#exercice-reputation').val();

	    if (client == '' || exercice == '') {
	        show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
	        return false;
	    }
	    
	    else {

	        var url = Routing.generate('revision_reputation_images', {
	            client: client,
	            exercice: exercice
	        });

	        $.ajax({
	            url: url,
	            type: "GET",
	            dataType: "json",
	            success: function(response) {

	                var pie = instance_pie(response);

	            }
	        });
	    }

	    //resize_graphe_reputation()
	}

	var exercice_reputation_selector = $('#exercice-reputation');

	var client_reputation_selector = $('#client-reputation');

	var current_year_m_1 = (new Date()).getFullYear() - 1;
	var current_year_m_2 = (new Date()).getFullYear() - 2;
	var current_year = (new Date()).getFullYear();
	var current_year_1 = (new Date()).getFullYear() + 1;
	var current_year_2 = (new Date()).getFullYear() + 2;

	exercice_reputation_selector.append('<option value="' + current_year_m_2 + '">' + current_year_m_2 + '</option>');
	exercice_reputation_selector.append('<option value="' + current_year_m_1 + '">' + current_year_m_1 + '</option>');
	exercice_reputation_selector.append('<option value="' + current_year + '" selected="">' + current_year + '</option>');
	exercice_reputation_selector.append('<option value="' + current_year_1 + '">' + current_year_1 + '</option>');
	exercice_reputation_selector.append('<option value="' + current_year_2 + '">' + current_year_2 + '</option>');

	function instance_pie(data) {

    	var subtitle = '';

	    if ($('#client-reputation').val() == 0) {
	        subtitle = '(Par Client)';
	    }
	    else{
	        var client = '';
	        if (data.length != 0) {
	            client = data[0].client;
	        }

	        subtitle = '(Par Dossier du client ' + client + ')';
	    }

    	var width = $('#graphe-reputaion').closest(".panel-body").width() - 200;

    	var isBig = $(window).width() > 900;
        
        var legendBig = {
            align: 'right',
            verticalAlign: 'middle',
            layout: 'vertical'
        };
        
        var legendSmall = {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom'
        }

        var options =  {
          chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
          },
          scrollbar: {
                enabled: true
            },
          legend: legendBig,
          title: {
            text: 'Répartitions',
            align: 'left'
          },
          subtitle: {
            text: subtitle,
            align: 'left'
            },
          plotOptions: {
            pie: {
              allowPointSelect: true,
              cursor: 'pointer',
              dataLabels: {
                enabled: false
              },
              showInLegend: true
            }
          },
          series: [{
            name: 'Images',
            colorByPoint: true,
            data: data
          }],
          credits: false,
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
        }

        var pie_chart = $("#graphe-reputaion").highcharts();

        if (pie_chart == undefined) {

            var pie = Highcharts.chart('graphe-reputaion', options);

            return pie;

        } else {
            delete pie_chart;
            pie_chart = $("#graphe-reputaion").highcharts();
            
            var pie = Highcharts.chart('graphe-reputaion', options);

            return pie;
        }

	}

	$('.chosen-select-client-reputation').chosen({
	    no_results_text: "Aucun client trouvé:",
	    search_contains: true,
	    width: '100%'
	});

 	//END
 })