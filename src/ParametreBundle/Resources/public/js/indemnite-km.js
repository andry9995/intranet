$(document).ready(function(){
    $(document).on('change', '#exercice, #typevehicule', function(){
        var exercice = $('#exercice').val();
        var typeVehicule = $('#typevehicule').val();

        if(parseInt(exercice) !== -1 && parseInt(typeVehicule) !== -1){
            chargerContenuFraisKmTable(exercice, typeVehicule);
        }

    });
});

function chargerContenuFraisKmTable(annee, typeVehicule) {
    $.ajax({
        url: Routing.generate('parametre_ik_tableau'),
        type: 'POST',
        data: {annee: annee, typeVehicule:typeVehicule},
        dataType: 'html',
        success: function (data) {
            $('.table-frais-km').html(data);
        }
    })
}