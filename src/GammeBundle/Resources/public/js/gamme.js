$(function () {
    var window_height = window.innerHeight,
        gamme_count = 0;
    var procedure_select_options,
        procedure_listes;

    $('#gamme-container').height(window_height - 195);
    $('#affectation-container').height(window_height - 195);
    getListeGammes();

    /** AJOUTER UNE GAMME */
    $('#btn-new-gamme').on('click', function(event) {
       event.preventDefault();
       var  new_gamme = $('#new-gamme-item').clone(true, true);
        gamme_count = gamme_count !== 0 ? gamme_count + 1 : $(document).find('.panel.gamme').length + 1;
        console.log(gamme_count);
       var new_gamme_id = 'collapse-' + gamme_count.toString();
       new_gamme
           .removeAttr('id')
           .removeClass('hidden');
       new_gamme.find('a.text-gamme-nom').attr('href', '#' + new_gamme_id);
       new_gamme.find('.panel-collapse').attr('id', new_gamme_id);
        new_gamme.find('.list-procedure-select')
            .html(procedure_select_options)
            .chosen({
                search_contains: true,
                width: '100%',
                placeholder_text_single: '---',
                no_results_text: 'Aucun résultat'
            });
       new_gamme.prependTo($('#gamme-group'));
       ProcedureMove(new_gamme);
    });

    /** MODIFIER NOM D'UNE GAMME */
    $(document).on('change', '.input-gamme-nom', function() {
        var input_gamme_nom = $(this).closest('.panel-title').find('.text-gamme-nom');
        input_gamme_nom.text($(this).val());
    });

    $(document).on('click', '.btn-edit-gamme-nom', function(event) {
        event.preventDefault();
        var gamme_title = $(this).closest('.panel-title').find('.text-gamme-nom'),
            input_gamme_nom = $(this).closest('.panel-title').find('.input-gamme-nom'),
            btn_edit_gamme_nom = $(this).closest('.panel-title').find('.btn-edit-gamme-nom'),
            btn_save_gamme_nom = $(this).closest('.panel-title').find('.btn-save-gamme-nom');
        gamme_title.addClass('hidden');
        btn_edit_gamme_nom.addClass('hidden');
        input_gamme_nom
            .removeClass('hidden')
            .focus()
            .putCursorAtEnd();
        btn_save_gamme_nom.removeClass('hidden');
    });

    /** AJOUTER UNE PROCEDURE A UNE GAMME */
    $(document).on('click', '.btn-add-procedure-to-gamme', function(event) {
        event.preventDefault();
        var parent = $(this).closest('.gamme');
        var procedure_list_container = parent.find('.list-procedure');

        var select = parent.find('.list-procedure-select');
        var value = select.val();
        if (value && value !== '') {
            var item = findProcedure(parseInt(value, 10));
            if (item) {
                var liste_items =   '<li class="list-group-item procedure-item" data-id="' + item.id + '"><span class="label label-default label-num-proc">' +
                                        item.numero + '</span> ' + item.nom +
                                        '<i class="fa fa-close pull-right btn-delete-procedure" style="cursor:default;"></i>' +
                                    '</li>';
                procedure_list_container.append(liste_items);

                var procedures_id = [];

                procedure_list_container.find('.procedure-item').each(function(index, item) {
                    procedures_id.push(parseInt($(item).attr('data-id'), 10));
                });

                updateProcedureDisabledOptions(parent);

                parent.find('.btn-save-gamme-item').addClass('btn-blink');

                select.val('').trigger('chosen:updated');

            }
        } else {
            show_info("", "Séléctionner une procédure à ajouter.", "warning");
        }
    });

    /** SUPPRIMER UNE PROCEDURE D'UNE GAMME */
    $(document).on('click', '.btn-delete-procedure', function() {
        var the_gamme = $(this).closest('.panel.gamme');
        $(this).closest('.procedure-item').remove();
        the_gamme.find('.btn-save-gamme-item')
            .addClass('btn-blink');
        updateProcedureDisabledOptions(the_gamme);
    });

    /** ENREGISTRER MODIFICATION NOM D'UNE GAMME */
    $(document).on('click', '.btn-save-gamme-nom', function(event) {
        event.preventDefault();
        var the_gamme = $(this).closest('.panel.gamme'),
            gamme_title = the_gamme.find('.text-gamme-nom'),
            input_gamme_nom = $(this).closest('.panel-title').find('.input-gamme-nom'),
            btn_edit_gamme_nom = $(this).closest('.panel-title').find('.btn-edit-gamme-nom'),
            btn_save_gamme_nom = $(this).closest('.panel-title').find('.btn-save-gamme-nom');
        gamme_title.removeClass('hidden');
        btn_edit_gamme_nom.removeClass('hidden');
        input_gamme_nom.addClass('hidden');
        btn_save_gamme_nom.addClass('hidden');
        the_gamme.find('.btn-save-gamme-item')
            .addClass('btn-blink');
    });

    /** ENREGISTRER UNE GAMME */
    $(document).on('click', '.btn-save-gamme-item', function() {
        showLoader(true);
        var the_gamme = $(this).closest('.panel.gamme'),
            nom = the_gamme.find('.text-gamme-nom').text(),
            id = the_gamme.attr('data-gamme-id'),
            procedures = [],
            formData = new FormData();
        the_gamme.find('.list-group-item.procedure-item').each(function(index, item) {
            procedures.push($(item).attr('data-id'));
        });

        formData.append('nom', nom);
        formData.append('procedures', JSON.stringify(procedures));

        var url = Routing.generate('gamme_edit', { id: typeof id !== 'undefined' ? id : 0});
        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            console.log(data);
            showLoader(false);
            the_gamme.find('.btn-save-gamme-item').removeClass('btn-blink');
            getListeGammes();
        }).catch(function(error) {
            console.log(error);
            show_info("", "Une erreur est survenue.", "error");
            showLoader(false);
        })
    });

    /** SUPPRIMER UNE GAMME */
    $(document).on('click', '.btn-remove-gamme', function() {
        var btn = $(this);
        swal({
            title: '',
            text: "Voulez-vous supprimer cette Gamme ?",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1ab394',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then(function(result){
            if (result) {
                showLoader(true);
                var the_gamme = btn.closest('.panel.gamme'),
                    id = the_gamme.attr('data-gamme-id');
                if (typeof id === 'undefined' || id === '') {
                    the_gamme.closest('.gamme-col').remove();
                    showLoader(false);
                    return;
                }
                var url = Routing.generate('gamme_remove', { gamme: id});
                fetch(url, {
                    method: 'DELETE',
                    credentials: 'include'
                }).then(function(response) {
                    return response.json();
                }).then(function() {
                    the_gamme.closest('.gamme-col').remove();
                    showLoader(false);
                }).catch(function(err) {
                    console.log(err);
                    show_info("", "Une erreur est survenue.", "error");
                    showLoader(false);
                });
            }
        }).catch(swal.noop);
    });

    function ProcedureMove(selector) {
        var element = ".list-group";
        if (typeof selector === 'undefined') {
            selector = $(document);
        }

        selector.find(element).sortable({
            cursor: 'move',
            axis: 'y'
        }).disableSelection();
    }

    function findProcedure(id) {
        return procedure_listes.find(function(item) {
            return item.id === id;
        });
    }

    function getListeGammes() {
        showLoader(true);
        var url = Routing.generate('gamme_liste');
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            procedure_listes = data.procedures;
            procedure_select_options = '<option></option>';
            data.procedures.forEach(function(item, index) {
                procedure_select_options += '<option value="' + item.id + '">' + item.numero + ' ' + item.nom + '</option>';
            });

            $('#gamme-group').empty();
            data.gammes.forEach(function(item, index) {
               makeGamme(index, item);
               gamme_count = index + 1;
            });

            showLoader(false);
        }).catch(function(error) {
            console.log(error);
            show_info("", "Une erreur est survenue.", "error");
            showLoader(false);
        });
    }

    function makeGamme(index, data) {
        var  new_gamme = $('#new-gamme-item').clone(true, true);
        var new_gamme_id = 'collapse-' + index;
        new_gamme
            .removeAttr('id')
            .removeClass('hidden');
        new_gamme.find('.panel.gamme')
            .attr('data-gamme-id', data.id);
        new_gamme.find('a.text-gamme-nom')
            .attr('href', '#' + new_gamme_id)
            .text(data.nom);
        new_gamme.find('input.input-gamme-nom')
            .val(data.nom);
        new_gamme.find('.panel-collapse')
            .attr('id', new_gamme_id)
            .addClass('collapse');
        new_gamme.find('.btn-save-gamme-item')
            .removeClass('btn-blink');

        var liste_items = '',
            procedures_id = [];

        data.procedures.forEach(function(item) {
            liste_items +=  '<li class="list-group-item procedure-item" data-id="' + item.id + '"><span class="label label-default label-num-proc">' +
                                item.numero + '</span> ' + item.nom +
                                '<i class="fa fa-close pull-right btn-delete-procedure" style="cursor:default;"></i>' +
                            '</li>';
            procedures_id.push(item.id);
        });
        new_gamme.find('.list-procedure').html(liste_items);

        new_gamme.find('.list-procedure-select')
            .html(procedure_select_options);

        new_gamme.find('.list-procedure-select').chosen({
            search_contains: true,
            width: '100%',
            placeholder_text_single: '---',
            no_results_text: 'Aucun résultat'
        });
        updateProcedureDisabledOptions(new_gamme);
        new_gamme.appendTo($('#gamme-group'));
        ProcedureMove(new_gamme);
    }

    function updateProcedureDisabledOptions(gamme_selector) {
        var procedures_id = [];

        gamme_selector.find('.procedure-item').each(function(index, item) {
            procedures_id.push(parseInt($(item).attr('data-id'), 10));
        });



        gamme_selector.find('.list-procedure-select').find('option').each(function(index, item) {
            var id = $(item).val();
            if (typeof id !== 'undefined' && id !== '') {
                if (procedures_id.indexOf(parseInt(id, 10)) >= 0) {
                    $(item).attr('disabled', true);
                } else {
                    $(item).removeAttr('disabled');
                }
            } else {
                $(item).removeAttr('disabled');
            }
        });

        gamme_selector.find('.list-procedure-select').trigger("chosen:updated");
    }

    function showLoader(show) {
        if (show) {
            $('#loader').show();
        } else {
            $('#loader').hide();
        }
    }
});