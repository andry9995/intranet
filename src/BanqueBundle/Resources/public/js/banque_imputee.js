// $(document).ready(function(){
//     $(document).on('click','.cl-tab',function(){
//         go();
// });
// });

// $(function () {
    // var imputee_grid = $('#get_listes_imputees');
    // imputee_grid.jqGrid({
    //     url: Routing.generate('banque_imputee_liste'),
    //     datatype: 'json',
    //     loadonce: true,
    //     sortable: true,
    //     autowidth: true,
    //     height: 500,
    //     shrinkToFit: true,
    //     viewrecords: true,
    //     rownumbers: true,
    //     rowNum: 100,
    //     rowList: [100, 200, 500],
    //     caption: 'Imputées',
    //     hidegrid: false,
    //     colNames: ['Clients', 'Dossier', 'Banque', 'Compte', 'Ecart', 'RB', 'OB', 'Tot lignes', 'Lettrée', 'Clef', 'A lettrée', 'Pièce manq', 'Cheq inconnus', '%Rapproché', 'Priorité'],
    //     colModel: [
    //         {name: 't-impute-clients', index: 't-impute-clients', editable: false, width: 80},
    //         {name: 't-impute-dossier', index: 't-impute-dossier', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-banque', index: 't-impute-banque', editable: false, width: 250, align: 'center'},
    //         {name: 't-impute-compte', index: 't-impute-compte', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-ecart', index: 't-impute-ecart', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-rb', index: 't-impute-rb', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-ob', index: 't-impute-ob', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-tot', index: 't-impute-tot', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-lettre', index: 't-impute-lettre', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-clef', index: 't-impute-clef', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-alettre', index: 't-impute-alettre', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-piece', index: 't-impute-piece', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-cheq', index: 't-impute-cheq', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-rapproche', index: 't-impute-rapproche', editable: false, width: 80, align: 'center'},
    //         {name: 't-impute-prio', index: 't-impute-prio', editable: false, width: 80, align: 'center'},
    //     ],
    //     ajaxRowOptions: {async: true}
    // });
// });

var index_ui_modal_log = 0;

$(document).ready(function(){

    $(document).on('change', 'input[name="show-filter-item"]', function() {
        isGo = true;
        filtrerAffichage();
    });

    $(document).on('change', '#show-aucun-image', function() {
        isGo = true;
        filtrerAffichage();
    });

    $(document).on('click', '.t_relbq', function() {
        var rowKey = $(this).closest('tr').attr('id'),
            dossier = $('#js_impute_liste').getCell(rowKey, 't-dossier'),
            value = $('#js_impute_liste').getCell(rowKey, 't_relbq'),
            options = { modal: false, resizable: true,title: 'LISTES LOG EMAILS' };
        index_ui_modal_log++;
        if(value.trim() == '' ) return;
        $.ajax({
            url: Routing.generate('app_notification_log'),
            type: 'POST',
            data: {
                dossier: dossier,
                index: index_ui_modal_log
            },
            dataType: 'html',
            success: function(data) {
                modal_ui(options,data, false,0.6,0.5);
            }
        });
    });

    $(document).on('click', '.show_contenu_mail', function() {
        var id = $(this).closest('tr').attr('data-id'),
            options = { modal: false, resizable: true,title: 'Contenu' };
        $.ajax({
            url: Routing.generate('app_notification_log_contenu_mail'),
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'html',
            success: function(data) {
                modal_ui(options,data, false,0.8,0.6);
            }
        });
    });
});


/*
* Image valider formatter
*/
function cell_image_importe_formatter(cell_value, options, row_object) {

    var new_val = '';
    var value = '';
    /* var ecart = row_object['t_ecart'];
     var acontroler = row_object['t-acontroler'].split('-')[0];
     var ctrl_saisie = row_object['t-acontroler'].split('-')[1];
     var rb1 = row_object['t_rb'];
     acontroler = parseInt(acontroler);*/
    var color = '#ffd700';
    var title = 'Non valider';
    //ecart = number_format(ecart, '2', ',', ' ');

    if (cell_value === 1) {
        color = '#008000';
        title = 'Validé';
        value = 'Imp.';
    }else if(cell_value === 2){
        color = '#ffd700';
        title = 'Non validé';
        value = 'Imp.';
    }else if(cell_value === 3){
        color = '#e95443';
        title = 'Non validé';
        value = 'Imp.';
    }else{
        color = '#e95443';
        title = 'Non validé';
        value = 'Nn imp.';
    }

    if(cell_value === ''){
        new_val = '';
    }else{
        new_val = value+'<i title="'+title+'" class="fa fa-circle" style="color:' + color + ';margin-left: 0px !important;float: right !important; padding-top: 3px;"></i>';
    }

    return new_val;
}

