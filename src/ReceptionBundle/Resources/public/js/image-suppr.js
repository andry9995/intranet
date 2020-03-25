$(document).ready(function(){

    var imageGrid = $('#image-list'),
        w = imageGrid.parent().width();


    imageGrid.jqGrid({
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
        mtype: 'POST',
        caption: 'Images',
        colNames: [
            'Image', 'Dossier', 'Exercice', '<input type="checkbox" id="chk-all">'
        ],
        colModel: [
            {
                name: 'is_nom',
                index: 'is_nom',
                align: 'center',
                editable: false,
                sortable: true,
                width: 30 * w / 100
            },
            {
                name: 'is_dossier',
                index: 'is_dossier',
                align: 'center',
                editable: false,
                sortable: true,
                width: 30 * w / 100
            },
            {
                name: 'is_exercice',
                index: 'is_exercice',
                align: 'center',
                editable: false,
                sortable: true,
                width: 30 * w / 100
            },

            {
                name: 'is_check',
                index: 'is_check',
                align: 'center',
                editoptions: {value: 'True:False'},
                classes: 'chk',
                formatter: 'checkbox',
                formatoptions: {disabled: false},
                editable: true,
                width: 10 * w / 100
            }
        ],
        loadComplete: function () {

            $('#jqgh_image-list_is_check').removeClass('ui-jqgrid-sortable');
            $('#image-list').jqGrid('setGridHeight', $(window).height() - 320);

            if ($('#btn-image-suppr').length === 0) {
                imageGrid.closest('.ui-jqgrid').find('.ui-jqgrid-title').
                after('<div class="pull-right" style="line-height: 40px;">' +
                    '<button id="btn-image-suppr" class="btn btn-danger btn-xs" style="margin-right: 20px;">Supprimer Les Images Selectionnées</button></div>');
            }
        }

    });

    $(document).on('change', '#filtre-client', function(e){
        e.preventDefault();
        e.stopPropagation();

        setDossiers($(this).val());
    });

    $(document).on('change', '#filtre-dossier', function(e){
        e.preventDefault();
        e.stopPropagation();

        setExercices($(this).val());
    });

    $(document).on('change', '#filtre-exercice', function(e){
        e.preventDefault();
        e.stopPropagation();

        var dossier = $('#filtre-dossier').val();

        setDateScans(dossier, $(this).val());
    });

    $(document).on('change', '#filtre-date-scan', function(e){
        e.preventDefault();
        e.stopPropagation();

        var dossier = $('#filtre-dossier').val(),
            exercice = $('#filtre-exercice').val()
        ;

        setLots(dossier, exercice, $(this).val());
    });

    $(document).on('click', '#btn-go', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#image-list').jqGrid('setGridParam', {
            url: Routing.generate('reception_image_suppr_list'),
            postData:{
                dossierid: $('#filtre-dossier').val(),
                exercice: $('#filtre-exercice').val(),
                datescan: $('#filtre-date-scan').val(),
                lot: $('#filtre-lot').val()
            },
            datatype: 'json'
        })
            .trigger('reloadGrid', {fromServer: true, page: 1});
    });

    $(document).on('click', '#btn-image-suppr', function(e){
        e.preventDefault();
        e.stopPropagation();

        var trs = $('#image-list').find('tr'),
            ids = [];

        trs.each(function(){
            if($(this).attr('id') !== undefined){

                if($(this).find('.chk').find('input[type="checkbox"]').is(':checked')) {
                    ids.push($(this).attr('id'));
                }
            }
        });

        if(ids.length === 0){
            show_info('','Aucune image selectionnée', 'warning');
            return false;
        }

        swal({
            title: 'Attention',
            text: "Voulez vous supprimer ces images",
            type: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confimer',
            cancelButtonText: 'Annuler'
        }).then(function () {
                $.ajax({
                    url: Routing.generate('reception_image_suppr_del'),
                    type: 'DELETE',
                    data: {ids: ids},
                    success: function(data){
                        show_info('', data.message, data.type);

                        if(data.type === 'success'){
                            $('#image-list').jqGrid('setGridParam', {
                                url: Routing.generate('reception_image_suppr_list'),
                                postData:{
                                    dossierid: $('#filtre-dossier').val(),
                                    exercice: $('#filtre-exercice').val(),
                                    datescan: $('#filtre-date-scan').val(),
                                    lot: $('#filtre-lot').val()
                                },
                                datatype: 'json'
                            })
                                .trigger('reloadGrid', {fromServer: true, page: 1});
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

    $(document).on('change', '#chk-all', function(e){
       e.preventDefault();
       e.stopPropagation();

       var isChecked = $(this).is(':Checked'),
           checkBoxes = $('#image-list').find('.chk').find('input[type="checkbox"]');

       checkBoxes.each(function () {
               $(this).prop('checked', isChecked);
       });

    });
});

function setDossiers(clientid){
    $.ajax({
        url: Routing.generate('reception_image_suppr_dossier'),
        type: 'GET',
        data: {clientid: clientid},
        success: function (data) {
            $('#filtre-dossier').html(data);
        }
    })
}

function setExercices(dossierid){
    $.ajax({
        url: Routing.generate('reception_image_suppr_exercice'),
        type: 'GET',
        data: {dossierid: dossierid},
        success: function(data){
            $('#filtre-exercice').html(data);

            var dossier = $('#filtre-dossier').val(),
                exercice = $('#filtre-exercice').val();

            if(parseInt(dossier)!== -1 && exercice !== '') {
                setDateScans(dossier, exercice);
            }
        }
    })
}

function setDateScans(dossierid, exercice){
    $.ajax({
        url: Routing.generate('reception_image_suppr_date_scan'),
        type: 'GET',
        data:{
            dossierid: dossierid,
            exercice: exercice
        },
        success: function(data){
            $('#filtre-date-scan').html(data);
        }

    })
}

function setLots(dossierid, exercice, datescan){
    $.ajax({
        url: Routing.generate('reception_image_suppr_lot'),
        type: 'GET',
        data:{
            dossierid: dossierid,
            exercice: exercice,
            datescan: datescan
        },
        success: function(data){
            $('#filtre-lot').html(data);
        }

    })
}