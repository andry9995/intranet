var index = 0,
    calendar_container = $('#calendar');
$(function () {
    var changeMonthTimeout,
        clients = [],
        ichecks = $('.i-checks'),
        ajax = null,
        table_tache_grid = $('#table-tache-list'),
        calendar_modal_loader = $('#calendar-modal-loader'),
        modalEventChanged = false,
        loader_selector = $('#loader'),
        popover_event_data = [];

    ichecks.iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    });

    ichecks.iCheck('uncheck');

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

    $('#config-handle-icon').on('click', function () {
        var icon = $(this);
        $('#config-container').find('.theme-config-box').toggleClass('show');
        icon.find('i').toggleClass('fa-arrow-left fa-arrow-right');
    });

    var tacheSource = [];

    var calendar = calendar_container.fullCalendar({
        customButtons: {
            refreshButton: {
                text: 'Rafraîchir',
                click: function() {
                    showEvents(function () {
                        loader_selector.hide();
                    });
                }
            }
        },
        header: {
            left: 'prev,today,next, title',
            right: '', //agendaDay,agendaWeek,month,refreshButton
            center: ''
        },
        selectable: true,
        editable: true,
        handleWindowResize: true,
        windowResizeDelay: 500,
        eventLimit: 6,
        eventDurationEditable: false,
        //weekends: false,
        views: {
            agenda: {
                eventLimit: 6
            }
        },
        eventSources: [tacheSource],
        select: selectDay, //ok
        eventResize: eventResize, //ok
        eventDrop: eventDrop,
        eventRender: eventRender, //ok
        eventClick: eventClick,
        dayClick: dayClick,
        dayRender: dayRender, //ok
        viewRender: viewRender //ok
    });

    updateHeight();
    showEvents(function () {
        loader_selector.hide();
    });

    $('.agenda-select-check').on('ifToggled', makeDebounce(function() {
        showEvents(function () {
            loader_selector.hide();
        });
    }, 1000));

    function showEvents(callback) {
        if (ajax !== null) {
            ajax.abort();
            ajax = null;
        }

        modalEventChanged = false;

        clients = getSelectedClients();
        calendar_container.fullCalendar('removeEventSources');
        var currentDate = calendar_container.fullCalendar('getDate');

        if (clients.length > 0)
        {
            var legale = 1,
                libre = 1,
                faite = 1,
                scriptura = 1,
                ec = 0,
                cf = 0;

            if ($('#id_filtre_container').length > 0)
            {
                legale = $('#id_filtre_legale').is(':checked') ? 1 : 0;
                libre = $('#id_filtre_libre').is(':checked') ? 1 : 0;
                faite = $('#id_filtre_faite').is(':checked') ? 1 : 0;
            }
            if ($('#id_filtre_container_responsable').length > 0)
            {
                scriptura = $('#id_filtre_responsable_scri').is(':checked') ? 1 : 0;
                ec = $('#id_filtre_responsable_ec').is(':checked') ? 1 : 0;
                cf = $('#id_filtre_responsable_cf').is(':checked') ? 1 : 0;
            }

            var url = Routing.generate('revision_agenda_3_tache_calendar', {
                clients: clients,
                periode: currentDate.format('YYYY-MM-DD'),
                legale: legale,
                libre: libre,
                faite: faite,
                scriptura: scriptura,
                ec: ec,
                cf: cf
            });
            loader_selector.show();

            ajax = $.ajax({
                type: "GET",
                url: url,
                success: function(data) {
                    calendar_container.fullCalendar('addEventSource', data.taches);
                    calendar_container.fullCalendar('addEventSource', data.gcal);
                    loader_selector.hide();
                    if (typeof callback === 'function') {
                        callback();
                    }
                    return;
                    $('#test_calendar').html(data);
                },
                error: function(error) {
                    loader_selector.hide();
                    if (typeof callback === 'function') {
                        callback();
                    }
                    show_info("", "Une erreur est survenue", error);
                }
            });
        }
    }

    function updateHeight() {
        var window_height = window.innerHeight;
        calendar_container.fullCalendar('option', 'height', window_height - 100);

        var html_filtre = '' +
            '<div id="id_filtre_container">' +
                '<button id="btn-refresh-calendar" type="button" class="btn btn-sm btn-danger" style="margin-right: 175px;">'+
                    '<i class="fa fa-refresh"></i> Actualiser'+
                '</button>' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_legale" checked>' +
                    '<label for="id_filtre_legale">Tache légale</label>' +
                '</div>' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_libre" checked>' +
                    '<label for="id_filtre_libre">Tache libre</label>' +
                '</div>' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_faite" checked>' +
                    '<label for="id_filtre_faite">Tache Faite</label>' +
                '</div>' +
            '</div>',

            html_responsable = '' +
            '<div id="id_filtre_container_responsable">' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_responsable_scri" checked>' +
                    '<label for="id_filtre_responsable_scri">Scriptura</label>' +
                '</div>' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_responsable_ec">' +
                    '<label for="id_filtre_responsable_ec">EC</label>' +
                '</div>' +
                '<div class="checkbox checkbox-inline">' +
                    '<input type="checkbox" class="cl_filtre" id="id_filtre_responsable_cf">' +
                    '<label for="id_filtre_responsable_cf">CF</label>' +
                '</div>' +
            '</div>';

            $('.fc-right').html(html_filtre);
            $('.fc-center').html(html_responsable);
    }

    function selectDay(start, end) {
        // console.log(start.format('DD/MM/YYYY'), end.subtract(1, 'days').format('DD/MM/YYYY'));
    }

    function eventResize(event, delta, revertFunc, jsEvent, ui, view) {
        return false;
    }

    function eventDrop(event, delta, revertFunc, jsEvent, ui, view)
    {
        var google_id = event.id,
            taches_synchro_id = parseInt(event.tachesSynchro),
            dossier = parseInt(event.dossier),
            reste_id = '';
        if (isNaN(dossier) || dossier === 0)
        {
            revertFunc();
            return;
        }

        if (isNaN(taches_synchro_id)) taches_synchro_id = 0;
        if (google_id.substring(0,4) === 'NONE')
        {
            reste_id = google_id.replace(/NONE/,'');
            google_id = 'NONE';
        }

        $.ajax({
            data: {
                google_id: google_id,
                taches_synchro_id: taches_synchro_id,
                dossier: dossier,
                new_date: event.start.format(),
                reste_id: reste_id
            },
            url: Routing.generate('revision_agenda_3_moov_tache'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                //$('#test_calendar').html(data);
                event.id = data;
                calendar_container.fullCalendar('updateEvent', event);
            }
        });
    }

    function eventRender(event, element) {
        element.addClass('has-context-menu-event')
            .removeAttr('href');
        if (event.type === "legal") {
            element.attr('data-type', 'legal')
                .attr('data-title', event.original_title);
        }

        var titles = element.find('.fc-title'),
            titleSplit = titles.text();

        titleSplit = titleSplit.split('#S');
        titles.html(titles.text()); 
        var depasser = event.depasser,
            icon_color = event.icon_color;

        if(depasser === 2){
            element.find('.fc-title').prepend('<img src="' + assetsBaseDir + 'circle-maintso.png'+ '" style = "height: 13px; float:right;">');
        }else if(depasser === 1){
            element.find('.fc-title').prepend('<img src="' + assetsBaseDir + 'circle-mena.png'+ '" style = "height: 13px; float:right;">');
        }

        if(titleSplit.length === 1){
            if(event.dossier === 0){
                element.find('.fc-title').prepend('<img src="' + assetsBaseDir + 'tree-bars.png'+ '" style = "height: 13px; float:right">');
            }
        }

        element.find('.fc-title').prepend('<i class="fa fa-hand-o-right" aria-hidden="true" style=";color: '+icon_color+'"></i>&nbsp;');
    }

    function eventClick(event, jsEvent, view) {
        modalEvent(event.start, event);
    }

    function dayClick(date, jsEvent, view) {
        var d = date.format('YYYY-MM-DD'),
            events = [];
        $('#calendar').fullCalendar('clientEvents', function(event) {
            var start = moment(event.start).format("YYYY-MM-DD");
            if (parseInt(event.dossier) !== 0 && d === start)
            {
                var google_id = event.id,
                    taches_syncho_id = event.tachesSynchro,
                    dossier = event.dossier;
                if (typeof taches_syncho_id === 'undefined') taches_syncho_id = 0;

                events.push({
                    google_id: google_id,
                    taches_syncho_id: taches_syncho_id,
                    dossier: dossier,
                    date: date.format('YYYY-MM-DD'),
                    title: event.title
                });
            }
        });

        var clients = getSelectedClients();
        if (clients.length > 0)
        {
            $.ajax({
                data: {
                    events: JSON.stringify(events),
                    clients: JSON.stringify(clients),
                    date: date.format('YYYY-MM-DD')
                },
                url: Routing.generate('revision_agenda_3_events_day'),
                type: 'POST',
                contentType: "application/x-www-form-urlencoded;charset=utf-8",
                beforeSend: function(jqXHR) {
                    jqXHR.overrideMimeType('text/html;charset=utf-8');
                },
                dataType: 'html',
                success: function(data){
                    show_modal(data,'Details Taches');
                    charger_dossier_for_tache();
                }
            });
        }
    }

    function dayRender(date, cell) {
        if (cell[0]) {
            $(cell[0]).addClass('has-context-menu-cell');
        }
    }

    function modalEvent(date, event) {
        var is_new = true;
        if (event) {
            is_new = false;
        }
        var title = "";
        if (is_new) {
            title = date.format("DD MMMM YYYY");
            $('#calendar-modal-title').text(title);
            $('#calendar-modal').modal('show');
        } else {
            if (parseInt(event.dossier) !== 0) {
                var google_id = event.id,
                    taches_syncho_id = event.tachesSynchro,
                    dossier = event.dossier;
                if (typeof taches_syncho_id === 'undefined') taches_syncho_id = 0;
                $.ajax({
                    data: {
                        google_id: google_id,
                        taches_syncho_id: taches_syncho_id,
                        dossier: dossier,
                        date: date.format('YYYY-MM-DD'),
                        title: event.title
                    },
                    url: Routing.generate('revision_agenda_3_event_by_date'),
                    type: 'POST',
                    contentType: "application/x-www-form-urlencoded;charset=utf-8",
                    beforeSend: function(jqXHR) {
                        jqXHR.overrideMimeType('text/html;charset=utf-8');
                    },
                    dataType: 'html',
                    success: function(data){
                        var title = event.title;
                        title = title.replace('*', ' ');
                        title = title.replace('*', ' ');
                        show_modal(data, title);
                        $('.inmodal .modal-body').css('padding', '0px 15px 30px 15px');
                        $('.inmodal .modal-header').css('padding', '5px 15px');
                        $('.inmodal .modal-header .close').css('margin-top', '8px');
                    }
                });
            }else{
                var clients = getSelectedClients();
                $.ajax({
                    data: {
                        title: event.title,
                        clients: JSON.stringify(clients),
                        date: date.format('YYYY-MM-DD'),
                        google_id : event.id
                    },
                    url: Routing.generate('revision_agenda_3_events_tache_libre'),
                    type: 'POST',
                    contentType: "application/x-www-form-urlencoded;charset=utf-8",
                    beforeSend: function(jqXHR) {
                        jqXHR.overrideMimeType('text/html;charset=utf-8');
                    },
                    dataType: 'html',
                    success: function(data){
                        show_modal(data,'Configuration Tache');
                        charger_dossier_for_tache();
                    }
                });
            }
        }
        //modalDraggable();
    }

    $(document).on('shown.bs.modal', '#calendar-modal', function() {
        calendar_modal_loader.show();
        table_tache_grid = $('#table-tache-list');
        table_tache_grid.jqGrid({
            datatype: 'local',
            loadonce: true,
            sortable: true,
            width: 840,
            height: 400,
            shrinkToFit: true,
            viewrecords: true,
            rowNum: 5000,
            rowList: [5000, 10000, 20000],
            pager: '#pager_tache_list',
            hidegrid: false,
            colNames: [ 'Param', 'Clients', 'Dossiers', 'Tâches', 'Nb img.', 'Responsable', 'Fait', 'Report' ],
            colModel: [
                {name: 'cal-param', index: 'cal-param', align: 'left', hidden: true, classes: 'js-cal-param'},
                {name: 'cal-client', index: 'cal-client', align: 'left', editable: false, sortable: true, width: 200, classes: 'js-cal-client'},
                {name: 'cal-dossier', index: 'cal-dossier', align: 'left', editable: false, sortable: true, width: 200, classes: 'js-cal-dossier'},
                {name: 'cal-tache', index: 'cal-tache', align: 'left', editable: false, sortable: true, width: 200, classes: 'js-cal-tache'},
                {name: 'cal-img', index: 'cal-img', align: 'center', editable: false, sortable: true, width: 80, fixed: true, classes: 'js-cal-img'},
                {name: 'cal-responsable', index: 'cal-responsable', align: 'center', editable: false, sortable: true, width: 100, fixed: true, classes: 'js-cal-responsable'},
                {name: 'cal-fait', index: 'cal-fait', align: 'center', editable: false, sortable: true, width: 60, fixed: true, classes: 'js-cal-fait'},
                {name: 'cal-report', index: 'cal-report', align: 'center', editable: false, sortable: true, width: 60, fixed: true, classes: 'js-cal-report pointer'}
            ],
            grouping: true,
            groupingView: {
                groupField: ['cal-client'],
                groupOrder: ['asc'],
                hideFirstGroupCol: true,
                groupCollapse: true
            },
            loadComplete: function() {
                calendar_modal_loader.hide();
                var datepicker = table_tache_grid.find('.event-report').datepicker({
                    autoclose: true,
                    format: 'yyyy-mm-dd'
                });

                datepicker.on('changeDate', function(newDate) {
                    calendar_modal_loader.show();
                    var row = $(newDate.currentTarget).closest('tr'),
                        params = JSON.parse(row.find('.js-cal-param').text().toString()),
                        check = row.find('[data-fait]'),
                        status = check.attr('data-fait');
                    params.new_report_date = newDate.format();
                    var url = Routing.generate('revision_event_update'),
                        formData = new FormData();
                    formData.append('params', JSON.stringify(params));
                    formData.append('status', status);

                    modalEventChanged = true;

                    fetch(url, {
                        method: 'POST',
                        credentials: 'include',
                        body: formData
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        calendar_modal_loader.hide();
                        show_info('', 'Tâche reportée au ' + newDate.format('dd/mm/yyyy'), 'success');
                    }).catch(function(error) {
                        calendar_modal_loader.hide();
                        console.log(error);
                    });
                });

            }
        });
    });

    /** Modif Statut Fait/Non Fait Tâches */
    $(document).on('click', '.js-cal-fait', function() {
        calendar_modal_loader.show();
        var check = $(this).find('[data-fait]'),
            status = check.attr('data-fait'),
            params = $(this).closest('tr').find('.js-cal-param').text().toString();

        modalEventChanged = true;

        var new_status;
        if (status === '0') {
            new_status = '1';
        } else {
            new_status = '0';
        }
        var url = Routing.generate('revision_event_update'),
            formData = new FormData();
        formData.append('params', params);
        formData.append('status', new_status);

        alert('cal fait');
        /*fetch(url, {
            method: 'POST',
            credentials: 'include',
            body: formData
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            calendar_modal_loader.hide();
            check.attr('data-fait', new_status);
            if (new_status === '0') {
                check.removeClass('fa-check-square-o').addClass('fa-square-o');
            } else {
                check.removeClass('fa-square-o').addClass('fa-check-square-o');
            }
        }).catch(function(error) {
            calendar_modal_loader.hide();
            console.log(error);
        });*/
    });


    $('#calendar-modal').on('hidden.bs.modal', function() {
        if (modalEventChanged) {
            showEvents(function () {
                loader_selector.hide();
            });
        }
    });

    $(document).on('click', '.popover-event-item', function() {
        var event = popover_event_data[$(this).attr('data-index')];
        if (event !== 'undefined') {
            modalEvent(event.start, event);
        }
    });

    function eventLimitClick(cellInfo, jsEvent) {
        popover_event_data = [];
        var events = calendar_container.fullCalendar('clientEvents', function (event) {
            return cellInfo.date.isSame(event.start, 'day');
        });
        var target = $(jsEvent.currentTarget).closest('td');
        console.log(events, jsEvent, target);
        target.popover({
            title: cellInfo.date.format('D MMMM YYYY'),
            content: function() {
                var html = '<ul class="popover-event-container">';
                events.forEach(function(item, index) {
                    popover_event_data[index] = item;
                    if (item.type === 'local') {
                        html += '<li class="popover-event-item local pointer noselect" data-index="' + index + '">' + item.title + '</li>';
                    } else {
                        html += '<li class="popover-event-item pointer noselect" style="color:' + item.textColor + ';background-color:' + item.color + ';" data-index="' + index + '">' + item.title + '</li>';
                    }
                });
                html += '</ul>';
                return html;
            },
            container: 'body',
            html: true,
            placement: 'auto right'
        })
    }

    function viewRender(view, element) {
        if (changeMonthTimeout) {
            clearTimeout(changeMonthTimeout);
            changeMonthTimeout = setTimeout(function () {
                showEvents(function () {
                    loader_selector.hide();
                });
            }, 2000);
        } else {
            changeMonthTimeout = setTimeout(function () {
                console.log('not exist');
                showEvents(function () {
                    loader_selector.hide();
                });
            }, 1000);
        }
    }

    /**
     * Get List selected clients
     * @returns {Array}
     */
    function getSelectedClients() {
        var clients = [];
        $('.agenda-select-check').each(function(index, item) {
            if ($(item).prop('checked') === true) {
                clients.push($(item).attr('data-id'));
            }
        });
        return clients;
    }

    $(document).ready(function(){
        $(document).on('change','#id_client',function(){
            charger_dossier_for_tache();
        });

        $(document).on('click','#id_marquer_fait',function(){
            if($('#js_date_fait').val() === ''){
                show_info('Erreur','Marquer la date','error');
                return;
            }
            var events = [];
            events.push({
                dossier: $(this).attr('data-dossier'),
                tachesSynchro: $(this).attr('data-taches_synchro'),
                google_id: $(this).attr('data-google_id'),
                nom_tache: $(this).attr('data-tache'),
                status: 1,
                dateFait: $('#js_date_fait').val()
            });
            marque_fait(events);
        });

        $(document).on('click','#id_set_all_fait',function(){
            $('.chk_fait').each(function(){
                $(this).prop('checked', true);
            });
        });

        $(document).on('click','#id_add_tache',function(){
            var option_dossier = $('#id_dossier').find('option:selected'),
                dossier = parseInt(option_dossier.val()),
                option_tache = $('#id_tache').find('option:selected'),
                tache = parseInt(option_tache.val()),
                nom_tache = option_tache.attr('data-nom'),
                nom_description = option_tache.attr('data-description'),
                nom_dossier = option_dossier.text(),
                nom_client = $('#id_client').find('option:selected').text();

            if (dossier === 0 || tache === 0)
            {
                show_info('Erreur','Choisir un dossier ET une tache','error');
                return;
            }

            var html = '' +
                '<li class="li_tache info-element" data-tache="'+nom_tache+'" data-dossier="'+dossier+'" data-google_id="-1" data-taches_synchro="-1">' +
                '<strong>'+
                nom_tache +
                '</strong>'+
                '&nbsp;('+nom_description+')'+
                '<div class="agile-detail">'+
                '<div class="checkbox checkbox-success checkbox-inline pull-right">'+
                '<input type="checkbox" class="chk_fait" id="fait-n-'+index+'">'+
                '<label for="fait-n-'+index+'">Faite</label>'+
                '</div>'+
                '<span>'+nom_client+'&nbsp;/&nbsp;'+nom_dossier+'</span>'+
                '</div>'+
                '</li>';

            $('#id_tache_liste').prepend(html);
        });

        $(document).on('click','#id_save_taches',function(){
            var events = [];
            $('.chk_fait').each(function(){
                var li = $(this).closest('li');
                events.push({
                    dossier: li.attr('data-dossier'),
                    tachesSynchro: li.attr('data-taches_synchro'),
                    google_id: li.attr('data-google_id'),
                    nom_tache: li.attr('data-tache'),
                    status: $(this).is(':checked') ? 1 : 0,
                    date: $('#id_tache_liste').attr('data-date'),
                    dateFait: null
                });
            });
            marque_fait(events);
        });
    });

    function charger_dossier_for_tache()
    {
        var client = parseInt($('#id_client').val());
        if (client === 0)
        {
            $('#id_dossier').html('<option value="0"></option>');
        }
        else
        {
            $.ajax({
                data: {
                    client: client
                },
                url: Routing.generate('revision_agenda_3_dossier'),
                type: 'POST',
                contentType: "application/x-www-form-urlencoded;charset=utf-8",
                beforeSend: function(jqXHR) {
                    jqXHR.overrideMimeType('text/html;charset=utf-8');
                },
                dataType: 'html',
                success: function(data){
                    $('#id_dossier').html(data);
                }
            });
        }
    }

    function marque_fait(events)
    {
        $.ajax({
            data: {
                events: JSON.stringify(events)
            },
            url: Routing.generate('revision_agenda_3_marquer_fait'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                close_modal();
                calendar_container.fullCalendar('prev');
                calendar_container.fullCalendar('next');
                show_info('Succes','Merci de patienter pendant la mise à jour du calendrier');
            }
        });
    }

    $(document).on('click','#id_save_congig_taches',function(){
        var events = [];
        var _this = $(this);
        $('.chk_fait').each(function(){
            var li = $(this).closest('li');
            events.push({
                dossier: li.attr('data-dossier'),
                tachesSynchro: li.attr('data-taches_synchro'),
                google_id: _this.attr('data-google-id'),
                nom_tache: li.attr('data-tache'),
                status: $(this).is(':checked') ? 1 : 0,
                date: $('#id_tache_liste').attr('data-date'),
                dateFait: null
            });
        });
        marque_fait(events);
    });

    $(document).on('click', '#btn-refresh-calendar', function() {
        var clientsSelected = getSelectedClients();
        var currentDate = $('#calendar').fullCalendar('getDate');

        if (clientsSelected.length > 0)
        {
            var legale = 1,
                libre = 1,
                faite = 1,
                scriptura = 1,
                ec = 0,
                cf = 0;

            if ($('#id_filtre_container').length > 0)
            {
                legale = $('#id_filtre_legale').is(':checked') ? 1 : 0;
                libre = $('#id_filtre_libre').is(':checked') ? 1 : 0;
                faite = $('#id_filtre_faite').is(':checked') ? 1 : 0;
            }
            if ($('#id_filtre_container_responsable').length > 0)
            {
                scriptura = $('#id_filtre_responsable_scri').is(':checked') ? 1 : 0;
                ec = $('#id_filtre_responsable_ec').is(':checked') ? 1 : 0;
                cf = $('#id_filtre_responsable_cf').is(':checked') ? 1 : 0;
            }
            var url = Routing.generate('revision_agenda_3_maj', {
                clients: clientsSelected,
                periode: currentDate.format('YYYY-MM-DD'),
                legale: legale,
                libre: libre,
                faite: faite,
                scriptura: scriptura,
                ec: ec,
                cf: cf
            });
            loader_selector.show();
            $.ajax({
                type: "GET",
                url: url,
                success: function(data){
                    show_info('Mise à jour Tache avec succès');
                    calendar_container.fullCalendar('addEventSource', data.taches);
                    calendar_container.fullCalendar('addEventSource', data.gcal);
                    loader_selector.hide();
                    if (typeof callback === 'function') {
                        callback();
                    }
                    return;
                    $('#test_calendar').html(data);
                },
                error: function(error) {
                    loader_selector.hide();
                    if (typeof callback === 'function') {
                        callback();
                    }
                    show_info("", "Une erreur est survenue", error);
                }
            });
        }
    });
});