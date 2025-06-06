document.addEventListener('DOMContentLoaded', () => {
    // DOM-узлы
    const table      = document.getElementById('applicationsTable');
    const hdrs       = table.querySelectorAll('thead th');
    const tbody      = document.getElementById('appsTbody');
    const statusSel  = document.getElementById('status');
    const tplSel     = document.getElementById('template');
    const loader     = document.getElementById('loader');

    // API endpoint
    const apiUrl = '/api/applications';

    // Пагинация и состояние
    let pageNum = 1;
    let hasMore = table.dataset.hasMore === 'true';
    let loading = false;

    // Функция для удаления лишних нулей
    function stripZeros(v) {
        if (v == null) return '';
        return String(v)
            .replace(/(\.\d*?[1-9])0+$/, '$1')
            .replace(/\.0+$/, '');
    }

    // HTML-шаблон строки
    function TPL_ROW(d) {
        return `
<tr class="hover:bg-gray-50">
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_created_at||''}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_status||''}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Сотрудник</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_id||''}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_meta_give0||''}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
    <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
      ${d.app_currency_give||''}
    </span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
    <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
      ${d.app_currency_get||''}
    </span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stripZeros(d.app_sum1dc)}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stripZeros(d.app_sum2dc)}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stripZeros(d.app_sum1c)}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stripZeros(d.app_sum2c)}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_first_name||''}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_txid_in||''}</td>
</tr>`;
    }

    // Параметры для шаблонов
    const paramToIndex = {
        'дата создания': 0, 'статус':1, 'сотрудник':2, 'номер заявки':3,
        'обменник':4, 'валюта прихода':5, 'валюта расхода':6,
        'приход (сумма)':7, 'расход (сумма)':8,
        'приход крипта':9, 'расход крипта':10,
        'мерчант':11, 'id ордера':12
    };
    const templates = {
        all:        Object.values(paramToIndex),
        exchange:   ['номер заявки','обменник','валюта прихода','валюта расхода','приход (сумма)','расход (сумма)','приход крипта','расход крипта','мерчант','id ордера'],
        sale:       ['обменник','валюта прихода','валюта расхода','расход (сумма)'],
        withdraw:   ['обменник','расход (сумма)'],
        transfer:   ['обменник','расход (сумма)'],
        payment:    ['обменник','расход (сумма)'],
        refund:     ['номер заявки','обменник','приход (сумма)','расход (сумма)'],
        buy_crypto: ['обменник','приход (сумма)','расход (сумма)','мерчант']
    };
// переводим строки-ключи в индексы, пропуская all:
    for (let key in templates) {
        if (key === 'all') continue;
        templates[key] = templates[key].map(p => paramToIndex[p]);
    }

    let currentTpl = 'all';

    // Показываем/прячем колонки
    function applyTemplate(name) {
        const showCols = templates[name] || [];
        hdrs.forEach((th,i) => th.style.display = showCols.includes(i) ? '' : 'none');
        table.querySelectorAll('tbody tr').forEach(tr =>
            tr.querySelectorAll('td').forEach((td,j) =>
                td.style.display = showCols.includes(j) ? '' : 'none'
            )
        );
    }

    // Собираем URL c page_num и статусом
    function buildUrl() {
        let url = apiUrl + '?page_num=' + pageNum;
        if (statusSel.value) {
            url += '&status=' + encodeURIComponent(statusSel.value);
        }
        return url;
    }

    // Подгружаем страницу
    function loadMore(reset = false) {
        if (loading || (!hasMore && !reset)) return;
        loading = true;
        loader.style.display = '';

        if (reset) {
            pageNum = 1;
            tbody.innerHTML = '';
            hasMore = true;
        } else {
            pageNum++;
        }

        fetch(buildUrl())
            .then(r => r.json())
            .then(json => {
                json.data.forEach(d => {
                    tbody.insertAdjacentHTML('beforeend', TPL_ROW(d));
                });
                applyTemplate(currentTpl);
                hasMore = json.has_more;
            })
            .finally(() => {
                loading = false;
                loader.style.display = 'none';
            });
    }

    // Слушатели
    tplSel .addEventListener('change', () => {
        currentTpl = tplSel.value;
        applyTemplate(currentTpl);
    });
    statusSel.addEventListener('change', () => loadMore(true));
    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 50) {
            loadMore();
        }
    });

    // Старт
    applyTemplate('all');
    // — первую страницу Blade уже отрисовал, больше не затираем
});


