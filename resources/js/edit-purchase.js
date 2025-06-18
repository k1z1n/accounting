const editModal = document.getElementById('modalEditPurchaseBackdrop');
const deleteModal = document.getElementById('modalDeletePurchaseBackdrop');

// Закрытие
document.getElementById('modalEditPurchaseClose')
    ?.addEventListener('click', () => editModal.classList.add('hidden'));
document.getElementById('cancelEditPurchase')
    ?.addEventListener('click', () => editModal.classList.add('hidden'));

document.getElementById('modalDeletePurchaseClose')
    ?.addEventListener('click', () => deleteModal.classList.add('hidden'));
document.getElementById('cancelDeletePurchase')
    ?.addEventListener('click', () => deleteModal.classList.add('hidden'));

// Открытие редактирования
document.querySelectorAll('.edit-purchase-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const exchangerId = btn.dataset.exchangerId;
        const receivedAmount = btn.dataset.receivedAmount;
        const receivedCurrId = btn.dataset.receivedCurrencyId;
        const saleAmount = btn.dataset.saleAmount;
        const saleCurrId = btn.dataset.saleCurrencyId;

        document.getElementById('edit_purchase_id').value = id;
        document.getElementById('purchaseModalId').textContent = `#${id}`;
        document.getElementById('edit_purchase_exchanger').value = exchangerId;
        document.getElementById('edit_received_amount').value = receivedAmount;
        document.getElementById('edit_received_currency').value = receivedCurrId;
        document.getElementById('edit_sale_amount').value = saleAmount;
        document.getElementById('edit_sale_currency').value = saleCurrId;

        editModal.classList.remove('hidden');
    });
});

// Открытие удаления
document.querySelectorAll('.delete-purchase-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('deletePurchaseId').textContent = `#${id}`;
        document.getElementById('confirmDeletePurchase').dataset.id = id;
        deleteModal.classList.remove('hidden');
    });
});

// Сохранить изменения
document.getElementById('editPurchaseForm').addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('edit_purchase_id').value;
    const data = {
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
        }, body: JSON.stringify(data)
    })
        .then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        })
        .then(() => location.reload())
        .catch(() => alert('Не удалось сохранить изменения'));
});

// Удалить
document.getElementById('confirmDeletePurchase').addEventListener('click', () => {
    const id = document.getElementById('confirmDeletePurchase').dataset.id;
    fetch(`/admin/purchases/${id}`, {
        method: 'DELETE', headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        })
        .then(() => location.reload())
        .catch(() => alert('Не удалось удалить запись'));
});
