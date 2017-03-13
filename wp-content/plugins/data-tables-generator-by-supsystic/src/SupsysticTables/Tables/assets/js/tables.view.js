(function ($, app, undefined) {

    $(document).ready(function () {

        height = $(window).height() - 240;
        $('#tableEditor').css({
            'max-height': height,
            'min-height': height,
            height: height,
        });

        var ace = window.ace.edit("css-editor");
        ace.setTheme("ace/theme/monokai");
        ace.getSession().setMode("ace/mode/css");

        var isFormula = function (value) {
            if (value) {
                if (value[0] === '=') {
                    return true;
                }
            }

            return false;
        };

        Handsontable.editors.NumericEditor.prototype.beginEditing = function () {
            var format = this.cellProperties.format || '';
            if (format.indexOf('%') > -1 && !isFormula(this.originalValue)) {
               this.originalValue = numeral(this.originalValue).format(format);
            }
            Handsontable.editors.TextEditor.prototype.beginEditing.call(this);
        };

        Handsontable.editors.NumericEditor.prototype.saveValue = function (val, ctrlDown) {
              var format = this.cellProperties.format || '';
              if (format.indexOf('%') > -1  && !isFormula(val[0][0])) {
                var value = val[0][0].replace(',', '.').replace('%', '');
                var fixed = value.split('.');
                if (fixed.length > 1) {
                    fixed = fixed[1].length + 3;
                } else {
                    fixed = 3;
                }
                val[0][0] = (value / 100).toFixed(fixed);
              }
              Handsontable.editors.TextEditor.prototype.saveValue.call(this, val, ctrlDown);
        };

        function initializeEditor() {
            var container = document.getElementById('tableEditor');

            return new Handsontable(container, {
                colHeaders:            true,
                colWidths:             100,
                comments:              true,
                contextMenu:           true,
                formulas:              true,
                manualColumnResize:    true,
                manualRowResize:       true,
                mergeCells:            true,
                outsideClickDeselects: false,
                renderer:              cleanHtmlRenderer,
                rowHeaders:            true,
                startCols:             app.getParameterByName('cols') || 5,
                startRows:             app.getParameterByName('rows') || 5,
            });
        }

        function cleanHtmlRenderer(instance, td, row, col, prop, value, cellProperties) {
            Handsontable.renderers.HtmlRenderer.apply(this, arguments);
            if (td.innerHTML === 'null') {
                td.innerHTML = '';
            }
        }

        function initializeTabs() {
            var $rows = $('.row-tab'),
                $buttons = $('.subsubsub.tabs-wrapper .button');

            var current = $buttons.filter('.current')
                .attr('href');

            $rows.filter(current)
               .addClass('active');

            $buttons.on('click', function (e) {
                e.preventDefault();

                var $button = $(this),
                    current = $button.attr('href');

                $rows.removeClass('active');

                $buttons.filter('.current').removeClass('current');
                $button.addClass('current');

                $rows.filter(current).addClass('active');

                if (current === '#row-tab-editor') {
                    editor.render();
                } else if (current === '#row-tab-preview') {
                    var $container = $(current).find('#table-preview'),
                        $_previewTable = null,
                        $customCss = $('#table-custom-css');

                    if (!$customCss.length) {
                        $customCss = $('<style/>', { id: 'table-custom-css' });
                        $('head').append($customCss);
                    }

                    saveTable.call($container).done(function () {
                        app.Models.Tables.render(app.getParameterByName('id'))
                            .done(function (response) {
                                var $preview = $(response.table),
                                    table;

                                if ($_previewTable !== null) {
                                    $_previewTable.destroy();
                                }

                                $container.empty().append($preview);
                                table = $container.find('table');

                                $_previewTable = app.initializeTable(table);

                                var ruleJS = window.ruleJS($container.find('table').attr('id'));

                                table.on('draw.dt init.dt', function () {
                                    ruleJS.init();
                                });


                                var $formatFormulaCells = table.find('[data-cell-type="numeric"][data-formula]').on('change', function(event) {
                                    var $this = $(this);
                                    $this.text(numeral($this.text()).format($this.data('cell-format')));
                                });

                                table.trigger('init.dt');
                                table.find('[data-cell-type="numeric"]').each(function(index, el) {
                                    $this = $(this);
                                    $this.text(numeral($this.text()).format($this.data('cell-format')));
                                });

                                table.find('td.editable').attr('contenteditable', true).on('keyup', function(event) {
                                    var $this = $(this);
                                    var originalValue = $this.data('original-value');

                                    if (originalValue !== undefined) {
                                        $this.attr('data-original-value', numeral().unformat($this.text()));
                                    }
                                    ruleJS.init();
                                    $formatFormulaCells.trigger('change');
                                });
                            });

                        $customCss.text(ace.getValue());
                    });
                }
            });
        }

        function saveTable() {
            var $loadable = $(this),
                defaultHtml = $loadable.html(),
                id = app.getParameterByName('id');

            $loadable.html(app.createSpinner());

            // Request to save settings.
            var settings = app.Models.Tables.request('saveSettings', {
                id: id,
                settings: $('form#settings').serialize()
            });

            // Request to save the table rows.
            var data = [];
            var columnsWidth = [];

            $.each(editor.getData(), function (x, rows) {
                var row = { cells: [] };

                $.each(rows, function (y, cell) {
                    var meta = editor.getCellMeta(x, y),
                        classes = [],
                        data = {
                            hidden: editor.mergeCells.mergedCellInfoCollection.getInfo(x, y) !== undefined
                        };
                    if (x == 0) {
                        columnsWidth.push(editor.getColWidth(y));
                    }

                    if (meta.className !== undefined) {
                        $.each(meta.className.split(' '), function (index, element) {
                            if (element.length) {
                                classes.push($.trim(element));
                            }
                        });
                    }

                    if ('comment' in meta && meta.comment.length) {
                        data.comment = meta.comment;
                    }

                    data.data = cell;
                    data.meta = classes;
                    data.calculatedValue = null;

                    if (meta.type) {
                        data.type = meta.type;
                        data.format = meta.format;
                        data.formatType = meta.formatType;
                        if (data.type == 'date') {
                            var date = moment(data.data, data.format);
                            if (date.isValid()) {
                                data.dateOrder = date.format('x');
                            }
                        }
                    }

                    if (isFormula(cell)) {
                        var item = editor.plugin.matrix.getItem(editor.plugin.utils.translateCellCoords({
                            row: x,
                            col: y
                        }));

                        if (item !== undefined) {
                            var value = item.value;
                            // round float
                            if (!isNaN(value) && value !== '0' && value !== 0 && value % 1 !== 0) {
                              var floatValue = parseFloat(value);
                              if (floatValue.toString().indexOf('.') !== -1) {
                                var afterPointSybolsLength = floatValue.toString().split('.')[1].length;
                                if (afterPointSybolsLength > 4) {
                                  item.value = floatValue.toFixed(4);
                                }
                              }
                            }
                            data.calculatedValue = item.value;
                        }
                    }

                    row.cells.push(data);
                });

                // Row height
                row.height = editor.getRowHeight(x);
                if (row.height === undefined || parseInt(row.height) < 10) {
                    row.height = null;
                }

                data.push(row);
            });

            var deferred = $.when(
                app.Models.Tables.setRows(id, data),
                app.Models.Tables.setMeta(id, {
                    mergedCells: editor.mergeCells.mergedCellInfoCollection,
                    columnsWidth: columnsWidth,
                    css: ace.getValue(),
                }),
                settings
            );

            return deferred.always(function () {
                $loadable.html(defaultHtml);
            });
        }

        initializeTabs() ;

        var editor, tableId = app.getParameterByName('id');
        editor = initializeEditor();
        window.editor = editor;

        editor.addHook('beforeChange', function (changes, source) {

            $.each(changes, function (index, changeSet) {

                var row = changeSet[0],
                    col = changeSet[1],
                    value = changeSet[3],
                    cell = editor.getCellMeta(row, col);

                if (cell.type == 'date') {
                    var newDate = moment(value, cell.format);
                    if (newDate.isValid()) {
                        changeSet[3] = newDate.format(cell.format);
                    }
                }

            });

        });

        //FORMULAS RENDER FIX
        editor.addHook('afterChange', function (changes) {

            if (!$.isArray(changes) || !changes.length) {
                return;
            }

            $.each(changes, function (index, changeSet) {

                var row = changeSet[0],
                    col = changeSet[1],
                    value = changeSet[3];

                if (value.toString().match(/\\/)) {
                    editor.setDataAtCell(row, col, value.replace(/\\/g, '&#92;'));
                }

                if (isFormula(value)) {
                    var renderer = Handsontable.TextCell.renderer;
                    editor.setCellMeta(row, col, 'renderer', renderer, 'type', 'text');
                }

            });

            editor.render();

        });

        editor.addHook('afterLoadData', function () {
            var data = editor.getData();

            if (!data.length) {
                return;
            }

            $.each(data, function (row, columns) {
                $.each(columns, function (col, cell) {
                    if (isFormula(cell)) {
                        editor.setCellMeta(row, col, 'renderer', Handsontable.TextCell.renderer);
                    }
                });
            });
        });

        // END FORMULA RENDER FIX
        app.Editor.Hot = editor;

        var toolbar = new app.Editor.Toolbar('#tableToolbar', editor);
        app.Editor.Tb = toolbar;
        toolbar.subscribe();

        var formula = new app.Editor.Formula(editor);
        formula.subscribe();

        var loading = $.when(
            app.Models.Tables.getMeta(app.getParameterByName('id')),
            app.Models.Tables.getRows(tableId)
        );

        loading.done(function (metaResponse, rowsResponse) {
            var rows = rowsResponse[0].rows,
                meta = metaResponse[0].meta,
                comments = [];

            // Set currency
            $('form#settings').find('[name="currencyFormat"]').on('change', function(event) {
                event.preventDefault();
                var format = $.trim($(this).val()),
                    formatWithoutCurrency = format.match(/\d.*\d/)[0],
                    currencySymbol = format.replace(formatWithoutCurrency, '') || '$',
                    formatDelimiters = (formatWithoutCurrency.match(/[^\d]/g) || ['.', ',']).reverse(),
                    delimiters = {
                        decimal: formatDelimiters[0],
                        thousands: formatDelimiters[1]
                    };

                $('.currency-format').attr('data-format', format);

                var languageData = numeral.languageData();
                languageData.currency.symbol = currencySymbol;
                languageData.delimiters = delimiters;
                numeral.language('en', languageData);
                editor.currencySymbol = currencySymbol;
                editor.currencyFormat = format.replace(
                    formatWithoutCurrency, formatWithoutCurrency.replace(formatDelimiters[0], '.').replace(formatDelimiters[1], ','));
                $('.currency-format').attr('data-format', editor.currencyFormat);

                $('.htNumeric').each(function(index, el) {
                    var $cell = $(this),
                        row = $cell.parent().index(),
                        col = $cell.index() - 1,
                        cellMeta = editor.getCellMeta(row, col);

                    cellMeta.format = editor.currencyFormat.replace(editor.currencySymbol, '$');
                    editor.setCellMeta(row, col, cellMeta);
                });

            }).trigger('change');

            // Set percent
            $('form#settings').find('[name="percentFormat"]').on('change', function(event) {
                $('.percent-format').attr('data-format', $.trim($(this).val()));
            });

            // Set date
            $('form#settings').find('[name="dateFormat"]').on('change', function(event) {
                $('.date-format').attr('data-format', $.trim($(this).val()));
            });

            if (typeof meta === 'object' &&
                'mergedCells' in meta &&
                meta.mergedCells.length) {
                editor.updateSettings({
                  mergeCells: meta.mergedCells
                });
            }

            if (rows.length > 0) {
                var data = [], cellMeta = [], heights = [], widths = [];

                // Colors
                var $style = $('#supsystic-tables-style');

                if (!$style.length) {
                    $style = $('<style/>', { id: 'supsystic-tables-style' });
                    $('head').append($style);
                }

                $.each(rows, function (x, row) {
                    var cells = [];
                    heights.push(row.height || undefined);

                    $.each(row.cells, function (y, cell) {
                        cells.push(cell.data);
                        var metaData = {};

                        if ('meta' in cell && cell.meta !== undefined) {
                            var color = /color\-([0-9abcdef]{6})/.exec(cell.meta),
                                background = /bg\-([0-9abcdef]{6})/.exec(cell.meta);

                            if (null !== color) {
                                $style.html($style.html() + ' .'+color[0]+' {color:#'+color[1]+'}');
                            }

                            if (null !== background) {
                                $style.html($style.html() + ' .'+background[0]+' {background:#'+background[1]+' !important}');
                            }

                            metaData = $.extend(metaData, { row: x, col: y, className: cell.meta });
                        }

                        if (cell.type !== 'text') {
                            metaData = $.extend(metaData, {
                                row: x,
                                col: y,
                                type: cell.type,
                                format: cell.format,
                                formatType: cell.formatType,
                            });

                            if (cell.type == 'date') {
                                metaData.dateFormat = cell.format;
                                metaData.correctFormat =  true;
                            }

                        }
                        cellMeta.push(metaData);
                        if (x === 0 && meta.columnsWidth) {
                            widths.push(meta.columnsWidth[y] > 0 ? meta.columnsWidth[y] : 62);
                        } else if (x === 0 ) {
                            // Old
                             widths.push(cell.width === undefined ? 62 : cell.width);
                        }

                        if ('comment' in cell && cell.comment.length) {
                            comments.push({
                                col:     y,
                                row:     x,
                                comment: cell.comment
                            });
                        }

                    });

                    data.push(cells);
                });

                $('#row-tab-editor').toggle();
                // Height & width
                editor.updateSettings({
                    rowHeights: heights,
                    colWidths: widths
                });

                // Load extracted data.
                editor.loadData(data);
                $('#row-tab-editor').toggle();

                // Comments
                // Note: comments need to be loaded after editor.loadData() call.
                if (comments.length) {
                    editor.updateSettings({
                        cell: comments
                    });
                }

                // Load extracted metadata.
                $.each(cellMeta, function (i, meta) {
                    meta.className = meta.className.join(' ');
                    editor.setCellMetaObject(meta.row, meta.col, meta);
                });
            }

            editor.render();

        }).fail(function (error) {
            alert('Failed to load table data: ' + error);
        }).always(function (response) {
            $('#loadingProgress').remove();
            editor.render();
        });

        editor.addHook('afterRender', function () {

            var tableWidth = parseInt($(editor.rootElement).find('.ht_clone_top').width());
            // 50 = "f(x)" block width
            $('#formula').css({width: tableWidth - 50 });

        });

        (function insertColumnsFix() {
            editor.addHook('afterLoadData', function () {
                if (! editor.allWidths) {
                   editor.allWidths = editor.getSettings().colWidths;
                }
            });

            editor.addHook('afterCreateCol', function(insertColumnIndex) {

                var selectedCell = editor.getSelected(),
                    selectedColumnIndex = selectedCell[1] || 0;

                if (insertColumnIndex > selectedColumnIndex) {
                    insertColumnIndex = insertColumnIndex -1 ;
                }

                editor.allWidths.splice(
                    selectedColumnIndex, 0, editor.allWidths[insertColumnIndex]
                );

                editor.updateSettings({
                    colWidths: editor.allWidths
                });
            });

            editor.addHook('afterRemoveCol', function(from, amount) {
                editor.allWidths.splice(from, amount);
                var countCols = editor.countCols(),
                    colWidth,
                    allWidths = editor.allWidths,
                    plugin = editor.getPlugin('ManualColumnResize');

                for (var i = 0; i < countCols; i++) {
                    colWidth = editor.getColWidth(i);
                    if (colWidth !== allWidths[i]) {
                        plugin.setManualSize(i, allWidths[i]);
                    }
                }
            });

            editor.addHook('afterColumnResize', function(column, width) {
                editor.allWidths.splice(column, 1, width);
            });

        })();

        $('#buttonSave').on('click', function () {
            saveTable.call(this).fail(function (error) {
                alert('Failed to save table data: ' + error);
            });
        });
        $cloneDialog = $('#cloneDialog').dialog({
            autoOpen: false,
            width:    480,
            modal:    true,
            buttons:  {
                Close: function () {
                    $(this).dialog('close');
                },
                Clone: function (event) {
                    $dialog = $(this);

                    var $button = $(event.target).closest('button'),
                        defaultHtml = $button.html();
                    $button.html(app.createSpinner());
                    $button.attr('disabled', true);

                    app.Models.Tables.request('cloneTable', {
                        id: app.getParameterByName('id'),
                        title: $(this).find('input').val()
                    }).done(function(response) {
                        if (response.success) {
                            html = 'Done. <a href="' + app.replaceParameterByName(window.location.href, 'id', response.id) + '" >Open cloned table</a>';
                            $dialog.find('.input-group').hide();
                            $dialog.find('.input-group').after($('<div>', {class: 'message', html: html}));
                        }
                        $button.html(defaultHtml);
                    });
                }
            }
        });

        $('#buttonClone').on('click', function () {
            $cloneDialog.dialog('open');
            $cloneDialog.find('.message').remove();
            $cloneDialog.find('.input-group').show();
            $cloneDialog.next().find('button:eq(1)').removeAttr('disabled');
            $cloneDialog.find('input').val($.trim($('.table-title').text()) + '_Clone');
        });

        $('#buttonDelete').on('click', function () {
            var $button = $(this),
                html = $button.html();

            if (!confirm('Are you sure?')) {
                return;
            }

            // Do loading animation inside the button.
            $button.html(app.createSpinner());

            app.Models.Tables.remove(app.getParameterByName('id'))
                .done(function () {
                    window.location.href = $('#menuItem_tables').attr('href');
                })
                .fail(function (error) {
                    alert('Failed to delete table: ' + error);
                })
                .always(function () {
                    $button.html(html);
                });
        });

        $('.table-title[contenteditable]').on('keydown', function (e) {
            if (!('keyCode' in e) || e.keyCode !== 13) {
                return;
            }

            var $heading = $(this),
                title = $heading.text();

            $heading.removeAttr('contenteditable')
                .html(app.createSpinner());

            app.Models.Tables.rename(app.getParameterByName('id'), title)
                .done(function () {
                    $heading.text(title);
                    $heading.attr('data-table-title', title);
                })
                .fail(function (error) {
                    $heading.text($heading.attr('data-table-title'));
                    alert('Failed to rename table: ' + error);
                });
        });

        $('[data-toggle="tooltip"]').tooltip();

        // Pro notify
        var $notification = $('#proPopup').dialog({
            autoOpen: false,
            width:    480,
            modal:    true,
            buttons:  {
                Close: function () {
                    $(this).dialog('close');
                }
            }
        });
        $('.pro-notify').on('click', function () {
            $notification.dialog('open');
        });

        $editableFieldProFeatureDialog = $('#editableFieldProFeatureDialog').dialog({
            autoOpen: false,
            width:    480,
            modal:    true,
            buttons:  {
                Close: function () {
                    $(this).dialog('close');
                }
            }
        });

        $('#editableFieldProFeature').on('click', function(event) {
            event.preventDefault();
            $editableFieldProFeatureDialog.dialog('open');
        });

        editor.render();

    });

}(window.jQuery, window.supsystic.Tables));