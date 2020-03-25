$(function() {

    var color_row = '<tr>\n' +
        '               <td>\n' +
        '                   <input type="color">\n' +
        '               </td>\n' +
        '               <td>\n' +
        '                   <input type="text" class="duree-min"><span style="margin: 0 3px;">à</span><input type="text" class="duree-max">\n' +
        '               </td>\n' +
        '               <td><span class="fa fa-trash pointer remove-color" title="Supprimer"></span></td>\n' +
        '            </tr>';

    loadParam(0);

    /** Enregistrer Jours Travaillés */
    $(document).on('click', '#btn-save-jour-travail', function() {
        var data = [];
        $(document).find('.weekday-row').each(function(index, elt) {
           var item = {
               'weekday': $(elt).attr('data-weekday'),
               'checked': $(elt).find('input[type="checkbox"]').prop('checked') ? 1 : 0,
               'heure': $(elt).find('input[type="text"]').val() !== '' ? $(elt).find('input[type="text"]').val() : 0
           };
           data.push(item);
        });
        console.log(data);
        $.ajax({
            url: Routing.generate('priorite_param_jour_edit'),
            type: 'POST',
            data: {
                jours: data
            },
            success: function() {
                loadParam(1);
            }
        })
    });

    /** Ajouter Une ligne dans couleur */
    $(document).on('click', '#btn-add-color-row', function() {
       addColorRow();
    });

    /** Supprimer une ligne dans couleur */
    $(document).on('click', '.remove-color', function() {
       $(this).closest('tr').remove();
    });

    /** Enregistrer paramètres couleur */
    $(document).on('click', '#btn-save-color', function() {
        var colors = [],
            default_color = $('#default-color').val();
        $('#table-couleur').find('tbody>tr').each(function(index, item) {
            var row = $(item);
            var color = row.find('input[type="color"]').val();
            var min = row.find('.duree-min').val();
            var max = row.find('.duree-max').val();
            colors.push({
               'color': color,
               'min': min,
               'max': max
            });
        });

        $.ajax({
            url: Routing.generate('priorite_param_color_edit'),
            type: 'POST',
            data: {
                colors: colors,
                default_color: default_color
            },
            success: function() {
                loadParam(2);
            }
        })
    });

    /**
     *
     * @param param 0: all, 1: jours, 2: couleurs
     */
    function loadParam(param) {
        if (typeof param === 'undefined') {
            return;
        }
        /** Charger Tous les Paramètres */
        $.ajax({
            url: Routing.generate('priorite_load_param', {param: param}),
            type: 'GET',
            success: function (data) {
                data = $.parseJSON(data);
                var tableau;
                /** Paramètres jours */
                if (typeof data['jours'] !== 'undefined') {
                    if (typeof data['jours']['paramValue'] !== 'undefined') {
                        tableau = $(document).find('#table-jour-travail');
                        data['jours']['paramValue'].forEach(function (elt) {
                            var row = tableau.find('tr[data-weekday="' + elt.weekday + '"]');
                            var checkbox = row.find('input[type="checkbox"]');
                            var heureInput = row.find('input[type="text"]');

                            if (elt.checked) {
                                if (!checkbox.prop('checked')) {
                                    checkbox.iCheck('check');
                                }
                            }
                            else {
                                if (checkbox.prop('checked')) {
                                    checkbox.iCheck('uncheck');
                                }
                            }

                            heureInput.val(elt.heure);
                        });
                    }
                }
                /** Paramètres couleurs */

                /** Couleur par défaut */
                if (data['default_color'] != null && typeof data['default_color'] !== 'undefined') {
                    if (Array.isArray(data['default_color']['paramValue'])) {
                        $('#default-color').val(data['default_color']['paramValue'][0]);
                    }
                }
                /** Couleur par intervalle */
                if (data['colors'] != null && typeof data['colors'] !== 'undefined') {
                    if (typeof data['colors']['paramValue'] !== 'undefined') {
                        tableau = $(document).find('#table-couleur');
                        var tbody = tableau.find('tbody');
                        tbody.empty();
                        var tab = [];

                        tab = sortByKeyAsc(data['colors']['paramValue'], 'min');

                        tab.forEach(function (elt) {
                            var row = '<tr>\n' +
                                '          <td>\n' +
                                '               <input type="color" value="' + elt.color + '">\n' +
                                '          </td>\n' +
                                '          <td>\n' +
                                '               <input type="text" class="duree-min" value="' + elt.min + '"><span style="margin: 0 3px;">à</span><input type="text" class="duree-max" value="' + elt.max + '">\n' +
                                '          </td>\n' +
                                '          <td><span class="fa fa-trash pointer remove-color" title="Supprimer"></span></td>\n' +
                                '       </tr>';
                            tbody.append(row);
                        });
                    }
                }
            }
        });
    }

    function addColorRow() {
        $('#table-couleur').find('tbody').append(color_row);
    }

    function sortByKeyDesc(array, key) {
        return array.sort(function (a, b) {
            var x = a[key]; var y = b[key];
            return ((x > y) ? -1 : ((x < y) ? 1 : 0));
        });
    }

    function sortByKeyAsc(array, key) {
        return array.sort(function (a, b) {
            var x = a[key]; var y = b[key];
            return ((x < y) ? -1 : ((x > y) ? 1 : 0));
        });
    }
});