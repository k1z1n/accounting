function attachLoadMore(tbodyId, btnId, apiUrl, render) {
    const tbody = document.getElementById(tbodyId);
    const btn   = document.getElementById(btnId);

    btn.addEventListener('click', () => {
        if (btn.dataset.hasMore !== 'true') return;

        const next = btn.dataset.nextPage;
        btn.disabled = true;
        btn.textContent = 'Загрузка…';

        fetch(`${apiUrl}?page=${next}`, {
            headers: {'X-Requested-With':'XMLHttpRequest'}
        })
            .then(r=>r.json())
            .then(json=>{
                json.data.forEach(item => {
                    tbody.insertAdjacentHTML('beforeend', render(item));
                });
                btn.dataset.nextPage = parseInt(next)+1;
                btn.dataset.hasMore  = json.has_more ? 'true':'false';
                btn.textContent      = json.has_more ? 'Загрузить ещё' : 'Больше нет';
                btn.disabled = false;
            })
            .catch(err=>{
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Загрузить ещё';
            });
    });
}

// 1) Transfers
attachLoadMore(
    'transfersTbody',
    'loadMoreTransfers',
    '{{ route('api.transfers') }}',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `
          <td class="px-5 py-4 whitespace-nowrap space-x-2">
            <button class="edit-transfer-btn …" data-id="${item.id}" …>✏️</button>
            <button class="delete-transfer-btn …">🗑️</button>
          </td>` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger_from.title}</td>
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger_to.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <div class="inline-flex items-center space-x-1">
            <span class="text-white">${item.amount ?? '—'}</span>
            ${item.amount_currency.code ? `<img src="/images/coins/${item.amount_currency.code}.svg" class="w-4 h-4">` : ''}
          </div>
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <div class="inline-flex items-center space-x-1">
            <span class="text-red-400">${item.commission ?? '—'}</span>
            ${item.commission_currency.code ? `<img src="/images/coins/${item.commission_currency.code}.svg" class="w-4 h-4">` : ''}
          </div>
        </td>
      </tr>`
);

// 2) Payments
attachLoadMore(
    'paymentsTbody',
    'loadMorePayments',
    '{{ route('api.payments') }}',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `
          <td class="px-5 py-4 whitespace-nowrap space-x-2">
            <button class="edit-payment-btn …" data-id="${item.id}" …>✏️</button>
            <button class="delete-payment-btn …">🗑️</button>
          </td>` : '' }
        <td class="px-5 py-4 text-white whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sell_amount}</span>
          <img src="/images/coins/${item.sell_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-white">${item.comment || '—'}</td>
      </tr>`
);

// 3) Purchases
attachLoadMore(
    'purchasesTbody',
    'loadMorePurchases',
    '{{ route('api.purchases') }}',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `…` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-green-400">${item.received_amount}</span>
          <img src="/images/coins/${item.received_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sale_amount}</span>
          <img src="/images/coins/${item.sale_currency.code}.svg" class="w-4 h-4">
        </td>
      </tr>`
);

// 4) SaleCrypts
attachLoadMore(
    'saleCryptsTbody',
    'loadMoreSaleCrypts',
    '{{ route('api.sale-crypts') }}',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `…` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sale_amount}</span>
          <img src="/images/coins/${item.sale_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="${item.fixed_amount>0?'text-green-400':'text-red-400'}">${item.fixed_amount>0?'+':''}${item.fixed_amount}</span>
          <img src="/images/coins/${item.fixed_currency.code}.svg" class="w-4 h-4">
        </td>
      </tr>`
);

window.onerror = (msg, src, ln, col, err) => console.error(msg, src, ln, col, err);
