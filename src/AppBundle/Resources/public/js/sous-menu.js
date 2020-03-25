/**
 * Created by DINOH on 17/04/2019.
 */
var tab_menu = [];
var sous_menu = $('#side-menu').find('.cl_menu_interne');
$(sous_menu).each(function () {
    var title_sous_menu = $(this).attr('data-id').trim();
    tab_menu.push(title_sous_menu);
});
$(sous_menu).each(function () {
    $(this).parent().parent().find('.arrow').remove('span');
    $(this).parent().remove('ul');
});

var li_tab = $('.tabs-container .gestion-sous-menu-intranet').find('a');
if(li_tab.length > 0){
    $(li_tab).each(function () {
        var tab_title = $(this).text().trim();
        if(tab_menu.indexOf(tab_title) === -1){
            $(this).parent().removeClass('active').addClass('disabled');
            if(!$(this).parent().parent().children().first().hasClass('active')){
                $(this).parent().next().addClass('active');
                $(this).parent().parent().next().find('.active').removeClass('active').next().addClass('active');
            }
            $(this).parent().on("click", function(e) {
                if ($(this).hasClass("disabled")) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
}
