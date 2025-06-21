function wireModal(openBtns, modal, closeSelectors) {
    openBtns.forEach(btn => btn.addEventListener('click', () => modal.classList.remove('hidden')));
    closeSelectors.forEach(sel => sel?.addEventListener('click', () => modal.classList.add('hidden')));
}

// 1) TRANSFERS
(function () {
    const modalEdit = document.getElementById('modalEditTransferBackdrop');
    const modalDelete = document.getElementById('modalDeleteTransferBackdrop');

    document.querySelectorAll('.edit-transfer-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit_transfer_id').value = d.id;
            document.getElementById('transferModalId').textContent = `#${d.id}`;
            document.getElementById('edit_transfer_from').value = d.fromId;
            document.getElementById('edit_transfer_to').value = d.toId;
            document.getElementById('edit_transfer_amount').value = d.amount;
            document.getElementById('edit_transfer_amount_currency').value = d.amountCurrencyId;
            document.getElementById('edit_transfer_commission').value = d.commission;
            document.getElementById('edit_transfer_commission_currency').value = d.commissionCurrencyId;
            modalEdit.classList.remove('hidden');
        });
    });

    // закрытие
    ['cancelEditTransfer', 'modalEditTransferClose'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => modalEdit.classList.add('hidden'));
    });

    // submit
    document.getElementById('editTransferForm')
        .addEventListener('submit', e => {
            e.preventDefault();
            const id = document.getElementById('edit_transfer_id').value;
            const payload = {
                exchanger_from_id: document.getElementById('edit_transfer_from').value,
                exchanger_to_id: document.getElementById('edit_transfer_to').value,
                amount: document.getElementById('edit_transfer_amount').value,
                amount_currency_id: document.getElementById('edit_transfer_amount_currency').value,
                commission: document.getElementById('edit_transfer_commission').value,
                commission_currency_id: document.getElementById('edit_transfer_commission_currency').value,
            };
            fetch(`/admin/transfers/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(() => location.reload());
        });

    // удалить
    document.querySelectorAll('.delete-transfer-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('deleteTransferId').textContent = `#${btn.dataset.id}`;
            document.getElementById('confirmDeleteTransfer').dataset.id = btn.dataset.id;
            modalDelete.classList.remove('hidden');
        });
    });
    ['cancelDeleteTransfer', 'modalDeleteTransferClose'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => modalDelete.classList.add('hidden'));
    });
    document.getElementById('confirmDeleteTransfer')
        .addEventListener('click', () => {
            const id = document.getElementById('confirmDeleteTransfer').dataset.id;
            fetch(`/admin/transfers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => r.json()).then(() => location.reload());
        });
})();


// 2) PAYMENTS
(() => {
    const modalEdit = document.getElementById('modalEditPaymentBackdrop');
    const modalDelete = document.getElementById('modalDeletePaymentBackdrop');

    document.querySelectorAll('.edit-payment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit_payment_id').value = d.id;
            document.getElementById('paymentModalId').textContent = `#${d.id}`;
            document.getElementById('edit_payment_exchanger').value = d.exchangerId;
            document.getElementById('edit_payment_amount').value = d.sellAmount;
            document.getElementById('edit_payment_currency').value = d.sellCurrencyId;
            document.getElementById('edit_payment_comment').value = d.comment;
            modalEdit.classList.remove('hidden');
        });
    });

    wireModal([], modalEdit, [document.getElementById('cancelEditPayment'), document.getElementById('modalEditPaymentClose')]);

    document.getElementById('editPaymentForm').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('edit_payment_id').value;
        const body = {
            exchanger_id: document.getElementById('edit_payment_exchanger').value,
            sell_amount: document.getElementById('edit_payment_amount').value,
            sell_currency_id: document.getElementById('edit_payment_currency').value,
            comment: document.getElementById('edit_payment_comment').value,
        };
        fetch(`/admin/payments/${id}`, {
            method: 'PUT', headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }, body: JSON.stringify(body)
        }).then(r => r.json()).then(() => location.reload());
    });

    document.querySelectorAll('.delete-payment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('deletePaymentId').textContent = `#${btn.dataset.id}`;
            document.getElementById('confirmDeletePayment').dataset.id = btn.dataset.id;
            modalDelete.classList.remove('hidden');
        });
    });

    wireModal([], modalDelete, [document.getElementById('cancelDeletePayment'), document.getElementById('modalDeletePaymentClose')]);

    document.getElementById('confirmDeletePayment').addEventListener('click', () => {
        const id = document.getElementById('confirmDeletePayment').dataset.id;
        fetch(`/admin/payments/${id}`, {
            method: 'DELETE', headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(() => location.reload());
    });
})();


