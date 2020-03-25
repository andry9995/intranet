$(function () {
    var window_height = window.innerHeight,
        client_list = $('#client-list'),
        loader = $('#loader'),
        form_calendrier_param = $('#form-calendrier-param'),
        selected_client = null,
        selected_client_id = null,
        calendrier_id = $('#calendrier-id'),
        calendrier_bg_color = $('#calendrier-bg-color'),
        calendrier_text_color = $('#calendrier-text-color'),
        btn_save_param = $('#btn-save-param'),
        send_to_google_elem = document.getElementById('send-to-google'),
        send_to_google = $('#send-to-google');
    /* Activer js-switch */
    var switchery = new Switchery(send_to_google_elem, {
        size: 'small',
        color: '#18a689'
    });

    loader.hide();
    form_calendrier_param.hide();
    btn_save_param.hide();

    updateSize();

    //RESIZE WINDOW
    window.addEventListener('resize', function() {
        updateSize();
    });

    /* Séléctionner un utilisateur */
    $(document).on('click', '#client-list .list-group-item', function (event) {
        event.preventDefault();

        $(this)
            .closest('.list-group')
            .find('.list-group-item')
            .removeClass('active');
        $(this).addClass('active');
        selected_client_id = $(this).attr('data-id');
        selected_client = $(this);

        clearForm();
        form_calendrier_param.hide(200);

        var url = Routing.generate('tache_google_calendrier_client', { client: selected_client_id});
        loader.show();

        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            // console.log(data);
            bindForm(data);
            form_calendrier_param.show(400);

            btn_save_param.show();
            loader.hide();
        }).catch(function(error) {
            show_info("", "Une erreur est survenue.", "error");
            console.log(error);
            loader.hide();
        });
    });

    /** ENREGISTRER PARAM */
    btn_save_param.on('click', function(e) {
        e.preventDefault();
        var formData = new FormData();
        formData.append('calendrier_id', calendrier_id.val());
        formData.append('calendrier_bg_color', calendrier_bg_color.val());
        formData.append('calendrier_text_color', calendrier_text_color.val());
        formData.append('send_to_google', send_to_google.prop('checked') === true ? '1' : '0');

        var url = Routing.generate('tache_google_calendrier_client_edit', { client: selected_client_id});

        loader.show();

        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            console.log(data);
            bindForm(data);
            form_calendrier_param.show(400);
            loader.hide();
            show_info("", "Paramètres enregistrés.", "success");
        }).catch(function(error) {
            show_info("", "Une erreur est survenue.", "error");
            console.log(error);
            loader.hide();
        })

    });

    function updateSize() {
        window_height = window.innerHeight;
        client_list.height(window_height - 210);
    }

    function clearForm() {
        calendrier_id.val('');
        calendrier_bg_color.val('#3cb08c');
        calendrier_text_color.val('#ffffff');
    }

    function bindForm(data) {
        if (!data) {
            if (send_to_google.prop('checked') === true) {
                send_to_google.parent().find(".switchery").trigger("click");
            }
            return 0;
        }
        calendrier_id.val(data.identifiant);
        calendrier_bg_color.val(data.color);
        calendrier_text_color.val(data.textColor);
        if (data.sendToGoogle) {
            //Actif
            if (send_to_google.prop('checked') === false) {
                send_to_google.parent().find(".switchery").trigger("click");
            }
        } else {
            //Inactif
            if (send_to_google.prop('checked') === true) {
                send_to_google.parent().find(".switchery").trigger("click");
            }
        }
    }
});