/**
 * Created by TEFY on 26/04/2016.
 */
$(function () {
    // Mettre en surbrillance le menu actif
    menu_active();

    minimizeLeftNavMenu();

    $(document).find('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });

    //Lancement d'une application utilisateur
    $('.user-app-launch').on('click', function (event) {
        var user_app = $(this).attr('data-app');

        $.ajax({
            'url': Routing.generate('application_exe_local', {'code_app': user_app}),
            type: 'POST',
            success: function (data) {

            }
        })
    })

    $(document).on('click', '#idGestionCategNature', function(){

        $.ajax({
            url: Routing.generate('parametre_gestion_categorie_nature'),
            type: 'POST',
            data: { ETAPE_ID: 25},
            success: function (data) {
                if (data == true)
                    show_info('Gestion catégorie - nature', 'Lancement du programme gestion catégorie/nature','success', 2000 );
                else
                    show_info('Gestion catégorie - nature', 'Vous devez lancer l\'application interface', 'error', 2000 );
            }
        });
    });
});