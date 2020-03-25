/************************
 *     EVENEMENTS
************************/
$(document).on('click','.js_menu_left',function(){
    $('.active-menu').each(function(){
        $(this).parent().removeClass('active-menu');
        $(this).removeClass('active-menu');
    });

    $(this).parent().addClass('active-menu');
    $(this).addClass('active-menu')
});

$(document).on('click', '.js_close_modal', function () {
    close_modal();
});

//fonction activer checkbox inspinia
function activer_checkbox()
{
    /*$('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_square-green',
    }).on('ifClicked', function(ev){
        $(this).parent().click();
    });*/

    $('input:radio').iCheck({
        radioClass: 'iradio_square-green',
    });
}

//fonction activer combow bootstrap
function activer_combow(selecteur,style)
{
    style = typeof style !== 'undefined' ? style : '';
    $(selecteur).selectpicker({
        style: style,
        size: 8,
    });
}

//function set table responsive
function set_tables_responsive()
{
    var width = $('.jqGrid_wrapper').width();
    $('table.jqGridTable').setGridWidth(width);
}

//valider mail
function is_email(email)
{
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

//show info
function show_info(titre,message,type, timeout)
{
    type = typeof type === 'undefined' ? 'success' : type;
    timeout = typeof timeout === 'undefined' ? 5000 : timeout;
    setTimeout(function(){
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: timeout
        };
        if(type === 'success') toastr.success(message, titre);
        if(type === 'warning') toastr.warning(message, titre);
        if(type === 'error') toastr.error(message, titre);
        if(type === 'info') toastr.info(message, titre);
    }, 500);
}

//fonction pop up
function show_modal(contenu,titre,animated,size)
{
    //size
    size = typeof size !== 'undefined' ? 'modal-' + size : 'modal-default';
    $('#modal-size').removeClass('modal-large').removeClass('modal-default').removeClass('modal-small').addClass(size);

    //animation
    $('#modal-animated').addClass('modal-content animated ' + animated);

    //header
    $('#modal-header').html(titre);

    //content
    $('#modal-body').html(contenu);

    //show modal
    $('#modal').modal('show');
    $('.modal-dialog').draggable({ handle:'.deplacer'});
}

function modal_ui(options, data, entete_html, percent_height, percent_width) {
    entete_html = typeof entete_html !== 'undefined' ? entete_html : false;
    percent_height = typeof percent_height !== 'undefined' ? percent_height : 0.85;
    percent_width = typeof percent_width !== 'undefined' ? percent_width : 0.9;

    var numero = parseInt($('#modal-ui').attr('data-id')),
        id = "modal-ui-" + numero;
    $('#modal-ui').append('<div id="' + id + '"></div>');

    var height = $(window).height() * percent_height,
        width = $(window).width() * percent_width;

    $('#' + id).html(data).dialog({
        title: options.title,
        height: height,
        width: width,
        show: {
            //effect: "scale",
            duration: 500
        },
        hide: {
            //effect: "scale",
            duration: 500
        },
        modal: options.modal
    })
        .parent().addClass('modal-shadow');

    if (entete_html) {
        $('#' + id).parent().find('div.ui-dialog-titlebar span.ui-dialog-title').html(options.title);
    }

    $('#modal-ui').attr('data-id', numero + 1);

    //modal
    $('div.ui-dialog').addClass('modal-content animated pulse');
    //modal-header
    $('div.ui-dialog div.ui-dialog-titlebar').addClass('modal-header');
    $('div.ui-dialog button.ui-dialog-titlebar-close').addClass('pull-right btn btn-default btn-xs').html('<i class="fa fa-times" aria-hidden="true"></i>');
    //modal-content
    $('div.ui-dialog div.ui-dialog-content').addClass('modal-body');
    //modal footer
    $('div.ui-dialog div.ui-resizable-se').addClass('pull-right')
        .removeClass('ui-icon')
        .removeClass('ui-icon-gripsmall-diagonal-se')
        .html('<i class="fa fa-expand fa-rotate-90" style="margin: 5px !important" aria-hidden="true"></i>');
    $('div.ui-dialog').resize(function(){
        if (typeof resize_modal_ui === 'function') resize_modal_ui();
    });
}

//function close pop up
function close_modal()
{
    $('#modal-body').empty();
    $('#modal').modal('hide');
}

