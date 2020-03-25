var controleGrid = $('#controle-list'),
    desassembleGrid = $('#desassemble-list');

function trouCellAttr(rowId, val, rawObject, cm, rdata) {
    if (val !== '0,00' && val !== '0.00') {
        return ' style="color:#ed5565;"';
    }
    else {
        return ' style="color:transparent;"';
    }
}

function doublonCellAttr(rowId, val){

    if(val !== '' && val !== undefined && val !== "&nbsp;" && val !== null && val !== '&#160;'){
        return ' style="color:#ed5565;"';
    }
}

function montantCellAttr(rowId, val, rawObject, cm, rdata){
    if(cm.name === 'c_solde_debut'){
        var ctrl = rawObject.c_c_solde_debut;
        if(ctrl === 'X'){
            return ' style="color:#ed5565"';
        }
        else if(ctrl === 'Y'){
            return ' style="color:#1ab394"';
        }

    }
    else if(cm.name === 'c_solde_fin'){
        var ctrl = rawObject.c_c_solde_fin;
        if(ctrl === 'X'){
            return ' style="color:#ed5565"';
        }
        else if(ctrl === 'Y'){
            return ' style="color:#1ab394"';
        }
    }
    return '';
}

$(function() {



    controleGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        // height: 400,
        hidegrid: false,
        caption: 'Controle Image, Trou et  Doublons',
        colNames: [
            'Image', '','Periode debut', 'Periode Fin', 'Solde Debut', 'Solde Fin', '','','Ass', 'Ass Ids','Trou', 'Doublon'
        ],
        colModel: [
            {
                name: 'c_image',
                index: 'c_image',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-image pointer',
                width: 90
            },
            {
                name: 'c_source',
                index: 'c_source',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-source',
                width: 30
            },
            {
                name: 'c_periode_debut',
                index: 'c_periode_debut',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-periode-debut',
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                width: 100
            },
            {
                name: 'c_periode_fin',
                index: 'c_periode_fin',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-periode-fin',
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                width: 100
            },
            {
                name: 'c_solde_debut',
                index: 'c_solde_debut',
                align: 'right',
                editable: true,
                sortable: true,
                classes: 'js-c-solde-debut',
                formatter: 'number',
                width: 100,
                cellattr: montantCellAttr
            },
            {
                name: 'c_solde_fin',
                index: 'c_solde_fin',
                align: 'right',
                editable: true,
                sortable: true,
                classes: 'js-c-solde-fin',
                formatter: 'number',
                width: 100,
                cellattr: montantCellAttr
            },
            {
                name: 'c_c_solde_debut',
                index: 'c_c_solde_debut',
                align: 'right',
                editable: true,
                sortable: true,
                hidden: true,
                classes: 'js-c-c-solde-debut',
                width: 20
            },
            {
                name: 'c_c_solde_fin',
                index: 'c_c_solde_fin',
                align: 'right',
                editable: true,
                sortable: true,
                hidden :true,
                classes: 'js-c-c-solde-fin',
                width: 20
            },
            {
                name: 'c_nb_assembler',
                index: 'c_nb_assembler',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-nb-assembler pointer',
                width: 90
            },
            {
                name: 'c_id_assembler',
                index: 'c_id_assembler',
                hidden: true,
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-id-assembler',
                width: 80
            },
            {
                name: 'c_trou',
                index: 'c_trou',
                align: 'right',
                editable: true,
                sortable: true,
                classes: 'js-c-trou pointer',
                formatter: 'number',
                width: 100,
                cellattr: trouCellAttr
            },
            {
                name: 'c_doublon',
                index: 'c_doublon',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-c-doublon pointer',
                cellattr: doublonCellAttr
            }
        ],
        loadComplete: function(){
            if ($('#btn-restore-doublon').length === 0) {
                controleGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').
                after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-restore-doublon" class="btn btn-outline btn-danger btn-xs" style="margin-right: 20px;"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Restaurer les doublons</button></div>');
            }
            if($('#btn-restore-assemble').length === 0){
                controleGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').
                after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-restore-assemble" class="btn btn-outline btn-warning btn-xs" style="margin-right: 20px;"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Désassembler</button></div>');
            }

            initButtons($('#statustemp').val());

            var dataIDs = controleGrid.getDataIDs(),
                modalBodyHeight = $('#myModal').find('.modal-body').height(),
                rows = controleGrid.closest('.ob-container').find('.row'),
                obContainerHeight = 0;

            rows.each(function(){
               if(!$(this).hasClass('row-grid')){
                   obContainerHeight += $(this).height();
               }
            });

            controleGrid.jqGrid("setGridHeight", modalBodyHeight - obContainerHeight - 10);

            for(var i = 0; i < dataIDs.length; i++)
            {
                var rowData = controleGrid.jqGrid ('getRowData', dataIDs[i]);

                if(rowData['c_source'] !== '' && Math.abs(rowData['c_trou']) > 0){
                    //Jerena ny lignes eo amboniny
                    if(i > 0){
                        var rowDataPrec = controleGrid.jqGrid('getRowData', dataIDs[i-1]);
                        if(rowDataPrec['c_source'] === ''){
                            controleGrid
                                .find('tr#'+dataIDs[i])
                                .find('.js-c-source')
                                .addClass('pointer sobank-edit')
                                .attr('data-rel', dataIDs[i-1]);

                        }
                    }
                }
            }

        }
    });


    desassembleGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        height: 300,
        hidegrid: false,
        caption: 'Image assemblées',
        colNames: [
            'Image Assemblée', '', 'Image originale', '', 'Action'
        ],
        colModel: [
            {
                name: 'd_image',
                index: 'd_image',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-d-image pointer',
                width: 90
            },
            {
                name: 'd_image_id',
                index: 'd_image_id',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-d-image',
                hidden: true
            },
            {
                name: 'd_image_or',
                index: 'd_image_or',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-d-image-or pointer',
                width: 90
            },

            {
                name: 'd_image_id_or',
                index: 'd_image_id_or',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-d-image-id-or',
                hidden: true
            },

            {
                name: 'd_action',
                index: 'd_action',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-d-doublon pointer',
                editoptions: {
                    defaultValue: '<i class="fa fa-unlink icon-action d-action" title="Desassembler"></i>'
                }

            }
        ],

        loadComplete: function(){
            desassembleGrid.jqGrid('setGridWidth',desassembleGrid.closest('.modal-body').width() - 10);
        }
    });


    $(document).on('click', '.sobank-edit', function(){
        var sobankid = $(this).closest('tr').attr('id'),
            releveid = $(this).attr('data-rel');

        $.ajax({
            url: Routing.generate('banque_sobank'),
            type: 'GET',
            data:{
                sobankid: sobankid,
                releveid: releveid
            },
            success: function(data){

                var sobankModal = $('#sobank-modal');

                sobankModal.find('.modal-body').html(data);
                sobankModal.modal('show');


                var lastsel_sb,
                    sobankGrid = $('#sobank-list'),
                    width = sobankGrid.closest('.col-lg-6').width(),
                    url = Routing.generate('banque_releve_image_details', {
                        image:sobankid,
                        soldedebut: 0,
                        soldefin: 0
                    }),
                    editurl = Routing.generate('banque_releve_dossier_details_edit',{imageid:sobankid});

                sobankGrid.jqGrid({
                    datatype: 'json',
                    loadonce: true,
                    sortable: true,
                    width: width,
                    height: 320,
                    shrinkToFit: true,
                    viewrecords: true,
                    hidegrid: false,
                    rownumbers: true,
                    rownumWidth: 30,
                    url: url,
                    editurl: editurl,
                    caption: 'Détails du Relevé',
                    colNames: [
                        'Date', 'Libelle', 'Débit', 'Crédit', 'Action'
                    ],
                    colModel: [

                        {
                            name: 'r-date',
                            index: 'r-date',
                            align: 'center',
                            editable: true,
                            editoptions: {
                                dataInit: function (el) {
                                    $(el).datepicker(
                                        {
                                            format:'dd/mm/yyyy',
                                            language: 'fr',
                                            autoclose:true,
                                            startView: 1
                                        });
                                }
                            },
                            sortable: true,
                            width: 100,
                            classes: 'js-r-date',
                            formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                            sorttype: 'date'
                        },
                        {
                            name: 'r-libelle',
                            index: 'r-libelle',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            classes: 'js-r-libelle'
                        },
                        {
                            name: 'r-debit',
                            index: 'r-debit',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            width: 90,
                            classes: 'js-r-debit',
                            formatter: 'number',
                            sorttype: 'number'
                        },
                        {
                            name: 'r-credit',
                            index: 'r-credit',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            width: 90,
                            classes: 'js-r-credit',
                            formatter: 'number',
                            sorttype: 'number'
                        },
                        {
                            name: 'r-action',
                            index: 'r-action',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 30,
                            fixed: true,
                            classes: 'js-r-action',
                            editoptions: {
                                defaultValue: '<i class="fa fa-save icon-action r-action" title="Enregistrer"></i>'
                            }
                        }

                    ],
                    onSelectRow: function (id) {
                        if (id && id !== lastsel_sb) {
                            sobankGrid.restoreRow(lastsel_sb);
                            lastsel_sb = id;
                        }
                        sobankGrid.editRow(id, false);
                    },

                    beforeSelectRow: function (rowid, e) {
                        var target = $(e.target);

                        var item_action = (target.closest('td').children('.icon-action').length > 0);

                        return !item_action;
                    },

                    loadComplete: function (data) {

                        if ($('#btn-add-sobank').length === 0) {
                            sobankGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                                '<button id="btn-add-sobank" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
                        }


                        $(document).on('click', '#btn-add-sobank', function(event){

                            event.stopPropagation();

                            var canAdd = true,
                                rows = sobankGrid.find('tr');

                            rows.each(function () {
                                if ($(this).attr('id') === 'new_row') {
                                    canAdd = false;
                                }
                            });

                            if(canAdd){
                                if(!$.isNumeric($('#image').val())){
                                    canAdd = false;
                                }
                            }


                            if (canAdd) {

                                event.preventDefault();
                                sobankGrid.jqGrid('addRow', {
                                    rowID: "new_row",
                                    initData: {},
                                    position: "first",
                                    useDefValues: true,
                                    useFormatter: true,
                                    addRowParams: {}
                                });

                            }
                        });

                        $(document).on('click', '.r-action', function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            if($('#'+lastsel_sb).attr('editable') !== '1') {
                                show_info('', 'Attention, ligne non editable', 'warning');
                                return;
                            }

                            sobankGrid.jqGrid('saveRow', lastsel_sb, {
                                "aftersavefunc": function(rowid, response) {
                                }
                            });
                        });

                    }
                });


            }
        });
    });


    $(document).on('click', '#js_save_sobank', function(event){
        event.preventDefault();
        event.stopPropagation();


        $.ajax({
            url: Routing.generate('banque_sobank_edit'),
            type: 'POST',
            data:   $('#form-sb').serialize(),
            success: function(data){

                show_info('', data.message, data.type);

                if (data.type === 'success') {
                    reloadControleGrid(data.image);
                }

            }
        })

    });

    $( "#mainside, .forme" ).resizable();

    $( "input" ).focus(function() {
        if ($(this).val() == '0.00') {
            $(this).val("");
        }
    });

    $('#minfos').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var infoPerdosContainer = $('#iperdos');

        if($(this).hasClass('blink')){
            $(this).removeClass('blink');
        }

        if (infoPerdosContainer.is(":visible")){
            infoPerdosContainer.hide();
        } else {
            infoPerdosContainer.show();
        }

    });
    $('#mocr').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        url = Routing.generate('banque_ocr_compte');
        var imag = [];
        $('.js_imgbq_selected').each(function() {
            imag.push($(this).attr("id"));
        });
        $.ajax({
            url:url,
            type: "POST",
            dataType: "json",
            data:{
                pdf:$("#pdfc").val(),
                image:$('#image').val(),
                imag:imag
            },
            success: function (data)
            {
                show_info('OCR Compte', 'Récupération compte términée', 'info');
            }
        });
    });
    $('#closeperdos').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $('#iperdos').hide();
    });

    $('.chosenimages').chosen({width: "450px"});
    // $('.chosen-select-banque').chosen({
    //     no_results_text: "Aucun banque trouvé:",
    //     search_contains: true,
    //     width: '100%'
    // });
    $("#dossier").chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('#js_sous_categorie').on('keyup', function () {
        nomb = $('#js_sous_categorie').val();
        $('#js_sous_categorie').val(nomb.toUpperCase());
    });

    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = Routing.generate('banque_dossier');
        var idata = {};
        idata['client'] = $(this).val();
        $.ajax({
            url:url,
            type: "POST",
            dataType: "json",
            data: {
                "idata": JSON.stringify(idata)
            },
            async: true,
            success: function (data)
            {
                $("#dossier option").remove();
                data.dossiers.forEach(function(d) {
                    $("#dossier").append('<option value="'+d.id+'">'+d.nom+'</option>');
                });
                $("#dossier").val('').trigger('chosen:updated');
            }
        });
    });
    // Changement dossier
    $(document).on('change', '#dossier', function () {
        var url = Routing.generate('banque_exercice'),
            dossierid = $(this).val();

        $.ajax({
            url: url,
            data: {dossierid: dossierid},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#exercice').html(data)
            }
        })
    });
    // Changement exercice
    $(document).on('change', '#exercice', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = Routing.generate('banque_date_scan'),
            did = $('#dossier').val(),
            exercice = $('#exercice').val(),
            souscategorieid = $('#souscat').val();
        $.ajax({
            url:url,
            type: "GET",
            dataType: "html",
            data: {
                did: did,
                exercice: exercice,
                souscategorieid: souscategorieid,
                soussouscategorieid: -1
            },
            async: true,
            success: function (data)
            {
                $('#dscan').html(data);
            }
        });
    });

    $(document).on('mouseover', '.js-c-nb-assembler', function () {
        var ids = $(this).closest('tr').find('.js-c-id-assembler').html();
        if(ids !== '&nbsp;'){
            var idarr = ids.split(',');
            if(idarr.length > 0) {
                $.each(idarr, function (index, value) {
                    $('tr[id='+value+']').css("background-color", "#e2efda");
                });
            }
        }
    });

    $(document).on('mouseout', '.js-c-nb-assembler', function () {
        var ids = $(this).closest('tr').find('.js-c-id-assembler').html();
        if(ids !== '&nbsp;'){
            var idarr = ids.split(',');
            if(idarr.length > 0) {
                $.each(idarr, function (index, value) {
                    $('table tr[id='+value+']').css("background-color", "");
                });
            }
        }
    });

    $(document).on('click', '.js-c-nb-assembler', function(){

        var ids = $(this).closest('tr').find('.js-c-id-assembler').html();

        if(ids === '&nbsp;')
            return;

        var imageids = ids.split(',');

        if($('#statustemp').val() !== ''){
            show_info('', 'Dossier non actif', 'warning');
            return;
        }

        swal({
            title: 'Assemblage',
            text: "Voulez vous assembler ces "+imageids.length+" images?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
                var url = Routing.generate('banque_saisie_assemble');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {images: imageids},
                    success: function (data) {

                        show_info('', data.message, data.type);

                        if(data.type === 'success') {

                            reloadControleGrid(data.id);

                            var first = true;
                            $.each(imageids, function(k,v){
                                var span = $('#mySidenav').find('span[id='+v+']'),
                                    tr = span.closest('tr');
                                if(first){
                                    span.attr('id', data.id);
                                    span.html(data.nom)
                                }
                                else{
                                    tr.remove();
                                }
                                first = false;
                            });
                        }
                    }
                });
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );
    });


    //panier
    $('#btn_panier').on('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

        $('#pdf').html('');

        var souscat = $('#souscat').val(),
            etape = $('#etape').val()
        ;

        $.ajax({
            url: Routing.generate('banque_get_panier'),
            type: "GET",
            dataType: "html",
            async: true,
            data: {
                souscat: souscat,
                soussouscat: -1,
                etape: etape
            },
            success: function (data) {

                choisirtraitement();

                var controle = $('#controle-list');
                controle.jqGrid("clearGridData");
                $('#panier-list').html(data);
                updateTooltip();
                $('#mySidenav').html('');
                $('.viewer-container').hide();
                $('#mainside').hide();
                $('#virement').hide();
                $('.forme').hide();
                $('#iperdos').hide();
                $('#myModal').modal('show');
                SetmodalHeight('myModal');
                $('#mfini').show();
                initButtons(status);

                $('#pdf-resize').resizable();
            }
        });

        return false;


    });

    $(document).on('dblclick', '#panier-list .lot', function () {
        //Modifier-na ny eo @ client & dossier
        $('#pdf').html('');
        vider();

        var dossierid = $(this).attr('data-dossier-id'),
            exercice = $(this).attr('data-exercice'),
            iperdos = $('#iperdos')
        ;

        loadInfoPerdos(iperdos, $('#minfos'), dossierid, exercice);

        $.ajax({
            url: Routing.generate('banque_liste_image_panier'),
            type: 'POST',
            data: {
                dossier: dossierid,
                exercice: exercice,
                souscat: 10,
                soussouscat: -1,
                etape: 'OS_1'
            },
            success: function (data) {

                var controle = $('#controle-list'),
                    myModal = $('#myModal'),
                    modalBody = myModal.find('.modal-body');

                controle.jqGrid("clearGridData");

                if (data !== '') {

                    vider();

                    var mySidenav = $('#mySidenav');

                    mySidenav.html(data);
                    $('.viewer-container').hide();
                    $('#mainside').hide();
                    $('#virement').hide();
                    $('.forme').hide();
                    iperdos.hide();
                    myModal.modal('show');

                    SetmodalHeight('myModal');

                    initBanquecompteListe(dossierid);

                    var status = $('#statustemp').val(),
                        title = $('#titletemp').val();

                    if(status !== ''){
                        status = '<span class="label label-danger">'+status+'</span>';
                    }

                    initButtons(status);

                    $('#js_titre').html(title+' '+status);

                    controle.jqGrid('setGridWidth', controle.closest('.ob-container').width());

                    var modalBodyHeight = modalBody.height(),
                        firstImage = $('#allimage tr:first').find('span');

                    mySidenav.height(modalBodyHeight - 5);

                    clickimage();

                    if(firstImage.length > 0) {
                        firstImage.css("background-color", "#f8ac59");

                        var lastsel_piece = firstImage.attr('id'),
                            height = $(window).height() * 0.95;

                        showimagedetails(lastsel_piece, $("#souscat").val(), $('#client').val(), exercice, height, true);
                    }

                } else {
                    show_info('Saisie RB', "Aucun résultat", 'warning');
                }
            }
        });

        return false;
    });

    //liste image a traiter
    $('#btn_go').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $('#pdf').html('');

        var souscat = $('#souscat').val(),
            dossier = $('#dossier').val(),
            dscan = $('#dscan').val(),
            exercice = $('#exercice').val(),
            etape = $('#etape').val()
        ;

        if(dossier=='' || exercice == '' || dscan == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {

            loadInfoPerdos($('#iperdos'), $('#minfos'), dossier, exercice);

            $.ajax({
                url: Routing.generate('banque_liste_image'),
                type: 'POST',
                data: {
                    dossier: dossier,
                    dscan: dscan,
                    souscat: souscat,
                    soussouscat: -1,
                    etape: etape,
                    exercice: exercice
                },
                success: function (data) {

                    vider();

                    var controle = $('#controle-list');
                    controle.jqGrid("clearGridData");

                    if (data !== '') {
                        var mySidenav = $('#mySidenav'),
                            myModal = $('#myModal'),
                            modalBody = myModal.find('.modal-body');

                        mySidenav.html(data);

                        $('.viewer-container').hide();
                        $('#mainside').hide();
                        $('#virement').hide();
                        $('.forme').hide();
                        $('#iperdos').hide();
                        myModal.modal('show');

                        SetmodalHeight('myModal');

                        $('#lesimages').hide();
                        $('#btn_ass').hide();

                        $('#mfini').hide();

                        initBanquecompteListe(dossier);


                        var status = $('#statustemp').val(),
                            title = $('#titletemp').val();

                        if(status !== ''){
                            status = '<span class="label label-danger">'+status+'</span>';
                        }

                        initButtons(status);

                        $('#js_titre').html(title+' '+status);

                        controle.jqGrid('setGridWidth',  controle.closest('.ob-container').width());

                        var modalBodyHeight = modalBody.height(),
                            firstImage = $('#allimage tr:first').find('span');

                        mySidenav.height(modalBodyHeight - 5);

                        $('#pdf-resize').resizable();

                        clickimage();

                        mySidenav.height(modalBodyHeight - 5);

                        if(firstImage.length > 0) {
                            firstImage.css("background-color", "#f8ac59");

                            var lastsel_piece = firstImage.attr('id'),
                                height = $(window).height() * 0.95;

                            showimagedetails(lastsel_piece, $("#souscat").val(), $('#client').val(), exercice, height, true);
                        }
                    } else {
                        show_info('Saisie RB', "Aucun résultat", 'warning');
                    }

                }
            });
        }
        return false;
    });
    /*$('#js_sous_categorie').typeahead({
            minLength: 2,
            source: function (query, process) {
                $.ajax({
                    url: Routing.generate('show_list_data_banque'),
                    type: 'POST',
                    dataType: 'JSON',
                    success: function(data) {
                        var newData = [];
                        $.each(data, function(){
                            newData.push(this);
                            });
                        return process(newData);
                        }
                });
            },
            afterSelect: function(args){
                    $('#js_iban_bq').val('');
                    $('#js_code_bq').val(args.code );
                    $('#js_num_compt_bq').val(args.num);
                    $('#js_key_compt_bq').val(args.cle);
                console.log(args);
            }
    });*/
    $('#js_num_compt_bq').typeahead({
        minLength: 1,
        source: function (query, process) {
            var dossier = $('#dossierpanier').val();
            $.ajax({
                url: Routing.generate('show_list_data_banque'),
                type: 'POST',
                data: {
                    // dossier: $("#dossier").val()
                    dossier: dossier
                },
                success: function (data) {
                    var newData = [];
                    $.each(data, function () {
                        newData.push(this);
                    });
                    return process(newData);
                }
            });
        },
        afterSelect: function (args) {
            $('#js_num_compt_bq').val(args.name);
            completer(args.name);
        }
    });



    $(document).on('change', '#isouscategorie', function () {
        var souscategorie = $(this).val();

        $.ajax({
            url: Routing.generate('banque_soussouscategorie'),
            type: 'GET',
            data: {souscategorie: souscategorie},
            success: function (data) {
                $('#isoussouscategorie').html(data);
            }
        });

    });

    /* Changement banque
    $(document).on('change', '#banques', function (event) {
        event.preventDefault();
        event.stopPropagation();
            $.ajax({
                url: Routing.generate('show_det_banque'),
                type: 'POST',
                dataType: 'JSON',
                data:{id: $('#banques').val()},
                success: function(data) {
                        $('#js_iban_bq').val(data.iban);
                        $('#js_code_bq').val(data.code );
                        $('#js_num_compt_bq').val(data.num);
                        $('#js_key_compt_bq').val(data.cle);
                        $('#banques').trigger("chosen:updated");
                }
            });

        return false;
    });	*/



    $('#js_debut_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#js_fin_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#dateregl').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#dateech').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#datef').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#datevi').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#rdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});




    //submit valider banque
    $('.banque_submit').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        if ($('#isoussouscategorie').val()==2263){
            $('#banquesaisie').val(1);
            show_info('Controle banque', "Banque valider avec succès", 'info');
        } else {
            if($('#js_key_compt_bq').val()==$('#js_key_compt_bq_valid').val() && $('#banques').val().length	>0 && $('#js_key_compt_bq').val().length==2){
                $('#js_num_compt_bq').parent().parent().removeClass('has-error');
                $('#js_key_compt_bq_valid').parent().parent().removeClass("has-error");
                $('#js_iban_bq').parent().parent().removeClass('has-error');
                $('#banques').parent().parent().removeClass('has-error');
                $('#banquesaisie').val(1);
                show_info('Controle banque', "Banque valider avec succès", 'info');
            } else {
                $('#js_key_compt_bq').parent().parent().addClass('has-error');
                $('#js_key_compt_bq_valid').parent().parent().addClass("has-error");
                $('#js_iban_bq').parent().parent().addClass('has-error');
                $('#banques').parent().parent().addClass('has-error');
                return false;
            }
        }
        return false;
    });

    //--submit form


    $(document).on('click', '.js-c-image', function () {
        showimagedetails($(this).closest('tr').attr('id'),$('#isouscategorie').val(), $('#client').val(), $('#exercice').val(), false);
    });

    $(document).on('click', '.js-c-doublon', function(){

        var imageid = $(this).closest('tr').attr('id');

        if($('#statustemp').val() !== ''){
            show_info('', 'Dossier non actif', 'warning');
            return;
        }

        swal({
            title: 'Doublon',
            text: "Voulez vous cosidérer l'image comme doublon?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
                setDoublon(imageid);
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );

    });

    $(document).on('click', '#btn-restore-doublon', function(e){
        e.preventDefault();
        var firstImage = $(this).closest('.ui-jqgrid-view').
        find('tbody').find('tr:nth-child(2)');

        if(firstImage.length > 0){
            var url = Routing.generate('banque_releve_restore_doublon');
            $.ajax({
                url: url,
                type: 'POST',
                data: {imageid: firstImage.attr('id')},
                success: function(data){
                    show_info('',data.message,data.type);
                    if(data.type === 'success')
                        reloadControleGrid(firstImage.attr('id'));
                }
            });
        }
    });

    $(document).on('click', '#btn-restore-assemble', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var dossier = $('#dossier').val(),
            exercice = $('#exercice').val();

        reloadDesassembleGrid(dossier, exercice);

        $('#desassemble-modal').modal('show');
    });


    $(document).on('mouseover', '#desassemble-list tr', function () {

        var desassembleGrid = $('#desassemble-list'),
            ids = getDesassembleIds($(this).attr('id'));

        $.each(ids, function (k,v) {
            desassembleGrid.find('tr[id= '+v+']').css('background-color', '#e2efda');
        });
    });

    $(document).on('mouseout', '#desassemble-list tr', function () {

        var desassembleGrid = $('#desassemble-list'),
            ids = getDesassembleIds($(this).attr('id'));

        $.each(ids, function (k,v) {
            desassembleGrid.find('tr[id= '+v+']').css('background-color', '');
        });
    });

    $(document).on('click', '.d-action', function(event){
        event.preventDefault();
        event.stopPropagation();

        var id = $(this).closest('tr').attr('id'),
            historiqueids = getDesassembleIds(id);

        if($('#statustemp').val() !== ''){
            show_info('', 'Dossier non actif', 'warning');
            return;
        }

        swal({
            title: 'Désassemblage',
            text: "Voulez vous désassembler ces pièces?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {

                var firstImage = $('#allimage tr:first').find('span');

                $.ajax({
                    url: Routing.generate('banque_saisie_assemble_image_restore'),
                    type: 'POST',
                    data: {ids: historiqueids},
                    success: function(data){
                        show_info('', data.message, data.type);
                        if(data.type === 'success'){
                            var dossier = $('#dossier').val(),
                                exercice = $('#exercice').val(),
                                deletedTr = $('#allimage').find('span[id='+data.deleted+']').closest('tr');
                            $.ajax({
                                url: Routing.generate('banque_image'),
                                type: 'GET',
                                data: { ids: data.restored },
                                success: function (data) {
                                    deletedTr.after(data);
                                    deletedTr.remove();
                                    clickimage();
                                    if(firstImage.length > 0) {
                                        firstImage.css("background-color", "#f8ac59");

                                        var lastsel_piece = firstImage.attr('id'),
                                            height = $(window).height() * 0.95;

                                        showimagedetails(lastsel_piece, $("#souscat").val(), $('#client').val(), exercice, height, true);
                                    }
                                }
                            });
                            reloadDesassembleGrid(dossier, exercice);
                        }
                    }
                });
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );



    });

    $(document).on('click', '.js-c-trou', function(e){

        e.preventDefault();
        e.stopPropagation();

        if($('#statustemp').val() !== ''){
            show_info('', 'Dossier non actif', 'warning');
            return;
        }

        swal({
            title: 'Relevé Manquant',
            text: "Voulez vous cosidérer qu'il y a un relevé manquant?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );

    });

});


$('#js_num_compt_bq').on('blur', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var compte = $(this).val().replace(/\s/g, '');
    compte = compte.replace('.', '');
    compte = compte.replace('.', '');
    compte = compte.replace(')', '');
    compte = compte.replace('(', '');
    if(compte.length>1){
        $(this).val(compte);
        completer(compte);
    } else {
        $('#js_iban_bq').val('');
        $('#js_code_bq').val('');
        $('#js_key_compt_bq_valid').val('');
        $('#js_key_compt_bq').val('');
    }
});



