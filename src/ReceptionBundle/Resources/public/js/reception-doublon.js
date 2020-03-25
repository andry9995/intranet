/**
 * Created by INFO on 23/07/2018.
 */

$(function () {
	$('#outils').hide();
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

	$('#editable').dataTable({
		"pageLength": 50,
		"language": {
            "sProcessing": "Traitement en cours ...",
            "sLengthMenu": "Afficher _MENU_ lignes",
            "sZeroRecords": "Aucun résultat trouvé",
            "sEmptyTable": "Aucune donnée disponible",
            "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
            "sInfoEmpty": "Aucune ligne affichée",
            "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
            "sInfoPostFix": "",
            "sSearch": "Chercher:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Chargement...",
            "oPaginate": {
              "sFirst": "Premier", "sLast": "Dernier", "sNext": "<i class='fa fa-angle-right'></i>", "sPrevious": "<i class='fa fa-angle-left'></i>"
            },
            "oAria": {
              "sSortAscending": ": Trier par ordre croissant", "sSortDescending": ": Trier par ordre décroissant"
            }
          }
	});
    var grid_tache = $('#js_tache_liste');
    var window_height = window.innerHeight;
    var grid_width = grid_tache.closest('.row').width() - 50;
    
	//Changement categorie
    $("#categorie").change(function() {
        if($(this).val() == "9") {
            $("#btn_trou").show();
			$("#btn_trou_doubl").show();
			$('#troutable').DataTable().destroy();
			$("#btn_doublon").hide();
			$('#editable').DataTable().destroy();
			$('#troutable').dataTable({
				"pageLength": 50,
				"language": {
	            "sProcessing": "Traitement en cours ...",
	            "sLengthMenu": "Afficher _MENU_ lignes",
	            "sZeroRecords": "Aucun résultat trouvé",
	            "sEmptyTable": "Aucune donnée disponible",
	            "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
	            "sInfoEmpty": "Aucune ligne affichée",
	            "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
	            "sInfoPostFix": "",
	            "sSearch": "Chercher:",
	            "sUrl": "",
	            "sInfoThousands": ",",
	            "sLoadingRecords": "Chargement...",
	            "oPaginate": {
	              "sFirst": "Premier", "sLast": "Dernier", "sNext": "<i class='fa fa-angle-right'></i>", "sPrevious": "<i class='fa fa-angle-left'></i>"
	            },
	            "oAria": {
	              "sSortAscending": ": Trier par ordre croissant", "sSortDescending": ": Trier par ordre décroissant"
	            }
	          }
			});
			$('#editable').hide();
			$('#troutable').show();
        }else {
			$('#outils').hide();
            $("#btn_trou").hide();
			$("#btn_trou_doubl").hide();
			$("#btn_doublon").show();
			$('#troutable').DataTable().destroy();
			$('#editable').show();
			$('#editable').DataTable().destroy();
			$('#editable').dataTable({
				"pageLength": 50,
				"language": {
	            "sProcessing": "Traitement en cours ...",
	            "sLengthMenu": "Afficher _MENU_ lignes",
	            "sZeroRecords": "Aucun résultat trouvé",
	            "sEmptyTable": "Aucune donnée disponible",
	            "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
	            "sInfoEmpty": "Aucune ligne affichée",
	            "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
	            "sInfoPostFix": "",
	            "sSearch": "Chercher:",
	            "sUrl": "",
	            "sInfoThousands": ",",
	            "sLoadingRecords": "Chargement...",
	            "oPaginate": {
	              "sFirst": "Premier", "sLast": "Dernier", "sNext": "<i class='fa fa-angle-right'></i>", "sPrevious": "<i class='fa fa-angle-left'></i>"
	            },
	            "oAria": {
	              "sSortAscending": ": Trier par ordre croissant", "sSortDescending": ": Trier par ordre décroissant"
	            }
	          }
			});
			$('#troutable').hide();
        }
    });
	
	
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

    /*Changement dossier
    $(document).on('change', '#dossier', function (event) {
        event.preventDefault();
        event.stopPropagation();
	   	url = Routing.generate('reception_doublon_dossier');
		var idata = {};
			idata['dossier'] = dossier_selector.val();
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
			}
		});
    });*/
	

    //Affichage Tache par dossier
    $(document).on('click', '#btn-tache-dossier', function(event) {
        event.preventDefault();
        var dossier_id = $('#dossier').val();

        if (dossier_id !== '') {
            reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_dossier', {dossier: dossier_id}));
        } else {
            $("#js_tache_liste").jqGrid('clearGridData');
        }
    });

    // Show fourchette
    $("#js_filtre_periode").change(function() {

        if($(this).val() == "5") {
            $("#js_filtre_fourchette").show();
            $("#js_filtre_fourchette").css({display: "block"});
        }else {
             $("#js_filtre_fourchette").hide();
        }
    });
	
	//Compress
    $('#compress').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$.fn.dataTable.ext.search.push(
		function( settings, data, dataIndex ) {
			var affi = parseFloat( data[1] ) || 0; 
			 if ( affi != 0 )
			{
				return true;
			}
			return false;
		}
		);
		$('#troutable').DataTable().draw();
		$('#compress').hide();
		$('#expand').show();
	});
	//expand
	$('#expand').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$.fn.dataTableExt.afnFiltering.pop(); 
		$('#troutable').DataTable().draw();
		$('#compress').show();
		$('#expand').hide();
	});
	
    // Go for doublon
    $('#btn_doublon').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var categorie = $('#categorie').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val();
        
		var critere =[];		
		if ($('#raison').is(':checked')){
			critere.push(1);
		}
		if ($('#date').is(':checked')){
			critere.push(2);
		}
		if ($('#num').is(':checked')){
			critere.push(3);
		}
		if ($('#montant').is(':checked')){
			critere.push(4);
		}
        if( categorie ==  '' || dossier == '' || exercice == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
            $.ajax({
                url: Routing.generate('reception_doublon_liste'),
                type: 'POST',
                data: {
                    dossier: dossier,
                    exercice : exercice,
                    categorie  : categorie,
					critere :  JSON.stringify(critere)
                },
                success: function (data) {
					if(data.length==0){
						show_info('Controle doublon', 'Aucun resultat pour la recherche', 'warning');	
					}
					$('#outils').hide();
					$('#editable').DataTable().destroy();
					$('#retourListe').html(data);
					$('#editable').dataTable( {
						"pageLength": 50,
						"language": {
				            "sProcessing": "Traitement en cours ...",
				            "sLengthMenu": "Afficher _MENU_ lignes",
				            "sZeroRecords": "Aucun résultat trouvé",
				            "sEmptyTable": "Aucune donnée disponible",
				            "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
				            "sInfoEmpty": "Aucune ligne affichée",
				            "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
				            "sInfoPostFix": "",
				            "sSearch": "Chercher:",
				            "sUrl": "",
				            "sInfoThousands": ",",
				            "sLoadingRecords": "Chargement...",
				            "oPaginate": {
				              "sFirst": "Premier", "sLast": "Dernier", "sNext": "<i class='fa fa-angle-right'></i>", "sPrevious": "<i class='fa fa-angle-left'></i>"
				            },
				            "oAria": {
				              "sSortAscending": ": Trier par ordre croissant", "sSortDescending": ": Trier par ordre décroissant"
				            }
				        },
						"drawCallback": function( settings ) {
							  $('.set_doublon').on('click', function (event) {
								 event.preventDefault();
								  var eli = $(this);
									$.ajax({
										url: Routing.generate('reception_set_doublon'),
										type: 'POST',
										data: {
											tid: $(this).attr('data-id')
										},
									success: function (data) {
										if (data.i==1){
											eli.html('Doublon');
										} else {
											eli.html('Normal');
										}
										
										$('#tid'+data.tid).html(data.nom);
									}	
									});		
									return false;
								});	
						}
					});			
                }
            });
        }
        return false;
    });
	//go for facture doublon
	$('#btn_trou_doubl').on('click', function (event) {
		var categorie = $('#categorie').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val();
        
		var critere =[];		
		if ($('#raison').is(':checked')){
			critere.push(1);
		}
		if ($('#date').is(':checked')){
			critere.push(2);
		}
		if ($('#num').is(':checked')){
			critere.push(3);
		}
		if ($('#montant').is(':checked')){
			critere.push(4);
		}
		$('#troutable').DataTable().destroy();
			$('#outils').hide();
			$('#editable').show();
			$('#editable').DataTable().destroy();
			$('#editable').dataTable({
										"pageLength": 50
									});
			$('#troutable').hide();
        if( categorie ==  '' || dossier == '' || exercice == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
            $.ajax({
                url: Routing.generate('reception_doublon_liste'),
                type: 'POST',
                data: {
                    dossier: dossier,
                    exercice : exercice,
                    categorie  : categorie,
					critere :  JSON.stringify(critere)
                },
                success: function (data) {
					if(data.length==0){
						show_info('Controle doublon', 'Aucun resultat pour la recherche', 'warning');	
					}
					$('#editable').DataTable().destroy();
					$('#retourListe').html(data);
					$('#editable').dataTable( {
						"pageLength": 50,
						"drawCallback": function( settings ) {
							  $('.set_doublon').on('click', function (event) {
								 event.preventDefault();
								  var eli = $(this);
									$.ajax({
										url: Routing.generate('reception_set_doublon'),
										type: 'POST',
										data: {
											tid: $(this).attr('data-id')
										},
									success: function (data) {
										if (data.i==1){
											eli.html('Doublon');
										} else {
											eli.html('Normal');
										}
										
										$('#tid'+data.tid).html(data.nom);
									}	
									});		
									return false;
								});	
						}
					});			
                }
            });
        }
        return false;
	});		
	 // Go for trou
    $('#btn_trou').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var categorie = $('#categorie').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val();

		if( categorie ==  '' || dossier === null || exercice == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
			$('#troutable').DataTable().destroy();
			$('#editable').DataTable().destroy();
			$('#editable').hide();
			$('#troutable').show();
			$('#outils').show();
            $.ajax({
                url: Routing.generate('reception_trou_liste'),
                type: 'POST',
                data: {
                    dossier: dossier,
                    exercice : exercice,
                    categorie  : categorie
                },
                success: function (data) {
					if(data.length==0){
						show_info('Controle de trou', 'Aucun resultat pour la recherche', 'warning');	
					}
					$('#retourTrou').html(data);
					$('#troutable').dataTable({
										"pageLength": 50, 
										"ordering": false
									});
                }
            });
        }
        return false;
    });
	
});

	
