/**
 * Created by SITRAKA on 02/04/2019.
 */
$(document).ready(function(){
    $('#id_client_sel').find('.colorpicker-element').each(function(){
        var bg = $(this).attr('data-bg');
        $(this).css('background-color',bg);
        $(this).colorpicker({
            color: bg
        }).on('hidePicker', function(ev){
            var new_bg = ev.color.toHex(),
                client = $(this).closest('.agenda-select-item').find('.agenda-select-check').attr('data-id');
            $(this).css('background-color',new_bg);

            $.ajax({
                data: {
                    client: client,
                    new_bg: new_bg
                },
                url: Routing.generate('revision_agenda_3_change_client_color'),
                type: 'POST',
                dataType: 'html',
                success: function(data){
                    calendar_container.fullCalendar('prev');
                    calendar_container.fullCalendar('next');
                }
            });
        }).on('changeColor', function(ev){
            $(this).css('background-color',ev.color.toHex());
        });
    });
});