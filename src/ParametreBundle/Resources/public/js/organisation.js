$(function () {
    var window_height = window.innerHeight;
    var org_container = $('#org');
    org_container.height(window_height - 100);

    var diagram = new dhx.Diagram("org", {
        type: "org",
        defaultShapeType: "img-card",
        scroll: true,
        dragMode: true,
        select: true
    });

    window.myDiagram = diagram;

    var url = Routing.generate('parametre_organisation_liste');
    fetch(url, {
        method: 'GET',
        credentials: 'include'
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        parseDiagram(data);
    }).catch(function (error) {
        show_info("Erreur", "Une erreur est survenue.", "error");
        console.log(error);
    });

    diagram.attachEvent("onAfterAdd", function(id){
        makeContextMenu();
    });

    diagram.attachEvent("onShapeClick", function (id) {

    });

    diagram.attachEvent("onAfterLoad", function(data){
        // console.log(data);
    });

    diagram.attachEvent("onShapeDblClick", function (id) {
        editDiagram(id, false);
    });

    $(document).on('contextmenu', '.shape_content', function() {
       var id = $(this).closest('g.dhx_diagram_item').attr('dhx_id');
       diagram.selectItem(id);
       diagram.paint();
    });

    org_container.on('wheel', function(event) {
        event.preventDefault();
        var scale = 1;
        if (event.originalEvent.deltaY < 0) {
            //Zoom -
            scale = diagram.config.scale;
            if (scale > 0.5) {
                scale -= 0.1;
            }
        } else {
            //Zoom +
            scale = diagram.config.scale;
            scale += 0.1;
        }
        diagram.config.scale = scale;
        diagram.paint();
        // console.log(event);
    });


    /** CREER NOUVEAU DIAGRAM */
    $('#btn-create-diagram').on('click', function() {
        var formData = new FormData();
        formData.append('pid', '0');
        formData.append('titre', 'Titre');
        formData.append('nom', 'Nom');

        saveDiagram(formData);
    });

    /** ENREGISTRER DIAGRAM */
    $('#btn-save-diagram').on('click', function(event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('pid', $('#diagram-parent').val());
        formData.append('titre', $('#diagram-titre').val());
        formData.append('nom', $('#diagram-nom').val());
        formData.append('posteOldId', $('#poste-id-update').val());

        saveDiagram(formData);
    });





    function makeContextMenu(selector) {
        if (typeof selector === 'undefined') {
            selector = '.shape_content';
        }

        $.contextMenu({
            selector: selector,
            autoHide: true,
            items: {
                add: {
                    name: "Ajouter",
                    callback: function (key, opt) {

                        var id = $(opt.$trigger).closest('g.dhx_diagram_item').attr('dhx_id');
                        editDiagram(id, true);
                    },
                    icon: function (opt, $itemElement, itemKey, item) {
                        $itemElement.html('<span class="fa fa-plus" aria-hidden="true"></span> ' + item.name);
                        return 'context-menu-icon-updated';
                    }
                },
                edit: {
                    name: "Modifier",
                    callback: function (key, opt) {
                        var id = $(opt.$trigger).closest('g.dhx_diagram_item').attr('dhx_id');
                        editDiagram(id, false);
                    },
                    icon: function (opt, $itemElement, itemKey, item) {
                        $itemElement.html('<span class="fa fa-edit" aria-hidden="true"></span> ' + item.name);
                        return 'context-menu-icon-updated';
                    }
                },
                delete: {
                    name: "Supprimer",
                    callback: function (key, opt) {
                        var id = $(opt.$trigger).closest('g.dhx_diagram_item').attr('dhx_id');
                        removeDiagram(id);
                    },
                    icon: function (opt, $itemElement, itemKey, item) {
                        $itemElement.html('<span class="fa fa-trash" style="color:red;" aria-hidden="true"></span> ' + item.name);
                        return 'context-menu-icon-updated';
                    }
                }
            }
        });
    }

    function removeDiagram(id) {
        swal({
            title: 'Voulez-vous supprimer cet élément ?',
            text: "A noter que tout ses fils seront aussi supprimés !",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, Supprimer',
            cancelButtonText: 'Non, Annuler',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: true,
            width: '600px',
            reverseButtons: true,
            showCloseButton: true,
            showLoaderOnConfirm: true,
            animation: false,
            customClass: 'animated fadeInDown',
            preConfirm: function() {
                return new Promise(function(resolve, reject) {
                    var url = Routing.generate('parametre_organisation_remove', {org: id});
                    fetch(url, {
                        method: 'DELETE',
                        credentials: 'include'
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        data  = removeParent(data, id);
                        parseDiagram(data);
                        resolve();
                    }).catch(function(error) {
                        reject();
                        show_info("Erreur", "Une erreur est survenue.", "error");
                        console.log(error);
                    });
                });
            }
        }).then(function() {
            swal({
                title: 'Terminé',
                text: 'Elément(s) supprimé(s).',
                type: 'success',
                timer: 1500
            }).catch(swal.noop)
        }, function(dismiss) {
            // dismiss can be 'cancel', 'overlay',
            // 'close', and 'timer'

        });
    }

    function removeParent(tableau, id)
    {
        var tab = [];
        for (var idx = 0; idx < tableau.length; idx ++)
        {
            var value = tableau[idx];
            if (parseInt(value['id']) === parseInt(id)) {
                value['parent'] = null;
            }
            if (value['parent']!== null && parseInt(value['parent']) === parseInt(id))
                value['parent'] = null;
            tab[idx] = value;
        }

        return tab;
    }

    function updateParent(tableau, id, idParent, oldId)
    {
        var tab = [];
        for (var idx = 0; idx < tableau.length; idx ++)
        {
            var value = tableau[idx];
            if (parseInt(value['id']) === parseInt(id)) {
                value['parent'] = idParent;
            }
            if (parseInt(value['id']) === parseInt(oldId)) {
                value['parent'] = null;
            }
            if (value['parent']!== null && parseInt(value['parent']) === parseInt(oldId))
                value['parent'] = null;
            tab[idx] = value;
        }
        return tab;
    }

    function saveDiagram(formData) {
        var id = $('#diagram-id').val();
        var url = Routing.generate('parametre_organisation_update', {org: id});
        var is_new = false;
        if (id === '0') {
            url = Routing.generate('parametre_organisation_create');
            is_new = true;
        }

        fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            if (is_new) {
                var tmp = null;
                $('#btn-create-diagram').addClass('hidden');
            }
            else
            {
                data = updateParent(data, id, formData.get('titre') ,formData.get('posteOldId'));
            }
            //diagram.paint();
            parseDiagram(data);
            diagram.paint();
            makeContextMenu();
            $('#diagram-modal').modal('hide');
        }).catch(function(error) {
            show_info("Erreur", "Une erreur est survenue.", "error");
            console.log(error);
        });
    }

    function editDiagram(id, is_new) {
        if (typeof is_new === 'undefined') {
            is_new = false;
        }
        var items = [];

        diagram.eachItem(function(item){
            var tmp = '';

            items.push({
                id: item.config.id,
                text: item.config.text,
                title: item.config.title,
                org_niveau_id: item.config.org_niveau_id
            });
        });


        items.sort(function(a, b) {
            return a.title > b.title;
        });

        var options = '<option value="0"></option>';
        var optionsPoste = '<option value="0"></option>';
        var optionsTitre = '<option value="0"></option>';
        var niveauId = [];
        items.forEach(function(item, index) {

            options += '<option value="' + item.id + '">' + item.title + ' - ' + item.text + '</option>';
            optionsPoste += '<option value="' + item.id + '">' + item.text + '</option>';
            if ($.inArray(item.org_niveau_id , niveauId) === -1) {
                niveauId.push(item.org_niveau_id);
                optionsTitre += '<option value="' + item.org_niveau_id + '">' + item.title + '</option>';
            }
        });

        $('#diagram-parent').html(options);

        $('#diagram-nom').html(optionsPoste);

        $('#diagram-titre').html(optionsTitre);

        $('#diagram-id').val('0');
        $('#poste-id-update').val('0');

        if (is_new) {
            var titre = '';
            diagram.eachChild(id,function(item){
                titre = item.config.title;
                return 0;
            });
            var item = diagram.getItem(id);
            var nid = item.config.org_niveau_id;

            $('#diagram-parent').val(id);
            //$('#diagram-titre').val(titre);
            //$('#diagram-nom').val('');
            $('#diagram-titre').val('0');
            $('#diagram-titre').val(nid);
            $('#diagram-modal-title').text('Ajouter');
        } else {
            $('#diagram-id').val(id);
            var item = diagram.getItem(id);
            var pid = item.config.parent;
            var nid = item.config.org_niveau_id;
            var idp = item.config.id;
            $('#diagram-parent').val('0');
            $('#diagram-parent').val(pid);
            //$('#diagram-titre').val(item.config.title);
            //$('#diagram-nom').val(item.config.text);
            $('#poste-id-update').val(idp);
            $('#diagram-titre').val('0');
            $('#diagram-titre').val(nid);
            $('#diagram-nom').val('0');
            $('#diagram-nom').val(idp);
            $('#diagram-modal-title').text('Modifier');
        }
        $('#diagram-modal').modal('show');
    }

    function parseDiagram(data) {
        if (data.length === 0) {
            $('#btn-create-diagram').removeClass('hidden');
        } else {
            $('#btn-create-diagram').addClass('hidden');
        }
        diagram.parse(data);
        diagram.paint();
        $('#diagram-id').val('0');
        makeContextMenu();
    }
});
