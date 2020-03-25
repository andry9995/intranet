$(function () {
    var changeMonthTimeout,
        clients = [],
        ichecks = $('.i-checks'),
        ajax = null,
        table_tache_grid = $('#table-tache-list'),
        calendar_modal_loader = $('#calendar-modal-loader'),
        modalEventChanged = false,
        loader_selector = $('#loader'),
        calendar_container = $('#calendar'),
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
            left: 'prev,today,next, refreshButton',
            right: 'agendaDay,agendaWeek,month',
            center: 'title'
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
        select: selectDay,
        eventResize: eventResize,
        eventDrop: eventDrop,
        eventRender: eventRender,
        eventClick: eventClick,
        dayClick: dayClick,
        dayRender: dayRender,
        viewRender: viewRender
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
            var url = Routing.generate('revision_agenda_2_tache_calendar', {
                clients: clients,
                periode: currentDate.format('YYYY-MM-DD')
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
                    }return;
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
            tache_synchro_id = parseInt(event.tacheSynchro),
            dossier = parseInt(event.dossier);

        if (isNaN(dossier) || dossier === 0)
        {
            revertFunc();
            return;
        }

        if (isNaN(tache_synchro_id)) tache_synchro_id = 0;
        if (google_id.substring(0,4) === 'NONE') google_id = 'NONE';

        $.ajax({
            data: {
                google_id: google_id,
                tache_synchro_id: tache_synchro_id,
                dossier: dossier,
                new_date: event.start.format()
            },
            url: Routing.generate('revision_agenda_2_moov_tache'),
            type: 'POST',
            contentType: "application/x-www-form-urlencoded;charset=utf-8",
            beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=utf-8');
            },
            dataType: 'html',
            success: function(data){
                //$('#test_calendar').html(data);
                if (data.trim() !== 'NONE')
                {
                    event.id = data;
                    calendar_container.fullCalendar('updateEvent', event);
                }
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
    }

    function eventClick(event, jsEvent, view) {
        modalEvent(event.start, event);
    }

    function dayClick(date, jsEvent, view) {
        // modalEvent(date);
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
            if (event.type === 'local') {
                table_tache_grid.jqGrid("clearGridData");
                table_tache_grid.jqGrid('GridUnload');
                $('#calendar-modal').modal('show');
                title = date.format("DD MMMM YYYY") + ' - ' + event.original_title;
                $('#calendar-modal-title').text(title);
                clients = getSelectedClients();
                var url = Routing.generate('revision_event_by_date', {
                    periode: date.format('YYYY-MM-DD'),
                    clients: clients,
                    nomtache: event.original_title,
                    jqgrid: 1
                });

                setTimeout(function () {
                    table_tache_grid.setGridParam({
                        url: url,
                        datatype: 'json'
                    });
                    table_tache_grid.trigger("reloadGrid", [{page: 1, current: true}]);
                }, 500);
            }
        }
        modalDraggable();
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

        fetch(url, {
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
        });
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
});