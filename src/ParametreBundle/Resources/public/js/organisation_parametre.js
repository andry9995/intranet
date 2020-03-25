$(function() {
    var niveau_list = $("#org-niveau-list");
    var poste_list = $('#poste');
    var selected_etape;

    niveau_list.sortable({
        axis: 'y',
        cursor: 'move',
        stop: updateNiveau
    }).disableSelection();

    var options = [];




    //
    //
    //
    //
    // $( '.dropdown-menu a' ).on( 'click', function( event ) {
    //
    //     var $target = $( event.currentTarget ),
    //         val = $target.attr( 'data-value' ),
    //         $inp = $target.find( 'input' ),
    //         idx;
    //
    //     if ( ( idx = options.indexOf( val ) ) > -1 ) {
    //         options.splice( idx, 1 );
    //         setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
    //     } else {
    //         options.push( val );
    //         setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
    //     }
    //
    //     $( event.target ).blur();
    //
    //     console.log( options );
    //     return false;
    // });

    /** Check Affectation des personne */
    $(document).on('change', '.affectation-personne', function() {
       var el = $(this),
           value = $(this).prop('checked');
       $('.affectation-personne').prop('checked', false);
       el.prop('checked', value);

       updateNiveau();
    });

    $('.etape-item').on('click', function() {
       var item = $(this);
       item.closest('.list-group')
           .find('.list-group-item')
           .removeClass('active');
       item.addClass('active');

       var id = item.attr('data-id');
       var url = Routing.generate('parametre_organisation_poste', { etape: id });
       selected_etape = id;

        poste_list.html('');

       fetch(url, {
           method: 'GET',
           credentials: 'include'
       }).then(function(response) {
           return response.json();
       }).then(function(data) {
           showListePostes(data);
       }).catch(function(error) {
           show_info("Erreur", "Une erreur est survenue.", "error");
           console.log(error);
       });
    });

    if ($(document).find('.etape-item').length > 0) {
        $(document).find('.etape-item').first().trigger('click');
    }

    $(document).on('click', '#btn-save-poste-affect', function(event) {
        event.preventDefault();
        saveEtapePosteParam();
    });

    chargerContenuRelationProcessus();

    $(document).on('click', '#btn-add-processus', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_processus'),
            type: 'POST',
            data: {
            },
            success: function (data) {
                console.log(data);
                //var response = $.parseJSON(data);
                show_modal(data, 'Ajouter un processus', 'bounce', 'default');
            }
        });

    });

    $(document).on('click', '#btn-refresh-processus', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_reloadallprocess'),
            type: 'POST',
            data: {
            },
            success: function (data) {
                close_modal();
                $('#nestable-processus').html(data);
            }
        });
    });

    /*AJOUT process*/
    $(document).on('click', '#btn-add-process', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_process'),
            type: 'POST',
            data: {
                processusId: $(this).attr('data-processus'),
            },
            success: function (data) {
                console.log(data);
                //var response = $.parseJSON(data);
                show_modal(data, 'Ajouter un process', 'bounce', 'default');
            }
        });

    });

    $(document).on('click', '#btn-edit-processus', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_editprocessus'),
            type: 'POST',
            data: {
                processusId: $(this).attr('data-id'),
                nom:$(this).attr('data-nom'),
                rang:$(this).attr('data-rang'),

            },
            success: function (data) {
                show_modal(data, 'Modifier processus', 'bounce', 'default');
            }
        });
    });


    //
    $(document).on('click', '#btn-refresh-relation', function(event) {
        event.preventDefault();
        chargerContenuRelationProcessus();
    });


    /*Enregistrer les modifications dans tableau processus/organisation/menus*/
    $(document).on('click', '#btn-save-relation', function(event) {
        event.preventDefault();
        var listeValue = new Object();
        var idx = 0;

        $('#table-processus-poste-menu tr').each(function() {
            if ($(this).attr('data-parent') !== '~') {
                var details = new Object();
                details['process-id'] = 0;
                details['poste-id'] = 0;
                details['menus-id'] = 0;
                $(this).find('td').each (function( column, td) {
                    if ($(td).attr('data-process-id') !== undefined)
                    {
                        details['process-id']=($('#' + $(td).attr('data-process-id')).attr('data-id'));
                    }
                    if ($(td).attr('data-poste-id') !== undefined)
                    {
                        var postesId = [];
                        $('#select-poste-' + $(td).attr('data-process')).find('option').each(function() {
                            postesId.push($(this).val());
                        });
                        details['poste-id']= postesId;
                    }
                    if ($(td).attr('data-menu-id') !== undefined)
                    {
                        var menusId = [];
                        $('#select-' + $(td).attr('data-process')).find('option').each(function() {
                            menusId.push($(this).val());
                        });
                        details['menus-id'] = menusId;
                    }
                });
                listeValue[idx] = details;
                idx = idx + 1;
            }
        });
        var sizeObj = Object.keys(listeValue).length;
        if (sizeObj > 0)
        {
            $.ajax({
                url: Routing.generate('parametre_organisation_save_process_relation'),
                type: 'POST',
                data: {
                    tableau: listeValue,
                },
                success: function (data) {
                    show_info('Enregistrer', 'Enregistrement avec succès');
                }
            });
        }
    });

    /* Séléction rôle */
    $(document).on('click', '#role-list .list-group-item', function (event) {
        event.preventDefault();
        $(this)
            .closest('.list-group')
            .find('.list-group-item')
            .removeClass('active');
        $(this).addClass('active');
        menu_list.find('.menu-select').prop('checked', false);
        menu_list.removeClass('hidden');
        var poste = $(this).attr('data-id');

        $.ajax({
            url: Routing.generate('parametre_menu_par_poste', {poste: poste}),
            type: 'GET',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);
                setMenuSettings(data, menu_list);
            }
        });

    });



    function checkMenus(selector)
    {
        var checkbox = selector;
        var state = checkbox.prop('checked');
        var level = checkbox.attr('data-level');

        /* MAJ descendant */
        checkbox.closest('.dd-item')
            .find('.menu-select')
            .prop('checked', state);

        /* MAJ ascendant  */
        if (state === true) {
            if (level === '1') {
                //Pas de parent
            } else if (level === '2') {
                //On cocher parent N+1
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="1"]')
                    .prop('checked', state);
            } else if (level === '3') {
                //On cocher parent N+1 et N+2
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="2"]')
                    .prop('checked', state);
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="2"]')
                    .closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="1"]')
                    .prop('checked', state);
            }
        }
    }
    $(document).on('change', '.menu-select', function () {
        checkMenus($(this))
        /*var checkbox = $(this);
        var state = checkbox.prop('checked');
        var level = checkbox.attr('data-level');*/

        /* MAJ descendant */
        /*checkbox.closest('.dd-item')
            .find('.menu-select')
            .prop('checked', state);*/

        /* MAJ ascendant  */
        /*if (state === true) {
            if (level === '1') {
                //Pas de parent
            } else if (level === '2') {
                //On cocher parent N+1
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="1"]')
                    .prop('checked', state);
            } else if (level === '3') {
                //On cocher parent N+1 et N+2
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="2"]')
                    .prop('checked', state);
                checkbox.closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="2"]')
                    .closest('.dd-list')
                    .closest('.dd-item')
                    .find('.menu-select[data-level="1"]')
                    .prop('checked', state);
            }
        }*/
    });


    /* Enregistrer Poste */

    $(document).on('click', '#btn-choose-poste', function (event) {
        event.preventDefault();
        var idRow = $(this).attr('data-id');
        var postes = [];
        $('#container-poste').find('.poste-select').each(function (index, item) {
            var state = $(item).prop('checked');
            if (state === true) {
                postes.push({'id':$(item).attr('data-poste-id'), 'nom':$(item).attr('data-nom')});
            }
        });

        $.ajax({
            url: Routing.generate('parametre_organisation_postes_choisis'),
            type: 'POST',
            data: {
                postes: postes,
                processId: idRow,
            },
            success: function (data) {
                close_modal();
                $('#contenu-poste-processus-' + idRow).html(data);
            }
        });
    });

    /* Enregistrer Menus par processus */
    $(document).on('click', '#btn-choose-menu', function (event) {
        event.preventDefault();
        var idRow = $(this).attr('data-id');
            var menus = [];
            $('#content-liste-menus').find('.menu-select').each(function (index, item) {
                var state = $(item).prop('checked');
                if (state === true) {
                    menus.push($(item).attr('data-menu-id'));
                }
            });

            $.ajax({
                url: Routing.generate('parametre_organisation_menus_choisis'),
                type: 'POST',
                data: {
                    menus: menus,
                    processId: idRow,
                },
                success: function (data) {
                    close_modal();
                    $('#contenu-menus-processus-' + idRow).html(data);
                }
            });
    });

    //
    $(document).on('click', '.liste-poste-processus', function(event) {
        event.preventDefault();
        var idRow = $(this).attr('data-id');
        var postes = [];
        $('#select-poste-' + idRow).find('option').each(function() {
            postes.push({'id' : $(this).val()});
        });
        $.ajax({
            url: Routing.generate('parametre_organisation_liste_poste'),
            type: 'POST',
            data: {
                processId: idRow,
                postesChoisis: postes,
            },
            success: function (data) {
                show_modal(data, '<div class="row">Liste des postes</div><div>' + $('#id-process-' + idRow).html() + '</div>', 'bounce');
            }
        });
    });



    $(document).on('click', '.liste-menu-processus', function(event) {
        event.preventDefault();
        var idRow = $(this).attr('data-id');
        var menus = [];
        $('#select-' + idRow).find('option').each(function() {
            menus.push({'id' : $(this).val() , 'menu_intranet_id' : $(this).attr('data-parent')});
        });
        $.ajax({
            url: Routing.generate('parametre_organisation_processus_liste_menus'),
            type: 'POST',
            data: {
            },
            success: function (data) {
                var input = '<input id="cocherToutMenus" type="checkbox" data-cocher="0">';
                var label = '<label for="cocherToutMenus">Cocher tout</label>';
                var html = '<div class="row" style="margin:0px"><div class="checkbox checkbox-primary" style="margin:5px">' + input + label + '</div></div>';
                html = html + '<div class="row" id="content-liste-menus" style="height: 380px; overflow: auto"></div>';
                html = html + '<div class="row" style="margin:15px"><span class="btn btn-primary pull-right" id="btn-choose-menu" data-id="' + idRow + '"><i class="fa fa-check"></i>&nbsp;Choisir</span></div>'
                show_modal(html, '<div class="row">Liste des menus</div><div>' + $('#id-process-' + idRow).html() + '</div>', 'bounce', 'default');
                $('#content-liste-menus').html(data);
                setTimeout(function() {
                    $('.nestable-menu').nestable({
                        group: 0
                    }).nestable('collapseAll');
                },1000);
                setMenuSettings(menus, '#content-liste-menus');
            }
        });
    });

    function setMenuSettings(data, parent) {
        if (typeof data === 'undefined') {
            return;
        }
        $.each(data, function(index, item) {
            if (typeof item.menu_intranet_id !== 'undefined' && item.menu_intranet_id !== null) {
                var search = $(parent).find('.menu-select[data-menu-id="' + item.id + '"]');
                if (search.length > 0) {
                    search.prop('checked', true);
                }
            }
        });
    }
    //Cocher ou décocher tout menus cocher: true/false
    function checkAllMenu(cocher)
    {
        $('.menu-select').each(function() {
            $(this).prop('checked', cocher);
        });
    }

    $(document).on('click', '#cocherToutMenus', function(event) {
        //event.preventDefault();
        checkAllMenu((parseInt($(this).attr('data-cocher')) === 0))
        if (parseInt($('#cocherToutMenus').attr('data-cocher')) === 0) {

            $(this).attr('data-cocher', '1');
        }
        else {

            $(this).attr('data-cocher', '0');
        }
    });

    //Cocher ou décocher tout postes cocher: true/false
    function checkAllPostes(cocher)
    {
        $('.poste-select').each(function() {
            $(this).prop('checked', cocher);
        });
    }

    $(document).on('click', '#cocherToutPostes', function(event) {
        //event.preventDefault();
        checkAllPostes((parseInt($(this).attr('data-cocher')) === 0))
        if (parseInt($('#cocherToutPostes').attr('data-cocher')) === 0) {
            $(this).attr('data-cocher', '1');
        }
        else {
            $(this).attr('data-cocher', '0');
        }
    });

    //
    $(document).on('click', '#code-head', function(event) {
        event.preventDefault();
        var expand = $(this).attr('data-collapse');
        $(document).find('.row-parent').each(function() {
            if (expand == 1)
            {
                $(this).attr('data-expand', '0');
                $('#data-parent-' + $(this).attr('data-parent')).removeClass('fa-minus');
                $('#data-parent-' + $(this).attr('data-parent')).addClass('fa-plus');
                $('.row-parent-' + $(this).attr('data-parent')).addClass('hidden');
            }
            else
            {
                $(this).attr('data-expand', '1');
                $('#data-parent-' + $(this).attr('data-parent')).removeClass('fa-plus');
                $('#data-parent-' + $(this).attr('data-parent')).addClass('fa-minus');
                $('.row-parent-' + $(this).attr('data-parent')).removeClass('hidden');
            }
        });
        if (expand == 1) {
            $('#code-head').attr('data-collapse', '0');
            $('.code-head').removeClass('fa-minus');
            $('.code-head').addClass('fa-plus');
        }
        else {
            $('#code-head').attr('data-collapse', '1');
            $('.code-head').removeClass('fa-plus');
            $('.code-head').addClass('fa-minus');
        }
    });

    $(document).on('click', '.row-parent', function(event) {
        event.preventDefault();
        if (parseInt($(this).attr('data-expand')) === 1) {
            $(this).attr('data-expand', '0');
            $('#data-parent-' + $(this).attr('data-parent')).removeClass('fa-minus');
            $('#data-parent-' + $(this).attr('data-parent')).addClass('fa-plus');
            $('.row-parent-' + $(this).attr('data-parent')).addClass('hidden');
        }
        else
        {
            $(this).attr('data-expand', '1');
            $('#data-parent-' + $(this).attr('data-parent')).removeClass('fa-plus');
            $('#data-parent-' + $(this).attr('data-parent')).addClass('fa-minus');
            $('.row-parent-' + $(this).attr('data-parent')).removeClass('hidden');
        }

    });


    function chargerContenuRelationProcessus()
    {
        $.ajax({
            url: Routing.generate('parametre_organisation_processus_poste_menus_liste'),
            type: 'POST',
            data: {
            },
            success: function (data) {
                $('#content-processus-poste-menu').html(data);
                var config = {
                    '.chosen-select'           : {},
                    '.chosen-select-deselect'  : {allow_single_deselect:true},
                    '.chosen-select-no-single' : {disable_search_threshold:10},
                    '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                    '.chosen-select-width'     : {width:"95%"}
                }
                for (var selector in config) {
                    $(selector).chosen(config[selector]);
                }
            }
        });
    }


    //Click tab conteneur liste processus/postes/menus
    $(document).on('click', '#id-tab-processus-poste', function(event) {
        chargerContenuRelationProcessus();
    });


    //Supprimer processus ainsi que ses fils
    $(document).on('click', '.btn-remove-processus', function(event) {
        event.preventDefault();
        var processusId = $(this).attr('data-id');
        swal({
            title: "Confirmation",
            text: "Etes-vous sûr de vouloir supprimer ce PROCESSUS, vous allez supprimer aussi les process rattaché avec ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            cancelButtonColor: "#acacac",
            confirmButtonText: "Oui, Supprimer !",
            cancelButtonText: "Non, annuler !"
        }).then (function () {
            console.log(processusId);
            $.ajax({
                url: Routing.generate('parametre_organisation_deleteprocessus'),
                type: 'POST',
                data: {
                    processusId: processusId,
                },
                success: function (data) {
                    console.log(data);
                    if (data === 'error') {
                        show_info('Suppression', 'Erreur lors de la suppression du processus', 'error');
                    } else{
                        show_info('Suppression', 'Process supprimé avec succès');
                        $('#nestable-processus').html(data);
                    }
                }
            });
        });



    });

    //Supprimer fils (process)
    $(document).on('click', '#btn-remove-process', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_deleteprocess'),
            type: 'POST',
            data: {
                processId: $(this).attr('data-id'),
            },
            success: function (data) {
                show_info('Suppression', 'Process supprimé avec succès');
                $('#nestable-processus').html(data);

            }
        });
    });


    $(document).on('click', '#btn-edit-process', function(event) {
        event.preventDefault();
        $.ajax({
            url: Routing.generate('parametre_organisation_editprocess'),
            type: 'POST',
            data: {
                processusId: $(this).attr('data-processus'),
                nom:$(this).attr('data-nom'),
                rang:$(this).attr('data-rang'),
                processId: $(this).attr('data-id'),
            },
            success: function (data) {
                show_modal(data, 'Modifier process', 'bounce', 'default');
            }
        });
    });

    //
    $(document).on('click', '.btn-expand', function(event) {
        event.preventDefault();
        if (parseInt($(this).attr('data-expand')) === 1)
        {
            $(this).attr('data-expand', '0');
            $('#btn-expand-' + $(this).attr('data-parent')).removeClass('fa-minus');
            $('#btn-expand-' + $(this).attr('data-parent')).addClass('fa-plus');
            $('.processus-fils-' + $(this).attr('data-parent')).addClass('hidden');
        }
        else
        {
            $(this).attr('data-expand', '1');
            $('#btn-expand-' + $(this).attr('data-parent')).addClass('fa-minus');
            $('#btn-expand-' + $(this).attr('data-parent')).removeClass('fa-plus');
            $('.processus-fils-' + $(this).attr('data-parent')).removeClass('hidden');
        }
    });

    //Replier ou déplier détail
    $(document).on('click', '.btn-expand-detail', function(event) {
        event.preventDefault();
        console.log('tafiidtra');
        if (parseInt($(this).attr('data-expand')) === 1)
        {
            console.log($(this).attr('data-parent'));
            console.log(parseInt($(this).attr('data-expand')));
            $(this).attr('data-expand', '0');
            $(this).removeClass('fa-minus');
            $(this).addClass('fa-plus');
            $('#expand-detail-' + $(this).attr('data-id')).addClass('hidden');

        }
        else
        {
            console.log($(this).attr('data-parent'));
            console.log(parseInt($(this).attr('data-expand')));
            $(this).attr('data-expand', '1');
            $(this).addClass('fa-minus');
            $(this).removeClass('fa-plus');
            $('#expand-detail-' + $(this).attr('data-id')).removeClass('hidden');
        }
    });

    //Replier ou déplier détail
    $(document).on('click', '.expand-process', function(event) {
        event.preventDefault();

        if (parseInt($(this).attr('data-expand')) === 1)
        {
            $(this).attr('data-expand', '0');
            $(this).html('<i class="fa fa-plus"></i>&nbsp;Déplier tout');

            $('.detail-de-' + $(this).attr('data-parent')).addClass('hidden');
            $('.id-parent-' + $(this).attr('data-parent')).removeClass('fa-minus');
            $('.id-parent-' + $(this).attr('data-parent')).addClass('fa-plus');
            $('.id-parent-' + $(this).attr('data-parent')).attr('data-expand', '0');
        }
        else
        {
            console.log($(this).attr('data-parent'));
            console.log(parseInt($(this).attr('data-expand')));
            $(this).attr('data-expand', '1');
            $(this).html('Replier tout');
            $(this).html('<i class="fa fa-minus"></i>&nbsp;Replier tout');

            $('.detail-de-' + $(this).attr('data-parent')).removeClass('hidden');
            $('.id-parent-' + $(this).attr('data-parent')).addClass('fa-minus');
            $('.id-parent-' + $(this).attr('data-parent')).removeClass('fa-plus');
            $('.id-parent-' + $(this).attr('data-parent')).attr('data-expand', '1');
        }
    });

    //btn-valide-edit-process

    $(document).on('click', '#btn-valide-edit-process', function(event){
        event.preventDefault();
        if ($('#nom-edit-process').val().trim() === '')
        {
            return;
        }
        else {
            var rang = 0;
            if ($('#rang-edit-process').val().trim() !== ''){
                rang = parseInt($('#rang-edit-process').val().trim());
            }
            var processAntId = parseInt($('#ant-edit-process').val());
            var processPostId = parseInt($('#post-edit-process').val());
            $.ajax({
                url: Routing.generate('parametre_organisation_saveeditprocess'),
                type: 'POST',
                data: {
                    rang:rang,
                    nom: $('#nom-edit-process').val().trim(),
                    processusId: $('#processus').val(),
                    processId: $('#processId').val(),
                    unite: $('#unite-edit-process').val(),
                    temps: $('#temps-edit-process').val().trim(),
                    processAntId: processAntId,
                    processPostId: processPostId,
                    description:$('#description').val().trim(),

                },
                success: function (data) {
                    close_modal();
                    show_info('Modification', 'Modification process avec succès');
                    $('#nestable-processus').html(data);
                }
            });
        }
    });

    $(document).on('click', '#btn-valide-edit-processus', function(event){
        event.preventDefault();
        if ($('#nom-edit-processus').val().trim() === '')
        {
            return;
        }
        else {
            var rang = 0;
            if ($('#rang-edit-processus').val().trim() !== ''){
                rang = $('#rang-edit-processus').val().trim();
            }
            $.ajax({
                url: Routing.generate('parametre_organisation_saveeditprocessus'),
                type: 'POST',
                data: {
                    rang:rang,
                    nom: $('#nom-edit-processus').val().trim(),
                    processusId: $("#processusId").val(),
                },
                success: function (data) {
                    close_modal();
                    show_info('Modification', 'Modification  processus avec succès');
                    $('#nestable-processus').html(data);
                }
            });
        }
    });

    $(document).on('click', '#btn-valide-new-process', function(event){
        event.preventDefault();
        if ($('#nom-process').val().trim() === '')
        {
            return;
        }
        else {
            var rang = 0;
            if ($('#rang-process').val().trim() === ''){
                rang = parseInt($('#rang-process').val().trim());
            }
            var processAntId = parseInt($('#ant-edit-process').val());
            var processPostId = parseInt($('#post-edit-process').val());
            $.ajax({
                url: Routing.generate('parametre_organisation_saveprocess'),
                type: 'POST',
                data: {
                    rang:rang,
                    nom: $('#nom-process').val().trim(),
                    processusId: $("#processus").val(),
                    unite: $('#unite-edit-process').val(),
                    temps: $('#temps-edit-process').val().trim(),
                    processAntId: processAntId,
                    processPostId: processPostId,
                    description:$('#description').val().trim(),
                },
                success: function (data) {
                    close_modal();
                    show_info('Ajout', 'Ajout  process avec succès');
                    $('#nestable-processus').html(data);
                }
            });
        }
    });


    $(document).on('click', '#btn-valide-new-processus', function(event){
        event.preventDefault();
        if ($('#nom-processus').val().trim() === '')
        {
            return;
        }
        else {
            var rang = 0;
            if ($('#rang-processus').val().trim() === ''){
                rang = $('#rang-processus').val().trim();
            }
            $.ajax({
                url: Routing.generate('parametre_organisation_saveprocessus'),
                type: 'POST',
                data: {
                    rang:rang,
                    nom: $('#nom-processus').val().trim(),
                },
                success: function (data) {
                    close_modal();
                    $('#nestable-processus').html(data);

                }
            });
        }
    });




    /**  Save Poste Par Etape */
    function saveEtapePosteParam() {
        var postes = [];
        $(document).find('.poste-affect-item').each(function(index, item) {
            if ($(item).prop('checked')) {
                postes.push($(item).attr('data-id'));
            }
        });
        var url = Routing.generate('parametre_organisation_poste_update', { etape:selected_etape }),
            formData = new FormData();
        formData.append('postes', JSON.stringify(postes));

        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            show_info("", "Paramètres enregistrés.", "info");
            console.log(data);
        }).catch(function(error) {
            show_info("Erreur", "Une erreur est survenue.", "error");
            console.log(error);
        });

    }

    /** Afficher Liste poste par Etape */
    function showListePostes(data) {
        if (!data instanceof Array || data.length === 0) {
            poste_list.html('');
            return false;
        }

        var items = '';
        data.forEach(function(item, index) {
            var checked = item.is_selected ? 'checked' : '';
            var checkbox = '<div class="switch pull-right">\n' +
                '                                <div class="onoffswitch">\n' +
                '                                    <input type="checkbox"' +  checked + ' class="onoffswitch-checkbox poste-affect-item" data-id="' + item.org_id + '"' +
                '                                       id="post-affect-' + item.org_id + '">\n' +
                '                                    <label class="onoffswitch-label" for="post-affect-' + item.org_id + '">\n' +
                '                                        <span class="onoffswitch-inner"></span>\n' +
                '                                        <span class="onoffswitch-switch"></span>\n' +
                '                                    </label>\n' +
                '                                </div>\n' +
                '                            </div>';
           items += '<ul class="list-group-item" id="' + item.org_id + '">' + checkbox + '<i class="fa fa-arrow-right"></i> ' + item.org_nom + '</ul>';
        });

        poste_list.html(items);

        $('#btn-save-poste-affect').removeAttr('disabled');
    }


    function loadJqGridRelactionProcessus()
    {
        $('#js_processus_poste_menu').jqGrid({
            url: Routing.generate('parametre_personnel_liste'),
            datatype: 'json',
            loadonce: true,
            sortable: true,
            autowidth: true,
            height: 500,
            shrinkToFit: true,
            viewrecords: true,
            rownumbers: true,
            rownumWidth: 30,
            IgnoreCase: true,
            rowNum: 1000,
            rowList: [10, 20, 30, 50, 100, 500, 1000],
            pager: '#pager_liste_personnel',
            caption: 'Liste des personnels',
            hidegrid: false,
            editurl: Routing.generate('parametre_personnel_edit'),
            colNames: ['Matricule', 'Nom', 'Prénom', 'Adresse', 'Téléphone', 'Sexe', 'Login', 'Mot de passe', 'Date Entrée', 'Date Sortie', 'Poste_Id', 'Poste', 'Role_Id', 'Rôle','Rattachement_Id', 'Rattachement', '<span style="display:inline-block"/>Reinitialiser Pwd', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action', ],
            //colNames: ['Matricule', 'Nom', 'Prénom', 'Adresse', 'Téléphone', 'Sexe', 'Login', 'Mot de passe', 'Date Entrée', 'Date Sortie', 'Poste_Id', 'Poste', 'Role_Id', 'Rôle','<span style="display:inline-block"/>Reinitialiser Pwd', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action', ],
            colModel: [
                {name: 'matricule', index: 'matricule', width: 80, align: "center", sorttype: 'integer', classes: 'js-personnel-matricule'},
                {name: 'nom', index: 'nom', width: 150,  editable: true, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-nom'},
                {name: 'prenom', index: 'prenom', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-prenom'},
                {name: 'adresse', index: 'adresse', editable: true, width: 200, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-adresse'},
                {name: 'telephone', index: 'telephone', editable: true, width: 100, classes: 'js-personnel-telephone'},
                {name: 'sexe', index: 'sexe', width: 100, align: "center", stype:"select",
                    searchoptions: listSexe,editable: true, edittype:"select",formatter:'select', editoptions:{value:"M:M;F:F"},
                    classes: 'js-personnel-sexe'},
                {name: 'login', index: 'login', editable: true, width: 100, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-login'},
                {name: 'password', index: 'password', editable: true, hidden: true, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-password'},
                {name: 'date_entree', index: 'date-entree', editable: true, width: 105, align: "center", sorttype: 'date', formatter: 'date',
                    formatoptions: {
                        newformat: "d-m-Y"
                    },
                    datefmt: 'd-m-Y',
                    editoptions : {
                        dataInit: function (el) {
                            setTimeout(function () {
                                $(el).datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true, clearBtn: true});
                            }, 50);
                        }
                    },
                    classes: 'js-personnel-date-entree'},
                {name: 'date_sortie', index: 'date-sortie', editable: true, width: 105, align: "center", sorttype: 'date', formatter: 'date',
                    formatoptions: {
                        newformat: "d-m-Y"
                    },
                    datefmt: 'd-m-Y',
                    editoptions : {
                        dataInit: function (el) {
                            setTimeout(function () {
                                $(el).datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true, clearBtn: true});
                            }, 50);
                        }
                    },
                    classes: 'js-personnel-date-sortie'},
                {name: 'poste-id', index: 'poste-id', hidden: true, classes: 'js-personnel-poste-id'},
                {name: 'poste', index: 'poste',editable: true,  width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                    edittype:"select", editoptions: { dataUrl: Routing.generate('parametre_poste_liste_with_cellule', { json: 0}),
                        dataInit: function(elem) {
                            $(elem).width(100);
                        }}, classes: 'js-personnel-poste'},
                {name: 'role-id', index: 'role-id', hidden: true, classes: 'js-personnel-role-id'},
                {name: 'role', index: 'role', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                    edittype:"select", editoptions: { dataUrl: Routing.generate('access_operateur_liste', { json: 0}),
                        dataInit: function(elem) {
                            $(elem).width(100);
                        }}, classes: 'js-personnel-role'},
                {name: 'rattachement-id', index: 'rattachement-id', hidden: true, classes: 'js-personnel-rattachement-id'},
                {name: 'rattachement', index: 'rattachement', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                    edittype:"select", editoptions: { dataUrl: Routing.generate('operateur_rattachement_liste', { json: 0}),
                        dataInit: function(elem) {
                            $(elem).width(100);
                        }}, classes: 'js-personnel-rattachement'},
                {name: 'reinitialiser', index: 'reinitialiser', width: 140, align: "center", sortable: false, },
                {name: 'action', index: 'action', width: 80, align: "center", sortable: false, classes: 'js-personnel-action'}
            ],
            onSelectRow: function(id) {
                if(id){
                    $('#js_personnel_liste')
                        .restoreRow(lastsel)
                        .editRow(id,true);
                    lastsel=id;
                }
            },
            beforeSelectRow: function(rowid, e) {
                var target = $(e.target);
                var cell_action = target.hasClass('js-personnel-action');
                var item_action = (target.closest('td').children('.icon-action').length > 0);
                return !(cell_action || item_action);
            }
        });
    }

    /** Save Update Niveau */
    function updateNiveau() {
        niveau_list.find('.list-group-item').each(function(index, item) {
            $(item).attr('data-rang', index + 1);
        });

        var items = [];
        niveau_list.find('.list-group-item').each(function(index, item) {
            items.push({
                'id': $(item).attr('data-id'),
                'rang': $(item).attr('data-rang'),
                'is_poste': $(item).find('input[type="checkbox"]').prop('checked') === true ? 1 : 0
            });
        });
        // console.log(items);

        var url = Routing.generate('parametre_organisation_titre_order');
        var formData = new FormData();
        formData.append('items', JSON.stringify(items));
        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            console.log(data);
        }).catch(function(error) {
            console.log(error);
            show_info("Erreur", "Une erreur est survenue.", "error");
        })
    }



    /*$('#nestable-processus').on('click', function (e) {
        var target = $(e.target),
            action = target.data('action');
        if (action === 'expand-all') {
            $('.dd').nestable('expandAll');
        }
        if (action === 'collapse-all') {
            $('.dd').nestable('collapseAll');
        }
        console.log(action);
    });*/
    /*$('#nestable-processus').nestable({

        maxDepth: 1,
        noDragClass:'dd-nodrag',
    }).on('change', updateOutput);

    var updateOutput = function (e) {

    };

    updateOutput($('#nestable').data('output', $('#nestable-output')));*/
    $(window).resize(function(){

    });
});