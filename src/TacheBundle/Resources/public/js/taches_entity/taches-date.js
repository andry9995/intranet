/**
 * Created by SITRAKA on 23/11/2018.
 */
$(document).ready(function(){
    $(document).on('click','.cl_tache_resp',function(){
        if (!$(this).hasClass('btn-primary') && $(this).closest('tr').find('.cl_chk_tache_action').is(':checked'))
        {
            $(this).closest('.btn-group').find('.cl_tache_resp').removeClass('btn-primary').addClass('btn-white');
            $(this).removeClass('btn-white').addClass('btn-primary');
        }
    });

    $(document).on('change','.cl_chk_tache_action',function(){
        if (!$(this).is(':checked'))
        {
            var tr = $(this).closest('tr');
            tr.find('.cl_jour_additif').val('0');
            tr.find('.cl_tache_resp').removeClass('btn-primary').addClass('btn-white');
        }
    });
});