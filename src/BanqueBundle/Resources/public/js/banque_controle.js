var releveGrid = $('#releve-list'),
    controleDoublonGrid = $('#controle-doublon-list'),
    cutoffGrid = $('#cutoff-list'),
    cleGrid = $('#cleob-list'),
    obGrid = $('#ob-list'),
    obSelectedGrid = $('#ob-selected-list');

function montantCellAttr(rowId, val, rawObject, cm, rdata){

    switch (cm.name) {
        case 'c_solde_init_n':
            var soldeinitn = rawObject.c_solde_init_n;

            if (soldeinitn >= 0) {
                return ' style="color:#1ab394"';
            }
            else {
                return ' style="color:#ed5565"';
            }

        case 'c_solde_fin_n':
            var soldefinn = rawObject.c_solde_fin_n;

            if (soldefinn >= 0) {
                return ' style="color:#1ab394"';
            }
            else {
                return ' style="color:#ed5565"';
            }
        case 'c_solde_init_n1':
            var soldeinitn1 = rawObject.c_solde_init_n1;

            if (soldeinitn1 >= 0) {
                return ' style="color:#1ab394"';
            }
            else {
                return ' style="color:#ed5565"';
            }
        case 'c_solde_fin_n1':
            var soldefinn1 = rawObject.c_solde_fin_n1;

            if (soldefinn1 >= 0) {
                return ' style="color:#1ab394"';
            }
            else {
                return ' style="color:#ed5565"';
            }

        default:
            return '';
    }
}

function releveCheckBox(e){
    e.stopPropagation();
    var tr = $('#releve-list').find('tr');

    if($('#releve-check-all').is(':checked')){
        tr.each(function () {
            var chk = $(this).find('input[type="checkbox"]');
            if(!chk.is(':checked')){
                chk.attr('checked', true);
            }

        })
    }
    else{
        tr.each(function () {
            var chk = $(this).find('input[type="checkbox"]');
            if(chk.is(':checked')){
                chk.attr('checked', false);
            }

        })
    }

}

