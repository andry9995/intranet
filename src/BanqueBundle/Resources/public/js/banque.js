/**
 * Created by Manasse on 23/07/2018.
 */
var tab_data_jqgrid = [];
var isGo = false;
var pileTable = [];
var ordreTable = [];
$(document).ready(function() {
    var window_height = window.innerHeight;
    var impute_grid = $('#js_impute_liste'),
        lastsel_impute;

    impute_grid.jqGrid('GridUnload');

    impute_grid.jqGrid({
        datatype: 'JSON',
        loadonce: true,
        sortable: true,
        autowidth: true,
        height: window_height - 100,
        shrinkToFit: true,
        viewrecords: true,
        rownumbers: true,
        rownumWidth: 35,
        rowList: [100, 200, 500],
        altRows: true,
        multiSort: true,
        sortIconsBeforeText: true,
        headertitles: true,
        pager: '#pager_liste_impute',
        hidegrid: false,
        caption: '<div class="text-center">Imputées</div>',
        colNames: ['Clients', 'Dossier', 'Statut', 'Tâche', 'ECH', 'Inst.', 'Respons','BI','Banque', 'Compte', 'RB1', 'RB2', 'Actif', 'Ecart', 'OB', 'Rel Bq', 'Image', 'A lettrer', 'Indicateur', 'Tot lignes', 'Lettrée', 'Clef', 'Pièces manq', 'Cheq inconnus', '%Rapproché', 'Priorité', 'acontroler', 'dataObM', 'aucunImage', 'dataTache', 'dataInst'],
        colModel: [
            {
                name: 't-client',
                width: 80,
                sortable: true,
                sorttype: 'text',
                editable:false,
                classes: ''
            },
            {
                name: 't-dossier',
                index: 't-dossier',
                sortable: true,
                width: 80,
                align: 'left',
                editable:false,
                sorttype: function (cell, obj) {
                    return cell + '_' + obj.FullName;
                }
            },
            {
                name: 't_statut',
                sortable: true,
                sorttype: 'text',
                width: 80,
                align: 'left',
                editable:false,
                classes:'t_statut'
            },
            {
                name: 't-tva',
                sortable: true,
                sorttype: 'text',
                width: 80,
                editable:false,
                formatter: cell_image_tva
            },
            {
                name: 't-ech',
                sortable: true,
                width: 70,
                align: 'center',
                editable:false,
                classes: 't-ech',
                sorttype:'date', 
                formatter:'date', 
                formatoptions: {newformat:'d-m-y'}
            },
            {
                name: 't-inst',
                sortable: true,
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-inst',
                sorttype: function (cell, obj) {
                    return cell + '_' + obj.FullName;
                }
            },
            {
                name: 't-respons',
                sortable: true,
                width: 80,
                align: 'center',
                sorttype: 'text',
                editable:false,
                classes: 't-respons'
            },
            {
                name: 't-sb',
                sortable: true,
                width: 40,
                align: 'center',
                sorttype: 'text',
                editable:false,
                classes: 't-sb'
            },
            {
                name: 't-banque',
                sortable: true,
                width: 120,
                align: 'left',
                classes: '',
                editable:false,
                classes: 't_qtip_banque',
                sorttype: function (cell, obj) {
                    return cell + '_' + obj.FullName;
                }
            },
            {
                name: 't-compte',
                sortable: true,
                width: 80,
                align: 'center',
                sorttype: 'int',
                editable:false,
                classes: 't-compte'
            },
            {
                name: 't_rb',
                sortable: true,
                sorttype: 'number',
                width: 60,
                align: 'left',
                classes: 't_rb',
                editable:false,
                formatter: cell_image_valider_formatter
            },
            {
                name: 't_rb2',
                sortable: true,
                sorttype: 'number',
                width: 80,
                align: 'left',
                classes: 't_rb2',
                editable:false,
                formatter: cell_image_importe_formatter
            },
            {
                name: 't_etat',
                sortable: true,
                width: 50,
                align: 'center',
                classes: 't_etat',
                editoptions: { value: "True:False" },
                editrules: { required: true },
                formatter: cell_checkbox_actif_formatter,
                formatoptions: { disabled: false },
                editable: true
            },
            {
                name: 't_ecart',
                sortable: true,
                sorttype: 'int',
                formatter: 'number',
                width: 80,
                align: 'center',
                editable:false,
                classes: ''
            },
            {
                name: 't_ob',
                sortable: true,
                width: 50,
                align: 'center',
                classes: 't_ob',
                editable:false,
                formatter: cell_ob_formatter,
                sorttype: function (cell, obj) {
                    return cell + '_' + obj.FullName;
                }
            },
            {
                name: 't_relbq',
                sortable: true,
                width: 80,
                align: 'center',
                classes: 't_relbq pointer',
                editable:false,
                sorttype:'date', 
                formatter:'date', 
                formatoptions: {newformat:'d-m-y'}
            },
            {
                name: 't_image',
                sortable: true,
                width: 50,
                align: 'center',
                formatter: cell_image_icon_formatter,
                classes: 't_image',
                editable:false,
                sorttype: 'number'
            },
            {
                name: 't_alettre',
                sortable: true,
                sorttype: 'int',
                width: 60,
                align: 'right',
                editable:false,
                classes: ''
            },
            {
                name: 't_indicateur',
                sortable: true,
                width: 50,
                align: 'center',
                classes: '',
                editable:false,
                formatter: cell_indicateur_formatter,
                sorttype: 'number'
            },
            {
                name: 't-total',
                sortable: true,
                sorttype: 'int',
                width: 80,
                editable:false,
                align: 'right'
            },
            {
                name: 't-lettre',
                sortable: true,
                sorttype: 'int',
                width: 80,
                editable:false,
                align: 'right',
                classes: ''
            },
            {
                name: 't-clef',
                sortable: true,
                sorttype: 'int',
                width: 80,
                align: 'right',
                editable:false,
                classes: ''
            },
            {
                name: 't-piece',
                sortable: true,
                width: 80,
                sorttype: 'int',
                align: 'right',
                editable:false,
                classes: ''
            },
            {
                name: 't-cheque',
                sortable: true,
                sorttype: 'int',
                width: 80,
                align: 'right',
                editable:false,
                classes: ''
            },
            {
                name: 't-rapproche',
                sortable: true,
                sorttype: 'int',
                formatter: "currency", formatoptions: {decimalPlaces: 0, suffix: " %"},
                width: 80,
                align: 'right',
                editable:false,
                classes: ''
            },
            {
                name: 't-priorite',
                sortable: true,
                width: 60,
                align: 'center',
                classes: '',
                formatter: cell_priorite_formatter,
                editable:false,
                sorttype: 'number'
            },
            {
                name: 't-acontroler',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-acontroler'
            },
            {
                name: 't-data-ob-m',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-data-ob-m'
            },
            {
                name: 't-aucun-image',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-aucun-image'
            },
            {
                name: 't-data-tache',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-data-tache'
            },
            {
                name: 't-data-inst',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-data-inst'
            }
        ],
        beforeSelectRow: function (rowid, e) {
            var $self = $(this),
                iCol = $.jgrid.getCellIndex($(e.target).closest("td")[0]),
                cm = $self.jqGrid("getGridParam", "colModel"),
                localData = $self.jqGrid("getLocalRow", rowid);
            if (cm[iCol].name === "t_etat" && e.target.tagName.toUpperCase() === "INPUT") {
                localData.EtatCompte = $(e.target).is(":checked");
                var url = Routing.generate('banque_compte_etat');
                $.ajax({
                    url:url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        'bcId': localData._id_,
                        'etat': localData.EtatCompte
                    },
                    async: true,
                    success: function (data)
                    {
                        impute_grid.jqGrid('setCell', rowid, 't_indicateur', 0);
                        return;
                    }
                });
            }

            return true; // allow selection
        },
        loadComplete: function (data) {
            var rows = impute_grid.getDataIDs(),
                m = 0, m_1 = 0, m_2 = 0, incomplet = 0, inexist = 0, total_compte,
                array_dossier = [], array_client = [];
            for (var i = 0; i < rows.length; i++) {
                var statut = impute_grid.getCell(rows[i], "t_rb");

                statut = statut.split('<')[0].trim();
                if (statut === 'A jour') {
                    m++;
                }else if(statut === 'M-1'){
                    m_1++;
                }else if(statut === 'M-2'){
                    m_2++;
                }else if(statut === 'Inc.'){
                    incomplet++;
                }else if(statut === 'Auc.'){
                    inexist++;
                }/*else if(statut === ''){
                    inexist++;
                }else{
                    incomplet++;
                }*/

                /*var dossier = impute_grid.getCell(rows[i], "t-dossier");
                if(($.inArray(dossier, array_dossier)) === -1){
                    array_dossier.push(dossier);
                }

                var client = impute_grid.getCell(rows[i], "t-client");
                if(($.inArray(client, array_client)) === -1){
                    array_client.push(client);
                }*/
            }

            /*total_compte = m + m_1 + m_2 + incomplet + inexist;
            $('#releves_manquants-cli').html(number_format(array_client.length, 0, ',', ' '));
            $('#releves_manquants-do').html(number_format(array_dossier.length, 0, ',', ' '));
            $('#releves_manquants-cpt').html(number_format(total_compte, 0, ',', ' '));

            $('#piece-manquant-cli').html(number_format(array_client.length, 0, ',', ' '));
            $('#piece-manquant-do').html(number_format(array_dossier.length, 0, ',', ' '));
            $('#piece-manquant-cpt').html(number_format(total_compte, 0, ',', ' '));

            $('#releves_manquants-un').html(number_format(m, 0, ',', ' '));
            $('#releves_manquants-deux').html(number_format(m_1, 0, ',', ' '));
            $('#releves_manquants-trois').html(number_format(m_2, 0, ',', ' '));
            $('#releves_manquants-inc').html(number_format(incomplet, 0, ',', ' '));
            $('#releves_manquants-abs').html(number_format(inexist, 0, ',', ' '));

            var total_taf = impute_grid.jqGrid('getCol', 't-total', false, 'sum');
            var lettre_taf = impute_grid.jqGrid('getCol', 't-lettre', false, 'sum');
            var clef_taf = impute_grid.jqGrid('getCol', 't-clef', false, 'sum');
            var pc_manq_taf = impute_grid.jqGrid('getCol', 't-piece', false, 'sum');
            var chq_inc_taf = impute_grid.jqGrid('getCol', 't-cheque', false, 'sum');
            var alettre_taf = impute_grid.jqGrid('getCol', 't-alettre', false, 'sum');
            var arapproche_taf = total_taf - (lettre_taf + clef_taf);
            var rapprocher = (total_taf !== 0) ? ((lettre_taf + clef_taf) * 100) / total_taf : 0;
            $('#piece-total').html(number_format(total_taf, 0, ',', ' '));
            $('#piece-lettre').html(number_format(lettre_taf, 0, ',', ' '));
            $('#piece-clef').html(number_format(clef_taf, 0, ',', ' '));
            $('#piece-rapprochee').html(number_format(rapprocher,'2',',',' ')+'%');
            $('#piece-pc-manquant').html(number_format(pc_manq_taf, 0, ',', ' '));
            $('#piece-chq-inconnu').html(number_format(chq_inc_taf, 0, ',', ' '));
            $('#piece-alettrer').html(alettre_taf);
            $('#piece-arapprocher').html(number_format(arapproche_taf, 0, ',', ' '));*/
            if(isGo){
                //statut
                /*var html = '<div class="col-sm-4" style="margin-left:-15px !important;padding-left:0 !important;">' +
                    '<label class="col-sm-2">' +
                    '<span>Statut: </span>' +
                    '</label>' +
                    '<div class="col-sm-10"><select id="impute_statut_option_select"><option value="Tous">Tous</option><option value="Actif" selected="selected">Actif</option><option value="Suspendu">Suspendu</option><option value="Radié">Radié</option></select></div>'+
                    '</div>' +
                    '<div class="col-sm-4 text-center">Imputées</div>';*/

                impute_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').addClass('col-sm-12');
                /*impute_grid.setCaption(html);*/

                tab_data_jqgrid = [];
                tab_data_jqgrid = impute_grid.getGridParam('data');
            }
            /*filterByDossierStatus();*/


            $('.t_qtip_banque').qtip({
                content: {
                    text: function (event, api) {
                        var impute_grid = $('#js_impute_liste');
                        var compte = $(this).next().html();
                        var label_html = '<label class="">Compte: '+compte+'</label>';
                        return label_html;
                    }
                },
                position: {
                    viewport: $(window),
                    corner: {
                        target: 'topLeft',
                        tooltip: 'middleRight'
                    },
                    adjust: {
                        x: -15,
                        y: -15
                    },
                    container: $('#tab-impute')
                },
                style: {
                    classes: 'qtip-light qtip-shadow'
                }
            });
            prepare_tooltip();
            setTimeout(function() {
                filtrerAffichage();
            }, 0);
        },
        ajaxRowOptions: {async: true}
    });
    impute_grid.jqGrid('hideCol',["t_statut"]);
    impute_grid.jqGrid('hideCol',["t-acontroler"]);
    impute_grid.jqGrid('hideCol',["t-data-ob-m"]);
    impute_grid.jqGrid('hideCol',["t-aucun-image"]);
    impute_grid.jqGrid('hideCol',["t-data-tache"]);
    impute_grid.jqGrid('hideCol',["t-compte"]);
    impute_grid.jqGrid('hideCol',["t-data-inst"]);
    impute_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').addClass('col-sm-12');

    $(document).on('click', '.menu-left-minimize', function(){
        resize_tab_impute();
    });

    resize_tab_impute();

    $(document).on('click', '.tab-sitiamage, .tab-relevemnq, .tab-piecemnq', function () {
        go();
    });


    $(document).on('click', '#js_form_bqsaisi_submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        // if ($('#isoussouscategorie').val() == 2263){
        //     $('#banquesaisie').val(1);
        //     show_info('Controle banque', "Banque valider avec succès", 'info');
        // } else {
        //     if($('#js_key_compt_bq').val() == $('#js_key_compt_bq_valid').val() &&
        // 	$('#banques').val().length	> 0 &&
        // 	$('#js_key_compt_bq').val().length == 2
        // ){
        //         $('#js_num_compt_bq').parent().parent().removeClass('has-error');
        //         $('#js_key_compt_bq_valid').parent().parent().removeClass("has-error");
        //         $('#js_iban_bq').parent().parent().removeClass('has-error');
        //         $('#banques').parent().parent().removeClass('has-error');
        //         $('#banquesaisie').val(1);
        //         show_info('Controle banque', "Banque valider avec succès", 'info');
        //     } else {
        //         $('#js_key_compt_bq').parent().parent().addClass('has-error');
        //         $('#js_key_compt_bq_valid').parent().parent().addClass("has-error");
        //         $('#js_iban_bq').parent().parent().addClass('has-error');
        //         $('#banques').parent().parent().addClass('has-error');
        //     }
        // }

        var
            // val_num_compt = $('#js_num_compt_bq').val(),
            // val_key_rib = $('#js_key_compt_bq').val(),
            num_releve = $('#js_num_releve_bq').val(),
            dat_deb = $('#js_debut_bq_date').val(),
            dat_fin = $('#js_fin_bq_date').val(),
            fin_debit = $('#js_fin_bq_debi').val().replace(/\s/g, ''),
            fin_credit = $('#js_fin_bq_cred').val().replace(/\s/g, ''),
            image = $('#image').val(),
            // verif_num_compt = val_num_compt.replace(/\s/g, '').length,
            // verif_key_rib = val_key_rib.replace(/\s/g, '').length,
            dossier = $('#dossier').val();

        if (parseFloat(fin_debit) === 0.00 && parseFloat(fin_credit) ===0.00){
            $('#sansfin').val(1);
        } else {
            $('#sansfin').val(0);
        }

        if (parseFloat(fin_debit) + parseFloat(fin_credit) === 0){
            suivant(image, true);
            return;
            // inSuivant = true;
        }

        // if ($('#banquesaisie').val()==0){
        //     show_info('Controle banque', "Veuillez valider votre saisie banque", 'warning');
        //     return false;
        // }

        //--Relevés bancaires Paypal, Stripe, etc.
        // if ($('#isoussouscategorie').val()!=2263){
        //     if($('#js_key_compt_bq').val()==$('#js_key_compt_bq_valid').val() && $('#banques').val().length	>0 && $('#js_key_compt_bq').val().length==2){
        //         $('#js_num_compt_bq').parent().parent().removeClass('has-error');
        //         $('#js_key_compt_bq_valid').parent().parent().removeClass("has-error");
        //         $('#js_iban_bq').parent().parent().removeClass('has-error');
        //         $('#banques').parent().parent().removeClass('has-error');
        //     } else {
        //         $('#js_key_compt_bq').parent().parent().addClass('has-error');
        //         $('#js_key_compt_bq_valid').parent().parent().addClass("has-error");
        //         $('#js_iban_bq').parent().parent().addClass('has-error');
        //         $('#banques').parent().parent().addClass('has-error');
        //         return false;
        //     }
        //     //--verif num compte
        //     var verif_compt = 0;
        //     if((verif_num_compt == 21 && verif_key_rib == 2)) {
        //         $('input#js_num_compt_bq').parent().parent().removeClass('has-error');
        //     }else{
        //         verif_compt = 1;
        //         $('input#js_num_compt_bq').parent().parent().addClass('has-error');
        //         return false;
        //     }
        // }


        //--verif num relevé
        if (num_releve !== '') {
            $('input#js_num_releve_bq').parent().parent().removeClass('has-error');
        }else{
            $('input#js_num_releve_bq').parent().parent().addClass('has-error');
            return false;
        }

        //--verif date

        if (dat_deb.length==10) {
            $('input#js_debut_bq_date').parent().removeClass('has-error');
        }else{
            $('input#js_debut_bq_date').parent().addClass('has-error');
            return false;
        }

        if (dat_fin.length==10) {
            $('input#js_fin_bq_date').parent().removeClass('has-error');
        }else{
            $('input#js_fin_bq_date').parent().addClass('has-error');
            return false;
        }

        var d1 = dat_deb.split('/'),
            d2 = dat_fin.split('/'),
            date1 = new Date(d1[2],parseInt(d1[1])-1,parseInt(d1[0])),
            date2 = new Date(d2[2],parseInt(d2[1])-1,parseInt(d2[0]));

        if (date1.getTime()>date2.getTime()){
            $('#js_debut_bq_date').parent(".cent").addClass('has-error');
            $('#js_fin_bq_date').parent(".cent").addClass('has-error');
        } else {
            $('#js_debut_bq_date').parent(".cent").removeClass('has-error');
            $('#js_fin_bq_date').parent(".cent").removeClass('has-error');
        }


        if(dossier == '') {
            show_info('Champs non Remplis', "Veuillez Choisir l'image à traiter", 'warning');
            return false;
        }




        saveSaisie(true);

        return false;
    });

    $(document).on('click', '#mfini', function(event){
        event.preventDefault();
        event.stopPropagation();

        var trs = $('#allimage tr'),
            allowFinish = true,
            imageIds = [];

        trs.each(function(){
            var chkBox = $(this).find('input[type="checkbox"]'),
                span = $(this).find('span');

            if(!chkBox.is(':checked')){
                allowFinish = false;
            }
            imageIds.push(span.attr('id'));
        });



        if(allowFinish){

            $.ajax({
                url: Routing.generate('banque_traitement_fini'),
                type: 'POST',
                data: {
                    images: imageIds,
                    souscategorie: $('#souscat').val()
                },
                success: function(data){
                    show_info('', data.message, data.type);
                    if(data.type === 'success'){
                        var exercicetmp = $('#exercicetemp').val(),
                            dossiertmp = $('#dossieridtemp').val(),
                            panier = $('#panier-list .lot[data-exercice="'+exercicetmp+'"][data-dossier-id="'+dossiertmp+'"]');

                        panier.remove();
                        $('#allimage').html('');
                    }
                }
            })

        }
        else{
            show_info('','Il y a encore d\'image(s) non saisie(s)', 'warning');
        }

    });

    /*
     * Changement groupe
     */
    $(document).on('change', '#filtre_groupe', function(event) {

        event.preventDefault();
        event.stopPropagation();

        var url = Routing.generate('banque_situation_image_clients_by_responsable', {
            responsable: $(this).val()
        }),
            client = $('#client_gestion_tache'),
            dossier = $('#dossier_gestion_tache')
        ;

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            async: true,
            success: function(clients) {
                dossier.find("option").remove();
                client.find("option").remove();
                client.append('<option value="0">Tous</option>');

                clients.sort().forEach(function(c) {
                    client.append('<option value="' + c.id + '">' + c.nom + '</option>');
                });

                client.val('').trigger('chosen:updated');
            }
        });

    });
});

