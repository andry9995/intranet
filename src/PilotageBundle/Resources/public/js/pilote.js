/**
 * Created by INFO on 23/07/2018.
 */

$(function () {

    var dateList = [],
        erreur_list = [],
        is_tache_legale;
    $('.chosen-select-client').chosen({
        no_results_text: "Aucun client trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-site').chosen({
        no_results_text: "Aucun site trouvé:",
        search_contains: true,
        width: '100%'
    });
    $('.chosen-select-dossier').chosen({
        no_results_text: "Aucun dossier trouvé:",
        search_contains: true,
        width: '100%'
    });

    var client_selector = $('#client'),
        site_selector = $('#site'),
        dossier_selector = $('#dossier'),
        loader_selector = $('#loader');

    client_selector.val('').trigger('chosen:updated');
    site_selector.val('').trigger('chosen:updated');
    dossier_selector.val('').trigger('chosen:updated');

    var grid_tache = $('#js_tache_liste');
    var window_height = window.innerHeight;
    var grid_width = grid_tache.closest('.row').width() - 50;

    // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        siteParClientImage(client_selector, site_selector, loader_selector, function() {
            dossierParSiteImage(client_selector, site_selector, dossier_selector, loader_selector)
        });
    });

    // Changement site
    $(document).on('change', '#site', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector);
    });


    // Show fourchette
    $("#js_filtre_periode").change(function() {

        if($(this).val() == "5") {
            $("#js_filtre_fourchette").show();
            $("#js_filtre_fourchette").css({display: "block"});
        }else {
             $("#js_filtre_fourchette").hide();
        }
        $('#periode').html($("#js_filtre_periode option[value='"+$(this).val()+"']").text());
    });

    //Date picker for fourchette
    // $('#js_debut_date').datepicker({format: "dd/mm/yyyy"});
    $('#js_debut_date').datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true});
    $('#js_fin_date').datepicker({format:'dd-mm-yyyy', language: 'fr', autoclose:true, todayHighlight: true});

    // Go for situation image
    $('#btn_situation_image').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        
        var select_periode = $("#js_filtre_periode").find('option:selected').html();
        var client = $('#client').val(),
            dossier = $('#dossier').val(),
            exercice = $('#exercice').val(),
            periode = $('#js_filtre_periode').val(),
            site = $('#site').val();

        if( client ==  '' || dossier == '' || exercice == '' || periode == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        }else {
           /* console.log('cli :' + client);
            console.log('dossier :' + dossier);
            console.log('exercice :' + exercice);
            console.log('periode :' + periode); */
           if( periode == "5" ) {
               var periodeDeb = $("#js_debut_date").val(),
                   periodeFin = $("#js_fin_date").val();
               if ( periodeDeb ==  '' || periodeFin == '') {
                   show_info('Champ Fourchette Invalide', 'Veuillez Remplir les Dates', 'info');
                   return false;
               }
               var perioDeb = periodeDeb.split("-"),
                   perioFin = periodeFin.split("-");
               var dateDeb = perioDeb[2] + '-' + perioDeb[1] + '-' + perioDeb[0],
                   dateFin = perioFin[2] + '-' + perioFin[1] + '-' + perioFin[0];
           }else {
               var dateDeb = null,
                   dateFin = null;
           }

            $.ajax({
                url: Routing.generate('pilotage_status_image'),
                type: 'POST',
                data: {
                    client : client,
                    site : site,
                    dossier: dossier,
                    exercice : exercice,
                    periode  : periode,
                    perioddeb: dateDeb,
                    periodfin: dateFin
                },
                success: function (data) {
                    
            
                  
                    $('#image_recu').html(data.recu);
                    $('#image_traite').html(data.separe);
                    $('#image_saisies').html(data.saisies);
                    $('#image_imputes').html(data.imputes);
                    $('#image_recu_trait').html(data.separe);
                    $('#image_separation_traite').html(data.separe);
                    $('#image_separation_traite').html(data.saisies);
                    $('#image_saisies_traites ').html(data.imputes);
                    $('#instance').html(data.instance);

                    //stock
                    $('#stocky').html(data.recu - data.separe);
                    $('#impute_stock').html(data.imputes - data.instance);
                    $('#saisie_stock').html(data.saisies - data.imputes );
                    $('#separation_stock').html(data.separe - data.saisies);
                    //moyen
                    $('#moyen_recu').html(data.moyen_recu);
                    $('#moyen_separe').html(data.moyen_separe);
                    $('#saisie_moyen').html(data.moyen_saisi);
                    $('#impute_traite').html(0);
                    
                    $('#moyen_general').html(data.moyen_general);
                   
                    $('#impute_moyen').html(data.instance/5);
                    
                   // $('#client_active').html(data.client_active); 
                    
                       
                    return false;
                  
                }
            });
        }
        return false;
    });
});
