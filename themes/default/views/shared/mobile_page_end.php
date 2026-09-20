<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
</div>
<script src="<?= $assets ?>js/mobile-actions.js?v=4"></script>
<script>
(function () {
    var root = document.querySelector('.mobile-shared-page');
    if (!root) return;
    function appUrl(href) {
        var url = new URL(href, location.href);
        if (url.origin !== location.origin) return href;
        url.searchParams.set('app', '1');
        url.searchParams.set('app_lang', root.dataset.appLanguage);
        return url.href;
    }
    root.querySelectorAll('form').forEach(function (form) {
        form.action = appUrl(form.getAttribute('action') || location.href);
        ['app','app_lang'].forEach(function (name) {
            if (!form.querySelector('input[name="' + name + '"]')) {
                var field = document.createElement('input');
                field.type = 'hidden'; field.name = name;
                field.value = name === 'app' ? '1' : root.dataset.appLanguage;
                form.appendChild(field);
            }
        });
    });
    root.addEventListener('click', function (event) {
        var link = event.target.closest('a[href]');
        if (!link || link.getAttribute('href').charAt(0) === '#' || link.hasAttribute('download')) return;
        if (/\/uploads\/|\/files\//.test(link.href) || !/^https?:/.test(link.href)) return;
        link.href = appUrl(link.href);
    }, true);
    $(function () {
        // Keep filtering and pagination reachable without horizontal table scrolling.
        root.querySelectorAll('table').forEach(function (table) {
            if (!table.closest('.table-responsive, .erp-table-wrap, .dataTables_wrapper')) {
                $(table).wrap('<div class="table-responsive"></div>');
            }
        });
        var search = root.querySelector('#search_table');
        if (search) {
            var panel = root.querySelector('.box-body');
            search.setAttribute('aria-label', search.placeholder || 'Search');
            search.classList.add('mobile-page-search');
            if (panel) panel.insertBefore(search, panel.firstChild);
        }
    });
})();
</script>