/*
* Image importe formatter
*/
function cell_image_valider_formatter(cell_value, options, row_object) {
    /*var acontroler = row_object['t-acontroler'].split('-')[0];
    var ctrl_saisie = row_object['t-acontroler'].split('-')[1];
    ctrl_saisie = parseInt(ctrl_saisie);
    acontroler = parseInt(acontroler);
    var color = '';
    var title = '';*/
    /*if( ctrl_saisie === 0 ){
        if (acontroler === 0) {
            color = '#008000';
            title = 'Validé';
        }else{
            color = '#ffd700';
            title = 'Non validé';
        }

        if(cell_value === 'Incompl.'){
            color = '#e95443';
            title = 'Non validé';
        }
    }else{
        color = '#e95443';
        title = 'Non validé';
    }

    if(cell_value === 'Aucun'){
        color = '#e95443';
        title = 'Non valider';
    }

    if(cell_value === ''){
        new_val = cell_value;
    }else{
        new_val = cell_value+'<i title="'+title+'" class="fa fa-circle" style="color:' + color + ';margin-left: 0px !important;float: right !important; padding-top: 3px;"></i>';
    }*/

    /*if(cell_value === 'M-2' || cell_value === 'M-3'){
        color = '#ffd700';
        title = 'Non validé';
    }else if(cell_value === 'Inc.' || cell_value === 'Auc.'){
        color = '#e95443';
        title = 'Non validé';
    }else{
        color = '#008000';
        title = 'Validé';
    }*/

    if(cell_value === '' || cell_value === null) return cell_value;
    var color = '';
    var title = '';
    if(cell_value === 10){
        cell_value = 'Inc.'
    }else if(cell_value === 11){
        cell_value = 'Auc.'
    }else{
        cell_value = 'M-'+cell_value;
    }

    if(cell_value === 'M-1'){
        color = '#008000';
        title = 'Validé';
    }else if(cell_value === 'Inc.' || cell_value === 'Auc.'){
        color = '#e95443';
        title = 'Non validé';
    }else{
        color = '#ffd700';
        title = 'Non validé';
    }
    return cell_value+'<i title="'+title+'" class="fa fa-circle" style="color:' + color + ';margin-left: 0px !important;float: right !important; padding-top: 3px;"></i>';
}

/*
* Indicateur formatter
*/
function cell_indicateur_formatter(cell_value, options, row_object) {

    /*var new_val = '';
    var rb1 = row_object['t_rb'];
    var rb2 = row_object['t_rb2'];
    var ob = row_object['t_ob'];
    var image = row_object['t_image'];
    var alettre = row_object['t_alettre'];
    var ecart = row_object['t_ecart'];
    var color = '';
    var etat = row_object['t_etat'];

    if(etat == undefined){
        var impute_grid = $('#js_impute_liste');
        var localData = impute_grid.jqGrid("getLocalRow", options.rowId);
        etat = localData.EtatCompte;
        rb1 = localData.t_rb;
        rb2 = localData.t_rb2;
        alettre = localData.t_alettre;
        ecart = localData.t_ecart;
        image = localData.t_image;
        ob = localData.t_ob;
    }

    if(!etat){
        color = '#008000';
    }else{
        if(rb1 === 'Inc.' || ob === 'PB' || image === 'color: #e95443;' || rb1 === 'Auc.'){
            color = '#e95443';
        }else if(alettre === 0 && (rb1 === 'M-1' || rb1 === 'A jour') && (rb2 === 'Imp.' && (ecart === '0,00' || ecart === '-0,00' || Math.round(ecart) === 0))){
             color = '#008000';
        }else{
            color = '#ffd700';
        }
    }*/
    var color = '';
    if(cell_value == 100) return '';
    if(cell_value === 1){
        color = '#008000';
    }else if(cell_value === 2){
        color = '#ffd700';
    }else{
        color = '#e95443';
    }

    var new_val = '<i class="fa fa-circle" style="color:' + color + ';margin-left: 0px !important; padding-top: 3px;"></i>';
    return new_val;
}

/*
* Priorité formatter
*/
function cell_priorite_formatter(cell_value, options, row_object){
    var new_val = '';
    if(cell_value == null || cell_value === ''){
        return new_val;
    }else{
        var cellPriorite = cell_value.split(' ')[1];
        if(cellPriorite === 'Expiré'){
            var arr = '<img src="' + assetsBaseDir + 'feu.png'+ '" style = "height: 20px;">';
            return arr;
        }
    }
    var cellPriorite = cell_value.split(' ')[1];
    new_val = '<i class="fa fa-circle t-priorite pointer" style="color:' + cellPriorite + ';" data-nbj = "'+cell_value.split(' ')[2]+'"></i>';
    return new_val;
}

