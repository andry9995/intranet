/*
 * Export excel
 */
$(document).ready(function() {

    $(document).on('click', '#btn_export_details', function(){

    	var tableau_grid = $('#js_tableau_images_recues'),
            datas = tableau_grid.jqGrid('getGridParam', 'data');

        if (tableau_grid[0].grid == undefined || datas.length == 0) {
            show_info("Echec de téléchargement", "Tableau vide", "error");
            return false;

        }

        else{
            $('#exp_datas').val(encodeURI(JSON.stringify(datas)));
            $('#exp_dossier').val($('#dossier').val());
            $('#exp_typedate').val($('#js_filtre_typedate').val());
            $('#exp_exercice').val($('#exercice').val());
            $('#exp_client').val($('#client').val());

            $('#form-export').attr('action',Routing.generate('details_export')).submit();
        }

	});

});