function completer(num){
    //--auto generate key from num compte
    var five_num = num.replace(/\s/g, '').length,
        code_bq,
        num_compte,
        numc,
        code_guichet,
        val_num_compt,
        cle_rib,
        banques = $('#banques')
    ;
    if (five_num===11 || five_num===8 || five_num===9){
        $.ajax({
            url: Routing.generate('show_nom_banque'),
            type: 'POST',
            dataType: 'JSON',
            data:{num: num},
            success: function(data) {
                if (data.compte.length>0){
                    $('#js_num_compt_bq').val(data.compte);
                }
                code_bq = data.compte.substring(0,5);
                $('input#js_code_bq').val(code_bq);
                $('#js_key_compt_bq').val(data.cle);
                banques.val(data.id);
                banques.trigger("chosen:updated");
                ibanCalculerCle();
            }
        });
        return false;
    }
    if( five_num >= 5) {
        code_bq = num;
        numc =num;
        numc = numc.toUpperCase();
        $('#js_num_compt_bq').val(numc);
        code_bq = code_bq.substring(0,5);
        $('input#js_code_bq').val(code_bq);
        if (five_num == 21) {
            val_num_compt = FormaterRibNir(numc);
            val_num_compti = numc;
            code_guichet = val_num_compt.substring(5,10);
            num_compte = val_num_compt.substring(10);
            cle_rib = 97 - ((89*code_bq+15*code_guichet+3*num_compte) % 97 );
            if(cle_rib<10){
                cle_rib = "0"+cle_rib;
            }
            $('input#js_key_compt_bq').val(cle_rib);
            $.ajax({
                url: Routing.generate('show_nom_banque'),
                type: 'POST',
                dataType: 'JSON',
                data:{num: val_num_compti+cle_rib},
                success: function(data) {
                    banques.val(data.id);
                    banques.trigger("chosen:updated");
                    ibanCalculerCle();
                }
            });
        }else{
            $('input#js_key_compt_bq').val('');
            $('input#js_code_bq').val('');
        }
    }
    if ($('#isoussouscategorie').val()==2263){
        $('#js_iban_bq').val('');
        banques.val(2093);
        banques.trigger("chosen:updated");
    }
}