/*
* OB formatter
*/
function cell_ob_formatter(cell_value, options, row_object) {
    var new_val = '';
    var color = '';
    var classOb = '';

    if(cell_value === 'PB'){
        color = '#e95443';
        classOb = 'isPB';
    }else{
        color = '#008000';
        classOb = 'isOB';
    }
    new_val = '<i class="fa fa-circle '+classOb+' t_ob_qtip pointer" style="color:' + color + ';margin-left: 0px !important; padding-top: 3px;"></i>';
    return new_val;
}

/*
* Image icone formatter
*/
function cell_image_icon_formatter(cell_value, options, row_object) {
    var new_val = '';
    var colorIconImage = '';
    if(cell_value === 0){
        colorIconImage = 'color: #008000;';
    }else if(cell_value === 2){
        colorIconImage = 'color: #e95443;';
    }else{
        colorIconImage = 'color: #ffd700;';
    }
    new_val = '<i class="fa fa-recycle pointer" style="'+colorIconImage+' font-size: 15px;"  aria-hidden="true"></i>';
    return new_val;
}

/*
* Tache formatter
*/
function cell_image_tva(cell_value, options, row_object){
    var new_val = '';
    var tache_split = cell_value.split('=');
    var color = 'color: #8400ff;';
    var icon = ' ';
    var tache_name = tache_split[1];
    if(tache_name != undefined){
        tache_name = tache_name.replace(' ', '');
        tache_name = tache_name.replace(' ', '');
    }
    if(tache_split[0]){
        if(tache_split[2]){
            return tache_name+'<img class="fa fa-play-circle-o qtip-tache pointer" src="' + assetsBaseDir + 'play-e95443.png'+ '" style = "color: #e95443; height: 13px; float:right;">';
        }else{
            return (tache_name != undefined) ? tache_name : '';
        }
    }else{
        if(tache_split[2]){
            return tache_name+'<img class="qtip-tache pointer" src="' + assetsBaseDir + 'play-8400ff.png'+ '" style = "color: #8400ff; height: 13px; float:right;">';
        }else{
            return (tache_name != undefined) ? tache_name : '';
        }
    }
    return (tache_name != undefined) ? tache_name : '';
}

/*
* Checkox actif formater
*/
function cell_checkbox_actif_formatter(cell_value, options, row_object) {
    var new_val = '';
    var compte = row_object['t-compte'];
    var checked = '';
    if(compte != null){
        checked = (cell_value) ? 'checked' : '';
        new_val = '<input type="checkbox" value="'+cell_value+'" '+checked+'>';
    }
    return new_val;
}

