var client_selector = $('#client'),
    site_selector = $('#site'),
    dossier_selector = $('#dossier'),
    loader_selector = $('#loader');

function setHeight()
{
    var height = $('#conteneur-tableau').height($(window).height() - 240);
    var width = $('#conteneur-tableau').width();
    $('#id-table-etape').setGridWidth(width);
    $('#id-table-etape').setGridHeight(height);
}
//Resize window
$(window).bind('resize', function () {
    setHeight();
});
$(document).ready(function(){
    //Clic sur radio par client
    $(document).on('click', '#id-par-client', function(event){
        $('#id-content-dossier').addClass('hidden');
    });
    //Clic sur radio par client
    $(document).on('click', '#id-par-dossier', function(event){
        $('#id-content-dossier').removeClass('hidden');
    });

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

    //Clic bouton go
    $(document).on('click', '#btn-show-workflow', function(event){
        var client = $('#client').val();
        var dossier = $('#dossier').val();
        var exo = $('#exercice').val();
        var isParClient = $('#id-par-client').is(':checked');
        if (isParClient)
            isParClient = 1;
        else
            isParClient = 0;
        $('#conteneur-tableau').html('<table id="id-table-etape">' +
            '</table>' +
            '<div id="pager_table_etape"></div>');
        $.ajax({
            data: { clients:client, dossiers:dossier, exercice:exo,isParClient:isParClient },
            type: 'POST',
            url: Routing.generate('parametre_workflow_categorie'),
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data) {
                var donnee = $.parseJSON(data);
                $("#id-table-etape").jqGrid({
                    data: donnee.data,
                    datatype: "local",
                    height: $('#conteneur-tableau').height($(window).height()- 240),
                    autowidth: true,
                    shrinkToFit: true,
                    rowNum: 1000,
                    rowList: [1000],
                    colNames: donnee.entete,
                    colModel: donnee.model

                    /*[
                        {name: 'id', index: 'id', width: 60, sorttype: "int"},
                        {name: 'invdate', index: 'invdate', width: 90, sorttype: "date", formatter: "date"},
                        {name: 'name', index: 'name', width: 100},
                        {name: 'amount', index: 'amount', width: 80, align: "right", sorttype: "float", formatter: "number"},
                        {name: 'tax', index: 'tax', width: 80, align: "right", sorttype: "float"},
                        {name: 'total', index: 'total', width: 80, align: "right", sorttype: "float"},
                        {name: 'note', index: 'note', width: 150, sortable: false}
                    ]*/,
                    pager: "#pager_table_etape",
                    viewrecords: true,
                    edit: true,
                    caption: "Liste étape par catégorie <i class='fa fa-floppy-o pointer'></i>",
                    hidegrid: false,
                    editurl: Routing.generate('parametre_workflow_editurl'),
                    ajaxRowOptions: {async: true},
                });
                $(".ui-jqgrid-titlebar").hide();

                //Mise en gras catégorie si au moins un coché
                setRowToBold();

            }
        });
    });

    function setRowToBold()
    {
        $('#id-table-etape').find('tr').each(function(){
            var iCpt = 0;
            $(this).find('td input[type="checkbox"]:enabled').each(function(){
                if ($(this).is(':checked')) {
                    iCpt++;
                    return null;
                }
            });
            if (iCpt > 0)
                $(this).addClass('gras');
            else
                $(this).removeClass('gras');
        });
    }
    /*
    Enregistrer les étapes choisies
     */
    $(document).on('click', '#btn-save-workflow', function(event){
        var client = $('#client').val();
        var dossier = $('#dossier').val();
        var exo = $('#exercice').val();
        var isParClient = $('#id-par-client').is(':checked');
        if (isParClient)
            isParClient = 1;
        else
            isParClient = 0;
        var resultat = [];
        $('#id-table-etape').find('tr').each(function(){
            if (!$(this).hasClass('jqgfirstrow')) {
                var infos = [];

                $(this).find('td input[type="checkbox"]:enabled').each(function () {
                    if ($(this).is(':checked')) {
                        var described = $(this).closest('td').attr('aria-describedby');
                        var splitId = described.split('-');
                        infos.push(splitId[splitId.length - 1]);
                    }
                });
                resultat.push({idCateg: $(this).find('.categorie-name').attr('data-id'), idEtape: infos});
            }
        });

        $.ajax({
            data: { idClient:client, idDossier:dossier, exercice:exo, resultats: JSON.stringify(resultat), isParClient:isParClient },
            type: 'POST',
            url: Routing.generate('parametre_workflow_save'),
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data) {
                $('#test').html(data);
                setRowToBold();
//                show_info('Enregistrement', 'Enregistrement avec succés',);
            }
        });
    });

});