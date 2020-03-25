$(document).ready(function(){

    $(document).on('change', '#banques', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var banqueid = $(this).val(),
            dossierid = $('#dossieridtemp').val();

        $.ajax({
            url: Routing.generate('banque_liste_banque_compte'),
            type: 'GET',
            data: {
                banqueid: banqueid,
                dossierid: dossierid
            },
            success: function (data) {
                $('#banquecomptes').html(data);
            }
        });
    });

    $(document).on('click', '#btn-add-banquecompte, #btn-undo-banquecompte, #btn-add-iban, #btn-undo-iban, #btn-add-numcb, #btn-undo-numcb', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var inputgroupAddBc = $('#btn-add-banquecompte').closest('.input-group'),
            inputgroupSaveBc = $('#btn-save-banquecompte').closest('.input-group'),
            inputgroupAddIban = $('#btn-add-iban').closest('.input-group'),
            inputgroupSaveIban = $('#btn-save-iban').closest('.input-group'),
            inputgroupAddNumCb = $('#btn-add-numcb').closest('.input-group'),
            inputgroupSaveNumCb = $('#btn-save-numcb').closest('.input-group')
        ;

        if ($(this).hasClass('banque-compte')) {
            if ($(this).attr('id') === 'btn-add-banquecompte') {
                if (!inputgroupAddBc.hasClass('hidden')) {
                    inputgroupAddBc.addClass('hidden');
                    inputgroupSaveBc.removeClass('hidden');
                }
            }
            else {
                if (!inputgroupSaveBc.hasClass('hidden')) {
                    inputgroupSaveBc.addClass('hidden');
                    inputgroupAddBc.removeClass('hidden');
                }
            }
        }

        else if ($(this).hasClass('iban')){
            if ($(this).attr('id') === 'btn-add-iban') {
                if (!inputgroupAddIban.hasClass('hidden')) {
                    inputgroupAddIban.addClass('hidden');
                    inputgroupSaveIban.removeClass('hidden');
                }
            }
            else {
                if (!inputgroupSaveIban.hasClass('hidden')) {
                    inputgroupSaveIban.addClass('hidden');
                    inputgroupAddIban.removeClass('hidden');
                }
            }
        }

        else{
            if ($(this).attr('id') === 'btn-add-numcb') {
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
        }
    });

    $(document).on('click', '#btn-save-banquecompte', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var inputgroupAdd = $('#btn-add-banquecompte').closest('.input-group'),
            inputgroupSave = $('#btn-save-banquecompte').closest('.input-group'),
            numcompte = $('#newbanquecompte').val(),
            banqueid = $('#banques').val(),
            dossierid = $('#dossieridtemp').val();

        setBanqueByCode(numcompte.substring(0, 5));

        $.ajax({
            url: Routing.generate('banque_save_banque_compte'),
            type: 'POST',
            data: {
                dossierid: dossierid,
                banqueid: banqueid,
                numcompte: numcompte
            },
            success: function (data) {
                show_info('', data.message, data.type);

                $('#newbanquecompte').val('');

                if (data.type === 'success' || data.type === 'warning') {
                    if (!inputgroupSave.hasClass('hidden')) {
                        inputgroupSave.addClass('hidden');
                        inputgroupAdd.removeClass('hidden');
                    }

                    var banquecompteid = data.id,
                        banquecomptes = $('#banquecomptes');

                    if (data.type === 'success') {
                        $.ajax({
                            url: Routing.generate('banque_liste_banque_compte'),
                            type: 'GET',
                            data: {
                                banqueid: banqueid,
                                dossierid: dossierid
                            },
                            success: function (data) {
                                banquecomptes.html(data);
                                banquecomptes.val(banquecompteid);
                            }
                        });
                    }
                    else {
                        banquecomptes.val(banquecompteid);
                    }
                }
            }
        });

    });

    $(document).on('change', '#ibans, #banquecomptes', function () {
        var banqueid = $(this).find('option:selected').attr('data-banque');

        $('#banques').val(banqueid);

        if ($(this).attr('id') === 'ibans') {
            $('#banquecomptes').val($(this).val());
        }
        else {
            $('#ibans').val($(this).val());
        }
    });

    $(document).on('click', '#btn-save-iban', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var inputgroupAdd = $('#btn-add-iban').closest('.input-group'),
            inputgroupSave = $('#btn-save-iban').closest('.input-group'),
            iban = $('#newiban').val(),
            banqueid = $('#banques').val(),
            dossierid = $('#dossieridtemp').val();

        if (isValidIban(iban) !== false) {

            setBanqueByCode(iban.substring(4, 9));

            $.ajax({
                url: Routing.generate('banque_save_banque_compte', {isIban: 1}),
                type: 'POST',
                data: {
                    dossierid: dossierid,
                    banqueid: banqueid,
                    iban: iban
                },
                success: function (data) {
                    show_info('', data.message, data.type);

                    $('#newbanquecompte').val('');

                    if (data.type === 'success' || data.type === 'warning') {
                        if (!inputgroupSave.hasClass('hidden')) {
                            inputgroupSave.addClass('hidden');
                            inputgroupAdd.removeClass('hidden');
                        }

                        var banquecompteid = data.id,
                            banquecomptes = $('#banquecomptes'),
                            ibans = $('#ibans');

                        if (data.type === 'success') {
                            $.ajax({
                                url: Routing.generate('banque_liste_banque_compte', {saisie: 1}),
                                type: 'GET',
                                data: {
                                    banqueid: banqueid,
                                    dossierid: dossierid
                                },
                                success: function (data) {
                                    banquecomptes.html(data);
                                    banquecomptes.val(banquecompteid);
                                }
                            });

                            $.ajax({
                                url: Routing.generate('banque_liste_banque_compte', {saisie: 1, iban: 1}),
                                type: 'GET',
                                data: {
                                    banqueid: banqueid,
                                    dossierid: dossierid
                                },
                                success: function (data) {
                                    ibans.html(data);
                                    ibans.val(banquecompteid);
                                }
                            });
                        }
                        else {
                            ibans.val(banquecompteid);
                            banquecomptes.val(banquecompteid);
                        }
                    }
                }
            });

        }
        else {
            show_info('', 'Iban invalide', 'error');
        }
    });


    $(document).on('click', '#mdupliquer', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $.ajax({
            url: Routing.generate('banque_duplique'),
            type: 'GET',
            success: function (data) {
                var dupliqueModal = $('#duplique-modal');
                dupliqueModal.find('.modal-body').html(data);
                dupliqueModal.modal('show');
            }
        });
    });

    $(document).on('change', '#dsouscategorie', function(event){
       event.preventDefault();
       event.stopPropagation();

        var souscategorie = $(this).val();

        $.ajax({
            url: Routing.generate('banque_soussouscategorie'),
            type: 'GET',
            data: {souscategorie: souscategorie},
            success: function (data) {
                $('#dsoussouscategorie').html(data);
            }
        });
    });

    $(document).on('click', '#js_form_duplique_submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        return false;

        var imageid = $('#image').val(),
            souscategorieid = $('#dsouscategorie').val(),
            soussouscategorieid = $('#dsoussouscategorie').val();

        if(imageid == ''){
            show_info('', 'Attention', 'Aucune image selectionée', 'warning');
            return false;
        }

        swal({
            title: 'Duplication Image',
            text: "Voulez-vous dupliquer cette image?",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then(function () {

                $.ajax({
                    url: Routing.generate('banque_duplique'),
                    data: {
                        imageid: imageid,
                        souscategorieid: souscategorieid,
                        soussouscategorieid: soussouscategorieid
                    },
                    type: 'POST',
                    success: function (data) {
                        show_info('', data.message, data.type);

                        if(data.type === 'success'){
                            $('#duplique-modal').modal('hide');
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

    $(document).on('click', '.btn-verfier-saisie', function(event){
        event.preventDefault();
        event.stopPropagation();

        var lab = $(this).closest('.ob-container').find('.saisir_label'),
            total = $(this).closest('.ob-container').find('.verif-total').val(),
            datePiece = $(this).closest('.ob-container').find('.verif-date').val(),
            imageid = $('#image').val(),
            banquecompte = $('#banquecomptes').val();

        if(total === ''){
            return;
        }

        if(parseInt(total) === 0){
            return;
        }

        $.ajax({
            url: Routing.generate('banque_verif_montant'),
            type: 'GET',
            data: {
                datepiece: datePiece,
                total: total.replace(/\s/g, ''),
                imageid: imageid,
                banquecompteid: banquecompte
            },
            success: function (data) {
                lab.html(data);
                lab.show();
            }
        });
    });

    $(document).on('click', '.saisir_label .btn', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if(!$(this).hasClass('btn-danger')){
            var releveids = $(this).find('i').attr('data-releve-id');

            if(releveids === '')
                return;

            $.ajax({
                url: Routing.generate('banque_show_releve'),
                type: 'GET',
                data: {
                    releveids: releveids
                },
                success: function(data){
                    show_modal(data, '');
                }
            })
        }
    });

    $(document).on('change', '#banquecomptes', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var lab = $(this).closest('.form-horizontal')
                .find('.ob-container:visible')
                .find('.saisir_label'),
            souscat = $('#souscat').val(),
            banquecompteid = $('#banquecomptes').val();

        if(parseInt(souscat) === 10){
            return;
        }

        // if(parseInt(banquecompteid) !== -1) {
            // setASaisirByBanqueCompte(banquecompteid, lab);
        // }
    });

    $(document).on('focus', '.verif-total, .montant', function (event){
        event.preventDefault();
        event.stopPropagation();

        $(this).val($(this).val().replace(/\s/g, ''));
    });

    $(document).on('blur', '.verif-total, .montant', function(event){
        event.preventDefault();
        event.stopPropagation();

        var tmp = $(this).val();
        tmp = mi(tmp);
        $(this).val(tmp);
    });

    $(document).on('blur', '.page',function (event) {
        event.preventDefault();
        event.stopPropagation();

        var val_page = $(this).val();
        if(isNaN(val_page)) {
            $(this).val(1);
        }
    });

    $(document).on('click', '#btn_recategoriser', function(event){
        event.preventDefault();
        event.stopPropagation();
        $.ajax({
            url: Routing.generate('banque_change_categorie'),
            type: 'POST',
            data: {
                imagid:$('#image').val(),
                c : $('#icategorie').val(),
                sc : $('#isouscategorie').val(),
                ssc  : $('#isoussouscategorie').val()
            },
            success: function (data) {
                show_info('', data.message, data.type);
            }
        });
        return false;
    });


    $(document).on('resize', '#pdf-resize', function () {

        var modalBody = $('#myModal').find('.modal-body.p-4').innerWidth(),
            mySidenav = $('#mySidenav').innerWidth(),
            pdfResize = $('#pdf-resize').innerWidth(),
            formResize = $('#form-resize'),
            formResizeWidth = modalBody - mySidenav - pdfResize,
            etape = $('#etape').val();

        formResize.width(formResizeWidth - 80);

        if(parseInt(etape) === 1) {
            var grid = $('.ob-container:visible').find('table[id$=list]');
            grid.jqGrid('setGridWidth', formResize.width() + 10);
        }
        else{
            var containerWidth = formResize.width();

            releveGrid.jqGrid('setGridWidth', containerWidth);
            cutoffGrid.jqGrid('setGridWidth', containerWidth);
        }
    });


    $(document).on('click', '#mrecategorisastion', function(event){
        event.preventDefault();
        event.stopPropagation();

        var recategorisationModal = $('#recategorisation-modal');

        recategorisationModal.modal('show');
    });

    $(document).on('change', '#rec-souscategorie', function(event){
        event.preventDefault();
        event.stopPropagation();

        var souscategorie = $(this).val();

        $.ajax({
            url: Routing.generate('banque_soussouscategorie'),
            type: 'GET',
            data: {souscategorie: souscategorie},
            success: function (data) {
                $('#rec-soussouscategorie').html(data);
            }
        });
    })
});

function setASaisirByBanqueCompte(banquecompteid, lab){
    if(banquecompteid === null)
        return;

    $.ajax({
        url: Routing.generate('banque_banque_compte_etat'),
        type: 'GET',
        data: {
            banquecompteid: banquecompteid
        },
        success: function (data) {
            lab.html(data);
            lab.show();
        }
    });
}


function setAsaisirBySouscategorie(dossierid, souscategorieid, lab){

    if(dossierid === undefined)
        return false;

    $.ajax({
        url: Routing.generate('banque_souscategorie_a_sasir'),
        type: 'GET',
        data:{
            dossierid: dossierid,
            souscategorieid: souscategorieid
        },
        success: function(data){
            lab.html(data);
            lab.show();
        }
    })
}



function validerImage(imageid){
    $.ajax({
        url: Routing.generate('banque_valider_image'),
        type: 'POST',
        data: {
            imageid: imageid
        },
        success: function(data){
            var span = $('#allimage').find('span[id="'+imageid+'"]'),
                tr = span.closest('tr');

            tr.html(data);
        }
    });
}


function choisirtraitement(dossierid) {

    var souscat = $('#souscat').val(),
        soussouscat = $('#souscat option:selected').attr('data-soussouscategorie-id'),
        relevebanque = $('#relevebancaire'),
        remise = $('#remisebanque'),
        relcb = $('#relcb'),
        lcrbanque = $('#lcrbanque'),
        virement = $('#virement'),
        autres = $('#autres'),
        cartecreditreleve = $('#cartecreditreleve'),
        cartecredit = $('#cartecredit'),
        cartedebit = $('#cartedebit');

    var lab = $('#result').find('.ob-container:visible')
            .find('.saisir_label')
    ;

    setAsaisirBySouscategorie(dossierid, souscat, lab);

    remise.hide();
    relcb.hide();
    lcrbanque.hide();
    virement.hide();
    autres.hide();
    cartecreditreleve.hide();
    cartecredit.hide();
    cartedebit.hide();
    relevebanque.hide();



    switch (parseInt(souscat)) {

        // case 1:
        //     switch (parseInt(soussouscat)) {
        //         case 1901:
        //             cartecreditreleve.show();
        //             cartecreditreleve.jqGrid('setGridWidth',  cartecreditreleve.closest('.ob-container').width());
        //             break;
        //         case 2791:
        //             cartedebit.show();
        //             cartedebit.jqGrid('setGridWidth',  cartedebit.closest('.ob-container').width());
        //             break;
        //         default:
        //             cartecredit.show();
        //             cartecredit.jqGrid('setGridWidth',  cartecredit.closest('.ob-container').width());
        //             break;
        //     }
        //     break;

        case 937:
            cartecredit.show();
            cartecredit.jqGrid('setGridWidth',  cartecredit.closest('.ob-container').width());
            break;
        case 939:
            cartedebit.show();
            cartedebit.jqGrid('setGridWidth',  cartedebit.closest('.ob-container').width());
            break;
        case 941:
            cartecreditreleve.show();
            cartecreditreleve.jqGrid('setGridWidth',  cartecreditreleve.closest('.ob-container').width());
            break;

        case 5:
            lcrbanque.show();
            lcrbanque.jqGrid('setGridWidth',  lcrbanque.closest('.ob-container').width());
            break;
        case 6:
        case 153:
            virement.show();
            virement.jqGrid('setGridWidth',  virement.closest('.ob-container').width());
            break;
        case 7:
            remise.show();
            remise.jqGrid('setGridWidth',  remise.closest('.ob-container').width());
            break;
        case 8:
            autres.show();
            autres.jqGrid('setGridWidth',  autres.closest('.ob-container').width());
            break;

        case 10:
            relevebanque.show();
            relevebanque.jqGrid('setGridWidth',  relevebanque.closest('.ob-container').width());
            break;
    }
}

function initBanquecompteListe(dossierid){
    var url = Routing.generate('banque_liste_banque_compte', {saisie: 1} ),
        urlIban = Routing.generate('banque_liste_banque_compte', {saisie: 1, iban:1})
    ;

    $.ajax({
        url: url,
        data: {dossierid: dossierid},
        success: function (data) {
            $('#banquecomptes').html(data);
        }
    });

    $.ajax({
        url: urlIban,
        data: {dossierid: dossierid},
        success: function (data) {
            $('#ibans').html(data);
        }
    });
}


function setFiltreRecategorisationClientDossier(souscategorie, soussouscategorie, exercice){
    $.ajax({
        url: Routing.generate('banque_recategoriesation_filtre'),
        type: 'GET',
        data:{
            souscategorie: souscategorie,
            soussouscategorie: soussouscategorie,
            exercice: exercice
        },
        success: function(data){
            $('#rec-dossier').html(data.dossier);
            $('#rec-client').html(data.client);
        }
    })
}