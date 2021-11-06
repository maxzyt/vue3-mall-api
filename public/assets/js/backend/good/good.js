define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'good.good/index' + location.search,
                    add_url: 'good.good/add',
                    edit_url: 'good.good/edit',
                    del_url: 'good.good/del',
                    multi_url: 'good.good/multi',
                    table: 'good',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'goodcategory.name', title: __('Goodcategory.name')},
                        {field: 'name', title: __('Name')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'market_price', title: __('Market_price'), operate:'BETWEEN'},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'policy', title: __('Policy')},
                        {field: 'sale', title: __('Sale')},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'is_home', title: __('Is_home'), searchList: {"0":__('Is_home 0'),"1":__('Is_home 1')}, formatter: Table.api.formatter.normal},
                        {field: 'is_best', title: __('Is_best'), searchList: {"0":__('Is_best 0'),"1":__('Is_best 1')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $(document).on('click', '.nav-tabs li', function () {
                console.log(1);
                $('.nav-tabs li').removeClass('active');
                $(this).addClass('active');
                var ele = $(this).data('ele');console.log(ele);
                $('.tab-pane').addClass('hide');
                $('#'+ele).removeClass('hide');
            });
            $(document).on('click', '.add-specs', function () {
                Fast.api.open('good.specs/add', __('Add_specs'), {
                    callback: function (data) {
                        console.log(data);
                        Fast.api.ajax('good.specs/getSpecs?id='+data, function (data) {
                           var len = data.data.length;
                           var str = '';
                           for (var i=0; i<len; i++) {
                               if(i == 0) {
                                   str += '<label>'+data.data[i].specs.name+'</label>';
                                   str += '<input type="hidden" name="row[specs_id][]" value="'+data.data[i].specs_id+'"/>';
                                   str += 'div';
                               }
                               str += '<label className="checkbox-inline">';
                               str += '    <input type="checkbox" name="row[specs_value_id]['+data.data[i].specs_id+'][]" value="'+data.data[i].id+'">'+data.data[i].name+'</label>';
                               if(i == len-1) {
                                   str += '</div>';
                               }
                           }
                           $('.specs-content').append(str);
                        });
                    }
                });
            });
        },
        edit: function () {
            Controller.api.bindevent();
            $(document).on('click', '.nav-tabs li', function () {
                console.log(1);
                $('.nav-tabs li').removeClass('active');
                $(this).addClass('active');
                var ele = $(this).data('ele');console.log(ele);
                $('.tab-pane').addClass('hide');
                $('#'+ele).removeClass('hide');
            });
            $(document).on('click', '.add-specs', function () {
                Fast.api.open('good.specs/add?id='+$('#specs').data('id'), __('Add_specs'), {
                    callback: function (data2) {
                        console.log(data2);
                        Fast.api.ajax('good.specs/getSpecs?id='+data2, function (data) {
                            console.log(data);
                            var len = data.length;
                            var str = '';
                            for (var i=0; i<len; i++) {
                                if(i == 0) {
                                    str += '<label>'+data[i].specs.name+'</label>';
                                    str += '<input type="hidden" name="row[specs_id][]" value="'+data[i].specs_id+'"/>';
                                    str += '<div>';
                                }
                                str += '<label className="checkbox-inline">';
                                str += '    <input type="checkbox" name="row[specs_value_id][]" value="'+data[i].id+'">'+data[i].name+'</label>';
                                if(i == len-1) {
                                    str += '</div>';
                                }
                            }
                            console.log(str);
                            $('.specs-content').append(str);
                        });
                    }
                });
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), '', '', function (success, error) {
                    var arr = [$('#c-is_home').val(), $('#c-is_best').val(), $('#c-butie').val()];
                    var k = 0;
                    for (var i=0;i<3;i++) {
                        if(arr[i] == 1) {
                            k++;
                        }
                    }
                    if(k > 1) {
                        Toastr.error("首页推荐，每日上新，百亿补贴只能选择一个是");return false;
                    }
                });
            }
        }
    };
    return Controller;
});