// 3) PURCHASES
(() => {
    const modalEdit = document.getElementById('modalEditPurchaseBackdrop');
    const modalDelete = document.getElementById('modalDeletePurchaseBackdrop');

    document.querySelectorAll('.edit-purchase-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit_purchase_id').value = d.id;
            document.getElementById('purchaseModalId').textContent = `#${d.id}`;
            document.getElementById('edit_purchase_exchanger').value = d.exchangerId;
            document.getElementById('edit_received_amount').value = d.receivedAmount;
            document.getElementById('edit_received_currency').value = d.receivedCurrencyId;
            document.getElementById('edit_sale_amount').value = d.saleAmount;
            document.getElementById('edit_sale_currency').value = d.saleCurrencyId;
            modalEdit.classList.remove('hidden');
        });
    });

    wireModal([], modalEdit, [document.getElementById('cancelEditPurchase'), document.getElementById('modalEditPurchaseClose')]);

    document.getElementById('editPurchaseForm').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('edit_purchase_id').value;
        const body = {
            exchanger_id: document.getElementById('edit_purchase_exchanger').value,
            received_amount: document.getElementById('edit_received_amount').value,
            received_currency_id: document.getElementById('edit_received_currency').value,
            sale_amount: document.getElementById('edit_sale_amount').value,
            sale_currency_id: document.getElementById('edit_sale_currency').value,
        };
        fetch(`/admin/purchases/${id}`, {
            method: 'PUT', headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }, body: JSON.stringify(body)
        }).then(r => r.json()).then(() => location.reload());
    });

    document.querySelectorAll('.delete-purchase-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('deletePurchaseId').textContent = `#${btn.dataset.id}`;
            document.getElementById('confirmDeletePurchase').dataset.id = btn.dataset.id;
            modalDelete.classList.remove('hidden');
        });
    });

    wireModal([], modalDelete, [document.getElementById('cancelDeletePurchase'), document.getElementById('modalDeletePurchaseClose')]);

    document.getElementById('confirmDeletePurchase').addEventListener('click', () => {
        const id = document.getElementById('confirmDeletePurchase').dataset.id;
        fetch(`/admin/purchases/${id}`, {
            method: 'DELETE', headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(() => location.reload());
    });
})();


