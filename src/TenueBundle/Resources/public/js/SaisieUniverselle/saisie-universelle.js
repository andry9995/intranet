var lastsel_vc,
    lastsel_c,
    lastsel_ndf,
    lastsel_ecriture_recap
;

$(function () {
    initFileInput();

    var impute_grid = $('#js_impute_liste');

    impute_grid.jqGrid('GridUnload');

    impute_grid.jqGrid({
        datatype: 'JSON',
        loadonce: true,
        sortable: true,
        autowidth: true,
        height: 300,
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
        colNames: ['Clients', 'Dossier', 'Statut', 'Tâche', 'ECH','Respons','BI','Banque', 'Compte', 'RB1', 'RB2', 'Actif', 'Ecart', 'OB', 'Rel Bq', 'Image', 'A lettrer', 'Indicateur', 'Tot lignes', 'Lettrée', 'Clef', 'Pièces manq', 'Cheq inconnus', '%Rapproché', 'Priorité', 'acontroler', 'dataObM', 'aucunImage','dataTache'],
        colModel: [
            {
                name: 't-client',
                width: 80,
                sortable: true,
                sorttype: 'text',
                editable:false,
                classes: '',
                hidden: true
            },
            {
                name: 't-dossier',
                index: 't-dossier',
                sortable: true,
                width: 80,
                align: 'left',
                editable:false,
                classes: 't-dossier pointer',
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
                classes:'t_statut',
                hidden: true
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
                name: 't-respons',
                sortable: true,
                width: 80,
                align: 'center',
                sorttype: 'text',
                editable:false,
                classes: 't-respons',
                hidden: true
            },
            {
                name: 't-sb',
                sortable: true,
                width: 40,
                align: 'center',
                sorttype: 'text',
                editable:false,
                classes: 't-sb',
                hidden: true
            },
            {
                name: 't-banque',
                sortable: true,
                width: 150,
                align: 'left',
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
                classes: 't-compte',
                hidden: true
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
                editable: true,
                hidden: true
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
                sorttype: 'number',
                hidden: true
            },
            {
                name: 't-total',
                sortable: true,
                sorttype: 'int',
                width: 80,
                editable:false,
                align: 'right',
                hidden: true
            },
            {
                name: 't-lettre',
                sortable: true,
                sorttype: 'int',
                width: 80,
                editable:false,
                align: 'right',
                classes: '',
                hidden: true
            },
            {
                name: 't-clef',
                sortable: true,
                sorttype: 'int',
                width: 80,
                align: 'right',
                editable:false,
                classes: '',
                hidden: true
            },
            {
                name: 't-piece',
                sortable: true,
                width: 80,
                sorttype: 'int',
                align: 'right',
                editable:false,
                classes: '',
                hidden: true
            },
            {
                name: 't-cheque',
                sortable: true,
                sorttype: 'int',
                width: 80,
                align: 'right',
                editable:false,
                classes: '',
                hidden: true
            },
            {
                name: 't-rapproche',
                sortable: true,
                sorttype: 'int',
                formatter: "currency", formatoptions: {decimalPlaces: 0, suffix: " %"},
                width: 80,
                align: 'right',
                editable:false,
                classes: '',
                hidden: true
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
                classes: 't-acontroler',
                hidden: true
            },
            {
                name: 't-data-ob-m',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-data-ob-m',
                hidden: true
            },
            {
                name: 't-aucun-image',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-aucun-image',
                hidden: true
            },
            {
                name: 't-data-tache',
                sortable: true,
                sorttype: 'text',
                width: 60,
                align: 'center',
                editable:false,
                classes: 't-data-tache',
                hidden: true
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
                }
            }
            if(isGo){

                impute_grid.closest('.ui-jqgrid').find('.ui-jqgrid-title').addClass('col-sm-12');
                tab_data_jqgrid = [];
                tab_data_jqgrid = impute_grid.getGridParam('data');
            }

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

    $(document).on('change', '#groupe', function(event){
        event.preventDefault();
        event.stopPropagation();

        var client = $('#client'),
            dossier = $('.filtre-dossier'),
            url = Routing.generate('app_clients_by_responsable', {
            responsable: $(this).val()
        });

        $.ajax({
            url: url,
            type: "GET",
            success: function(clients) {
                dossier.find("option").remove();
                client.find('option').remove();
                client.append('<option value="0">Tous</option>');

                clients.sort().forEach(function(c) {
                    client.append('<option value="' + c.id + '">' + c.nom + '</option>');
                });
            }
        });
    });

    $(document).on('change', '.filtre-client', function(event){
        event.preventDefault();
        if($(this).attr('id') !== 'filtre-client') {
            $('.filtre-client').val($(this).val());
            setDossiers($(this).val());
        }
    });

    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = Routing.generate('js_site_par_client', {client : $(this).val()}),
            site = $('#site'),
            dossier = $('.filtre-dossier')
        ;

        $.ajax({
            url:url,
            type: "GET",
            dataType: "json",
            async: true,
            success: function (data)
            {
                vider();
                site.find('option').remove();
                dossier.find("option").remove();
                site.append('<option value=""></option>');
                site.append('<option value="0">Tous</option>');
                data.forEach(function(s) {
                    site.append('<option value="'+s.id+'">'+s.nom+'</option>');
                });
            }
        });
    });

    $(document).on('change', '#site', function(event){
       event.preventDefault();
       event.stopPropagation();

        var client = $('#client'),
            site = $('#site'),
            dossier = $('.filtre-dossier'),
            url = Routing.generate('js_dossier_par_site', {client : client.val(), site: site.val()});
        $.ajax({
            url:url,
            type: "GET",
            success: function (data)
            {
                vider();
                dossier.find("option").remove();
                dossier.append('<option value=""></option>');
                dossier.append('<option value="0">Tous</option>');
                data.forEach(function(d) {
                    dossier.append('<option value="'+d.id+'">'+d.nom+'</option>');
                });
                dossier.val('');
            }
        });
    });

    $(document).on('change', '.filtre-dossier', function(event){
        event.preventDefault();
        $('.filtre-dossier').val($(this).val());
        // setExercices($(this).val(), $(this));
    });


    $(document).on('dblclick', '.t-dossier', function(event){
        event.preventDefault();
        var dossier = $(this).text(),
            dossierOptions = $('#dossier').find('option'),
            dossierId = -1
        ;

        dossierOptions.each(function(){
            if($(this).text() == dossier){
                dossierId = $(this).attr('value');
                return true;
            }
        });

        var filtreDossier = $('.filtre-dossier');
        filtreDossier.val(dossierId);
        $('#dossier').trigger('change');

    });

    $(document).on('change', '.filtre-exercice', function(event){
        event.preventDefault();
        $('.filtre-exercice').val($(this).val());
    });

    $(document).on('change', '.filtre-categorie', function(event){
        event.preventDefault();
        $('.filtre-categorie').val($(this).val());
        setSouscategories($(this).val());
    });

    $(document).on('change', '.filtre-souscategorie', function(event){
        event.preventDefault();
        $('.filtre-souscategorie').val($(this).val());
        setSoussouscategories($(this).val(), undefined, undefined);
    });

    $(document).on('change', '.filtre-statut', function(event){
       event.preventDefault();
       $('.filtre-statut').val($(this).val());

       var filtre = $(this).closest('.form-group'),
            dossierid = filtre.find('.filtre-dossier').val(),
            status = $(this).val(),
            exercice = $('#exercice').val();

        setCategorie(dossierid, exercice, status);
    });

    $(document).on('change', '.filtre-date-scan', function(event){
       event.preventDefault();
       $('.filtre-date-scan').val($(this).val());
    });

    $('#btn-situation-image').on('click', function () {
        isGo = true;
        var client = $('#client').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val(),
            site = $('#site').val(),
            responsable = ''
        ;

        if(client == null || client === '0' || client === ''){
            show_info('Erreur','Choisir un client','error');
            return false;
        }

        if(site == null || site === ''){
            show_info('Erreur','Choisir un site','error');
            return false;
        }

        if(dossier == null){
            show_info('Erreur','Choisir un dossier','error');
            return false;
        }

        if( client ===  '' || dossier === '' || exercice === '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
            var impute_grid = $('#js_impute_liste');
            impute_grid.jqGrid('setGridParam', {
                url: Routing.generate('app_state_image_gestion_bilan'),
                postData: {
                    client: client,
                    dossier: dossier,
                    exercice: exercice,
                    responsable : responsable
                },
                mtype: 'POST',
                datatype: 'json'
            })
                .trigger('reloadGrid', {fromServer: true, page: 1});
        }
    });



    $(document).on('click', '.btn-go', function(event){
       event.preventDefault();
       event.stopPropagation();
       var filtre = $(this).closest('.form-group'),
           dossierid = filtre.find('.filtre-dossier').val(),
           status = filtre.find('.filtre-statut').val(),
           categorieid = filtre.find('.filtre-categorie').val(),
           souscategorieid = filtre.find('.filtre-souscategorie').val(),
           soussouscategorieid = filtre.find('.filtre-soussouscategorie').val(),
           exercice = $('#exercice').val(),
           // datescan = filtre.find('.filtre-date-scan').val();
           datescan = '-1';

       if(souscategorieid === null)
           souscategorieid = -1;

        if(soussouscategorieid === null)
            soussouscategorieid = -1;

       $.ajax({
           url: Routing.generate('tenu_su_list_image'),
           type: 'GET',
           data: {
               dossierid: dossierid,
               status: status,
               categorieid: categorieid,
               souscategorieid: souscategorieid,
               soussouscategorieid: soussouscategorieid,
               exercice: exercice,
               datescan: datescan
           },
           success: function(data) {
               var suModal = $('#su-modal'),
                   mySidenav = $('#mySidenav'),
                   modalBody = suModal.find('.modal-body');

               mySidenav.html(data);
               suModal.modal('show');

               SetModalHeight('su-modal');

               var  modalBodyHeight = modalBody.height(),
                   firstImage = $('#allimage tr:first').find('span')
               ;
               mySidenav.height(modalBodyHeight - 5);

               if(firstImage.length > 0) {
                   firstImage.addClass('active');
                   ShowImageDetails(firstImage.attr('data-id'));
               }
               else{
                   ShowImageDetails(-1);
               }
           },
           error: function(){
               var  mySidenav = $('#mySidenav');

               mySidenav.html('');
               ShowImageDetails(-1);
           }
       })

    });

    $(document).on('click', '#btn-recategorisation', function(event){
        event.stopPropagation();
        event.preventDefault();
        var recategorisationModal = $('#recategorisation-modal');
        $.ajax({
            url: Routing.generate('tenu_su_recategoriser_form'),
            type: 'GET',
            data: {imageid: getImageId()},
            success: function (data) {

                if(data.type !== undefined){
                    show_info('Attention', data.message, data.type);
                    return false;
                }

                recategorisationModal.find('.modal-body').html(data);
                var recSouscategorie = $('#rec-souscategorie');
                setRecSouscategories($('#rec-categorie').val(), recSouscategorie.val());
                setRecSoussouscategories(recSouscategorie.val(), $('#rec-soussouscategorie').val());
                recategorisationModal.modal('show');
            }
        });

    });

    $(document).on('click', '.image', function (event) {
        event.stopPropagation();
        event.preventDefault();

        var id = $(this).attr('data-id'),
            images = $('.image');

        images.removeClass('active');
        $(this).addClass('active');

        ShowImageDetails(id);

    });

    $(document).on('focusout', '#siren', function(event){
       event.stopPropagation();
       setRsBySiren($(this).val());
    });

    $(document).on('change', '#type-av', function(event){
        event.preventDefault();
        event.stopPropagation();

        var typeAv = $(this).val(),
            periodeContainer = $('.periode-livraison-container'),
            dateContainer = $('.date-livraison-container');

        periodeContainer.addClass('hidden');
        dateContainer.addClass('hidden');

        switch (parseInt(typeAv)){
            //Bien
            case 1:
                dateContainer.removeClass('hidden');
                break;
            //Service
            case 2:
                periodeContainer.removeClass('hidden');
                break;
            //Bien et service
            default:
                dateContainer.removeClass('hidden');
                periodeContainer.removeClass('hidden');
                break;
        }
    });

    $(document).on('click', '#btn-save', function(event){
        event.preventDefault();
        event.stopPropagation();

        var form = $(this).closest('form');

        $.ajax({
            url: Routing.generate('tenu_su_save'),
            type: 'POST',
            data: form.serialize(),
            success: function (data) {
                show_info(data.title, data.message, data.type);
                validerImage(getImageId());
                showEcritureRecap(getImageId());
            }
        });
    });

    $(document).on('click', '#btn-save-social, #btn-save-fiscal', function(event){
       event.preventDefault();
       event.stopPropagation();

       var form = $('#form-social'),
           categorie = 'social';

       if($(this).attr('id') === 'btn-save-fiscal'){
           form = $('#form-fiscal');
           categorie = 'fiscal';
       }

       $.ajax({
           url: Routing.generate('tenu_su_save_fiscal_social', {categorie: categorie}),
           type: 'POST',
           data: form.serialize(),
           success: function(data){
               show_info(data.title, data.message, data.type);
               // validerImage($('#image').val());
               validerImage(getImageId());
           }
       })
    });

    $(document).on('click', '#btn-save-ndf, #btn-save-caisse', function(event){
        event.preventDefault();
        event.stopPropagation();

        var form = $('#form-ndf-caisse'),
            categorie = 'ndf';

        if($(this).attr('id') === 'btn-save-caisse'){
            categorie = 'caisse';
        }

        $.ajax({
            url: Routing.generate('tenu_su_save_ndf_caisse',{categorie: categorie}),
            type: 'POST',
            data: form.serialize(),
            success: function(data){
                show_info(data.title, data.message, data.type);

                if(categorie === 'caisse'){
                    // var imageid = $('#image').val();
                    var imageid = getImageId();
                    $('#allimage').find('span[data-id="'+imageid+'"]').click();
                }

                validerImage(imageid);
            }
        })

    });

    $(document).on('change','#type-echeance', function(event){
        event.preventDefault();
        event.stopPropagation();

        var typeEcheance = $(this).val(),
            regle = '';

        if(parseInt(typeEcheance) === 0){
            regle = 'regle';
        }
        else if(parseInt(typeEcheance) === 1){
            regle = 'dossier';
        }

        if(parseInt(typeEcheance) === 1 || parseInt(typeEcheance) === 0){
            var regleid = $('#regleid');

            $.ajax({
                url: Routing.generate('tenue_su_regle', {regle: regle}),
                data:{
                    // imageid: $('#image').val(),
                    imageid: getImageId(),
                    categorieid: $('#categorie').val(),
                    regleid: regleid.val(),
                    regleentity: regleid.attr('data-entity')
                },
                type: 'GET',
                success: function(data){
                   $('#regle-modal .modal-body').html(data);
                    $('#regle-modal').modal('show');
                }

            });
        }
    });

    $(document).on('change', '#rp-datele-active', function(event){
        event.stopPropagation();

        var datele = $('#rp-datele'),
            disabled = true;

        if($(this).is(':checked')){
            disabled = false;
        }
        if(disabled){
            datele.val(-1);
        }

        datele.attr('disabled', disabled);
    });

    $(document).on('click', '#rp-save', function(event){
        event.preventDefault();

        // var imageid = $('#image').val(),
        var imageid = getImageId(),
            regle = $('#regleid'),
            regleid = regle.val(),
            rpdate= $('#rp-date').val(),
            rpnbjour = $('#rp-nbjour').val(),
            rpdatele = $('#rp-datele').val(),
            typeecheance = $('#type-echeance').val(),
            dateimage = $('#date-fact').val();


        if(parseInt(rpdate) === 1){
            var dateLivraison = $('#date-livraison').val();

            if(dateLivraison !== ''){
                dateimage = dateLivraison;
            }
            else{
                dateimage = $('#periode-debut').val();
            }

            if(dateimage === ''){
                show_info('','La date livraison ou les periodes doivent être renseignées');
                return;
            }
        }

        if(dateimage === ''){
            show_info('','La date facture doit être renseignée');
            return;
        }

        $.ajax({
            url: Routing.generate('tenue_su_regle_save'),
            data:{
                imageid: imageid,
                regleid: regleid,
                rpdate: rpdate,
                rpnbjour: rpnbjour,
                rpdatele: rpdatele,
                dateimage: dateimage,
                typeecheance: typeecheance
            },
            type: 'POST',
            success: function(data){
                show_info(data.title, data.message, data.type);
                if(data.type === 'success'){
                    $('#date-echeance').val(data.dateecheance);
                }
            }
        });
    });

    $(document).on('change', '.souscategorie-ecriture', function(event){
        event.stopPropagation();
        var souscategorie = $(this).val();
        setSoussouscategories(souscategorie, $(this).closest('.form-horizontal').find('.soussouscategorie-ecriture'), undefined);

        if($('#justificatif').length === 0) {
            var ecriture = $(this).closest('.form-horizontal.ecriture'),
                souscategorieId = $(this).val();

            setPccDossier(souscategorieId, ecriture.find('select[name="pccdossier"]'));
        }
    });

    $(document).on('change', '.nature', function(event){
        event.stopPropagation();
        var nature = $(this).val(),
            selector = $(this).closest('.form-horizontal').find('.sousnature');

        setSousnature(nature, selector, undefined);
    });

    $(document).on('change', '.typevente-ecriture', function(event){
        event.stopPropagation();
        event.preventDefault();

        var typevente = $(this).val(),
            closestform = $(this).closest('.form-horizontal'),
            datelivraison = closestform.find('.ecrituredatelivraison'),
            periodelivraison = closestform.find('.ecritureperiodelivraison');

        datelivraison.removeClass('hidden');
        periodelivraison.removeClass('hidden');

        if(parseInt(typevente) === 1){
            periodelivraison.addClass('hidden');

            closestform.find('input[name="periodedebuttva"]').val('');
            closestform.find('input[name="periodefintva"]').val('');
        }
        else if(parseInt(typevente) === 2){
            datelivraison.addClass('hidden');

            closestform.find('input[name="datelivraisontva"]').val('');
        }

    });

    $(document).on('change', '.sousnature', function(event){
       event.preventDefault();
       event.stopPropagation();

       var sousnature = $(this).val(),
           closestform = $(this).closest('.form-horizontal'),
           souscategorie = closestform.find('.souscategorie-ecriture'),
           soussouscategorie = closestform.find('.soussouscategorie-ecriture'),
           pcc = closestform.find('select[name="pccdossier"]')
       ;

       $.ajax({
           url: Routing.generate('tenu_su_souscategorie'),
           type: 'GET',
           data:  {sousnatureid: sousnature},
           success: function(data){
               souscategorie.val(data.souscategorie);
               setSoussouscategories(data.souscategorie, soussouscategorie, data.soussouscategorie);
               setPccDossier(data.souscategorie, pcc, pcc.val());
           }
       })
    });

    $(document).on('click', '.panel-body', function(event){
       event.preventDefault();
       event.stopPropagation();

       if(!$(this).hasClass('active')) {
           var panels = $(this).closest('.ibox-content').find('.panel');
           panels.each(function () {
               $(this).removeClass('panel-primary');
               $(this).addClass('panel-default');
           });

           $(this).closest('.panel').removeClass('panel-default');
           $(this).closest('.panel').addClass('panel-primary');
           $(this).closest('.panel').addClass('active');
       }

    });

    $(document).on('click', '.btn-save-ecriture', function(event){
        event.stopPropagation();
        event.preventDefault();

        var form = $(this).closest('.form-horizontal'),
            tvaCalcule = form.find('.montant-tva').val().replace(' ', ''),
            tvaSaisi = form.find('.montant-tva-saisi').val().replace(' ', ''),
            tvaEntity = form.find('input[name="tvaentity"]'),
            tvaId = form.find('input[name="tvaid"]')
        ;


        if(tvaCalcule === '' || tvaSaisi === 'NaN'){
            tvaCalcule = 0;
        }

        if(tvaSaisi === '' || tvaSaisi === 'NaN'){
            tvaSaisi = 0;
        }

        if(parseFloat(tvaCalcule) !== parseFloat(tvaSaisi)){
            show_info('', 'Le TVA saisi et calculé sont differents', 'error');
            return false;
        }

        $.ajax({
            url: Routing.generate('tenu_su_save_ecriture'),
            data: form.serialize(),
            type: 'POST',
            success: function (data) {
                if(data.action  !== undefined && data.action === 'insert'){
                    tvaId.val(data.id);
                    tvaEntity.val('TvaImputationControle');
                }
                show_info('', data.message, data.type);
                validerImage(getImageId());

                $('#ecriture-recap').jqGrid('setGridParam', {
                        postData: {
                            imageid: getImageId()
                        },
                        datatype: 'json',
                        loadonce: true,
                        page: 1
                    }
                ).trigger('reloadGrid', {fromServer: true, page: 1});
            }
        })
    });

    $(document).on('click', '.btn-del-ecriture', function(event){
        event.preventDefault();
        event.stopPropagation();

        var form = $(this).closest('.panel').find('.form-horizontal'),
            tvaEntity = form.find('input[name="tvaentity"]').val(),
            tvaId = form.find('input[name="tvaid"]').val()
        ;

        swal({
            title: 'Attention',
            text: "Voulez vous supprimer",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confimer',
            cancelButtonText: 'Annuler'
        }).then(function () {
                if(tvaId.includes('new')){
                    form.closest('.panel').remove();
                }
                else {
                    $.ajax({
                        url: Routing.generate('tenu_su_supprimer_ecriture'),
                        data: {
                            tvaId: tvaId,
                            tvaEntity: tvaEntity
                        },
                        type: 'DELETE',
                        success: function (data) {
                            show_info('', data.message, data.type);
                            if (data.type === 'success') {
                               form.closest('.panel').remove();
                            }
                        }
                    });
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

    $(document).on('click', '.btn-dupliquer-ecriture', function(event){
        event.preventDefault();
        event.stopPropagation();

        var imageid = getImageId(),
            formModel = $(this).closest('.panel').find('.form-horizontal'),
            ecritureid = $('.ecriture-container').find('.ecriture').length + 1,
            sousnatureid = $('#jfsousnature').val();
        addEcriture(imageid, ecritureid, '0-0-0', sousnatureid, formModel);
    });

    $(document).on('change', '#rec-categorie', function (event) {
        event.preventDefault();
        event.stopPropagation();

        setRecSouscategories($(this).val(), -1);
    });

    $(document).on('change', '#rec-souscategorie', function (event) {
        event.preventDefault();
        event.stopPropagation();

        setRecSoussouscategories($(this).val(), -1);
    });
    
    $(document).on('click', '#btn-rec-valider', function (event) {
        event.preventDefault();
        event.stopPropagation();

        swal({
            title: 'Attention',
            text: "Vous allez modifier la catégorie de cette image ",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confimer',
            cancelButtonText: 'Annuler'
        }).then(function () {
                $.ajax({
                    url: Routing.generate('tenu_su_recategoriser'),
                    data: $('#form-recategorisation').serialize(),
                    type: 'POST',
                    success: function (data) {
                        show_info('', data.message, data.type);
                        if(data.reloadScreen === true){
                            var currentImage = $('#allimage').find('span.active');
                            if(currentImage.length > 0) {
                                ShowImageDetails(currentImage.attr('data-id'));
                            }
                        }

                        $('#title-saisie').html(data.title);

                        $('#recategorisation-modal').modal('hide');
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
    
    $(document).on('click', '#btn-add-ecriture', function(event) {
        event.stopPropagation();
        event.preventDefault();
        // var imageid = $('#image').val(),
        var imageid = getImageId(),
            ecritureid = $('.ecriture-container').find('.ecriture').length + 1,
            sousnatureid = $('#jfsousnature').val();
        addEcriture(imageid, ecritureid, '0-0-0', sousnatureid, undefined);

    });

    $(document).on('change', '.montant-pcc, .montant-tva-saisi, .montant-tiers', function(event) {
        event.stopPropagation();
        event.preventDefault();

        var ecriture = $(this).closest('.form-horizontal.ecriture'),
            montantPcc = ecriture.find('.montant-pcc'),
            montantPccVal = montantPcc.val().replace(' ', ''),
            montantTvaSaisi = ecriture.find('.montant-tva-saisi'),
            montantTvaSaisiVal = montantTvaSaisi.val().replace(' ', ''),
            montantHt = ecriture.find('.montant-tiers'),
            montantHtVal = montantHt.val().replace(' ', '')
            ;


        if (montantPccVal === '' || !$.isNumeric(montantPccVal)) {
            montantPccVal = 0;
            montantPcc.val(0);
        }

        if (montantTvaSaisiVal === '' || !$.isNumeric(montantTvaSaisiVal)) {
            montantTvaSaisiVal = 0;
            montantTvaSaisi.val(0);
        }

        if(montantHtVal === '' || !$.isNumeric(montantHtVal)){
            montantHtVal = 0;
            montantHt.val(0);
        }

        if($(this).hasClass('montant-pcc') || $(this).hasClass('montant-tva-saisi')) {
            montantHtVal = parseFloat(montantPccVal) - parseFloat(montantTvaSaisiVal);
            ecriture.find('.montant-tiers').val(number_format(montantHtVal, 2, '.', ' '));
        }
        else if($(this).hasClass('montant-tiers')){
            montantPccVal = parseFloat(montantHtVal) + parseFloat(montantTvaSaisiVal);
            ecriture.find('.montant-pcc').val(number_format(montantPccVal, 2, '.', ' '));
        }
    });

    $(document).on('change', '.taux-tva, .montant-pcc', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var ecriture = $(this).closest('.form-horizontal.ecriture'),
            montantTiers = ecriture.find('.montant-tiers'),
            montantTiersVal = montantTiers.val().replace(' ', ''),
            montantPcc = ecriture.find('.montant-pcc'),
            montantPccVal = montantPcc.val().replace(' ', ''),

            montantTva = ecriture.find('.montant-tva'),
            tvaTaux = ecriture.find('.taux-tva');


        if(montantTiersVal === '' || !$.isNumeric(montantTiersVal)){
            montantTiersVal = 0;
            montantTiers.val(0);
        }

        if(montantPccVal === '' || !$.isNumeric(montantPccVal)){
            montantPccVal  = 0;
            montantPccVal.val(0);
        }

        if (parseInt(tvaTaux.val()) !== -1) {
            var tvaTauxVal = ecriture.find('.taux-tva').find('option:selected').text(),
                montantTvaVal = parseFloat(montantTiersVal) * parseFloat(tvaTauxVal) / 100;

            montantTva.val(number_format(montantTvaVal, 2, '.', ' '));
        }
        //Calcul tva TAUX
        else {
            // Taux de TVA = [(Prix TTC – Prix HT) / Prix HT] x 100

        }

    });

    $(document).on('change', '.montant-tva-saisi', function(event){

        event.preventDefault();
        event.stopPropagation();

        var ecriture = $(this).closest('.form-horizontal.ecriture'),
            montantTiers = ecriture.find('.montant-tiers'),
            montantTiersVal = montantTiers.val().replace(' ', ''),
            montantPcc = ecriture.find('.montant-pcc'),
            montantPccVal = montantPcc.val().replace(' ', ''),

            tvaTaux = ecriture.find('.taux-tva');

        if (parseFloat(montantTiersVal) !== 0) {
            var taux = (((parseFloat(montantPccVal) - parseFloat(montantTiersVal)) / parseFloat(montantTiersVal)) * 100)
                .toFixed(1);

            tvaTaux.find('option').each(function(){
               if(parseFloat($(this).text()) === parseFloat(taux)){
                  tvaTaux.val($(this).attr('value'));
                  return true;
               }
            });


        }
        else{
            tvaTaux.val(-1);
        }
    });

    $(document).on('click', '#tiers-add', function(event){
        event.preventDefault();
        event.stopPropagation();

        var tiersModal = $('#tiers-modal'),
            imageid = getImageId(),
            ecritureId = $(this).closest('.ecriture').attr('id'),
            dataEcriture = $('#tiers-ecriture-id'),
            tiersimage = $('#tiers-image')
        ;

        dataEcriture.val('');

        $('#tiers-intitule').val('');
        $('#tiers-compte').val('');

        tiersimage.val('');

        $.ajax({
            url: Routing.generate('tenu_su_compte_collectif'),
            type: 'GET',
            data: {imageid: imageid},
            success: function(data){
                if(data.type !== undefined){
                    show_info('', data.message, data.type)
                }
                else {
                    $('#tiers-pcc').html(data);
                    dataEcriture.val(ecritureId);
                    tiersModal.modal('show');
                    tiersimage.val(imageid);
                }
            }
        })
    });

    $(document).on('click', '#btn-tiers-valider', function(event){
        event.preventDefault();
        event.stopPropagation();

        var ecritureid = $('#tiers-ecriture-id').val(),
            tiersModal = $('#tiers-modal');

        $.ajax({
            url: Routing.generate('tenu_su_save_tiers'),
            type: 'POST',
            data: $('#form-tiers').serialize(),
            success: function(data){
                if(data.type === 'success') {
                    var tiersSelect = $('#' + ecritureid).find('select[name="pcctiers"]');
                    tiersSelect.append(data.option);
                    tiersSelect.val(data.id);
                    tiersSelect.trigger("chosen:updated");
                    tiersModal.modal('hide');
                }
                else if(data.type === 'error'){
                    show_info('', data.message, data.type);
                }
            }
        })

    });

    $(document).on('change', '.sousnature', function(event){
        event.preventDefault();
        event.stopPropagation();

        if($('#justificatif').length > 0) {
            var ecriture = $(this).closest('.form-horizontal.ecriture'),
                sousnatureId = $(this).val(),
                pccDossier = ecriture.find('select[name="pccdossier"]');

            $.ajax({
                url: Routing.generate('tenu_su_td_ndf_sousnature_pcc_ecriture'),
                type: 'GET',
                data: {
                    // imageid: $('#image').val(),
                    imageid: getImageId(),
                    sousnatureid: sousnatureId
                },
                success: function (data) {
                    pccDossier.html(data);
                }
            });
        }

    });

    $(document).on('blur', '.montant', function(event){
        event.preventDefault();
        event.stopPropagation();

        var montant = $(this).val();
        $(this).val(number_format(parseFloat(montant),2,'.',' '));

    });

    $(document).on('focus', '.montant', function(event){
        event.preventDefault();
        event.stopPropagation();

        var montant = $(this).val();
        $(this).val(montant.replace(' ', ''));
    });

    $(document).on('change', '.filtre-dossier, .filtre-exercice', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var filtre = $(this).closest('.form-group'),
            dossier = filtre.find('.filtre-dossier').val(),
            souscategorie = filtre.find('.filtre-souscategorie').val(),
            soussouscategorie = filtre.find('filtre-soussouscategorie').val(),
            exercice = $('#exercice').val();

        if (dossier === undefined || exercice === undefined) {
            return false;
        }
        if (dossier !== '' && parseInt(dossier) !== 0 && exercice !== '') {
            setStatut(dossier, exercice);
        }

    });

    $(document).on('change', '#siren', function(event){
        event.preventDefault();
        event.stopPropagation();
        var ecritureDossierGrid = $('#ecriture-dossier'),
            url = Routing.generate('tenu_su_ecriture_dossier', {typeRecherche: 'siren'});

        ecritureDossierGrid.jqGrid('setGridParam', {
                url: url,
                postData: {
                    imageid: getImageId(),
                    champ: $('#siren').val()
                },
                datatype: 'json'
            }
        ).trigger('reloadGrid', {fromServer: true, page: 1});

    });

    $(document).on('click', '#btn-infoperdos, #btn-categorie', function(event) {
        event.stopPropagation();
        event.preventDefault();

        var infoPerdosContainer = $('#infoperdos-container'),
            categorieContainer = $('#categorie-container');

        if ($(this).attr('id') === 'btn-infoperdos') {
            if (!infoPerdosContainer.hasClass('hidden')) {
                infoPerdosContainer.addClass('hidden');
            }
        }
        else {
            if (!categorieContainer.hasClass('hidden')) {
                categorieContainer.addClass('hidden');
            }
        }

        if($(this).hasClass('blink')){
            $(this).removeClass('blink');
        }
        if($(this).hasClass('active')){
            $(this).removeClass('active');
        }
        else{
            $(this).addClass('active');
            if($(this).attr('id') === 'btn-infoperdos')
                infoPerdosContainer.removeClass('hidden');
            else
                categorieContainer.removeClass('hidden');
        }
    });

    $(document).on('change', '#chk-reglement', function (event) {
        event.stopPropagation();

        var reglements = $('.reglement');

        reglements.each(function(){
            var id = $(this).attr('id');

            if(id !== 'num-cb') {
                $(this).attr('disabled', false);
            }
            else{
                if(parseInt($('#mode-reglement').val()) === 1){
                    $(this).attr('disabled', false);
                }
            }
        });

        if(!$(this).is(':checked')){
            reglements.attr('disabled', true);
        }
    });

    $(document).on('change', '#organisme', function(event){
        event.stopPropagation();
        event.preventDefault();

        var organismeid = $(this).val(),
            nature = $('#nature'),
            sousnature = $('#sousnature'),
            souscategorie = $('#fs-souscategorie'),
            soussouscategorie = $('#fs-soussouscategorie'),
            categorieid = -1,
            formId = $(this).closest('form').attr('id');

        if(formId === 'form-social'){
            categorieid = 20;
        }
        else if(formId === 'form-fiscal'){
            categorieid = 21;
        }
        setSelectsByOrganisme(organismeid, categorieid, nature, undefined, 'nature');
        setSelectsByOrganisme(organismeid, categorieid, sousnature, undefined, 'sousnature');
        setSelectsByOrganisme(organismeid, categorieid, souscategorie, undefined, 'souscategorie');
        setSelectsByOrganisme(organismeid, categorieid, soussouscategorie, undefined, 'soussouscategorie');
    });

    $(document).on('change', '#type-sociale', function(event){
        event.stopPropagation();
        event.preventDefault();

        var organisme = $('#organisme');

        if(!organisme.is(':disabled')){
            organisme.attr('disabled', true);
            organisme.val(-1);
        }

        if(parseInt($(this).val()) === 2){
            organisme.attr('disabled', false);
        }
    });

    $(document).on('change', '#fs-souscategorie', function(event){
       event.preventDefault();
       event.stopPropagation();

       setSoussouscategories($(this).val(), $('#fs-soussouscategorie'),undefined);
    });

    $(document).on('change', '#cerfa', function(event){
        event.preventDefault();
        event.stopPropagation();

        var options = $(this).find('option'),
            cerfaId = $(this).val(),
            intitule = ''
        ;

        options.each(function () {
            if( $(this).val() === cerfaId){
                intitule = $(this).attr('data-intitule');
                return true;
            }
        });

        $('#cerfa-intitule').val(intitule);
    });

    $(document).on('click', '#ben-edit,#ben-add', function(event){
       event.preventDefault();
       event.stopPropagation();

       var benModal = $('#beneficiaire-modal'),
           beneficiaire = $('#beneficiaire'),
           benificiaireId = beneficiaire.val(),
           nom = '',
           prenom = '',
           mandataire = '',
           newBen = true;


       if($(this).attr('id') === 'ben-edit') {
           if (parseInt(beneficiaire.val()) !== -1) {
               var option = beneficiaire.find('option[value="' + benificiaireId + '"]');
               nom = option.attr('data-nom');
               prenom = option.attr('data-prenom');
               mandataire = option.attr('data-mandataire');

               $('#ben-nom').val(nom);
               $('#ben-prenom').val(prenom);
               $('#ben-id').val(benificiaireId);

               if (parseInt(mandataire) === 1) {
                   $('#ben-mandataire').attr('checked', true);
               }
               newBen = false;
           }
       }

       if(newBen) {
           $('#ben-nom').val('');
           $('#ben-prenom').val('');
           $('#ben-id').val(-1);
           $('#ben-mandataire').attr('checked', false);
           $('#ben-image-id').val(getImageId());
       }

       benModal.modal('show');
    });

    $(document).on('click', '#btn-ben-valider',function(event){
        event.preventDefault();
        event.stopPropagation();

        var formBeneficiaire = $('#form-beneficiaire'),
            beneficiaire = $('#beneficiaire')
        ;


        $.ajax({
            url: Routing.generate('tenu_su_valider_beneficiaire'),
            type: 'POST',
            data: formBeneficiaire.serialize(),
            success: function (data) {
                show_info('', data.message, data.type);

                if (data.type === 'success') {
                    if (data.action === 'insert') {
                        beneficiaire.append(data.option);
                    }
                    else if (data.action === 'update') {
                        var option = beneficiaire.find('option[value="' + data.id + '"]');
                        option.remove();
                        beneficiaire.append(data.option);
                    }
                    beneficiaire.val(data.id);
                    $('#beneficiaire-modal').modal('hide');
                }
            }
        });

    });

    $(document).on('click', '#vehicule-edit, #vehicule-add', function(event){
        event.stopPropagation();
        event.preventDefault();

        var modal = $('#vehicule-modal'),
            vehicule = $('#ikvehicule'),
            vehiculeId = vehicule.val(),
            marque = -1,
            modele = '',
            immatricule = '',
            typeVehicule = -1,
            ndfTypeVehicule = -1,
            carburant = -1,
            puissance = '',
            newVehicule = true;


        if($(this).attr('id') === 'vehicule-edit') {
            if (parseInt(vehiculeId) !== -1) {
                $('#vehicule-id').val(vehiculeId);
                var option = vehicule.find('option[value="' + vehiculeId+ '"]');
                marque = option.attr('data-marque');
                modele = option.attr('data-modele');
                immatricule = option.attr('data-immatricule');
                typeVehicule = option.attr('data-typevehicule');
                ndfTypeVehicule = option.attr('data-ndftypevehicule');
                carburant = option.attr('data-carburant');
                puissance = option.attr('data-puissance');
                newVehicule = false;

                $('#marque').val(marque);
                $('#modele').val(modele);
                $('#immatricule').val(immatricule);
                $('#typevehicule').val(typeVehicule);
                $('#ndftypevehicule').val(ndfTypeVehicule);
                $('#carburant').val(carburant);
                $('#puissance').val(puissance);
            }
        }

        if(newVehicule) {
            $('#vehicule-id').val(-1);
            $('#marque').val(-1);
            $('#modele').val('');
            $('#immatricule').val('');
            $('#typevehicule').val(-1);
            $('#ndftypevehicule').val(-1);
            $('#carburant').val(-1);
            $('#puissance').val('');

            $('#vehicule-image-id').val(getImageId());
        }

        modal.modal('show');
    });

    $(document).on('click', '#btn-vehicule-valider',function(event){
        event.preventDefault();
        event.stopPropagation();

        var formVehicule = $('#form-vehicule'),
            vehicule = $('#ikvehicule');


        $.ajax({
            url: Routing.generate('tenu_su_valider_vehicule'),
            type: 'POST',
            data: formVehicule.serialize(),
            success: function (data) {
                show_info('', data.message, data.type);

                if (data.type === 'success') {
                    if (data.action === 'insert') {
                        vehicule.append(data.option);
                    }
                    else if (data.action === 'update') {
                        var option = vehicule.find('option[value="' + data.id + '"]');
                        option.remove();
                        vehicule.append(data.option);
                    }
                    vehicule.val(data.id);
                    $('#vehicule-modal').modal('hide');
                }
                else{
                    show_info('', data.error, 'error');
                }
            }
        });

    });

    $(document).on('click', '#saisie-content .collapse-link', function(){
        var ibox = $(this).closest('div.ibox'),
            button = $(this).find('i'),
            content = ibox.find('div.ibox-content');

        content.slideToggle(200);
        button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        ibox.toggleClass('').toggleClass('border-bottom');
        setTimeout(function () {
            ibox.resize();
            ibox.find('[id^=map-]').resize();
        }, 50);
    });

    $(document).on('change', '#type-recherche', function(event){
        event.preventDefault();
        event.stopPropagation();

        $('.recherche').addClass('hidden');

        if(parseInt($(this).val()) === 1 || parseInt($(this).val()) === 2){
            $('#recherche-libelle-container').removeClass('hidden');
        }
        else if(parseInt($(this).val()) === 0){
            $('#recherche-tiers-container').removeClass('hidden');
        }
    });

    $(document).on('change', '#type-recherche', function(event){
        event.stopPropagation();
        event.preventDefault();

        if(parseInt($(this).val()) === 2){
            $('#recherche-libelle').val($('#siren').val());
        }
    });

    $(document).on('click', '#recherche-ecriture', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var typeRechercheVal = $('#type-recherche').val(),
            typeRecherche = '',
            champ = '',
            ecritureDossierGrid = $('#ecriture-dossier');

        if (parseInt(typeRechercheVal) === 1 || parseInt(typeRechercheVal) === 2) {
            typeRecherche = 'libelle';
            champ = $('#recherche-libelle').val();
            if(parseInt(typeRechercheVal) === 2){
                typeRecherche = 'siren';

                if(!isSiren(champ)){
                    show_info('','Siren invalide', 'warning');
                    return false;
                }
            }

        }
        else if(parseInt(typeRechercheVal) === 0){
            typeRecherche = 'tiers';
            champ = $('#recherche-tiers').val();
        }
        else{
            show_info('', 'Il faut choisir le type de recherche', 'warining');
            return false;
        }

        if (typeRecherche !== '') {
            var url = Routing.generate('tenu_su_ecriture_dossier', {typeRecherche: typeRecherche});
            ecritureDossierGrid.jqGrid('setGridParam', {
                    url: url,
                    postData: {
                        imageid: getImageId(),
                        champ: champ
                    },
                    datatype: 'json'
                }
            ).trigger('reloadGrid', {fromServer: true, page: 1});
        }
    });

    //GRID

    $(document).on('click', '.btn-pcc', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var modal = $('#ndf-caisse-td-modal'),
            btnPcc = $(this),
            categorie = btnPcc.attr('data-categorie'),
            id = btnPcc.closest('tr').attr('id'),
            typePcc = btnPcc.attr('data-type'),
            data = null,
            url = null,
            grid = null,
            ndfGrid = $('#ndf-details'),
            venteComptoirGrid = $('#vente-comptoir-details'),
            caisseGrid = $('#caisse-details'),
            imageid = getImageId()
        ;


        if (categorie === 'ndf') {
            grid = ndfGrid;

            url = Routing.generate('tenu_su_td_ndf_pcg');

            var sousnature = getCellValue(id, 'ndf_categorie');

            data = {
                imageid: imageid,
                sousnature: sousnature,
                nbcouvert: getCellValue(id, 'ndf_nbre_couvert'),
                distance: getCellValue(id, 'ndf_distance'),
                typepcc: typePcc
            };

            if (sousnature === undefined)
                return false;
        }
        else if (categorie === 'vc') {
            grid = venteComptoirGrid;
            var caissenature = undefined;

            url = Routing.generate('tenu_su_td_vc_pcg');
            if (parseInt($(venteComptoirGrid.jqGrid('getInd', id, true)).attr('editable')) === 1) {
                caissenature = getCellValue(id, 'vc_caisse_nature');

            }
            if (caissenature === undefined)
                return false;

            data = {
                imageid: imageid,
                caissenatureid: caissenature,
                typepcc: typePcc
            };

        }
        else if (categorie === 'c') {
            grid = caisseGrid;

            if (parseInt($(caisseGrid.jqGrid('getInd', id, true)).attr('editable')) === 1) {

                var entreeSortie = getCellValue(id, 'c_es');

                if(parseInt(entreeSortie) === 1){
                    caissenature = getCellValue(id, 'c_caisse_nature_e');
                    url = Routing.generate('tenu_su_td_vc_pcg');
                    data = {
                        imageid: imageid,
                        caissenatureid: caissenature,
                        typepcc: typePcc
                    };

                    if (caissenature === undefined)
                        return false;
                }
                else if(parseInt(entreeSortie) === 0){
                    caissenature = getCellValue(id, 'c_caisse_nature_s');
                    url = Routing.generate('tenu_su_td_vc_pcg');
                    data = {
                        imageid: imageid,
                        caissenatureid: caissenature,
                        typepcc: typePcc
                    };

                    if (caissenature === undefined)
                        return false;
                }
            }
        }

        if(url === null)
            return false;

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            dataType: 'json',
            success: function (data) {

                if(data.type !== undefined) {
                    if (data.type === 'error') {
                        show_info('', data.message, data.type);
                        return;
                    }
                }

                var ndfCaisseTree = $('#ndf-caisse-tree');
                ndfCaisseTree.jstree('destroy');
                ndfCaisseTree.jstree({'core': data})
                    .bind('dblclick.jstree', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        var CurrentNode = $(this).jstree('get_selected'),
                            selectId = 0;

                        try {
                            selectId = $(this).jstree().get_selected(true)[0].id;
                        }
                        catch (e) {
                        }

                        if (selectId.indexOf('pcg') >= 0) {
                            return false;
                        }

                        var tmp = selectId.split('_'),
                            selectText = $('#' + CurrentNode).text();

                        selectId = parseInt(tmp[1]);
                        if (!isNaN(selectId) && selectId !== 0) {
                            setNdfCaissePcc(grid, id, selectId, selectText.split('-')[0], typePcc, categorie);
                        }
                    });
                modal.modal('show');
            }
        });
    });


    $(document).on('click', '#btn-add-ndf, #btn-add-vc, #btn-add-caisse', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var ndfGrid = $('#ndf-details'),
            venteComptoirGrid = $('#vente-comptoir-details'),
            caisseGrid = $('#caisse-details'),
            grid = ndfGrid;

        if ($(this).attr('id') === 'btn-add-vc') {
            grid = venteComptoirGrid;
        }
        else if ($(this).attr('id') === 'btn-add-caisse') {
            grid = caisseGrid;
        }

        addGridRow(grid);
    });

    $(document).on('click', '.ndf-save, .vc-save, .c-save', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var lastsel = lastsel_ndf,
            ndfGrid = $('#ndf-details'),
            venteComptoirGrid = $('#vente-comptoir-details'),
            caisseGrid = $('#caisse-details'),
            grid = ndfGrid;

        if ($(this).hasClass('vc-save')) {
            lastsel = lastsel_vc;
            grid = venteComptoirGrid;
        }
        else if ($(this).hasClass('c-save')) {
            lastsel = lastsel_c;
            grid = caisseGrid;
        }

        if (grid.find('tr[id='+lastsel+']').attr('editable') !== '1') {
            return;
        }

        grid.jqGrid('saveRow', lastsel, {
            "aftersavefunc": function (rowid, response) {
                reloadNdfCaisseGrid(grid, getImageId());
                show_info('', response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.ndf-delete, .c-delete, .vc-delete', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id'),
            imageid = getImageId(),
            grid = null;

        if($(this).hasClass('ndf-delete')){
            grid = $('#ndf-details');
        }
        else if($(this).hasClass('vc-delete')){
            grid = $('#vente-comptoir-details');
        }
        else if($(this).hasClass('c-delete')){
            grid = $('#caisse-details');
        }

        if (rowid === 'new_row') {
            $(this).closest('tr').remove();
            return;
        }

        if(grid === null)
            return false;

        if(rowid === undefined)
            return false;

        grid.jqGrid('delGridRow', rowid, {
            url: Routing.generate('tenu_su_ndf_caisse_details_delete', {imageid: imageid}),
            top: 200,
            left: 400,
            width: 400,
            mtype: 'DELETE',
            closeOnEscape: true,
            modal: true,
            msg: 'Supprimer cette ligne?'
        });
    });

    $(document).on('click', '.btn-ik', function(event){
        event.preventDefault();
        event.stopPropagation();


        var modal = $('#ik-modal'),
            ikVehicule = $('#ikvehicule'),
            tr = $(this).closest('tr'),
            rowid = tr.attr('id'),
            vehiculeid = tr.find('input[name="ndf_vehicule_ik"]').val(),
            periodeDebut = tr.find('input[name="ndf_periode_deb_ik"]').val(),
            periodeFin = tr.find('input[name="ndf_periode_fin_ik"]').val(),
            trajet = tr.find('input[name="ndf_trajet_ik"]').val(),
            btnik = $('#btn-ik-valider'),
            ndfGrid = $('#ndf-details')
        ;


        if($(ndfGrid.jqGrid('getInd', rowid, true)).attr('editable') !== '1') {
            return false;
        }

        $.ajax({
            url: Routing.generate('tenu_su_vehicule'),
            data: {
                imageid: getImageId()
            },
            type: 'GET',
            success: function(data){
                ikVehicule.html(data);

                $('#iktrajet').val(trajet);
                $('#ikperiodedu').val(periodeDebut);
                $('#ikperiodeau').val(periodeFin);
                ikVehicule.val(vehiculeid);

                btnik.attr('data-row-id', rowid);

                $('.input-daterange').datepicker({
                    format:'dd/mm/yyyy',
                    language: 'fr',
                    autoclose:true,
                    startView: 1
                });

                modal.modal('show');
            }
        })
    });

    $(document).on('click', '#btn-ik-valider', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var id = $(this).attr('data-row-id'),
            ndfGrid = $('#ndf-details'),
            tr = ndfGrid.find('tr[id="' + id + '"]'),
            spanik = tr.find('.btn-ik'),
            trajetVal = $('#iktrajet').val(),
            vehiculeCell = tr.find('input[name="ndf_vehicule_ik"]'),
            exerciceVal = $('#annee').val(),
            vehiculeVal = $('#ikvehicule').val(),
            periodeDebCell = tr.find('input[name="ndf_periode_deb_ik"]'),
            periodeDebVal = $('#ikperiodedu').val(),
            periodeFinCell = tr.find('input[name="ndf_periode_fin_ik"]'),
            periodeFinVal = $('#ikperiodeau').val(),
            trajetCell = tr.find('input[name="ndf_trajet_ik"]').val(trajetVal);

        if(trajetVal !== '') {
            spanik.html(trajetVal + ' km');
            vehiculeCell.val(vehiculeVal);
            periodeDebCell.val(periodeDebVal);
            periodeFinCell.val(periodeFinVal);
            trajetCell.val(trajetVal);


            if(parseInt(vehiculeVal) !== -1 && trajetVal !== '') {
                $.ajax({
                    url: Routing.generate('tenu_su_calcul_ik'),
                    type: 'GET',
                    data: {
                        trajet: trajetVal,
                        vehiculeid: vehiculeVal,
                        exercice: exerciceVal
                    },
                    success: function(data){
                        tr.find('input[name="ndf_ttc_devise"]').val(data);
                        tr.find('input[name="ndf_ttc"]').val(data);
                        tr.find('select[name="ndf_devise"]').val(1);

                        setMontantTvaHt(id, 'ndf');
                    }
                });
            }
            else{
                show_info('','Il faut choisir un vehicule', 'error');
            }
        }
        else{

            spanik.html('Détails IK');
            vehiculeCell.val(-1);
            periodeDebCell.val('');
            periodeFinCell.val('');
            trajetCell.val('');
        }
        $('#ik-modal').modal('hide');
    });

    $(document).on('change', '#num-cb', function(event){
       event.preventDefault();
       event.stopPropagation();

       var carteBleuBanqueCompteId = $(this).val();

       $.ajax({
           url: Routing.generate('tenue_su_cartebleu_banquecompte'),
           type: 'GET',
           data: { carteBleuBanqueCompte: carteBleuBanqueCompteId },
           success: function(data){
               $('#banque').val(data.banque);
               $('#num-cpt').val(data.numcpt);
           }
       });
    });

    $(document).on('blur', '#montant-paye', function(event){
       event.preventDefault();
       event.stopPropagation();

       var montantPaye = $(this).val(),
           dateReglement = $('#date-reglement').val();

       $.ajax({
           url: Routing.generate('tenu_su_check_rel_cb'),
           type: 'GET',
           data:{
               montantPaye: montantPaye,
               dateReglement: dateReglement,
               imageId: getImageId()
           },
           success: function(data){
                   $('#trouve-rel-cb').val(data.trouve);
           }
       })
    });

    $(document).on('change', '#mode-reglement', function(event){
        event.preventDefault();
        event.stopPropagation();

        var modeReglement = $(this).val(),
            numCb = $('#num-cb')
        ;

        if(parseInt(modeReglement) === 1){
            numCb.attr('disabled', false);
        }
        else{
            numCb.attr('disabled',true);
        }
    });

    $(document).on('click', '#btn-add-ecriture-recap', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var ecritureRecapGrid = $('#ecriture-recap');

        addGridRow(ecritureRecapGrid);
    });

    $(document).on('click', '.e-save', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var lastsel = lastsel_ecriture_recap,
            ecritureRecapGrid = $('#ecriture-recap');


        if (ecritureRecapGrid.find('tr[id='+lastsel+']').attr('editable') !== '1') {
            return;
        }

        ecritureRecapGrid.jqGrid('saveRow', lastsel, {
            "aftersavefunc": function (rowid, response) {
                ecritureRecapGrid.setGridParam({
                    postData: {
                        imageid: getImageId()
                    },
                    datatype: 'json',
                    loadonce: true,
                    page: 1
                }).trigger('reloadGrid');

                show_info('', response.responseJSON.message, response.responseJSON.type);
            }
        });
    });

    $(document).on('click', '.e-delete', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var rowid = $(this).closest('tr').attr('id'),
            ecritureRecapGrid = $('#ecriture-recap');


        if (rowid === 'new_row') {
            $(this).closest('tr').remove();
            return false;
        }

        if(rowid.includes('e_picdoc_'))
            return false;

        if(ecritureRecapGrid === null)
            return false;

        if(ecritureRecapGrid === undefined)
            return false;

        swal({
            title: 'Attention',
            text: "Voulez vous supprimer cette ligne?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confimer',
            cancelButtonText: 'Annuler'
        }).then(function () {
                $.ajax({
                    url: Routing.generate('tenu_su_ecriture_recap_delete'),
                    type: 'DELETE',
                    data: { id: rowid},
                    success: function(data){
                        if(data.type === 'success'){

                            show_info('', data.message, data.type);

                            ecritureRecapGrid.setGridParam({
                                postData: {
                                    imageid: getImageId()
                                },
                                datatype: 'json',
                                loadonce: true,
                                page: 1
                            }).trigger('reloadGrid');
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


    $(document).on('click', '#btn-import', function(event){
       event.preventDefault();
       event.stopPropagation();

       var dossier = $('#dossier').val(),
           exercice = $('#exercice').val(),
           importModal = $('#import-modal')
       ;

       if(dossier === '-1' || dossier === '' ||
       exercice === '' || exercice === -1){
           show_info('','Il faut choisr un dossier et l\'exercice', 'warning');
           return false;
       }
       importModal.modal('show');
    });
});

function initFileInput() {
    var excel = $('#excel');

    excel.fileinput({
        language: 'fr',
        theme: 'fa',
        uploadAsync: false,
        showPreview: false,
        showUpload: true,
        showRemove: false,
        fileTypeSettings: {
            text: function (vType, vName) {
                return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(xls|xlsx)$/i);
            }
        },
        allowedFileTypes: ['image', 'text', 'pdf'],
        uploadUrl: Routing.generate('tenu_su_import'),
        uploadExtraData: function() {
            return {
                dossierId: $('#dossier').val(),
                exercice: $('#exercice').val(),
                categorieId: $('#categorie-import').val()
            };
        }
    });
    excel.on('filebatchuploadsuccess', function(event, data, previewId, index) {
        show_info('Import', data.response.message, data.response.type);
        if(data.response.type === 'success') {
            $('#import-modal').modal('hide');
        }
    });
}


function setDossiers(clientid){
    $.ajax({
        url: Routing.generate('tenue_su_dossier'),
        type: 'GET',
        data: {clientid: clientid},
        success: function (data) {
            $('.filtre-dossier').html(data);
        }
    })
}

function setExercices(dossierid, selector){
    $.ajax({
        url: Routing.generate('tenue_su_exercice'),
        type: 'GET',
        data: {dossierid: dossierid},
        success: function(data){

            var filtre = selector.closest('.form-group'),
                dossier = filtre.find('.filtre-dossier').val(),
                status = filtre.find('.filtre-statut').val(),
                categorie = filtre.find('.filtre-categorie').val(),
                souscategorie = filtre.find('.filtre-souscategorie').val(),
                soussouscategorie = filtre.find('.filtre-soussouscategorie').val(),
                exercice = filtre.find('.filtre-exercice');

            $('.filtre-exercice').html(data);

            if(souscategorie === null)
                souscategorie = -1;

            if(soussouscategorie === null)
                soussouscategorie = -1;

            if(parseInt(dossier)!== -1 && exercice !== '') {

                // setStatut(dossier, exercice.val());
                // setCategorie(dossier, exercice.val(), status);
            }

        }
    })
}

function setSouscategories(categorieid){
    $.ajax({
        url: Routing.generate('tenue_su_souscategorie', {toutes: 1}),
        type: 'GET',
        data: {categorieid: categorieid},
        success: function(data){
            var souscategorie = $('.filtre-souscategorie'),
                souscategorieVal = -1;

            souscategorie.html(data);

            souscategorie.each(function(){
               souscategorieVal = $(this).val();
               return true;
            });

            setSoussouscategories(souscategorieVal, undefined, undefined);
        }
    })
}

function setRecSouscategories(categorieid, souscategorieid){
    if(parseInt(categorieid) !== -1) {
        $.ajax({
            url: Routing.generate('tenue_su_souscategorie', {toutes: 0}),
            type: 'GET',
            data: {categorieid: categorieid},
            success: function (data) {
                var souscategorie = $('#rec-souscategorie'),
                    soussouscategorieid = $('#rec-soussouscategorie').val()
                ;
                souscategorie.html(data);
                souscategorie.val(souscategorieid);

                setRecSoussouscategories(souscategorie.val(), soussouscategorieid);
            }
        })
    }
}

function setRecSoussouscategories(souscategorieid, soussouscategorieid){

    if(parseInt(souscategorieid) !== -1) {
        $.ajax({
            url: Routing.generate('tenue_su_soussouscategorie', {toutes: 0}),
            type: 'GET',
            data: {souscategorieid: souscategorieid},
            success: function (data) {
                var soussouscategorie = $('#rec-soussouscategorie');
                soussouscategorie.html(data);
                soussouscategorie.val(soussouscategorieid);
            }
        })
    }
}

function setSoussouscategories(souscategorieid, selector, soussouscategorieid){

    var toutes = 0;
    if(selector === undefined){
        toutes = 1;
    }

    $.ajax({
        url: Routing.generate('tenue_su_soussouscategorie', {toutes: toutes}),
        type: 'GET',
        data: {souscategorieid: souscategorieid},
        success: function(data){
            if(selector === undefined) {
                var soussouscategorie = $('.filtre-soussouscategorie'),
                    soussouscategorieVal = -1,
                    dossierid = -1,
                    status = -1,
                    categorie = -1,
                    souscategorie = -1,
                    exercice = -1;

                soussouscategorie.html(data);

                soussouscategorie.each(function(){
                   soussouscategorieVal = $(this).val();

                   var filtre = $(this).closest('.form-group');
                   dossierid = filtre.find('.filtre-dossier').val();
                   status = filtre.find('.filtre-statut').val();
                   categorie = filtre.find('.filtre-categorie').val();
                   souscategorie = filtre.find('.filtre-souscategorie').val();
                   exercice = filtre.find('.filtre-exercice').val();

                   return true;
                });

            }
            else{
                selector.html(data);
                if(soussouscategorieid !== undefined){
                    selector.val(soussouscategorieid);
                }
            }
        }
    })
}


function setPccDossier(souscategorieid, selector, pccid){
    $.ajax({
        url: Routing.generate('tenue_su_pcc_dossier'),
        type: 'GET',
        data: {
            souscategorieid: souscategorieid,
            pccid: pccid,
            imageid: getImageId()
        },
        success: function(data) {
            selector.html(data);
            if (pccid !== undefined) {
                selector.val(pccid);
            }
            selector.trigger("chosen:updated");
        }
    })
}

function setSelectsByOrganisme(organismeid, categorieid, selector, value, select){
    $.ajax({
        url: Routing.generate('tenu_su_select_organisme', {select: select}),
        type: 'GET',
        data: {
            organismeid: organismeid ,
            categorieid: categorieid
        },
        success: function (data) {
            selector.html(data);
            if (value !== undefined)
                selector.val(value);
        }
    })
}

function setSousnature(natureid, selector, sousnature){
    $.ajax({
        url: Routing.generate('tenu_su_sousnature'),
        type: 'GET',
        data:{
            natureid: natureid
        },
        success: function(data){
            selector.html(data);
            if(sousnature !== undefined){
                selector.val(sousnature)
            }
        }

    });
}

function setStatut(dossierid, exercice){
    $.ajax({
        url: Routing.generate('tenu_su_statut'),
        type: 'GET',
        data:{
            dossierid: dossierid,
            exercice: exercice
        },
        success: function (data) {
            var filtreStatut = $('.filtre-statut');
            filtreStatut.html(data);
            setCategorie(dossierid, exercice, filtreStatut.val());
        }
    });
}

function setCategorie(dossierid, exercice, status){
    $.ajax({
        url: Routing.generate('tenu_su_categorie'),
        type: 'GET',
        data:{
            dossierid: dossierid,
            exercice: exercice,
            status: status
        },
        success: function(data){
            $('.filtre-categorie').html(data);
        }
    })
}


function SetModalHeight(selector) {
    var winHeight = $(window).height(),
        modal = $('#' + selector),
        headearHeight = modal.find('.modal-header .ibox-title').height();

    modal.find('.modal-body').height(winHeight - headearHeight - 60);

}

function ShowImageDetails(imageid) {

    $('#btn-categorie').removeClass('active');

    $.ajax({
        url: Routing.generate('tenu_su_data_image'),
        type: 'GET',
        data: {
            imageid: imageid
        },
        success: function (data) {
            var saisieContent = $('#saisie-content');

            saisieContent.html(data);

            showEcritureRecap(imageid);

            $.ajax({
                url: Routing.generate('tenu_su_tiers'),
                type: 'GET',
                data: {imageid: imageid},
                success: function(data){
                    var rechercheTiers = $('#recherche-tiers');
                    rechercheTiers.html(data);
                    rechercheTiers.chosen();
                    rechercheTiers.trigger("chosen:updated");
                }
            });

            $.ajax({
                url: Routing.generate('tenu_su_image'),
                type: 'GET',
                data: {
                    imageid: imageid
                },
                success: function (data) {
                    PDFObject.embed(data, "#pdf");
                    var modalBody = $('#su-modal').find('.modal-body'),
                        modalBodyHeight = modalBody.height(),
                        pdf = $('#pdf')
                    ;

                    if (!pdf.hasClass('ndf-caisse')) {
                        pdf.height(modalBodyHeight - 5);
                    }
                    else {
                        pdf.height(modalBodyHeight / 2);
                    }

                }
            });

            $('#date-echeance').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('#date-fact').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('#date-reglement').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('#date-livraison').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('#periode-debut').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('#periode-fin').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});
            $('.date-ecriture').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});

            var siren = $('#siren').val(),
                url = Routing.generate('tenu_su_ecriture_dossier', {typeRecherche: 'siren'}),
                urlRecap = Routing.generate('tenu_su_ecriture_recap'),
                editUrlRecap = Routing.generate('tenu_su_ecriture_recap_edit',{
                    imageid: imageid
                }),

                ecritureDossierGrid = $('#ecriture-dossier'),
                ecritureRecapGrid = $('#ecriture-recap'),
                ndfGrid = $('#ndf-details'),
                urlNdf = Routing.generate('tenu_su_ndf_caisse_details', {categorie: 'ndf'}),
                editUrlNdf = Routing.generate('tenu_su_ndf_caisse_details_edit', {imageid: imageid, categorie: 'ndf'}),
                venteComptoirGrid = $('#vente-comptoir-details'),
                urlVenteComptoir = Routing.generate('tenu_su_ndf_caisse_details', {categorie: 'vc'}),
                editUrlVenteComptoir = Routing.generate('tenu_su_ndf_caisse_details_edit', {
                    imageid: imageid,
                    categorie: 'vc'
                }),
                caisseGrid = $('#caisse-details'),
                urlCaisse = Routing.generate('tenu_su_ndf_caisse_details', {categorie: 'c'}),
                editUrlCaisse = Routing.generate('tenu_su_ndf_caisse_details_edit', {
                    imageid: imageid,
                    categorie: 'c'
                }),
                modalBody = $('#su-modal').find('.modal-body'),
                modalBodyHeight = modalBody.height(),
                ecritures = $('.form-horizontal.ecriture'),
                btnInfoPerdos = $('#btn-infoperdos'),
                formSocial = $('#form-social'),
                formFiscal = $('#form-fiscal');

            btnInfoPerdos.removeClass('blink');

            if (parseInt($('#with-instruction').val()) === 1) {
                btnInfoPerdos.addClass('blink');
            }

            $('.chosen').chosen();

            ecritures.each(function () {
                var souscategorie = $(this).find('.souscategorie-ecriture'),
                    souscategorieVal = souscategorie.val(),
                    soussouscategorie = $(this).find('.soussouscategorie-ecriture'),
                    soussouscategorieVal = soussouscategorie.val(),
                    nature = $(this).find('.nature'),
                    natureVal = nature.val(),
                    sousnature = $(this).find('.sousnature'),
                    sousnatureVal = sousnature.val(),
                    pccDossier = $(this).find('select[name="pccdossier"]'),
                    pccDossierVal = $(this).find('select[name="pccdossier"]').val()
                ;

                setSoussouscategories(souscategorieVal, soussouscategorie, soussouscategorieVal);
                setSousnature(natureVal, sousnature, sousnatureVal);

                setPccDossier(souscategorieVal, pccDossier, pccDossierVal);
            });

            if (formSocial.length > 0 || formFiscal.length > 0) {
                var organismeid = $('#organisme').val(),
                    nature = $('#nature'),
                    sousnature = $('#sousnature'),
                    souscategorie = $('#fs-souscategorie'),
                    soussouscategorie = $('#fs-soussouscategorie'),
                    categorieid = -1,
                    formId = $(this).closest('form').attr('id');

                if (formId === 'form-social') {
                    categorieid = 20;
                }
                else {
                    categorieid = 21;
                }
                setSelectsByOrganisme(organismeid, categorieid, nature, nature.val(), 'nature');
                setSelectsByOrganisme(organismeid, categorieid, sousnature, sousnature.val(), 'sousnature');
                setSelectsByOrganisme(organismeid, categorieid, souscategorie, souscategorie.val(), 'souscategorie');
                setSelectsByOrganisme(organismeid, categorieid, soussouscategorie, soussouscategorie.val(), 'soussouscategorie');
            }


            var dataimage = $('#data-image');

            dataimage.height(modalBodyHeight - 5);

            ecritureDossierGrid.jqGrid({
                datatype: 'json',
                loadonce: true,
                sortable: true,
                shrinkToFit: true,
                viewrecords: true,
                hidegrid: false,
                rownumbers: true,
                rownumWidth: 30,
                footerrow: true,
                userDataOnFooter: true,
                url: url,
                mtype: 'POST',
                postData: {
                    imageid: imageid,
                    champ: siren
                },
                caption: 'Ecritures du Siren dans ce dossier',
                colNames: [
                    'Résultat', 'TVA', 'Bilan', 'Occ', 'Image', 'Image id', ''
                ],
                colModel: [
                    {
                        name: 'd_resultat',
                        index: 'd_resultat',
                        align: 'left',
                        editable: true,
                        sortable: true
                    },
                    {
                        name: 'd_tva',
                        index: 'd_tva',
                        align: 'left',
                        editable: true,
                        sortable: true
                    },
                    {
                        name: 'd_bilan',
                        index: 'd_bilan',
                        align: 'left',
                        editable: true,
                        sortable: true
                    },
                    {
                        name: 'd_occurence',
                        index: 'd_occurence',
                        align: 'left',
                        editable: true,
                        sortable: true,
                        width: 40
                    },
                    {
                        name: 'd_image',
                        index: 'd_image',
                        align: 'left',
                        editable: true,
                        sortable: true,
                        width: 100
                    },
                    {
                        name: 'd_image_id',
                        index: 'd_image_id',
                        align: 'left',
                        editable: true,
                        sortable: true,
                        hidden: true
                    },
                    {
                        name: 'd_check',
                        index: 'd_check',
                        align: 'center',
                        editoptions: {value: 'True:False'},
                        formatter: 'checkbox',
                        formatoptions: {disabled: false},
                        editable: true
                    }
                ],
                loadComplete: function () {
                    var containerWith = ecritureDossierGrid.closest('.col-lg-12').width();
                    ecritureDossierGrid.jqGrid('setGridWidth', containerWith);
                },
                beforeSelectRow: function (rowid, e) {
                    // var imageid = $('#image').val(),
                    var imageid = getImageId(),
                        iCol = $.jgrid.getCellIndex($(e.target).closest("td")[0]),
                        cm = $(this).jqGrid("getGridParam", "colModel"),
                        ecritureid = $('.ecriture-container').find('.ecriture').length + 1;
                    if (cm[iCol].name === "d_check" && e.target.tagName.toUpperCase() === "INPUT") {
                        addEcriture(imageid, ecritureid, rowid, undefined, undefined);
                    }

                    return true;
                }


            });


            ecritureRecapGrid.jqGrid({
                datatype: 'json',
                loadonce: true,
                sortable: true,
                shrinkToFit: true,
                viewrecords: true,
                hidegrid: false,
                rownumbers: true,
                rownumWidth: 30,
                footerrow: true,
                userDataOnFooter: true,
                url: urlRecap,
                editurl: editUrlRecap,
                mtype: 'POST',
                postData: {
                    imageid: imageid
                },
                caption: 'Ecritures Recap',
                colNames: [
                    'Date', 'Compte',  'Libelle', 'Débit', 'Crédit', 'Action'
                ],
                colModel: [
                    {
                        name: 'e_date',
                        index: 'e_date',
                        align: 'center',
                        editable: true,
                        editoptions: {
                            dataInit: function (el) {
                                $(el).datepicker({
                                    format: 'dd/mm/yyyy',
                                    language: 'fr',
                                    autoclose: true,
                                    startView: 1
                                });
                            }
                        },
                        width: 100,
                        formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                        sorttype: 'date'
                    },
                    {
                        name: 'e_compte',
                        index: 'e_compte',
                        align: 'left',
                        editoptions:{
                            dataUrl: Routing.generate('tenue_su_all_pcc_dossier'),
                            postData: function () {
                                return {
                                    imageid: getImageId()
                                }
                            }
                        },
                        edittype: 'select',
                        editable: true,
                        sortable: true
                    },
                    {
                        name: 'e_libelle',
                        index: 'e_libelle',
                        align: 'left',
                        editable: true,
                        sortable: true
                    },
                    {
                        name: 'e_debit',
                        index: 'e_debit',
                        align: 'right',
                        editable: true,
                        sortable: true,
                        formatter: 'number',
                        sorttype: 'number'
                    },
                    {
                        name: 'e_credit',
                        index: 'e_credit',
                        align: 'right',
                        editable: true,
                        sortable: true,
                        formatter: 'number',
                        sorttype: 'number'
                    },
                    {
                        name: 'e_action',
                        index: 'e_action',
                        align: 'center',
                        editable: false,
                        sortable: false,
                        editoptions: {
                            defaultValue: '<i class="fa fa-save icon-action e-save" title="Enregistrer"></i><i class="fa fa-trash icon-action e-delete" title="supprimer"></i>'
                        }
                    }
                ],
                loadComplete: function (data) {

                    if(parseInt(data.typeecriture) > 0) {
                        if ($('#btn-add-ecriture-recap').length === 0) {
                            ecritureRecapGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                                '<button id="btn-add-ecriture-recap" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
                        }
                    }


                    ecritureRecapGrid.jqGrid('setCaption', data.title);

                    lastsel_ecriture_recap = -1;
                    var ids = ecritureRecapGrid.jqGrid('getDataIDs');
                    lastsel_ecriture_recap = ids[0];

                    var containerWith = ecritureRecapGrid.closest('.col-lg-12').width();
                    ecritureRecapGrid.jqGrid('setGridWidth', containerWith);
                },
                onCellSelect: function (rowid, icol, cellcontent, e) {
                    if (rowid && rowid !== lastsel_ecriture_recap) {
                        ecritureRecapGrid.restoreRow(lastsel_ecriture_recap);
                        lastsel_ecriture_recap = rowid;
                    }

                    if (!rowid.includes('e_picdoc_')) {
                        ecritureRecapGrid.editRow(rowid, false);
                    }
                },

                beforeSelectRow: function (rowid, e) {
                    var target = $(e.target);
                    var item_action = (target.closest('td').children('.icon-action').length > 0);
                    return !item_action;
                }
            });

            var isNdfCaisse = false;

            if (ndfGrid.length > 0) {

                isNdfCaisse = true;

                ndfGrid.jqGrid({
                    datatype: 'json',
                    loadonce: true,
                    sortable: true,
                    shrinkToFit: false,
                    viewrecords: true,
                    hidegrid: false,
                    rownumbers: true,
                    rownumWidth: 30,
                    footerrow: true,
                    userDataOnFooter: true,
                    url: urlNdf,
                    editurl: editUrlNdf,
                    mtype: 'POST',
                    postData: {
                        imageid: imageid
                    },
                    caption: 'Détails',
                    colNames: [
                        'Row id', 'Date', 'Catégorie', 'Mode Paiement', 'Nbre Couvert', 'G', 'Distance',
                        'IK', 'Trajet IK', 'Vehicule', 'Période Début', 'Période Fin','Pays', 'Devise',
                        'Taux TVA', 'TTC Devise', 'TTC', 'TVA', 'HT', 'E/T', 'TTC', 'PCC TTC ID', 'TVA', 'PCC TVA ID', 'HT', 'PCC HT ID','ACTION'
                    ],
                    colModel: [
                        {
                            name: 'ndf_row_id',
                            index: 'ndf_row_id',
                            align: 'left',
                            editable: true,
                            sortable: false,
                            hidden: true
                        },
                        {
                            name: 'ndf_date',
                            index: 'ndf_date',
                            align: 'center',
                            editable: true,
                            editoptions: {
                                dataInit: function (el) {
                                    $(el).datepicker({
                                        format: 'dd/mm/yyyy',
                                        language: 'fr',
                                        autoclose: true,
                                        startView: 1
                                    });
                                },
                                defaultValue: function() {
                                    var ids = ndfGrid.jqGrid('getDataIDs'),
                                        lastDate = '';

                                    if (ids.length > 0) {
                                        var id = ids[ids.length - 1],
                                            rowData = ndfGrid.jqGrid('getRowData', id);
                                        lastDate = rowData['ndf_date'];
                                    }
                                    return lastDate.split('/').reverse().join('-');
                                }

                            },
                            width: 100,
                            formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                            sorttype: 'date'
                        },
                        {
                            name: 'ndf_categorie',
                            index: 'ndf_categorie',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            with: 130,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_sousnature'),
                                postData: function () {
                                    return {
                                        natureid: 393,
                                        jqgrid: true
                                    }
                                },
                                dataInit: function (elem) {
                                    $(elem).width(120);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function(e){
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var tr =  $(this).closest('tr');
                                        setTdNdfSousnature(tr, imageid);

                                        var sousnature = tr.find('select[name="ndf_categorie"]'),
                                            spanik = tr.find('.btn-ik');

                                        checkTva(tr);

                                        spanik.removeClass('disabled');

                                        if(parseInt(sousnature.val()) !== 389){
                                            spanik.addClass('disabled');

                                            tr.find('input[name="ndf_trajet_ik"]').val('');
                                            tr.find('input[name="ndf_vehicule_ik"]').val(-1);
                                            tr.find('input[name="ndf_periode_deb_ik"]').val('');
                                            tr.find('input[name="ndf_periode_fin_ik"]').val('');
                                        }


                                    }
                                }]
                            }
                        },
                        {
                            name: 'ndf_mode_reglement',
                            index: 'ndf_mode_reglement',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 110,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_mode_reglement'),
                                dataInit: function (elem) {
                                    $(elem).width(100);
                                }
                            },
                            hidden: true
                        },
                        {
                            name: 'ndf_nbre_couvert',
                            index: 'ndf_nbre_couvert',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 110,
                            editoptions: {
                                value:{ '-1':'' , '1':'1 Participant' ,'2':'Sup à 1 Participant'},
                                dataInit: function (elem) {
                                    $(elem).width(100);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function(e){
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var tr =  $(this).closest('tr');
                                        setTdNdfSousnature(tr, imageid);
                                    }
                                }]
                            }
                        },
                        {
                            name: 'ndf_groupe',
                            index: 'ndf_groupe',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            width: 50,
                            edittype: 'select',
                            editoptions: {
                                value:{ '-1':'' , '1':'G' ,'0':'NG'},
                                dataInit: function (elem) {
                                    $(elem).width(40);
                                }
                            }
                        },
                        {
                            name: 'ndf_distance',
                            index: 'ndf_distance',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 110,
                            editoptions: {
                                value: {'-1': '', '0': 'Inférieur à 50Km', '1': 'Supérieur à 50Km'},
                                dataInit: function (elem) {
                                    $(elem).width(100);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function(e){
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var tr =  $(this).closest('tr');
                                        setTdNdfSousnature(tr, imageid);
                                    }
                                }]
                            }
                        },

                        {
                            name: 'ndf_ik',
                            index: 'ndf_ik',
                            align: 'left',
                            editable: false,
                            sortable: true,
                            classes: 'ndf-ik',
                            editoptions: {
                                defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-ik disabled" style="font-size: 10px !important;">Détails IK</span>'
                            }
                        },
                        {
                            name: 'ndf_trajet_ik',
                            index: 'ndf_trajet_ik',
                            editable: true,
                            hidden: true
                        },
                        {
                            name: 'ndf_vehicule_ik',
                            index: 'ndf_vehicule_ik',
                            editable: true,
                            hidden: true
                        },
                        {
                            name: 'ndf_periode_deb_ik',
                            index: 'ndf_periode_deb_ik',
                            editable: true,
                            hidden: true
                        },
                        {
                            name: 'ndf_periode_fin_ik',
                            index: 'ndf_periode_fin_ik',
                            editable: true,
                            hidden: true
                        },
                        {
                            name: 'ndf_pays',
                            index: 'ndf_pays',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 100,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_pays'),
                                dataInit: function (elem) {
                                    $(elem).width(90);
                                },
                                defaultValue: 'France'
                            }
                        },
                        {
                            name: 'ndf_devise',
                            index: 'ndf_devise',
                            align: 'left',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 80,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_devise'),
                                dataInit: function (elem) {
                                    $(elem).width(70);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var gridId = $(this).closest('tr').attr('id');
                                        setMontantEuro(gridId, 'ndf');
                                    }
                                }],
                                defaultValue: 'EURO'
                            }
                        },
                        {
                            name: 'ndf_tva_taux',
                            index: 'ndf_tva_taux',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            width: 70,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_tva_taux'),
                                dataInit: function (elem) {
                                    $(elem).width(60);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var gridId = $(this).closest('tr').attr('id');
                                        setMontantTvaHt(gridId, 'ndf');
                                    }
                                }]
                            }
                        },
                        {
                            name: 'ndf_ttc_devise',
                            index: 'ndf_tt_devise',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            editoptions: {
                                dataUrl: Routing.generate('tenu_su_devise'),
                                dataInit: function (elem) {
                                    $(elem).width(100);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var gridId = $(this).closest('tr').attr('id');
                                        setMontantEuro(gridId, 'ndf');
                                    }
                                }]
                            },
                            formatter: 'number',
                            sorttype: 'number',
                            width: 100
                        },
                        {
                            name: 'ndf_ttc',
                            index: 'ndf_ttc',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            editoptions: {
                                dataEvents: [{
                                    type: 'change',
                                    fn: function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var gridId = $(this).closest('tr').attr('id');
                                        setMontantTvaHt(gridId, 'ndf');
                                    }
                                }]
                            },
                            formatter: 'number',
                            sorttype: 'number',
                            width: 100
                        },
                        {
                            name: 'ndf_tva',
                            index: 'ndf_tva',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            formatter: 'number',
                            sorttype: 'number',
                            width: 100
                        },
                        {
                            name: 'ndf_ht',
                            index: 'ndf_ht',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            formatter: 'number',
                            sorttype: 'number',

                            width: 100
                        },
                        {
                            name: 'ndf_et',
                            index: 'ndf_et',
                            align: 'center',
                            editable: true,
                            sortable: true,
                            edittype: 'select',
                            editoptions: {
                                value: {'-1': '', '0': 'Engagement', '1': 'Tresorerie'},
                                dataInit: function (elem) {
                                    $(elem).width(100);
                                },
                                dataEvents: [{
                                    type: 'change',
                                    fn: function(e){
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var tr =  $(this).closest('tr');

                                        setPccByET(tr, $(this).val(), 'ndf');
                                    }
                                }]
                            }
                        },
                        {
                            name: 'ndf_pcc_ttc',
                            index: 'ndf_pcc_ttc',
                            align: 'right',
                            editable: false,
                            sortable: true,
                            classes: 'ndf-pcc',
                            editoptions: {
                                defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="ndf" data-type="ttc" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                            }
                        },
                        {
                            name: 'ndf_pcc_ttc_id',
                            index: 'ndf_pcc_ttc_id',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            hidden: true,
                            classes: 'ndf-pcc'
                        },
                        {
                            name: 'ndf_pcc_tva',
                            index: 'ndf_pcc_tva',
                            align: 'right',
                            editable: false,
                            sortable: true,
                            classes: 'ndf-pcc',
                            editoptions: {
                                defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="ndf" data-type="tva" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                            }
                        },
                        {
                            name: 'ndf_pcc_tva_id',
                            index: 'ndf_pcc_tva_id',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            hidden: true,
                            classes: 'ndf-pcc'
                        },
                        {
                            name: 'ndf_pcc_ht',
                            index: 'ndf_pcc_ht',
                            align: 'right',
                            editable: false,
                            sortable: true,
                            classes: 'ndf-pcc',
                            editoptions: {
                                defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="ndf" data-type="ht" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                            }

                        },
                        {
                            name: 'ndf_pcc_ht_id',
                            index: 'ndf_pcc_ht_id',
                            align: 'right',
                            editable: true,
                            sortable: true,
                            hidden: true,
                            classes: 'ndf-pcc'

                        },
                        {
                            name: 'ndf_action',
                            index: 'ndf_action',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 80,
                            fixed: true,
                            classes: 'ndf_action',
                            editoptions: {
                                defaultValue: '<i class="fa fa-save icon-action ndf-save" title="Enregistrer"></i><i class="fa fa-trash icon-action ndf-delete" title="supprimer"></i>'
                            }
                        }

                    ],
                    loadComplete: function () {

                        if ($('#btn-add-ndf').length === 0) {
                            ndfGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                                '<button id="btn-add-ndf" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
                        }

                        lastsel_ndf = -1;
                        var ids = ndfGrid.jqGrid('getDataIDs');
                        lastsel_ndf = ids[0];

                        var containerWith = ndfGrid.closest('.col-lg-12').width();
                        ndfGrid.jqGrid('setGridWidth', containerWith);
                    },
                    onCellSelect: function (rowid, icol, cellcontent, e) {
                        if (rowid && rowid !== lastsel_ndf) {
                            ndfGrid.restoreRow(lastsel_ndf);
                            lastsel_ndf = rowid;
                        }
                        var colName = ndfGrid.jqGrid('getGridParam', 'colModel')[icol].name,
                            colPccs = ['ndf_ik','ndf_pcc_ttc', 'ndf_pcc_tva', 'ndf_pcc_ht'];

                        if ($.inArray(colName, colPccs) === -1) {
                            ndfGrid.editRow(rowid, false);
                        }
                    },
                    beforeSelectRow: function (rowid, e) {
                        var target = $(e.target);
                        var item_action = (target.closest('td').children('.icon-action').length > 0);
                        return !item_action;
                    }
                }).contextMenu({
                    selector: '.jqgrow',
                    build: function ($trigger, e) {
                        var target = e.target,
                            tr = $(target).closest('tr.jqgrow');

                        if ($(target).hasClass('btn-pcc')) {
                            return {
                                callback: function (key, option) {
                                    var id = tr.attr('id'),
                                        typepcc = $(target).attr('data-type'),
                                        rowData = ndfGrid.jqGrid('getRowData', id),
                                        rowid = rowData['ndf_row_id']
                                    ;

                                    if (key === 'delete') {
                                        removeNdfCaissePcc(imageid, rowid, -1, typepcc, 'ndf');
                                    }
                                },
                                items: {
                                    delete: {name: "Supprimer compte", icon: "delete"}
                                }
                            }
                        }
                    }
                });
            }

            var typeCaisse = $('#typecaisse');
            if (typeCaisse.length > 0) {

                isNdfCaisse = true;

                var typeCaisseVal = typeCaisse.val();

                if (parseInt(typeCaisseVal) <= 0) {
                    initVenteComptoirGrid(venteComptoirGrid, imageid, urlVenteComptoir, editUrlVenteComptoir);
                }
                else {
                    initCaisseGrid(caisseGrid, imageid, urlCaisse, editUrlCaisse);
                }
            }

            var formResize = $('#form-resize');

            if(isNdfCaisse){
                dataimage.height(dataimage.height() / 2);
            }
            else{
                $('#pdf-resize').resizable({handles: 'e,w'});

                $(document).on('resize', '#pdf-resize', function () {

                    var ecritureDossierGrid = $('#ecriture-dossier'),
                        saisieContentWidth = $('#saisie-content').innerWidth(),
                        pdfResizeWidth = $('#pdf-resize').innerWidth(),
                        formResizeWidth = saisieContentWidth - pdfResizeWidth;

                        formResize.width(formResizeWidth - 40);
                        ecritureDossierGrid.jqGrid('setGridWidth', formResizeWidth - 40);
                });
            }
        }
    });
}

