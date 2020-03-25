/**
 * Created by SITRAKA on 31/10/2018.
 */
var cl_t_item_edited = 'taches_item_edited';
$(document).ready(function(){
    $(document).on('click','.cl_taches_item_edit',function(){
        var action = parseInt($(this).attr('data-action')),
            taches = 0,
            taches_item = 0,
            regime_imposition = 0;

        if (action !== 1)
        {
            $('.'+cl_t_item_edited).removeClass(cl_t_item_edited);
            $(this).closest('.panel').addClass(cl_t_item_edited);
        }

        if (action === 0) taches = parseInt($(this).closest('.panel').attr('data-id'));
        else if (action === 1)
        {
            taches = parseInt($(this).attr('data-taches'));
            taches_item = parseInt($(this).attr('data-taches_item'));
            /*regime_imposition = parseInt($('#id_regime_imposition').val());
            if (regime_imposition === 0)
            {
                show_info('ERREUR','Régime d imposition vide','error');
                $('#id_regime_imposition').closest('.form-group').addClass('has-error');
                return;
            }
            else $('#id_regime_imposition').closest('.form-group').removeClass('has-error');*/

        }
        else if (action === 2)
        {
            taches = parseInt($(this).closest('.panel').attr('data-id'));
            taches_item = parseInt($(this).closest('.footable').attr('data-id'));
        }

        edit_taches_item(action, taches, taches_item);
    });

    $(document).on('click','.cl_taches_item_save',function(){
        var taches = parseInt($(this).attr('data-taches')),
            taches_item = parseInt($(this).attr('data-taches_item')),
            regime_imposition = parseInt($('#id_regime_imposition').val());

        if (regime_imposition === 0)
        {
            show_info('ERREUR','Régime d imposition vide','error');
            $('#id_regime_imposition').closest('.form-group').addClass('has-error');
            return;
        }
        else $('#id_regime_imposition').closest('.form-group').removeClass('has-error');

        edit_taches_item(1, taches, taches_item);
    });
});

function charger_taches_item(div)
{
    close_modal();
    var taches = div.attr('data-id');
    $.ajax({
        data: {
            taches: taches
        },
        url: Routing.generate('taches_items'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            div.find('.cl_taches_item_container').html(data);
            $('.footable').footable()
                .bind({
                'footable_row_collapsed' : function(e) {
                    hide_taches_date_edit();
                },
                'footable_row_expanded' : function(e) {
                    $(e.target).find('.footable-row-detail-inner').css('width','100%');
                    hide_taches_date_edit();
                    activer_qtip();
                }
            });
        }
    });
}

function edit_taches_item(action, taches, taches_item)
{
    taches_item = typeof taches_item !== 'undefined' ? taches_item : 0;
    $.ajax({
        data: {
            action: action,
            taches:taches,
            taches_item: taches_item,
            regime_imposition: parseInt($('#id_regime_imposition').val())
        },
        url: Routing.generate('taches_item_edit'),
        type: 'POST',
        contentType: "application/x-www-form-urlencoded;charset=utf-8",
        beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=utf-8');
        },
        dataType: 'html',
        success: function(data){
            if (action === 0) show_modal(data,'Ajout Sous Tache');
            else charger_taches_item($('.'+cl_t_item_edited));
        }
    });
}
