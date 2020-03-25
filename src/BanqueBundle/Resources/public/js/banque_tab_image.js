var index_tab = 0;
$(document).ready(function () {
    $(document).on('click', '.t_image', function () {
        var client = $('#client_gestion_tache').val(),
            impute_grid = $('#js_impute_liste'),
            banqueCompteId = $(this).closest('tr').attr('id'),
            exercice = $('#exercice').val(),
            site = $('#site_gestion_tache').val(),
            rb1 = impute_grid.jqGrid('getCell',banqueCompteId,'t-rb');

        if(client == undefined && site == undefined){
            client = $('#client').val();
            site = $('#site').val();
        }

        if(rb1 === ''){
            show_info('Controle image', 'Aucun image disponible', 'info');
            return false;
        }
        $.ajax({
            url: Routing.generate('banque_tab_image'),
            type: 'POST',
            data: {
                client : client,
                site : site,
                banqueCompteId: banqueCompteId,
                exercice : exercice,
                dateScan : false
            },
            success: function (data) {
                var animated = 'bounceInRight',
                    titre = '<span>Tableau des images</span>';
                show_modal(data,titre, animated);
                $('#modal-body .tab-image-content tbody').each(function(){
                    var tr_banque = $(this).find('tr').find('.cat_banque').parent();

                });
            }
        });
    });

    $(document).on('click','.date-scan-chevron-down', function () {
        $(this).removeClass('date-scan-chevron-down').addClass('date-scan-chevron-up');
        $(this).parent('.ibox-tools').toggleClass('js-opened');
        $('.ibox-content-date-scan').toggleClass('js-opened');
        $(this).parent('.ibox-tools').find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $(document).on('click','.date-scan-chevron-up', function () {
        $(this).removeClass('date-scan-chevron-up').addClass('date-scan-chevron-down');
        $(this).parent('.ibox-tools').toggleClass('js-opened');
        $('.ibox-content-date-scan').toggleClass('js-opened');
        $(this).parent('.ibox-tools').find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    $(document).on('click','.date_scan_select',function(){
        $('.date_scan_select').removeClass('white-bg-date-scan');
        $(this).addClass('white-bg-date-scan');
        var date = $(this).html().trim();
        var dateDeb = $("#modal-body #js_debut_date").val();
        var dateFin = $("#modal-body #js_fin_date").val();
        if(dateDeb === '')
            $("#modal-body #js_debut_date").val(date);
        else{
            if(dateFin === ''){
                $("#modal-body #js_fin_date").val(date);
            }else{
                var perioDeb = dateDeb.split("-"),
                    perioFin = dateFin.split("-"),
                    perioSelect = date.split("-");
                dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0];
                dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
                var dateSelect = perioSelect[2] + '-' + perioSelect[1] + '-' + perioSelect[0];
                var d = new Date(dateDeb);
                var f = new Date(dateFin);
                var s = new Date(dateSelect);
                if(s.getTime() >= d.getTime()){
                    $("#modal-body #js_fin_date").val(date);
                }else if(s.getTime() < d.getTime()){
                    $("#modal-body #js_debut_date").val(date);
                }
            }
        }

    });

    $(document).on('click','#btn_search_image_date_scan',function(){
        var client = $('#client_gestion_tache').val(),
            banqueCompteId = $('#banque_compte_id').attr('data-banque-compte-id'),
            exercice = $('#exercice').val(),
            periodeDeb = $("#modal-body #js_debut_date").val(),
            periodeFin = $("#modal-body #js_fin_date").val();
        if ( periodeDeb ===  '' || periodeFin === '') {
            show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
            return false;
        }
        var perioDeb = periodeDeb.split("-"),
            perioFin = periodeFin.split("-");
        var dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0],
            dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
        $.ajax({
            url: Routing.generate('banque_tab_image'),
            type: 'POST',
            data: {
                client : client,
                banqueCompteId: banqueCompteId,
                exercice : exercice,
                perioddeb: dateDeb,
                periodfin: dateFin,
                dateScan : true
            },
            success: function (data) {
                var animated = 'bounceInRight',
                    titre = '<span>Tableau des images</span>';
                show_modal(data,titre, animated);
                $("#modal-body #js_debut_date").val(periodeDeb);
                $("#modal-body #js_fin_date").val(periodeFin);
            }
        });
    });

    $(document).on('click', '.td-tab-count-image', function(){
        index_tab ++;
        $('.tr-tab-image').removeAttr('style');
        $(this).parent().attr('style', 'color: #915608;border: 1px solid #fcd113;');
        var client = $('#client_gestion_tache').val(),
            banqueCompteId = $('#banque_compte_id').attr('data-banque-compte-id'),
            exercice = $('#exercice').val(),
            categorieId = $(this).parent().attr('data-cat-id'),
            count_tab_image = $(this).html().trim(),
            detail = $(this).attr('data-detail'),
            isScan = '',
            dateDeb = null,
            dateFin = null;

        if(client === undefined){
            client = $('#client').val();
        }

        if(count_tab_image === '0'){
            show_info('Controle image', 'Aucun image disponible', 'info');
            return false;
        }
        if(detail === 'tous' || detail === 'tous-non-lettre'){
            isScan =  detail;
        }else if(detail === 'scan' || detail === 'scan-non-lettre'){
            isScan =  detail;
            var periodeDeb = $("#modal-body #js_debut_date").val(),
                periodeFin = $("#modal-body #js_fin_date").val();
            var perioDeb = periodeDeb.split("-"),
                perioFin = periodeFin.split("-");
            dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0];
            dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
        }
        $.ajax({
            url: Routing.generate('banque_tab_image_details'),
            type: 'POST',
            data: {
                client : client,
                banqueCompteId: banqueCompteId,
                exercice : exercice,
                categorieId : categorieId,
                index_tab : index_tab,
                isScan : isScan,
                perioddeb: dateDeb,
                periodfin: dateFin
            },
            success: function (data) {
                var options = { modal: false, resizable: true,title: '' };
                modal_ui(options,data, false, 0.8, 0.6);
                var detail_image_grid =  $('#js_tb_detail_image_'+index_tab);
                detail_image_grid.jqGrid('GridUnload');
                detail_image_grid = $('#js_tb_detail_image_'+index_tab);
                var mydata = $('.details-data-'+index_tab).attr('data-datas');
                var isEncours = $('.details-data-'+index_tab).attr('data-encours');
                var rowDataImage = $.parseJSON(mydata);
                var lastsel_id;
                var multiselectImage = false;
                var rownumbersImage = true;
                var colNameModel = ['Image', 'Date envoi', 'Date pièce', 'RS', 'Avancement', 'ImageId', 'categorieId'];
                var ColModelImage = [
                        {
                            name: 'tb_detail_image',
                            index: 'tb_detail_image',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            classes: 'js-tb-detail-image'
                        },
                        {
                            name: 'tb_detail_datescan',
                            index: 'tb_detail_datescan',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            sorttype: 'date',
                            classes: 'js-tb-detail-datescan'
                        },
                        {
                            name: 'tb_detail_datepiece',
                            index: 'tb_detail_datepiece',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            sorttype: 'date',
                            classes: 'js-tb-detail-datepiece'
                        },
                        {
                            name: 'tb_detail_rs',
                            index: 'tb_detail_rs',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-tb-detail-rs'
                        },
                        {
                            name: 'tb_detail_avancement',
                            index: 'tb_detail_avancement',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-tb-detail-avancement'
                        },/*
                        {
                            name: 'tb_detail_rapprochement',
                            index: 'tb_detail_rapprochement',
                            align: 'left',
                            width: 80,
                            editable: false,
                            sortable: true,
                            classes: 'js-tb-detail-rapprochement'
                        },*/
                        {
                            name: 'tb_image_id',
                            index: 'tb_image_id',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-image-id'
                        },
                        {
                            name: 'tb_categorie_id',
                            index: 'tb_categorie_id',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-categorie-id'
                        }
                    ];
                if(isEncours){
                    colNameModel = ['Image', 'Date envoi', 'Date pièce', 'RS', 'Avancement', '<input style="float: right !important; margin: 2px 0 0 !important;" type="checkbox" id="checkAll" onclick="checkBox(this)" />Envoie Reception', 'ImageId', 'categorieId'];
                    multiselectImage = true;
                    rownumbersImage = false;
                    ColModelImage = [
                        {
                            name: 'tb_detail_image',
                            index: 'tb_detail_image',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            classes: 'js-tb-detail-image'
                        },
                        {
                            name: 'tb_detail_datescan',
                            index: 'tb_detail_datescan',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            sorttype: 'date',
                            classes: 'js-tb-detail-datescan'
                        },
                        {
                            name: 'tb_detail_datepiece',
                            index: 'tb_detail_datepiece',
                            align: 'center',
                            editable: false,
                            sortable: true,
                            width: 40,
                            sorttype: 'date',
                            classes: 'js-tb-detail-datepiece'
                        },
                        {
                            name: 'tb_detail_rs',
                            index: 'tb_detail_rs',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-tb-detail-rs'
                        },
                        {
                            name: 'tb_detail_avancement',
                            index: 'tb_detail_avancement',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-tb-detail-avancement'
                        },
                        {
                            name: 'tb_checkbox',
                            width: 80,
                            align: "center",
                            editoptions: { value: "True:False" },
                            editrules: { required: true },
                            formatter: "checkbox",
                            formatoptions: { disabled: false },
                            sortable: false,
                            classes: 'tb_checkbox'
                        },
                        {
                            name: 'tb_image_id',
                            index: 'tb_image_id',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-image-id'
                        },
                        {
                            name: 'tb_categorie_id',
                            index: 'tb_categorie_id',
                            align: 'left',
                            width: 40,
                            editable: false,
                            sortable: true,
                            classes: 'js-categorie-id'
                        }
                    ];
                }

                detail_image_grid.jqGrid({
                    datatype: 'local',
                    height: 600,
                    autowidth: true,
                    rownumbers: true,
                    viewrecords: true,
                    hidegrid: false,
                    shrinkToFit: true,
                    loadonce: true,
                    altRows: true,
                    rownum: 1000,
                    sortable: true,
                    colNames: colNameModel,
                    colModel: ColModelImage,/*
                    beforeSelectRow: function (rowid, e) {
                        var $self = $(this),
                            iCol = $.jgrid.getCellIndex($(e.target).closest("td")[0]),
                            cm = $self.jqGrid("getGridParam", "colModel"),
                            localData = $self.jqGrid("getLocalRow", rowid);
                        if (cm[iCol].name === "tb_checkbox" && e.target.tagName.toUpperCase() === "INPUT") {
                            localData.MyPrint = $(e.target).is(":checked");
                            alert(localData.tb_image_id);
                        }
                        return true; 
                    },*/

                    onSelectRow: function(id) {
                        if (id && id !== lastsel_id) {
                            detail_image_grid.jqGrid('restoreRow',lastsel_id);
                            lastsel_id=id;
                        }
                        var val_t_image_id = detail_image_grid.jqGrid('getCell',id,'tb_image_id');
                        var val_t_categorie_id = detail_image_grid.jqGrid('getCell',id,'tb_categorie_id');
                        var isExistImage = false;
                        $('.details-image-'+index_tab+' .details-image-id').each(function(){
                            if($(this).attr('data-id') === val_t_image_id){
                                if(!isExistImage){
                                    $(this).removeClass('hidden');
                                }
                                isExistImage = true;
                            }else{
                                $(this).addClass('hidden');
                            }

                        });
                        $('.details-image-'+index_tab+' #image_no').val(val_t_image_id);
                        var height_jqgrid = $('.details-data-'+index_tab).height();
                        if(val_t_categorie_id === '0'){
                            height_jqgrid = $('.details-data-'+index_tab).height() - 90;
                            $('.details-image-'+index_tab+' .details-image-id').attr('style','height:'+height_jqgrid+'px;');
                        }else{
                            $('.details-image-'+index_tab+' .details-image-id').attr('style','height:'+height_jqgrid+'px;');
                        }
                    },

                    loadComplete: function (data) {
                        var height_jqgrid = $('.details-data-'+index_tab).height();
                        $('.details-image-'+index_tab).attr('style','height:'+height_jqgrid+'px;');
                    },
                    ajaxRowOptions: {async: true}
                });
                detail_image_grid.jqGrid('hideCol',["tb_image_id"]);
                detail_image_grid.jqGrid('hideCol',["tb_categorie_id"]);
                if(isEncours)
                    $('#js_tb_detail_image_'+index_tab+'_tb_checkbox div').attr('class', '');
                for(var i=0;i<=rowDataImage.length;i++)
                    detail_image_grid.jqGrid('addRowData',i+1,rowDataImage[i]);

                var top_rowid = $('#js_tb_detail_image_'+index_tab+' tr:nth-child(2)').attr('id');
                detail_image_grid.setSelection(top_rowid, true);
                resize_tab_image();
            }
        });
    });

    $(window).bind('resize', function () {
        resize_tab_image();
    });

    $(document).on('change', '#js_option_statut_image', function () {
        var statut_select = $(this).val();
        var detail_image_grid = $('#js_tb_detail_image_'+index_tab);
        var rowKey = detail_image_grid.jqGrid('getGridParam',"selrow");
        var imageId = detail_image_grid.jqGrid('getCell',rowKey,'tb_image_id');
        if(statut_select !== '0'){
            $.ajax({
                url: Routing.generate('banque_tab_image_change_statut'),
                type: 'POST',
                data: {
                    imageId : imageId,
                    statut_select: statut_select
                },
                success: function (data) {
                    show_info('','Modification statut image enregistrée avec succès');
                    return false;
                }
            });
        }
    });

    $(document).on("click", '.next', function(){
        var detail_image_grid = $('#js_tb_detail_image_'+index_tab);
        var image_now = $('.details-image-'+index_tab+' #image_no').val();
        var image_select;
        $('.details-image-'+index_tab+' .details-image-id').each(function(){
            if($(this).attr('data-id') === image_now){
                image_select = $(this).next().attr('data-id');
            }
        });
        var rows = detail_image_grid.getDataIDs();
        for (var i = 0; i < rows.length; i++) {
            var image_id = detail_image_grid.getCell(rows[i], "tb_image_id");
            if(image_id === image_select){
                detail_image_grid.setSelection(rows[i], true);
            }
        }
        if(image_select === undefined){
            var top_rowid = $('#js_tb_detail_image_'+index_tab+' tr:nth-child(2)').attr('id');
            detail_image_grid.setSelection(top_rowid, true);
        }
    });
    $(document).on("click", '.previous', function(){
        var detail_image_grid = $('#js_tb_detail_image_'+index_tab);
        var image_now = $('.details-image-'+index_tab+' #image_no').val();
        var image_select;
        $('.details-image-'+index_tab+' .details-image-id').each(function(){
            if($(this).attr('data-id') === image_now){
                image_select = $(this).prev().attr('data-id');
            }
        });
        var rows = detail_image_grid.getDataIDs();
        for (var i = 0; i < rows.length; i++) {
            var image_id = detail_image_grid.getCell(rows[i], "tb_image_id");
            if(image_id === image_select){
                detail_image_grid.setSelection(rows[i], true);
            }
        }
        if(image_select === undefined){
            detail_image_grid.setSelection(rows[rows.length-1], true);
        }
    });

    $(document).on('click', '.situation-image-chevron-down', function(){
        $(this).removeClass('situation-image-chevron-down').addClass('situation-image-chevron-up');
        $(this).parent('.ibox-tools').toggleClass('js-opened');
        $('.ibox-content-date-scan').toggleClass('js-opened');
        $(this).parent('.ibox-tools').find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $('.sitimage-banque-content').css('display','block');
        $('#result_rb_bancaire').css('display','block');
        $('#result_piece_manquant').css('display','block');
        $('.result-taf').css('display','block');
    });

    $(document).on('click', '.situation-image-chevron-up', function(){
        $(this).removeClass('situation-image-chevron-up').addClass('situation-image-chevron-down');
        $(this).parent('.ibox-tools').toggleClass('js-opened');
        $('.ibox-content-date-scan').toggleClass('js-opened');
        $(this).parent('.ibox-tools').find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $('.sitimage-banque-content').css({'display':'none'});
        $('#result_rb_bancaire').css('display','none');
        $('#result_piece_manquant').css('display','none');
        $('.result-taf').css('display','none');
    });

    $(document).on('click', '.chevron-down-tab-bank', function () {
        $(this).removeClass('chevron-down-tab-bank').addClass('chevron-up-tab-bank');
        $(this).removeClass('fa fa-sort-desc').addClass('fa fa-caret-right');
        $('.result-bank-scat').css('display','none');
    });

    $(document).on('click', '.chevron-up-tab-bank', function () {
        $(this).removeClass('chevron-up-tab-bank').addClass('chevron-down-tab-bank');
        $(this).removeClass('fa fa-caret-right').addClass('fa fa-sort-desc');
        $('.result-bank-scat').css('display','table-row');
    });

    $(document).on('click', '.show_lettrage_rapprochement', function () {
        var imageId = $(this).attr('data-id');
        var type = imageId.split('-')[1];
        if(type === "alettre"){
            $.ajax({
                url: Routing.generate('banque_tab_image_show_rapprochement'),
                type: 'POST',
                data: {
                    imageId : imageId.split('-')[0]
                },
                success: function (data) {
                    var options = { modal: false, resizable: true,title: 'Rapprochement' };
                    modal_ui(options,data, false, 0.8, 0.6);
                    return false;
                }
            });
        }else{
            $.ajax({
                data: {
                    imageId: imageId.split('-')[0],
                    height:$(window).height()
                },
                url: Routing.generate('image_consultation'),
                type: 'POST',
                dataType: 'html',
                success: function(data){
                    var options = { modal: false, resizable: true,title: '' };
                    modal_ui(options,data, false, 0.8, 0.6);

                    $('.js_embed').each(function(){
                        $(this).height($(this).closest('.row').height() - 25);
                    });
                }
            });
        }
    });

    $(document).on('change', '.tb_checkbox input, #checkAll', function () {
        var tabImageId = [];
        $('#js_tb_detail_image_'+index_tab).find('tr').each(function(){
            if (!$(this).hasClass('jqgfirstrow')){
                var checked = $(this).find('.tb_checkbox input');
                var imageId = $(this).find('.js-image-id').text();
                if(checked.is(":checked")){
                    tabImageId.push({imageId:imageId, checked: true});
                }
                else if(checked.is(":not(:checked)")){
                    tabImageId.push({imageId:imageId, checked: false});
                }
            }
        });
        $.ajax({
            url:Routing.generate('banque_priorite_image'),
            type: "POST",
            dataType: "json",
            data: {
                'data': tabImageId
            },
            async: true,
            success: function (data)
            {
                show_info('Priorité image', 'Les images selectionnées sont mises en priorité', 'info');
                return;
            }
        });
    });
});

function resize_tab_image(){
    var detail_image_grid = $('#js_tb_detail_image_'+index_tab);
    detail_image_grid.jqGrid("setGridWidth", detail_image_grid.closest(".ui-jqgrid").parent().width());
    detail_image_grid.jqGrid("setGridHeight", detail_image_grid.closest('.modal-body').height()-50);
    var height = detail_image_grid.closest('.modal-body').height()-20;
    $('.details-image-'+index_tab).attr('style', 'height:'+height+'px;');
    var rowKey = detail_image_grid.jqGrid('getGridParam',"selrow");
    var val_t_categorie_id = detail_image_grid.jqGrid('getCell',rowKey,'tb_categorie_id');
    if(val_t_categorie_id === '0'){
        height = height - 90;
    }
    $('.details-image-'+index_tab+' .details-image-id').attr('style','height:'+height+'px;');
}

function checkBox(obj) {
    $('.tb_checkbox input').prop('checked', obj.checked);
}
