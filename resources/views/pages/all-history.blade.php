{{-- resources/views/pages/all-history.blade.php --}}
@extends('template.app')
@section('title','Вся история')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-white">Вся история операций</h1>
    <div class="bg-[#191919] rounded-xl shadow-md border border-[#2d2d2d] divide-y divide-[#2d2d2d]">
        @forelse($histories as $history)
            @php
                $amount = $history->amount;
                $sign = $amount > 0 ? '+' : '-';
                $abs = abs($amount);
                $formatted = rtrim(rtrim(sprintf('%.8f', $abs), '0'), '.');
                $currency = $history->currency;
                $icon = $currency ? "/images/coins/{$currency->code}.svg" : null;
                $date = $history->created_at ? \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i') : '';
                $type = class_basename($history->sourceable_type);
                $source = $history->sourceable;
                $typeRu = $type;
                if ($type === 'Purchase') $typeRu = 'Покупка крипты';
                elseif ($type === 'SaleCrypt') $typeRu = 'Продажа крипты';
                elseif ($type === 'Payment') $typeRu = 'Оплата';
                elseif ($type === 'Transfer') $typeRu = 'Перевод';
                elseif ($type === 'Application') $typeRu = 'Заявка';
                $exchanger = null;
                if ($type === 'Purchase' && $source && $source->exchanger) {
                    $exchanger = $source->exchanger->title;
                } elseif ($type === 'SaleCrypt' && $source && $source->exchanger) {
                    $exchanger = $source->exchanger->title;
                } elseif ($type === 'Payment' && $source && $source->exchanger) {
                    $exchanger = $source->exchanger->title;
                } elseif ($type === 'Transfer' && $source && $source->exchangerFrom) {
                    $exchanger = $source->exchangerFrom->title . ' → ' . ($source->exchangerTo->title ?? '');
                } elseif ($type === 'Application' && $source && $source->merchant) {
                    $exchanger = $source->merchant;
                } else {
                    $exchanger = $typeRu;
                }
            @endphp
            <div class="flex items-center justify-between px-6 py-4 bg-[#191919] hover:bg-gray-800 transition"
                 data-type="{{ $type }}"
                 data-id="{{ $source->id ?? '' }}"
                 data-exchanger="{{ $exchanger ?? '' }}"
                 data-amount="{{ $amount ?? '' }}"
                 data-currency="{{ $currency->code ?? '' }}"
                 data-date="{{ $date ?? '' }}"
                 data-type-ru="{{ $typeRu ?? '' }}"
                 @if($source)
                    data-sale-amount="{{ $source->sale_amount ?? '' }}"
                    data-sale-currency="{{ optional($source->saleCurrency)->code ?? '' }}"
                    data-fixed-amount="{{ $source->fixed_amount ?? '' }}"
                    data-fixed-currency="{{ optional($source->fixedCurrency)->code ?? '' }}"
                    data-received-amount="{{ $source->received_amount ?? '' }}"
                    data-received-currency="{{ optional($source->receivedCurrency)->code ?? '' }}"
                    data-sell-amount="{{ $source->sell_amount ?? '' }}"
                    data-sell-currency="{{ optional($source->sellCurrency)->code ?? '' }}"
                    data-comment="{{ $source->comment ?? '' }}"
                    data-commission="{{ $source->commission ?? '' }}"
                    data-commission-currency="{{ optional($source->commissionCurrency)->code ?? '' }}"
                    data-amount-transfer="{{ $source->amount ?? '' }}"
                    data-amount-currency="{{ optional($source->amountCurrency)->code ?? '' }}"
                 @endif
            >
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate">
                        {{ $exchanger }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                        <span>{{ $date }}</span>
                        @if($source)
                            <button class="show-modal-btn bg-gray-700 rounded px-2 py-0.5 text-xs ml-2 hover:bg-cyan-700 text-cyan-200" data-type="{{ $type }}" data-id="{{ $source->id ?? '' }}">{{ $typeRu }}</button>
                        @else
                            <span class="text-red-400 ml-2">нет данных для модалки</span>
                        @endif
                    </div>
                    {{-- ВРЕМЕННО: диагностика sourceable --}}
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <span class="text-lg font-bold {{ $amount > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $sign }}{{ $formatted }}</span>
                    @if($currency)
                        <img src="{{ $icon }}" alt="{{ $currency->code }}" class="w-6 h-6" onerror="this.style.display='none';this.insertAdjacentHTML('afterend', '<span>{{ $currency->code }}</span>')">
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-400">Нет операций</div>
        @endforelse
    </div>
    <div class="mt-6 flex justify-center">
        {{ $histories->links() }}
    </div>

    {{-- ВАЖНО: view-модалки подключаются только здесь, чтобы не было дублирования id на странице! --}}
    @include('modal/view-purchase')
    @include('modal/view-salecrypt')
    @include('modal/view-payment')
    @include('modal/view-transfer')
    @include('modal/view-application')

    <script>
        function trimZeros(val) {
            if (val === null || val === undefined) return '';
            let s = String(val);
            if (s.indexOf('.') !== -1) {
                s = s.replace(/(?:\.\d*?[1-9])0+$/,'$1'); // убираем конечные нули
                s = s.replace(/\.0+$/,''); // убираем .000...
                s = s.replace(/\.$/, ''); // убираем точку в конце
            }
            return s;
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.show-modal-btn').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.stopPropagation();
                    const parent = btn.closest('[data-type]');
                    const type = btn.dataset.type;
                    const id = btn.dataset.id;
                    // Ajax-запрос по типу
                    let url = null;
                    if (type === 'Purchase') url = `/purchase/${id}`;
                    else if (type === 'SaleCrypt') url = `/salecrypt/${id}`;
                    else if (type === 'Payment') url = `/payment/${id}`;
                    else if (type === 'Transfer') url = `/transfer/${id}`;
                    else if (type === 'Application') url = `/api/applications/${id}`;
                    if (!url) return;
                    try {
                        const resp = await fetch(url);
                        if (!resp.ok) throw new Error('Ошибка загрузки данных');
                        const data = await resp.json();
                        if (type === 'Purchase') {
                            document.getElementById('modalViewPurchase').classList.remove('hidden');
                            // UX: ПРИХОД+ → ПРОДАЖА−
                            let rec = document.getElementById('viewPurchaseReceived');
                            let sale = document.getElementById('viewPurchaseSale');
                            rec.innerHTML = sale.innerHTML = '';
                            rec.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-green-400 text-2xl font-bold'>+${trimZeros(data.received_amount) || ''}</span>
                                ${data.received_icon ? `<img src="${data.received_icon}" alt="${data.received_currency}" class="w-8 h-8">` : ''}
                            </div>`;

                            sale.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sale_amount) || ''}</span>
                                ${data.sale_icon ? `<img src="${data.sale_icon}" alt="${data.sale_currency}" class="w-8 h-8">` : ''}
                            </div>`;
                            document.getElementById('viewPurchaseDate').textContent = data.date || '';
                        } else if (type === 'SaleCrypt') {
                            document.getElementById('modalViewSaleCrypt').classList.remove('hidden');
                            // UX: ПРОДАЖА− → ПРИХОД+
                            let sale = document.getElementById('viewSaleCryptSale');
                            let fixed = document.getElementById('viewSaleCryptFixed');
                            sale.innerHTML = fixed.innerHTML = '';
                            sale.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sale_amount) || ''}</span>
                                ${data.sale_icon ? `<img src="${data.sale_icon}" alt="${data.sale_currency}" class="w-8 h-8">` : ''}
                            </div>`;

                            fixed.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-green-400 text-2xl font-bold'>+${trimZeros(data.fixed_amount) || ''}</span>
                                ${data.fixed_icon ? `<img src="${data.fixed_icon}" alt="${data.fixed_currency}" class="w-8 h-8">` : ''}
                            </div>`;
                            document.getElementById('viewSaleCryptDate').textContent = data.date || '';
                        } else if (type === 'Payment') {
                            document.getElementById('modalViewPayment').classList.remove('hidden');
                            let amount = document.getElementById('viewPaymentAmount');
                            amount.innerHTML = '';
                            amount.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sell_amount) || ''}</span>
                                ${data.sell_icon ? `<img src="${data.sell_icon}" alt="${data.sell_currency}" class="w-8 h-8">` : ''}
                            </div>`;

                            document.getElementById('viewPaymentDate').textContent = data.date || '';
                        } else if (type === 'Transfer') {
                            document.getElementById('modalViewTransfer').classList.remove('hidden');
                            // ОТКУДА → КУДА
                            let from = document.getElementById('viewTransferFrom');
                            let to = document.getElementById('viewTransferTo');
                            let amount = document.getElementById('viewTransferAmount');
                            let comm = document.getElementById('viewTransferCommission');
                            from.innerHTML = `<span class='text-gray-300 text-lg font-medium'>${data.from || ''}</span>`;
                            to.innerHTML = `<span class='text-gray-300 text-lg font-medium'>${data.to || ''}</span>`;

                            let amountFrom = document.getElementById('viewTransferAmountFrom');
                            let amountTo = document.getElementById('viewTransferAmountTo');
                            amountFrom.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.amount) || ''}</span>
                                ${data.amount_icon ? `<img src="${data.amount_icon}" alt="${data.amount_currency}" class="w-8 h-8">` : ''}
                            </div>`;
                            amountTo.innerHTML = `<div class="flex items-center justify-center gap-2">
                                <span class='text-green-400 text-2xl font-bold'>+${trimZeros(data.amount) || ''}</span>
                                ${data.amount_icon ? `<img src="${data.amount_icon}" alt="${data.amount_currency}" class="w-8 h-8">` : ''}
                            </div>`;

                            // Комиссия
                            comm.innerHTML = '';
                            if (data.commission) {
                                comm.innerHTML = `<div class="flex items-center justify-center gap-2">
                                    <span class='text-red-400 text-lg font-bold'>-${trimZeros(data.commission) || ''}</span>
                                    ${data.commission_icon ? `<img src="${data.commission_icon}" alt="${data.commission_currency}" class="w-6 h-6">` : ''}
                                </div>`;
                            }
                            document.getElementById('viewTransferDate').textContent = data.date || '';
                        } else if (type === 'Application') {
                            document.getElementById('modalViewApplication').classList.remove('hidden');
                            document.getElementById('viewApplicationId').textContent = `#${data.app_id}`;
                            document.getElementById('viewApplicationMerchant').textContent = data.merchant || '';
                            document.getElementById('viewApplicationStatus').textContent = data.status || '';
                            document.getElementById('viewApplicationDate').textContent = data.app_created_at || '';
                            // Современный UX: все блоки одинаково наглядно
                            let details = `<div class='grid grid-cols-2 gap-4 my-2'>`;
                            // ПРИХОД+
                            if (data.related_purchases && data.related_purchases.length) {
                                data.related_purchases.forEach(p => {
                                    details += `<div class='flex items-center gap-2 bg-[#18291a] rounded-lg px-3 py-2 w-full'>
                                        <span class='text-green-400 text-lg font-bold'>+${trimZeros(p.received_amount) || ''}</span>`;
                                    if (p.received_icon) details += `<img src="${p.received_icon}" alt="${p.received_currency}" class="w-6 h-6">`;
                                    details += `<span class='text-green-400 font-medium'>${p.received_currency || ''}</span>
                                        <span class='ml-2 text-xs text-gray-400'>ПРИХОД+</span>
                                    </div>`;
                                });
                            } else {
                                details += `<div class='col-span-2 text-center text-gray-500'>Нет прихода</div>`;
                            }
                            // ПРОДАЖА−
                            if (data.related_purchases && data.related_purchases.length) {
                                data.related_purchases.forEach(p => {
                                    details += `<div class='flex items-center gap-2 bg-[#2a1919] rounded-lg px-3 py-2 w-full'>
                                        <span class='text-red-400 text-lg font-bold'>-${trimZeros(p.sale_amount) || ''}</span>`;
                                    if (p.sale_icon) details += `<img src="${p.sale_icon}" alt="${p.sale_currency}" class="w-6 h-6">`;
                                    details += `<span class='text-red-400 font-medium'>${p.sale_currency || ''}</span>
                                        <span class='ml-2 text-xs text-gray-400'>ПРОДАЖА−</span>
                                    </div>`;
                                });
                            } else {
                                details += `<div class='col-span-2 text-center text-gray-500'>Нет продажи</div>`;
                            }
                            // КУПЛЯ+
                            if (data.buy_amount) {
                                details += `<div class='flex items-center gap-2 bg-[#18291a] rounded-lg px-3 py-2 w-full'>
                                    <span class='text-green-400 text-lg font-bold'>+${trimZeros(data.buy_amount)}</span>`;
                                if (data.buy_icon) details += `<img src="${data.buy_icon}" alt="${data.buy_currency}" class="w-6 h-6">`;
                                details += `<span class='text-green-400 font-medium'>${data.buy_currency || ''}</span>
                                    <span class='ml-2 text-xs text-gray-400'>КУПЛЯ+</span>
                                </div>`;
                            } else {
                                details += `<div class='col-span-2 text-center text-gray-500'>Нет купли</div>`;
                            }
                            // РАСХОД−
                            if (data.expense_amount) {
                                details += `<div class='flex items-center gap-2 bg-[#2a1919] rounded-lg px-3 py-2 w-full'>
                                    <span class='text-red-400 text-lg font-bold'>-${trimZeros(data.expense_amount)}</span>`;
                                if (data.expense_icon) details += `<img src="${data.expense_icon}" alt="${data.expense_currency}" class="w-6 h-6">`;
                                details += `<span class='text-red-400 font-medium'>${data.expense_currency || ''}</span>
                                    <span class='ml-2 text-xs text-gray-400'>РАСХОД−</span>
                                </div>`;
                            } else {
                                details += `<div class='col-span-2 text-center text-gray-500'>Нет расхода</div>`;
                            }
                            details += `</div>`;
                            // Списки связанных продаж
                            if (data.related_sale_crypts && data.related_sale_crypts.length) {
                                details += `<div class='mt-2'><b>Связанные продажи:</b><ul class='ml-4 list-disc'>`;
                                data.related_sale_crypts.forEach(s => {
                                    details += `<li class='flex items-center gap-2'>`;
                                    if (s.sale_icon) details += `<img src="${s.sale_icon}" alt="${s.sale_currency}" class="w-5 h-5">`;
                                    details += `<span class='text-red-400'>-${trimZeros(s.sale_amount) || ''} ${s.sale_currency || ''}</span>`;
                                    details += `<span class='mx-1 text-gray-400'>→</span>`;
                                    if (s.fixed_icon) details += `<img src="${s.fixed_icon}" alt="${s.fixed_currency}" class="w-5 h-5">`;
                                    details += `<span class='text-green-400'>+${trimZeros(s.fixed_amount) || ''} ${s.fixed_currency || ''}</span>`;
                                    details += `</li>`;
                                });
                                details += `</ul></div>`;
                            }
                            // Основная информация
                            let merchant = document.getElementById('viewApplicationMerchant');
                            if (merchant) merchant.innerHTML = (data.merchant || '') + details;
                        }
                    } catch (err) {
                        alert('Ошибка загрузки данных для модального окна');
                    }
                });
            });
            // Закрытие всех view-модалок
            [
                ['modalViewPurchase', 'closeViewPurchase'],
                ['modalViewSaleCrypt', 'closeViewSaleCrypt'],
                ['modalViewPayment', 'closeViewPayment'],
                ['modalViewTransfer', 'closeViewTransfer'],
                ['modalViewApplication', 'closeViewApplication'],
            ].forEach(([modalId, btnId]) => {
                const modal = document.getElementById(modalId);
                const btn = document.getElementById(btnId);
                if (modal && btn) {
                    btn.addEventListener('click', () => modal.classList.add('hidden'));
                    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
                }
            });
        });
    </script>
@endsection
