$(function () {
    var listSexe = {value: ":Tous;F:F;M:M"};
    var listAffDos = {value: ":;Non:Non;Oui:Oui"};
    var lastsel;
    $('#js_personnel_liste').jqGrid({
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
        //IgnoreCase: true,
        rowNum: 1000,
        rowList: [10, 20, 30, 50, 100, 500, 1000],
        pager: '#pager_liste_personnel',
        caption: 'Liste des personnels',
        //caseSensitive: false,

        hidegrid: false,
        editurl: Routing.generate('parametre_personnel_edit'),
        //colNames: ['Matricule', 'Nom', 'Prénom', 'Adresse', 'Téléphone', 'Sexe', 'Login', 'Mot de passe', 'Date Entrée', 'Date So rtie', 'Poste_Id', 'Poste', 'Role_Id', 'Rôle','Rattachement_Id', 'Rattachement', '<span style="display:inline-block"/>Reinitialiser Pwd', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action', ],
        //colNames: ['Matricule', 'Nom', 'Prénom', 'Téléphone', 'Sexe', 'Login', 'Mot de passe', 'Date Entrée', 'Date Sortie', 'Poste_Id', 'Poste', 'Role_Id', 'Rôle','Rattachement_Id', 'Rattachement', 'Affect. Dos.', '<span style="display:inline-block"/>Reinitialiser Pwd', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action', ],
        colNames: ['Matricule', 'Nom', 'Prénom', 'Téléphone', 'Sexe', 'Login', 'Mot de passe', 'Date Entrée', 'Date Sortie', 'Poste_Id', 'Poste', 'Rattachement_Id', 'Rattachement', 'Affect. Dos.', '<span style="display:inline-block"/>Reinitialiser Pwd', '<span class="fa fa-bookmark-o" style="display:inline-block"/> Action', ],

        colModel: [
            {name: 'matricule', index: 'matricule', width: 80, align: "center", sorttype: 'integer', classes: 'js-personnel-matricule'},
            {name: 'nom', index: 'nom', width: 150,  editable: true, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-nom'},
            {name: 'prenom', index: 'prenom', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-prenom'},
            //{name: 'adresse', index: 'adresse', editable: true, width: 200, sorttype: function(cell) { return jqGridSortable(cell); }, classes: 'js-personnel-adresse'},
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
            /*{name: 'role-id', index: 'role-id', hidden: true, classes: 'js-personnel-role-id'},*/
            /*{name: 'role', index: 'role', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select", editoptions: { dataUrl: Routing.generate('access_operateur_liste', { json: 0}),
                dataInit: function(elem) {
                    $(elem).width(100);
                }}, classes: 'js-personnel-role'},*/
            {name: 'rattachement-id', index: 'rattachement-id', hidden: true, classes: 'js-personnel-rattachement-id'},
            {name: 'rattachement', index: 'rattachement', editable: true, width: 150, sorttype: function(cell) { return jqGridSortable(cell); },
                edittype:"select",
                editoptions: { dataUrl: Routing.generate('operateur_chef_superieur'), /*dataUrl: Routing.generate('operateur_rattachement_liste')*/
                            postData: function (rowid) {
                                return {
                                        posteId: $("#" + rowid + " .js-personnel-poste-id").html(),
                                    }
                            },

                    dataInit: function(elem) {
                        $(elem).width(100);

                    }
                    }, classes: 'js-personnel-rattachement'},
            {name: 'affecter-dossier', index: 'affecter-dossier', width: 100, align: "center", stype:"select",
                searchoptions: listAffDos,editable: true, edittype:"select", editoptions:{value:":;Non:Non;Oui:Oui"},
                classes: 'js-personnel-affecter-dossier'},
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

    $("#js_personnel_liste").filterToolbar();


    // Enregistrement modif personnel

    $(document).on('click', '.js-save-personnel', function(event) {
        event.preventDefault();
        event.stopPropagation();

        $('#js_personnel_liste').jqGrid('saveRow', lastsel);
    });

    //Charger les chefs n+1
    function setChefSuperieur(posteId, idselect) {
        //alert(posteId + " "+idselect);
        $.ajax({
            url: Routing.generate('operateur_chef_superieur'),
            type: 'POST',
            data: {
                orgId: posteId},
            success: function(data){
               $('#' + idselect).html(data);
            }
        })
    }

    // Enregistrement ajout personnel
    $(document).on('click', '#btn-add-personnel-save', function (event) {
        event.preventDefault();
        $(document).find('#add-personnel-erreur').addClass('hidden');
        var matricule = $(document).find('#add-personnel-matricule').val().trim();
        var nom = $(document).find('#add-personnel-nom').val().trim();
        var prenom = $(document).find('#add-personnel-prenom').val().trim();
        var adresse = $(document).find('#add-personnel-adresse').val().trim();
        var telephone = $(document).find('#add-personnel-telephone').val().trim();
        var sexe = $(document).find('#add-personnel-sexe').val().trim();
        var login = $(document).find('#add-personnel-login').val().trim();
        var password = $(document).find('#add-personnel-password').val().trim();
        var poste = $(document).find('#add-personnel-poste').val().trim();
        var role = $(document).find('#add-personnel-role').val().trim();
        var rattachement = $(document).find('#add-personnel-rattachement').val().trim();
        var id = $(document).find('#add-personnel-id').val();

        if (nom != '') {
            $.ajax({
                url: Routing.generate('parametre_personnel'),
                type: 'POST',
                data: {
                    id: id,
                    matricule: matricule,
                    nom: nom,
                    prenom: prenom,
                    adresse: adresse,
                    telephone: telephone,
                    sexe: sexe,
                    login: login,
                    password: password,
                    poste: poste,
                    role: role,
                    rattachement: rattachement,
                },
                success: function (data) {
                    var response = $.parseJSON(data);
                    if (response.erreur == true) {
                        $('#add-personnel-erreur')
                            .removeClass('hidden')
                            .text(response.erreur_text);
                    } else {
                        $('#add-personnel-erreur').addClass('hidden');
                        $('#add-personnel-matricule').val('');
                        $('#add-personnel-nom').val('');
                        $('#add-personnel-prenom').val('');
                        $('#add-personnel-adresse').val('');
                        $('#add-personnel-telephone').val('');
                        $('#add-personnel-sexe').val('');
                        $('#add-personnel-login').val('');
                        $('#add-personnel-password').val('');
                        $('#add-personnel-poste').val('0');
                        $('#add-personnel-role').val('0');
                        $('#add-personnel-rattachement').val('0');
                        $('#personnel-modal').modal('hide');

                        $("#js_personnel_liste").setGridParam({datatype: 'json', page: 1}).trigger('reloadGrid');

                    }
                }
            });
        }
    });
    // Ajouter un personnel
    $(document).on('click', '#btn-add-personnel', function (event) {
        event.preventDefault();
        $(document).find('#modal-personnel-title').text('Ajouter un personnel');
        $(document).find('#add-personnel-id').val('');
        $(document).find('#add-personnel-poste').val('0');
        $(document).find('#personnel-modal').modal();
        modalDraggable();
    });

    // Modifier un personnel
    $(document).on('click', '.js-edit-personnel', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var row = $(this).closest('tr');
        var rowid = row.attr('id');

        $(document).find('#add-personnel-id').val('');
        $(document).find('#add-personnel-id').val(rowid);
        $(document).find('#add-personnel-matricule').val(row.find('.js-personnel-matricule').text());
        $(document).find('#add-personnel-nom').val(row.find('.js-personnel-nom').text());
        $(document).find('#add-personnel-prenom').val(row.find('.js-personnel-prenom').text());
        $(document).find('#add-personnel-adresse').val(row.find('.js-personnel-adresse').text());
        $(document).find('#add-personnel-telephone').val(row.find('.js-personnel-telephone').text());
        $(document).find('#add-personnel-sexe').val(row.find('.js-personnel-sexe').text());
        $(document).find('#add-personnel-login').val(row.find('.js-personnel-login').text());
        $(document).find('#add-personnel-password').val(row.find('.js-personnel-password').text());
        $(document).find('#add-personnel-poste').val(row.find('.js-personnel-poste-id').text());
        $(document).find('#add-personnel-role').val(row.find('.js-personnel-role-id').text());
        $(document).find('#add-personnel-rattachement').val(row.find('.js-personnel-rattachement-id').text());
        $(document).find('#modal-personnel-title').text('Modifier un personnel');
        $(document).find('#personnel-modal').modal();
        modalDraggable();
    });


    // Supprimer un personnel
    $(document).on('click', '.js-delete-personnel', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');
        $('#js_personnel_liste').jqGrid('delGridRow', rowid, {
            url: Routing.generate('parametre_personnel'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cet enregistrement ?'
        });
    });

    // Reinitialiser mot de pass operateur
    $(document).on('click', '.js-erase-pwd-personnel', function(event) {
        event.preventDefault();

        var id = $(this).closest('tr').attr('id');
        swal({
                title: "Confirmation",
                text: "Etes-vous sûr de vouloir Reinitialiser le Mot de pass de ce personnel ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1ab394",
                cancelButtonColor: "#acacac",
                confirmButtonText: "Oui, Reinitialiser !",
                cancelButtonText: "Non, annuler !"
            }).then (function () {
                $.ajax({
                     url: Routing.generate('erase_pwd_personnel'),
                     type: 'POST',
                     data: {
                        idopera: id
                     },
                     success: function (data) {
                         var response = $.parseJSON(data);
                         if (response.erreur === true) {
                             $('#add-personnel-erreur')
                             .removeClass('hidden')
                             .text(response.erreur_text);
                         } else if(response.etat == "done") {
                             swal("Reinitialisation Faite", "Le mot de pass a été réinitialisé.", "success");
                         }
                     }
                });
        });
    });
});