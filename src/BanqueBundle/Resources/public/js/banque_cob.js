$(function() {
	$( "#mainside, .forme" ).resizable();
	//liste
	$('#btn_go').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var souscat = $('#souscat').val(),
            dossier = $('#dossier').val(),
            dscan = $('#dscan').val();
       
        if(dossier=='' || exercice == '' || dscan == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {
			$.ajax({
                url: Routing.generate('banque_liste_doublon'),
                type: 'POST',
                data: {
                    dossier: dossier,
                },
                success: function (data) {
					$('#retourListe').html(data.txt);
					$('#allimage').html(data.images);
					clickimage();
					$('#myModal').modal('show');
					document.getElementById("mySidenav").style.width = "140px";		
                }
            });
        }
        return false;
    });
	 // Changement client
    $(document).on('change', '#client', function (event) {
        event.preventDefault();
        event.stopPropagation();
        url = Routing.generate('reception_doublon_dossier');
		var idata = {};
			idata['client'] = $(this).val();
			$.ajax({
			url:url,
			type: "POST",
			dataType: "json",
			data: {
				"idata": JSON.stringify(idata)
			},
			async: true,
			success: function (data)
			{	
				$("#dossier option").remove();
				data.dossiers.forEach(function(d) {
					$("#dossier").append('<option value="'+d.id+'">'+d.nom+'</option>');
				});	
				$("#dossier").val('').trigger('chosen:updated');
			}
		});
    });
	// Changement dossier
    $(document).on('change', '#dossier', function (event) {
        event.preventDefault();
        event.stopPropagation();
		var did = $("#dossier").val();
		$("#dossierpanier").val(did);
	   	url = Routing.generate('banque_date_scan');
			$.ajax({
			url:url,
			type: "POST",
			dataType: "json",
			data: {
				"did": $("#dossier").val()
			},
			async: true,
			success: function (data)
			{	
				
				$("#dscan option").remove();
				$("#dscan").append('<option value="0">Tous</option>');
				$("#exercice option").remove();
				data.exercice.forEach(function(d) {
					$("#exercice").append('<option value="'+d.exercice+'">'+d.exercice+'</option>');
				});	
				data.dscan.forEach(function(d) {
					$("#dscan").append('<option value="'+d.bd+'">'+d.aff+'</option>');
				});	
			}
		});
    });
	function clickimage(){
		//afficher images
		$('.js_imgbq_selected').on('click', function () {
			var pdf = $(this).attr('data-id');
			PDFObject.embed(pdf, "#pdf");
			$('.js_imgbq_selected').each(function() {
					$(this).css("background-color", "transparent");
				});	
			$(this).closest('span').css("background-color", "#f8ac59");
		});	
	}
});
$('#imageclose').on('click', function (event) {
	event.preventDefault();
	event.stopPropagation();
	document.getElementById("mySidenav").style.width = "0px";	
});	
$('#imageside').on('click', function (event) {
	event.preventDefault();
	event.stopPropagation();
	if (document.getElementById("mySidenav").style.width == "140px"){
		document.getElementById("mySidenav").style.width = "0px";		
	} else {
		document.getElementById("mySidenav").style.width = "140px";		
	}
});	

function ajouterOption(id,nom){
	var ajout = true;
	$('.chosenimages > option').each(function() {
		if (id==$(this).val()){
			ajout = false;
		}
	});
	if (ajout){
		$(".chosenimages").append('<option value="'+id+'" selected>'+nom+'</option>');
		$('.chosenimages').trigger("chosen:updated");
	}
}
function mi(nbr){
	nbr=(nbr==null ? "" : nbr.toString())
	if (nbr.length==0){
		nbr='0';	
	}
	nbr = nbr.replace(/\s/g, '');
	nbr = nbr.replace(",", ".");
	nbr = parseFloat(nbr);
	return nbr.toFixed(2).replace(/(\d)(?=(\d{3})+\b)/g,'$1 ');	
}
