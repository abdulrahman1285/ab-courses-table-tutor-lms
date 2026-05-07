/* LMS Courses Table v5 — script.js */
(function () {
    'use strict';

    var i18n = window.abctI18n || {
        noResults: 'No matching courses found',
        of: 'of',
        prev: '‹',
        next: '›'
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.abct-wrapper').forEach(initTable);
    });

    function initTable(wrapper) {
        var allRows    = Array.from(wrapper.querySelectorAll('.abct-row'));
        var pagination = wrapper.querySelector('.abct-pagination');
        var perPage    = pagination ? (parseInt(pagination.dataset.perPage) || 8) : 9999;
        var currentPage = 1;

        var activeMode = 'offline';
        var activeCat  = 'all';
        var searchVal  = '';

        // ── Category Tabs ──
        wrapper.querySelectorAll('.abct-cat-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                wrapper.querySelectorAll('.abct-cat-tab').forEach(function (b) { b.classList.remove('abct-active'); });
                btn.classList.add('abct-active');
                activeCat = btn.dataset.cat;
                currentPage = 1;
                render();
            });
        });

        // ── Mode Toggle ──
        wrapper.querySelectorAll('.abct-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                wrapper.querySelectorAll('.abct-mode-btn').forEach(function (b) { b.classList.remove('abct-mode-active'); });
                btn.classList.add('abct-mode-active');
                activeMode = btn.dataset.mode;
                currentPage = 1;
                render();
            });
        });

        // ── Search ──
        var searchEl = wrapper.querySelector('.abct-search');
        if (searchEl) {
            searchEl.addEventListener('input', function () {
                searchVal = searchEl.value.trim().toLowerCase();
                currentPage = 1;
                render();
            });
        }

        // ── Row highlight ──
        var tbody = wrapper.querySelector('#abct-tbody');
        if (tbody) {
            tbody.addEventListener('click', function (e) {
                var row = e.target.closest('.abct-row');
                if (!row) return;
                allRows.forEach(function (r) { r.classList.remove('abct-selected'); });
                row.classList.add('abct-selected');
            });
        }

        // ── Core render ──
        function render() {
            var visible = allRows.filter(function (row) {
                var okMode   = activeMode === 'all' || row.dataset.delivery === activeMode;
                var okCat    = activeCat === 'all'  || (row.dataset.cats || '').split(',').indexOf(activeCat) !== -1;
                var okSearch = !searchVal || (row.dataset.title || '').indexOf(searchVal) !== -1;
                return okMode && okCat && okSearch;
            });

            allRows.forEach(function (r) { r.style.display = 'none'; });

            // remove old empty row
            var oldEmpty = wrapper.querySelector('.abct-empty-dynamic');
            if (oldEmpty) oldEmpty.remove();

            var totalPages = Math.max(1, Math.ceil(visible.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            var start = (currentPage - 1) * perPage;
            visible.slice(start, start + perPage).forEach(function (r) { r.style.display = ''; });

            if (visible.length === 0 && tbody) {
                var tr = document.createElement('tr');
                tr.className = 'abct-empty-dynamic';
                tr.innerHTML = '<td colspan="10" class="abct-empty">' + i18n.noResults + '</td>';
                tbody.appendChild(tr);
            }

            renderPagination(totalPages, visible);
        }

        // ── Pagination ──
        function renderPagination(total, visible) {
            if (!pagination) return;
            pagination.innerHTML = '';
            if (total <= 1) return;

            pagination.appendChild(makeBtn('&#8249;', currentPage === 1, function () { currentPage--; render(); }));

            buildPageList(currentPage, total).forEach(function (p) {
                if (p === '...') {
                    var d = document.createElement('span');
                    d.className = 'abct-page-dots';
                    d.textContent = '...';
                    pagination.appendChild(d);
                } else {
                    var b = makeBtn(p, false, function () {
                        currentPage = parseInt(this.textContent);
                        render();
                    });
                    if (p === currentPage) b.classList.add('abct-active');
                    pagination.appendChild(b);
                }
            });

            pagination.appendChild(makeBtn('&#8250;', currentPage === total, function () { currentPage++; render(); }));

            if (visible.length > 0) {
                var s = (currentPage - 1) * perPage + 1;
                var e = Math.min(currentPage * perPage, visible.length);
                var lbl = document.createElement('span');
                lbl.className = 'abct-page-count';
                lbl.textContent = s + '–' + e + ' ' + i18n.of + ' ' + visible.length;
                pagination.appendChild(lbl);
            }
        }

        function makeBtn(html, disabled, onClick) {
            var btn = document.createElement('button');
            btn.className = 'abct-page-btn';
            btn.innerHTML = html;
            btn.disabled  = disabled;
            btn.addEventListener('click', onClick);
            return btn;
        }

        function buildPageList(cur, total) {
            var pages = [];
            for (var i = 1; i <= total; i++) {
                if (i === 1 || i === total || Math.abs(i - cur) <= 1) pages.push(i);
                else if (pages[pages.length - 1] !== '...') pages.push('...');
            }
            return pages;
        }

        render();
    }
})();