$(function () {
    var dateList = [],
        erreur_list = [],
        is_tache_legale;
    $('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-site').chosen({
        no_results_text: "Aucun site trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-dossier').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader');

    client_selector.val(0).trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    dossier_selector.val(0).trigger('chosen:updated');

    var grid_tache = $('#js_tache_liste');
    var grid_width = grid_tache.closest('.row').width() - 50;

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
                vider();
                $("#dossier option").remove();
                $("#dossier").append('<option value="0">Tous</option>');
                data.dossiers.forEach(function(d) {
                    $("#dossier").append('<option value="'+d.id+'">'+d.nom+'</option>');
                }); 
                $("#dossier").val('').trigger('chosen:updated');
            }
        });
    });

    // Changement site
    $(document).on('change', '#site', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector);
    });

     // Changement client _gestion_tache
    $(document).on('change', '#client_gestion_tache', function (event) {
        event.preventDefault();
        event.stopPropagation();
        url = Routing.generate('js_site_par_client', {client : $(this).val()});
        $.ajax({
            url:url,
            type: "GET",
            dataType: "json",
            async: true,
            success: function (data)
            {
                vider();
                $("#site_gestion_tache option").remove();
                $("#dossier_gestion_tache option").remove();
                $("#site_gestion_tache").append('<option value=""></option>');
                $("#site_gestion_tache").append('<option value="0">Tous</option>');
                data.forEach(function(s) {
                    $("#site_gestion_tache").append('<option value="'+s.id+'">'+s.nom+'</option>');
                }); 
                $("#site_gestion_tache").val('').trigger('chosen:updated');
            }
        });
    });

    // Changement site_gestion_tache
    $(document).on('change', '#site_gestion_tache', function (event) {      
        event.preventDefault();
        event.stopPropagation();
        var client = $('#client_gestion_tache').val();
        var site = $('#site_gestion_tache').val();
        url = Routing.generate('js_dossier_par_site', {client : client, site: site});
        $.ajax({
            url:url,
            type: "GET",
            dataType: "json",
            async: true,
            success: function (data)
            {
                vider();
                $("#dossier_gestion_tache option").remove();
                $("#dossier_gestion_tache").append('<option value=""></option>');
                $("#dossier_gestion_tache").append('<option value="0">Tous</option>');
                data.forEach(function(d) {
                    $("#dossier_gestion_tache").append('<option value="'+d.id+'">'+d.nom+'</option>');
                }); 
                $("#dossier_gestion_tache").val('').trigger('chosen:updated');
            }
        });
    });   

    //Affichage Tache par dossier
    $(document).on('click', '#btn-tache-dossier', function(event) {
        event.preventDefault();
        var dossier_id = $('#dossier').val();

        if (dossier_id !== '') {
            reloadGrid($("#js_tache_liste"), Routing.generate('tache_liste_tache_par_dossier', {dossier: dossier_id}));
        } else {
            $("#js_tache_liste").jqGrid('clearGridData');
        }
    });

    // Show fourchette
    $("#js_filtre_periode").change(function() {

        if($(this).val() == "5") {
            $('#js-filtre-fourchette').modal('show');
            /* $("#js_filtre_fourchette").css({display: "block"});*/
        }else {
             /*$("#js_filtre_fourchette").hide();*/
        }
    });

    //Date picker for fourchette
    $('#js-filtre-fourchette #js_debut_date').datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true});
    $('#js-filtre-fourchette #js_fin_date').datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true});

    // Go for situation image
    $('#btn_situation_image_tableau_bord').on('click', function (event) {
        go(); 
    });

    $(document).on('click', '#btn-fourchette-filtre', function () {
        var periodeDeb = $("#js_debut_date").val(),
            periodeFin = $("#js_fin_date").val();
        if ( periodeDeb ==  '' || periodeFin == '') {
            show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
            return false;
        }
        $('#js-filtre-fourchette').modal('hide');
        var perioDeb = periodeDeb.split("-"),
            perioFin = periodeFin.split("-");
        var dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0],
            dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
        $('.data_deb').attr('data-deb', dateDeb);
        $('.data_fin').attr('data-fin', dateFin);
    });

    $(window).bind('resize',function () {
        resize_tab_impute();
    });

    $('#btn_situation_image_gestion_bilan').on('click', function () {
        isGo = true;
        var client = $('#client_gestion_tache').val(),
            dossier = $('#dossier_gestion_tache').val(),
            exercice = $('#exercice').val(),
            periode = $('#js_filtre_periode').val();
            responsable = $('#js_filtre_respons_tache').val();
            site = $('#site_gestion_tache').val();

        if(client == null || client === '0' || client === ''){
            show_info('Erreur','Choisir un client','error');
            $('#clien_gestion_tache').closest('.form-group').addClass('has-error');
            return false;
        }

        if(site == null || site === ''){
            show_info('Erreur','Choisir un site','error');
            $('#site_gestion_tache').closest('.form-group').addClass('has-error');
            return false;
        }

        if(dossier == null){
            show_info('Erreur','Choisir un dossier','error');
            $('#dossier_gestion_tache').closest('.form-group').addClass('has-error');
            return false;
        }

        if( client ===  '' || dossier === '' || exercice === '' || periode === '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
            var impute_grid = $('#js_impute_liste');
            impute_grid.jqGrid('setGridParam', {
                url: Routing.generate('app_state_image_gestion_bilan'),
                postData: {client: client, dossier: dossier, exercice: exercice, responsable : responsable},
                mtype: 'POST',
                datatype: 'json'
            })
                .trigger('reloadGrid', {fromServer: true, page: 1});
        }
    });

    $(document).on('change', '#show-tri', function() {
        if($(this).is(':checked')){
            $.ajax({
                url: Routing.generate('banque_show_list_title_column'),
                type: 'GET',
                contentType: "application/x-www-form-urlencoded;charset=utf-8",
                beforeSend: function(jqXHR) {
                    jqXHR.overrideMimeType('text/html;charset=utf-8');
                },
                dataType: 'html',
                success: function(data){
                    pileTable = [];
                    show_modal(data,'Listes titres des colonnes');
                }
            });
        }
    });

    $(document).on('click', '.cl_chk_list', function() {
        var trs = $('.todo-list li'),
            rang = 0;
        if(!($(this).is(':checked'))){
            var valueOrder = $(this).parent().find('small').html().split('Ordre: '),
                count = pileTable.length;
            valueOrder = parseInt(valueOrder[1]);
            $(this).parent().find('small').remove();
            for( var i = 0; i < pileTable.length; i++){ if ( pileTable[i].ordre === valueOrder) { pileTable.splice(i, 1); }}
            trs.each(function(){
                var chkBox = $(this).find('input[type="checkbox"]');
                if(chkBox.is(':checked')){
                    var span = $(this).find('small').html().split('Ordre: '),
                        order = parseInt(span[1]),
                        label = $(this).find('label').html().trim();
                    if(valueOrder < order && count != valueOrder){
                        for( var i = 0; i < pileTable.length; i++){ if ( pileTable[i].label === label) {  pileTable[i].ordre =  pileTable[i].ordre - 1; }}
                        $(this).find('small').html('Ordre: '+ (order - 1));
                    }
                }
            });
        }else{
            pileTable.push({
                label : $(this).parent().find('label').html().trim(),
                ordre : pileTable.length + 1
            });
            $(this).parent().append('<small class="label label-primary">Ordre: '+ pileTable.length +'</small>')
        }
    });

    $(document).on('hidden.bs.modal', '#modal',function() {
        var impute_grid = $('#js_impute_liste');
        pileTable.sort(function(a,b) {
            return a.ordre - b.ordre;
        });
        for( var i = 0; i < pileTable.length; i++){ 
            if ( pileTable[i].label == 'Dossier') {
                ordreTable.push('t-dossier'); 
            }
            if ( pileTable[i].label == 'Echeance') {
                ordreTable.push('t-ech'); 
            }
            if ( pileTable[i].label == 'Tâche') {
                ordreTable.push('t-tva'); 
            }
            if ( pileTable[i].label == 'Responsable') {
                ordreTable.push('t-respons'); 
            }
            if ( pileTable[i].label == 'RB1') {
                ordreTable.push('t_rb'); 
            }
            if ( pileTable[i].label == 'RB2') {
                ordreTable.push('t_rb2'); 
            }
            if ( pileTable[i].label == 'Ecart') {
                ordreTable.push('t_ecart'); 
            }
            if ( pileTable[i].label == 'OB') {
                ordreTable.push('t_ob'); 
            }
            if ( pileTable[i].label == 'Image') {
                ordreTable.push('t_image'); 
            }
            if ( pileTable[i].label == 'A lettrer') {
                ordreTable.push('t-alettre'); 
            }
            if ( pileTable[i].label == 'Indicateur') {
                ordreTable.push('t_indicateur'); 
            }
            if ( pileTable[i].label == 'Totales lignes') {
                ordreTable.push('t-total'); 
            }
            if ( pileTable[i].label == 'Lettrée') {
                ordreTable.push('t-lettre'); 
            }
            if ( pileTable[i].label == 'Clef') {
                ordreTable.push('t-clef'); 
            }
            if ( pileTable[i].label == 'Pièce manquante') {
                ordreTable.push('t-piece'); 
            }
            if ( pileTable[i].label == 'Chèques inconnus') {
                ordreTable.push('t-cheque'); 
            }
            if ( pileTable[i].label == '% Rapprochée') {
                ordreTable.push('t-rapproche'); 
            }
            if ( pileTable[i].label == 'Priorité') {
                ordreTable.push('t-priorite'); 
            }
        }

        for(var i = 0; i < pileTable.length; i++){
            impute_grid.jqGrid('sortGrid', ordreTable[i], true, "asc");
        }
        impute_grid.trigger("reloadGrid", {page: 1});
    });

});

