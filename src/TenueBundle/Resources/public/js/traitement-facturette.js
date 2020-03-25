$(function () {

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader'),
        fg_height;

    $(document).bind("ajaxSend", function(){
        loader_selector.show();
    }).bind("ajaxComplete", function(){
        loader_selector.hide();
    });

    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        siteParClientMulti(client_selector, site_selector, loader_selector, function() {
            dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, function() {
                loader_selector.hide();
            })
        });
    });
    
    $(document).on('click', '#btn-traitement-facturette', function () {
       var dossierid = $('#dossier').val(),
           exercice = $('#exercice').val(),
           url = Routing.generate('tenue_traitement_facturette_content');

       if(dossierid === '' || exercice === ''){
           show_info('Attention', 'Dossier et Exercice obligatoire', 'warning');
           return;
       }

       $.ajax({
           url: url,
           type: 'POST',
           dataType: 'html',
           data:{
               dossierid: dossierid,
               exercice: exercice
           },
           success: function (data) {
               $('#facturette-content').html(data);
               ready_inspinia();
               $('.input-daterange').datepicker({
                   keyboardNavigation: false,
                   forceParse: false,
                   autoclose: true,
                   language: 'fr'
               });

               fg_height = $('#page-wrapper').height() -
                   $('#page-wrapper .page-heading').height() -
                   ($('#facturette-content > div:nth-child(1)').height() * 3) -200;

               var facturetteGrid = $('#table-facuturette-list'),
                   rapprochementGrid = $('#table-rapprochement-list');

               facturetteGrid.jqGrid({
                   datatype: 'json',
                   loadonce: true,
                   sortable: true,
                   shrinkToFit: true,
                   viewrecords: true,
                   height:'auto',
                   width: facturetteGrid.closest('.ibox-content').width(),
                   caption: 'Facturettes',
                   colNames: [
                       'Date', 'Image', 'RS', 'Nature','Sous Nature', 'Sous Nature-id', 'Sous catégorie','Distance', 'Nb Couverts',
                       'Indication', 'Rappro', 'Catégorie', 'Categorie-id', 'TTC', 'TVA', 'HT', 'Resultat', 'Resulat-id',
                       'TVA', 'TVA-id','Td Ndf Sousnature', 'Bilan', 'Bilan-id', 'Is Tiers','Bilan TypeCompte', 'Image Flague', 'Valider'
                   ],
                   colModel: [
                       {
                           name: 'fg-date',
                           index: 'fg-date',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-date',
                           formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}
                       },
                       {
                           name: 'fg-image',
                           index: 'fg-image',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-image'
                       },
                       {
                           name: 'fg-rs',
                           index: 'fg-rs',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-rs'
                       },
                       {
                           name: 'fg-nat',
                           index: 'fg-nat',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-nat'
                       },
                       {
                           name: 'fg-snat',
                           index: 'fg-snat',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-snat'
                       },
                       {
                           name: 'fg-snat-id',
                           index: 'fg-snat-d',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-snat-id'
                       },
                       {
                           name: 'fg-scat',
                           index: 'fg-scat',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-scat'
                       },
                       {
                           name: 'fg-distance',
                           index: 'fg-distance',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-distance'
                       },
                       {
                           name: 'fg-nbcouvert',
                           index: 'fg-nbcouvert',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-nbcouvert'
                       },
                       {
                           name: 'fg-indication',
                           index: 'fg-indication',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-indication'
                       },
                       {
                           name: 'fg-rappro',
                           index: 'fg-rappro',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-rappro'
                       },
                       {
                           name: 'fg-categorie',
                           index: 'fg-categorie',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-categorie'
                       },
                       {
                           name: 'fg-categorie-id',
                           index: 'fg-categorie-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-categorie-id'
                       },
                       {
                           name: 'fg-ttc',
                           index: 'fg-ttc',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-ttc'
                       },
                       {
                           name: 'fg-tva',
                           index: 'fg-tva',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-tva'
                       },
                       {
                           name: 'fg-ht',
                           index: 'fg-ht',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-ht'
                       },
                       {
                           name: 'fg-pccresultat',
                           index: 'fg-pccresultat',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-pccresultat'
                       },
                       {
                           name: 'fg-pccresultat-id',
                           index: 'fg-pccresultat-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-pccresultat-id'
                       },
                       {
                           name: 'fg-pcctva',
                           index: 'fg-pcctva',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-pcctva'
                       },
                       {
                           name: 'fg-pcctva-id',
                           index: 'fg-pcctva-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-pcctva-id'
                       },
                       {
                           name: 'fg-tdndfsousnature-id',
                           index: 'fg-tdndfsousnature-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-tdndfsousnature-id'
                       },
                       {
                           name: 'fg-pccbilan',
                           index: 'fg-pccbilan',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-fg-pccbilan'
                       },
                       {
                           name: 'fg-pccbilan-id',
                           index: 'fg-pccbilan-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-pccbilan-id'
                       },
                       {
                           name: 'fg-istiers',
                           index: 'fg-istiers',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-istiers'
                       },
                       {
                           name: 'fg-pccbilan-typecompte',
                           index: 'fg-pccbilan-typecompte',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-pccbilan-typecompte'
                       },
                       {
                           name: 'fg-image-flague-id',
                           index: 'fg-image-flague-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-fg-image-flague-id'
                       },
                       {
                           name: 'fg-validation',
                           index: 'fg-validation',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           width: 100,
                           fixed: true,
                           classes: 'js-fg-validation',
                           formatter: "checkbox", formatoptions: { disabled: false},
                           edittype: "checkbox", editoptions: {value: "Yes:No", defaultValue: "Yes"}
                       }
                   ]
               }).contextMenu({
                   selector: '.jqgrow',
                   build: function ($trigger, e) {
                       var target = e.target,
                           tr = $(target).closest('tr.jqgrow');

                       if($(target).hasClass('js-fg-pccresultat') ||
                           $(target).hasClass('js-fg-pcctva') ||
                           $(target).hasClass('js-fg-pccbilan')){
                           var gridClass = '';

                           if($(target).hasClass('js-fg-pccresultat')){
                               gridClass = 'fg-pccresultat';
                           }
                           else if($(target).hasClass('js-fg-pcctva')){
                               gridClass = 'fg-pcctva';
                           }
                           else if($(target).hasClass('js-fg-pccbilan')){
                               gridClass = 'fg-pccbilan';
                           }

                           return {
                               callback: function (key) {
                                   var id = tr.attr('id'),
                                       rowData = facturetteGrid.jqGrid('getRowData', id),
                                       allRows = facturetteGrid.jqGrid('getGridParam','data'),
                                       lien = '',
                                       panelHead = $('#panel-plan-comptable > div.panel-heading'),
                                       tdNdfSousnatureId = rowData['fg-tdndfsousnature-id'],
                                       pccBilanTypeCompte = rowData['fg-pccbilan-typecompte'],
                                       pcgTree = $('#tree-pcg'),
                                       pccTree = $('#tree-pcc'),
                                       dossierid = $('#dossier').val(),
                                       modalPlanComptable = $('#modal-plan-comptable');

                                   if(key === 'delete'){
                                       rowData[gridClass] = '';
                                       rowData[gridClass+'-id'] = '';
                                       facturetteGrid.jqGrid('setRowData', id, rowData);

                                       //Modifier-na ny lignes rehetra
                                       $.each(allRows, function (key, value) {
                                           var otherId  = allRows[key]['_id_'];
                                           if(otherId !== id){
                                               if(gridClass === 'fg-pccbilan'){
                                                   if(value['fg-pccbilan-typecompte'].toString() === pccBilanTypeCompte){
                                                       value[gridClass] = '';
                                                       value[gridClass + '-id'] = '';
                                                       facturetteGrid.jqGrid('setRowData', otherId, value);
                                                   }
                                               }
                                               else{
                                                   if (value['fg-tdndfsousnature-id'].toString() === tdNdfSousnatureId) {
                                                       value[gridClass] = '';
                                                       value[gridClass + '-id'] = '';
                                                       facturetteGrid.jqGrid('setRowData', otherId, value);
                                                   }
                                               }
                                           }
                                       });
                                   }
                                   else if(key === 'add'){

                                       if(gridClass ==='fg-pccresultat') {
                                           lien = Routing.generate('tenu_rapprochement_pcg', {typecompte: 'resultat'});
                                           panelHead.html('Plan Comptable (Resultat)');
                                       }
                                       else if(gridClass === 'fg-pcctva'){
                                           lien = Routing.generate('tenu_rapprochement_pcg', {typecompte: 'tva'});
                                           panelHead.html('Plan Comptable (TVA)');
                                       }
                                       else if(gridClass === 'fg-pccbilan'){
                                           lien = Routing.generate('tenu_rapprochement_pcg', {typecompte: 'bilan'});
                                           panelHead.html('Plan Comptable (Bilan)');
                                       }

                                       $.ajax({
                                           url: lien,
                                           data: {
                                               dossierId: dossierid,
                                               tdndfsousnatureid: tdNdfSousnatureId,
                                               pccbilantypecompte: pccBilanTypeCompte
                                           },
                                           type: 'POST',
                                           dataType: 'json',
                                           success: function (data) {

                                               pcgTree.jstree('destroy');
                                               pccTree.jstree('destroy');

                                               if(data.length === 0){
                                                   show_info('Attention', 'Il n\'y a pas encore de table de décision pour cette sousnature', 'warning');
                                                   return;
                                               }

                                               pcgTree.jstree({
                                                   'plugins' : ['wholerow'],
                                                   'core' : {
                                                       'data':  data
                                                   }
                                               }) .bind('click.jstree', function () {
                                                   var CurrentNode = $(this).jstree('get_selected'),
                                                       pcgid = $('#' + CurrentNode).attr('id');

                                                   lien = Routing.generate('tenu_rapprochement_pcc', {dossierid: dossierid, pcgid: pcgid});
                                                   $.ajax({
                                                       url: lien,
                                                       type: 'GET',
                                                       dateType: 'json',
                                                       success: function (data) {

                                                           pccTree.jstree('destroy');
                                                           pccTree.jstree({
                                                               'plugins' : ['wholerow'],
                                                               'core' : {
                                                                   'data':  data
                                                               }
                                                           }).bind('click.jstree', function () {
                                                               var CurrentNode = $(this).jstree('get_selected'),
                                                                   current = $('#' + CurrentNode),
                                                                   pccid = current.attr('id'),
                                                                   pccCompteLibelle = $(this).jstree('get_selected', true)[0].text;

                                                               rowData[gridClass] = pccCompteLibelle.split('--', 1);
                                                               rowData[gridClass+'-id'] = pccid;
                                                               facturetteGrid.jqGrid('setRowData', id, rowData);

                                                               //Modifier-na ny lignes rehetra
                                                               $.each(allRows, function (key, value) {
                                                                   var otherId  = allRows[key]['_id_'];
                                                                   if(otherId !== id){
                                                                       if(gridClass === 'fg-pccbilan'){
                                                                           if(value['fg-pccbilan-typecompte'].toString() === pccBilanTypeCompte){
                                                                               value[gridClass] = pccCompteLibelle.split('--', 1);
                                                                               value[gridClass + '-id'] = pccid;
                                                                               facturetteGrid.jqGrid('setRowData', otherId, value);
                                                                           }
                                                                       }
                                                                       else{
                                                                           if (value['fg-tdndfsousnature-id'].toString() === tdNdfSousnatureId) {
                                                                               value[gridClass] = pccCompteLibelle.split('--', 1);
                                                                               value[gridClass + '-id'] = pccid;
                                                                               facturetteGrid.jqGrid('setRowData', otherId, value);
                                                                           }
                                                                       }
                                                                   }
                                                               });

                                                               modalPlanComptable.modal('hide');
                                                           });
                                                       }
                                                   })
                                               });

                                               modalPlanComptable.modal('show');
                                               $('.modal-dialog').draggable();
                                           }
                                       });
                                   }
                               },
                               items: {
                                   add: {name: 'Modifier Compte', icon: 'edit'}, delete: {name: 'Supprimer Compte', icon: 'delete'}
                               }
                           };
                       }
                       else{
                           return {
                               callback: function (key, options) {},
                               items: {
                                   na: {name: 'Aucune action', icon: ''}
                               }
                           };
                       }
                   }
               });

               rapprochementGrid.jqGrid({
                   datatype: 'json',
                   loadonce: true,
                   sortable: true,
                   shrinkToFit: true,
                   viewrecords: true,
                   height:'auto',
                   width: rapprochementGrid.parent().width(),
                   caption: 'Rapprochement avec les paiements',
                   colNames: [
                       'Document', 'Document type', 'Date', 'Image', 'Image-id', 'Libelle', 'Montant', 'Valider'
                   ],
                   colModel: [
                       {
                           name: 'rg-document',
                           index: 'rg-document',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-document'
                       },
                       {
                           name: 'rg-document-type',
                           index: 'rg-document-type',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-document-type'
                       },
                       {
                           name: 'rg-date',
                           index: 'rg-date',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-date',
                           formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}
                       },
                       {
                           name: 'rg-image',
                           index: 'rg-image',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-image'
                       },
                       {
                           name: 'rg-image-id',
                           index: 'rg-image-id',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           hidden: true,
                           classes: 'js-rg-image-id'
                       },
                       {
                           name: 'rg-libelle',
                           index: 'rg-libelle',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-libelle'
                       },
                       {
                           name: 'rg-ttc',
                           index: 'rg-ttc',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           classes: 'js-rg-ttc'
                       },
                      {
                           name: 'rg-action',
                           index: 'rg-action',
                           align: 'center',
                           editable: false,
                           sortable: true,
                           width: 100,
                           fixed: true,
                           classes: 'js-rg-action',
                           editoptions: {defaultValue: '<i class="fa fa-check icon-action rg-action" title="Valider"></i>'}
                       }
                   ]
               });

           }
       })
    });
    
    $(document).on('click', '#js-refresh-facturette-grid', function () {
        var facturetteGrid = $('#table-facuturette-list'),
            datepicker = $('#datepicker'),
            periodeDu = datepicker.find('input[name="start"]').val(),
            periodeAu = datepicker.find('input[name="end"]').val(),
            status = $('#js-status-facturette-grid').val(),
            dossierid = $('#dossier').val(),
            exercice = $('#exercice').val(),
            urlFacturetteGrid = Routing.generate('tenue_traitement_facturette_list'),
            editUrlFacturetteGrid = Routing.generate('tenue_traitement_facturette_edit');

        facturetteGrid.jqGrid('setGridParam', {
            url: urlFacturetteGrid,
            editurl: editUrlFacturetteGrid,
            postData: {
                dossierid: dossierid,
                exercice: exercice,
                periodedu: periodeDu,
                periodeau: periodeAu,
                status: status
            },
            mtype: 'POST',
            datatype: 'json'

        })
            .trigger('reloadGrid', {fromServer: true, page: 1});

        facturetteGrid.jqGrid('setGridHeight', fg_height);

    });

    $(document).on('click', '.js-fg-indication', function(){
        var imageid = $(this).closest('tr').attr('id'),
            urlindication = Routing.generate('tenue_rapprochement_indication'),
            modalIndication = $('#modal-indication');

        $.ajax({
            url: urlindication,
            type: 'POST',
            data: {
                imageid: imageid
            },
            datatype: 'html',
            success: function(data){
                modalIndication.find('.form-group').html(data);

                modalIndication.modal('show');
                $('.modal-dialog').draggable();

            }
        })
    });

    $(document).on('click', '.js-fg-rappro', function () {
        var facturetteGrid = $('#table-facuturette-list'),
            rapprochementGrid = $('#table-rapprochement-list'),
            imageid = $(this).closest('tr').attr('id'),
            urlpcc = Routing.generate('tenue_rapprochement_facturette_list'),
            rowData = facturetteGrid.jqGrid('getRowData', imageid),
            imageflagueid = rowData['fg-image-flague-id'],
            modalRapprochement = $('#modal-rapprochement');

        $('#rapprochement-image-parent').val(imageid);

        rapprochementGrid.jqGrid('setGridParam', {
            url: urlpcc,
            editurl: urlpcc,
            postData: {
                imageid: imageid,
                imageflagueid: imageflagueid
            },
            mtype: 'POST',
            datatype: 'json'

        })
            .trigger('reloadGrid', {fromServer: true, page: 1});

        modalRapprochement.modal('show');

        rapprochementGrid.jqGrid('setGridWidth',  modalRapprochement.find('.modal-content').width() - 80);
        $('.modal-dialog').draggable();
    });

    $(document).on('click', '.js-fg-validation', function () {

        var facturetteGrid = $('#table-facuturette-list'),
            tr = $(this).closest('tr'),
            id = tr.attr('id'),
            rowData = facturetteGrid.jqGrid('getRowData', id),
            pccresultatid = rowData['fg-pccresultat-id'],
            ttc = rowData['fg-ttc'],
            pccbilanid = rowData['fg-pccbilan-id'],
            ht = rowData['fg-ht'],
            pcctvaid = rowData['fg-pcctva-id'],
            tva = rowData['fg-tva'],
            tdndfsousnatureid = rowData['fg-tdndfsousnature-id'],
            pccbilantypecompte = rowData['fg-pccbilan-typecompte'],
            istiers = rowData['fg-istiers'],
            categorieid = rowData['fg-categorie-id'],
            dossierid = $('#dossier').val(),
            url = Routing.generate('tenue_traitement_facturette_edit'),
            status = $('#js-status-facturette-grid').val(),
            exercice = $('#exerice').val();



        if(rowData['fg-validation'] === "No"){
            rowData['fg-validation'] = "Yes";
            facturetteGrid.jqGrid('setRowData', id, rowData);
        }

        $.ajax({
            url: url,
            type: 'POST',
            data:{
                id: id,
                ttc: ttc,
                ht: ht,
                tva: tva,
                pccresultatid: pccresultatid,
                pccbilanid: pccbilanid,
                pcctvaid: pcctvaid,
                tdndfsousnatureid: tdndfsousnatureid,
                pccbilantypecompte: pccbilantypecompte,
                istiers: istiers,
                categorieid: categorieid,
                dossierid: dossierid
            },
            success: function (data) {
                show_info('', data['message'], data['type']);

                if(data['type'] === 'success'){
                    if(parseInt(status ) === 0){
                        facturetteGrid.jqGrid('delRowData',id);
                    }

                    //Atao mise à jour ny situations
                    $.ajax({
                        url: Routing.generate('tenue_rapprochement_situation'),
                        type: 'GET',
                        dataType: 'html',
                        data: {
                            dossierid:dossierid,
                            exercice:exercice
                        },
                        success: function (data) {
                            $('#panel-situation').html(data);
                        }
                    })
                }
            }
        })

    });

    $(document).on('click', '.js-rg-action', function(){

        if($(this).find('i').length === 0){
            return;
        }

        var facturetteGrid = $('#table-facuturette-list'),
            rapprochementGrid = $('#table-rapprochement-list'),
            imageaflaguer = $('#rapprochement-image-parent').val(),
            fgRowData = facturetteGrid.jqGrid('getRowData', imageaflaguer),
            id = $(this).closest('tr').attr('id'),
            rowData = rapprochementGrid.jqGrid('getRowData', id),
            documenttype = rowData['rg-document-type'],
            url = Routing.generate('tenue_rapprochement_facturette_edit'),
            modalRapprochement = $('#modal-rapprochement');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                imageaflaguer: imageaflaguer,
                documenttype: documenttype,
                documentid: id
            },
            success: function (data) {
                if(data['type'] === 'success'){
                    fgRowData['fg-image-flague-id'] = data['imageflagueid'];
                    facturetteGrid.jqGrid('setRowData', imageaflaguer, fgRowData);

                    modalRapprochement.modal('hide');
                }
            }
        });

    });


    $(document).on('click', '.js-fg-image, .js-rg-image', function () {
        var id = $(this).closest('tr').attr('id'),
            rapprochementGrid = $('#table-rapprochement-list');

        if($(this).hasClass('js-fg-image')) {
            show_image_pop_up(id);
        }
        else{
            var rappDataRow = rapprochementGrid.jqGrid('getRowData', id),
                imageid = rappDataRow['rg-image-id'];
            show_image_pop_up(imageid);
        }
    });

});