//fonction set table jqgrid
function set_table_jqgrid(data,height,colNames,colModel,table,caption)
{
    caption = typeof caption !== 'undefined' ? caption : '';
    var id_table = table.attr('id');
    table.after('<table id="'+id_table+'_temp"></table>');
    table.jqGrid("clearGridData");
    table.jqGrid('GridDestroy');
    $('#'+id_table+'_temp').attr('id',id_table);
    $('#'+id_table).after('<table id="'+id_table+'_temp"></table>');
    $('#'+id_table).jqGrid({
        data: data,
        datatype: "local",
        height: height,
        autowidth: true,
        shrinkToFit: true,
        rowNum: 100000,
        rowList: [10, 20, 30],
        colNames: colNames,
        colModel: colModel,
        viewrecords: true,
        caption: caption,
        hidegrid: false,
        edit: true,
    });

    if(caption === '') $('#gview_'+id_table+' div.ui-jqgrid-caption').addClass('hidden');
}

function group_head_jqgrid(id,groupHeaders,useColSpanStyle)
{
    jQuery("#"+id).jqGrid('setGroupHeaders', {
        useColSpanStyle: useColSpanStyle,
        groupHeaders: groupHeaders
    });
}

//fonction set titre content
function set_wrapper_header(html)
{
    $('#wrapper-header-text').html(html);
}

//fonction choix menu active
function menu_active()
{
    // lien actuel sur le navigateur
    var current_path = window.location.pathname;

    $('.nav.metismenu li').removeClass('active');
    $('.nav.metismenu li').each(function () {
        // lien sur chaque menu
        var lien = $(this).find('a:first-child').attr('href');
        if (typeof lien !== 'undefined') {
            var path_menu = lien;
            //si path_menu == lien actuel
            if (current_path == (path_menu)) {
                $(this).addClass('active');

                if ($(this).closest('ul.nav').hasClass('nav-second-level')) {
                    $(this).closest('ul.nav').addClass('collapse in');
                    $(this).closest('ul.nav').closest('li').addClass('active');
                }

                return;
            }
        }
    });
}

//gerer height
function gerer_height()
{
    var hauteur = $(window).height() * 0.9;
    $('.principale-scroll').css({'min-height':hauteur , 'max-height':hauteur});
    $('.scroller').height( $('.principale-scroll').height() * 0.8);
    //$('.in_scoller').height($('.scroller').)

    $('.menu-scroll').css({'min-height':hauteur * 0.85 , 'max-height':hauteur * 0.85 });
}

//get class active table tr
function get_active_tr()
{
    return 'success';
}

function close_pop_over(e)
{
    $('[data-toggle="popover"]').each(function () {
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0)
        {
            $(this).popover('hide');
        }
    });
}

function activer_qTip()
{
    $('.js_tooltip').each(function(){
        var content = $(this).attr('data-tooltip');
        $(this).qtip({
            content: content,
            show: 'mouseover',
            hide: 'mouseout',
            style: {
                classes: 'qtip-youtube'
            }
        })
    })
}

function reinitialiser_inspinia(selecteur)
{
    // Collapse ibox function
    $('#'+selecteur+' .collapse-link').click(function () {
        var ibox = $(this).closest('div.ibox');
        var button = $(this).find('i');
        var content = ibox.find('div.ibox-content');
        content.slideToggle(200);
        button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        ibox.toggleClass('').toggleClass('border-bottom');
        setTimeout(function () {
            ibox.resize();
            ibox.find('[id^=map-]').resize();
        }, 50);
    });

    // Close ibox function
    $('#'+selecteur+' .close-link').click(function () {
        var content = $(this).closest('div.ibox');
        content.remove();
    });

    // Fullscreen ibox function
    $('#'+selecteur+' .fullscreen-link').click(function() {
        var ibox = $(this).closest('div.ibox');
        var button = $(this).find('i');
        $('body').toggleClass('fullscreen-ibox-mode');
        button.toggleClass('fa-expand').toggleClass('fa-compress');
        ibox.toggleClass('fullscreen');
        setTimeout(function() {
            $(window).trigger('resize');
        }, 100);
    });

    // Initialize slimscroll for right sidebar
    $('#'+selecteur+' .sidebar-container').slimScroll({
        height: '100%',
        railOpacity: 0.4,
        wheelStep: 10
    });

    // Add slimscroll to element
    $('#'+selecteur+' .full-height-scroll').slimscroll({
        height: '100%',
        wheelStep: 3,
        //color: '#a9a9a9',
    })
}