function resize_tab_impute(){
    setTimeout(function(){
        var impute_grid = $('#js_impute_liste');
        impute_grid.jqGrid("setGridWidth", impute_grid.closest("#tab-impute").width());
        var height = impute_grid.closest("#tab-impute").height() - 110;
        impute_grid.jqGrid("setGridHeight", height);
    }, 400);
}

function filterByDossierStatus() {
    var statut_select = $('#impute_statut_option_select').val();
    var tab_filtre_jqgrid = [];
    var impute_grid = $('#js_impute_liste');
    impute_grid.jqGrid("clearGridData");
    tab_data_jqgrid.forEach(function (data) {
        if(data.t_statut === statut_select){
            tab_filtre_jqgrid.push(data);
        }else if(statut_select === 'Tous'){
            tab_filtre_jqgrid.push(data);
        }
    });

    for(var i=0;i<tab_filtre_jqgrid.length;i++)
        impute_grid.jqGrid('addRowData',tab_filtre_jqgrid[i]._id_,tab_filtre_jqgrid[i]);
}



function go()
{
    var client = $('#client_gestion_tache').val(),
        dossier = $('#dossier_gestion_tache').val(),
        exercice = $('#exercice').val(),
        periode = $('#js_filtre_periode').val(),
        groupe = $('#filtre_groupe').val(),
        site = $('#site_gestion_tache').val(),
        dateDeb = null,
        dateFin = null;

    if(client == null || client === '0'){
        show_info('Erreur','Choisir un client','error');
        $('#client').closest('.form-group').addClass('has-error');
        return false;
    }

    if( client ===  '' || dossier === '' || exercice === '' || periode === '') {
        show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
        return false;
    }else {
        /* console.log('cli :' + client);
         console.log('dossier :' + dossier);
         console.log('exercice :' + exercice);
         console.log('periode :' + periode); */
        if( periode === "5" ) {
            var periodeDeb = $('.data_deb').attr('data-deb'),
                periodeFin = $('.data_fin').attr('data-fin');

            if ( periodeDeb ===  undefined || periodeFin === undefined) {
                show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
                $('#js-filtre-fourchette').modal('show');
                return false;
            }
            var perioDeb = periodeDeb.split("-"),
                perioFin = periodeFin.split("-");
            dateDeb = periodeDeb;
            dateFin = periodeFin;
        }

        $.ajax({
            url: Routing.generate('banque_state_image_tableau_bord'),
            type: 'POST',
            data: {
                client : client,
                site : site,
                dossier: dossier,
                exercice : exercice,
                periode  : periode,
                groupe  : groupe,
                perioddeb: dateDeb,
                periodfin: dateFin
            },
            success: function (data) {
                vider();
                $('.sitimage_depuis').html(data.date_debut);
                $('#banque-stock-sitimage').html(number_format(data.banque_stock, 0, ',', ' '));
                var col_sitimage = [];
                var total_image = 0;
                var total_stock = 0;
                var nb_stock = 0;
                $.each(data.situations_images, function( index, value ) {
                     nb_stock = (value.nb_stock === null) ? 0 : value.nb_stock;
                     total_image = total_image + value.nb_image;
                     total_stock = total_stock + nb_stock;
                     col_sitimage[index] = "<tr><td><strong>"+ value.libelle_new +"</strong></td>" +
                         "<td class='text-center'>"+ number_format(value.nb_image, 0, ',', ' ') +"</td>" +
                         "<td class='text-center'>"+ number_format(nb_stock, 0, ',', ' ') +"</td></tr>";
                 });

                $('#banque-sitimage').html(number_format(total_image, 0, ',', ' '));
                var col_inconnu_sitimage = "<td><strong>En cours</strong></td>"+
                    "<td class='text-center'>"+ number_format(data.nb_img_encours, 0, ',', ' ') +"</td>"+
                    "<td class='text-center'>"+  number_format(data.nb_img_encours, 0, ',', ' ') +"</td>";
                $('#col_data_sitimage').append(col_sitimage);
                $('#col_inconnu_sitimage').html(col_inconnu_sitimage);

                $('#releves_manquants-cli').html(number_format(data.dataPieceManquant.nbClient, 0, ',', ' '));
                $('#releves_manquants-do').html(number_format(data.dataPieceManquant.nbDossier, 0, ',', ' '));
                $('#releves_manquants-cpt').html(number_format(data.dataPieceManquant.totalCompte, 0, ',', ' '));

                $('#piece-manquant-cli').html(number_format(data.dataPieceManquant.nbClient, 0, ',', ' '));
                $('#piece-manquant-do').html(number_format(data.dataPieceManquant.nbDossier, 0, ',', ' '));
                $('#piece-manquant-cpt').html(number_format(data.dataPieceManquant.totalCompte, 0, ',', ' '));

                $('#releves_manquants-un').html(number_format(data.dataPieceManquant.ajour, 0, ',', ' '));
                $('#releves_manquants-deux').html(number_format(data.dataPieceManquant.m_1, 0, ',', ' '));
                $('#releves_manquants-trois').html(number_format(data.dataPieceManquant.m_2, 0, ',', ' '));
                $('#releves_manquants-inc').html(number_format(data.dataPieceManquant.incompl, 0, ',', ' '));
                $('#releves_manquants-abs').html(number_format(data.dataPieceManquant.aucun, 0, ',', ' '));

                $('#piece-total').html(number_format(data.dataTaf.total, 0, ',', ' '));
                $('#piece-lettre').html(number_format(data.dataTaf.lettre, 0, ',', ' '));
                $('#piece-clef').html(number_format(data.dataTaf.clef, 0, ',', ' '));
                $('#piece-rapprochee').html(number_format(data.dataTaf.rapprocher,'2',',',' ')+'%');
                $('#piece-pc-manquant').html(number_format(data.dataTaf.pc_manquant, 0, ',', ' '));
                $('#piece-chq-inconnu').html(number_format(data.dataTaf.cheque_inconnu, 0, ',', ' '));
                $('#piece-alettrer').html(0);
                $('#piece-arapprocher').html(number_format(data.dataTaf.arapprocher, 0, ',', ' '));

                if(data.status !== ''){
                    var status = '<div class="btn-group"><span class="btn btn-sm btn-danger btn-block" style="cursor: text;">'+data.status+'</span></div>';
                    $('#js_titre_status').html(status);
                }else{
                    $('#js_titre_status').html('');
                }
                go_taf();
                return false;
            }
        });
    }
    return false;
}