//saisie terminee image suivante
function suivant(data, save) {
    validerImage(data);

    var imagesuiv = $('#imagesuiv'),
        suiv = imagesuiv.val();

    if (save === true) {
        saveSaisie(false);
    }

    if (!(suiv.length > 0)) {
        var reste = true;
        $('.js_imgbq_selected').each(function () {
            if ($(this).attr("data-id") == 0) {
                $(this).css("background-color", "#f8ac59");
                suiv = $(this).attr('id');
                reste = false;
                return false;
            }
        });
        if (reste) {
            $('#informations').hide();
            show_info('Merci', "Saisies terminées", 'success');
            suiv = image;
            vider();
            $('#mainside').hide();
            $('.forme').hide();
            return false;
        }
    }
    var suivo = $('#' + suiv).closest('tr').next().find('span').attr('id');

    $('#imagesuiv').val(suivo);
    $('#' + suiv).css("background-color", "#f8ac59");
    $.ajax({
        data: {
            imgid: suiv
        },
        url: Routing.generate('data_banque_saisie'),
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            vider();
            $("#nbpage").html(data.nbpage);
            if (data.nbpage > 1) {
                $("#btn_dass").show();
            } else {
                $("#btn_dass").hide();
            }
            $("#information").show();
            $("#generale").html(data.generale);
            $("#mandataire").html(data.mandataire);
            $("#comptable").html(data.comptable);
            $("#fiscale").html(data.fiscale);
            $("#isaisie").html(data.isaisie);
            $("#idossier").html(data.idossier);
            $("#dossier").val(data.dossier);
            $("#image").val(suiv);
            $("#icategorie").val(data.infocat.c);
            $("#isouscategorie").val(data.infocat.sc);
            $("#isoussouscategorie").val(data.infocat.ssc);

            var banquecomptes = $('#banquecomptes'),
                ibans = $('#ibans');

            banquecomptes.val(data.banque_compte_id);
            ibans.val(data.banque_compte_id);
            $('#banques').val(data.banque_id);

            PDFObject.embed(data.pdf, "#pdf");
            $("#pdfc").val(data.pdf);
            $('.forme').show();
            $('#mainside').show();
            $('#js_num_releve_bq').val(data.num_releve);
            $('#js_debut_bq_date').val(data.debut_periode);
            $('#js_debut_bq_debi').val(mi(data.ddebit));
            $('#js_debut_bq_cred').val(mi(data.dcredit));
            $('#js_debut_page').val(data.page_solde_debut);
            $('#js_fin_bq_date').val(data.fin_periode);
            $('#js_fin_bq_debi').val(mi(data.fdebit));
            $('#js_fin_bq_cred').val(mi(data.fcredit));
            $('#js_fin_page').val(data.page_solde_fin);
            //remise banque
            $('#nombrecheque').val(data.nombre_cheque);
            $('#totalecheque').val(mi(data.total_cheque));
            //lcr
            $('#dateregl').val(data.date_reglement);
            $('#dateech').val(data.date_echeance);
            $('#nombreligne').val(data.nombreligne);
            $('#totallcr').val(mi(data.totallcr));
            $('#relevelcr').val(mi(data.num_releve));
            //fraisban
            $('#totalfrais').val(mi(data.totallcr));
            $('#datef').val(data.date_facture);
            $('#numf').val(data.num_facture);


            //Recharger doublon Grid
            reloadControleGrid($('#image').val());


            return false;
        }
    });
}


