define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'good.specs/index' + location.search,
                    add_url: 'good.specs/add',
                    edit_url: 'good.specs/edit',
                    del_url: 'good.specs/del',
                    multi_url: 'good.specs/multi',
                    table: 'specs',
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
                        {field: 'good_id', title: __('Good_id')},
                        {field: 'name', title: __('Name')},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'good.id', title: __('Good.id')},
                        {field: 'good.good_category_id', title: __('Good.good_category_id')},
                        {field: 'good.name', title: __('Good.name')},
                        {field: 'good.price', title: __('Good.price'), operate:'BETWEEN'},
                        {field: 'good.market_price', title: __('Good.market_price'), operate:'BETWEEN'},
                        {field: 'good.image', title: __('Good.image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'good.images', title: __('Good.images'), events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'good.policy', title: __('Good.policy')},
                        {field: 'good.sale', title: __('Good.sale')},
                        {field: 'good.weigh', title: __('Good.weigh')},
                        {field: 'good.status', title: __('Good.status'), formatter: Table.api.formatter.status},
                        {field: 'good.is_home', title: __('Good.is_home')},
                        {field: 'good.is_best', title: __('Good.is_best')},
                        {field: 'good.createtime', title: __('Good.createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'good.updatetime', title: __('Good.updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $(document).on('click', '.add-specs', function () {
               var htmls = $('.table tbody tr').eq(0).clone();
               var length = $('.table tbody tr').length;
               ++length;
               htmls.find('.btn-danger').removeClass('hide');
               htmls.find('input').eq(1).val('');
               htmls.find('input').eq(1).attr('id', 'c-image'+length);
               htmls.find('.plupload').attr('id', 'plupload-image'+length);
               htmls.find('.plupload').attr('data-input-id', 'c-image'+length);
               htmls.find('.plupload').attr('data-preview-id', 'p-image'+length);
               htmls.find('.plupload').removeAttr('initialized');
               htmls.find('.plupload').next().remove();
               htmls.find('.fachoose').attr('id', 'fachoose-image'+length);
               htmls.find('.fachoose').attr('data-input-id', 'c-image'+length);
               htmls.find('.msg-box').attr('for', 'c-image'+length);
               htmls.find('.plupload-preview').attr('id', 'p-image'+length);
               console.log(htmls.find('.plupload-preview').empty());
               $('.table tbody').append(htmls);
               Form.events.plupload('#add-form');
               Form.events.faselect("#add-form");
            });
            $(document).on('click', '.del-specs', function () {
                $(this).parents('tr').hide();
                $(this).parents('tr').find('input').attr('disabled', true);
            });
            //Controller.api.bindevent();
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                console.log(data);
                Fast.api.close(data);
                //这里是关闭弹窗后传递 Fast.api.open中的callback:function
            });
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});