<div x-data="appModal()" x-cloak>
    {{-- Кнопка открытия --}}
    <div class="flex justify-end mb-4">
        <button
            @click="open = true"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >Добавить заявку
        </button>
    </div>

    {{-- Модалка --}}
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black opacity-50" @click="open=false"></div>
            <div
                class="bg-white rounded-lg shadow-lg p-6 relative z-10 w-full max-w-2xl"
                @keydown.escape.window="open=false"
            >
                <h3 class="text-xl font-semibold mb-4">Новая заявка</h3>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Поля: app_created_at, app_status, app_id, app_meta_give0, app_currency_give, app_sum1dc, app_sum1c … -->
                        <template x-for="(label, field) in [
                            ['Дата создания','app_created_at'],
                            ['Статус','app_status'],
                            ['Номер заявки','app_id'],
                            ['Обменник','app_meta_give0'],
                            ['Валюта прихода','app_currency_give'],
                            ['Приход (сумма)','app_sum1dc'],
                            ['Приход Крипта','app_sum1c'],
                            ['Валюта расхода','app_currency_get'],
                            ['Расход (сумма)','app_sum2dc'],
                            ['Расход Крипта','app_sum2c'],
                            ['Сотрудник','app_first_name'],
                            ['TXID входящий','app_txid_in'],
                        ]" :key="field">
                            <div>
                                <label class="block text-sm font-medium" x-text="label"></label>
                                <input
                                    :type="field==='app_created_at' ? 'datetime-local' :
                                           field==='app_id' ? 'number' : 'text'"
                                    x-model="form[field]"
                                    @input="validateField(field)"
                                    class="mt-1 block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p class="text-red-600 text-sm mt-1" x-text="errors[field]"></p>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="open=false"
                                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Отмена
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
<script>
    function appModal() {
        return {
            open: false,
            form: {
                app_created_at: '', app_status: '', app_id: '',
                app_meta_give0: '', app_currency_give: '',
                app_sum1dc: '', app_sum1c: '',
                app_currency_get: '', app_sum2dc: '',
                app_sum2c: '', app_first_name: '',
                app_txid_in: '',
            },
            errors: {},
            validateField(field) {
                this.errors[field] = '';
                if (!this.form[field]) {
                    this.errors[field] = 'Обязательное поле';
                    return;
                }
                if (['app_id', 'app_sum1dc', 'app_sum1c', 'app_sum2dc', 'app_sum2c'].includes(field)) {
                    if (!/^[\d.]+$/.test(this.form[field])) {
                        this.errors[field] = 'Неверный формат числа';
                    }
                }
            },
            validateAll() {
                Object.keys(this.form).forEach(f => this.validateField(f));
                return !Object.values(this.errors).some(e => e);
            },
            submit() {
                console.log('Attempt submit', this.form)
                if (!this.validateAll()) return;
                fetch('{{ route('applications.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                })
                    .then(res => {
                        console.log('Response:', res.status, res);
                        return res.json().then(json => ({ status: res.status, json }));
                    })
                    .then(({ status, json }) => {
                        if (status === 201) {
                            document.getElementById('appsTbody')
                                .insertAdjacentHTML('afterbegin', this.TPL_ROW(json));
                            this.reset(); this.open = false;
                        } else if (status === 422) {
                            this.errors = json.errors || {};
                        } else {
                            console.error('Unexpected response:', json);
                        }
                    })
                    .catch(err => console.error('Fetch error:', err));
            },
            reset() {
                Object.keys(this.form).forEach(f => this.form[f] = '');
                this.errors = {};
            },
            TPL_ROW(d) {
                // те же колонки, что в Blade
                return `
<tr class="hover:bg-gray-50">
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_created_at}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_status}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_first_name}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_id}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_currency_give}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_sum1dc}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_sum1c}</td>
</tr>`;
            }
        }
    }
</script>
