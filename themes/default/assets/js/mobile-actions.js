/* Keep original server-authorized links and handlers; render like Products. */
(function ($) {
    var root = document.querySelector('.mobile-shared-page, .kls-mobile-ui');
    if (!root) return;
    var types = {
        edit: { icon: 'fa fa-pencil', label: 'ပြင်ဆင်ရန်' },
        view: { icon: 'fa fa-eye', label: 'အသေးစိတ်ကြည့်ရန်' },
        unit: { icon: 'fa fa-balance-scale', label: 'ယူနစ်ဆက်စပ်မှု သတ်မှတ်ရန်' },
        print: { icon: 'fa fa-print', label: 'ပုံနှိပ်ရန်' },
        delete: { icon: 'fa fa-trash', label: 'ဖျက်ရန်' }
    };
    function kind(link) {
        var href = link.getAttribute('href') || '';
        if (/delete|remove/i.test(href) || link.classList.contains('text-danger')) return 'delete';
        if (/edit/i.test(href + ' ' + link.className + ' ' + link.title) || link.querySelector('.fa-edit, .fa-pencil, .fa-pen')) return 'edit';
        if (/print|barcode/i.test(href)) return 'print';
        if (/unit|conversion/i.test(href)) return 'unit';
        if (/view|note|detail/i.test(href)) return 'view';
        return 'other';
    }
    function render(table) {
        if (!table) return;
        table.querySelectorAll('tbody .dropdown-menu, tbody .erp-actions, tbody .erp-action-buttons').forEach(function (menu) {
            var cell = menu.closest('td');
            if (!cell || cell.querySelector('.erp-action-btn')) return;
            var links = Array.from(menu.querySelectorAll('a[href]')).filter(function (link) { return !link.classList.contains('dropdown-toggle'); });
            if (!links.length) return;
            var actions = document.createElement('div'); actions.className = 'erp-actions';
            links.sort(function (a,b) {
                var order = ['edit','view','unit','print','other','delete'];
                return order.indexOf(kind(a)) - order.indexOf(kind(b));
            });
            links.forEach(function (link) {
                var type = kind(link), info = types[type];
                var label = link.textContent.trim() || link.title || 'Action';
                if (/^https?:/.test(link.href) && new URL(link.href).origin === location.origin) {
                    var url = new URL(link.href);
                    url.searchParams.set('app','1');
                    url.searchParams.set('app_lang', new URLSearchParams(location.search).get('app_lang') || document.documentElement.lang || 'english');
                    link.href = url.href;
                }
                var originalIcon = link.querySelector('i');
                var iconClass = info ? info.icon : (originalIcon ? originalIcon.className : 'fa fa-circle-o');
                link.classList.add('erp-action-btn', 'erp-action-' + type);
                link.removeAttribute('style'); link.setAttribute('aria-label',label); link.title = label;
                link.replaceChildren();
                var badge = document.createElement('span'); badge.className = 'erp-action-icon';
                var icon = document.createElement('i'); icon.className = iconClass; icon.setAttribute('aria-hidden','true');
                badge.appendChild(icon); link.appendChild(badge);
                var text = document.createElement('span'); text.className = 'erp-action-label'; text.textContent = label;
                link.appendChild(text); actions.appendChild(link);
            });
            cell.replaceChildren(actions); cell.classList.add('action-cell'); table.classList.add('kls-action-table');
        });
        var current = Array.from(table.querySelectorAll('.erp-action-btn'));
        if (!current.length) return;
        var host = table.closest('.box-body') || table.parentElement;
        var legend = host.querySelector('.mobile-generated-legend');
        if (!legend) {
            legend = document.createElement('div'); legend.className = 'erp-action-legend mobile-generated-legend';
            legend.setAttribute('aria-label','Action icon meanings'); host.appendChild(legend);
        }
        legend.replaceChildren();
        var title = document.createElement('div'); title.className = 'erp-action-legend-title';
        title.innerHTML = '<i class="fa fa-info-circle" aria-hidden="true"></i><span>လုပ်ဆောင်ချက်ပုံများ၏ အဓိပ္ပာယ်</span>';
        legend.appendChild(title);
        var list = document.createElement('div'); list.className = 'erp-action-legend-list'; legend.appendChild(list);
        var seen = new Set();
        current.forEach(function (link) {
            var type = kind(link), label = types[type] ? types[type].label : link.getAttribute('aria-label');
            if (seen.has(label)) return; seen.add(label);
            var item = document.createElement('div'); item.className = 'erp-action-legend-item erp-action-legend-' + type;
            var icon = document.createElement('span'); icon.className = 'erp-action-legend-icon';
            icon.appendChild(link.querySelector('i').cloneNode(true)); item.appendChild(icon);
            var text = document.createElement('span'); text.textContent = label; item.appendChild(text); list.appendChild(item);
        });
    }
    $(root).on('draw.dt', function (event) { render(event.target); });
    $(function () { root.querySelectorAll('table').forEach(render); });
})(jQuery);
