/**
 * Created by SITRAKA on 13/09/2018.
 */
$(document).ready(function(){
    $(document).on('change','.cl_filtre',function(){
        calendar_container.fullCalendar('prev');
        calendar_container.fullCalendar('next');
    });
});