function setRsBySiren(siren){

    if(isSiren(siren)) {
        $.ajax({
            url: Routing.generate('tenu_su_siren'),
            type: 'GET',
            data: {
                siren: siren
            },
            success: function (data) {
                $('#rs').val(data.rs);

                var codePostal = $('#codepostal');
                if(codePostal.length > 0){
                    codePostal.val(data.codpos);
                }
            }
        });
    }
    else{
        show_info('Attention', 'siren invalide', 'warning');
    }
}

function isSiren(siren) {
    var estValide;
    if ( (siren.length !== 9) || (isNaN(siren)) )
        estValide = false;
    else {
        // Donc le SIREN est un numérique à 9 chiffres
        var somme = 0;
        var tmp;
        for (var cpt = 0; cpt<siren.length; cpt++) {
            if ((cpt % 2) === 1) { // Les positions paires : 2ème, 4ème, 6ème et 8ème chiffre
                tmp = siren.charAt(cpt) * 2; // On le multiplie par 2
                if (tmp > 9)
                    tmp -= 9;	// Si le résultat est supérieur à 9, on lui soustrait 9
            }
            else
                tmp = siren.charAt(cpt);
            somme += parseInt(tmp);
        }

        estValide = (somme % 10) === 0;
    }
    return estValide;
}

