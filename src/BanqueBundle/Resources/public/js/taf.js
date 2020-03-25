$(document).ready(function() {

	// intance_taf_grid();
	/* $('#btn_situation_image').on('click', function (event) {
        go_taf();
     });
*/


    // $(window).resize(function() {
    //     resize_tab_taf();
    // });

});
function intance_taf_grid() {

    var colNames= ['','A saisir','A controler','Trou','Doublon'];

    var colModel= [{
        name    : 'releve',
        index   : 'releve',
        align   : 'left',
        editable: false,
        sortable: true,
        width   : 125,
        classes : 'js-releve'
    }, {
        name    : 'a-saisir',
        index   : 'a-saisir',
        align   : 'center',
        editable: false,
        sortable: true,
        width   : 125,
        classes : 'js-a-saisir'
    }, {
        name    : 'a-controler',
        index   : 'a-controler',
        align   : 'center',
        editable: false,
        sortable: true,
        width   : 125,
        classes : 'js-a-controler'
    }, {
        name    : 'trou',
        index   : 'trou',
        align   : 'center',
        editable: false,
        sortable: true,
        width   : 125,
        classes : 'js-trou'
    }, {
        name    : 'doublon',
        index   : 'doublon',
        align   : 'center',
        editable: false,
        sortable: true,
        width   : 125,
        classes : 'js-doublon'
    }];

    var options = {
        datatype: 'local',
        height: 200,
        autowidth: true,
        loadonce: true,
        shrinkToFit: true,
        rownumbers: false,
        altRows: false,
        colNames: colNames,
        colModel: colModel,
        viewrecords: true,
        hidegrid: true,
        caption: '',
        sortable: true

    };

    var tableau_grid = $('#js_taf');

    if (tableau_grid[0].grid == undefined) {

        tableau_grid.jqGrid(options);

    } else {
        delete tableau_grid;
        $('#js_taf').GridUnload('#js_taf');
        tableau_grid = $('#js_taf');
        tableau_grid.jqGrid(options);
    }

    return tableau_grid;

}

function go_taf(){

    // resize_tab_taf();

    // intance_taf_grid();

    var client  = $('#client').val(),
        dossier = $('#dossier').val(),
        exercice= $('#exercice').val(),
        groupe = $('#filtre_groupe').val(),
        periode = $('#js_filtre_periode').val(),
        site    = $('#site').val();

    if(client == null || client == ''){
        show_info('Erreur','Choisir un client','error');
        $('#client').closest('.form-group').addClass('has-error');
        return false;
    }

    if( client ==  '' || dossier == '' || exercice == '' || periode == '') {
        show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
        return false;
    }else {

        if( periode == "5" ) {
            var periodeDeb = $("#js_debut_date").val(),
                periodeFin = $("#js_fin_date").val();
            if ( periodeDeb ==  '' || periodeFin == '') {
                show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
                return false;
            }
            var perioDeb= periodeDeb.split("-"),
                perioFin= periodeFin.split("-");
            var dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0],
                dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
        }else {
            var dateDeb = null,
                dateFin = null;
        }

        var url = Routing.generate('banque_taf');

        var data = {
            client   : client,
            dossier  : dossier,
            exercice : exercice,
            periode  : periode,
            groupe  : groupe,
            perioddeb: dateDeb,
            periodfin: dateFin
        };

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            datatype: 'json',
            success: function(data){

                $('#js_taf').find('tbody').html('');

                var tr = '';
                $.each(data, function(k,v){
                    var td = '';
                    $.each(v, function (key, val) {
                        $.each(val, function (ke, va) {
                            if (ke === 0) {
                                td += '<td class="pad-m"><strong>' + va + '</strong></td>';
                            }
                            else {
                                td += '<td class="text-center">' + va + '</td>';
                            }
                        });
                    });
                    tr +='<tr>'+td+'</tr>';
                });

                $('#js_taf').find('tbody').html(tr);

            }
        });
    }

}

// function resize_tab_taf() {
//     setTimeout(function() {
//         var tableau_grid = $('#js_taf');
//
//         var width = tableau_grid.closest("#taf_container").width();
//
//         tableau_grid.jqGrid("setGridWidth", width);
//
//     }, 600);
// }