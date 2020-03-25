/**
 * Created by SITRAKA on 03/10/2018.
 */
var chargement = true;
$(document).ajaxStart(function(){
    show_loading(true);
});
$(document).ajaxStop(function(){
    show_loading(false);
});
$( document ).ajaxError(function(){
    show_loading(false);
});

function show_loading(actif)
{
    if(chargement)
    {
        actif = typeof actif !== 'undefined' ? actif : true;
        if (actif) $('body').loadingModal({text: 'Chargement...'});
        else $('body').loadingModal('destroy');
    }
}
