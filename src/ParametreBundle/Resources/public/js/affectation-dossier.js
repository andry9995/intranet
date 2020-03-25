$(function () {
    var window_height = window.innerHeight,
        affectation_container = $('#affectation-container'),
        loader = $('#loader'),
        loader2 = $('#loader2');

    affectation_container.height(window_height - 115);
    var responsable_item = '<li class="dd-item responsable-item" data-id="id-responsable">' +
        '   <button class="dd-action pull-right remove-responsable-item" type="button" data-action="remove" title="Supprimer">x</button>' +
        '   <div class="dd-handle"><span class="responsable-nom">Nom responsable</span></div>' +
        '</li>';
	var client_item = '<li class="dd-item client-item" data-id="id-client">' +
        '   <button class="dd-action pull-right remove-responsable-item removet" type="button" data-action="remove" title="Supprimer">x</button>' +
        '   <div class="dd-handle"><span class="client-nom">Nom responsable</span></div>' +
        '</li>';
    var utilisateur_select = $('#utilisateur'),
		client_select = $('#client'),
        responsable_nestable = $('#responsable-nestable'),
        responsable_list = $('#responsable-list');
		client_list = $('#client-list');
    // responsable_list.height(window_height - 380);
	
	
    responsable_nestable.nestable({
        group: 1,
        maxDepth: 4,
        onRemove: function(el) {
            removeResponsable(el);
        },
        verifyRemoveButtons: function(el) {
            $.each(el.find('.dd-item'), function(index, item) {
               if (!$(item).find('button[data-action="remove"]').length) {
                   $(item).prepend('<button class="dd-action pull-right remove-responsable-item" type="button" data-action="remove" title="Supprimer">x</button>');
               }
            });
        }
    }).on('change', function (e, el) {
		$(".dd-handle" ).each(function() {
			$( this ).css( "background-color", "#f5f5f5" );
			$( this ).css( "color", "#333" );
		});
		var item1 = $( ".dd-handle" )[ 0 ];
		$(el).find(".dd-handle").eq(0).css( "color", "#fff" );
		$(el).find(".dd-handle").eq(0).css( "background-color", "#30849c" );
        if ($(el).attr('data-id')) {
			$("#idresp").val($(el).attr('data-id'));
			client_list.empty();
			url = Routing.generate('parametre_client_responsable');
			$.ajax({
                url:url,
                type: "POST",
                dataType: "json",
                data: {
                    "idresp": $(el).attr('data-id')
                },
                async: true,
                success: function (data)
                {
                    data.forEach(function(d) {
						 var new_item = $(client_item);
							 new_item.attr('data-id', d.id);
							 new_item.find('.client-nom').text(d.nom);
							 new_item.appendTo(client_list);
					});	
					$('.removet').on('click', function (e) {
						var clientsup = $(this).closest("li").attr('data-id');
						$(this).closest("li").remove();
						swal({
							title: 'Enlever cet client ?',
							text: "Le client sera aussi enlevé pour les responsables qui sont au dessus.",
							type: 'warning',
							showCancelButton: true,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							reverseButtons: true,
							confirmButtonText: 'Oui, enlever',
							cancelButtonText: 'Annuler'
						}).then(function (result) {
							if(result) {
								var idata = {};
									idata['clients'] = clientsup;
									idata['responsable']=$(el).attr('data-id');
				
								url = Routing.generate('parametre_affectation_dossier_client_sup');
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
									   loader2.hide();
									   show_info("", "La suppression a été effectuée", "success");
									}
								});
							}
						}).catch(function() {

						});
					});	
                }
            });
            showListeClient($(el).attr('data-id'));
        }
    });

    //Ajouter un responsable
    $('#btn-add-operateur').on('click', function (e) {
        e.preventDefault();
        if (utilisateur_select.val() !== 'undefined' && utilisateur_select.val() !== '') {
            var id = utilisateur_select.val(),
                nom = utilisateur_select.find('option:selected').text();

            if (responsable_list.find('[data-id="' + id + '"]').length === 0) {
                var new_item = $(responsable_item);
                new_item.attr('data-id', id);
                new_item.find('.responsable-nom').text(nom);
                new_item.appendTo(responsable_list);
                utilisateur_select.val('');
            } else {
                show_info("", "L'utilisateur " + nom + " est déjà dans liste des responsables", "warning");
            }
        }
    });
	
	//Ajouter client
	
	$('#btn-add-client').on('click', function (e) {
        e.preventDefault();
		if (client_select.val() !== 'undefined' && client_select.val() !== '') {
            var id = client_select.val(),
                nom = client_select.find('option:selected').text();

            if (client_list.find('[data-id="' + id + '"]').length === 0) {
                var new_item = $(client_item);
				new_item.attr('data-id', id);
				new_item.find('.client-nom').text(nom);
				new_item.appendTo(client_list);
                client_select.val('');
				$('.removet').on('click', function (e) {
					(this).closest("li").remove();
				});	
            } else {
                show_info("", "Le client " + nom + " est déjà dans liste des clients", "warning");
            }
        }
		
	});	
	
	
    //Enregistrer client
    $('#btn-save-client').on('click', function (e) {

        e.preventDefault();
		if ($("#idresp").val().length >0){
			loader2.show();
			var idata = {};idata['clients'] = [];idata['responsable']=$("#idresp").val();
				
				$('#client-nestable li').each(function(index,value){				
					idata['clients'].push($(this).attr('data-id').toString());
				});
				url = Routing.generate('parametre_affectation_dossier_client_edit');
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
                   loader2.hide();
				   show_info("", "La mise à jour a été effectuée", "success");
                }
            });
		}
		else {
			show_info("", "Veuillez choisir un responsable", "warning");
		}
    });

    //Enregistrer liste modification
    $('#btn-save-responsable').on('click', function (e) {
        loader.show();
        e.preventDefault();
        var responsables = responsable_nestable.nestable('serialize');

        var formData = new FormData(),
            url = Routing.generate('parametre_affectation_dossier_responsable_edit');
        formData.append('responsables', JSON.stringify(responsables));
        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function (response) {
            return response.json();
        }).then(function () {
            loader.hide();
			show_info("", "La mise à jour a été effectuée", "success");
        }).catch(function () {
            loader.hide();
            show_info("", "Une erreur est survenue", "error");
        });
    });

    //Afficharge liste clients
    function showListeClient(user_id) {
        if (!user_id) {
            return false;
        }

    }

    //Supprimer un responsable
    function removeResponsable(item) {
        swal({
            title: 'Enlever cet utilisateur ?',
            text: "Tous ceux qui sont en dessous seront aussi enlevés.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: true,
            confirmButtonText: 'Oui, enlever',
            cancelButtonText: 'Annuler'
        }).then(function (result) {
            if(result) {
                item.remove();
            }
        }).catch(function() {

        });
    }
});	