//image choisie
function clickimage() {
    choisirtraitement();
    $('.js_imgbq_selected').on('click', function () {
        $('#informations').show();
        var lastsel_piece = $(this).closest('span').attr('id'),
            lastsel_cli = $(this).closest('span').attr('client'),
            lastsel_exerc = $(this).closest('span').attr('exercice');
        $('.js_imgbq_selected').each(function () {
            $(this).css("background-color", "transparent");
            if ($(this).attr("data-id") == 1) {
                $(this).css("background-color", "#FFD966");
            }
        });
        $(this).closest('span').css("background-color", "#f8ac59");
        $('#imagesuiv').val($(this).closest('tr').next().find('span').attr('id'));

        var height = $(window).height() * 0.95;

        showimagedetails(lastsel_piece, $("#souscat").val(), lastsel_cli, lastsel_exerc, height, true);
    });
}

function setDoublon(imageid){
    var url = Routing.generate('banque_releve_set_doublon');

    $.ajax({
        url: url,
        type: 'POST',
        data: {imageid: imageid},
        success: function (data) {
            show_info('', data.message, data.type);
            var first = $('tr[id='+imageid+']').closest('tbody').find('tr:nth-child(2)');

            reloadControleGrid(first.attr('id'));
        }
    });
}

function reloadControleGrid(imageid){
    var url = Routing.generate('banque_releve_controle', {image: imageid});
    controleGrid.jqGrid('setGridParam', {
            url: url,
            datatype: 'json'
        }
    )
        .trigger('reloadGrid', {fromServer: true, page: 1});
}