function vider(){
    $('#banque-sitimage').html(0);
    $('#banque-stock-sitimage').html(0);
    $('#col_data_sitimage').html('');
    var col_inconnu_sitimage = "<td><strong>En cours</strong></td>"+
        "<td class='text-center'>0</td>"+
        "<td class='text-center'>0</td>";
    $('#col_inconnu_sitimage').html(col_inconnu_sitimage);

    $('#releves_manquants-cli').html(0);
    $('#releves_manquants-do').html(0);
    $('#releves_manquants-cpt').html(0);
    $('#releves_manquants-un').html(0);
    $('#releves_manquants-deux').html(0);
    $('#releves_manquants-trois').html(0);
    $('#releves_manquants-quatre').html(0);
    $('#releves_manquants-inc').html(0);
    $('#releves_manquants-abs').html(0);

    $('#piece-manquant-cli').html(0);
    $('#piece-manquant-do').html(0);
    $('#piece-manquant-cpt').html(0);
    $('#piece-total').html(0);
    $('#piece-lettre').html(0);
    $('#piece-clef').html(0);
    $('#piece-arapprocher').html(0);
    $('#piece-rapprochee').html(0);
    $('#piece-pc-manquant').html(0);
    $('#piece-chq-inconnu').html(0);
    $('#js_impute_liste').jqGrid("clearGridData");
}

