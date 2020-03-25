var timeout = 120000;

$(function () {

    refreshImage();
    // setTimeout(function() {
    //     refreshImage();
    // }, 1000);


    //Refresh page
    //    --- Timer ---
    var refresh_status = $('#refresh-status');
    refresh_status.hide();
    setInterval(function () {
        refreshImage();
    }, timeout);

    function refreshImage() {
        $('#btn_refresh_reception').hide();
        var refresh_status = $('#refresh-status');
        var url = Routing.generate('reception_dashboard', {json: 1});
        refresh_status.show();

        /** AJAX */
        fetch(url, {
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            var erreur = data.erreur;
            if (erreur === false) {
                var image_non_descendu = numeral(data.image_non_descendu).format('0,0'),
                    lot_non_descendu = numeral(data.lot_non_descendu).format('0,0'),
                    image_attente_traitement = numeral(data.image_attente_traitement).format('0,0'),
                    lot_attente_traitement = numeral(data.lot_attente_traitement).format('0,0'),
                    image_remonte_current = numeral(data.image_remonte_current).format('0,0'),
                    lot_remonte_current = numeral(data.lot_remonte_current).format('0,0'),
                    image_niv1 = numeral(data.image_niv1).format('0,0'),
                    lot_niv1 = numeral(data.lot_niv1).format('0,0'),
                    image_niv2 = numeral(data.image_niv2).format('0,0'),
                    lot_niv2 = numeral(data.lot_niv2).format('0,0'),
                    current_date = moment(data.current_date.date).format('DD MMMM HH:mm');
                $('#image-non-descendu').text(image_non_descendu);
                $('#lot-non-descendu').text(lot_non_descendu);
                $('#image-attente-traitement').text(image_attente_traitement);
                $('#lot-attente-traitement').text(lot_attente_traitement);
                $('#image-remonte-current').text(image_remonte_current);
                $('#lot-remonte-current').text(lot_remonte_current);
                $('#image-niv1').text(image_niv1);
                $('#lot-niv1').text(lot_niv1);
                $('#image-niv2').text(image_niv2);
                $('#lot-niv2').text(lot_niv2);
                $('#current-date-time').text(current_date);
            }
            refresh_status.hide();
            $('#btn_refresh_reception').show();
        }).catch(function (ex) {
            $('#btn_refresh_reception').show();
            refresh_status.hide();
            console.log("Error: ", ex);
        });
    }
    $(document).on('click', '#btn_refresh_reception', function(event) {
        refreshImage();
    });

});