function prepare_tooltip(){
    $('.t_ob_qtip').qtip({
        content: {
            text: function (event, api) {
                var impute_grid = $('#js_impute_liste');
                var row_key = impute_grid.jqGrid('getGridParam', 'selrow');
                var statutOb = impute_grid.getCell(row_key, 't_ob');
                var dataObMq = impute_grid.getCell(row_key, 't-data-ob-m');
                dataObMq = $.parseJSON(dataObMq);
                statutOb = statutOb.split('class')[1];
                statutOb = statutOb.split(' ')[2];
                var table_html = '<table class="table table-bordered">';
                if (statutOb === 'isPB') {
                    table_html += '' +
                        '<tr>' +
                        '<th>Sous Categorie</th>' +
                        '<th>Total</th>' +
                        '</tr>';
                    $.each(dataObMq, function (i,v)
                    {
                        if(v.nb !== 0){
                            table_html += '' +
                                '<tr>' +
                                '<td>'+v.libelle+'</td>' +
                                '<td>'+number_format(v.nb, 0, '', ' ')+'</td>' +
                                '</tr>';
                        }
                    });
                }else{
                    table_html += '<tr><td class="col-sm-12 aucun-mq-ob"> <i class="fa fa-check-circle"></i> Aucun Manquant </td></tr>';
                }
                table_html += '</table>';
                return table_html;
            }
        },
        position: {
            viewport: $(window),
            corner: {
                target: 'topLeft',
                tooltip: 'middleRight'
            },
            adjust: {
                x: -5,
                y: -5
            },
            container: $('#tab-impute')
        },
        show : 'click',
        hide : 'unfocus',
        style: {
            classes: 'qtip-light qtip-shadow'
        }
    });

    $('.t-priorite').qtip({
        content: {
            text: function (event, api) {
                var impute_grid = $('#js_impute_liste');
                var row_key = impute_grid.jqGrid('getGridParam', 'selrow');
                var priorite = impute_grid.getCell(row_key, 't-priorite');
                var nbjr = priorite.split('data-nbj');
                nbjr = nbjr[1].split('"');
                nbjr = nbjr[1];
                var label_html = '<label class="">Moins '+nbjr+' Jours</label>';
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
                x: -5,
                y: -5
            },
            container: $('#tab-impute')
        },
        show : 'click',
        hide : 'unfocus',
        style: {
            classes: 'qtip-light qtip-shadow'
        }
    });

    $('.qtip-tache').qtip({
        content: {
            text: function (event, api) {
                var impute_grid = $('#js_impute_liste');
                var row_key = impute_grid.jqGrid('getGridParam', 'selrow');
                var data_tache = impute_grid.getCell(row_key, 't-data-tache');
                data_tache = JSON.parse(data_tache);
                var table_html = '<table class="table table-bordered table-wrapper-gestion-tache-scroll-y my-custom-scrollbar-gestion-tache">';
                var color = '';
                var responsable = $('#js_filtre_respons_tache').val();
                /*var responsable = '';*/
                table_html += '' +
                    '<tr>' +
                    '<th>Titre</th>' +
                    '<th>Date</th>' +
                    '</tr>';
                $.each(data_tache, function (i,v)
                {
                    color = '';
                    if(v.expirer){
                        color = 'color: #e95443;'
                    }
                    if(responsable != '' && responsable == v.responsable){
                        table_html += '' +
                            '<tr>' +
                            '<td style = "'+color+'">'+v.titre2+'</td>' +
                            '<td style = "'+color+'">'+v.date+'</td>' +
                            '</tr>';
                    }else if(responsable === '' || responsable == undefined){
                        table_html += '' +
                            '<tr>' +
                            '<td style = "'+color+'">'+v.titre2+'</td>' +
                            '<td style = "'+color+'">'+v.date+'</td>' +
                            '</tr>';
                    }
                });
                table_html += '</table>';
                return table_html;
            }
        },
        position: {
            viewport: $(window),
            corner: {
                target: 'topLeft',
                tooltip: 'middleRight'
            },
            adjust: {
                x: -5,
                y: -5
            },
            container: $('#tab-impute')
        },
        show : 'click',
        hide : 'unfocus',
        style: {
            classes: 'qtip-light qtip-shadow'
        }
    });

    $('.qtip_instruction').qtip({
        content: {
            text: function (event, api) {
                var impute_grid = $('#js_impute_liste');
                var row_key = impute_grid.jqGrid('getGridParam', 'selrow');
                var html = impute_grid.getCell(row_key, 't-data-inst');
                var label_html = '<label class="">'+html+'</label>';
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
                x: -5,
                y: -5
            },
            container: $('#tab-impute')
        },
        show : 'click',
        hide : 'unfocus',
        style: {
            classes: 'qtip-instruction qtip-light qtip-shadow'
        }
    });
}

function filtrerAffichage() {
    var impute_grid = $('#js_impute_liste');
    var selected = $(document).find('input[name="show-filter-item"]:checked').val();
    var show_aucun_image_check = $('#show-aucun-image');
    var field = "t_statut";
    var field_aucun_image = "t-aucun-image";
    var selected_aucun_image;
    if (show_aucun_image_check.prop('checked')) {
        selected_aucun_image = 11;
    } else {
        selected_aucun_image = "Tous";
    }
    setSearch(selected, field, selected_aucun_image, field_aucun_image);
    if (isGo) {
        isGo = false;
        impute_grid.trigger("reloadGrid", {page: 1});
    }
}

function setSearch(selected, field, selected_aucun_image, field_aucun_image) {
    var impute_grid = $('#js_impute_liste');
    if (typeof selected_aucun_image !== 'undefined' && selected_aucun_image !== 'Tous' && selected !== 'Tous') {
        impute_grid.jqGrid("setGridParam", {
            postData: {
                filters: JSON.stringify({
                    groupOp: "AND",
                    rules: [
                        {field: field, op: "eq", data: selected},
                        {field: field_aucun_image, op: "ne", data: selected_aucun_image}
                    ]
                })
            },
            search: true
        });
    }
    else {
        if (typeof selected !== 'undefined' && selected === 'Tous' && selected_aucun_image !== 'Tous') {
            impute_grid.jqGrid("setGridParam", {
                postData: {
                    filters: JSON.stringify({
                        groupOp: "AND",
                        rules: [
                            {field: field_aucun_image, op: "ne", data: selected_aucun_image}
                        ]
                    })
                },
                search: true
            });
        }else if(typeof selected !== 'undefined' && selected !== 'Tous' && selected_aucun_image === 'Tous'){
            impute_grid.jqGrid("setGridParam", {
                postData: {
                    filters: JSON.stringify({
                        groupOp: "AND",
                        rules: [
                            {field: field, op: "eq", data: selected},
                        ]
                    })
                },
                search: true
            });
        }else{
            impute_grid.jqGrid("setGridParam", {
                search: false
            });
        }
    }
    return selected;
}