number_format = function (number, decimals, dec_point, thousands_sep) {
    number = number.toFixed(decimals);

    var nstr = number.toString();
    nstr += '';
    x = nstr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? dec_point + x[1] : '';
    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(x1))
        x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

    return x1 + x2;
};

function saveSaisie(allowSuivant){
    var iban = $('#js_iban_bq').val(),
        deb_debit = $('#js_debut_bq_debi').val().replace(/\s/g, ''),
        deb_credit = $('#js_debut_bq_cred').val().replace(/\s/g, ''),
        deb_page = $('#js_debut_page').val(),
        fin_page = $('#js_fin_page').val(),
        dat_deb = $('#js_debut_bq_date').val(),
        dat_fin = $('#js_fin_bq_date').val(),
        num_releve = $('#js_num_releve_bq').val(),
        fin_debit = $('#js_fin_bq_debi').val().replace(/\s/g, ''),
        fin_credit = $('#js_fin_bq_cred').val().replace(/\s/g, ''),
        banquecompte = $('#banquecomptes').val(),
        image = $('#image').val();

    var debut = 0, trouveDeb = false,
        fin = 0, trouveFin = false;

    if(deb_debit !== ''){
        if(parseFloat(deb_debit) != 0){
            debut = parseFloat(deb_debit);
            trouveDeb = true;
        }
    }
    if(!trouveDeb){
        if(deb_credit !== ''){
            debut = parseFloat(deb_credit);
        }
    }

    if(fin_debit !== ''){
        if(parseFloat(fin_debit) != 0){
            fin = parseFloat(fin_debit);
            trouveFin = true;
        }
    }
    if(!trouveFin){
        if(fin_credit !== ''){
            fin = parseFloat(fin_credit);
        }
    }

    $.ajax({
        url: Routing.generate('banque_releve_check_doublon'),
        type: 'GET',
        data: {
            imageId: image,
            periodeDebut: dat_deb,
            periodeFin: dat_fin,
            soldeDebut: debut,
            soldeFin: fin
        },
        success: function(data){
            if(data.length === 0){
                $.ajax({
                    data: {
                        iban: iban,
                        banquecompte: banquecompte,
                        releve: num_releve,
                        ddate: dat_deb,
                        ddebit: deb_debit,
                        dcredit: deb_credit,
                        dpage: deb_page,
                        fdate: dat_fin,
                        fdebit: fin_debit,
                        fcredit: fin_credit,
                        fpage: fin_page,
                        image:image
                    },
                    url: Routing.generate('banque_entete_releve'),
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        $(".chosenimages option").remove();
                        if(allowSuivant) {
                            suivant(data, false);
                        }
                        show_info('Saisie réussi', "Enregistrement effectuée avec succès", 'success');
                    }
                });
            }
            else{
                swal({
                    title: 'Attention',
                    text: "Cette image a dejà été saisi, clicker sur Doublon pour le mettre en doublon",
                    type: 'question',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Doublon',
                    cancelButtonText: 'Annuler'
                }).then(function () {
                        setDoublon(image);
                    },
                    function (dismiss) {
                        if (dismiss === 'cancel') {

                        } else {
                            throw dismiss;
                        }
                    }
                );
            }
        }
    });
}