function checkBox(e)
{
    e.stopPropagation();
    var tr = $('#ob-list').find('tr');

    if($('#check-all').is(':checked')){

        tr.each(function(){
            var chk = $(this).find('input[type="checkbox"]');
            if(!(chk.is(':checked'))){
                var rmq = $(this).find('.c_ob_rapprochement').text();
                if(rmq.toLowerCase().indexOf('manqu') >= 0)
                    chk.attr('checked', true);
            }
        });
    }
    else{
        tr.each(function(){
            var chk = $(this).find('input[type="checkbox"]');
            if(chk.is(':checked')){
                chk.attr('checked', false);
            }
        });
    }

}
$(function() {

    cutoffGrid = $('#cutoff-list');
    cutoffGrid.jqGrid({
        datetype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        height: 70,
        hidegrid: false,
        caption: 'CutOff',
        colNames: [
            'Date Initial N', 'Solde Initial N', 'Date Final N', 'Solde Final N', 'Date Initial N+1', 'Solde Initial N+1', 'Date Final N+1', 'Solde Final N+1', 'Action'
        ],
        colModel: [
            {
                name: 'c_date_init_n',
                index: 'c_date_init_n',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}

            },
            {
                name: 'c_solde_init_n',
                index: 'c_solde_init_n',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'number',
                cellattr: montantCellAttr
            },
            {
                name: 'c_date_fin_n',
                index: 'c_date_fin_n',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}
            },
            {
                name: 'c_solde_fin_n',
                index: 'c_solde_fin_n',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'number',
                sorttype: 'number',
                cellattr: montantCellAttr
            },
            {
                name: 'c_date_init_n1',
                index: 'c_date_init_n1',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}
            },
            {
                name: 'c_solde_init_n1',
                index: 'c_solde_init_n1',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'number',
                sorttype: 'number',
                cellattr: montantCellAttr
            },
            {
                name: 'c_date_fin_n1',
                index: 'c_date_fin_n1',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'}
            },
            {
                name: 'c_solde_fin_n1',
                index: 'c_solde_fin_n1',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                formatter: 'number',
                sorttype: 'number',
                cellattr: montantCellAttr
            },
            {
                name: 'c_action',
                index: 'c_action',
                align: 'center',
                editable: true,
                sortable: true,
                width: 100,
                classes: 'js-c-action pointer'
            }
        ],
        loadComplete: function(data){
            cutoffGrid.jqGrid('setCaption', data.caption );
        }
    });

	releveGrid = $('#releve-list');
	var lastsel_rel;
    releveGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        // height: window.innerHeight - 320,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        mtype: 'POST',
        caption: 'Relevés',
        colNames: [
            'Date', 'Compte', 'Libelle', 'Débit', 'Crédit', 'Progression', 'Pièce', 'Tiers','Commentaire', 'Action', '<input type="checkbox" id="releve-check-all" onclick="releveCheckBox(event)">'
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
                classes: 'js-r-date',
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'r-compte',
                index: 'r-compte',
                align: 'center',
                editable: true,
                sortable: true,

                classes: 'js-r-compte',
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('banque_compte_attente'),
                    postData: function (rowid, value, cmName) {
                        return {
                            dossierid: $('#dossier').val()
                        }
                    },
                    dataInit: function (elem) {
                        $(elem).width(120);
                    }
                }
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

                classes: 'js-r-credit',
				formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'r-progression',
                index: 'r-progression',
                align: 'right',
                editable: false,
                sortable: true,

                classes: 'js-r-progression',
				formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'r-piece',
                index: 'r-piece',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-r-piece'
            },
            {
                name: 'r-tiers',
                index: 'r-tiers',
                align: 'center',
                editable: true,
                sortable: true,
                classes: 'js-r-tiers'
            },
            {
                name: 'r-commentaire',
                index: 'r-commentaire',
                align: 'center',
                editable: true,
                sortable: true,

                classes: 'js-r-commentaire'
            },
            {
                name: 'r-action',
                index: 'r-action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                fixed: true,
                classes: 'js-r-action',
                editoptions: {
                	defaultValue: '<i class="fa fa-save icon-action r-action" title="Enregistrer"></i>'
                }
            },
            {
                name: 'r-check',
                index: 'r-check',
                edittype: 'checkbox',
                sortable: false,
                width: 40,
                classes: 'js-r-check',
                align: "center",
                editoptions: { value: "True:False" },
                editrules: { required: true },
                formatter: "checkbox",
                formatoptions: { disabled: false },
                cb: { header: true }
            }

        ],
        onSelectRow: function (id) {
            if (id && id !== lastsel_rel) {
                releveGrid.restoreRow(lastsel_rel);
                lastsel_rel = id;
            }
            releveGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        loadComplete: function (data) {

            var ecart = 0;
            if(data.progressionGenerale){
                var finG = $('#sfing').html(),
                    sgProg = data.progression;
                ecart = Math.abs(parseFloat(sgProg).toFixed(2) - parseFloat(finG).toFixed(2));

                $('#sprog').html(parseFloat(sgProg).toFixed(2));
                $('#secartg').html(ecart.toFixed(2));

                var indicateursG = [$('#ecartg'), $('#procg')];
                if(ecart === 0){
                    $.each(indicateursG, function (index, value) {
                        value.removeClass('panel-danger');
                        value.addClass('panel-primary');
                    });

                }
                else{
                    $.each(indicateursG, function (index, value) {
                        value.removeClass('panel-primary');
                        value.addClass('panel-danger');
                    });
                }

                var ecarts = data.ecarts,
                    qtipecart = $('#qtip-ecart');


                qtipecart.qtip('destroy');

                qtipecart.qtip({
                    content: {
                        text: function(event, api) {
                            var tr = '';
                            ecarts.forEach(function(item) {
                                tr += '<tr>' +
                                            '<td class="col-sm-6">' + item.image + '</td>' +
                                            '<td class="col-sm-6">' + item.ecart + '</td>' +
                                    '</tr>';
                            });

                            var modalbody = '<div class="panel panel-default">' +
                                            '<div class="panel-heading"><h3>Ecarts</h3></div>' +
                                            '<div class="panel-body">' +
                                                '<table class="table">' +
                                                    '<tr>' +
                                                        '<th class="col-sm-6">Image</th>' +
                                                        '<th class="col-sm-6">Ecart</th>' +
                                                    '</tr>';
                            modalbody += tr;
                            modalbody += '</table></div></div>';

                            return modalbody;
                        }
                    },
                    position: {
                        corner: {
                            tooltip: 'bottomMiddle',
                            target: 'topMiddle'
                        }
                    },
                    style: {
                        classes: 'qtip-light qtip-shadow'
                    }
                });


            }
            else{
                var fin = $('#sfin').html(),
                    spro = data.progression;

                ecart = Math.abs(parseFloat(spro).toFixed(2) - parseFloat(fin).toFixed(2));

                $('#spro').html(parseFloat(spro).toFixed(2));
                $('#secart').html(ecart.toFixed(2));

                var indicateurD = [$('#proc'), $('#ecart')];
                if(ecart === 0){
                    $.each(indicateurD, function(index, value){
                        value.removeClass('panel-danger');
                        value.addClass('panel-primary');
                    });

                }
                else{
                    $.each(indicateurD, function(index, value){
                        value.removeClass('panel-primary');
                        value.addClass('panel-danger');
                    });

                }
            }


            if ($('#btn-add-releve').length === 0) {
                releveGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-releve" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }

            if ($('#btn-delete-releve').length === 0){
                releveGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-delete-releve" class="btn btn-danger btn-xs" style="margin-right: 20px;">Supprimer</button></div>');
            }

            initButtons($('#statustemp').val());


            var txcutHeight = $('#txcut').height(),
                modalBodyHeight = $('#mySidenav').height();

            releveGrid.jqGrid("setGridHeight", modalBodyHeight - txcutHeight - 80);

        }
    });

    cleGrid = $('#cleob-list');
    var lastsel_cle;
    cleGrid.jqGrid({
        datetype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        mtype: 'POST',
        height: 'auto',
        hidegrid: false,
        caption: 'Mots Clés',
        colNames: [
            'Mot clés', 'Banque', '' ,'Action'
        ],
        colModel: [

            {
                name: 'c_mot_cle_ob',
                index: 'c_mot_cle_ob',
                align: 'left',
                editable: true,
                sortable: true,
                classes: 'js_c_mot_cle_ob'
            },
            {
                name: 'c_banque',
                index: 'c_banque',
                editable: false,
                sortable: true,
                width: 200,
                edittype: 'checkbox',
                editoptions: { value: "True:False" },
                classes: 'js_c_banque'

            },
            {
                name: 'c_check',
                index: 'c_check',
                editable: false,
                width: 40,
                classes: 'js_check',
                align: "center",
                editoptions: { value: "True:False" },
                editrules: { required: true },
                formatter: "checkbox",
                formatoptions: { disabled: false }
            },
            {
                name: 'c_action_ob',
                index: 'c_action_ob',
                align: 'center',
                editable: false,
                sortable: true,
                width: 70,
                classes: 'pointer',
                editoptions: {defaultValue: '<i class="fa fa-save js_c_action_ob pointer icon-action" title="Enregistrer"></i>' +
                    '<i class="fa fa-trash js_c_remove_ob pointer icon-action" title="Supprimer"></i>'}

            }
        ],
        loadComplete: function (data) {


            if($('#select-banque-ob').length === 0){
                cleGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;padding-right: 10px;">' +
                    '<select id="select-banque-ob"><option value="0">Banque Actuelle</option><option value="1">Toutes les banques</option></select></div>');

            }

            if($('#btn-refresh-cle-ob').length === 0){
                cleGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;padding-right: 10px;">' +
                    '<button id="btn-refresh-cle-ob" class="btn btn-primary btn-xs" style="margin-right: 20px;"><i class="fa fa-refresh"></i></button></div>');
            }

            if ($('#btn-add-cle-ob').length === 0) {
                cleGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-cle-ob" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }

            cleGrid.jqGrid('setGridWidth', cleGrid.closest('.col-lg-6').width() - 30);

            reloadObGrid(data.cles);
        },
        onSelectRow: function (id) {
            if (id && id !== lastsel_cle) {
                cleGrid.restoreRow(lastsel_cle);
                lastsel_cle = id;
            }
            cleGrid.editRow(id, false);
        },

        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);

            var item_action = (target.closest('td').children('.icon-action').length > 0);

            return !item_action;
        },

        ajaxRowOptions: {async: true},
        reloadGridOptions: {fromServer: true}
    });

    obGrid = $('#ob-list');
    obGrid.jqGrid({
        datetype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        mtype: 'POST',
        height: 300,
        hidegrid: false,
        caption: 'Relevés et OB trouvés',
        colNames: [
            'Image', 'Image Id', 'Date', 'Libelle', 'Débit', 'Crédit', 'Remarque', '', '<input type="checkbox" id="check-all" onclick="checkBox(event)">'
        ],
        colModel: [

            {
                name: 'c_ob_image',
                index: 'c_ob_image',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_image pointer'
            },
            {
                name: 'c_image_id',
                index: 'c_image_id',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_image_id',
                hidden: true
            },
            {
                name: 'c_ob_date',
                index: 'c_ob_date',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_ob_date',
                formatter: 'date',
                formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'c_ob_libelle',
                index: 'c_ob_libelle',
                align: 'left',
                editable: false,
                sortable: true,
                classes: 'js_c_ob_libelle'
            },
            {
                name: 'c_ob_debit',
                index: 'c_ob_debit',
                align: 'right',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_ob_debit',
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'c_ob_credit',
                index: 'c_ob_credit',
                align: 'right',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_ob_credit',
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'c_ob_rapprochement',
                index: 'c_ob_rapprochement',
                align: 'left',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'c_ob_rapprochement'
            },
            {
                name: 'c_ob_ids',
                index: 'c_ob_ids',
                align: 'left',
                editable: false,
                sortable: true,
                width: 100,
                hidden: true,
                classes: 'c_ob_ids'
            },
            {
                name: 'c_ob_check',
                index: 'c_ob_check',
                edittype: 'checkbox',
                sortable: false,
                width: 40,
                classes: 'js_ob_check',
                align: "center",
                editoptions: { value: "True:False" },
                editrules: { required: true },
                formatter: "checkbox",
                formatoptions: { disabled: false },
                cb: { header: true }
            }

        ],

        loadComplete: function(){

            if ($('#btn-valider-ob').length === 0) {
                obGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-valider-ob" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Valider</button></div>');
            }

            obGrid.jqGrid('setGridWidth', obGrid.closest('.col-lg-12').width() - 30);

        },

        ajaxRowOptions: {async: true},
        reloadGridOptions: {fromServer: true}
    });

    obSelectedGrid = $('#ob-selected-list');
    obSelectedGrid.jqGrid({
        datetype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: true,
        viewrecords: true,
        mtype: 'POST',
        height: 300,
        hidegrid: false,
        caption: 'OB trouvés',
        colNames: [
           'Image', 'Image Id','Date', 'Libelle', 'Montant', 'Action'
        ],
        colModel: [

            {
                name: 'c_ob_selected_image',
                index: 'c_ob_selected_image',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_image pointer'
            },
            {
                name: 'c_image_id',
                index: 'c_image_id',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_image_id',
                hidden: true
            },
            {
                name: 'c_ob_selected_date',
                index: 'c_ob_selected_date',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js_c_ob_selected_date',
                formatter: 'date',
                formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
            },
            {
                name: 'c_ob_selected_libelle',
                index: 'c_ob_selected_libelle',
                align: 'left',
                editable: false,
                sortable: true,
                classes: 'js_c_ob_selected_libelle'
            },
            {
                name: 'c_ob_selected_montant',
                index: 'c_ob_selected_montant',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'js_c_ob_selected_montant',
                formatter: 'number'
            },
            {
                name: 'c_ob_selected_action',
                index: 'c_ob_selected_action',
                align: 'center',
                editable: false,
                sortable: true,
                classes: 'js_c_ob_selected_action'
            }
        ],
        loadComplete: function(){
            obSelectedGrid.jqGrid('setGridWidth', obSelectedGrid.closest('.modal-body').width() - 40);
        },
        ajaxRowOptions: {async: true},
        reloadGridOptions: {fromServer: true}
    });


    controleDoublonGrid = $('#controle-doublon-list');
    controleDoublonGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        height: 400,
        shrinkToFit: true,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        caption: 'Controle Doublon Relevés',
        colNames: [
            'Image','Date', 'Libelle', 'Débit', 'Crédit', 'Commentaire', 'Action', ''
        ],
        colModel: [

            {
                name: 'dr-image',
                index: 'dr-image',
                align: 'left',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js-dr-image'
            },

            {
                name: 'dr-date',
                index: 'dr-date',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js-dr-date',
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },

            {
                name: 'dr-libelle',
                index: 'dr-libelle',
                align: 'left',
                editable: false,
                sortable: true,
                classes: 'js-dr-libelle'
            },
            {
                name: 'dr-debit',
                index: 'dr-debit',
                align: 'right',
                editable: true,
                sortable: true,
                width: 100,
                classes: 'js-dr-debit',
                formatter: 'number',
                sorttype: 'number'
            },
            {
                name: 'dr-credit',
                index: 'dr-credit',
                align: 'right',
                editable: false,
                sortable: true,
                width: 100,
                classes: 'js-dr-credit',
                formatter: 'number',
                sorttype: 'number'
            },

            {
                name: 'dr_commentaire',
                index: 'dr_commentaire',
                align: 'center',
                editable: true,
                sortable: true,
                width: 80,
                classes: 'js-dr-commentaire'
            },
            {
                name: 'dr-action',
                index: 'dr-action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 100,
                fixed: true,
                classes: 'js-dr-action',
                editoptions: {
                    defaultValue: '<i class="fa fa-trash icon-action dr-action" title="Supprimer"></i>'
                }
            },
            {
                name: 'dr_reference',
                index: 'dr_reference',
                align: 'center',
                editable: false,
                hidden: true,
                classes: 'js-dr-reference'

            }

        ],


        loadComplete: function (data) {

            if ($('#btn-restore-doublon').length === 0) {
                controleDoublonGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').
                after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-restore-doublon" class="btn btn-outline btn-danger btn-xs" style="margin-right: 20px;"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Restaurer les doublons</button></div>');
            }


            controleDoublonGrid.jqGrid('setGridWidth', controleDoublonGrid.closest('.modal-body').width() - 30);
            controleDoublonGrid.jqGrid('setCaption', data.caption );
        }
    });


    $( "#mainside, .forma" ).resizable();
	$('#eremise').hide();

    $('#excel').on('filebatchuploadsuccess', function(event, data, previewId, index) {

        show_info('Import', 'Importation effectuée', 'success');

        var url = Routing.generate('banque_releve_image_details');

        releveGrid.jqGrid('setGridParam', {
                url: url,
                datatype: 'json',
                postData: {
                    image: $('#image').val(),
                    soldedebut: number_fr_to_float($('#sdebut').html()),
                    soldefin: number_fr_to_float($('#sfin').html())
                }
            }
        )
            .trigger('reloadGrid', {fromServer: true, page: 1});
    });

	$('#import').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		var form = $("#myform")[0];

		$.ajax({
		  url: Routing.generate('banque_releve_import'),
		  data: new FormData(form),
		  type:"post",
		  contentType:false,
		  processData:false,
		  cache:false,
		  dataType:"json",
		  success:function(data) {
              show_info('Import', data.message, data.type);

              if(data.type === 'success') {

                  var url = Routing.generate('banque_releve_image_details');

                  releveGrid.jqGrid('setGridParam', {
                          url: url,
                          datatype: 'json',
                          postData: {
                              image: $('#image').val(),
                              soldedebut: number_fr_to_float($('#sdebut').html()),
                              soldefin: number_fr_to_float($('#sfin').html())
                          }
                      }
                  )
                      .trigger('reloadGrid', {fromServer: true, page: 1});
              }
          }
		});
	});

	$('#tajout').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		if ($('#iajout').is(":visible")){
			$('#iajout').hide();
		} else {
			$('#iajout').show();
		}
		choisirform();
	});

    $('#minfos').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var iperdos = $('#iperdos'),
            infoPerdosContainer = iperdos.closest('.ibox.float-e-margins');

        if($(this).hasClass('blink')){
            $(this).removeClass('blink');
        }

        if (infoPerdosContainer.is(":visible")){
            infoPerdosContainer.hide();
            iperdos.hide();

        } else {
            infoPerdosContainer.show();
            iperdos.show();
        }

    });

	$('#closeperdos').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$('#iperdos').hide();
	});	
	$('#mentete').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		if ($('#itete').is(":visible")){
			$('#itete').hide();
		} else {
			$('#itete').show();
		}
	});	
	$('#closetete').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
		$('#itete').hide();
	});	

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
	$('#rmontant').on('keyup', function () {
		var tmp =$('#rmontant').val().replace(/\s/g, '');
		    tmp = tmp.replace(",", ".");
			$('#rmontant').val(tmp);
	});
	$('#ldebit').on('keyup', function () {
		var tmp =$('#ldebit').val().replace(/\s/g, '');
		    tmp = tmp.replace(",", ".");
			$('#ldebit').val(tmp);
	});
	$('#lcredit').on('keyup', function () {
		var tmp =$('#lcredit').val().replace(/\s/g, '');
		    tmp = tmp.replace(",", ".");
			$('#lcredit').val(tmp);
	});
	
	 // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        url = Routing.generate('banque_dossier');
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

    $(document).on('change', '#dossier', function () {
        var urlexercice = Routing.generate('banque_exercice'),
            urlbanque = Routing.generate('banque_liste_banque'),
            dossierid = $(this).val();

        $.ajax({
            url: urlexercice,
            data: {dossierid: dossierid},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#exercice').html(data);
            }
        });

        $.ajax({
            url: urlbanque,
            data: {dossierid: dossierid},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#banque').html(data);
                changeBanque();
            }
        })
    });

    $(document).on('change', '#banque', function(){
        changeBanque();
    });

	// Changement dossier
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
	//terminer image
	$('.btn_terminer').on('click', function (event) {
		event.preventDefault();
		event.stopPropagation();
		var image = $("#image").val();
		$("#c"+image).prop( "checked", true);
		$("#c"+image).closest('td').css("background-color", "#e2efda");
		$("#"+image).closest('td').css("background-color", "#e2efda");
		$.ajax({
			data: {
				image:$('#image').val(),
				souscat:$('#souscat').val()
			},
			url: Routing.generate('valider_ligne_terminer'),
			type: 'POST',
			dataType: 'json',
			success: function (data) {
				suivant(data);
			}	
		});
		return false;
	});	
	//panier
	$('#btn_panier').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $('#pdf').html('');

        $.ajax({
            url:Routing.generate('banque_get_panier'),
            type: "GET",
            dataType: "html",
            async: true,
            data: {
                souscat: $("#souscat").val(),
                etape: 'BQ_DETAILS'
            },
            success: function (data)
            {
                $('#panier-list').html(data);
                updateTooltip();
            }
        });
        return false;
    });


    $(document).on('dblclick', '#panier-list .lot', function () {

        $('#pdf').html('');

        $('.freleve').hide();

        var dossierid = $(this).attr('data-dossier-id'),
            banquecompteid = $(this).attr('data-banque-compte-id'),
            numcompte = $(this).attr('data-num-compte'),
            nomBanque = $(this).attr('data-banque'),
            exercice = $(this).attr('data-exercice'),
            iperdos =  $('#iperdos'),
            infoPerdosContainer = iperdos.closest('.ibox.float-e-margins');

        loadInfoPerdos(iperdos, $('#minfos'), dossierid, exercice);

        $.ajax({
            url: Routing.generate('banque_liste_image_panier'),
            type: 'POST',
            data: {
                dossier: dossierid,
                souscat: 10,
                soussouscat: -1,
                exercice: exercice,
                banquecompteid: banquecompteid,
                etape: 'BQ_DETAILS'
            },
            success: function (data) {

                var controle = $('#controle-list'),
                    modalBody = $('#result'),
                    mySidenav=  $('#mySidenav');


                controle.jqGrid("clearGridData");

                if (data !== '') {
                    mySidenav.html(data);
                    $('.viewer-container').hide();
                    $('#mainside').hide();
                    $('#virement').hide();
                    $('.forme').hide();
                   iperdos.hide();
                   infoPerdosContainer.hide();

                    $('#myModal').modal('show');

                    SetmodalHeight('myModal');

                    $('#lesimages').hide();

                    progressifb();

                    var modalBodyHeight = modalBody.height(),
                        firstImage = $('#allimage tr:first').find('span'),
                        gridWidth=  $('#form-resize').width();

                    clickimage();

                    if(firstImage.length > 0) {
                        firstImage.css("background-color", "#f8ac59");

                        var lastsel_piece = firstImage.attr('id'),
                            height = $(window).height() * 0.95;

                        detailsImage(lastsel_piece, height);
                    }

                    mySidenav.height(modalBodyHeight - 5);

                    $('#sdebutg').html($('#sdebutgtemp').val());
                    $('#sfing').html($('#sfingtemp').val());

                    $('#excel').fileinput({
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
                        uploadUrl: Routing.generate('banque_releve_import')

                    });

                    var exercice = $('#exercicetemp').val(),
                        urlreleve = Routing.generate('banque_releve_dossier_details'),
                        datescan = $('#dscan').val(),
                        editurlreleve = Routing.generate('banque_releve_dossier_details_edit',{imageid: $('#image').val()}),
                        urlcutoff = Routing.generate('banque_cutoff_list', {
                            dossierid: dossierid,
                            exercice: exercice,
                            banquecompteid: banquecompteid
                        });

                    releveGrid.jqGrid('setGridParam', {
                        url: urlreleve,
                        editurl: editurlreleve,
                        datatype: 'json',
                        postData: {
                            banquecompteid:banquecompteid,
                            exercice:exercice,
                            datescan: datescan,
                            soldedebut: $('#sdebutgtemp').val()
                        }

                    })
                        .trigger('reloadGrid', {fromServer: true, page: 1});
                    releveGrid.jqGrid('setGridWidth', ($(window).width() / 2) - 30);


                    if(nomBanque.length > 30){
                        nomBanque = nomBanque.slice(0, 30) + '...';
                    }

                    releveGrid.jqGrid('setCaption', 'Relevés: ' + nomBanque + '  ('+numcompte+')');

                    cutoffGrid.jqGrid('setGridParam', {
                        url: urlcutoff,
                        datatype: 'json'
                    }).trigger('reloadGrid', {fromServer: true, page: 1});

                    cutoffGrid.jqGrid('setGridWidth', ($(window).width() / 2) - 30);

                    initBanquecompteListe(dossierid);

                    var status = $('#statustemp').val();
                    if(status !== ''){
                        status = '<span class="label label-danger">'+status+'</span>';
                    }

                    initButtons(status);
                    $('#js_titre').html(status);



                    releveGrid.jqGrid('setGridWidth', gridWidth);
                    cutoffGrid.jqGrid('setGridWidth', gridWidth);

                    $('#pdf-resize').resizable();

                } else {
                    show_info('Saisie RB', "Aucun résultat", 'warning');
                }
            }
        });

        return false;
    });



    $('#btn_go, #tout').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $('#toutdetails').val('tout');

        var souscat = $('#souscat').val(),
            dossier = $('#dossier').val(),
            dscan = $('#dscan').val(),
            exercice = $('#exercice').val(),
            banquecompte = $('#banquecompte').val(),
            banquecomptetempid = $('#banquecomptetempid'),
            banquecomptetempnum = $('#banquecomptetempnum'),
            banquenomtemp = $('#banquenomtemp'),
            exercicetemp = $('#exercicetemp'),
            dossieridtemp = $('#dossieridtemp');


        if($(this).attr('id') === 'btn_go'){
            banquecomptetempid.val('');
            banquecomptetempnum.val('');
            banquenomtemp.val('');
            exercicetemp.val('');
            dossieridtemp.val('');
        }

        if($(this).attr('id') === 'tout'){

            if(banquecomptetempid.val() !== '' &&  exercicetemp.val() !== '' ){
                dossier = dossieridtemp.val();
                exercice = exercicetemp.val();
                banquecompte = banquecomptetempid.val();
                dscan = 0;
            }
        }

        $('#pdf').html('');

        $('.freleve').hide();

        if(dossier ==='' || exercice === '' || dscan === '' || banquecompte === '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {

            var iperdos = $('#iperdos'),
                infoPerdosContainer = iperdos.closest('.ibox.float-e-margins');
            loadInfoPerdos(iperdos, $('#minfos'), dossier, exercice);


            $.ajax({
                url: Routing.generate('banque_liste_image', {banquecompteid: banquecompte}),
                type: 'POST',
                data: {
                    dossier: dossier,
                    dscan: dscan,
                    souscat: souscat,
                    etape: 'BQ_DETAILS',
                    exercice: exercice
                },
                success: function (data) {

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
                        iperdos.hide();
                        infoPerdosContainer.hide();
                        myModal.modal('show');

                        SetmodalHeight('myModal');


                        $('#lesimages').hide();

                        progressifb();

                        var modalBodyHeight = modalBody.height(),
                            firstImage = $('#allimage tr:first').find('span'),
                            gridWidth=  $('#form-resize').width();

                        clickimage();

                        mySidenav.height(modalBodyHeight - 5);

                        if(firstImage.length > 0) {
                            firstImage.css("background-color", "#f8ac59");

                            var lastsel_piece = firstImage.attr('id'),
                                height = $(window).height() * 0.95;

                            detailsImage(lastsel_piece, height);
                        }

                        $('#sdebutg').html($('#sdebutgtemp').val());
                        $('#sfing').html($('#sfingtemp').val());

                        $('#excel').fileinput({
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
                            uploadUrl: Routing.generate('banque_releve_import')

                        });

                        var urlreleve = Routing.generate('banque_releve_dossier_details'),
                            editurlreleve = Routing.generate('banque_releve_dossier_details_edit',{imageid: $('#image').val()}),
                            urlcutoff = Routing.generate('banque_cutoff_list', {
                                dossierid: dossier,
                                exercice: exercice,
                                banquecompteid: banquecompte
                            });

                        releveGrid.jqGrid('setGridParam', {
                            url: urlreleve,
                            editurl: editurlreleve,
                            datatype: 'json',
                            postData: {
                                banquecompteid:banquecompte,
                                exercice:exercice,
                                soldedebut: $('#sdebutgtemp').val(),
                                datescan: dscan
                            }

                        })
                            .trigger('reloadGrid', {fromServer: true, page: 1});

                        var nomBanque = $('#banque option:selected').text();

                        if(nomBanque.length > 30){
                            nomBanque = nomBanque.slice(0, 30) + '...';
                        }

                        releveGrid.jqGrid('setCaption', 'Relevés: ' + nomBanque + '  ('+$("#banquecompte option:selected").text()+')');

                        cutoffGrid.jqGrid('setGridParam', {
                            url: urlcutoff,
                            datatype: 'json'
                        }).trigger('reloadGrid', {fromServer: true, page: 1});

                        initBanquecompteListe(dossier);

                        var status = $('#statustemp').val();
                        if(status !== ''){
                            status = '<span class="label label-danger">'+status+'</span>';
                        }
                        initButtons(status);
                        $('#js_titre').html(status);

                        mySidenav.height(modalBodyHeight - 5);

                        releveGrid.jqGrid('setGridWidth', gridWidth);
                        cutoffGrid.jqGrid('setGridWidth', gridWidth);

                        $('#pdf-resize').resizable();

                    } else {
                        show_info('Saisie RB', "Aucun résultat", 'warning');
                    }


                }
            });
        }
        return false;
    });

    $(document).on('click', '#btn-delete-releve', function(event){
        event.stopPropagation();
        event.preventDefault();

        var trs = $('#releve-list').find('tr'),
            selectedIds = []
        ;

        trs.each(function(){
            if($(this).attr('id') !== undefined) {
                if ($(this).find('input[type="checkbox"]').is(':checked')) {
                    selectedIds.push($(this).attr('id'));
                }
            }
        });


        swal({
            title: 'Relevé',
            text: "Voulez-vous supprimer ce(s) relevé(s)?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {

                if (selectedIds.length > 0) {
                    $.ajax({
                        url: Routing.generate('banque_releve_delete_multiple'),
                        type: 'POST',
                        data: {
                            selectedids: selectedIds
                        },
                        success: function (data) {
                            show_info('', data.message, data.type);
                            if (data.type === 'success') {
                                $.each(selectedIds,function(k,v){
                                    var trid = releveGrid.find('tr[id="'+v+'"]');
                                    trid.remove();
                                })
                            }
                        }
                    })
                }
                else{
                    show_info('', 'Aucun relevé selectionné', 'warning');
                }
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );


    });

    $(document).on('click', '#btn-doublon', function(event){
       event.preventDefault();
       event.stopPropagation();

        var banquecompte = $('#banquecompte').val(),
            exercice = $('#exercice').val(),
            urlControleDoublon = Routing.generate('banque_controle_releve_doublon', {banquecompteid: banquecompte, exercice: exercice });

        controleDoublonGrid.jqGrid('setGridParam', {
            url: urlControleDoublon,
            datatype: 'json'

        })
            .trigger('reloadGrid', {fromServer: true, page: 1});

        $('#controle-doublon-modal').modal('show');
    });

    $(document).on('click', '#param-num-cb', function(event){
        event.preventDefault();
        event.stopPropagation();

        $('#param-scat').val(1);

        var banqueid = $('#banque').val(),
            souscategorieid = 1,
            banquecompteid = $('#banquecompte').val(),
            infonumcb = $('#info-num-cb');


        infonumcb.closest('.ibox').find('.ibox-title h5').html('Intormation dossier');
        infonumcb.show();

        $('#info-ob').hide();


        $.ajax({
            url: Routing.generate('banque_param_cb_dossier'),
            type: 'GET',
            data: {
                banquecompteid: banquecompteid
            },
            success: function (data) {
                $('#select-cb-bc').val(data);

                if(data !== 0){
                    reloadCbBc();
                    reloadCleGrid(cleGrid,banqueid, souscategorieid);
                }
                else {
                    cleGrid.jqGrid('clearGridData');
                    obGrid.jqGrid('clearGridData');
                }
                $('#param-cb-modal').modal('show');
            }
        });

    });

    $(document).on('click', '#btn-avec-frais', function(event){
        event.preventDefault();
        event.stopPropagation();

        var banquecompteid = $('#banquecompte').val(),
            avecFrais = $('#select-avec-frais').val(),
            souscategorieid = $('#param-scat').val();

        $.ajax({
            url: Routing.generate('banque_param_banque_compte_sc_edit'),
            type: 'POST',
            data: {
                banquecompteid: banquecompteid,
                avecfrais: avecFrais,
                souscategorieid: souscategorieid
            },
            success: function (data) {
                show_info('',data.message, data.type);
                if(data.type === 'success'){
                    $('#btn-refresh-cle-ob').click();
                }
            }
        });
    });

    $(document).on('click', '.param-ob', function(event){
        event.preventDefault();
        event.stopPropagation();

        var banqueid = $('#banque').val(),
            souscategorieid = $(this).attr('data-id'),
            banquecompteid = $('#banquecompte').val(),
            infoob = $('#info-ob');

        $('#info-num-cb').hide();
        infoob.show();

        $.ajax({
            url: Routing.generate('banque_param_avec_frais'),
            data: {
                banquecompteid: banquecompteid,
                souscategorieid: souscategorieid
            },
            type: 'GET',
            success: function (data) {
                infoob.closest('.ibox').find('.ibox-title h5').html(data.title);
                infoob.find('label').html(data.label);

                $('#select-avec-frais').val(data.value);
            }
        });

        reloadCleGrid(cleGrid,banqueid, souscategorieid);

        $('#param-scat').val(souscategorieid);

        $('#param-cb-modal').modal('show');


    });

    $(document).on('click', '.param-valide', function(event){
        event.preventDefault();
        event.stopPropagation();

        $('#rappr-ob-modal').modal('show');

        var id = $(this).closest('tr').attr('id'),
            row = $('#ob-list').jqGrid('getRowData', id);

        $('#ob-selected-id').val(id);

        $('#ob-selected-image').html(row.c_ob_image);
        $('#ob-selected-date').html(row.c_ob_date);
        $('#ob-selected-libelle').html(row.c_ob_libelle);
        $('#ob-selected-debit').html(row.c_ob_debit);
        $('#ob-selected-credit').html(row.c_ob_credit);

        reloadObSelectedGrid(row.c_ob_ids);
    });

    $(document).on('click', '.r-action', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if($('#'+lastsel_rel).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        releveGrid.jqGrid('saveRow', lastsel_rel, {
            "aftersavefunc": function(rowid, response) {

                if(lastsel_rel === 'new_row') {
                    $("#"+lastsel_rel).attr('id',response.responseJSON.id);
                }

                show_info('',response.responseJSON.message, response.responseJSON.type);

            }
        });
    });

    $(document).on('click', '#btn-add-releve', function(event) {
        event.preventDefault();
        event.stopPropagation();


        var canAdd = true;
        var rows = releveGrid.find('tr');

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
            releveGrid.jqGrid('addRow', {
                rowID: "new_row",
                initData: {},
                position: "first",
                useDefValues: true,
                useFormatter: true,
                addRowParams: {}
            });

        }

    });

    $(document).on('click', '.js-c-action[title=CUTOFF]', function(event){
        event.preventDefault();
        event.stopPropagation();
        var tr = $(this).closest('tr'),
            imageid = tr.attr('id'),
            dateInitN = tr.find('td:nth-child(1)').text(),
            soldeInitN = number_fr_to_float(tr.find('td:nth-child(2)').text()),
            dateFinN = tr.find('td:nth-child(3)').text(),
            soldeFinN = number_fr_to_float(tr.find('td:nth-child(4)').text()),
            dateInitN1 = tr.find('td:nth-child(5)').text(),
            soldeInitN1 = number_fr_to_float(tr.find('td:nth-child(6)').text()),
            dateFinN1 = tr.find('td:nth-child(7)').text(),
            soldeFinN1 = number_fr_to_float(tr.find('td:nth-child(8)').text()),
            url = Routing.generate('banque_cut_off');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                imageid: imageid,
                dateInitN: dateInitN,
                dateFinN: dateFinN,
                soldeInitN: soldeInitN,
                soldeFinN: soldeFinN,
                dateInitN1: dateInitN1,
                dateFinN1: dateFinN1,
                soldeInitN1: soldeInitN1,
                soldeFinN1: soldeFinN1
            },
            success: function (data) {
                if(data.type === 'success'){
                    show_info('', 'Cutoff effectué', data.type);

                    var dossier = $('#dossier').val(),
                        exercice = $('#exercice').val(),
                        banquecompte = $('#banquecompte').val(),
                        soldedebut = number_fr_to_float($('#sdebut').html()),
                        soldefin = number_fr_to_float($('#sfin').html()),
                        urlreleve = Routing.generate('banque_releve_image_details'),
                        editurlreleve = Routing.generate('banque_releve_dossier_details_edit',{imageid:imageid}),
                        urlcutoff = Routing.generate('banque_cutoff_list', {
                            dossierid: dossier,
                            exercice: exercice,
                            banquecompteid: banquecompte
                        });


                    releveGrid.jqGrid('setGridParam', {
                        url: urlreleve,
                        editurl: editurlreleve,
                        datatype: 'json',
                        postData: {
                            image: imageid,
                            soldedebut: soldedebut,
                            soldefin: soldefin
                        }

                    })
                        .trigger('reloadGrid', {fromServer: true, page: 1});

                    cutoffGrid.jqGrid('setGridParam', {
                        url: urlcutoff,
                        datatype: 'json'
                    }).trigger('reloadGrid', {fromServer: true, page: 1});


                }
            }
        });
    });

    $(document).on('click', '#btn-add-cle-ob', function(event){
        event.preventDefault();
        event.stopPropagation();

        var canAdd = true;

        var rows = cleGrid.find('tr');
        if($(this).attr('id') === 'btn-add-cle-ob'){
            rows = cleGrid.find('tr');
        }

        rows.each(function () {
            if ($(this).attr('id') === 'new_row') {
                canAdd = false;
            }
        });

        if (canAdd) {
            event.preventDefault();

            cleGrid.jqGrid('addRow', {
                rowID: "new_row",
                initData: {},
                position: "first",
                useDefValues: true,
                useFormatter: true,
                addRowParams: {}
            });

        }

    });

    $(document).on('click', '.js_c_action_ob', function(event){
        event.preventDefault();
        event.stopPropagation();
        var tr = $(this).closest('tr'),
            cleid = tr.attr('id'),
            banqueid = $('#banque').val(),
            souscategorieid = $('#param-scat').val(),
            cle= tr.find('td:first').find('input').val();

        if(cle === '')
        {
            show_info('', 'la clé ne peut être vide', 'error');
            return;
        }

        if($('#'+cleid).attr('editable') !== '1') {
            show_info('', 'Attention, ligne non editable', 'warning');
            return;
        }

        $.ajax({
            url: Routing.generate('banque_param_cle_edit'),
            data: {
                cleid: cleid,
                banqueid: banqueid,
                souscategorieid: souscategorieid,
                cle: cle
            },
            type: 'POST',
            success: function (data) {
                if(data.type === 'success') {
                    reloadCleGrid(cleGrid, $('#banque').val(), souscategorieid);
                    $('#select-banque-ob').val(0);
                }
            }
        })

    });

    $(document).on('click', '.js_c_remove_ob', function(event){
        event.stopPropagation();
        event.preventDefault();
        var rowid = $(this).closest('tr').attr('id');


        if(rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        cleGrid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('banque_param_cle_remove'),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer ce mot clé?'
        });

    });

    $(document).on('change', '#select-banque-ob', function(){
        var banqueid = $('#banque').val(),
            tous = 0;

        if(parseInt($(this).val()) === 1){
            tous = 1;
        }

        souscategorieid = $('#param-scat').val();
        reloadCleGrid(cleGrid, banqueid, souscategorieid, tous);


    });

    $(document).on('click', '#btn-refresh-cle-ob', function(event){
        event.preventDefault();
        event.stopPropagation();
        var ids = '';
        cleGrid.find('tr').each(function(){
            if($(this).find('.js_check input:checked').length > 0){
                if(ids === ''){
                    ids += $(this).attr('id');
                }
                else{
                    ids += ',' + $(this).attr('id');
                }
            }
        });

        if(ids !== '')
            reloadObGrid(ids);
        else
            obGrid.jqGrid("clearGridData");
    });

    $(document).on('click', '.js_c_add_key_ob', function(event){
        event.preventDefault();
        event.stopPropagation();
        var tr = $(this).closest('tr'),
            banqueid = $('#banque').val(),
            souscategorieid = $('#param-scat').val(),
            cle = tr.find('td:first').find('input').val();

        if(cle === undefined){
            cle = tr.find('td:first').html();
        }

        if(cle !== '') {
            swal({
                title: 'Clé',
                text: "Voulez-vous enregistrer la clé pour cette banque?",
                type: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Non'
            }).then(function () {

                    $.ajax({
                        url: Routing.generate('banque_param_cle_edit'),
                        data: {
                            cleid: 'new_row',
                            banqueid: banqueid,
                            souscategorieid: souscategorieid,
                            cle: cle
                        },
                        type: 'POST',
                        success: function (data) {

                            if(data.type === 'success') {
                                reloadCleGrid(cleGrid, $('#banque').val(), souscategorieid);
                                $('#select-banque-ob').val(0);
                            }

                        }
                    })
                },
                function (dismiss) {
                    if (dismiss === 'cancel') {

                    } else {
                        throw dismiss;
                    }
                }
            );
        }



    });

    $(document).on('click', '.js_c_image', function(event) {
        event.preventDefault();
        event.stopPropagation();

       show_image_pop_up(
           $(this).closest('table').
           jqGrid('getRowData', $(this).closest('tr').attr('id')).c_image_id
       );

    });

    $(document).on('mouseup', '.js_c_ob_libelle', function(event){
        event.preventDefault();
        event.stopPropagation();

        if(parseInt($('#param-scat').val()) !== 1){
            return false;
        }

        var numcb = '';
        if (window.getSelection) {
            numcb = window.getSelection().toString();
        }


        if(numcb !== '') {
            swal({
                title: 'Carte Bleu',
                text: "Voulez vous enregistrer ce numero de carte bleu?",
                type: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Non'
            }).then(function () {

                var url =  Routing.generate('banque_param_add_cb_bc');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        numcb: numcb,
                        banquecompteid: $('#banquecompte').val()
                    },
                    success: function(data){
                        show_info('', data.message, data.type);
                        if(data.type === 'success')
                            reloadCbBc();
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
        }

    });

    $(document).on('click', '.delete-cb-bc', function(event){
        event.preventDefault();
        event.stopPropagation();

        var item = $(this).closest('.list-group-item'),
            id = item.attr('data-id');


        swal({
            title: 'Carte Bleu',
            text: "Voulez vous supprimer ce numero de carte bleu?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {

                $.ajax({
                    url: Routing.generate('banque_param_delete_bc_cb'),
                    data: {id: id},
                    type: 'DELETE',
                    success: function(data){
                        if(data.type === 'success'){
                            item.remove();
                            $('#btn-refresh-cle-ob').click();
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

    $(document).on('click', '.edit-cb-bc', function(event){
        event.preventDefault();
        event.stopPropagation();

        var cbbcid = $(this).closest('li').attr('data-id'),
            typerecherche = $(this).closest('li').find('.select-cb-bc-type').val();
        $.ajax({
            url: Routing.generate('banque_param_edit_cb_bc'),
            type: 'POST',
            data: {
                cbbcid: cbbcid,
                typerecherche: typerecherche
            },
            success: function(data){
                show_info('', data.message, data.type);
                if(data.type === 'success'){
                    $('#btn-refresh-cle-ob').click();
                }
            }
        });
    });

    $(document).on('click', '#btn-cb-bc', function(event){
        event.preventDefault();
        event.stopPropagation();

        var banquecompteid = $('#banquecompte').val(),
            banqueid = $('#banque').val(),
            aveccb = $('#select-cb-bc').val();
        $.ajax({
            url: Routing.generate('banque_param_edit_cb_dossier'),
            type: 'POST',
            data: {
                banquecompteid:banquecompteid,
                aveccb: aveccb
            },
            success: function(data){
                show_info('',data.message, data.type);

                if(data.type === 'success'){
                    if(data.recharger === 'true'){
                        reloadCbBc();
                    }
                }

                if(parseInt(aveccb) !== 0){
                    reloadCleGrid(cleGrid, banqueid, $('#param-scat').val());
                }
                else{
                    cleGrid.jqGrid('clearGridData');
                    obGrid.jqGrid('clearGridData');
                }
            }
        })
    });

    $(document).on('click', '.js_c_ob_selected_action', function(event){

        event.preventDefault();
        event.stopPropagation();

        var releveid = $('#ob-selected-id').val(),
            obid = $(this).closest('tr').attr('id');

        $.ajax({
           url: Routing.generate('banque_param_ob_flague'),
           type: 'POST',
           data: {
               releveid: releveid,
               obid: obid
           },
           success: function(data) {
               show_info('', data.message, data.type);
               $('#ob-list tr#' + releveid).
               find('.c_ob_rapprochement').
               html('<span class="text-success js_c_ob_deselect_action pointer">Pièce validée</span>');
               $('#rappr-ob-modal').modal('hide');
           }
        });
    });

    $(document).on('click', '.js_c_ob_deselect_action', function(event){
        event.stopPropagation();
        event.preventDefault();

        var releveid = $(this).closest('tr').attr('id');

        swal({
            title: '',
            text: "Voulez-vous dévalider cette pièce?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {

                $.ajax({
                    url: Routing.generate('banque_param_ob_deflague'),
                    type: 'POST',
                    data: {
                        releveid: releveid,
                        souscategorieid : $('#param-scat').val()
                    },
                    success: function (data) {
                        show_info('',data.message, data.type);

                        if(data.type === 'success') {
                            reloadObGrid(data.cles);
                        }
                    }
                })
            },
            function (dismiss) {
                if (dismiss === 'cancel') {

                } else {
                    throw dismiss;
                }
            }
        );

    });

    $(document).on('click', '#btn-valider-ob', function(event){
        event.stopPropagation();
        event.preventDefault();

        var trs = $('#ob-list').find('tr'),
            selectedIds = [],
            notselectedIds = []
        ;

        trs.each(function(){
            if($(this).attr('id') !== undefined) {
                if ($(this).find('input[type="checkbox"]').is(':checked')) {
                    selectedIds.push($(this).attr('id'));
                }
                else{
                    notselectedIds.push($(this).attr('id'));
                }
            }
        });

        $.ajax({
            url: Routing.generate('banque_param_ob_manquant_edit'),
            type: 'POST',
            data: { selectedids: selectedIds,
                notselectedids: notselectedIds
            },
            success: function(data){
                show_info('', data.message, data.type);
            }
        })
    });

    $(document).on('mouseover', '#controle-doublon-list tr', function () {
        var tr = $(this),
            id = tr.attr('id'),
            gridRow = controleDoublonGrid.jqGrid('getRowData', id),
            commentaire = gridRow['dr_commentaire'],
            reference = gridRow['dr_reference']
           ;

        if(commentaire === ''){
            return;
        }

        if(reference !== ''){
            tr.closest('table').find('tr[id='+id+']').css("background-color", "#e2efda");
            tr.closest('table').find('tr[id='+reference+']').css("background-color", "#e2efda");
        }
    });

    $(document).on('mouseout', '#controle-doublon-list tr', function () {
        var tr = $(this),
            id = tr.attr('id'),
            gridRow = controleDoublonGrid.jqGrid('getRowData', id),
            commentaire = gridRow['dr_commentaire'],
            reference = gridRow['dr_reference']
        ;

        if(commentaire === ''){
            return;
        }

        if(reference !== ''){
            tr.closest('table').find('tr[id='+id+']').css("background-color", "");
            tr.closest('table').find('tr[id='+reference+']').css("background-color", "");
        }


    });

    $(document).on('click', '.js-dr-action', function(event){
       event.preventDefault();
       event.stopPropagation();

       var id = $(this).closest('tr').attr('id');

        swal({
            title: 'Attention',
            text: "Voulez vous supprimer cette ligne de relevé?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
                var url = Routing.generate('banque_releve_delete');
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {id: id},
                    success: function (data) {
                        show_info('', data.message, data.type);

                        if(data.type === 'success') {


                            //esorina ny doublon & reference
                            var gridRow = controleDoublonGrid.jqGrid('getRowData', id),
                                ref = gridRow['dr_reference'],
                                gridRowRef = controleDoublonGrid.jqGrid('getRowData', ref);

                            gridRowRef['dr_reference'] = '';
                            gridRowRef['dr_commentaire'] = '';

                            controleDoublonGrid.jqGrid('setRowData', ref, gridRowRef);

                            controleDoublonGrid.find('tr[id="'+id+'"]').remove();
                            var toutdetails = $('#toutdetails').val();

                            if(toutdetails === 'tout'){


                                var banquecompte = $('#banquecompte').val(),
                                    exercice = $('#exercice').val(),
                                    datescan = $('#dscan').val(),
                                    urlreleve = Routing.generate('banque_releve_dossier_details'),
                                    editurlreleve = Routing.generate('banque_releve_dossier_details_edit',{imageid: $('#image').val()});

                                releveGrid.jqGrid('setGridParam', {
                                    url: urlreleve,
                                    editurl: editurlreleve,
                                    datatype: 'json',
                                    postData: {
                                        banquecompteid:banquecompte,
                                        exercice:exercice,
                                        soldedebut: $('#sdebutgtemp').val(),
                                        datescan: datescan
                                    }

                                })
                                    .trigger('reloadGrid', {fromServer: true, page: 1});


                            }
                            else if(toutdetails === 'details'){

                                var imgid = $('#currentimage').val(),
                                    soldedebut = number_fr_to_float($('#sdebut').html()),
                                    soldefin = number_fr_to_float($('#sfin').html()),
                                    urlreleve = Routing.generate('banque_releve_image_details'),
                                    editurlreleve = Routing.generate('banque_releve_dossier_details_edit', {imageid: imgid});

                                releveGrid.jqGrid('setGridParam', {
                                    url: urlreleve,
                                    editurl: editurlreleve,
                                    datatype: 'json',
                                    postData: {
                                        image: imgid,
                                        soldedebut: soldedebut,
                                        soldefin: soldefin
                                    }

                                })
                                    .trigger('reloadGrid', {fromServer: true, page: 1});

                            }

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

    $(document).on('click', '#btn-restore-doublon', function(event){
        event.preventDefault();
        event.stopPropagation();

        swal({
            title: 'Attention',
            text: "Voulez vous restaurer les doublons?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {
                var url = Routing.generate('banque_releve_restore'),
                    banquecompte = $('#banquecompte').val(),
                    exercice = $('#exercice').val();
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        banquecompteid: banquecompte,
                        exercice: exercice
                    },
                    success: function (data) {
                        show_info('', data.message, data.type);

                        if(data.type === 'success') {

                            var urlControleDoublon = Routing.generate('banque_controle_releve_doublon', {banquecompteid: banquecompte, exercice: exercice });

                            controleDoublonGrid.jqGrid('setGridParam', {
                                url: urlControleDoublon,
                                datatype: 'json'

                            })
                                .trigger('reloadGrid', {fromServer: true, page: 1});



                            var toutdetails = $('#toutdetails').val();

                            if(toutdetails === 'tout'){


                                var urlreleve = Routing.generate('banque_releve_dossier_details'),
                                    datescan = $('#dscan').val(),
                                    editurlreleve = Routing.generate('banque_releve_dossier_details_edit',{imageid: $('#image').val()});

                                releveGrid.jqGrid('setGridParam', {
                                    url: urlreleve,
                                    editurl: editurlreleve,
                                    datatype: 'json',
                                    postData: {
                                        banquecompteid:banquecompte,
                                        exercice:exercice,
                                        soldedebut: $('#sdebutgtemp').val(),
                                        datescan: datescan
                                    }

                                })
                                    .trigger('reloadGrid', {fromServer: true, page: 1});


                            }
                            else if(toutdetails === 'details'){

                                var imgid = $('#currentimage').val(),
                                    soldedebut = number_fr_to_float($('#sdebut').html()),
                                    soldefin = number_fr_to_float($('#sfin').html()),
                                    urlreleve = Routing.generate('banque_releve_image_details'),
                                    editurlreleve = Routing.generate('banque_releve_dossier_details_edit', {imageid: imgid});

                                releveGrid.jqGrid('setGridParam', {
                                    url: urlreleve,
                                    editurl: editurlreleve,
                                    datatype: 'json',
                                    postData: {
                                        image: imgid,
                                        soldedebut: soldedebut,
                                        soldefin: soldefin
                                    }

                                })
                                    .trigger('reloadGrid', {fromServer: true, page: 1});

                            }

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

	$(document).on('change', '#ftaux', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var tva = $('#fht').val() * $( "#ftaux option:selected" ).text() / 100,
			ttc = tva + parseFloat($('#fht').val());
		$('#ftva').val(tva.toFixed(2));
		$('#fttc').val(ttc.toFixed(2));
    });
    $('#js_debut_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#js_fin_bq_date').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#ldate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#rdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#adate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#lcrdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#dateregl').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
    $('#dateech').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#datef').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});
	$('#fdate').datepicker({format:'dd/mm/yyyy', language: 'fr', autoclose:true, startView: 1});


});






function changeBanque(){
    var url = Routing.generate('banque_liste_banque_compte'),
        banqueid = $('#banque').val(),
        dossierid = $('#dossier').val();
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        data: {
            banqueid: banqueid,
            dossierid: dossierid
        },
        success: function (data) {
            $('#banquecompte').html(data);
        }

    });
}


//image suivant
function suivant(imm){
        var lastsel_piece = imm;
			$('.js_imgbq_selected').each(function() {
				$(this).css("background-color", "transparent");
			});	
			suiv = $('#imagesuiv').val();
		if (!(suiv.length >0)){
			var reste = true;
			$('.js_imgbq_selected').each(function() {
				if ($(this).attr("data-id")==1){
					$(this).css("background-color", "#f8ac59");
					suiv = $(this).attr('id');
					reste = false;
					return false;
				}
			});
			if (reste){
				suiv = image;
				$('#mainside').show();
				return false;
			}
		}

        var height = $(window).height() * 0.95;
		var suivo = $('#'+suiv).closest('tr').next().find('span').attr('id');
		$('#imagesuiv').val(suivo);
		$('#'+suiv).css("background-color", "#f8ac59");

		detailsImage(suiv, height);


}
//saisie terminee image suivante 
function clickimage() {
    $('.js_imgbq_selected').on('click', function () {
        if ($(this).attr('data-id') == 0) {
            show_info('Image', "Entête de l'image pas encore saisie, veuillez choisir une autre", 'warning');
            return false;
        }
        var lastsel_piece = $(this).closest('span').attr('id');
        $('.js_imgbq_selected').each(function () {
            $(this).css("background-color", "transparent");
        });
        $(this).closest('span').css("background-color", "#f8ac59");
        $('#imagesuiv').val($(this).closest('tr').next().find('span').attr('id'));
        var height = $(window).height() * 0.95;

        detailsImage(lastsel_piece, height);

        choisirform();
    });
}
function choisirform(){
	$('.freleve').hide();
	$('.fremise').hide();
	$('.flcr').hide();
	$('.fautre').hide();
	$('.ffrais').hide();
	
	if ($('#souscat').val()==5){
		$('.flcr').show();
	} else if ($('#souscat').val()==7){
		$('.fremise').show();
	} else if ($('#souscat').val()==10){
		$('.freleve').show();
	} else if ($('#souscat').val()==8){
		$('.ffrais').show();
	} else {
		$('.fautre').show();	
	}	
}	


function vider(){
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
		initializeCommun();
}

function supprimer(){
	$('.sligne').on('click', function (event) {
		event.preventDefault();
		event.stopPropagation();
		$.ajax({
			data: {
				lid: $(this).attr('data-id'),
				image: $('#image').val(),
				souscat:$('#souscat').val()
			},
			url: Routing.generate('supprimer_ligne_banque'),
			type: 'POST',
			dataType: 'json',
			success: function (data) {
				if ($('#souscat').val()==10){
					$('#ligneb').html(data);
					progressif();
				} else if($('#souscat').val()==5) {
					$('#lignelcr').html(data);
					totall();
				} else if($('#souscat').val()==7) {
					$('#ligner').html(data);
					totalr();
				} else if($('#souscat').val()==8) {	
					$('#lignef').html(data);
				} else {
					$('#lignea').html(data);
				}
				supprimer();
				editer();
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
				if ($('#souscat').val()==10){
					$('#ldate').val(data.date);
					$('#llibelle').val(data.libelle);
					$('#ldebit').val(mi(data.debit));
					$('#lcredit').val(mi(data.credit));
					$('#lcommentaire').val(data.commentaire);
					$('#lid').val(lid);
				} else {
					$('#rdate').val(data.date);
					$('#rbenef').val(data.nom_tiers);
					$('#rlibelle').val(data.libelle);
					$('#rncheque').val(data.num_cheque);
					$('#rmontant').val(mi(data.montant));	
					$('#lid').val(lid);
				}
			}	
		});
		return false;
	});	
}
function totalr(){
	var totalv =parseFloat($('#totalecheque').val());
	var totalc = 0;
	$('#ligner tr').each(function() {
		var montant = parseFloat(this.cells[6].innerHTML);
		totalc = totalc + montant;
		this.cells[6].innerHTML = montant.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
	});
	var ecart = totalv - totalc;
	$('#secartr').html(ecart.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	$('.totals').html(totalc.toFixed(2));
	if (totalc.toFixed(2) == totalv) {
		$('#ctotal').removeClass('panel-danger');
		$('#ctotal').addClass('panel-primary');
		$('#ecartr').removeClass('panel-danger');
		$('#ecartr').addClass('panel-primary');
	}else{
		$('#ctotal').addClass('panel-danger');
		$('#ctotal').removeClass('panel-primary');
		$('#ecartr').addClass('panel-danger');
		$('#ecartr').removeClass('panel-primary');
	}
}
function totall(){
	var totalv =parseFloat($('#totallcr').val());
	var totalc = 0;
	$('#lignelcr tr').each(function() {
		var montant = parseFloat(this.cells[3].innerHTML);
		totalc = totalc + montant;
		this.cells[3].innerHTML = montant.toFixed(2);
	});
	var ecart = totalv - totalc;
	$('#lecartr').html(ecart.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	$('.totalll').html(totalc.toFixed(2));
	if (totalc.toFixed(2) == totalv) {
		$('#ltotal').removeClass('panel-danger');
		$('#ltotal').addClass('panel-primary');
		$('#ecartl').removeClass('panel-danger');
		$('#ecartl').addClass('panel-primary');
	}else{
		$('#ltotal').addClass('panel-danger');
		$('#ltotal').removeClass('panel-primary');
		$('#ecartl').addClass('panel-danger');
		$('#ecartl').removeClass('panel-primary');
	}
}
function progressif(){
	var thHeight = $("table#demo-table th:first").height();
	  $("table#demo-table th").resizable({
		  handles: "e",
		  minHeight: thHeight,
		  maxHeight: thHeight,
		  minWidth: 40,
		  resize: function (event, ui) {
			var sizerID = "#" + $(event.target).attr("id") + "-sizer";
			$(sizerID).width(ui.size.width);
		  }
	  });
	// var solde =parseFloat(ne($('#sdebut').html().replace(/\s/g, '')));
	// var controle = parseFloat(ne($('#sfin').html().replace(/\s/g, '')));
	// $('#sdebut').html(solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// $('#sfin').html(controle.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// $('#ligneb tr').each(function() {
	// 	var debit = parseFloat(ne(this.cells[4].innerHTML.replace(/\s/g, '')));
	// 	var credit = parseFloat(ne(this.cells[5].innerHTML.replace(/\s/g, '')));
	// 	solde = solde -  debit + credit;
	// 	debit = debit.toFixed(2);
	// 	credit = credit.toFixed(2);
	// 	var daffich = this.cells[4].innerHTML.replace(/<span>.{2,}<\/span>/g,'<span>'+debit.replace(/(\d)(?=(\d{3})+\b)/g,'$1 ')+'</span>');
	// 	var caffich = this.cells[5].innerHTML.replace(/<span>.{2,}<\/span>/g,'<span>'+credit.replace(/(\d)(?=(\d{3})+\b)/g,'$1 ')+'</span>');
	// 	this.cells[4].innerHTML= daffich;
	// 	this.cells[5].innerHTML= caffich;
	// 	this.cells[6].innerHTML= solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
    //
	// });
	// if (solde>0){
	// 	$('#sllib').html('[Crédit]');
	// } else {
	// 	$('#sllib').html('[Débit]');
	// }
	// $('#spro').html(solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// var ecart = controle - solde;
	// 	ecart = ecart.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
	// 	ecart = ecart.replace("-","");
	// $('#secart').html(ecart);
	// if (solde.toFixed(2) == controle) {
	// 	$('#proc').removeClass('panel-danger');
	// 	$('#proc').addClass('panel-info');
	// 	$('#ecart').removeClass('panel-danger');
	// 	$('#ecart').addClass('panel-info');
	// }else{
	// 	$('#proc').addClass('panel-danger');
	// 	$('#proc').removeClass('panel-info');
	// 	$('#ecart').addClass('panel-danger');
	// 	$('#ecart').removeClass('panel-info');
	// }
}
function progressifb(){
	var thHeight = $("table#demo-table th:first").height();
	  $("table#demo-table th").resizable({
		  handles: "e",
		  minHeight: thHeight,
		  maxHeight: thHeight,
		  minWidth: 40,
		  resize: function (event, ui) {
			var sizerID = "#" + $(event.target).attr("id") + "-sizer";
			$(sizerID).width(ui.size.width);
		  }
	  });
	// var solde =parseFloat(ne($('#sdebutg').html().replace(/\s/g, '')));
	// var controle = parseFloat(ne($('#sfing').html().replace(/\s/g, '')));
	// $('#sdebutg').html(solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// $('#sfing').html(controle.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// $('#ligneb tr').each(function() {
	// 	var debit = parseFloat(ne(this.cells[4].innerHTML.replace(/\s/g, '')));
	// 	var credit = parseFloat(ne(this.cells[5].innerHTML.replace(/\s/g, '')));
	// 	solde = solde -  debit + credit;
	// 	debit = debit.toFixed(2);
	// 	credit = credit.toFixed(2);
	// 	var daffich = this.cells[4].innerHTML.replace(/<span>.{2,}<\/span>/g,'<span>'+debit.replace(/(\d)(?=(\d{3})+\b)/g,'$1 ')+'</span>');
	// 	var caffich = this.cells[5].innerHTML.replace(/<span>.{2,}<\/span>/g,'<span>'+credit.replace(/(\d)(?=(\d{3})+\b)/g,'$1 ')+'</span>');
	// 	this.cells[4].innerHTML= daffich;
	// 	this.cells[5].innerHTML= caffich;
	// 	this.cells[6].innerHTML= solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
    //
	// });
	// if (solde>0){
	// 	$('#sllib').html('[Crédit]');
	// } else {
	// 	$('#sllib').html('[Débit]');
	// }
	// $('#sprog').html(solde.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 '));
	// var ecart = controle - solde;
	//     ecart = ecart.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');
	// 	ecart = ecart.replace("-","");
	// $('#secartg').html(ecart);
	// if (solde.toFixed(2) == controle) {
	// 	$('#procg').removeClass('panel-danger');
	// 	$('#procg').addClass('panel-info');
	// 	$('#ecartg').removeClass('panel-danger');
	// 	$('#ecartg').addClass('panel-info');
	// }else{
	// 	$('#procg').addClass('panel-danger');
	// 	$('#procg').removeClass('panel-info');
	// 	$('#ecartg').addClass('panel-danger');
	// 	$('#ecartg').removeClass('panel-info');
	// }
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
function ne(str){
	str = str.replace(/<span>/g, '');
	return str.replace(/\<\/span.{2,}/g, '');
}

function detailsImage(imgid, height) {
    $.ajax({
        data: {
            imgid: imgid,
            souscat: $("#souscat").val(),
            height: height
        },
        url: Routing.generate('data_banque_saisie'),
        type: 'POST',
        dataType: 'json',
        success: function (data) {

            $('#toutdetails').val('details');
            $('#currentimage').val(imgid);

            vider();
            choisirtraitement(data.dossier);
            $("#informations").show();
            $(".freleve").show();


            $("#generale").html(data.generale);
            $("#mandataire").html(data.mandataire);
            $("#comptable").html(data.comptable);
            $("#fiscale").html(data.fiscale);
            $("#isaisie").html(data.isaisie);
            $("#idossier").html(data.idossier);
            $("#dossier").val(data.dossier);
            $("#dossierpanier").val(data.dossier);
            $("#image").val(imgid);
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

            var modalBody = $('#myModal').find('.modal-body.p-4'),
                modalBodyHeight = modalBody.height();
            $('#pdf').height(modalBodyHeight - 5);

            $("#js_num_compt_bq").val(data.numc);

            $('#js_num_releve_bq').val(data.num_releve);
            $('#js_debut_bq_date').val(data.debut_periode);
            $('#js_debut_bq_debi').val(mi(data.ddebit));
            $('#js_debut_bq_cred').val(mi(data.dcredit));
            $('#js_debut_page').val(data.page_solde_debut);
            $('#js_fin_bq_date').val(data.fin_periode);
            $('#js_fin_bq_debi').val(mi(data.fdebit));
            $('#js_fin_bq_cred').val(mi(data.fcredit));
            $('#js_fin_page').val(data.page_solde_fin);



            if ((data.dcredit - data.ddebit) > 0) {
                $('#sdebut').html(data.dcredit - data.ddebit);
                $('#ssdebut').html(data.dcredit - data.ddebit);
                $('#sdlib').html('[Crédit]');
            } else {
                $('#sdebut').html(data.dcredit - data.ddebit);
                $('#ssdebut').html(data.ddebit - data.dcredit);
                $('#sdlib').html('[Débit]');
            }
            if ((data.fcredit - data.fdebit) > 0) {
                $('#sfin').html(data.fcredit - data.fdebit);
                $('#sflib').html('[Crédit]');
            } else {
                $('#sfin').html(data.fcredit - data.fdebit);
                $('#sflib').html('[Débit]');
            }
            $("#ligneb").html(data.lignes);
            progressif();

            $('#mainside').show();
            $('#locr').val(data.ocr);
            supprimer();
            editer();
            $('#lid').val(0);
            $('#imid').val(data.imid);

            var soldedebut = number_fr_to_float($('#sdebut').html()),
                soldefin = number_fr_to_float($('#sfin').html()),
                urlreleve = Routing.generate('banque_releve_image_details'),
                editurlreleve = Routing.generate('banque_releve_dossier_details_edit', {imageid: imgid});

            releveGrid.jqGrid('setGridParam', {
                url: urlreleve,
                editurl: editurlreleve,
                datatype: 'json',
                postData:{
                    image: imgid,
                    soldedebut: soldedebut,
                    soldefin: soldefin
                }

            })
                .trigger('reloadGrid', {fromServer: true, page: 1});

            return false;
        }
    });
}

function reloadCbBc() {
    var banquecompteid =  $('#banquecompte').val();
    $.ajax({
        url: Routing.generate('banque_param_list_bc_cb'),
        data: {banquecompteid: banquecompteid},
        type: 'GET',
        success: function (data) {
            $('#cb-bc-list').html(data);
        }
    });
}


function reloadCleGrid(grid,banqueid, souscategorieid, tous){

    if(tous === undefined){
        tous = 0;
    }

    var urlrb = Routing.generate('banque_param_cle_list'),
        urlEditRb = Routing.generate('banque_param_cle_edit');

    grid.jqGrid('setGridParam', {
            url: urlrb,
            postData: {
                banqueid: function(){return banqueid;},
                souscategorieid: function(){return souscategorieid;},
                tous: function () {return tous;}
            },
            editurl: urlEditRb,
            datatype: 'json'
        }
    ).trigger('reloadGrid', {fromServer: true, page: 1});
}



function reloadObGrid(cles){
    var url = Routing.generate('banque_param_search_ob'),
        banquecompteid = $('#banquecompte').val(),
        exercice = $('#exercice').val(),
        souscategorieid = $('#param-scat').val();

    obGrid.jqGrid('setGridParam', {
            url: url,
            postData: {
                banquecompteid: function(){return banquecompteid;},
                exercice: function(){return exercice;},
                souscategorieid: function(){return souscategorieid;},
                cles: function(){return cles;}
            },
            datatype: 'json'
        }
    ).trigger('reloadGrid', {fromServer: true, page: 1});

    // obGrid.jqGrid('setCaption', 'Relevés: ' + $('#banque option:selected').text());

    var nomBanque = $('#banque option:selected').text();

    if(nomBanque.length > 30){
        nomBanque = nomBanque.slice(0, 30) + '...';
    }

    obGrid.jqGrid('setCaption', 'Relevés: ' + nomBanque + '  ('+$("#banquecompte option:selected").text()+')');

}

function reloadObSelectedGrid(ids) {
    var url = Routing.generate('banque_param_ob_selected');
    obSelectedGrid.jqGrid('setGridParam', {
            url: url,
            postData: {
                ids: function () {
                    return ids;
                }
            },
            datatype: 'json'
        }
    ).trigger('reloadGrid', {fromServer: true, page: 1});
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
                    datescan = moment($(this).attr('data-datescan')).format('DD/MM/Y'),
                    tache = $(this).attr('data-tache'),
                    banque = $(this).attr('data-banque'),
                    numcompte = $(this).attr('data-num-compte');


                var modalbody = '<table class="table">';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Client</th><td class="col-sm-9">' + client + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Site</th><td class="col-sm-9">' + site + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Dossier</th><td class="col-sm-9">' + dossier + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Banque</th><td class="col-sm-9">' + banque + '</td></tr>';
                modalbody += '<tr><th class="col-sm-3" style="text-align: left !important;">Num Compte</th><td class="col-sm-9">' + numcompte + '</td></tr>';
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


function reloadReleveCutOffGrid(){
    var dossier = $('#dossier').val(),
        exercice = $('#exercice').val(),
        banquecompte = $('#banquecompte').val(),
        soldedebut = number_fr_to_float($('#sdebut').html()),
        soldefin = number_fr_to_float($('#sfin').html()),
        urlreleve = Routing.generate('banque_releve_image_details'),
        editurlreleve = Routing.generate('banque_controle_releve_delete',{imageid:imageid}),
        urlcutoff = Routing.generate('banque_cutoff_list', {
            dossierid: dossier,
            exercice: exercice,
            banquecompteid: banquecompte
        });


    releveGrid.jqGrid('setGridParam', {
        url: urlreleve,
        editurl: editurlreleve,
        datatype: 'json',
        postData: {
            image: imageid,
            soldedebut: soldedebut,
            soldefin: soldefin
        }

    })
        .trigger('reloadGrid', {fromServer: true, page: 1});

    cutoffGrid.jqGrid('setGridParam', {
        url: urlcutoff,
        datatype: 'json'
    }).trigger('reloadGrid', {fromServer: true, page: 1});
}