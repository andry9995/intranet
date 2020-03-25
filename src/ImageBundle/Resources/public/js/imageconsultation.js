/**
 *
 * @param id
 *
 * id: id image crypter
 */
function show_image_pop_up(id)
{
    $.ajax({
        data: {
            imageId:id,
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