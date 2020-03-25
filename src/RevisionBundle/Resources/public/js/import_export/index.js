/**
 * Created by SITRAKA on 18/02/2019.
 */
$(document).ready(function(){

    $(document).on('click','#id_save',function(){
        $('#id_form').ajaxSubmit({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: { dossier:'1' },
            success: function(data){
                show_modal(data,'test');
            }
        });
    });

    var mydata = [
        {id: "1", invdate: "2010-05-24", name: "test", note: "note", tax: "10.00", total: "2111.00", n:'name_1'} ,
        {id: "2", invdate: "2010-05-25", name: "test2", note: "note2", tax: "20.00", total: "320.00", n:'name_2'},
        {id: "3", invdate: "2007-09-01", name: "test3", note: "note3", tax: "30.00", total: "430.00", n:'name_3'},
        {id: "4", invdate: "2007-10-04", name: "test", note: "note", tax: "10.00", total: "210.00", n:'name_4'},
        {id: "5", invdate: "2007-10-05", name: "test2", note: "note2", tax: "20.00", total: "320.00", n:'name_5'},
        {id: "6", invdate: "2007-09-06", name: "test3", note: "note3", tax: "30.00", total: "430.00", n:'name_6'},
        {id: "7", invdate: "2007-10-04", name: "test", note: "note", tax: "10.00", total: "210.00", n:'name_7'}
    ];

    // Configuration for jqGrid Example 1
    $("#id_export").jqGrid({
        data: mydata,
        datatype: "local",
        height: 250,
        autowidth: true,
        shrinkToFit: true,
        rowNum: 14,
        rowList: [10, 20, 30],
        colNames: ['Inv No', 'Date', 'Client'],
        colModel: [
            {name: 'id', index: 'id', width: 60, sorttype: "int"},
            {name: 'invdate', index: 'invdate', width: 90, sorttype: "date", formatter: "date"},
            {name: 'n', index: 'n', align: 'left', editable: true, formatter: function(v) { return v === 0 ? '' : '<input type="file" name="'+v+'">' }}
        ],
        pager: "#pager_list_1",
        viewrecords: true,
        caption: "Example jqGrid 1",
        hidegrid: false
    });
});
