$(function() {
	$('#exercice').val("2018");
    var window_height = window.innerHeight;
    $('#liste-container').height(window_height - 140);

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader'),
        table_list_grid = $('#table-list');
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
   

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    
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
				dossier_selector.append('<option value="">Tous</option>');
				data.dossiers.forEach(function(d) {
					dossier_selector.append('<option value="'+d.id+'">'+d.nom+'</option>');
					dossier_selector.chosen({
							no_results_text: "Aucun client trouvé:",
							placeholder_text_single: "Tous",
							search_contains: true,
							width: '100%'
						});
				});
				dossier_selector.val("").trigger('chosen:updated');	
			}
		});
    });

    // Changement site
    $(document).on('change', '#site', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, function() {
            getListe();
        });
    });

    //Changement view
    $('.btn-change-view').on('click', function() {
       $(this).closest('#change-view')
           .find('.btn-change-view')
           .removeClass('active');
       $(this).addClass('active');
       var id = $(this).attr('id');
       if (id === 'btn-show-box') {
           $('#box-list').removeClass('hidden');
           $('#table-list-container').addClass('hidden');
       } else {
           $('#table-list-container').removeClass('hidden');
           $('#box-list').addClass('hidden');
       }
    });

    /**
     * Tableau liste
     */
    table_list_grid.jqGrid({
        datatype: 'local',
        loadonce: true,
        sortable: true,
        height: window_height - 320,
        shrinkToFit: false,
        viewrecords: true,
        hidegrid: false,
        colNames: [
            'Clients', 'Dossiers', 'Resp dossier','date demande client', 'prochaine tache demandée', 'prochaine Tache scriptura', 'Stock a traiter',
            'delai estimé', 'Banque', 'DRP - DRT envoyée', 'Mails', 'Priorite', 'date import export'
        ],	
        colModel: [
            {name: 'db-client', index: 'db-client', align: 'left', editable: false, sortable: true, width: 200, classes: 'js-db-client'},
            {name: 'db-dossier', index: 'db-dossier', align: 'left', editable: false, sortable: true, width: 200, classes: 'js-db-dossier'},
			{name: 'db-resp-dossier', index: 'db-resp-dossier', align: 'left', editable: false, sortable: true, width: 200, fixed: true, classes: 'js-db-resp-dossier'},
			{name: 'db-date-demande', index: 'db-date-demande', align: 'center', editable: false, sortable: true, width: 100, fixed: true, classes: 'js-db-date-demande'},
			{name: 'db-proc-demande', index: 'db-proc-demande', align: 'center', editable: false, sortable: true, width: 100, fixed: true, classes: 'js-db-proc-demande'},
			{name: 'db-proc-scriptura', index: 'db-proc-scriptura', align: 'center', editable: false, sortable: true, width: 100, fixed: true, classes: 'js-db-proc-scriptura'},
            {name: 'db-stock', index: 'db-stock', align: 'center', editable: false, sortable: true, width: 100, classes: 'js-db-stock'},
            {name: 'db-delai-estime', index: 'db-delai-estime', align: 'center', editable: false, sortable: true, width: 100, fixed: true, classes: 'js-db-delai-estime'},
			{name: 'db-banque', index: 'db-banque', align: 'center', editable: false, sortable: true, width: 150, classes: 'js-db-banque'},
            {name: 'db-drt', index: 'db-drt', align: 'center', editable: false, sortable: true, width: 100, classes: 'js-db-drt'},
            {name: 'db-mail-dossier', index: 'db-mail-dossier', align: 'center', editable: false, sortable: true, width: 100, classes: 'js-db-mail-dossier'},
            {name: 'db-priorite', index: 'db-priorite', align: 'center', editable: false, sortable: true, width: 100, classes: 'js-db-priorite'},
            {name: 'db-import-export',index: 'db-import-export',align: 'center', editable: false, sortable: true, width: 100, classes: 'js-db-import-export'}

        ],
        loadComplete: function() {

        }
    });

    // Affichage liste
    $('#btn-show-list').on('click', function() {
		event.preventDefault();
        event.stopPropagation();
		 var client = $('#client').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val();

        
        if(client == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }
        getListe();
    });

    //Affichage détail box
    $('#box-list').on('click', '.card', function() {
        $('#box-detail').modal('show');
    });
	
    function getListe() {
        table_list_grid.jqGrid('clearGridData');
        loader_selector.show();
        $('#box-list').empty();
        var client = client_selector.val(),
            dossier = dossier_selector.val() && dossier_selector.val() !== '' ? dossier_selector.val() : 0,
			exercice = $('#exercice').val() && $('#exercice').val() !== '' ? $('#exercice').val() : 0,
            url = Routing.generate('revision_dashboard_list', {client: client, dossier: dossier,exercice: exercice});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            console.log(data);
            var liste = '',
                row_data = [];
            data.forEach(function(item) {
                var tache = '',
                    delai = '';
                if (item.tache.tache) {
                    tache = item.tache.tache;
                    if (item.tache.delai) {
                        delai = moment(item.tache.delai.date).format('DD/MM/YYYY');
                    }
                }
                liste += '<div class="col-sm-3 col-md-2">' +
                            '<div class="card" data-id="' + item.id + '">' +
                                '<h4>' + item.dossier + '</h4>' +
                                '<p><i class="fa fa-file-image-o"></i> 200</p>' +
                                '<p><i class="fa fa-calendar-check-o"></i> ' + delai + '</p>' +
                                '<p><i class="fa fa-tasks"></i> ' + tache + '</p>' +
                            '</div>' +
                        '</div>';
				var stock ='<a href="" id="t'+item.id+'" data-toggle="popover" title="Pieces à réviser" data-placement="bottom" data-trigger="hover" >'+item.stocka+'</a>';
				var rmanq ='<a href="" id="r'+item.id+'" data-toggle="popover" title="Situation banque" data-placement="bottom" data-trigger="hover" >'+item.rmanqa+'</a>';
                row_data.push({
                    'db-client': item.client,
                    'db-dossier': item.dossier,
					'db-stock':stock,
                    'db-proch-ech': delai,
					'db-resp-dossier': item.resp,
					'db-priorite': item.priorite,
					'db-banque': rmanq,
                });
            });
            table_list_grid.jqGrid('setGridParam', {
                datatype: 'local',
                data: row_data
            }).trigger('reloadGrid', [{ page: 1 }]);
			data.forEach(function(item) {
				$('#t'+item.id).attr("data-content", item.stock);					
				$('#r'+item.id).attr("data-content", item.rmanq);
			});
			$('[data-toggle = "popover"]').popover({html : true});
            $('#box-list').html(liste);
			$('.prior').on('click', function() {
				$('#priocontent').hide();
				$('#headt').hide();
				$('#attente').show();
				$.ajax({
					url: Routing.generate('revision_dashboard_lot'),
					type: 'POST',
					data: {
						iid: $(this).attr('data-id'),
						exercice:$('#exercice').val()
					},
				success: function (data) {
					$('#priocontent').html(data);
					$('#priocontent').show();
					$('#attente').hide();
					$('#headt').show();
				}	
				});	
			});
            loader_selector.hide();
        }).catch(function(error) {
            console.log(error);
            loader_selector.hide();
        })
    }

});