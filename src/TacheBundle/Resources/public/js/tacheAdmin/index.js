/**
 * Created by SITRAKA on 04/09/2018.
 */
var class_cell_edit = 'cl_edited_cell';

$(document).ready(function(){
    client_required = true;
    charger_entity_taches();

    $(document).on('change','#client',function(){
        charger_entity_taches();
    });

    $(document).on('change','#dossier',function(){
        charger_taches(parseInt($('#client').val()),parseInt($('#dossier').val()));
    });

    $(document).on('change','#id_heriter_client',function(){
        if ($(this).is(':checked')) $('.cl_container_tache_param').addClass('hidden');
        else $('.cl_container_tache_param').removeClass('hidden');
    });

    $(document).on('click','.cl_t_cell input[type="checkbox"]',function(){
        $('.'+class_cell_edit).removeClass(class_cell_edit);
        var type_tache = ($(this).closest('td').addClass(class_cell_edit).hasClass('legale')) ? 0 : 1,
            entity = $(this).closest('tr').attr('id'),
            aria_spliters = $(this).closest('td').attr('aria-describedby').split('_'),
            tache = aria_spliters[aria_spliters.length - 1],
            type_entity = parseInt($('#id_param_entity').val());

        revert_value_check_box();
        $.ajax({
            data: {
                entity: entity,
                type_tache: type_tache,
                tache: tache,
                type_entity: type_entity
            },
            url: Routing.generate('tache_admin_show_imputation_tache'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                show_modal(data,'Parametrage',undefined,(type_tache === 0) ? 'large' : 'default');
                if ($('#id_heriter_client').length > 0) $('#id_heriter_client').change();
                if (type_tache === 1)
                {
                    set_datepicker($('#id_tache_libre_demarrage'));
                    set_datepicker($('#id_tache_libre_date_calcul'));
                    lb_change_periode();
                }
            }
        });
    });
});

function set_datepicker(input)
{
    input.datepicker({
        dateFormat: 'dd/mm/yyyy',
        language: 'fr',
        daysOfWeekHighlighted: '0,6',
        todayHighlight: true,
        autoclose: true
    });
}

function charger_entity_taches()
{
    var type_entity = parseInt($('#id_param_entity').val()),
        client = $('#client').val().trim(),
        dossier = 0;

    if (type_entity === 0) charger_taches(client,dossier);
    else charger_site();
}

function charger_taches(client,dossier)
{
    var type = parseInt($('#id_param_entity').val());
    if (type === 1 && client === 0)
    {
        show_info('Erreur','Choisir un client','error');
        $('#client').closest('.form-group').addClass('has-error');
        return;
    }
    else $('#client').closest('.form-group').removeClass('has-error');

    $.ajax({
        data: {
            client: client,
            dossier: dossier,
            type: type
        },
        url: Routing.generate('tache_admin_taches'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            //$('#tache_test').html(data);return;
            set_grid($.parseJSON(data));
        }
    });
}

function set_grid(datas)
{
    //tache_legale_action_grid.jqGrid('GridUnload');
    $('#id_table_tache_container').html(
        '<table id="id_table_tache"></table>'+
        '<div id="id_pager_tache"></div>'
    );

    var table_tache = $('#id_table_tache');

    table_tache.jqGrid({
        data: datas.datas,
        datatype: "local",
        height: $(window).height() - 250,
        width: $('#id_table_tache_container').width(),
        //autowidth: true,
        shrinkToFit: true,
        rowNum: 100,
        rowList: [100, 200, 300],
        colNames: datas.colNames,
        colModel: datas.colModels,
        pager: "#id_pager_tache",
        viewrecords: true,
        caption: "Example jqGrid 1",
        hidegrid: false
    });

    //group_head_jqgrid('id_table_tache',datas.headGroups,false);
    $('#id_table_tache_container div.ui-jqgrid-titlebar').addClass('hidden');

    $('#id_table_tache').find('td.legale input[type="checkbox"]').each(function(){
        if (parseInt($(this).val()) === -1) $(this).remove();
        else $(this).addClass('pointer');
    });

    hide_column_empty();
}

function revert_value_check_box()
{
    var input = $('.'+class_cell_edit).find('input[type="checkbox"]'),
        new_status = !input.is(':checked');
    input.prop('checked', new_status);
}

function after_charged_dossier()
{
    charger_taches($('#client').val(),parseInt($('#dossier').val()),1);
}

function hide_column_empty()
{
    var iteration = 0,
        columns_legales_non_vide = [],
        columns_legales = [],
        table = $('#id_table_tache');
    table.find('td.legale').each(function(){
        iteration++;

        var aria_describedby_spliters = $(this).attr('aria-describedby').toString().split('_'),
            column_name = 't_lg_' + aria_describedby_spliters[aria_describedby_spliters.length - 1],
            is_empty = ($(this).html().trim() === '');

        if (!is_empty && !columns_legales_non_vide.in_array(column_name)) columns_legales_non_vide.push(column_name);
        if (!columns_legales.in_array(column_name)) columns_legales.push(column_name);
    });

    for (var i = 0; i < columns_legales.length; i++)
        if (!columns_legales_non_vide.in_array(columns_legales[i]))
        {
            //$('#id_table_tache').hideCol(columns_legales[i]);
            table.jqGrid('hideCol',[columns_legales[i]]);
        }

    table.jqGrid('setGridWidth', $('#id_table_tache_container').width());
}