function remove_j_query_ui()
{
    $('.ui-corner-all').removeClass('ui-corner-all');
    $('.ui-widget').removeClass('ui-widget');
}

//get next class ul of menu
function get_next_class_ul_menu(ul)
{
    var result = 'nav ';
    if(ul.hasClass('metismenu')) result += 'nav-second-level';
    else if(ul.hasClass('nav-second-level')) result += 'nav-third-level';
    else if(ul.hasClass('nav-third-level')) result += 'nav-fourth-level';
    else if(ul.hasClass('nav-fourth-level')) result += 'nav-fifth-level';
    return result;
}

//refresh metisMenu
function refreshMetsiMenu()
{
    $('.side-menu').removeData("mm");

    $('.side-menu ul').unbind( "click" );
    $('.side-menu li').unbind( "click" );
    $('.side-menu a').unbind( "click" );

    $('.side-menu').metisMenu();
}

//function sans accent
String.prototype.sansAccent = function(){
    var accent = [
        /[\300-\306]/g, /[\340-\346]/g, // A, a
        /[\310-\313]/g, /[\350-\353]/g, // E, e
        /[\314-\317]/g, /[\354-\357]/g, // I, i
        /[\322-\330]/g, /[\362-\370]/g, // O, o
        /[\331-\334]/g, /[\371-\374]/g, // U, u
        /[\321]/g, /[\361]/g, // N, n
        /[\307]/g, /[\347]/g, // C, c
    ];
    var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];
    var str = this;
    for(var i = 0; i < accent.length; i++){
        str = str.replace(accent[i], noaccent[i]);
    }
    return str;
};

Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++) {
        if(this[i] === p_val) {
            return true;
        }
    }
    return false;
};

// Modal Déplaçable
function modalDraggable() {
    $(document).find('.modal-dialog').draggable({
        handle: '.modal-title'
    });
}

// Sort case insensitive
function jqGridSortable(s) {
    if (s === null) s = '';
    return s.toLowerCase();
}

//Minimize left Navbar Menu
function minimizeLeftNavMenu() {
    var minimize = (Cookies('intranet-nav-left-menu') === 'minimize');

    if (minimize) {
        $('.menu-left-minimize').closest('body').addClass('mini-navbar');
    } else {
        $('.menu-left-minimize').closest('body').removeClass('mini-navbar');

    }
}

// GET USER LOCAL IP
function getUserIp(callback) {
    window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;   //compatibility for firefox and chrome
    var pc = new RTCPeerConnection({iceServers: []}), noop = function () {
    };
    pc.createDataChannel("");    //create a bogus data channel
    pc.createOffer(pc.setLocalDescription.bind(pc), noop);    // create offer and set local description
    pc.onicecandidate = function (ice) {  //listen for candidate events
        if (!ice || !ice.candidate || !ice.candidate.candidate)  return '127.0.0.1';
        var myIP = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/.exec(ice.candidate.candidate)[1];
       callback(myIP);
        pc.onicecandidate = noop;
    };
}

//Test if user input is a number
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
}

//Pluralize a Word
function pluralize(singular, plural, number) {
    if (number <= 1) {
        return singular;
    }  else {
        return plural;
    }
}

jQuery.fn.putCursorAtEnd = function() {

    return this.each(function() {

        // Cache references
        var $el = $(this),
            el = this;

        // Only focus if input isn't already
        if (!$el.is(":focus")) {
            $el.focus();
        }

        // If this function exists... (IE 9+)
        if (el.setSelectionRange) {

            // Double the length because Opera is inconsistent about whether a carriage return is one character or two.
            var len = $el.val().length * 2;

            // Timeout seems to be required for Blink
            setTimeout(function() {
                el.setSelectionRange(len, len);
            }, 1);

        } else {

            // As a fallback, replace the contents with itself
            // Doesn't work in Chrome, but Chrome supports setSelectionRange
            $el.val($el.val());

        }

        // Scroll to the bottom, in case we're in a tall textarea
        // (Necessary for Firefox and Chrome)
        this.scrollTop = 999999;

    });
};