function setDate(stringDate) {
    var pattern = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/,
        arrayDate = stringDate.match(pattern);

    return new Date(arrayDate[3], arrayDate[2] - 1, arrayDate[1]);
}

function addEcriture(imageid, ecritureid, rowid, sousnatureid, model){
    var ids = rowid.split('-'),
        tiersid = parseInt(ids[1]),
        pccid = parseInt(ids[0]),
        tvaid = parseInt(ids[2]),

        canAddEcriture = true,
        ecritureContainer = $('.ecriture-container');

    if(tvaid === 0){
        tvaid = -1;

    }

    ecritureContainer.find('.form-horizontal').each(function (data) {
        var pccTiers = parseInt($(this).find('select[name="pcctiers"]').val()),
            pccTva = parseInt($(this).find('select[name="pcctva"]').val()),
            pccDossier = parseInt($(this).find('select[name="pccdossier"]').val());

        if(pccTiers === tiersid && pccTva === tvaid && pccid === pccDossier){
            canAddEcriture = false;
        }

    });

    if(canAddEcriture) {
        $.ajax({
            url: Routing.generate('tenu_su_ecriture'),
            type: 'GET',
            data: {
                imageid: imageid,
                ecritureid: ecritureid,
                rowid: rowid,
                sousnatureid: sousnatureid
            },
            success: function (data) {
                ecritureContainer.append(data);

                $('.chosen').chosen();
                $('.date-ecriture').datepicker({format: 'dd/mm/yyyy', language: 'fr', autoclose: true, startView: 1});

                //Copierna ny date na ny periode periode
                var ecriture = $('.ecriture-container').find('.form-horizontal').last(),
                    natureId = ecriture.find('.nature').val(),
                    sousnature = ecriture.find('.sousnature'),
                    periodeDebutEntete = $('#periode-debut').val(),
                    periodeFinEntete = $('#periode-fin').val(),
                    dateLivraisonEntete = $('#date-livraison').val(),
                    typeAchatVente = $('#type-av').val();

                ecriture.find('input[name="periodedebuttva"]').val(periodeDebutEntete);
                ecriture.find('input[name="periodefindtva"]').val(periodeFinEntete);
                ecriture.find('input[name="datelivraisontva"]').val(dateLivraisonEntete);
                ecriture.find('select[name="typevente"]').val(typeAchatVente);

                if(sousnatureid !== undefined){
                    setSousnature(natureId, sousnature, sousnatureid);
                }


                if(model !== undefined){

                    copyVal(ecriture, model, 'select[name="typevente"]');
                    copyVal(ecriture, model, 'select[name="nature"]');
                    copyVal(ecriture, model, 'select[name="sousnature"]');
                    copyVal(ecriture, model, 'select[name="souscategorie"]');
                    copyVal(ecriture, model, 'select[name="soussouscategorie"]');
                    copyVal(ecriture, model, 'input[name="datelivraisontva"]');
                    copyVal(ecriture, model, 'input[name="periodedebuttva"]');
                    copyVal(ecriture, model, 'input[name="periodefintva"]');
                    copyVal(ecriture, model, 'select[name="pccdossier"]');
                    copyVal(ecriture, model, 'select[name="pcctva"]');
                    copyVal(ecriture, model, 'select[name="pcctiers"]');

                }
            }
        });
    }
    else{
        show_info('','Cette ligne existe déjà', 'warning');
    }
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = number.toFixed(decimals);

    var nstr = number.toString();
    nstr += '';
    var x = nstr.split('.'),
        x1 = x[0],
        x2 = x.length > 1 ? dec_point + x[1] : '',
        rgx = /(\d+)(\d{3})/;

    while (rgx.test(x1))
        x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

    return x1 + x2;
}