function reloadDesassembleGrid(dossierid, exercice){
    var url = Routing.generate('banque_saisie_assemble_restore', {dossierid: dossierid, exercice: exercice});
    desassembleGrid.jqGrid('setGridParam', {
            url: url,
            datatype: 'json'
        }
    )
        .trigger('reloadGrid', {fromServer: true, page: 1});
}

function showimagedetails(imageid,souscategorie,clientid,exercice,height, reloadDoublon){
    $.ajax({
        data: {
            imgid: imageid,
            souscat: souscategorie,
            clientId: clientid,
            exercice: exercice,
            height: height
        },
        url: Routing.generate('data_banque_saisie'),
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            vider();

            $("#information").show();
            $("#generale").html(data.generale);
            $("#mandataire").html(data.mandataire);
            $("#comptable").html(data.comptable);
            $("#fiscale").html(data.fiscale);
            $("#isaisie").html(data.isaisie);
            $("#idossier").html(data.idossier);
            $("#dossier").val(data.dossier);
            $("#dossierpanier").val(data.dossier);
            $("#image").val(imageid);
            $("#icategorie").val(data.infocat.c);
            $("#isouscategorie").val(data.infocat.sc);
            $("#isoussouscategorie").val(data.infocat.ssc);


            var banquecomptes = $('#banquecomptes'),
                ibans = $('#ibans');

            banquecomptes.val(data.banque_compte_id);
            ibans.val(data.banque_compte_id);
            $('#banques').val(data.banque_id);


            PDFObject.embed(data.pdf, "#pdf");
            $("#pdfc").val(data.pdf);

            var modalBody = $('#myModal').find('.modal-body'),
                modalBodyHeight = modalBody.height();
            $('#pdf').height(modalBodyHeight - 5);

            $('.forme').show();
            $('#mainside').show();
            $('#js_num_releve_bq').val(data.num_releve);
            $('#js_debut_bq_date').val(data.debut_periode);
            $('#js_debut_bq_debi').val(mi(data.ddebit));
            $('#js_debut_bq_cred').val(mi(data.dcredit));
            $('#js_debut_page').val(data.page_solde_debut);
            $('#js_fin_bq_date').val(data.fin_periode);
            $('#js_fin_bq_debi').val(mi(data.fdebit));
            $('#js_fin_bq_cred').val(mi(data.fcredit));
            $('#js_fin_page').val(data.page_solde_fin);
            //remise banque
            $('#nombrecheque').val(data.nombre_cheque);
            $('#totalecheque').val(mi(data.total_cheque));
            //lcr
            $('#dateregl').val(data.date_reglement);
            $('#dateech').val(data.date_echeance);
            $('#nombreligne').val(data.nombreligne);
            $('#totallcr').val(mi(data.totallcr));
            $('#relevelcr').val(data.num_releve);
            //fraisban
            $('#totalfrais').val(mi(data.totallcr));
            $('#datef').val(data.date_facture);
            $('#numf').val(data.num_facture);


            popo();


            if(reloadDoublon) {
                reloadControleGrid($('#image').val());
            }

            return false;
        }
    });
}

