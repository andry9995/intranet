var lcrGrid = $('#lcr-list'),
	virementGrid = $('#virement-list'),
	remiseGrid = $('#remise-list'),
	fraisGrid = $('#frais-list'),
    ccReleveGrid = $('#carte-credit-releve-list'),
    cDebitGrid = $('#carte-debit-list'),
    cCreditGrid = $('#carte-credit-list');

$(function() {

    lcrGrid = $('#lcr-list');
    var lastsel_lcr;
    lcrGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow : true,
        userDataOnFooter : true,
        caption: 'Ligne(s) LCR',
        colNames: [
            'N° Ordre', 'N° Facture', 'Date Facture', 'Tireur', 'Compte', 'Montant', 'Action'
        ],
        colModel: [
            {
                name: 'lcr_n_ordre',
                index: 'lcr_n_ordre',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'lcr_n_facture',
                index: 'lcr_n_facture',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'lcr_date_facture',
                index: 'lcr_date_facture',
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'lcr_tireur',
                index: 'lcr_tireur',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'lcr_compte',
                index: 'lcr_compte',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 100,
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc',{create: [471600, 471700]}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [401]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'lcr_montant',
                index: 'lcr_montant',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'lcr_action',
                index: 'lcr_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'lcr_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action lcr-save" title="Enregistrer"></i><i class="fa fa-trash icon-action lcr-delete" title="supprimer"></i>'
                }
            }

        ],



        onSelectRow: function (id) {
            if (id && id !== lastsel_lcr) {
                lcrGrid.restoreRow(lastsel_lcr);
                lastsel_lcr = id;
            }
            lcrGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-lcr').length === 0) {
                lcrGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-lcr" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }

            setGridSize(lcrGrid);


            initButtons($('#statustemp').val());
            checkVerifTotalInput(lcrGrid, data.userdata.lcr_montant);
        }
    });

    $(document).on('click', '#btn-add-lcr', function(event){
    	event.preventDefault();
    	event.stopPropagation();

        addGridRow(lcrGrid);
	});

    $(document).on('click', '.lcr-save', function(event){
    	event.preventDefault();
    	event.stopPropagation();

        if($('#'+lastsel_lcr).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        lcrGrid.jqGrid('saveRow', lastsel_lcr, {
            "aftersavefunc": function(rowid, response) {

                var imageid = $('#image').val();
                reloadGrid(lcrGrid, imageid);

            	show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
	});

    $(document).on('click', '.lcr-delete', function(event){
    	event.preventDefault();
    	event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        lcrGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
	});

    virementGrid = $('#virement-list');
    var lastsel_virement;
    virementGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow : true,
        userDataOnFooter : true,
        caption: 'Ligne(s) VRT/CHQ EMIS',
        colNames: [
            'Date', 'Num', 'Montant', 'Num Fact','Tiers', 'Bénéficiaire', 'Type Tiers', 'Compte', 'Commentaire','Action'
        ],
        colModel: [
            {
                name: 'virement_date',
                index: 'virement_date',
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'virement_num',
                index: 'virement_num',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'virement_montant',
                index: 'virement_montant',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'virement_num_fact',
                index: 'virement_num_fact',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'virement_tiers',
                index: 'virement_tiers',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'virement_beneficiaire',
                index: 'virement_beneficiaire',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'virement_type_tiers',
                index: 'virement_type_tiers',
                align: 'center',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_type_tiers'),
                    dataInit: function (elem) {
                        $(elem).width(180);
                    },
                    dataEvents: [
                        {
                            type: 'change',
                            fn: function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                var gridId = $(this).closest('tr').attr('id'),
                                    select = $('#' + gridId + '_virement_compte'),
                                    banqueTypeId = $('#'+gridId+'_virement_type_tiers').val();
                                $.ajax({
                                    url: Routing.generate('banque_pcc_banque_type', {type: 1, create: [471600, 471700]}),
                                    data:{
                                        dossierid: $('#dossieridtemp').val(),
                                        banquetypeid: banqueTypeId
                                    },
                                    type: 'GET',
                                    success: function(data){
                                        select.html(data);

                                        select.select2({
                                                placeholder: 'choisir...',
                                                allowClear: true
                                        });
                                    }
                                });
                            }
                        }
                    ]
                }
            },
            {
                name: 'virement_compte',
                index: 'virement_compte',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc_banque_type', {type: 0, create: [471600, 471700]}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            rowid: rowid
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'virement_commentaire',
                index: 'virement_commentaire',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'virement_action',
                index: 'virement_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'virement_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action virement-save" title="Enregistrer"></i><i class="fa fa-trash icon-action virement-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_virement) {
                virementGrid.restoreRow(lastsel_virement);
                lastsel_virement = id;
            }
            virementGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-virement').length === 0) {
                virementGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-virement" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }

           setGridSize(virementGrid);

            initButtons($('#statustemp').val());

            checkVerifTotalInput(virementGrid, data.userdata.virement_montant);

        }
    });

    $(document).on('click', '#btn-add-virement', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(virementGrid);
    });

    $(document).on('click', '.virement-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_virement).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        virementGrid.jqGrid('saveRow', lastsel_virement, {
            "aftersavefunc": function(rowid, response) {
                var imageid = $('#image').val();
                reloadGrid(virementGrid, imageid);

                show_info('', response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.virement-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        virementGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });

    remiseGrid = $('#remise-list');
    var lastsel_remise;
    remiseGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        caption: 'Ligne(s) Remise',
        footerrow : true,
        userDataOnFooter : true,
        colNames: [
            'N°Remise', 'N°Chèque', 'Libelle', 'Tiers', 'Compte', 'Montant', 'Action'
        ],
        colModel: [
            {
                name: 'remise_num',
                index: 'remise_num',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'remise_cheque_num',
                index: 'remise_cheque_num',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'remise_libelle',
                index: 'remise_libelle',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'remise_tiers',
                index: 'remise_tiers',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'remise_compte',
                index: 'remise_compte',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc', {create: [471600, 471700]}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [411,580]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'remise_montant',
                index: 'remise_montant',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'remise_action',
                index: 'remise_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'remise_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action remise-save" title="Enregistrer"></i><i class="fa fa-trash icon-action remise-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_remise) {
                remiseGrid.restoreRow(lastsel_remise);
                lastsel_remise = id;
            }
            remiseGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-remise').length === 0) {
                remiseGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-remise" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');


            }


            setGridSize(remiseGrid);

            initButtons($('#statustemp').val());

            checkVerifTotalInput(remiseGrid, data.userdata.remise_montant);
        }

    });

    $(document).on('click', '#btn-add-remise', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(remiseGrid);
    });

    $(document).on('click', '.remise-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_remise).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        remiseGrid.jqGrid('saveRow', lastsel_remise, {
            "aftersavefunc": function(rowid, response) {
                var imageid = $('#image').val();
                reloadGrid(remiseGrid, imageid);

                show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.remise-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        remiseGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });


    fraisGrid = $('#frais-list');
    var lastsel_frais;
    fraisGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow : true,
        userDataOnFooter : true,
        caption: 'Ligne(s) Frais',
        colNames: [
            'Date', 'Libelle', 'HT', 'Taux%',  'TVA', 'Com', 'Total', 'Bilan', 'Tva', 'Resultat', 'Action'
        ],
        colModel: [
            {
                name: 'frais_date',
                index: 'frais_date',
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'frais_libelle',
                index: 'frais_libelle',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'frais_montant_ht',
                index: 'frais_montant_ht',
                align: 'right',
                editable: true,
                sortable: true,
                editoptions: {
                    dataEvents: [
                        {
                            type: 'change',
                            fn: function (e) {
                                e.preventDefault();
                                e.stopPropagation();

                                var gridId = $(this).closest('tr').attr('id'),
                                    montantHt = $('#' + gridId + '_frais_montant_ht').val(),
                                    montantCom = $('#' + gridId + '_frais_montant_com').val(),
                                    tvaTaux = $('#' + gridId + '_frais_taux').find('option:selected').text();

                                if (tvaTaux !== '') {
                                    tvaTaux = parseFloat(tvaTaux);
                                }
                                else {
                                    tvaTaux = 0;
                                }

                                if (montantHt !== '') {
                                    montantHt = parseFloat(montantHt);
                                }
                                else {
                                    montantHt = 0;
                                }

                                if(montantCom !== ''){
                                    montantCom = parseFloat(montantCom);
                                }
                                else{
                                    montantCom = 0;
                                }

                                var montantTva = (montantHt * tvaTaux / 100).toFixed(2);
                                $('#' + gridId + '_frais_montant_ttc').val((parseFloat(montantCom) + parseFloat(montantTva) + parseFloat(montantHt)).toFixed(2));
                                $('#' + gridId + '_frais_montant_tva').val(montantTva);

                            }
                        }
                    ]

                },
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'frais_taux',
                index: 'frais_taux',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_taux_tva'),
                    dataEvents: [
                        {
                            type: 'change',
                            fn: function (e) {
                                e.preventDefault();
                                e.stopPropagation();

                                var gridId = $(this).closest('tr').attr('id'),
                                    montantHt = $('#' + gridId + '_frais_montant_ht').val(),
                                    montantCom = $('#' + gridId + '_frais_montant_com').val(),
                                    tvaTaux = $('#' + gridId + '_frais_taux').find('option:selected').text();

                                if (tvaTaux !== '') {
                                    tvaTaux = parseFloat(tvaTaux);
                                }
                                else {
                                    tvaTaux = 0;
                                }

                                if (montantHt !== '') {
                                    montantHt = parseFloat(montantHt);
                                }
                                else {
                                    montantHt = 0;
                                }

                                if(montantCom !== ''){
                                    montantCom = parseFloat(montantCom);
                                }
                                else{
                                    montantCom = 0;
                                }

                                var montantTva = (montantHt * tvaTaux / 100).toFixed(2);
                                $('#' + gridId + '_frais_montant_ttc').val((parseFloat(montantCom) + parseFloat(montantTva) + parseFloat(montantHt)).toFixed(2));
                                $('#' + gridId + '_frais_montant_tva').val(montantTva);
                            }
                        }
                    ]

                }
            },
            {
                name: 'frais_montant_tva',
                index: 'frais_montant_tva',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'frais_montant_com',
                index: 'frais_montant_com',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                editoptions: {
                    dataEvents: [
                        {
                            type: 'change',
                            fn: function (e) {
                                e.preventDefault();
                                e.stopPropagation();

                                var gridId = $(this).closest('tr').attr('id'),
                                    montantHt = $('#' + gridId + '_frais_montant_ht').val(),
                                    montantCom = $('#' + gridId + '_frais_montant_com').val(),
                                    montantTva = $('#' + gridId + '_frais_montant_tva').val();



                                if (montantHt !== '') {
                                    montantHt = parseFloat(montantHt);
                                }
                                else {
                                    montantHt = 0;
                                }

                                if(montantCom !== ''){
                                    montantCom = parseFloat(montantCom);
                                }
                                else{
                                    montantCom = 0;
                                }

                                if(montantTva !== ''){
                                    montantTva = parseFloat(montantTva);
                                }
                                else{
                                    montantTva = 0;
                                }

                                $('#' + gridId + '_frais_montant_ttc').val((parseFloat(montantCom) + parseFloat(montantTva) + parseFloat(montantHt)).toFixed(2));

                            }
                        }
                    ]
                }
            },
            {
                name: 'frais_montant_ttc',
                index: 'frais_montant_ttc',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'frais_compte_bilan',
                index: 'frais_compte_bilan',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc', {create: [471600, 471700]}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: []
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'frais_compte_tva',
                index: 'frais_compte_tva',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc'),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [445]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'frais_compte_resultat',
                index: 'frais_compte_resultat',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc'),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [627]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'frais_action',
                index: 'frais_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'frais_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action frais-save" title="Enregistrer"></i><i class="fa fa-trash icon-action frais-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_frais) {
                fraisGrid.restoreRow(lastsel_frais);
                lastsel_frais = id;
            }
            fraisGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-frais').length === 0) {
                fraisGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-frais" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }


            setGridSize(fraisGrid);

            var clmethode = $(this).closest('.ob-container').find('.cl_methode_dossier.active').attr('data-type');
            //0: Engagement,  1: Tresorerie
            if(parseInt(clmethode) === 0) {
                fraisGrid.setColProp('frais_compte_resultat', {editable: false});
                fraisGrid.setColProp('frais_compte_bilan', {editable: true});
                fraisGrid.setColProp('frais_compte_tva', {editable: false});
                fraisGrid.setColProp('frais_taux', {editable: false});
                fraisGrid.setColProp('frais_montant_tva', {editable: false});
            }
            else if(parseInt(clmethode) === 1){
                fraisGrid.setColProp('frais_compte_bilan', {editable: false});
                fraisGrid.setColProp('frais_compte_tva', {editable: true});
                fraisGrid.setColProp('frais_taux', {editable: true});
                fraisGrid.setColProp('frais_montant_tva', {editable: true});
                fraisGrid.setColProp('frais_compte_resultat', {editable: true});
            }

            initButtons($('#statustemp').val());
            checkVerifTotalInput(fraisGrid, data.userdata.frais_montant_ttc);

        }
    });

    $(document).on('click', '#btn-add-frais', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(fraisGrid);
    });

    $(document).on('click', '.frais-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_frais).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        fraisGrid.jqGrid('saveRow', lastsel_frais, {
            "aftersavefunc": function(rowid, response) {

                var imageid = $('#image').val();
                reloadGrid(fraisGrid, imageid);


                show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.frais-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        fraisGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });

    ccReleveGrid = $('#carte-credit-releve-list');
    var lastsel_ccreleve;
    ccReleveGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow : true,
        userDataOnFooter : true,

        caption: 'Ligne(s) Carte de credits relevés',
        colNames: [
            'Date', 'Libellé', 'Tiers', 'Code Pos', 'Nature', 'Bilan', 'TVA', 'Resultat', 'Débit', 'Crédit','Dont TVA', 'Dont Com', 'Action'
        ],
        colModel: [
            {
                name: 'ccreleve_date',
                index: 'ccreleve_date',
                align: 'center',
                editable: true,
                sortable: true,
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'ccreleve_libelle',
                index: 'ccreleve_libelle',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'ccreleve_tiers',
                index: 'ccreleve_tiers',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'ccreleve_codepos',
                index: 'ccreleve_codepos',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'ccreleve_nature',
                index: 'ccreleve_nature',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_nature'),
                    dataInit: function (elem) {
                        $(elem).width(120);
                    }
                }
            },
            {
                name: 'ccreleve_compte_bilan',
                index: 'ccreleve_compte_bilan',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc', {create: [471600,471700]}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [401]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'ccreleve_compte_tva',
                index: 'ccreleve_compte_tva',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc'),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [445]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'ccreleve_compte_resultat',
                index: 'ccreleve_compte_resultat',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_pcc', {create: 0}),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossieridtemp').val(),
                            comptes: [6]
                        }
                    },
                    dataInit: function(e){
                        $(e).width(200).select2({
                            placeholder: 'choisir...',
                            allowClear: true
                        });
                    }
                }
            },
            {
                name: 'ccreleve_debit',
                index: 'ccreleve_debit',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'ccreleve_credit',
                index: 'ccreleve_credit',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'ccreleve_tva',
                index: 'ccreleve_tva',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'ccreleve_com',
                index: 'ccreleve_com',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'ccreleve_action',
                index: 'ccreleve_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'ccreleve_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action ccreleve-save" title="Enregistrer"></i><i class="fa fa-trash icon-action ccreleve-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_ccreleve) {
                ccReleveGrid.restoreRow(lastsel_ccreleve);
                lastsel_ccreleve = id;
            }
            ccReleveGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-ccreleve').length === 0) {
                ccReleveGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-ccreleve" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');


            }

           setGridSize(ccReleveGrid);

            var clmethode = $(this).closest('.ob-container').find('.cl_methode_dossier.active').attr('data-type');

            //0: Engagement,  1: Tresorerie
            if(parseInt(clmethode) === 0) {
                ccReleveGrid.setColProp('ccreleve_compte_resultat', {editable: false});
                ccReleveGrid.setColProp('ccreleve_compte_bilan', {editable: true});
                ccReleveGrid.setColProp('ccreleve_compte_tva', {editable: false});
                ccReleveGrid.setColProp('frais_taux', {editable: false});
                ccReleveGrid.setColProp('ccreleve_tva', {editable: false});
            }
            else if(parseInt(clmethode) === 1){
                ccReleveGrid.setColProp('ccreleve_compte_bilan', {editable: false});
                ccReleveGrid.setColProp('ccreleve_compte_resultat', {editable: true});
                ccReleveGrid.setColProp('ccreleve_compte_tva', {editable: true});
                ccReleveGrid.setColProp('ccreleve_tva', {editable: true});

            }

            initButtons($('#statustemp').val());

            var debit = data.userdata.ccreleve_debit,
                credit = data.userdata.ccreleve_credit,
                solde =  parseFloat(debit) - parseFloat(credit) ;

            checkVerifTotalInput(ccReleveGrid, Math.abs(solde));

        }
    });

    $(document).on('click', '#btn-add-ccreleve', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(ccReleveGrid);
    });

    $(document).on('click', '.ccreleve-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_ccreleve).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        ccReleveGrid.jqGrid('saveRow', lastsel_ccreleve, {
            "aftersavefunc": function(rowid, response) {

                var imageid = $('#image').val();
                reloadGrid(ccReleveGrid, imageid);

                show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.ccreleve-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        ccReleveGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });



    cDebitGrid = $('#carte-debit-list');
    var lastsel_cdebit;
    cDebitGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        caption: 'Ligne(s) Carte Débits',
        colNames: [
            'Date', 'Client', 'Action'
        ],
        colModel: [
            {
                name: 'cdebit_date',
                index: 'cdebit_date',
                align: 'center',
                editable: true,
                sortable: true,
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'cdebit_client',
                index: 'cdebit_client',
                align: 'left',
                editable: true,
                sortable: true
            },

            {
                name: 'cdebit_action',
                index: 'cdebit_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'cdebit_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action cdebit-save" title="Enregistrer"></i><i class="fa fa-trash icon-action cdebit-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_cdebit) {
                cDebitGrid.restoreRow(lastsel_cdebit);
                lastsel_cdebit = id;
            }
            cDebitGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-cdebit').length === 0) {
                cDebitGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-cdebit" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');


            }

            setGridSize(cDebitGrid);

            initButtons($('#statustemp').val());

        }
    });

    $(document).on('click', '#btn-add-cdebit', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(cDebitGrid);
    });

    $(document).on('click', '.cdebit-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_cdebit).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        cDebitGrid.jqGrid('saveRow', lastsel_cdebit, {
            "aftersavefunc": function(rowid, response) {

                var imageid = $('#image').val();
                reloadGrid(cDebitGrid, imageid);

                show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.cdebit-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        cDebitGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });



    cCreditGrid = $('#carte-credit-list');
    var lastsel_ccredit;
    cCreditGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 300,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        caption: 'Ligne(s) Carte Crédit',
        colNames: [
            'Date', 'Fournisseur', 'Nature', 'Num CB', 'Montant', 'Action'
        ],
        colModel: [
            {
                name: 'ccredit_date',
                index: 'ccredit_date',
                align: 'center',
                editable: true,
                sortable: true,
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
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'ccredit_fournisseur',
                index: 'ccredit_fournisseur',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'ccredit_nature',
                index: 'ccredit_nature',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_nature'),
                    dataInit: function (elem) {
                        $(elem).width(120);
                    }
                }
            },
            {
                name: 'ccredit_numcb',
                index: 'ccredit_numcb',
                align: 'left',
                editable: true,
                sortable: true
            },
            {
                name: 'ccredit_montant',
                index: 'ccredit_montant',
                align: 'left',
                formatter: 'number',
                sorttype: 'number',
                editable: true,
                sortable: true
            },
            {
                name: 'ccredit_action',
                index: 'ccredit_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'ccredit_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action ccredit-save" title="Enregistrer"></i><i class="fa fa-trash icon-action ccredit-delete" title="supprimer"></i>'
                }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_ccredit) {
                cCreditGrid.restoreRow(lastsel_ccredit);
                lastsel_ccredit = id;
            }
            cCreditGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            if ($('#btn-add-ccredit').length === 0) {
                cCreditGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-ccredit" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');


            }

            setGridSize(cCreditGrid);

            initButtons($('#statustemp').val());

        }
    });

    $(document).on('click', '#btn-add-ccredit', function(event){
        event.preventDefault();
        event.stopPropagation();

        addGridRow(cCreditGrid);
    });

    $(document).on('click', '.ccredit-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_ccredit).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        cCreditGrid.jqGrid('saveRow', lastsel_ccredit, {
            "aftersavefunc": function(rowid, response) {
                var imageid = $('#image').val();
                reloadGrid(cCreditGrid, imageid);

                validerImage(imageid);

                show_info('',response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.ccredit-delete', function(event){
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        cCreditGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_details_ligne_ob_delete'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });


    $(document).on ('click', '.methode-dossier .cl_methode_dossier', function(event) {
        var txt = $(this).find('a').text(),
            lis = $(this).parent().find('li'),
            libelle = $(this).closest('.methode-dossier').find('.libelle');

        lis.removeClass('active');
        $(this).addClass('active');

        libelle.text(txt);


        var obcontainer = $(this).closest('.ob-container'),
            imageid = $('#image').val(),
            containerid = obcontainer.attr('id');


        switch (containerid){
            case 'cartecreditreleve':
                reloadGrid(ccReleveGrid, imageid);
                break;
            case 'autres':
                reloadGrid(fraisGrid, imageid);
                break;
            default:
                break;
        }

    });


    $( "#mainside, .forme" ).resizable();

	$('#imageside').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		if (document.getElementById("mySidenav").style.width == "140px"){
			document.getElementById("mySidenav").style.width = "0px";		
		} else {
			document.getElementById("mySidenav").style.width = "140px";		
		}
	});	
	$('#minfos').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		if ($('#iperdos').is(":visible")){
			$('#iperdos').hide();
		} else {
			$('#iperdos').show();
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
	
	$('#imageclose').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		document.getElementById("mySidenav").style.width = "0px";	
	});	
	$('.chosenimages').chosen({width: "450px"});
	$('.chosen-select-banque').chosen({
        no_results_text: "Aucun banque trouvé:",
        search_contains: true,
        width: '100%'
    });
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
        var url = Routing.generate('banque_dossier'),
			idata = {};
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

    // Changement dossier
    $(document).on('change', '#exercice', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var url = Routing.generate('banque_count_souscategorie'),
            did = $('#dossier').val(),
            exercice = $('#exercice').val();

        $.ajax({
            url: url,
            type: 'GET',
            data:{
                dossierid: did,
                exercice: exercice
            },
            success: function(data) {

                var countSoucategories = data.souscategorie,
                    countSoussouscategoires = data.soussouscategorie,
                    souscats = $('#souscat option'),
                    soussouscats = $('#souscat option[value="1"]');

                souscats.each(function () {
                    var opt = $(this),
                        oldTxt = opt.text().replace(' ', '');

                    opt.text(oldTxt.split('-')[0]);

                    if (parseInt(opt.attr('value')) !== 1) {
                        $.each(countSoucategories, function (k, v) {
                            var souscatVal = parseInt(opt.attr('value')),
                                reste = v.nbre;

                            if (souscatVal === v.souscategorie_id) {
                                reste = v.nbre - v.valide;
                                opt.text(oldTxt.split('-')[0] + ' - ' + v.nbre + ' ['+reste+']');
                            }
                        });
                    }
                });

                // soussouscats.each(function () {
                //     var opt = $(this),
                //         oldTxt = opt.text().replace(' ', '');
                //     opt.text(oldTxt.split('-')[0]);
                //     $.each(countSoussouscategoires, function (k, v) {
                //         var soussouscatVal = parseInt(opt.attr('data-soussouscategorie-id')),
                //             reste = v.nbre;
                //         if(soussouscatVal === v.soussouscategorie_id) {
                //             reste = v.nbre - v.valide;
                //             opt.text(oldTxt.split('-')[0] + ' - ' + v.nbre+ ' ['+reste+']');
                //         }
                //     })
                // });
                initDateScan();
            }
        });


    });


    $(document).on('change', '#souscat', function (event) {
        event.stopPropagation();
        event.preventDefault();

        initDateScan();
    });

	//panier

    $('#btn_panier').on('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

        $('#pdf').html('');

        var souscat = $('#souscat').val(),
            etape = $('#etape').val(),
            selectedOption = $('#souscat option:selected'),
            soussouscat = selectedOption.attr('data-soussouscategorie-id'),
            soussouscatadd = selectedOption.attr('data-soussouscategorie-add')
        ;

        if(soussouscat === undefined){
            soussouscat = -1;
        }

        if(soussouscatadd === undefined){
            soussouscatadd = -1;
        }

        $.ajax({
            url: Routing.generate('banque_get_panier'),
            type: "GET",
            dataType: "html",
            async: true,
            data: {
                souscat: souscat,
                soussouscat: soussouscat,
                soussouscatadd: soussouscatadd,
                etape: etape
            },
            success: function (data) {

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
                initGridBySouscategorie(souscat, soussouscat,-1);

                $('#pdf-resize').resizable();

            }
        });

        return false;


    });

	$('#btn_ass').on('click', function (event) {
		event.preventDefault();
        event.stopPropagation();
		return false;
	});


    $(document).on('click', '.js_imgbq_selected', function () {
        $('#informations').show();
        var lastsel_piece = $(this).closest('span').attr('id');
        $('.js_imgbq_selected').each(function () {
            $(this).css("background-color", "transparent");
            if ($(this).attr("data-id") == 1) {
                $(this).css("background-color", "#FFD966");
            }
        });
        $(this).closest('span').css("background-color", "#f8ac59");
        $('#imagesuiv').val($(this).closest('tr').next().find('span').attr('id'));

        var height = $(window).height() * 0.95;

        detailsImage(lastsel_piece, height);

    });


    $(document).on('dblclick', '#panier-list .lot', function () {
        //Modifier-na ny eo @ client & dossier

        $('#pdf').html('');
        vider();

        var dossierid = $(this).attr('data-dossier-id'),
            // souscat = $('#souscat').val(),
            souscat = $(this).attr('data-souscategorie-id'),
            // soussouscat = $('#souscat option:selected').attr('data-soussouscategorie-id');
            soussouscat = $(this).attr('data-soussouscategorie-id'),
            iperdos = $('#iperdos'),
            exercice = $(this).attr('data-exercice')
        ;

        if(soussouscat === undefined){
            soussouscat = -1;
        }

        loadInfoPerdos(iperdos, $('#minfos'), dossierid, exercice);

        $.ajax({
            url: Routing.generate('banque_liste_image_panier'),
            type: 'POST',
            data: {
                dossier: $(this).attr('data-dossier-id'),
                exercice: exercice,
                souscat: souscat,
                soussouscat: soussouscat,
                etape: 'OS_1'
            },
            success: function (data) {

                vider();

                if (data !== '') {
                    var mfini = $('#mfini'),
                        myModal = $('#myModal'),
                        modalBody = myModal.find('.modal-body'),
                        mySidenav = $('#mySidenav');


                    initSoussouscategorie(souscat);
                    $('#isouscategorie').val(souscat);

                    if(soussouscat !== -1) {
                        $('#isoussouscategorie').val(soussouscat);
                    }

                    mySidenav.html(data);
                    $('.viewer-container').hide();
                    $('#mainside').hide();
                    $('#virement').hide();
                    $('.forme').hide();
                    iperdos.hide();
                    myModal.modal('show');

                    SetmodalHeight('myModal');

                    $('#lesimages').hide();
                    $('#btn_ass').hide();

                    mfini.show();
                    mfini.removeAttr('style');

                    initBanquecompteListe(dossierid);

                    var status = $('#statustemp').val(),
                        title = $('#titletemp').val();

                    if(status !== ''){
                        status = '<span class="label label-danger">'+status+'</span>';
                    }

                    initButtons(status);

                    $('#js_titre').html(title+' '+status);

                    var modalBodyHeight = modalBody.height();
                    mySidenav.height(modalBodyHeight - 5);

                    choisirtraitement(dossierid);

                    initGridBySouscategorie(souscat, soussouscat,-1);

                } else {
                    show_info('Saisie OB', "Aucun résultat", 'warning');
                }
            }
        });

        return false;
    });

	//liste image a traiter
	$('#btn_go').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var souscat = $('#souscat').val(),
            dossier = $('#dossier').val(),
			exercice = $('#exercice').val(),
            dscan = $('#dscan').val(),
            selectedOption = $('#souscat option:selected'),
            soussouscat = selectedOption.attr('data-soussouscategorie-id'),
            soussouscatadd = selectedOption.attr('data-soussouscategorie-add')
        ;

      if(soussouscat === undefined){
          soussouscat = -1;
      }


        $('#pdf').html('');
       
        if(dossier =='' || exercice == '' || dscan == '') {
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
                    soussouscat: soussouscat,
                    soussouscatadd: soussouscatadd,
                    etape: $("#etape").val(),
                    exercice: exercice
                },
                success: function (data) {

                    vider();

                    if (data !== '') {
                        var souscategorie = $('#souscat').val(),
                            mySidenav = $('#mySidenav'),
                            myModal = $('#myModal'),
                            modalBody = myModal.find('.modal-body');

                        initSoussouscategorie(souscategorie);
                        $('#isouscategorie').val(souscategorie);

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

                        var status = $('#statustemp').val(),
                            title = $('#titletemp').val();

                        if (status !== '') {
                            status = '<span class="label label-danger">' + status + '</span>';
                        }

                        initButtons(status);

                        $('#js_titre').html(title + ' ' + status);

                        $('#mfini').hide();
                        initBanquecompteListe(dossier);

                        initGridBySouscategorie(souscat, soussouscat, -1);

                        var modalBodyHeight = modalBody.height();
                        mySidenav.height(modalBodyHeight - 5);

                        $('#pdf-resize').resizable();

                        choisirtraitement(dossier);
                    } else {
                        show_info('Saisie OB', "Aucun résultat", 'warning');
                    }

                }
            });
        }
        return false;
    });


	$(document).on('change', '#ftaux', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var tva = $('#fht').val() * $( "#ftaux option:selected" ).text() / 100,
			ttc = tva + parseFloat($('#fht').val());
		$('#ftva').val(tva.toFixed(2));
		$('#fttc').val(ttc.toFixed(2));
    });

    $(document).on('change', '#isouscategorie', function () {
        var souscategorie = $(this).val();

       initSoussouscategorie(souscategorie);

    });


    $('#datecartecreditreleve').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
    $('#js_debut_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#js_fin_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#dateregl').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#dateech').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#datef').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#datevi').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#rdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#lcrdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#reldate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#ticdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#fdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	
	
	
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
	//submit remisebanque
	$('#js_form_remisebanque_submit').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		var totalecheque = $('#rtotal').val().replace(/\s/g, '');
		if(totalecheque>0){
			$('#rtotal').parent().parent().removeClass('has-error');
		} else {
			$('#rtotal').parent().parent().addClass('has-error');
			return false;
		}
		if($('#rnombrecheque').val()>0){
			$('#rnombrecheque').parent().parent().removeClass('has-error');
		} else {
			$('#rnombrecheque').parent().parent().addClass('has-error');
			return false;
		}
		var imageid = $('#image').val();
		
		$.ajax({
			url: Routing.generate('banque_entete_remise'),
			type: 'POST',
			data: {
				imagid: imageid,
				banquecompte:$('#banquecomptes').val(),
				totalecheque : totalecheque,
				nombrecheque : $('#rnombrecheque').val(),
				dateremise : $('#rdate').val()
			},
			success: function () {
                reloadGrid(remiseGrid, imageid);
				validerImage(imageid);
				show_info("Saisie remise", "Enregistrement effectuée", "success");
			}
		});
		return false;
	});	

	$('#js_form_virementbanque_submit').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		var total = $('#totalvi').val().replace(/\s/g, ''),
            imageid = $('#image').val();


		$.ajax({
			url: Routing.generate('banque_entete_vir'),
			type: 'POST',
			data: {
				imagid: imageid,
				numcompte:$('#js_num_compt_bq').val(),
				datevi:$('#datevi').val(),
				total:total,
				banquecompte: $('#banquecomptes').val()
			},
			success: function () {
                reloadGrid(virementGrid, imageid);
				validerImage(imageid);
				show_info("Saisie Virement", "Enregistrement effectuée", "success");	
			}
		});
        return false;
    });

	$('#js_form_frais_submit').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		var totalfrais = $('#totalfrais').val().replace(/\s/g, ''),
            imageid = $('#image').val();

		$.ajax({
			url: Routing.generate('banque_entete_autre'),
			type: 'POST',
			data: {
				imagid: imageid,
				numcompte:$('#js_num_compt_bq').val(),
				nombanque:$('#banques').val(),
				dossier:$('#dossier').val(),
				iban:$('#js_iban_bq').val(),
				totalfrais:totalfrais,
				datef:$('#datef').val(),
				numf:$('#numf').val()
			},
			success: function () {
                reloadGrid(fraisGrid, imageid);
				validerImage(imageid);
				show_info("Saisie Frais Bancaire", "Enregistrement effectuée", "success");	
			}
		});
        return false;
    });

	$('#js_form_tic_submit').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		//var total = $('#tictotal').val().replace(/\s/g, '');	
        var imageid = $('#image').val();

		$.ajax({
			url: Routing.generate('banque_entete_tic'),
			type: 'POST',
			data: {
				imagid: imageid,
				numcompte:$('#js_num_compt_bq').val(),
				nombanque:$('#banques').val(),
				dossier:$('#dossier').val(),
				iban:$('#js_iban_bq').val()
			},
			success: function () {
				validerImage(imageid);
				show_info("Saisie Ticket CB", "Enregistrement effectuée", "success");	
			}
		});
        return false;
    });
	//lcrsubmit

	$(document).on('click', '#js_form_lcrbanque_submit', function (event){
        event.preventDefault();
        event.stopPropagation();
		var total = $('#totallcr').val().replace(/\s/g, ''),
            imagid = $('#image').val(),
            dateregl = $('#dateregl').val(),
            dateech = $('#dateech').val(),
            relevelcr = $('#relevelcr').val(),
            nombreligne = $('#nombreligne').val(),
            banquecompteid =  $('#banquecomptes').val(),
			datef = $('#datef').val(),
			numf = $('#numf').val(),
			totalfrais = $('#totalfrais').val().replace(/\s/g, ''),
            obContainer = $(this).closest('.ob-container'),
            idObContainer = obContainer.attr('id'),
            isFrais = true,
            engagement = -1
        ;



            if(idObContainer === 'lcrbanque') {
                isFrais = false;
            }

            if(isFrais){
                engagement = obContainer.find('.methode-dossier .cl_methode_dossier.active').attr('data-type');
            }

		$.ajax({
			url: Routing.generate('banque_entete_lcr'),
			type: 'POST',
			data: {
				imagid:imagid,
				dateregl:dateregl,
				dateech:dateech,
				relevelcr:relevelcr,
				nombreligne:nombreligne,
				total:total,
				banquecompteid:banquecompteid,
				datef: datef,
				numf: numf,
				totalfrais: totalfrais,
                engagement: engagement,
                isfrais: isFrais
			},
			success: function () {

			    if(!isFrais) {
                    reloadGrid(lcrGrid, imagid);
                }
                else{
			        reloadGrid(fraisGrid, imagid);
                }

				validerImage(imagid);
				show_info("Saisie LCR", "Enregistrement effectuée", "success");	
			}
		});
        return false;
    });

	$(document).on('click', '#js_form_cartecreditreleve_submit', function(event){
	   event.preventDefault();
	   event.stopPropagation();

	   var imageid = $('#image').val(),
           banquecompteid = $('#banquecomptes').val(),
           obContainer = $(this).closest('.ob-container'),
           numcb = obContainer.find('.numcbs').val(),
           typecb = obContainer.find('.typecb').val(),
           engagement = obContainer.find('.methode-dossier .cl_methode_dossier.active').attr('data-type');

	   if(engagement === undefined){
	       engagement = -1;
       }

        $.ajax({
            url: Routing.generate('banque_entete_carte_credit_releve'),
            type: 'POST',
            data: {
                imagid: imageid,
                banquecompteid:banquecompteid,
                dossier:$('#dossier').val(),
                total:$('#totalcartecreditreleve').val().replace(/\s/g, ''),
                datecarte: $('#datecartecreditreleve').val(),
                numcbid: numcb,
                typecb: typecb,
                engagement: engagement
            },
            success: function () {
                reloadGrid(ccReleveGrid, imageid);
                validerImage(imageid);
                show_info("Saisie Carte de credit relevé", "Enregistrement effectuée", "success");
            }
        });
        return false;

    });

	$(document).on('click', '#js_form_cartedebit_submit', function(event){
	   event.preventDefault();
	   event.stopPropagation();

	   var imageid = $('#image').val();

        $.ajax({
            url: Routing.generate('banque_entete_carte_debit'),
            type: 'POST',
            data: {
                imagid: imageid,
                banquecompteid:$('#banquecomptes').val()
            },
            success: function () {
                reloadGrid(cDebitGrid, imageid);
                validerImage(imageid);
                show_info("Saisie Carte Débit", "Enregistrement effectuée", "success");
            }
        });
        return false;

    });

    $(document).on('click', '#js_form_cartedebit_anpc', function(event){
        event.preventDefault();
        event.stopPropagation();

        swal({
            title: 'Attention',
            text: "Voulez-vous mettre cette image en ANPC?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
                // $.ajax({
                //     url: Routing.generate('banque_entete_carte_debit_anpc'),
                //     type: 'POST',
                //     data: {
                //         imagid:$('#image').val()
                //     },
                //     success: function (data) {
                //
                //         suivant(data);
                //         show_info("Saisie Carte Débit", "Enregistrement effectuée", "success");
                //     }
                // });
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );

    });

    $(document).on('click', '#js_form_cartecredit_submit', function(event){
        event.preventDefault();
        event.stopPropagation();

        var imageid = $('#image').val(),
            obContainer = $(this).closest('.ob-container'),
            numcb = obContainer.find('.numcbs').val(),
            typecb = obContainer.find('.typecb').val()
        ;

        $.ajax({
            url: Routing.generate('banque_entete_carte_debit'),
            type: 'POST',
            data: {
                imagid: imageid,
                numcbid: numcb,
                typecb: typecb,
                banquecompteid:$('#banquecomptes').val()
            },
            success: function () {
                reloadGrid(cCreditGrid, imageid);
                validerImage(imageid);
                show_info("Saisie Carte Crédit", "Enregistrement effectuée", "success");
            }
        });
        return false;

    });

    $(document).on('change', '#banquecomptes', function(event){
        event.preventDefault();
        event.stopPropagation();

        var obContainer = $('.ob-container:visible'),
            banquecompteid = $(this).val()
        ;

        if(obContainer.attr('id') ==='cartecreditreleve' ||
            obContainer.attr('id') ==='cartecredit'){
            initNumCbCombo(banquecompteid, -1, obContainer);
        }

    });

    $(document).on('change', '.numcbs', function(event){
        event.stopPropagation();
        event.preventDefault();

        var typecb = $(this).find('option:selected').attr('data-type');
        if(typecb === undefined)
            typecb = -1;

        $(this).closest('.ob-container').find('.typecb').val(typecb);
    });

    $(document).on('click', '.btn-add-numcb, .btn-undo-numcb', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var obContainer = $(this).closest('.ob-container'),
            inputgroupAddNumCb = obContainer.find('.btn-add-numcb').closest('.input-group'),
            inputgroupSaveNumCb = obContainer.find('.btn-save-numcb').closest('.input-group')
        ;

        if ($(this).hasClass('btn-add-numcb')) {
            if (!inputgroupAddNumCb.hasClass('hidden')) {
                inputgroupAddNumCb.addClass('hidden');
                inputgroupSaveNumCb.removeClass('hidden');
            }
        }
        else {
            if (!inputgroupSaveNumCb.hasClass('hidden')) {
                inputgroupSaveNumCb.addClass('hidden');
                inputgroupAddNumCb.removeClass('hidden');
            }
        }

    });

    $(document).on('click', '.btn-save-numcb', function(event) {
        event.preventDefault();
        event.stopPropagation();


        var obContainer = $(this).closest('.ob-container'),
            inputgroupSave = $(this).closest('.input-group'),
            inputgroupAdd = obContainer.find('.btn-add-numcb').closest('.input-group'),
            numcb = obContainer.find('.newnumcb').val(),
            banquecompteid = $('#banquecomptes').val();

        $.ajax({
            url: Routing.generate('banque_save_num_cb'),
            type: 'POST',
            data: {
                banquecompteid: banquecompteid,
                numcb: numcb
            },
            success: function (data) {
                show_info('', data.message, data.type);

                obContainer.find('.newnumcb').val('');

                if (data.type === 'success' || data.type === 'warning') {
                    if (!inputgroupSave.hasClass('hidden')) {
                        inputgroupSave.addClass('hidden');
                        inputgroupAdd.removeClass('hidden');
                    }

                    var numcbid = data.id
                      ;

                    if (data.type === 'success') {
                        initNumCbCombo(banquecompteid, numcbid, obContainer);
                    }

                }
            }
        });


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
                    datescan = moment($(this).attr('data-datescan')).format('DD/MM/Y'),
                    tache = $(this).attr('data-tache');


                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
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
	
function completer(num){
	//--auto generate key from num compte
        var five_num = num.replace(/\s/g, '').length,
            code_bq,
            num_compte,
            code_guichet,
            val_num_compt,
            cle_rib;
		if (five_num==11 || five_num==8 || five_num==9){
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
					$('#banques').val(data.id);
					$('#banques').trigger("chosen:updated");
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
								$('#banques').val(data.id);
								$('#banques').trigger("chosen:updated");
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
			$('#banques').val(2093);
			$('#banques').trigger("chosen:updated");
		}	
}



function initSoussouscategorie(souscategorie){
    $.ajax({
        url: Routing.generate('banque_soussouscategorie'),
        type: 'GET',
        data: {souscategorie: souscategorie},
        success: function (data) {
            $('#isoussouscategorie').html(data);
        }
    });
}


function detailsImage(imageid, height) {
    $.ajax({
        data: {
            imgid: imageid,
            height: height
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

            var modalBody = $('#myModal').find('#result'),
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
            $('#reltotal').val(data.montant_ttc);
            $('#relnum').val(data.num_facture);

            //remise banque
            $('#rnombrecheque').val(data.nombre_cheque);
            $('#rtotal').val(mi(data.total_cheque));
            $('#rdate').val(data.date_remise);

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

            //virement
            $('#datevi').val(data.date_echeance);
            $('#totalvi').val(mi(data.totallcr));

            //carte credit releve
            $('#totalcartecreditreleve').val(data.totalccreleve);

            $('#datecartecreditreleve').val(data.dateccreleve);

            $('.saisir_label').hide();

            var souscat = $('#souscat').val(),
                soussouscat = $('#souscat option:selected').attr('data-soussouscategorie-id');

            if(soussouscat === undefined){
                soussouscat = -1;
            }

            var convention = data.convention,
                engagement = data.engagement,
                methodeDossiers = $('.cl_methode_dossier ');

            methodeDossiers.removeClass('active');
            //0: Engagement, 1:Tresorerie
            if(parseInt(engagement) === 1){
                $('.libelle').html('Treserorie');
                $('.cl_methode_dossier[data-type="1"]').addClass('active');
            }
            else {
                //Engagement
                if (convention === 1) {
                    $('.libelle').html('Engagement');
                    $('.cl_methode_dossier[data-type="0"]').addClass('active');
                }

                else {
                    $('.libelle').html('Treserorie');
                    $('.cl_methode_dossier[data-type="1"]').addClass('active');
                }
            }

            var lab = $('#result').find('.ob-container:visible')
                    .find('.saisir_label'),
                banquecompte = $('#banquecomptes').val();


            // setASaisirByBanqueCompte(banquecompte, lab);

            setAsaisirBySouscategorie(data.dossier, souscat, lab);

            initGridBySouscategorie(souscat, soussouscat, imageid);


            //carte de credit releve ihany no misy cb na credit Ticket
            if(parseInt(data.infocat.ssc) === 1901 || parseInt(data.infocat.ssc) === 3) {
                var numcb = data.numcb;
                if(numcb === null)
                    numcb = -1;

                initNumCbCombo(banquecompte, numcb, $('.ob-container:visible'));
            }


            popo();
            return false;
        }
    });
}

function initGridBySouscategorie(souscategorie, soussouscategorie,imageid){
    var obExcelLcr = $('#obexcel-lcr'),
        parentLcr = obExcelLcr.closest('.col-md-4'),
        obExcelCcr = $('#obexcel-ccr'),
        parentCcr = obExcelCcr.closest('.col-md-4'),
        obExcelChq = $('#obexcel-chq'),
        parentChq = obExcelChq.closest('.col-md-4'),
        obExcelRemise = $('#obexcel-remise'),
        parentRemise = obExcelRemise.closest('.col-md-4')
    ;

    if(!parentLcr.hasClass('hidden')){
        parentLcr.addClass('hidden');
    }

    if(!parentCcr.hasClass('hidden')){
        parentCcr.addClass('hidden');
    }

    if(!parentChq.hasClass('hidden')){
        parentChq.addClass('hidden');
    }

    if(!parentRemise.hasClass('hidden')){
        parentRemise.addClass('hidden');
    }

    switch(parseInt(souscategorie)) {


        case 5:
            parentLcr.removeClass('hidden');
            obExcelLcr.fileinput({
                language: 'fr',
                theme: 'fa',
                uploadAsync: false,
                showPreview: false,
                showUpload: true,
                showRemove: false,
                fileTypeSettings: {
                    text: function(vType, vName) {
                        return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
                    }
                },
                allowedFileTypes: ['image', 'text', 'pdf'],
                uploadUrl: Routing.generate('banque_details_lcr_import')

            });

            reloadGrid(lcrGrid, imageid);

            obExcelLcr.on('filebatchuploadsuccess', function(event, data, previewId, index) {

                show_info('Import', 'Importation effectuée', 'success');

                var imageval = $('#image').val();

                reloadGrid(lcrGrid, imageval);

            });


            break;

        case 6:
        case 153:
            parentChq.removeClass('hidden');
            obExcelChq.fileinput({
                language: 'fr',
                theme: 'fa',
                uploadAsync: false,
                showPreview: false,
                showUpload: true,
                showRemove: false,
                fileTypeSettings: {
                    text: function(vType, vName) {
                        return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
                    }
                },
                allowedFileTypes: ['image', 'text', 'pdf'],
                uploadUrl: Routing.generate('banque_details_virement_import',{dossierid: $('#dossier').val(), exercice: $('#exercice').val()})

            });

            reloadGrid(virementGrid, imageid);

            obExcelChq.on('filebatchuploadsuccess', function(event, data, previewId, index) {

                show_info('Import', 'Importation effectuée', 'success');

                var imageval = $('#image').val();

                reloadGrid(virementGrid, imageval);

            });


            break;

        case 7:

            parentRemise.removeClass('hidden');
            obExcelRemise.fileinput({
                language: 'fr',
                theme: 'fa',
                uploadAsync: false,
                showPreview: false,
                showUpload: true,
                showRemove: false,
                fileTypeSettings: {
                    text: function(vType, vName) {
                        return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
                    }
                },
                allowedFileTypes: ['image', 'text', 'pdf'],
                uploadUrl: Routing.generate('banque_details_remise_import',{dossierid: $('#dossier').val(), exercice: $('#exercice').val()})

            });

            reloadGrid(remiseGrid, imageid);

            obExcelRemise.on('filebatchuploadsuccess', function(event, data, previewId, index) {

                show_info('Import', 'Importation effectuée', 'success');

                var imageval = $('#image').val();

                reloadGrid(remiseGrid, imageval);

            });


            break;

        case 8:
            reloadGrid(fraisGrid, imageid);
            break;

        // case 1:
        //
        //     switch (parseInt(soussouscategorie)){
        //         case 1901:
        //
        //             parentCcr.removeClass('hidden');
        //
        //             reloadGrid(ccReleveGrid, imageid);
        //
        //              obExcelCcr.fileinput({
        //                 language: 'fr',
        //                 theme: 'fa',
        //                 uploadAsync: false,
        //                 showPreview: false,
        //                 showUpload: true,
        //                 showRemove: false,
        //                 fileTypeSettings: {
        //                     text: function(vType, vName) {
        //                         return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
        //                     }
        //                 },
        //                 allowedFileTypes: ['image', 'text', 'pdf'],
        //                 uploadUrl: Routing.generate('banque_details_carte_credit_releve_import')
        //
        //             });
        //
        //
        //
        //             obExcelCcr.on('filebatchuploadsuccess', function(event, data, previewId, index) {
        //
        //                 show_info('Import', 'Importation effectuée', 'success');
        //
        //                 var imageval = $('#image').val();
        //
        //                 reloadGrid(ccReleveGrid, imageval);
        //             });
        //
        //
        //             break;
        //         case 2791:
        //             reloadGrid(cDebitGrid,imageid);
        //             break;
        //
        //         case 3:
        //              reloadGrid(cCreditGrid, imageid);
        //             break;
        //     }

            case 941:
                parentCcr.removeClass('hidden');

                reloadGrid(ccReleveGrid, imageid);

                obExcelCcr.fileinput({
                    language: 'fr',
                    theme: 'fa',
                    uploadAsync: false,
                    showPreview: false,
                    showUpload: true,
                    showRemove: false,
                    fileTypeSettings: {
                        text: function(vType, vName) {
                            return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
                        }
                    },
                    allowedFileTypes: ['image', 'text', 'pdf'],
                    uploadUrl: Routing.generate('banque_details_carte_credit_releve_import')

                });



                obExcelCcr.on('filebatchuploadsuccess', function(event, data, previewId, index) {

                    show_info('Import', 'Importation effectuée', 'success');

                    var imageval = $('#image').val();

                    reloadGrid(ccReleveGrid, imageval);
                });


                break;
            case 939:
                reloadGrid(cDebitGrid,imageid);
                break;

            case 937:
                reloadGrid(cCreditGrid, imageid);
                break;



    }
}

function reloadGrid(jqgrid, imageid){

    var id = jqgrid.attr('id'),
        url =  '',
        editUrl = '';

    switch (id){
        case 'lcr-list':
            url = Routing.generate('banque_details_lcr', {imageid: imageid});
            editUrl = Routing.generate('banque_details_lcr_edit', {imageid: imageid });

            break;

        case 'virement-list':
            url = Routing.generate('banque_details_virement', {imageid: imageid});
            editUrl = Routing.generate('banque_details_virement_edit', {imageid: imageid});
            break;

        case 'remise-list':
            url = Routing.generate('banque_details_remise', {imageid: imageid});
            editUrl = Routing.generate('banque_details_remise_edit', {imageid: imageid});
            break;

        case 'frais-list':
            url = Routing.generate('banque_details_frais', {imageid: imageid});
            editUrl = Routing.generate('banque_details_frais_edit', {imageid: imageid});
            break;

        case 'carte-credit-releve-list':
            url = Routing.generate('banque_details_carte_credit_releve', {imageid: imageid});
            editUrl = Routing.generate('banque_details_carte_credit_releve_edit', {imageid: imageid});
            break;

        case 'carte-debit-list':
            url = Routing.generate('banque_details_carte_debit', {imageid: imageid});
            editUrl = Routing.generate('banque_details_carte_debit_edit', {imageid: imageid});
            break;

        case 'carte-credit-list':
            url = Routing.generate('banque_details_carte_credit', {imageid: imageid});
            editUrl = Routing.generate('banque_details_carte_credit_edit', {imageid: imageid});
            break;
    }


    if(url !== '') {

        jqgrid.jqGrid('setGridParam', {
                url: url,
                editurl: editUrl,
                datatype: 'json'
            }
        )
            .trigger('reloadGrid', {fromServer: true, page: 1});
    }
}

function popo() {
    $('[data-toggle="popover"]').popover({html: true}).on('shown.bs.popover', function () {
        $(".checkpcg input[type=radio]").on('click', function () {
            $("#pcc").jstree();
            $('.search-input').val($(this).attr("data-x"));
            $.ajax({
                url: Routing.generate('banque_get_pcc'),
                type: 'POST',
                data: {
                    dossier: $('#dossier').val(),
                    pcg: $(this).attr("id").substr(1, 20),
                },
                success: function (data) {
                    $("#pcc").jstree("destroy");
                    $("#pcc").jstree({
                        'core': {'data': data},
                        'multiple': false,
                        'checkbox': {
                            'deselect_all': true,
                            'three_state': false,
                        },
                        'plugins': ['checkbox', 'search'],
                        'search': {
                            'case_sensitive': false,
                            'show_only_matches': true
                        }
                    }).on('ready.jstree', function () {
                        $('.search-input').on('keyup', function () {
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
            var id = $(this).attr("data-id");
            url = Routing.generate('banque_set_compte');
            $.ajax({
                url: url,
                type: "POST",
                dataType: "json",
                data: {
                    "compte": compte,
                    "id": id,
                },
                async: true,
                success: function (data) {
                    $('#cp' + id).html(data);
                    $('[data-toggle="popover"]').each(function () {
                        $(this).popover('hide');
                    });
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
    $('#rdate').val('');
    $('#rnombrecheque').val('');
    $('#rtotal').val('');
    $('#rmontant').val('');
    $('#rlibelle').val('');
    $('#remet').val('');
    $('#rnumcheque').val('');
    $('#rnumremise').val('');
    $('#datevi').val();
    $('#totalvi').val();

    $('#lignevi').html('');
    $('#ligner').html('');
    $('#lignelcr').html('');

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

    $('#datecartecreditreleve').val('');
    $('.numcbs').val(-1);
    $('.typecb').val(-1);

    $('.verif-total').val(0);
    $('.verif-date').val('');

    initializeCommun();
}
function supprimer() {
    $('.sligne').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $.ajax({
            data: {
                lid: $(this).attr('data-id'),
                image: $('#image').val(),
                souscat: $('#souscat').val()
            },
            url: Routing.generate('supprimer_ligne_banque'),
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                if ($.type(data) !== "string") {
                    if ($("#souscat").val() == 6) {
                        $('#lignevi').html(data.html);
                    } else if ($("#souscat").val() == 7) {
                        $('#ligner').html(data.html);
                    } else if ($("#souscat").val() == 5) {
                        $('#lignelcr').html(data.html);
                    } else if ($("#souscat").val() == 13) {
                        $('#lignetic').html(data.html);
                    } else if ($("#souscat").val() == 14) {
                        $('#lignerel').html(data.html);
                    }
                    supprimer();
                    editer();
                    $.each(data, function (key, value) {
                        if (!isNaN(key)) {
                            $('#q' + key).attr("data-content", value);
                        }
                    });
                }
                popo();
                $('#lid').val(0);
            }
        });
        return false;
    });
}
function editer(){
	$('.eligne').on('click', function (event) {
		event.preventDefault();
		event.stopPropagation();
		$('#iajout').show();
		var lid =$(this).attr('data-id');
		$.ajax({
			data: {
				lid: lid,
				image: $('#image').val(),
				souscat:$('#souscat').val()
			},
			url: Routing.generate('editer_ligne_banque'),
			type: 'POST',
			dataType: 'json',
			success: function (data) {
                if ($('#souscat').val() == 10) {
                    $('#ldate').val(data.date);
                    $('#llibelle').val(data.libelle);
                    $('#ldebit').val(mi(data.debit));
                    $('#lcredit').val(mi(data.credit));
                    $('#lcommentaire').val(data.commentaire);
                } else if ($('#souscat').val() == 6) {
                    $('#rdate').val(data.datie);
                    $('#vibenef').val(data.nom_tiers);
                    $('#rlibelle').val(data.libelle);
                    $('#vinum').val(data.num_virement);
                    $('#vimontant').val(data.montant);
                    $('#vitype').val(data.type_tiers_id);
                } else if ($('#souscat').val() == 5) {
                    $('#lcrordre').val(data.ordre);
                    $('#lcrtireur').val(data.nom_tiers);
                    $('#lcrfacture').val(data.num_facture);
                    $('#lcrdate').val(data.date_facture);
                    $('#lcrmontant').val(data.montant);
                } else if ($('#souscat').val() == 7) {
                    $('#rmontant').val(data.montant);
                    $('#rlibelle').val(data.libelle);
                    $('#remet').val(data.nom_tiers);
                    $('#rnumcheque').val(data.num_cheque);
                    $('#rnumremise').val(data.num_remise);
                } else if ($('#souscat').val() == 8) {
                    $('#fdate').val(data.datie);
                    $('#flibelle').val(data.libelle);
                    $('#fht').val(data.montant_ht);
                    $('#ftva').val(data.montant_tva);
                    $('#fttc').val(data.montant);
                    $('#ftaux').val(data.tva_taux_id);
                } else if ($('#souscat').val() == 13) {
                    $('#ticmontant').val(data.montant);
                    $('#ticlibelle').val(data.nom_tiers);
                    $('#ticdate').val(data.datie);
                    $('#ticnum').val(data.num_cb);
                } else if ($('#souscat').val() == 14) {
                    $('#relmontant').val(data.montant);
                    $('#rellibelle').val(data.nom_tiers);
                    $('#reldate').val(data.datie);
                    $('#ticnum').val(data.num_cb);
                }
                $('#lid').val(lid);
            }
		});
		return false;
	});	
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

function initDateScan(){
    var url = Routing.generate('banque_date_scan'),
        did = $('#dossier').val(),
        exercice = $('#exercice').val(),
        souscategorieid = $('#souscat').val(),
        selectedOption = $('#souscat option:selected'),
        soussouscategorieid = selectedOption.attr('data-soussouscategorie-id'),
        soussouscategorieadd = selectedOption.attr('data-soussouscategorie-add');

    if(soussouscategorieid === undefined){
        soussouscategorieid = -1;
    }

    $.ajax({
        url:url,
        type: "GET",
        dataType: "html",
        data: {
            did: did,
            exercice: exercice,
            souscategorieid: souscategorieid,
            soussouscategorieid: soussouscategorieid,
            soussouscategorieadd: soussouscategorieadd
        },
        async: true,
        success: function (data)
        {
            $('#dscan').html(data);
        }
    });
}

function checkVerifTotalInput(jqGrid, userData){

    var totalInput = jqGrid.closest('.ob-container').find('.verif-total'),
        totalVal = totalInput.val().replace(/\s/g, '');

    if(parseFloat(userData) === 0 && parseFloat(totalVal) === 0){
        totalInput.css({borderColor: ''});
        return;
    }

    if(Math.abs(parseFloat(userData) - parseFloat(totalVal)) >= 0.001){
        totalInput.css({borderColor: '#ec4758'});
    }
    else{
        totalInput.css({borderColor: '#1ab394'});
    }
}

function setGridSize(grid){
    var modalBodyHeight = $('#result').height(),
        rows = grid.closest('.ob-container').find('.row'),
        communContaier = $('.commun-container'),
        obContainerHeight = 0,
        communContainerHeight = 0;

    rows.each(function(){

        if(!$(this).hasClass('row-grid')){
            obContainerHeight += $(this).innerHeight();
        }

    });


    communContaier.each(function(){
        communContainerHeight += $(this).innerHeight();
    });


    grid.jqGrid("setGridHeight", modalBodyHeight - obContainerHeight - communContainerHeight - 90);

    grid.jqGrid('setGridWidth', grid.closest('.ob-container').width());
}


function initNumCbCombo(banquecompteid, numcbid, container){

    if(numcbid === undefined){
        numcbid = -1;
    }

    $.ajax({
        url: Routing.generate('banque_liste_num_cb'),
        type: 'GET',
        data: {
            banquecompteid: banquecompteid
        },
        success: function (data) {
            var numcbs = container.find('.numcbs'),
                typecbs = container.find('.typecb');

            numcbs.html(data);
            numcbs.val(numcbid);

            if(numcbid !==-1){
                var typecb = numcbs.find('option:selected').attr('data-type');
                typecbs.val(typecb);
            }
            else{
                typecbs.val(-1);
            }
        }
    });
}