function setNdfCaissePcc(grid, rowid, selectid, selecttext, typepcc, categorie){

    if(parseInt($(grid.jqGrid('getInd', rowid, true)).attr('editable')) === 1) {
        var span = grid.find('tr[id=' + rowid + ']').find('span[data-type=' + typepcc + ']');

        if (span.length > 0) {
            span.html(selecttext);
            setCellValue(grid, rowid, categorie + '_pcc_' + typepcc + '_id', selectid);
        }

        $('#ndf-caisse-td-modal').modal('hide');
    }
}

function removeNdfCaissePcc(imageid, rowid, pccid, typepcc, categorie){

    var grid = $('#ndf-details');

    if(categorie === 'vc'){
        grid = $('#vente-comptoir-details');
    }

    $.ajax({
        url: Routing.generate('tenu_su_ndf_caisse_details_pcc_edit'),
        type: 'POST',
        data: {
            typepcc: typepcc,
            imageid: imageid,
            rowid: rowid,
            pccid: pccid
        },
        success: function(data){
            show_info('',data.message, data.type);
            if(data.type === 'success'){
                reloadNdfCaisseGrid(grid, imageid);
            }
        }
    });
}

function setMontantEuro(rowId, gridName) {
    var ndfTtcDevise = $('#' + rowId + '_'+gridName+'_ttc_devise').val(),
        ndfDevise = $('#' + rowId + '_'+gridName+'_devise').find('option:selected').val();

    if (ndfTtcDevise !== '') {
        $.ajax({
            url: Routing.generate('tenu_su_calcul_devise'),
            type: 'GET',
            data: {
                montant: ndfTtcDevise,
                deviseid: ndfDevise
            },
            success: function (data) {
                $('#' + rowId + '_ndf_ttc').val(data);
                setMontantTvaHt(rowId, gridName);
            }
        })
    }
}