function popo(){
    $('[data-toggle="popover"]').popover({html : true}).on('shown.bs.popover', function() {
        $(".checkpcg input[type=radio]").on('click',function () {
            $("#pcc").jstree();
            $.ajax({
                url: Routing.generate('banque_get_pcc'),
                type: 'POST',
                data: {
                    dossier:$('#dossier').val(),
                    pcg : $(this).attr("id").substr(1,20),
                },
                success: function (data) {
                    $("#pcc").jstree("destroy");
                    $("#pcc").jstree({
                        'core' : { 'data' : data } ,
                        'multiple':false,
                        'checkbox' : {
                            'deselect_all': true,
                            'three_state' : false,
                        },
                        'plugins' : [ 'checkbox','search' ],
                        'search': {
                            'case_sensitive': false,
                            'show_only_matches': true
                        }
                    }).on('ready.jstree', function(){
                        $('.search-input').on('keyup', function(){
                            var searchString = $(this).val();
                            $('#pcc').jstree('search', searchString);
                        });
                    });
                }
            });
        });
        $('#imputation_submit').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var compte = $("#pcc").jstree("get_selected");
            var id =$(this).attr("data-id");
            url = Routing.generate('banque_set_compte');
            $.ajax({
                url:url,
                type: "POST",
                dataType: "json",
                data: {
                    "compte": compte
                },
                async: true,
                success: function (data)
                {
                    $('#cp'+id).html(data);
                }
            });
        });

    });
    $('body').on('click', function (e) {
        $('[data-toggle="popover"]').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });
}

