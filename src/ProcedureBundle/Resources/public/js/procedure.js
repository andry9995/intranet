$(function () {
    var lastsel;

    $('.chosen-select').chosen({
        width: '100%',
        search_contains: true
    });

    $('#js_procedure_liste').jqGrid({
        url: Routing.generate('procedure_liste'),
        datatype: 'json',
        loadonce: true,
        sortable: true,
        autowidth: true,
        height: 500,
        shrinkToFit: true,
        viewrecords: true,
        rownumbers: true,
        rownumWidth: 40,
        rowNum: 500,
        rowList: [500, 1000, 2000],
        pager: '#pager_liste_procedure',
        caption: 'Liste des procédures',
        hidegrid: false,
        editurl: Routing.generate('procedure_edit'),
        colNames: ['Numéro', 'Procédure', 'Description', 'Précédent', 'Suivant', 'Poste_Id', 'Poste',
            'Unite_Id', 'Unité', 'Durée (h)', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action'],
        colModel: [
            {name: 'numero', index: 'numero', editable: false, width: 60, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); },
                editoptions: {dataInit: function(elem) {$(elem).css({'text-align': 'center', 'background-color': '#fafed4'});}},
                editrules: { required: true }, classes: 'js-procedure-numero'},
            {name: 'procedure', index: 'procedure', editable: false, width: 200, sorttype: function(cell) { return jqGridSortable(cell); },
                editoptions: {dataInit: function(elem) {$(elem).css({'background-color': '#fafed4'});}},
                editrules: { required: true }, classes: 'js-procedure-nom'},
            {name: 'description', index: 'description', editable: false, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:'textarea', editoptions:{rows:'4',cols:'22'}, classes: 'js-procedure-description'},
            {name: 'precedent', index: 'precedent', editable: false, width: 150, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { multiple: true, dataUrl: Routing.generate('procedure_liste_simple', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(150);
                }}, classes: 'js-procedure-precedent'},
            {name: 'suivant', index: 'suivant', editable: false, width: 150, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { multiple: true, dataUrl: Routing.generate('procedure_liste_simple', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(150);
                }}, classes: 'js-procedure-suivant'},
            {name: 'poste-id', index: 'poste-id', hidden: true, classes: 'js-procedure-poste-id'},
            {name: 'poste', index: 'poste', editable: false, width: 100, sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { dataUrl: Routing.generate('parametre_poste_liste_with_cellule', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(100);
                }}, classes: 'js-procedure-poste'},
            {name: 'unite-id', index: 'unite-id', hidden: true, classes: 'js-procedure-unite-id'},
            {name: 'unite', index: 'unite', editable: false, width: 120, fixed: true, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { dataUrl: Routing.generate('unite_comptage_liste', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(80);
                }}, classes: 'js-procedure-unite'},
            {name: 'duree', index: 'duree', editable: false, width: 70, fixed: true, align: 'center', sorttype: function(cell) { return jqGridSortable(cell); },
                editoptions: {dataInit: function(elem) {$(elem).css({'text-align': 'center', 'background-color': '#fafed4'});}},
                classes: 'js-procedure-duree'},
            {name: 'action', index: 'action', width: 80, fixed: true, align: 'center', sortable: false, classes: 'js-entite-action'}
        ],
        onSelectRow: function(id) {
            if(id){
                $('#js_procedure_liste').restoreRow(lastsel);
                $('#js_procedure_liste').editRow(id,true);
                lastsel=id;
            }
        },
        beforeSelectRow: function(rowid, e) {
            var target = $(e.target);
            var cell_action = target.hasClass('js-procedure-action');
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            return !(cell_action || item_action);

        },
        ajaxRowOptions: { async: true }
    });

    // Modif Procedure
    $(document).on('click', '.js-edit-procedure', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var id = $(this).closest('tr').attr('id'),
            numero = $(this).closest('tr').find('.js-procedure-numero').text(),
            procedure = $(this).closest('tr').find('.js-procedure-nom').text();
        var url = Routing.generate("procedure_get_one", {id: id});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            updateListePrecedentSuivant(function() {
                bindProcedureToForm(data);
            });
            $(document).find('#modal-procedure-title').text('Modification: ' + numero + " - " + procedure);
            $(document).find('#add-procedure-id').val('');
            $(document).find('#procedure-modal').modal();
            $('#add-procedure-id').val(id);
            modalDraggable();
        }).catch(function(error) {
            console.log(error);
            show_info("", "Une erreur est survenue.", "error");
        });
    });

    // Ajouter une procedure
    $(document).on('click', '#btn-add-procedure', function (event) {
        event.preventDefault();
        clearProcedureForm();
        var btn = $(this),
            html_default = '<i class="fa fa-plus-circle"></i> Ajouter une procédure',
            html_busy = '<i class="fa fa-spinner fa-pulse fa-fw"></i> Ajouter une procédure';
        btn.html(html_busy);
        updateListePrecedentSuivant(function() {
            $(document).find('#modal-procedure-title').text('Ajouter une procédure');
            $(document).find('#add-procedure-id').val('');
            $(document).find('#procedure-modal').modal();
            $('#add-procedure-id').val('');
            modalDraggable();
            btn.html(html_default);
        });
    });

    // Enregistrement Ajout Procedure
    $(document).on('click', '#btn-add-procedure-save', function (event) {
        event.preventDefault();
        $(document).find('#add-procedure-erreur').addClass('hidden');
        var id = $('#add-procedure-id').val();
        var numero = $(document).find('#add-procedure-numero').val().trim();
        var procedure = $(document).find('#add-procedure-nom').val().trim();
        var description = $(document).find('#add-procedure-description').val().trim();
        var precedent = $(document).find('#add-procedure-prec').val();
        var suivant = $(document).find('#add-procedure-suiv').val();
        var poste = $(document).find('#add-procedure-poste').val();
        var unite = $(document).find('#add-procedure-unite').val();
        var duree = $(document).find('#add-procedure-duree').val().trim();
        var data = {
                id: id,
                numero: numero,
                procedure: procedure,
                description: description,
                precedent: precedent,
                suivant: suivant,
                poste: poste,
                unite: unite,
                duree: duree
        };
        if (numero !== '' && procedure !== '') {
            $.ajax({
                url: Routing.generate('procedure_add'),
                type: 'POST',
                data: data,
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (typeof response.erreur !== 'undefined' && response.erreur == true) {
                        $('#add-procedure-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-procedure-erreur').addClass('hidden');
                        $('#add-procedure-numero').val('');
                        $('#add-procedure-nom').val('');
                        $('#add-procedure-description').val('');
                        $('#add-procedure-prec').val('').trigger('chosen:updated');
                        $('#add-procedure-suiv').val('').trigger('chosen:updated');
                        $('#add-procedure-poste').val('0');
                        $('#add-procedure-unite').val('0');
                        $('#add-procedure-duree').val('');
                        $('#add-procedure-id').val('');
                        $('#procedure-modal').modal('hide');

                        $("#js_procedure_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        }
    });

    // Supprimer une procedure
    $(document).on('click', '.js-delete-procedure', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');

        $('#js_procedure_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('procedure_delete'),
            top: 200,
            left: 400,
            width: 400,
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    /** Mise à jour liste des procédures précentes et suivantes */
    function updateListePrecedentSuivant(callback) {
        var url = Routing.generate("procedure_liste_simple", {json: 1});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var precedent = $('#add-procedure-prec'),
                suivant = $('#add-procedure-suiv');
            precedent.html('');
            suivant.html('');
            if (data instanceof Array) {
                var options = '';
                data.forEach(function (item) {
                    options += '<option value="' + item.id + '">' + item.numero + ' ' + item.nom + '</option>';
                });
                precedent.html(options).trigger('chosen:updated');
                suivant.html(options).trigger('chosen:updated');
            }
            if (typeof callback === 'function') {
                callback();
            }
        }).catch(function(error) {
            console.log(error);
            show_info("", "Une erreur est survenue.", "error");
        });
    }

    function bindProcedureToForm(data) {
        $('#add-procedure-id').val(data.id);
        $('#add-procedure-numero').val(data.numero);
        $('#add-procedure-nom').val(data.nom);
        $('#add-procedure-description').val(data.description);
        if (data.organisation) {
            $('#add-procedure-poste').val(data.organisation.id);
        } else {
            $('#add-procedure-poste').val('0');
        }
        if (data.uniteComptage) {
            $('#add-procedure-unite').val(data.uniteComptage.id);
        } else {
            $('#add-procedure-unite').val('0');
        }
        $('#add-procedure-duree').val(data.duree);
        $('#add-procedure-prec').val(data.precedent).trigger('chosen:updated');
        $('#add-procedure-suiv').val(data.suivant).trigger('chosen:updated');
    }

    function clearProcedureForm() {
        $('#add-procedure-id').val('');
        $('#add-procedure-numero').val('');
        $('#add-procedure-nom').val('');
        $('#add-procedure-description').val('');
        $('#add-procedure-poste').val('0');
        $('#add-procedure-unite').val('0');
        $('#add-procedure-duree').val('');
        $('#add-procedure-prec').val('').trigger('chosen:updated');
        $('#add-procedure-suiv').val('').trigger('chosen:updated');
    }
});