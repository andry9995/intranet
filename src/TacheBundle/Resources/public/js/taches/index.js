/**
 * Created by SITRAKA on 31/10/2018.
 */
$(document).ready(function(){
    set_height_taches();
    activer_qtip();
});

function set_height_taches()
{
    $('.cl_taches_container').height($(window).height() - 350);
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