// 4) SALECRYPTS
(() => {
    const modalEdit = document.getElementById('modalEditSaleCryptBackdrop');
    const modalDelete = document.getElementById('modalDeleteSaleCryptBackdrop');

    document.querySelectorAll('.edit-salecrypt-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit_salecrypt_id').value = d.id;
            document.getElementById('saleCryptModalId').textContent = `#${d.id}`;
            document.getElementById('edit_salecrypt_exchanger').value = d.exchangerId;
            document.getElementById('edit_salecrypt_sale_amount').value = d.saleAmount;
            document.getElementById('edit_salecrypt_sale_currency').value = d.saleCurrencyId;
            document.getElementById('edit_salecrypt_fixed_amount').value = d.fixedAmount;
            document.getElementById('edit_salecrypt_fixed_currency').value = d.fixedCurrencyId;
            modalEdit.classList.remove('hidden');
        });
    });

    wireModal([], modalEdit, [document.getElementById('cancelEditSaleCrypt'), document.getElementById('modalEditSaleCryptClose')]);

    document.getElementById('editSaleCryptForm').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('edit_salecrypt_id').value;
        const body = {
            exchanger_id: document.getElementById('edit_salecrypt_exchanger').value,
            sale_amount: document.getElementById('edit_salecrypt_sale_amount').value,
            sale_currency_id: document.getElementById('edit_salecrypt_sale_currency').value,
            fixed_amount: document.getElementById('edit_salecrypt_fixed_amount').value,
            fixed_currency_id: document.getElementById('edit_salecrypt_fixed_currency').value,
        };
        fetch(`/admin/sale-crypts/${id}`, {
            method: 'PUT', headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }, body: JSON.stringify(body)
        }).then(r => r.json()).then(() => location.reload());
    });

    document.querySelectorAll('.delete-salecrypt-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('deleteSaleCryptId').textContent = `#${btn.dataset.id}`;
            document.getElementById('confirmDeleteSaleCrypt').dataset.id = btn.dataset.id;
            modalDelete.classList.remove('hidden');
        });
    });

    wireModal([], modalDelete, [document.getElementById('cancelDeleteSaleCrypt'), document.getElementById('modalDeleteSaleCryptClose')]);

    document.getElementById('confirmDeleteSaleCrypt').addEventListener('click', () => {
        const id = document.getElementById('confirmDeleteSaleCrypt').dataset.id;
        fetch(`/admin/sale-crypts/${id}`, {
            method: 'DELETE', headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(() => location.reload());
    });


    /**
     * Убирает все лишние нули в конце дробной части.
     * Примеры:
     *  "123.00000000"  → "123"
     *  "123.00004000"  → "123.00004"
     *  "42.500000"     → "42.5"
     */
    function stripZeros(value) {
        const s = String(value);
        if (!s.includes('.')) return s;
        return s.replace(/\.?0+$/, '');
    }

    // Хелпер для вставки иконки валюты по коду
    function getCurrencyImg(code, size = 'w-6 h-6') {
        const url = '/images/coins/' + code + '.svg';
        if (!url) {
            // если иконки нет — показываем текстовый код
            return `<span class="${size} inline-block align-text-bottom text-xs mr-1">${code}</span>`;
        }
        return `<img src="${url}" alt="${code}" class="${size} inline-block align-text-bottom mr-1">`;
    }

// Показать модалку
    function showDetailsModal() {
        document.getElementById('modalAppDetailsBackdrop').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

// Скрыть модалку
    function hideDetailsModal() {
        document.getElementById('modalAppDetailsBackdrop').classList.add('hidden');
        document.body.style.overflow = '';
    }

// Кнопки закрытия
    document.getElementById('btnCloseAppDetails').addEventListener('click', hideDetailsModal);
    document.getElementById('modalAppDetailsClose').addEventListener('click', hideDetailsModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') hideDetailsModal();
    });

// Вешаем на каждую кнопку .details-btn
    document.querySelectorAll('.details-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const appId = btn.dataset.id;
            fetch(`/api/applications/${appId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(data => {
                    // Основные поля
                    document.getElementById('detailsAppId').textContent     = `#${data.app_id}`;
                    document.getElementById('detailsAppNumber').textContent = data.app_id;
                    document.getElementById('detailsCreatedAt').textContent = data.app_created_at;
                    document.getElementById('detailsExchanger').textContent = data.exchanger;
                    document.getElementById('detailsStatus').textContent    = data.status;
                    document.getElementById('detailsMerchant').textContent  = data.merchant || '—';

                    // Приход+
                    const incEl = document.getElementById('detailsIncoming');
                    if (data.incoming_amount != null && data.incoming_currency_code) {
                        incEl.innerHTML =
                            getCurrencyImg(data.incoming_currency_code) +
                            stripZeros(data.incoming_amount);
                    } else {
                        incEl.textContent = '—';
                    }

                    // Продажа−
                    const sellEl = document.getElementById('detailsSell');
                    if (data.sell_amount != null && data.sell_currency_code) {
                        sellEl.innerHTML =
                            getCurrencyImg(data.sell_currency_code) +
                            stripZeros(data.sell_amount);
                    } else {
                        sellEl.textContent = '—';
                    }

                    // Купля+
                    const buyEl = document.getElementById('detailsBuy');
                    if (data.buy_amount != null && data.buy_currency_code) {
                        buyEl.innerHTML =
                            getCurrencyImg(data.buy_currency_code) +
                            stripZeros(data.buy_amount);
                    } else {
                        buyEl.textContent = '—';
                    }

                    // Расход−
                    const expEl = document.getElementById('detailsExpense');
                    if (data.expense_amount != null && data.expense_currency_code) {
                        expEl.innerHTML =
                            getCurrencyImg(data.expense_currency_code) +
                            stripZeros(data.expense_amount);
                    } else {
                        expEl.textContent = '—';
                    }

                    // Списки покупок и продаж крипты
                    const ulBuy  = document.getElementById('detailsPurchaseList');
                    const ulSell = document.getElementById('detailsSaleList');
                    ulBuy.innerHTML  = '';
                    ulSell.innerHTML = '';

                    (data.related_purchases || []).forEach(p => {
                        ulBuy.insertAdjacentHTML('beforeend', `
          <li class="flex items-center space-x-2">
            <span class="flex items-center">
              ${getCurrencyImg(p.received_currency_code)}
              <span>${stripZeros(p.received_amount)}</span>
            </span>
            <span>←</span>
            <span class="flex items-center">
              ${getCurrencyImg(p.sale_currency_code)}
              <span>${stripZeros(p.sale_amount)}</span>
            </span>
          </li>
        `);
                    });

                    (data.related_sale_crypts || []).forEach(s => {
                        ulSell.insertAdjacentHTML('beforeend', `
          <li class="flex items-center space-x-2">
            <span class="flex items-center">
              ${getCurrencyImg(s.fixed_currency_code)}
              <span>${stripZeros(s.fixed_amount)}</span>
            </span>
            <span>←</span>
            <span class="flex items-center">
              ${getCurrencyImg(s.sale_currency_code)}
              <span>${stripZeros(s.sale_amount)}</span>
            </span>
          </li>
        `);
                    });

                    showDetailsModal();
                })
                .catch(err => {
                    console.error('Ошибка при загрузке деталей заявки:', err);
                    alert('Не удалось получить детали заявки');
                });
        });
    });
})();