function setMontantTvaHt(rowId, gridName, e) {

    var montantTtc = $('#' + rowId + '_'+gridName+'_ttc').val(),
        ndftvaTaux = $('#' + rowId + '_'+gridName+'_tva_taux').find('option:selected').val();

    if(e !== undefined) {
        montantTtc = $('#' + rowId + '_' + gridName + '_ttc_' + e).val();
        ndftvaTaux = $('#' + rowId + '_' + gridName + '_tva_taux_' + e).find('option:selected').val();
    }

    if (montantTtc !== '') {
        $.ajax({
            url: Routing.generate('tenu_su_calcul_tva_ht'),
            type: 'GET',
            data: {
                montant: montantTtc,
                tvatauxid: ndftvaTaux
            },
            success: function (data) {
                if(e === undefined) {
                    $('#' + rowId + '_' + gridName + '_tva').val(data.tva);
                    $('#' + rowId + '_' + gridName + '_ht').val(data.ht);
                }
                else{
                    $('#' + rowId + '_' + gridName + '_tva_'+e).val(data.tva);
                    $('#' + rowId + '_' + gridName + '_ht_'+e).val(data.ht);
                }

                if(gridName === 'c'){
                    setSoldeFinal(rowId);
                }
            }
        })
    }
}


function setSoldeFinal(rowId){
    var soldeInit = $('#' + rowId + '_c_solde_init').val(),
        ttcE = $('#' + rowId + '_c_ttc_e').val(),
        ttcS = $('#' + rowId + '_c_ttc_s').val()
    ;
    if(soldeInit === '' || soldeInit === undefined){
        soldeInit = 0;
    }

    if(ttcE === '' || ttcE === undefined){
        ttcE = 0;
    }

    if(ttcS === '' || ttcS === undefined){
        ttcS = 0;
    }

    $('#'+rowId+'_c_solde_fin').val(parseFloat(soldeInit) + parseFloat(ttcE) - parseFloat(ttcS));

    return true;

}

