{{-- resources/views/pages/wallets/history.blade.php --}}
@extends('template.app')
@section('title','Вся история транзакций')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-6">

        {{-- Фильтры --}}
        <div class="bg-[#191919] p-6 rounded-2xl flex flex-wrap gap-4 text-white">
            <div>
                <label class="block text-gray-400 text-sm mb-1">Провайдер</label>
                <select id="fltProvider" class="bg-gray-800 rounded-lg px-3 py-2">
                    @foreach($providers as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-400 text-sm mb-1">Обменник</label>
                <select id="fltExchanger" class="bg-gray-800 rounded-lg px-3 py-2">
                    @foreach($exchangers as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Таблица + спиннер --}}
        <div class="relative bg-[#191919] rounded-2xl overflow-x-auto">
            <div id="spinner" class="absolute inset-0 flex items-center justify-center bg-black/50 hidden z-10">
                <svg class="animate-spin h-9 w-9 text-cyan-500" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-20" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-80" fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
            </div>

            <table class="min-w-full text-white">
                <thead class="bg-[#1F1F1F]">
                <tr>
                    <th class="px-4 py-2 text-left">Дата</th>
                    <th class="px-4 py-2 text-left">Тип</th>
                    <th class="px-4 py-2 text-right">Сумма</th>
                    <th class="px-4 py-2 text-left">Валюта</th>
                </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-[#2d2d2d]">
                <tr><td colspan="4" class="py-6 text-center text-gray-500">Загрузка…</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div id="pager" class="flex justify-center gap-2"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tbody  = document.getElementById('tbody');
            const pager  = document.getElementById('pager');
            const spin   = document.getElementById('spinner');
            const prov   = document.getElementById('fltProvider');
            const exch   = document.getElementById('fltExchanger');

            const fmt = d => new Date(d).toLocaleString('ru-RU',{
                day:'2-digit',month:'2-digit',year:'numeric', hour:'2-digit',minute:'2-digit'
            });

            const show = () => spin.classList.remove('hidden');
            const hide = () => spin.classList.add('hidden');

            async function load(page = 1){
                show();
                const url = `{{ route('wallets.history.data') }}`
                    + `?provider=${prov.value}&exchanger=${exch.value}&page=${page}`;
                try{
                    const res  = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
                    const json = await res.json();
                    paintTable(json.data);
                    paintPager(json.meta);
                }catch(e){
                    console.error(e);
                    tbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-red-600">Ошибка</td></tr>`;
                    pager.innerHTML = '';
                }finally{ hide(); }
            }

            function paintTable(rows){
                if(!rows.length){
                    tbody.innerHTML =
                        `<tr><td colspan="4" class="py-6 text-center text-gray-500">Нет записей</td></tr>`;
                    return;
                }
                tbody.innerHTML = rows.map(tx=>`
            <tr class="hover:bg-gray-800">
              <td class="px-4 py-3">${fmt(tx.date)}</td>
              <td class="px-4 py-3">${tx.type}</td>
              <td class="px-4 py-3 text-right">
                 <span class="${tx.amount>0?'text-green-400':'text-red-400'}">
                   ${tx.amount>0?'+':''}${Math.abs(tx.amount).toFixed(4)}
                 </span>
              </td>
              <td class="px-4 py-3">${tx.currency}</td>
            </tr>`).join('');
            }

            function paintPager({page,last}){
                if(last<=1){ pager.innerHTML=''; return; }
                let h='';
                const btn=(p,l,act)=>h+=`<button data-p="${p}" class="px-3 py-1 rounded-md
                     ${act?'bg-cyan-600':
                    'bg-gray-700 hover:bg-gray-600'}">${l}</button>`;
                if(page>1) btn(page-1,'←',false);
                for(let i=Math.max(1,page-2); i<=Math.min(last,page+2); i++) btn(i,i,page===i);
                if(page<last) btn(page+1,'→',false);
                pager.innerHTML=h;
                pager.querySelectorAll('button').forEach(b=>b.onclick=()=>load(b.dataset.p));
            }

            prov.onchange = ()=>load(1);
            exch.onchange = ()=>load(1);

            load();        // старт
        });
    </script>
@endsection
