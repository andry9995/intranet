/**
 * Created by TEFY on 27/07/2016.
 */

$(function () {
    //Lancement EXE

    $(document).on('click', 'td.server-app-launch', function (event) {
        event.preventDefault();
        event.stopPropagation();

        // |||cabinet|DOSSIER|EXERCICE|DATESCAN|LOT|NOMBRE IMAGE|IDLOT|IDOPERATEUR|IDTRAITEMENT|idcategorie
        var code_app = $(this).closest('tr').attr('data-app');
        var client = $(this).closest('tr').attr('data-client');
        var dossier = $(this).closest('tr').attr('data-dossier');
        var exercice = $(this).closest('tr').attr('data-exercice');
        var date_scan = $(this).closest('tr').attr('data-date-scan');
        var lot = $(this).closest('tr').attr('data-lot');
        var nb_image = $(this).closest('tr').attr('data-nb-image');
        var lot_id = $(this).closest('tr').attr('data-lot-id');
        var user_id = $(this).closest('tr').attr('data-user-id');
        var traitement_id = $(this).closest('tr').attr('data-traitement-id');
        var categorie_id = $(this).closest('tr').attr('data-categorie-id');

        var parametre = client + '|' + dossier + '|' + exercice + '|' + date_scan + '|' + lot + '|' + nb_image + '|' + lot_id + '|' + user_id + '|' + traitement_id + '|' + categorie_id;
alert(parametre);
        $.ajax({
            url: Routing.generate('application_exe_server', {code_app: code_app}),
            type: 'POST',
            data: {
                parametre: parametre
            },
            success: function (data) {
                if (data.erreur === true) {
                    show_info('', data.erreur_text, 'error');
                }
            }
        })
    });
});