function validerImage(imageid){
    $.ajax({
        url: Routing.generate('tenu_su_valider_image'),
        data: {imageid: imageid},
        type: 'GET',
        success: function(data){
            var allImage = $('#allimage'),
                span = allImage.find('span[data-id="'+imageid+'"]'),
                tr = span.closest('tr');
            tr.html(data);
            allImage.find('span[data-id="'+imageid+'"]').addClass('active');
        }
    })
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

        var initData = {};

        jqgrid.jqGrid('addRow', {
            rowID: 'new_row',
            initData: initData,
            position: 'last',
            useDefValues: true,
            useFormatter: true,
            addRowParams: {}
        });

    }
}

function reloadNdfCaisseGrid(grid, imageid) {
    grid.setGridParam({
        postData: {
            imageid: imageid
        },
        datatype: 'json',
        loadonce: true,
        page: 1
    }).trigger('reloadGrid');

}

function getCellValue(rowId, cellId) {
    var cell = $('#' + rowId + '_' + cellId);
    return cell.val();
}

function setCellValue(grid, rowId, cellId, value){
    grid.find('tr[id='+rowId+']').find('input[name="'+cellId+'"]').val(value);
    return true;
}

function setPccByET(tr, et, gridName){
    var spanTvaPcc = tr.find('.btn-pcc[data-type="tva"]'),
        tvaPcc = tr.find('input[name="'+gridName+'_pcc_tva_id"]'),
        spanHtPcc = tr.find('.btn-pcc[data-type="ht"]'),
        htPcc = tr.find('input[name="'+gridName+'_pcc_ht_id"]');

    spanTvaPcc.removeClass('disabled');
    spanHtPcc.removeClass('disabled');
    //Tresorerie
    if(parseInt(et) === 0){
        spanTvaPcc.html('Clicker ici');
        spanTvaPcc.addClass('disabled');
        spanHtPcc.html('Clicker ici');
        spanHtPcc.addClass('disabled');
        tvaPcc.val(-1);
        htPcc.val(-1);
    }
}