function vider() {
    $('#js_iban_bq').val('');
    $('#js_code_bq').val('');
    $('#js_sous_categorie').val('');
    $('#js_num_compt_bq').val('');
    $('#js_key_compt_bq_valid').val('');
    $('#js_key_compt_bq').val('');
    $('#js_num_releve_bq').val('');
    $('#js_debut_bq_date').val('');
    $('#js_debut_bq_debi').val(0);
    $('#js_debut_bq_cred').val(0);
    $('#js_debut_page').val(1);
    $('#js_fin_bq_date').val('');
    $('#js_fin_bq_debi').val(0);
    $('#js_fin_bq_cred').val(0);
    $('#js_fin_page').val(1);
    $('#banques').val('');
    $('#banquesaisie').val(0);
    $('#banques').trigger("chosen:updated");
    $('#totalecheque').val(0);
    $('#nombrecheque').val(0);
    $('#dateregl').val('');
    $('#dateech').val('');
    $('#relevelcr').val('');
    $('#nombreligne').val(0);
    $('#totallcr').val(0);
    $('#totalfrais').val(0);


    initializeCommun();
}

function FormaterRibNir(texte) {
    return strtr(texte.toString(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","12345678912345678923456789");
}
function strtr (str, from, to) {
    var fr = '', i = 0, j = 0, lenStr = 0, lenFrom = 0;
    var tmpFrom = [];
    var tmpTo   = [];
    var ret = '';
    var match = false;
    // Received replace_pairs?
    // Convert to normal from->to chars
    if (typeof from === 'object') {
        for (fr in from) {
            tmpFrom.push(fr);
            tmpTo.push(from[fr]);
        }
        from = tmpFrom;
        to = tmpTo;
    }
    // Walk through subject and replace chars when needed
    lenStr  = str.length;
    lenFrom = from.length;
    for (i = 0; i < lenStr; i++) {
        match = false;
        for (j = 0; j < lenFrom; j++) {
            if (str.substr(i, from[j].length) == from[j]) {
                match = true;
                // Fast forward
                i = (i + from[j].length)-1;
                break;
            }
        }
        if (false !== match) {
            ret += to[j];
        } else {
            ret += str[i];
        }
    }
    return ret;
}
function ibanCalculerCle() {

    var pays="FR";
    var bban=ibanFormater(fgetElementById("js_num_compt_bq").value +fgetElementById("js_key_compt_bq").value);

    var numero=ibanConvertirLettres(bban.toString()+pays.toString())+"00";
    var calculCle=0;
    var pos=0;
    while (pos<numero.length) {
        calculCle=parseInt(calculCle.toString()+numero.substr(pos,9),10) % 97;
        pos+=9;
    }
    calculCle=98-(calculCle % 97);
    var cle=(calculCle<10 ? "0" : "")+calculCle.toString();
    fgetElementById("js_iban_bq").value=pays+cle+bban;
}
function ibanFormater(texte) {
    var texte=(texte==null ? "" : texte.toString().toUpperCase());
    return texte;
}
function ibanConvertirLettres(texte) {
    texteConverti="";

    for (i=0;i<texte.length;i++) {
        caractere=texte.charAt(i);
        if (caractere>"9") {
            if (caractere>="A" && caractere<="Z") {
                texteConverti+=(caractere.charCodeAt(0)-55).toString();
            }
        }else if (caractere>="0"){
            texteConverti+=caractere;
        }
    }
    return texteConverti;
}
function fgetElementById(ichElement_V) {

    if (document.getElementById) return document.getElementById(ichElement_V);
    if (document.all) return document.all[ichElement_V];
    return null;
}
function mi(nbr){
    nbr=(nbr==null ? "" : nbr.toString());
    if (nbr.length==0){
        nbr='0';
    }
    nbr = nbr.replace(/\s/g, '');
    nbr = nbr.replace(",", ".");
    nbr = parseFloat(nbr);
    return nbr.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
}


function updateTooltip() {
    makeTooltipLot('#panier-list');
    $('*').qtip('hide');
}
function makeTooltipLot(container) {
    var tooltip_parent = $(document);
    if (typeof container !== 'undefined') {
        tooltip_parent = $(document).find(container);
    }

    var position = { my: 'top center', at: 'bottom center' };

    tooltip_parent.find('.lot[data-image]').qtip({
        content: {
            text: function (event, api) {
                var client = $(this).attr('data-client'),
                    site = $(this).attr('data-site'),
                    dossier = $(this).attr('data-dossier'),
                    exercice = $(this).attr('data-exercice'),
                    datescan = moment($(this).attr('data-datescan')).format('DD/MM/Y'),
                    tache = $(this).attr('data-tache');


                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Exercice</th><td class="col-sm-9">' + exercice + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Date de scan</th><td class="col-sm-9" >' + datescan + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Tâche</th><td class="col-sm-9" >' + tache + '</td></tr>';
                modalbody += '</table>';

                return modalbody;
            }
        },
        position: position,
        style: {
            classes: 'qtip-dark qtip-shadow'
        },
        show: 'click'
    });
}


function getDesassembleIds(id){
    var desassembleGrid = $('#desassemble-list'),
        ids = [],
        gridRow = desassembleGrid.jqGrid('getRowData', id),
        allgridRows = desassembleGrid.jqGrid('getGridParam','data'),
        imageAssembleeId = gridRow['d_image_id'];

    $.each(allgridRows ,function(k, v){
        if(parseInt(v['d_image_id']) === parseInt(imageAssembleeId)){
            ids.push(v['_id_']);
        }
    });

    return ids;

}


