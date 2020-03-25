var window_height = window.innerHeight;
//$('#liste-container').height(window_height - 140);
var client_selector = $('#client'),
    site_selector = $('#site'),
    dossier_selector = $('#dossier');
var loader_selector = $('#loader');


client_selector.val('').trigger('chosen:updated');
site_selector.val('').trigger('chosen:updated');
dossier_selector.val('').trigger('chosen:updated');


$(document).ajaxStart(function(){
    loader_selector.show();
});
$(document).ajaxStop(function(){
    loader_selector.hide();
});


function setHeight()
{
    $('.content-proposer').height($(window).height() - 200);
}


//Resize window
$( window ).resize(function() {
    setHeight();
});



$(document).ready(function(){
    setHeight();

    $(".sortable-list").sortable({
        connectWith: ".connectList"
    }).disableSelection();

    /*$('#nestable-dispo').nestable({
        group: 0
    })*/
    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (!$('#id-content-dossier').hasClass('hidden')) {
            siteParClientMulti(client_selector, site_selector, loader_selector, function () {
                dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, function () {
                    loader_selector.hide();
                })
            });
        }
    });

    // Changement site
    /*$(document).on('change', '#site', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, function() {
            //loader_selector.html('');
        });
    });*/

    //Clic bouton go
    $(document).on('click', '#btn-show-workflow', function(event){
        var client = $('#client').val();
        var dossier = $('#dossier').val();
        var exo = $('#exercice').val();
        $.ajax({
            data: { clients:client, dossiers:dossier, exercice:exo },
            type: 'POST',
            url: Routing.generate('parametre_workflow_categorie'),
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data) {
                $('#id-liste-categorie').html(data);
            }
        });
    });

    //Clic sur radio par client
    $(document).on('click', '#id-par-client', function(event){
        $('#id-content-dossier').addClass('hidden');
    });
    //Clic sur radio par client
    $(document).on('click', '#id-par-dossier', function(event){
        $('#id-content-dossier').removeClass('hidden');
    });


    var updateOutput = function (e) {
        var list = e.length ? e : $(e.target),
            output = list.data('output');
        if (window.JSON) {
            output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
        } else {
            output.val('JSON browser support required for this application.');
        }

    };

    //On clic catégorie
    $(document).on('click', '.liste-categorie', function(event){
        var idCateg = $(this).attr('data-id');
        var codeCateg = $(this).attr('data-code');
        var idClient = $('#client').val();
        var idDossier = $('#dossier').val();
        var exo = $('#exercice').val();
        $('#id-input-categ').val(idCateg);
        $(this).closest('ul').find('li').removeClass('active');
        $(this).addClass('active');
        $.ajax({
            data: { idClient:idClient, idCateg:idCateg, idDossier:idDossier, exercice:exo },
            type: 'POST',
            url: Routing.generate('parametre_workflow_etape_choisie'),
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data) {
                $('#id-liste-etape-choisi').html(data);

                //Liste id etape choisie
                var listeIdEtape = [];
                $('#id-liste-etape-choisi').find('li').each(function(){
                    listeIdEtape.push($(this).attr('data-id'));
                });
                $.ajax({
                    data:{listeIdEtape:JSON.stringify( listeIdEtape)},
                    type: 'POST',
                    url: Routing.generate('parametre_workflow_etape_dispo'),
                    contentType: "application/x-www-form-urlencoded;charset=utf-8",
                    beforeSend: function(jqXHR) {
                        jqXHR.overrideMimeType('text/html;charset=utf-8');
                    },
                    dataType: 'html',
                    success: function(data)
                    {
                        $('#id-liste-etape-dispo').html(data);
                    }
                });
                // activate Nestable etape choisie
                $('#nestable-choisi').nestable({
                    group: 1
                }).on('change', updateOutput);
                updateOutput($('#nestable-choisi').data('output', $('#nestable-output')));
            }
        });
    });


    //Save étape sélectionnée
    $(document).on('click', '#btn-save-workflow', function(event){
        var listeIdEtape = [];
        $('#id-liste-etape-choisi').find('li').each(function(){
            if ($(this).attr('data-id') > 0)
                listeIdEtape.push($(this).attr('data-id'));
        });
        var idClient = $('#client').val();
        var idDossier = $('#dossier').val();
        var exercice = $('#exercice').val();
        var isParClient = $('#id-par-client').is(':checked');
        var idCategorie = $('#id-input-categ').val();

        $.ajax({
            data:{listeIdEtape:JSON.stringify(listeIdEtape),
                idClient:idClient,
                idDossier:idDossier,
                idCategorie:idCategorie,
                exercice:exercice,
                isParClient:isParClient  },
            type: 'POST',
            url: Routing.generate('parametre_workflow_save'),
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data)
            {
                show_info('Enregistrement', 'Etape traitement enregistrée');
            }

        });
    });


});