function siteParClientMulti(client_selector, site_selector, loader_selector, callback) {
    site_selector
        .html('')
        .val('')
        .trigger('chosen:updated');

    if (client_selector.val() && client_selector.val() !== '') {
        if (loader_selector) {
            loader_selector.show().html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
        }
        var url = Routing.generate('js_site_par_client', {client: client_selector.val()});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            site_selector.append('<option value="">Tous</option>');
            data.map(function(site) {
                site_selector.append('<option value="' + site.id + '">' + site.nom + '</option>');
            });
            site_selector.trigger('chosen:updated');

            if (typeof callback === 'function') {
                callback();
            } else {
                if (loader_selector) {
                    loader_selector.hide();
                }
            }
        }).catch(function(error) {
            if (loader_selector) {
                loader_selector.hide();
            }
        });
    }
}

function siteParClientImage(client_selector, site_selector, loader_selector, callback) {
    site_selector
        .html('')
        .val('')
        .trigger('chosen:updated');

    if (client_selector.val() && client_selector.val() !== '') {
        if (loader_selector) {
            loader_selector.show().html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
        }
        var url = Routing.generate('js_site_par_client', {client: client_selector.val()});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            site_selector.append('<option value="">Tous</option>');
            data.map(function(site) {
                site_selector.append('<option value="' + site.id + '">' + site.nom + '</option>');
            });
            site_selector.trigger('chosen:updated');

            if (typeof callback === 'function') {
                callback();
            } else {
                if (loader_selector) {
                    loader_selector.hide();
                }
            }
        }).catch(function(error) {
            if (loader_selector) {
                loader_selector.hide();
            }
        });
    }
}

function dossierParSiteImage(client_selector, site_selector, dossier_selector, loader_selector, callback) {
    dossier_selector
        .html('')
        .val('')
        .trigger('chosen:updated');

    if (typeof site_selector.val() !== 'undefined') {
        if (loader_selector) {
            loader_selector.show().html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
        }
        var url = Routing.generate('js_dossier_par_site', {client: client_selector.val(), site: site_selector.val()});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            dossier_selector.append('<option value="0">Tous</option>');
            data.map(function (dossier) {
                dossier_selector.append('<option value="' + dossier.id + '">' + dossier.nom + '</option>');
            });
            dossier_selector.trigger('chosen:updated');

            if (typeof callback === 'function') {
                callback();
            } else {
                if (loader_selector) {
                    loader_selector.hide();
                }
            }
        }).catch(function(error) {
            console.log(error);
            if (loader_selector) {
                loader_selector.hide();
            }
        });
    }
}

function dossierParSiteMulti(client_selector, site_selector, dossier_selector, loader_selector, callback) {
    dossier_selector
        .html('')
        .val('')
        .trigger('chosen:updated');

    if (typeof site_selector.val() !== 'undefined') {
        if (loader_selector) {
            loader_selector.show().html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
        }
        var url = Routing.generate('js_dossier_par_site', {client: client_selector.val(), site: site_selector.val()});
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            dossier_selector.append('<option value="0">Tous</option>');
            data.map(function (dossier) {
                dossier_selector.append('<option value="' + dossier.id + '">' + dossier.nom + '</option>');
            });
            dossier_selector.trigger('chosen:updated');

            if (typeof callback === 'function') {
                callback();
            } else {
                if (loader_selector) {
                    loader_selector.hide();
                }
            }
        }).catch(function(error) {
            console.log(error);
            if (loader_selector) {
                loader_selector.hide();
            }
        });
    }
}

/** permet de déclencher l'appel à une fonction après un certain délai
 * Annuler l'appel précédent si nouvel appel
 */
function makeDebounce(callback, delay){
    var timer;
    return function(){
        var args = arguments;
        var context = this;
        clearTimeout(timer);
        timer = setTimeout(function(){
            callback.apply(context, args);
        }, delay)
    }
}

/** Eviter des appels consécutifs en introduisant un délai */
function makeThrottle(callback, delay) {
    var last;
    var timer;
    return function () {
        var context = this;
        var now = +new Date();
        var args = arguments;
        if (last && now < last + delay) {
            // le délai n'est pas écoulé on reset le timer
            clearTimeout(timer);
            timer = setTimeout(function () {
                last = now;
                callback.apply(context, args);
            }, delay);
        } else {
            last = now;
            callback.apply(context, args);
        }
    };
}

function number_fr_to_float(s)
{
    return parseFloat(s.replace(/&nbsp;/gi, '').replace(/ /g,"").replace(/,/,'.'));
}

