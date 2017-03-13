(function (vendor, $, window) {

    var appName = 'Tables';

    if (!(appName in vendor)) {
        vendor[appName] = {};

        vendor[appName].getAppName = (function getAppName() {
            return appName;
        });

        vendor[appName].request = (function request(route, data) {
            if (!$.isPlainObject(route) || !('module' in route) || !('action' in route)) {
                throw new Error('Request route is not specified.');
            }

            if (!$.isPlainObject(data)) {
                data = {};
            }

            if ('action' in data) {
                throw new Error('Reserved field "action" used.');
            }

            data.action = 'supsystic-tables';

            var request = $.post(window.ajaxurl, $.extend({}, { route: route }, data)),
                deferred = $.Deferred();

            request.done(function (response, textStatus, jqXHR) {
                if (typeof response.success !== 'undefined' && response.success) {
                    deferred.resolve(response, textStatus, jqXHR);
                } else {
                    var message = 'There are errors during the request.';

                    if (typeof response.message !== 'undefined') {
                        message = response.message;
                    }

                    deferred.reject(message, textStatus, jqXHR);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                deferred.reject(errorThrown, textStatus, jqXHR);
            });

            return deferred.promise();
        });

        vendor[appName].getParameterByName = (function getParameterByName(name) {
            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(location.search);

            return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        });

        vendor[appName].replaceParameterByName = (function (url, paramName, paramValue) {
            var pattern = new RegExp('\\b('+paramName+'=).*?(&|$)');
            if (url.search(pattern) >= 0) {
                return url.replace(pattern,'$1' + paramValue + '$2');
            }
            return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
        });

        vendor[appName].createSpinner = (function createSpinner () {
            return $('<i/>', { class: 'fa fa-fw fa-spin fa-circle-o-notch' });
        });

        vendor[appName].initializeTable = (function initializeTable(table) {
            var defaultFeatures = {
                autoWidth:  false,
                info:       false,
                ordering:   false,
                paging:     false,
                responsive: true,
                searching:  false,
                stateSave:  false
            };

            var $table = (table instanceof $ ? table : $(table)),
                features = $table.data('features'),
                config = {},
                translation = {},
                language = $table.data('lang'),
                override = $table.data('override');

            $.each(features, function () {
                var featureName = this.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); });
                config[featureName] = true;
				if (featureName == 'ordering') {
					config.columnDefs = [
						{ 'type': 'natural', targets: '_all' }
					];
					if (!$table.data('head')) {
						config.aoColumnDefs = [
							{ "bSortable": false, "aTargets": [ "_all" ] }
						];
					}
				}
            });

            if ($table.data('sort-order')) {
                config.order = [
                    [$table.data('sort-column') - 1, $table.data('sort-order')]
                ];
            }

            if ($table.data('pagination-length')) {
                aLengthMenu = [];
                paginationLength = String($table.data('pagination-length'));
                aLengthMenu.push(paginationLength.replace('All', -1).split(',').map(Number));
                aLengthMenu.push(paginationLength.split(','));
                config.aLengthMenu = aLengthMenu;
            }

            if ($table.data('auto-index') && $table.data('auto-index') !== 'off') {
                config.fnRowCallback = function(nRow, aData, iDisplayIndex) {
                    $("td:first", nRow).html(iDisplayIndex +1);
                    return nRow;
                };
            } 

            config.responsive = {
                details: {
                    renderer: function (api, rowIdx, columns) {
                        var $table = $(api.table().node()),
                            $data = $('<table/>');

                        $.each(columns, function (i, col) {
                            if (col.hidden) {
                                var $cell = $(api.cell(col.rowIndex, col.columnIndex).node()),
                                    markup = '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">';
                                if ($table.data('head') == 'on') {
                                    var $headerContent = $(api.table().header()).find('th').eq(col.columnIndex).html();
                                    markup += '<td>';
                                    if ($headerContent) {
                                        markup += $headerContent;
                                    }
                                    markup += '</td>';
                                }
                                markup += '</tr>';
                                $cell.after(
                                    $('<td>')
                                        .addClass('collapsed-cell-holder')
                                        .attr('data-cell-row', col.rowIndex)
                                        .attr('data-cell-column', col.columnIndex)
                                        .hide()
                                );
                                $data.append($(markup).append($cell.addClass('collapsed').show()));
                            }
                        });
                        return $data.is(':empty') ? false : $data;
                    }
                }
            };

            $table.on('responsive-resize.dt', function(event, api, columns) {
                if ($table.width() > $table.parent().width()) {
                    api.responsive.recalc();
                    return;
                }
                for (var i = 0, len = columns.length; i < len; i++) {
                    if (columns[i]) {
                        $table.find('tr > td.collapsed-cell-holder[data-cell-column="' + i + '"]').each(function(index, el) {
                            var $this = $(this);
                            var $cell = $(api.cell(
                                    $this.data('cell-row'),
                                    $this.data('cell-column')
                                ).node());

                            if ($cell.hasClass('collapsed')) {
                                $cell.removeClass('collapsed');
                                $this.replaceWith($cell);
                            }
                        });
                    } 
                }
            });

            if ($table.data('responsive-mode') !== 1) {
                config.responsive = false;
            }

            $.fn.dataTableExt.oApi.fnFakeRowspan = function (oSettings) {

                function setCellAttributes(cellArray) {
                    for (var i = 0; i < cellArray.length; i++) {

                        if (cellArray[i].getAttribute('data-hide')) {
                            cellArray[i].style.display = 'none';
                        };

                        if (colspan = cellArray[i].getAttribute('data-colspan')) {
                            if (colspan > 1) {

                                cellArray[i].setAttribute('colspan', colspan);
                            };
                        };

                        if (rowspan = cellArray[i].getAttribute('data-rowspan')) {
                            if (rowspan > 1) {

                                cellArray[i].setAttribute('rowspan', rowspan);
                            };
                        };
                    };
                }

                $.each(oSettings.aoData, function(index, rowData) {
                    setCellAttributes(rowData.anCells);
                });

                if (oSettings.aoHeader.length) {
                    cells = [];
                    $.each(oSettings.aoHeader[0], function(index, cellData) {
                        cells.push(cellData.cell);
                    });
                    setCellAttributes(cells);
                };

                if (oSettings.aoFooter.length) {
                    cells = [];
                    $.each(oSettings.aoFooter[0], function(index, cellData) {
                        cells.push(cellData.cell);
                    });
                    setCellAttributes(cells);
                };

                return this;
            };

            if (language.length && language !== 'default') {
                $.get('//cdn.datatables.net/plug-ins/1.10.9/i18n/'+ language +'.json')
                    .done(function (response) {
                        translation = response;
                    }).always(function () {
                        $.each(override, function (key, value) {
                            if (value.length) {
                                translation[key] = value;
                                // We need to support old DT format, cuz some languages use it
                                translation['s' + key.charAt(0).toUpperCase() + key.substr(1)] = value;
                            }
                        });

                        config.language = translation;

                        return $table.dataTable($.extend({}, defaultFeatures, config)).fnFakeRowspan();
                    });
            } else {
                $.each(override, function (key, value) {
                    if (value.length) {
                        translation[key] = value;
                        // We need to support old DT format, cuz some languages use it
                        translation['s' + key.charAt(0).toUpperCase() + key.substr(1)] = value;
                    }
                });

                config.language = translation;

                return $table.dataTable($.extend({}, defaultFeatures, config)).fnFakeRowspan();
            }
        });
    }

    var reviewNoticeResponse = function() {
        $(document).one('click',
            '.supsystic-admin-notice a, .supsystic-admin-notice button',
            function(event) {

            var responseCode = $(this).data('response-code') || 'hide';
            
            $('.supsystic-admin-notice .notice-dismiss').trigger('click');

            window.supsystic.Tables.request({
                module: 'tables',
                action: 'reviewNoticeResponse',
            }, {
                responseCode: responseCode,
            });
        });
    };

    reviewNoticeResponse();

}(window.supsystic = window.supsystic || {}, window.jQuery, window));