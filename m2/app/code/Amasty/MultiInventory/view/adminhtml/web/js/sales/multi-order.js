define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        'jquery/ui'
    ], function (jQuery, _, $t) {

        return {
            globals: {
                data: null,
                classTable: null,
                regexp:null,
                findRegexp: null,
                findRegexpOptions: null,
                attrRegexp: null,
                numberRegexp:0,
                addSelect: false
            },
            table: null,

            findTable: function () {
                return jQuery(this.globals.classTable);
            },
            addHead: function (id, text) {
                jQuery(this.table).find('thead tr').append("<th class='col-" + id + "'>" + $t(text) + "</th>");
            },
            addTd: function (tr, id, text) {
                jQuery(this.table).children('tbody:eq(' + tr + ')').children("tr").append("<td class='col-" + id + "'>" + text + "</td>");
            },
            addBundleTd: function(option, name, id) {
                jQuery(option.parentElement.parentElement).append('<td class="col-' + id + '">' + name + '</td>');
            },
            getItems: function () {
                var items = [];
                var self = this;
                _.each(jQuery(this.table).find(self.globals.findRegexp), function (value, index) {
                    var name = jQuery(value).attr(self.globals.attrRegexp);
                    var regexp = new RegExp(self.globals.regexp,'ig');
                    var result = regexp.exec(name);
                    if (_.size(result) > 0) {
                        items[index] = result[self.globals.numberRegexp];
                    }
                });

                return items;
            },
            addData: function () {
                console.log(this.globals.data);
                if (_.size(this.globals.data) > 0) {
                    this.table = this.findTable();
                    var items = this.getItems();
                    var self = this;
                    this.addHead('warehouse', 'Ship from Warehouse');
                    this.addHead('room', 'Room & Shelf');
                    _.each(this.globals.data, function (value, index) {
                        var parent = index;
                        var tr = _.indexOf(items, parent);
                        if (!value.list) {
                            self.addBundle(tr, value);
                        } else {
                            self.addSimple(tr, parent, value, self);
                        }
                    });
                }
            },
            addSimple: function (tr, index, value, self) {
                if (!isNaN(tr) && tr != -1) {
                    if (self.globals.addSelect) {
                        var select = '<select class="admin__control-select warehouse-select" data-index="' + index + '" name="shipment[warehouse][' + index + ']">';
                        _.each(value.list, function (option) {
                            var selected = '';
                            if (option.warehouse_id == value.data.warehouse_id) {
                                selected = 'selected="selected"';
                            }
                            select += '<option value="' + option.warehouse_id + '" ' + selected + '>' + option.title + '</option>';
                        });
                        select += '</select>';
                        self.addTd(tr, "warehouse", select);
                    } else {
                        var text = '';
                        _.each(value.list, function (option) {
                            if (option.warehouse_id == value.data.warehouse_id) {
                                text = option.title;
                            }
                        });
                        self.addTd(tr, "warehouse", text);
                    }
                    self.addTd(tr, "room", value.data.room_shelf);
                }
            },
            addBundle: function (tr, value) {
                var optionIds = jQuery(this.table).children('tbody:eq(' + tr + ')').find(this.globals.findRegexpOptions),
                    index = 0,
                    self = this;
                _.each(value, function (option) {
                    var roomShelf = option.data.room_shelf,
                        optionWh = option.data.warehouse_id;
                    _.each(option.list, function (item) {
                        if (optionWh === item.warehouse_id) {
                            self.addBundleTd(optionIds[index], item.title, 'warehouse');
                        }
                    });
                    self.addBundleTd(optionIds[index], roomShelf, 'room');

                    index++;
                });

            }
        };

    });