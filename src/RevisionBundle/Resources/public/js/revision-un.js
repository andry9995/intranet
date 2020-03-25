/**
 * Created by INFO on 23/07/2018.
 */

$(function () {

    var dateList = [],
        erreur_list = [],
        is_tache_legale;
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
  

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
		categorie_selector = $('#categorie'),
		exercice_selector = $('#exercice'),
        loader_selector = $('#loader');

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    

    var grid_tache = $('#js_tache_liste');
    var window_height = window.innerHeight;
    var grid_width = grid_tache.closest('.row').width() - 50;
    
	
    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        url = Routing.generate('reception_doublon_dossier');
		var idata = {};
			idata['client'] = client_selector.val();
			$.ajax({
			url:url,
			type: "POST",
			dataType: "json",
			data: {
				"idata": JSON.stringify(idata)
			},
			async: true,
			success: function (data)
			{	
				$("#dossier option").remove();
				data.dossiers.forEach(function(d) {
					dossier_selector.append('<option value="'+d.id+'">'+d.nom+'</option>');
					dossier_selector.chosen({
							no_results_text: "Aucun client trouvé:",
							search_contains: true,
							width: '100%'
						});
					dossier_selector.val('').trigger('chosen:updated');

				});	
			}
		});
    });

    // Changement dossier
    $(document).on('change', '#dossier', function (event) {
        event.preventDefault();
        event.stopPropagation();
	    loader_selector.show();
	   	url = Routing.generate('revision_un_rev');
			$.ajax({
			url:url,
			type: "POST",
			dataType: "json",
			data: {
				"did": dossier_selector.val()
			},
			async: true,
			success: function (data)
			{	
				$("#exercice option").remove();
				data.forEach(function(d) {
					exercice_selector.append('<option value="'+d+'">'+d+'</option>');
				});	
			   loader_selector.hide();
			}
		});
    });
	//go for valider
	
	$('#btn_valider').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$.ajax({
                url: Routing.generate('revision_un_rev'),
                type: 'POST',
                data: {
					imagid:$('#imagid').val(),
                    c : $('#icategorie').val(),
                    sc : $('#isouscategorie').val(),
                    ssc  : $('#isoussouscategorie').val()
                },
				success: function (data) {
					show_info("", "La mise à jour a été effectuée", "success");
				}
		});	
		return false;
    });	
		
	 // Go for rev
    $('#btn_rev').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$('#imagable').dataTable();
        var categorie = $('#categorie').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val();
			stati = $('#status').val();
       
        if(dossier=='' || exercice == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
            $.ajax({
                url: Routing.generate('revision_un_rev'),
                type: 'POST',
                data: {
                    dossier: dossier,
                    exercice : exercice,
                    categorie  : categorie,
					stati : stati
                },
                success: function (data) {
					$("canvas#chart-area").remove();
					$("#canvas-holder").append('<canvas id="chart-area"></canvas>');
					var config = {
						type: 'radar',
						data: {
							labels: ['reçues', 'saisies', 'imputées', 'révisées','en instance', 'autres', 'a réviser'],
							datasets: [{
								label: 'Nombres',
								backgroundColor: 'rgba(54, 162, 235,0.2)',
								borderColor: 'rgb(54, 162, 235)',
								pointBackgroundColor: 'rgb(255, 99, 132)',
								data: []
							}]
						},
						options: {
							legend: {
								display: false,
							},
							title: {
								display: true,
								text: 'Pieces à réviser'
							},
							scale: {
								ticks: {
									beginAtZero: true
								}
							}
						}
					};
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.recu);
						});
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.saisie);
						});
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.impute);
						});
						
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.previse);
						});
						
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.instance);
						});

						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.autres);
						});
						config.data.datasets.forEach(function(dataset) {
							dataset.data.push(data.parevise);
						});

					myRadar = new Chart(document.getElementById('chart-area'), config);	
					$("#information").show();
					$("#generale").html(data.generale);
					$("#mandataire").html(data.mandataire);
					$("#comptable").html(data.comptable);
					$("#fiscale").html(data.fiscale);
					$("#isaisie").html(data.isaisie);
					$("#idossier").html(data.idossier);
					$("#precu").html(data.recu);
					$("#psaisie").html(data.saisie);
					$("#pimpute").html(data.impute);
					$("#pinstance").html(data.instance);
					$("#pautres").html(data.autres);
					$("#previse").html(data.previse);
					$("#parevise").html(data.parevise);
					$("#rmanquant").html(data.rmanquant);
					$('#imagable').DataTable().destroy();
					$('#retourListe').html(data.imaging);
					$('#pdf').hide();
					$('#imagable').dataTable({
						"paging": false,
						"drawCallback": function( settings ) {
							$('.set_image').on('click', function (event) {
							 event.preventDefault();
							 $('#pdf').show();
							 PDFObject.embed($(this).attr('rel'), "#pdf");
							 $('#imagid').val($(this).attr('data-id'));
							 $.ajax({
									url: Routing.generate('revision_un_rev'),
									type: 'POST',
									data: {
										iid: $(this).attr('data-id')
									},
								success: function (data) {
									$('#icategorie').val(data.c);
									$('#isouscategorie').val(data.sc);
									$('#isoussouscategorie').val(data.ssc);
								}	
								});		
							});	
						}
					});			
                }
            });
        }
        return false;
    });
	
	
		
		
		

	/*var config = {
			type: 'pie',
			data: {
				datasets: [{
					data: [
						430000,
						350000,
						255000,
						121000,
						5000000,
					],
					backgroundColor: [
						'rgb(255, 99, 132)',
						'rgb(255, 159, 64)',
						'rgb(255, 205, 86)',
						'rgb(75, 192, 192)',
						'rgb(54, 162, 235)'
					],
					label: 'Dataset 1'
				}],
				labels: [
					'Impute',
					'Orange',
					'Yellow',
					'Green',
					'Blue'
				]
			},
			options: {
				responsive: true
			}
		};

		var ctx = document.getElementById("chart-area").getContext("2d");
        var myPie =  new Chart(ctx, config);
		myPie.data.datasets.forEach((dataset) => {
        dataset.data.pop();
    });
    myPie.update();*/
	
	   
	
});

	
