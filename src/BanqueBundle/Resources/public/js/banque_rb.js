$(function() {
	$( "#mainside, .forme" ).resizable();
	//liste



	$(document).on('click','#btn_gox', function(){

        var url = Routing.generate('banque_controle_releve_grid'),
            idClient = $('#client').val(),
            idDossier = $('#dossier').val(),
            idBanque = $('#banque').val(),
			exercice = $('#exercice').val(),
			nucompte = $('#numCompte').val();


        // releveGrid.jqGrid('clearGridData');

        // releveGrid.jqGrid('setGridParam', {
        //     url: url,
        //     postData: {
        //         clientId: idClient,
        //         dossierId: idDossier,
        //         banqueId: idBanque,
        //         exercice: exercice
        //
        //     },
        //     footerrow: true
        // }).trigger('reloadGrid');


		$.ajax({
			url: url,
			type: 'POST',
			data: {
                clientId: idClient,
                dossierId: idDossier,
                banqueId: idBanque,
                exercice: exercice,
				numcompte: nucompte
			},
			dataType: 'json',
			success: function(data){
				console.log(data);
			}
		})

	});

	$('#btn_go').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var souscat = $('#souscat').val(),
            dossier = $('#dossier').val(),
            dscan = $('#dscan').val(),
			exercice = $('#exercice').val(),
			banquecompte = $('#banquecompte').val();
       
        if(dossier=='' || exercice == '' || dscan == '') {
            show_info('Champs non Remplis', 'Veuillez Verifiez les Champs', 'info');
            return false;
        } else {
			$(".chosenimages option").remove();
            $.ajax({
                url: Routing.generate('banque_liste_doublon'),
                type: 'GET',
                data: {
                    dossier: dossier,
					exercice: exercice,
					souscat: souscat,
					banquecompte: banquecompte

                },
                success: function (data) {
					$('#retourListe').html(data.txt);
					$('#retourListe tr').click(function (){ 
							var pdf = $(this).attr('data-id');
							PDFObject.embed(pdf, "#pdf");
							$('.js_imgbq_selected').each(function() {
									$(this).css("background-color", "transparent");
							});	
							$('#'+ $(this).attr('data-tid')).css("background-color", "#f8ac59");
					});	
					$('#allimage').html(data.images);
					var i=0;
					$.each(data.ass, function(key, value) {
						ajouterOption(key,value);
						i++;
					});	
					if (i>1){
						$('.chosenimages').chosen({width: "700px"});
						$('.lesimages').show();
						$('#btn_ass').show();
					} else {
						$('.lesimages').hide();
						$('#btn_ass').hide();
					} 
					$('#myModal').modal('show');
					document.getElementById("mySidenav").style.width = "140px";		
					  clickimage();
					  var x="";
						$('.forme tr').each(function() {
								if (this.cells[1].innerHTML!='Trou'){
									this.cells[1].innerHTML= x;
								}
								if (!isNaN(this.cells[0].innerHTML)){
									 x = this.cells[0].innerHTML;
								}
						});	
						$('#retourListe tr').each(function() {
							if (!isNaN(this.cells[1].innerHTML)){
								var trou =this.cells[0].innerHTML-this.cells[1].innerHTML-1;
								if (trou<0){
									trou=0;
								}
								if (trou==0){
									this.cells[1].innerHTML= ''; 
								} else {
									this.cells[1].innerHTML= "<span style='font-weight:bold;color:red;'>"+trou+"</span>";
								}
							}	
						});
						$('#retourListe tr').each(function() {									
							var ecart =this.cells[5].innerHTML-this.cells[7].innerHTML;
							if (ecart!=0){
								this.cells[7].innerHTML= "<span style='font-weight:bold;color:red;'>"+mi(ecart)+"</span>";
							} else {
								this.cells[7].innerHTML= ''; 	
							}
							if (this.cells[6].innerHTML!=0){
								this.cells[6].innerHTML=mi(this.cells[6].innerHTML);
							}
						});
						$('#retourListe tr').each(function() {									
							var solded =this.cells[4].innerHTML;
							var soldef =this.cells[5].innerHTML;
							if (solded==0){
								this.cells[4].innerHTML= "";
							} else {
								this.cells[4].innerHTML= mi(solded); 	
							}
							if (soldef==0){
								this.cells[5].innerHTML= "";
							} else {
								this.cells[5].innerHTML= mi(soldef); 	
							}	
						});
						var solded = [],soldef = [],soldev=[],soldevi=0;
						$('#retourListe tr').each(function() {		
							solded.push(this.cells[4].innerHTML);		
							soldef.push(this.cells[5].innerHTML);
						});
						
						$.each(solded, function(key, value) {
							if (key!=0){
								if (solded[key]==soldef[key-1] && solded[key]!=0){
									soldev[key]=1;
									if (key==1){
										soldevi =1;
									}
								} else {
									soldev[key]=0;
								}
							}
						});	
						var key =0;
						$('#retourListe tr').each(function() {
							if (key==0){
								if (soldevi==1){
									this.cells[5].innerHTML= "<span style='font-weight:bold;color:green;'>"+this.cells[5].innerHTML+"</span>";
								} else {
									this.cells[5].innerHTML= "<span style='font-weight:bold;color:red;'>"+this.cells[5].innerHTML+"</span>";
								}
							} else {
								if (soldev.length-1!=key){
									if (soldev[key+1]==1){
										this.cells[5].innerHTML= "<span style='font-weight:bold;color:green;'>"+this.cells[5].innerHTML+"</span>";
									} else {
										this.cells[5].innerHTML= "<span style='font-weight:bold;color:red;'>"+this.cells[5].innerHTML+"</span>";
									}
								}
							}
							key++;	
						});
						var key =0;
						$('#retourListe tr').each(function() {
							if (key!=0){
								if (soldev[key]==1){
									this.cells[4].innerHTML= "<span style='font-weight:bold;color:green;'>"+this.cells[4].innerHTML+"</span>";
								} else {
									this.cells[4].innerHTML= "<span style='font-weight:bold;color:red;'>"+this.cells[4].innerHTML+"</span>";
								}
							}	
							key++;	
						});

						  $('.set_doublon').on('click', function (event) {
							 event.preventDefault();
							  var eli = $(this);
							  if (eli.html()=='Normal'){
									var c=16,sc=3,ssc=5;ret='<span style="font-weight:bold;color:red;">Doublon</span>';
							  } else {
									var c=16,sc=10,ssc=11;ret='Normal';
							  }
								$.ajax({
									url: Routing.generate('revision_un_rev'),
									type: 'POST',
									data: {
										imagid:eli.attr('data-id'),
										c : c,
										sc : sc,
										ssc  : ssc
									},
									success: function (data) {
											eli.html(ret);
									}
								});
								return false;
							});	
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
    $(document).on('change', '#dossier', function () {
        var urlexercice = Routing.generate('banque_exercice'),
            urlbanque = Routing.generate('banque_liste_banque'),
            dossierid = $(this).val();

        $.ajax({
            url: urlexercice,
            data: {dossierid: dossierid},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#exercice').html(data);
            }
        });

        $.ajax({
            url: urlbanque,
            data: {dossierid: dossierid},
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#banque').html(data);
                changeBanque();
            }
        })
    });

    $(document).on('change', '#banque', function(){
        changeBanque();
    });

    // Changement dossier
    $(document).on('change', '#exercice', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = Routing.generate('banque_date_scan'),
            did = $('#dossier').val(),
            exercice = $('#exercice').val(),
            souscategorieid = $('#souscat').val();
        $.ajax({
            url:url,
            type: "GET",
            dataType: "html",
            data: {
                did: did,
                exercice: exercice,
                souscategorieid: souscategorieid
            },
            async: true,
            success: function (data)
            {
                $('#dscan').html(data);
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
	// var ajout = true;
	// $('.chosenimages > option').each(function() {
	// 	if (id==$(this).val()){
	// 		ajout = false;
	// 	}
	// });
	// if (ajout){
	// 	$(".chosenimages").append('<option value="'+id+'" selected>'+nom+'</option>');
	// 	$('.chosenimages').trigger("chosen:updated");
	// }
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


function changeBanque(){
    var url = Routing.generate('banque_liste_banque_compte'),
        banqueid = $('#banque').val(),
        dossierid = $('#dossier').val();
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        data: {
            banqueid: banqueid,
            dossierid: dossierid
        },
        success: function (data) {
            $('#banquecompte').html(data);
        }

    });
}