function checkTva(tr){
    var spanTvaPcc = tr.find('.btn-pcc[data-type="tva"]'),
        tvaPcc = tr.find('input[name="ndf_pcc_tva_id"]'),
        tvaTaux = tr.find('select[name="ndf_tva_taux"]'),
        tva = tr.find('input[name="ndf_tva"]'),
        sousnature = tr.find('select[name="ndf_categorie"]')
    ;

    tvaTaux.attr('disabled', false);
    tva.attr('disabled', false);
    spanTvaPcc.removeClass('disabled');


    $.ajax({
        url: Routing.generate('tenu_su_check_tva_ns'),
        type: 'GET',
        data:{
            sousnatureid: sousnature.val()
        },
        success: function(data){
            if(data === true){
                tvaTaux.attr('disabled', true);
                tva.attr('disabled', true);
                tva.val(0);
                tvaTaux.val(1);
                tvaPcc.val(-1);
                spanTvaPcc.addClass('disabled');
                spanTvaPcc.html('Clicker ici');

                setMontantTvaHt(tr.attr('id'), 'ndf');
            }
        }
    });

}

function enableCell(rowId, es, first){
    var caisseGrid = $('#caisse-details'),
        tr = caisseGrid.find('tr[id="'+rowId+'"]'),
        inputEntrees = tr.find('.c_e').find('input'),
        selectEntrees = tr.find('.c_e').find('select'),
        inputSorties = tr.find('.c_s').find('input'),
        selectSorties = tr.find('.c_s').find('select');

        inputEntrees.each(function(){
            $(this).attr('disabled', true);
            if(!first){
                $(this).val('');
            }
        });

        inputSorties.each(function(){
            $(this).attr('disabled', true);
            if(!first){
                $(this).val('')
            }
        });

        selectEntrees.each(function(){
            $(this).attr('disabled', true);
            if(!first){
                $(this).val('-1');
            }
        });

        selectSorties.each(function(){
            $(this).attr('disabled', true);
            if(!first){
                $(this).val('-1');
            }
        });


    if(parseInt(es) === 1){
        inputEntrees.each(function(){
            $(this).attr('disabled', false);
        });


        selectEntrees.each(function(){
            $(this).attr('disabled', false);
        });

    }
    else{
        inputSorties.each(function(){
            $(this).attr('disabled', false);
        });


        selectSorties.each(function(){
            $(this).attr('disabled', false);
        });


    }
}


function initVenteComptoirGrid(venteComptoirGrid, imageid, urlVenteComptoir, editUrlVenteComptoir){
    venteComptoirGrid.jqGrid('GridUnload');

    venteComptoirGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: false,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow: true,
        userDataOnFooter: true,
        url: urlVenteComptoir,
        editurl: editUrlVenteComptoir,
        mtype: 'POST',
        postData: {
            imageid: imageid
        },
        caption: 'Détails',
        colNames: [
            'Row id', 'Date', 'Libellé', 'Type', 'Type Vente', 'Caisse nature id', 'Nature', 'Analytique', 'Taux Tva', 'Total TTC',
            'TVA', 'HT', 'E/T' ,'TTC', 'PCC TTC ID', 'TVA', 'PCC TVA ID', 'HT', 'PCC HT ID', 'ACTION'
        ],
        colModel: [
            {
                name: 'vc_row_id',
                index: 'vc_row_id',
                align: 'left',
                editable: true,
                sortable: false,
                hidden: true
            },
            {
                name: 'vc_date',
                index: 'vc_date',
                align: 'center',
                editable: true,
                editoptions: {
                    dataInit: function (el) {
                        $(el).datepicker({
                            format: 'dd/mm/yyyy',
                            language: 'fr',
                            autoclose: true,
                            startView: 1
                        });
                    },
                    defaultValue: function() {
                        var ids = venteComptoirGrid.jqGrid('getDataIDs'),
                            lastDate = '';

                        if (ids.length > 0) {
                            var id = ids[ids.length - 1],
                                rowData = venteComptoirGrid.jqGrid('getRowData', id);
                            lastDate = rowData['vc_date'];
                        }
                        return lastDate.split('/').reverse().join('-');
                    }
                },
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'vc_libelle',
                index: 'vc_liblle',
                align: 'left',
                editable: true,
                sortable: true,
                width: 100
            },
            {
                name: 'vc_mode_reglement',
                index: 'vc_mode_reglement',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_mode_reglement'),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    }
                }
            },
            {
                name: 'vc_caisse_nature',
                index: 'vc_caisse_nature',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_caisse_nature', {type: 0}),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var tr =  $(this).closest('tr');
                            setTdCaisse(tr, imageid, 'vc', undefined);
                        }
                    }]
                }
            },
            {
                name: 'vc_caisse_nature_id',
                index: 'vc_caisse_nature_id',
                sortable: false,
                editable: true,
                hidden: true
            },
            {
                name: 'vc_caisse_type',
                index: 'vc_caisse_type',
                align: 'left',
                editable: true,
                sortable: true,
                width: 100,
                edittype: 'select',
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_caisse_type'),
                    dataInit: function (elem) {
                        $(elem).width(90);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var tr =  $(this).closest('tr');
                            setTdCaisse(tr, imageid, 'vc', undefined);
                        }
                    }]
                }
            },
            {
                name: 'vc_code_analytique',
                index: 'vc_code_analytique',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_code_analytique', {imageid: imageid}),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    }
                }
            },
            {
                name: 'vc_tva_taux',
                index: 'vc_tva_taux',
                align: 'right',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 70,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_tva_taux'),
                    dataInit: function (elem) {
                        $(elem).width(60);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var tr = $(this).closest('tr');

                            setMontantTvaHt(tr.attr('id'), 'vc');
                            setTdCaisse(tr, imageid, 'vc', undefined);
                        }
                    }]
                }
            },
            {
                name: 'vc_ttc',
                index: 'vc_ttc',
                align: 'right',
                editable: true,
                sortable: true,
                editoptions: {
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var gridId = $(this).closest('tr').attr('id');
                            setMontantTvaHt(gridId, 'vc');
                        }
                    }]
                },
                formatter: 'number',
                sorttype: 'number',
                width: 100
            },
            {
                name: 'vc_tva',
                index: 'vc_tva',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100
            },
            {
                name: 'vc_ht',
                index: 'vc_ht',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100
            },
            {
                name: 'vc_et',
                index: 'vc_et',
                align: 'center',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 100,
                editoptions: {
                    value: {'-1': '', '0': 'Engagement', '1': 'Tresorerie'},
                    dataInit: function (elem) {
                        $(elem).width(90);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var tr =  $(this).closest('tr');

                            setPccByET(tr, $(this).val(), 'vc');
                        }
                    }]
                }
            },
            {
                name: 'vc_pcc_ttc',
                index: 'vc_pcc_ttc',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'vc-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="vc" data-type="ttc" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'vc_pcc_ttc_id',
                index: 'vc_pcc_ttc_id',
                align: 'right',
                editable: true,
                hidden: true
            },
            {
                name: 'vc_pcc_tva',
                index: 'vc_pcc_tva',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'vc-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="vc" data-type="tva" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'vc_pcc_tva_id',
                index: 'vc_pcc_tva_id',
                align: 'right',
                editable: true,
                hidden: true
            },
            {
                name: 'vc_pcc_ht',
                index: 'vc_pcc_ht',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'vc-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="vc" data-type="ht" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'vc_pcc_ht_id',
                index: 'vc_pcc_ht_id',
                align: 'right',
                editable: true,
                hidden: true
            },
            {
                name: 'vc_action',
                index: 'vc_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'vc_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action vc-save" title="Enregistrer"></i><i class="fa fa-trash icon-action vc-delete" title="supprimer"></i>'
                }
            }
        ],
        loadComplete: function () {
            if ($('#btn-add-vc').length === 0) {
                venteComptoirGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-vc" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }
            var containerWith = venteComptoirGrid.closest('.col-lg-12').width();
            venteComptoirGrid.jqGrid('setGridWidth', containerWith);

            lastsel_vc = -1;
            var ids = venteComptoirGrid.jqGrid('getDataIDs');
            lastsel_vc = ids[0];
        },
        onCellSelect: function (rowid, icol, cellcontent, e) {
            if (rowid && rowid !== lastsel_vc) {
                venteComptoirGrid.restoreRow(lastsel_vc);
                lastsel_vc = rowid;
            }
            var colName = venteComptoirGrid.jqGrid('getGridParam', 'colModel')[icol].name,
                colPccs = ['vc_pcc_ttc', 'vc_pcc_tva', 'vc_pcc_ht'];

            if ($.inArray(colName, colPccs) === -1) {
                venteComptoirGrid.editRow(rowid, false);
            }
        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            return !item_action;
        }
    }).contextMenu({
        selector: '.jqgrow',
        build: function ($trigger, e) {
            var target = e.target,
                tr = $(target).closest('tr.jqgrow');

            if ($(target).hasClass('btn-pcc')) {
                return {
                    callback: function (key, option) {
                        var id = tr.attr('id'),
                            typepcc = $(target).attr('data-type'),
                            rowData = venteComptoirGrid.jqGrid('getRowData', id),
                            rowid = rowData['vc_row_id'];

                        if (key === 'delete') {
                            removeNdfCaissePcc(imageid, rowid, -1, typepcc, 'vc');
                        }
                    },
                    items: {
                        delete: {name: "Supprimer compte", icon: "delete"}
                    }
                }
            }
        }
    });
}

