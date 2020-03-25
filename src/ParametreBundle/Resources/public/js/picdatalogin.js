/**
 * Created by MANASSE on 11/07/2018.
 */
$(document).ready(function(){
    charger();

    $(document).on('click','#id_table tr',function(){
        var operateur = $(this).attr('id');
        $.ajax({
            data: { operateur:operateur },
            url: Routing.generate('parametre_personnel_picdata_edit'),
            type: 'POST',
            dataType: 'html',
            success: function(data){
                show_modal(data,'Parametrage');
                var config = {
                    '.chosen-select'           : {},
                    '.chosen-select-deselect'  : {allow_single_deselect:true},
                    '.chosen-select-no-single' : {disable_search_threshold:10},
                    '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                    '.chosen-select-width'     : {width:"95%"}
                };
                for (var selector in config) {
                    $(selector).chosen(config[selector]);
                }
            }
        });
    });

    $(document).on('click','#id_save',function(){
        var operateur = parseInt($(this).attr('data-operateur')),
            utilisateur = parseInt($('#id_utilisateur_select').val());

        $.ajax({
            data: {
                operateur: operateur,
                utilisateur: utilisateur
            },
            url: Routing.generate('parametre_personnel_picdata_save'),
            dataType: 'html',
            type: 'POST',
            success: function(data){
                var newData = $.parseJSON(data);
                $('#id_table').jqGrid('setRowData', newData.id, newData);
                close_modal();
            }
        });
    });
});

function charger()
{
    $('#id_container').html('<table id="id_table"></table>');
    $.ajax({
        data: { },
        url: Routing.generate('parametre_personnel_picdata_charger'),
        dataType: 'html',
        success: function(data){
            $('#id_table').jqGrid({
                data: $.parseJSON(data),
                datatype: "local",
                height: $(window).height() - 200,
                autowidth: true,
                shrinkToFit: true,
                ignoreCase: true,
                rowNum: 10000,
                colNames: ['Nom', 'Login', '', 'Utilisateur Picdata'],
                colModel: [
                    {name: 'nom', index: 'nom', classes:'pointer', width: 60, sorttype: "string"},
                    {name: 'login', index: 'login', classes:'pointer', width: 90, sorttype: "string"},
                    {name: 'ou_id', index: 'ou_id', hidden: true, width: 90, sorttype: "string"},
                    {name: 'utilisateur', index: 'utilisateur', classes:'pointer', width: 90, sorttype: "string"}
                ],
                viewrecords: true,
                caption: 'Utilisateur (INTRANET-PICDATA)',
                hidegrid: false
            });
        }
    });
}


/*$(function () {

    $(document).ready(function(){
        $('#ul-operateur-pic-data').height($(window).height() - 250);
    });
    // Resize overflow

    var window_height = window.innerHeight;
    $('#show_img_height').height(window_height - 220);
    // $('#utilis-list').height(window_height - 220);
    window.onresize = function () {
        var window_height = window.innerHeight;
        $('#show_img_height').height(window_height - 220);
        // $('#utilis-list').height(window_height - 220);
    };

    // Parametrer un Operateur avec User Pic Data

    $( 'ul.list-group li' ).on( 'click', function() {
        $( this ).parent().find( 'li.op-active' ).removeClass( 'op-active' );
        $( this ).addClass( 'op-active' );
    });

    $(document).on('click', '#ul-operateur-pic-data li', function (event) {
        event.stopPropagation();
        event.preventDefault();

        var idOperat = $(this).attr("value");
        $.ajax({
            url: Routing.generate('verif_opera_utilis'),
            type: 'POST',
            data: {
                idopera: idOperat
            },
            success: function (data) {
                var status = data.etat, idUtilisateur;
                if ( status === 'exist' ) {
                     idUtilisateur = data.idUtil;
                     $('#ul-utilisateur-pic-data option[value="' + idUtilisateur + '"]').prop('selected', true);
                }else{
                     $('#ul-utilisateur-pic-data option[value=""]').prop('selected', true);
                }
            }
        });

        $('#value_operateur').val(idOperat);
        return false;
    });

    $(document).on('change', '#ul-utilisateur-pic-data', function (event) {
        event.stopPropagation();
        event.preventDefault();

        var idUtilis = this.value;
        $('#value_utilisateur').val(idUtilis);
        return false;
    });

    $(document).on('click', '#param_opera_utilis', function (event) {
        var idOpera = $('#value_operateur').val(),
            idUtilis = $('#value_utilisateur').val();

        $.ajax({
            url: Routing.generate('param_operat_picdata'),
            type: 'POST',
            data: {
                idopera: idOpera,
                idutilis: idUtilis
            },
            success: function (data) {
                var status = data.etat;
                    if ( status === 'new' ) {
                        show_info('Ajout Attachement', 'Ajout Effectuer', 'info');
                    }else{
                        show_info('Modif Attachement', 'Modification Effectuer', 'info');
                    }
            }
        });
    });
});*/