function SetmodalHeight(selector){
    var winHeight = $(window).height(),
        modal = $('#myModal'),
        headearHeight = modal.find('.modal-header').height(),
        footerHeight = modal.find('.modal-footer').height();

    modal.find('.modal-body').height(winHeight - headearHeight - footerHeight - 60);

}

function setBanqueByCode(codebanque){
    var selected = -1;

    $('#banques option').each(function(){
        var optionTxt = $(this).text();
        if(optionTxt.indexOf(codebanque) !== -1){
            selected = $(this).val();

            return false;
        }
    });

    if(selected !== -1)
        $('#banques').val(selected);
}

function calculNumCompteByIban(iban){
    var numcompte = '';

    if(iban.length > 20){
        numcompte = iban.substring(4, iban.length);
    }

    return numcompte;
}

function calculIban(numcompte){

    if(numcompte.length > 0) {
        var pays = "FR",
            bban = ibanFormater(numcompte),
            numero = ibanConvertirLettres(bban.toString() + pays.toString()) + "00",
            calculCle = 0,
            pos = 0;

        while (pos < numero.length) {
            calculCle = parseInt(calculCle.toString() + numero.substr(pos, 9), 10) % 97;
            pos += 9;
        }
        calculCle = 98 - (calculCle % 97);
        var cle = (calculCle < 10 ? "0" : "") + calculCle.toString();

        return (pays + cle + bban);
    }

    return '';
}



