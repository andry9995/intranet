/**
 * Created by SITRAKA on 20/11/2018.
 */
var class_cell_edit = 'cl_edited_cell',
    id_index = 0;

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
        change_taches_libre();
        set_hide_heritage();
    });

    $(document).on('change','#id_activer',function(){
        change_taches_libre();
        set_hide_heritage();
    });

    $(document).on('change','input[name="radio-responsable"]',function(){
        change_taches_libre();
    });

    $(document).on('click','.cl_t_cell input[type="checkbox"]',function(){
        id_index++;
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
                type_entity: type_entity,
                id_index:id_index
            },
            url: Routing.generate('taches_admin_show_imputation_tache'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                var num_modal = parseInt($('#modal-ui').attr('data-id'));
                for (var i = num_modal - 1; i >= 0; i--)
                    $('#modal-ui-'+i).remove();
                var options = { modal: false, resizable: true, title: 'Parametrage' };
                modal_ui(options,data, false, 0.8);

                if (type_tache === 0)
                {
                    $('.footable').footable()
                        .bind({
                            'footable_row_collapsed' : function(e) {
                                hide_taches_date_edit();
                            },
                            'footable_row_expanded' : function(e) {
                                $(e.target).find('.footable-row-detail-inner').css('width','95%');
                                hide_taches_date_edit();
                                activer_qtip();
                            }
                        });
                }
                else set_hide_heritage();
            }
        });
    });
});

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
        url: Routing.generate('taches_param_entity_liste'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            //$('#taches_entity_test').html(data);return;
            set_grid($.parseJSON(data));
        }
    });
}

function after_charged_dossier()
{
    charger_taches($('#client').val(),parseInt($('#dossier').val()),1);
}

function set_grid(datas)
{
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
        colNames: datas.colNames,
        colModel: datas.colModels,
        //pager: "#id_pager_tache",
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

function activer_qtip()
{
    $('.qtip_new').qtip({
        content: {
            text: function (event, api) {
                return $(this).removeClass('qtip_new').attr('title');
            }
        },
        position: {my: 'bottom center', at: 'top left'},
        style: {
            classes: 'qtip-dark qtip-shadow'
        }
    });
}

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

function set_hide_heritage()
{
    var heriter = false;
    if ($('#id_heriter_client').length > 0)
        heriter = $('#id_heriter_client').is(':checked');

    if (heriter)
    {
        $('#id_tache').addClass('hidden');
        $('#id_activer').closest('.checkbox').removeClass('hidden');
    }
    else
    {
        $('#id_tache').removeClass('hidden');
        $('#id_activer').closest('.checkbox').addClass('hidden');
    }
}

function change_taches_libre()
{
    var element_tache = $('#id_tache');
    $.ajax({
        data: {
            responsable: parseInt($('input[name="radio-responsable"]:checked').val()),
            heriter: parseInt($('#id_heriter_client').is(':checked') ? 1 : 0),
            tache: parseInt(element_tache.attr('data-id')),
            client: parseInt(element_tache.attr('data-client')),
            dossier: parseInt(element_tache.attr('data-dossier')),
            activer: $('#id_activer').is(':checked') ? 1 : 0
        },
        url: Routing.generate('taches_libre_heriter_change'),
        type: 'POST',
        contentType: 'application/x-www-form-urlencoded;charset=utf-8',
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            set_val_check(parseInt(data) === 1);
        }
    });
}

function set_val_check(val)
{
    var input = $('.'+class_cell_edit).find('input[type="checkbox"]');
    input.prop('checked', val);
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


