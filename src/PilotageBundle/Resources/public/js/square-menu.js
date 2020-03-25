/**
 * Menu pavé
 */
$(function () {

	$('#side-menu li a').each(function(n,v){
        var icon = $(this).children('i').attr('class') ;
        var libelle = $(this).children('span').html() ;
        var lien = $(this).attr('href');

        var menu = '<div class="col-sm-6 col-md-4">';
        menu += '<a href=" '+ lien +'" class="tile-link">';
        menu += '<div class="tile-menu">';
        menu += '<h2 class="tile-title">' + libelle + '</h2>';
        menu += '<div class="tile-icon">';
        menu += '<i class="'+ icon +'"></i>';
        menu += '</div>';
        menu += '</div>';
        menu += '</a>';
        menu += '</div>';

        $('#pilotage-square-menu').append(menu);

    });
    
})