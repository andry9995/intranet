
$(document).ready(function() {

    loadDashBord();

    function loadDashBord() {
        $.ajax({
            url: Routing.generate('tenue_dashboard'),
            type: 'POST',
            data: {
                status : true
            },
            success: function (data) {
                $('.resultat-dashboard').css('display', 'block');

                $('.nb_lot_S1').html(data.nb_lot_S1);
                $('.nb_image_S1').html(data.nb_image_S1);
                $('.nb_lot_S2').html(data.nb_lot_S2);
                $('.nb_image_S2').html(data.nb_image_S2);
                $('.nb_lot_CTRL_OS').html(data.nb_lot_CTRL_OS);
                $('.nb_image_CTRL_OS').html(data.nb_image_CTRL_OS);
                $('.nb_image_IMP').html(data.nb_image_IMP);
                $('.nb_lot_IMP').html(data.nb_lot_IMP);
                $('.nb_lot_CTRL_IMP').html(data.nb_lot_CTRL_IMP);
                $('.nb_image_CTRL_IMP').html(data.nb_image_CTRL_IMP);
            }
        });

    }
	$(document).on('click', '#btn_refresh_dashboard_tenue', function() {
        loadDashBord();
	});
});