function initializeCommun(){
    $('#banques').val(-1);

    var inputgroupAddBc = $('#btn-add-banquecompte').closest('.input-group'),
        inputgroupSaveBc = $('#btn-save-banquecompte').closest('.input-group'),
        inputgroupAddIban = $('#btn-add-iban').closest('.input-group'),
        inputgroupSaveIban = $('#btn-save-iban').closest('.input-group');

    if(inputgroupAddIban.hasClass('hidden')){
       inputgroupSaveIban.addClass('hidden');
       inputgroupAddIban.removeClass('hidden');
    }

    if(inputgroupAddBc.hasClass('hidden')){
        inputgroupSaveBc.addClass('hidden');
        inputgroupAddBc.removeClass('hidden');
    }

    $('#newbanquecompte').val('');
    $('#newiban').val('');
}



function isValidIban(input) {
    var CODE_LENGTHS = {
        AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
        CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
        FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
        HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
        LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
        MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
        RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26
    };
    var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''),
        code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/),
        digits;

    if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
        return false;
    }

    digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
        return letter.charCodeAt(0) - 55;
    });

    return mod97(digits);
}

function mod97(string) {
    var checksum = string.slice(0, 2), fragment;
    for (var offset = 2; offset < string.length; offset += 7) {
        fragment = String(checksum) + string.substring(offset, offset + 7);
        checksum = parseInt(fragment, 10) % 97;
    }
    return checksum;
}

function addGridRow(jqgrid){
    var canAdd = true;
    var rows = jqgrid.find('tr');

    rows.each(function () {
        if ($(this).attr('id') === 'new_row') {
            canAdd = false;
        }
    });



    if (canAdd) {

        event.preventDefault();
        jqgrid.jqGrid('addRow', {
            rowID: "new_row",
            initData: {},
            position: "first",
            useDefValues: true,
            useFormatter: true,
            addRowParams: {}
        });

    }
}

function initButtons(status) {
    var btns = $('#myModal .btn');

    if (status !== '') {

        btns.each(function () {
            if (!$(this).hasClass('hidden')) {
                $(this).addClass('hidden');
            }
        });
    }
    else {
        btns.each(function () {
            if ($(this).hasClass('hidden')) {
                $(this).removeClass('hidden');
            }
        })
    }
}