function initCaisseGrid(caisseGrid, imageid, urlCaisse, editUrlCaisse){
    caisseGrid.jqGrid('GridUnload');

    caisseGrid.jqGrid({
        datatype: 'json',
        loadonce: true,
        sortable: true,
        shrinkToFit: false,
        viewrecords: true,
        hidegrid: false,
        rownumbers: true,
        rownumWidth: 30,
        footerrow: true,
        userDataOnFooter: true,
        url: urlCaisse,
        editurl: editUrlCaisse,
        mtype: 'POST',
        postData: {
            imageid: imageid
        },
        caption: 'Détails',
        colNames: [
            'Row id', 'Date', 'Libellé', 'Solde Init', 'Type Mvt', 'Analytique', 'E/S',
            'Nature Entrée', 'Nature Entrée Id', 'Taux Tva',  'TTC', 'TVA', 'HT',
            'Nature Sortie', 'Nature Sortie Id', 'Taux Tva',  'TTC', 'TVA', 'HT','Solde fin', 'E/T',
            'TTC', 'TTC PCC ID', 'TVA', 'TVA PCC ID', 'HT', 'HT PCC ID', 'ACTION'
        ],
        colModel: [
            {
                name: 'c_row_id',
                index: 'c_row_id',
                align: 'left',
                editable: false,
                sortable: false,
                hidden: true
            },
            {
                name: 'c_date',
                index: 'c_date',
                align: 'center',
                editable: true,
                editoptions: {
                    dataInit: function (el) {
                        $(el).datepicker({
                            format: 'dd/mm/yyyy',
                            language: 'fr',
                            autoclose: true,
                            startView: 1
                        });
                    },
                    defaultValue: function() {
                        var ids = caisseGrid.jqGrid('getDataIDs'),
                            lastDate = '';

                        if (ids.length > 0) {
                            var id = ids[ids.length - 1],
                                rowData = caisseGrid.jqGrid('getRowData', id);
                            lastDate = rowData['c_date'];
                        }
                        return lastDate.split('/').reverse().join('-');
                    }
                },
                width: 100,
                formatter: 'date', formatoptions: {srcformat: 'ISO8601Short', newformat: 'd/m/Y'},
                sorttype: 'date'
            },
            {
                name: 'c_libelle',
                index: 'c_liblle',
                align: 'left',
                editable: true,
                sortable: true,
                width: 100
            },
            {
                name: 'c_solde_init',
                index: 'c_solde_init',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                editoptions: {
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();

                            var rowId = $(this).closest('tr').attr('id');
                            setSoldeFinal(rowId);
                        }
                    }],
                    defaultValue: function(e) {

                        var ids = caisseGrid.jqGrid('getDataIDs'),
                            soldefinal = 0;

                        if (ids.length > 0) {
                            var id = ids[ids.length - 1],
                                rowData = caisseGrid.jqGrid('getRowData', id);
                            soldefinal = rowData['c_solde_fin'];
                        }
                        return soldefinal;

                    }
                }
            },
            {
                name: 'c_mode_reglement',
                index: 'c_mode_reglement',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_mode_reglement'),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    }
                }
            },
            {
                name: 'c_code_analytique',
                index: 'c_code_analytique',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_code_analytique', {imageid: imageid}),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    }
                }
            },
            {
                name: 'c_es',
                index: 'c_es',
                align: 'center',
                editable: true,
                enable: true,
                edittype: 'select',
                width: '50',
                editoptions: {
                    value:{ '-1':'' , '1':'E' ,'0':'S'},
                    dataInit: function (e) {
                        $(e).width(40);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var id = $(e.target).closest('tr').attr('id');
                            enableCell(id, $(e.target).val(), false);
                        }
                    }]
                }

            },
            {
                name: 'c_caisse_nature_e',
                index: 'c_caisse_nature_e',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_caisse_nature', {type: 0}),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var tr =  $(this).closest('tr');
                            setTdCaisse(tr, imageid, 'c', 'e');
                        }
                    }]

                },
                classes: 'c_e'
            },
            {
                name: 'c_caisse_nature_e_id',
                index: 'c_caisse_nature_e_id',
                sortable: false,
                editable: true,
                hidden: true,
                classes: 'c_e'
            },
            {
                name: 'c_tva_taux_e',
                index: 'c_tva_taux_e',
                align: 'right',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 70,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_tva_taux'),
                    dataInit: function (elem) {
                        $(elem).width(60);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            var tr = $(this).closest('tr');
                            setMontantTvaHt(tr.attr('id'), 'c', 'e');
                            setTdCaisse(tr, imageid, 'c', 'e');

                        }
                    }]
                },
                classes: 'c_e'
            },
            {
                name: 'c_ttc_e',
                index: 'c_ttc_e',
                align: 'right',
                editable: true,
                sortable: true,
                editoptions: {
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var gridId = $(this).closest('tr').attr('id');
                            setMontantTvaHt(gridId, 'c', 'e');
                        }
                    }]
                },
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_e'
            },
            {
                name: 'c_tva_e',
                index: 'c_tva_e',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_e'
            },
            {
                name: 'c_ht_e',
                index: 'c_ht_e',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_e'
            },
            {
                name: 'c_caisse_nature_s',
                index: 'c_caisse_nature_s',
                align: 'left',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 110,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_caisse_nature', {type: 1}),
                    dataInit: function (elem) {
                        $(elem).width(100);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            var tr = $(this).closest('tr');
                            setTdCaisse(tr, imageid, 'c', 's');

                        }
                    }]

                },
                classes: 'c_s'
            },
            {
                name: 'c_caisse_nature_s_id',
                index: 'c_caisse_nature_s_id',
                sortable: false,
                editable: true,
                hidden: true,
                classes: 'c_s'
            },
            {
                name: 'c_tva_taux_s',
                index: 'c_tva_taux_s',
                align: 'right',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 70,
                editoptions: {
                    dataUrl: Routing.generate('tenu_su_tva_taux'),
                    dataInit: function (elem) {
                        $(elem).width(60);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            var tr = $(this).closest('tr');
                            setMontantTvaHt(tr.attr('id'), 'c', 's');
                            setTdCaisse(tr, imageid, 'c', 's');

                        }
                    }]
                },
                classes: 'c_s'
            },
            {
                name: 'c_ttc_s',
                index: 'c_ttc_s',
                align: 'right',
                editable: true,
                sortable: true,
                editoptions: {
                    dataEvents: [{
                        type: 'change',
                        fn: function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var gridId = $(this).closest('tr').attr('id');
                            setMontantTvaHt(gridId, 'c', 's');
                        }
                    }]
                },
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_s'
            },
            {
                name: 'c_tva_s',
                index: 'c_tva_s',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_s'
            },
            {
                name: 'c_ht_s',
                index: 'c_ht_s',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100,
                classes: 'c_s',
                editoptions:{
                    dataInit: function(e){
                        var id = $(e).closest('tr').attr('id'),
                            es = $(e).closest('tr').find('select[name="c_es"]').val()
                        ;
                        enableCell(id, es, true);
                    }
                }
            },
            {
                name: 'c_solde_fin',
                index: 'c_solde_fin',
                align: 'right',
                editable: true,
                sortable: true,
                formatter: 'number',
                sorttype: 'number',
                width: 100
            },
            {
                name: 'c_et',
                index: 'c_et',
                align: 'center',
                editable: true,
                sortable: true,
                edittype: 'select',
                width: 100,
                editoptions: {
                    value: {'-1': '', '0': 'Engagement', '1': 'Tresorerie'},
                    dataInit: function (elem) {
                        $(elem).width(90);
                    },
                    dataEvents: [{
                        type: 'change',
                        fn: function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var tr =  $(this).closest('tr');

                            setPccByET(tr, $(this).val(), 'c');
                        }
                    }]
                }
            },
            {
                name: 'c_pcc_ttc',
                index: 'c_pcc_ttc',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'c-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="c" data-type="ttc" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'c_pcc_ttc_id',
                index: 'c_pcc_ttc_id',
                align: 'right',
                editable: true,
                sortable: true,
                hidden: true,
                classes: 'c-pcc'
            },
            {
                name: 'c_pcc_tva',
                index: 'c_pcc_tva',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'c-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="c" data-type="tva" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'c_pcc_tva_id',
                index: 'c_pcc_tva_id',
                align: 'right',
                editable: true,
                sortable: true,
                hidden: true,
                classes: 'c-pcc'
            },
            {
                name: 'c_pcc_ht',
                index: 'c_pcc_ht',
                align: 'right',
                editable: false,
                sortable: true,
                classes: 'c-pcc',
                editoptions: {
                    defaultValue: '<span class="btn btn-primary btn-block btn-xs btn-pcc" data-categorie="c" data-type="ht" data-id="-1" data-table="pcc" style="font-size: 10px !important;">Clicker ici</span>'
                }
            },
            {
                name: 'c_pcc_ht_id',
                index: 'c_pcc_ht_id',
                align: 'right',
                editable: true,
                sortable: true,
                hidden: true,
                classes: 'c-pcc'
            },
            {
                name: 'c_action',
                index: 'c_action',
                align: 'center',
                editable: false,
                sortable: true,
                width: 80,
                fixed: true,
                classes: 'c_action',
                editoptions: {
                    defaultValue: '<i class="fa fa-save icon-action c-save" title="Enregistrer"></i><i class="fa fa-trash icon-action c-delete" title="supprimer"></i>'
                }
            }
        ],
        loadComplete: function () {
            if ($('#btn-add-caisse').length === 0) {
                caisseGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-add-caisse" class="btn btn-outline btn-primary btn-xs" style="margin-right: 20px;">Ajouter</button></div>');
            }
            var containerWith = caisseGrid.closest('.col-lg-12').width();
            caisseGrid.jqGrid('setGridWidth', containerWith);

            lastsel_c = -1;
            var ids = caisseGrid.jqGrid('getDataIDs');
            lastsel_c = ids[0];
        },
        onCellSelect: function (rowid, icol, cellcontent, e) {
            if (rowid && rowid !== lastsel_c) {
                caisseGrid.restoreRow(lastsel_c);
                lastsel_c = rowid;
            }
            var colName = caisseGrid.jqGrid('getGridParam', 'colModel')[icol].name,
                colPccs = ['c_pcc_ttc', 'c_pcc_tva', 'c_pcc_ht'];

            if ($.inArray(colName, colPccs) === -1) {
                caisseGrid.editRow(rowid, false);
            }

        },
        beforeSelectRow: function (rowid, e) {
            var target = $(e.target);
            var item_action = (target.closest('td').children('.icon-action').length > 0);
            return !item_action;
        }
    }).contextMenu({
        selector: '.jqgrow',
        build: function ($trigger, e) {
            var target = e.target,
                tr = $(target).closest('tr.jqgrow');

            if ($(target).hasClass('btn-pcc')) {
                return {
                    callback: function (key, option) {
                        var id = tr.attr('id'),
                            typepcc = $(target).attr('data-type'),
                            rowData = caisseGrid.jqGrid('getRowData', id),
                            rowid = rowData['c_row_id'];

                        if (key === 'delete') {
                            removeNdfCaissePcc(imageid, rowid, -1, typepcc, 'c');
                        }
                    },
                    items: {
                        delete: {name: "Supprimer compte", icon: "delete"}
                    }
                }
            }
        }
    });
}

function setTdCaisse(tr, imageid, categorie, es) {
    if (es === undefined)
        es = '';
    else
        es = '_' + es;

    var rowid = tr.attr('id'),
        caissenature = tr.find('select[name="' + categorie + '_caisse_nature'+es+'"]').val(),
        caissetype = tr.find('select[name="' + categorie + '_caisse_type'+es+'"]').val(),
        tva = tr.find('select[name="'+categorie+'_tva_taux'+es+'"]').val();

    $.ajax({
        url: Routing.generate('tenu_su_td_caisse_pcc'),
        type: 'GET',
        data: {
            imageid: imageid,
            caissenatureid: caissenature,
            caissetypeid: caissetype,
            tvaid: tva,
            categorie: categorie
        },
        success: function(data){
            if(data.type === 'success'){
                var pccht = $('#' + rowid + '_'+categorie+'_pcc_ht_id'),
                    pcctva = $('#' + rowid + '_'+categorie+'_pcc_tva_id'),
                    pccttc = $('#' + rowid + '_'+categorie+'_pcc_ttc_id'),
                    spanHt = tr.find('span[data-type="ht"]'),
                    spanTva = tr.find('span[data-type="tva"]'),
                    spanTtc = tr.find('span[data-type="ttc"]')
                ;

                if(pccht.val() === '' || parseInt(pccht.val()) === -1) {
                    spanHt.html(data.resultat);
                    pccht.val(data.resultatid);
                    spanHt.attr('data-id', data.resultatid);
                }
                if(pcctva.val() === '' || parseInt(pcctva.val()) === -1) {
                    spanTva.html(data.tva);
                    pcctva.val(data.tvaid);
                    spanTva.attr('data-id', data.tvaid);
                }
                if(pccttc.val() === '' || parseInt(pccttc.val()) === -1) {
                    spanTtc.html(data.bilan);
                    pccttc.val(data.bilanid);
                    spanTtc.attr('data-id', data.bilanid);
                }
            }
        }
    })
}

function setTdNdfSousnature(tr, imageid){
    var rowid = tr.attr('id'),
        sousnatureid = tr.find('select[name="ndf_categorie"]').val(),
        nbcouvert = tr.find('select[name="ndf_nbre_couvert"]').val(),
        distance = tr.find('select[name="ndf_distance"]').val()
    ;

    $.ajax({
        url: Routing.generate('tenu_su_td_ndf_sousnature_pcc'),
        type: 'GET',
        data: {
            imageid: imageid,
            sousnatureid: sousnatureid,
            nbcouvert: nbcouvert,
            distance: distance
        },
        success: function(data) {
            if (data.type === 'success') {
                var ndfpccht = $('#' + rowid + '_ndf_pcc_ht_id'),
                ndfpcctva = $('#' + rowid + '_ndf_pcc_tva_id'),
                ndfpccttc = $('#' + rowid + '_ndf_pcc_ttc_id'),
                spanHt = tr.find('span[data-type="ht"]'),
                    spanTva = tr.find('span[data-type="tva"]'),
                    spanTtc = tr.find('span[data-type="ttc"]')
                ;

                if(ndfpccht.val() === '' || parseInt(ndfpccht.val()) === -1) {
                    spanHt.html(data.resultat);
                    ndfpccht.val(data.resultatid);
                    spanHt.attr('data-id', data.resultatid);
                }
                if(ndfpcctva.val() === '' || parseInt(ndfpcctva.val()) === -1) {
                    spanTva.html(data.tva);
                    ndfpcctva.val(data.tvaid);
                    spanTva.attr('data-id', data.tvaid);
                }
                if(ndfpccttc.val() === '' || parseInt(ndfpccttc.val()) === -1) {
                    spanTtc.html(data.bilan);
                    ndfpccttc.val(data.bilanid);
                    spanTtc.attr('data-id', data.bilanid);
                }
            }
        }
    })
}

function showEcritureRecap(){

    var ecritureDossierGrid = $('#ecriture-dossier'),
        url = Routing.generate('tenu_su_ecriture_recap');

    ecritureDossierGrid.jqGrid('setGridParam', {
            url: url,
            postData: {
                imageid: getImageId()
            },
            datatype: 'json'
        }
    ).trigger('reloadGrid', {fromServer: true, page: 1});
}


function getImageId(){
    return $('#allimage .image.active').attr('data-id');
}

function copyVal(input, model, selector){
    input.find(selector).val(model.find(selector).val());
}

function vider(){
    // $('#js_impute_liste').jqGrid("clearGridData");
}