(function($) {
    'use strict';

    $(function() {
        if (typeof init_selectpicker === 'function') {
            init_selectpicker();
        }

        init_tooltip();
        bindStatusToggles();
        initSubmenuBuilder();
    });

    function bindStatusToggles() {
        var $toggles = $('.ccx-toggle-menu');

        if (!$toggles.length) {
            return;
        }

        $toggles.on('change', function() {
            var $checkbox = $(this);
            var status = $checkbox.is(':checked') ? 1 : 0;
            var url = $checkbox.data('url');

            if (!url) {
                return;
            }

            $checkbox.prop('disabled', true);

            var payload = buildPayload({ status: status });

            $.post(url, payload)
                .fail(function() {
                    alert_float('danger', 'Unable to update menu status. Try again.');
                    $checkbox.prop('checked', !status);
                })
                .always(function() {
                    $checkbox.prop('disabled', false);
                });
        });
    }

    function initSubmenuBuilder() {
        var $wrapper = $('#ccx-submenu-wrapper');
        var $template = $('#ccx-submenu-template');
        var $trigger = $('#ccx-add-submenu');

        if (!$wrapper.length || !$template.length || !$trigger.length) {
            return;
        }

        var emptyStateHtml = '<div class="ccx-submenu-empty tw-text-center tw-text-slate-500 tw-bg-slate-50 tw-border tw-border-dashed tw-border-slate-300 tw-rounded tw-py-4">No sub-menus yet. Use "Add Sub-Menu" to start crafting contextual navigation.</div>';
        var index = parseInt($wrapper.data('count'), 10);

        if (isNaN(index)) {
            index = 0;
        }

        $trigger.on('click', function() {
            if ($wrapper.find('.ccx-submenu-empty').length) {
                $wrapper.empty();
            }

            var currentIndex = index++;
            var statusId = 'submenu_status_' + currentIndex + '_' + Date.now();
            var html = $template.html()
                .replace(/__INDEX__/g, currentIndex)
                .replace(/__HUMAN_INDEX__/g, currentIndex + 1)
                .replace(/__STATUS_ID__/g, statusId);

            $wrapper.append($(html));
            init_selectpicker();
        });

        $wrapper.on('click', '.ccx-remove-submenu', function() {
            $(this).closest('.ccx-submenu-card').remove();

            if ($wrapper.find('.ccx-submenu-card').length === 0) {
                $wrapper.html(emptyStateHtml);
            }
        });
    }

    function buildPayload(data) {
        var payload = $.extend({}, data);

        if (typeof csrfData !== 'undefined' && csrfData.token_name) {
            payload[csrfData.token_name] = csrfData.hash;
        }

        return payload;
    }
})(jQuery);
