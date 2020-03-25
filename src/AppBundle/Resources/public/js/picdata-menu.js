/**
 * Created by SITRAKA on 25/01/2019.
 */
$(document).ready(function(){
    $(document).on('click','.cl_picdata',function(){
        var l = $(this).attr('data-lien');
        $.ajax({
            data: { },
            url: Routing.generate('app_utilisateur_by_operateur'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                if (parseInt(data) === 0) show_info('Utilisateur','Pas de correspondance sur PICDATA','error');
                else
                {
                    var url = 'http://192.168.0.11/newpicdata/web/login_auto?l='+data+'&ln='+l;
                    //var url = 'https://lesexperts.biz/login_auto?l='+data+'&ln='+l;
                    var win = window.open(url, '_blank');
                    win.focus();
                }
            }
        });
    });
});