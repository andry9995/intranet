/**
 * Created by TEFY on 27/10/2016.
 */

/** Reload data */
function reloadGrid(selector, url) {
    selector.setGridParam({
        url: url,
        datatype: 'json',
        loadonce: true,
        page: 1
    }).trigger('reloadGrid');
}

/** Afficher les dates séléctionnées */
function showDateList(dateList) {
    var date_format = 'DD-MM';
    var periode = $('#add-tache-periode').val();
    var item_number_max = parseInt(periode);
    if (item_number_max === 0) {
        date_format = 'DD-MM-YYYY';
    }
    $(document).find('#multi-date-container').empty();
    $.each(dateList, function (index, item) {
        if (item.trim() !== '' && item.trim().length === 10) {
            var date = moment(item.trim()).format(date_format);
            var el = '<span class="multi-date-item noselect" data-date="' + item + '">' + date;
            el += '<span class="close-item pointer" style="margin-left:5px;">&times;</span></span>';
            $(document).find('#multi-date-container').append(el);
        }
    });
}

/** Modal Ajout/Modif Tache par client */
function modalAddTache(client_id, edit, callback) {
    if (typeof edit === 'undefined') {
        edit = false;
    }

    $('.libre-group').removeClass('hidden');
    $('.legale-group').addClass('hidden');

    $.ajax({
        url: Routing.generate('tache_utilisateur_par_client', {client: client_id}),
        type: 'POST',
        success: function (data) {
            data = $.parseJSON(data);
            if (data.erreur === false) {
                $('#add-tache-responsable-client').empty()
                    .append('<option value=""></option>');
                $.each(data.utilisateurs, function (index, item) {
                    var option = '<option value="' + item.id + '">' + item.email + '</option>';
                    $('#add-tache-responsable-client').append(option);
                });
                if (typeof callback === 'function') {
                    callback();
                }
            } else {
                show_info('Erreur', data.erreur_text, 'error');
            }
        }
    });
    $('#tache-modal').modal('show');
    $('#add-tache-demarrage').datepicker({
        language: 'fr',
        daysOfWeekHighlighted: '0,6',
        todayHighlight: true,
        autoclose: true,
        format: 'dd/mm/yyyy',
        clearBtn: true
    });
    modalDraggable();

    $('#add-tache-error')
        .addClass('hidden')
        .empty();
    $('#responsable-client-group').addClass('hidden');
    $('#responsable-client-scriptura').addClass('hidden');

    if (edit) {
        $('#modal-tache-title').text('Modifier une tâche');
        return false;
    } else {
        var dateList = [];
        $('#modal-tache-title').text('Ajouter une tâche');

        return dateList;
    }
}

/** Valider champs taches clients ou taches dossiers */
function validateFormTacheClientDossier(is_tache_legale) {
    if (typeof is_tache_legale === 'undefined') {
        is_tache_legale = false;
    }
    var erreur_list = [];

    if (!is_tache_legale ) {
        if ($('#tache-generale').val() === '') {
            erreur_list.push("Choisir une tache");
        }

        var periode_elt = $('#add-tache-periode');
        var periode = periode_elt.val();
        var periode_text = periode_elt.find('option:selected').text();
        var item_number_max = parseInt(periode);
        if (item_number_max === 0) {
            item_number_max = 1;
            periode = 1;
        }
        var item_number = $(document).find('#multi-date-container .multi-date-item').length;

        if (item_number < item_number_max) {
            erreur_list.push("Vous devez choisir " + periode + " " + pluralize('date', 'dates', item_number_max) + " pour la période " + periode_text);
        }
    }
    return erreur_list;
}

/** Valider Form Taches Libres */
function validateFormTacheLibre() {
    var erreur_list = [];

    var tache_domaine = $('#add-tache-domaine');
    var tache_nom = $('#add-tache-nom');

    if (tache_nom.val().trim() === '') {
        erreur_list.push("Le nom de la tâche ne doit pas être vide.");
        tache_nom.closest('.form-group').addClass('has-error');
    }
    if (tache_domaine.val() === '0' || tache_domaine.val() === null) {
        erreur_list.push("Sélectionner un domaine dans la liste.");
        tache_domaine.closest('.form-group').addClass('has-error');
    }
    return erreur_list;
}