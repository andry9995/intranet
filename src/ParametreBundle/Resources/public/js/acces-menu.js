$(document).ready(function () {
    set_accordion();
    var user_search = document.getElementById('user-search');
    var window_height = window.innerHeight;
    var menu_list = $('#menu-list');
    var role_list = $('#role-list');
    var user_list = $('#user-list');
    var menu_list_user = $('#menu-list-user');
    var tab_container = $('#tab-container');

    tab_container.height(window_height - 150);
    role_list.height(tab_container.height() - 100);
    user_list.height(tab_container.height() - 170);
    menu_list.height(role_list.height());
    menu_list_user.height(user_list.height() + 35);

    setTimeout(function() {
        menu_list.nestable({
            group: 0,
            maxDepth: 2,
            reject: [{
                rule: function () {
                    var ils = $(this).find('>ol.dd-list > li.dd-item');
                    for (var i = 0; i < ils.length; i++) {
                        var datatype = $(ils[i]).data('type');
                        if (datatype === 'child')
                            return true;
                    }
                    return false;
                },
                action: function (nestable) {
                }
            }]
        }).nestable('collapseAll');
        menu_list_user.nestable({
            group: 0,
            maxDepth: 2,
            reject: [{
                rule: function () {
                    var ils = $(this).find('>ol.dd-list > li.dd-item');
                    for (var i = 0; i < ils.length; i++) {
                        var datatype = $(ils[i]).data('type');
                        if (datatype === 'child')
                            return true;
                    }
                    return false;
                },
                action: function (nestable) {
                }
            }]
        }).nestable('collapseAll');
        menu_list.nestable({handleClass:'123'});  
    },1000);

    /** Chercher un utilisateur */
    user_search.addEventListener('keyup', makeDebounce(function(e) {
        var search_text = (e.target.value).toLowerCase();
        $('#user-list').find('.list-group-item').each(function(index, item) {
            var item_text = $(item).text().toLowerCase();
            if (item_text.indexOf(search_text) >= 0) {
                $(item).removeClass('hidden');
            } else {
                $(item).addClass('hidden');
            }
        });
    }, 500));

    /* Séléction rôle */
    $(document).on('click', '.show-menu-post', function (event) {
        event.preventDefault();
        /*$(this)
            .closest('.list-group')
            .find('.list-group-item')
            .removeClass('active');
        $(this).addClass('active');*/
        $('.liste-user-menu')
            .find('.list-group-item')
            .removeClass('active');
        $('#btn-refresh-menu-acces').addClass('hidden');
        $('#id_acces_menu_accordion').find('.show-menu-post').removeClass('active');
        $('#id_acces_menu_accordion').find('.show-menu-post').parent().parent().removeAttr('style');
        $(this).parent().parent().css('background-color', '#d9edf7');
        $(this).addClass('active');
        $('#id_acces_menu_accordion').find('.panel-collapse').removeClass('in');
        $('#id_acces_menu_accordion').find('.panel-collapse').attr('aria-expanded', false);
        $('#btn-save-menu-user').attr('id', 'btn-save-menu-acces');
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

    /* Séléction rôle */
    $(document).on('click', '.show-menu-post-by-user', function (event) {
        event.preventDefault();
        $('.liste-user-menu')
            .find('.list-group-item')
            .removeClass('active');
        $(this).addClass('active');
        $('#btn-refresh-menu-acces').removeClass('hidden');
        $('#btn-save-menu-acces').attr('id', 'btn-save-menu-user');
        menu_list.find('.menu-select').prop('checked', false);
        menu_list.removeClass('hidden');
        var poste = $(this).attr('data-post-id');
        var operateur = $(this).attr('data-user-id');
        $('#btn-refresh-menu-acces').attr('data-id', operateur);

        $.ajax({
            url: Routing.generate('parametre_menu_par_operateur', {operateur: operateur}),
            type: 'GET',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);
                setMenuSettingsUser(data.menus, menu_list, data.menusRefuser);
            }
        });
    });

    $(document).on('change', '.menu-select', function () {
        var checkbox = $(this);
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
    });

    /* Enregistrer Menus par poste */
    $(document).on('click', '#btn-save-menu-acces', function (event) {
        event.preventDefault();
        console.log($('.show-menu-post.active').length)
        if ($('.show-menu-post.active').length > 0) {
            var poste = $('.show-menu-post.active')
                .attr('data-id');
            var menus = [];
            menu_list.find('.menu-select').each(function (index, item) {
                var state = $(item).prop('checked');
                if (state === true) {
                    menus.push({
                        menu: $(item).attr('data-menu-id'),
                    });
                }
            });
            $.ajax({
                url: Routing.generate('parametre_acces_menu_par_poste_edit', {poste: poste}),
                type: 'POST',
                data: {
                    menus: menus
                },
                success: function (data) {
                    data = $.parseJSON(data);
                    if (data.erreur === false) {
                        show_info("", "Paramètres enregistrés.", "success");
                        menu_list.find('.menu-select').prop('checked', false);
                        setMenuSettings(data.menus, menu_list)
                    } else {
                        show_info("", data.erreur_text, "error");
                    }
                }
            });
        } else {
            show_info("", "Séléctionner un rôle.", "warning");
        }
    });

    function setMenuSettings(data, parent) {
        if (typeof data === 'undefined') {
            return;
        }

        parent.find('.menu-select').removeAttr('disabled');
        $.each(data, function(index, item) {
            if (typeof item.menuIntranet !== 'undefined' && item.menuIntranet !== null) {
                var search = parent.find('.menu-select[data-menu-id="' + item.menuIntranet.id + '"]');
                if (search.length > 0) {
                    search.prop('checked', true);
                }
            }
        });
    }

    function setMenuSettingsUser(data, parent, dataPoste = null) {
        if (typeof data === 'undefined') {
            return;
        }

        if(dataPoste){
            parent.find('.menu-select').attr('disabled', 'disabled');
            $.each(dataPoste, function(index, item) {
                if (typeof item.menuIntranet !== 'undefined' && item.menuIntranet !== null) {
                    var search = parent.find('.menu-select[data-menu-id="' + item.menuIntranet.id + '"]');
                    if (search.length > 0) {
                        search.removeAttr('disabled');
                    }
                }
            });
        }

        $.each(data, function(index, item) {
            if (typeof item.menuIntranet !== 'undefined' && item.menuIntranet !== null) {
                var search = parent.find('.menu-select[data-menu-id="' + item.menuIntranet.id + '"]');
                if (search.length > 0) {
                    search.prop('checked', true);
                }
            }
        });
    }

    $(document).on('click', '#user-list .list-group-item', function (event) {
        event.preventDefault();

        $(this)
            .closest('.list-group')
            .find('.list-group-item')
            .removeClass('active');
        $(this).addClass('active');
        menu_list_user.find('.menu-select').prop('checked', false);
        menu_list_user.removeClass('hidden');

        var user_id = $(this).attr('data-id');
        $.ajax({
            url: Routing.generate('parametre_menu_par_operateur', {operateur: user_id}),
            type: 'GET',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);
                setMenuSettings(data, menu_list_user);
            }
        });
    });

    $(document).on('click', '#btn-refresh-menu-acces', function (event) {
        event.preventDefault();
        var operateur = $(this).attr('data-id');
        $.ajax({
            url: Routing.generate('parametre_menu_default', {operateur: operateur}),
            type: 'GET',
            data: {},
            success: function (data) {
                data = $.parseJSON(data);
                setMenuSettingsUser(data.menus, menu_list, data.menusRefuser);
                show_info("", "Paramètres enregistrés.", "success");
            }
        });
    })

    /* Enregistrer Menus par Operateur */
    $(document).on('click', '#btn-save-menu-user', function (event) {
        event.preventDefault();
        console.log($('.liste-user-menu').find('.list-group-item.active').length)
        if ($('.liste-user-menu').find('.list-group-item.active').length > 0) {
            var user = $('.liste-user-menu').find('.list-group-item.active')
                .attr('data-id');
            var menus = [];
            menu_list.find('.menu-select').each(function (index, item) {
                var state = $(item).prop('checked');
                if (state === true) {
                    menus.push({
                        menu: $(item).attr('data-menu-id')
                    });
                }
            });

            $.ajax({
                url: Routing.generate('parametre_menu_par_operateur_edit', {operateur: user}),
                type: 'POST',
                data: {
                    menus: menus
                },
                success: function (data) {
                    data = $.parseJSON(data);
                    if (data.erreur === false) {
                        show_info("", "Paramètres enregistrés.", "success");
                        menu_list_user.find('.menu-select').prop('checked', false);
                        setMenuSettings(data.menus, menu_list_user);
                    } else {
                        show_info("", data.erreur_text, "error");
                    }
                }
            });
        } else {
            show_info("", "Séléctionner un rôle.", "warning");
        }
    });

    /* Ouvrir tout / Réduire tout - liste menus - acces operateur */
    $(document).on('click', '.btn-collapse-list-menu', function (event) {
        event.preventDefault();
        var target = $(this).attr('data-target');
        var action = $(this).attr('data-action');
        if (action === 'expand-all') {
            $(target).nestable('expandAll');
        } else {
            $(target).nestable('collapseAll');
        }
    });

    $(document).on('click', '.acces-user-menu', function () {
        
    });
});


function set_accordion()
{
    $('#id_acces_menu_accordion').on('show.bs.collapse', function (e) {
        if ($(e.target).closest('.panel').hasClass('js_regime'))
        {
            $(e.target).closest('.panel').find('.cl_taches_edit').removeClass('hidden');
            $(e.target).closest('.panel').find('.cl_edit_taches_group').removeClass('hidden');
        }
    }).on('hide.bs.collapse', function (e) {
        if ($(e.target).closest('.panel').hasClass('js_regime'))
        {
            $(e.target).closest('.panel').find('.cl_taches_edit').addClass('hidden');
            $(e.target).closest('.panel').find('.cl_edit_taches_group').addClass('hidden');
        }
    });
}