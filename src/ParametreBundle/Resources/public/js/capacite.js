$(function() {
    var window_height = window.innerHeight;
    $('.panel-body.operateurs').height(window_height - 250);
    $('#operateur-list').height(window_height - 280);
    $('#operateur-capacite').removeClass('hidden').hide();
    $('.loader').hide();
    updateOperateurCount();

    /** CAPACITE PAR POSTE */
    $(document).on('click', '#btn-save-capacite-poste', function(event) {
        event.preventDefault();
        var postes = [];
        $('#poste-list').find('tr[data-poste]').each(function(index, item) {
           postes.push({
               id: $(this).attr('data-poste'),
               capacite: $(this).find('input[type="text"]').val().trim()
           });
        });

        var url = Routing.generate('parametre_capacite_update');
        var body = new FormData();
        body.append('postes', JSON.stringify(postes));
        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: body
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            if (data.erreur === false) {
                show_info('', "Paramètres enregistrés.", 'info');
            } else {
                show_info('', "Erreur lors de l'enregistrement des paramètres.", 'error', 5000);
            }
        }).catch(function(error) {
            console.log(error);
        });
    });

    /** CAPACITE PAR PERSONNE */
    $(document).on('click', '.list-operateur-item', function(event) {
       event.preventDefault();
       var operateur_id = $(this).attr('data-operateur');
       $('#operateur-list')
           .find('.list-operateur-item')
           .removeClass('active');
       $(this).addClass('active');
       $('#operateur-capacite').fadeOut(500);
       $('#loader2').fadeIn(500);
       var url = Routing.generate('parametre_capacite_par_operateur', {operateur: operateur_id});
       fetch(url, {
           method: 'GET',
           credentials: 'include'
       }).then(function(response) {
           return response.json();
       }).then(function(data) {
           $('#poste-title').text(data.poste);
           $('#operateur-nom').text(data.nom);
           $('#poste-capacite').text(data.capacite);
           $('#operateur-coeff').val(data.coefficient);
           $('#selected-operateur').val(data.operateur_id);
           $('#selected-operateur-poste').val(data.poste_id);
           $('#operateur-capacite').fadeIn(500);
           $('#loader2').fadeOut(500);
       }).catch(function(error) {
           console.log(error);
       });
    });

    /** ENREGISTRER CAPACITE PAR PERSONNE */
    $(document).on('click', '#btn-save-capacite-operateur', function(event) {
        event.preventDefault();
        var body = new FormData(),
            operateur = $('#selected-operateur').val(),
            poste = $('#selected-operateur-poste').val(),
            coefficient = $('#operateur-coeff').val();

        if (!poste || poste === '' || poste === '0') {
            show_info('', "Cette personne n'a pas encore de poste.<br>Il faut d'abord lui donner un poste", 'error', 10000);
            return 0;
        }

        var url = Routing.generate('parametre_capacite_par_operateur_update', {operateur: operateur});
        body.append("coeff", coefficient);
        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: body
        }).then(function(response) {
            return response.json();
        }).then(function() {
            show_info('', "Paramètres enregistrés", 'info');
        }).catch(function(error) {
           console.log(error);
        });
    });

    /** MENU CONTEXTUEL AFFECTATION POSTE */
    var url = Routing.generate('parametre_capacite_menu_poste');
    fetch(url, {
        method: 'GET',
        credentials: 'include'
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        $.contextMenu({
            selector: '.poste-affectation',
            className: 'menu-css-title',
            trigger: 'left',
            build: function ($trigger, e) {
                return {
                    callback: function (key, options) {
                        var new_poste = key.replace(/_/g, "").trim();
                        new_poste = new_poste !== '' ? new_poste : 0;
                        var operateur_id = $trigger.closest('.list-operateur-item').attr('data-operateur');
                        url = Routing.generate('parametre_capacite_poste_update', {operateur: operateur_id});
                        body = new FormData();
                        body.append('poste', new_poste);
                        $('#loader1').fadeIn(500);
                        fetch(url, {
                            method: 'POST',
                            credentials: 'include',
                            body: body
                        }).then(function(response) {
                            return response.json();
                        }).then(function() {
                            var operateur_item = $trigger.closest('.list-operateur-item').detach();
                            var new_poste_group = $(document)
                                .find('[data-poste=' + new_poste + ']');
                            var list_operateur = new_poste_group.find('.list-operateur');
                            if (list_operateur.length) {
                                operateur_item.appendTo(list_operateur);
                            } else {
                                new_poste_group
                                    .find('.panel-body')
                                    .html('<ul id="list-group-' + new_poste + '" class="list-operateur"></ul>');
                                list_operateur = new_poste_group.find('.list-operateur');
                                operateur_item.appendTo(list_operateur);
                            }
                            updateOperateurCount();
                            $('#loader1').fadeOut(500);
                        }).catch(function(error) {
                            console.log(error);
                        });
                    },
                    items: data
                };
            }
        });
    }).catch(function(error) {
        console.log(error);
    });


    function updateOperateurCount() {
        /** NOMBRE D'OPERATEUR PAR POSTE */
        $(document).find('.operateur-count').each(function(index, item) {
            var operateur_count = $(item),
                panel = operateur_count.closest('.panel');
            operateur_count.text(panel.find('.list-operateur-item').length);
        });
    }
});