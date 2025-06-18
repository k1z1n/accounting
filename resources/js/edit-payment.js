// === PAYMENT ===
const editPaymentModal = document.getElementById('modalEditPaymentBackdrop');
const deletePaymentModal = document.getElementById('modalDeletePaymentBackdrop');

document.querySelectorAll('.edit-payment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('edit_payment_id').value = id;
        document.getElementById('paymentModalId').textContent = `#${id}`;

        document.getElementById('edit_payment_exchanger').value = btn.dataset.exchangerId;
        document.getElementById('edit_payment_amount').value = btn.dataset.sellAmount;
        document.getElementById('edit_payment_currency').value = btn.dataset.sellCurrency;
        document.getElementById('edit_payment_comment').value = btn.dataset.comment;

        editPaymentModal.classList.remove('hidden');
    });
});

document.getElementById('cancelEditPayment')?.addEventListener('click', () => {
    editPaymentModal.classList.add('hidden');
});
document.getElementById('modalEditPaymentClose')?.addEventListener('click', () => {
    editPaymentModal.classList.add('hidden');
});

document.getElementById('editPaymentForm').addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('edit_payment_id').value;
    const data = {
        exchanger_id: document.getElementById('edit_payment_exchanger').value,
        sell_amount: document.getElementById('edit_payment_amount').value,
        sell_currency_id: document.getElementById('edit_payment_currency').value,
        comment: document.getElementById('edit_payment_comment').value,
    };
    fetch(`/payments/${id}`, {
        method: 'PUT', headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }, body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(() => window.location.reload())
        .catch(() => alert('Не удалось обновить оплату'));
});

document.querySelectorAll('.delete-payment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('deletePaymentId').textContent = `#${btn.dataset.id}`;
        document.getElementById('confirmDeletePayment').dataset.id = btn.dataset.id;
        deletePaymentModal.classList.remove('hidden');
    });
});
document.getElementById('cancelDeletePayment')?.addEventListener('click', () => {
    deletePaymentModal.classList.add('hidden');
});
document.getElementById('modalDeletePaymentClose')?.addEventListener('click', () => {
    deletePaymentModal.classList.add('hidden');
});
document.getElementById('confirmDeletePayment').addEventListener('click', () => {
    const id = document.getElementById('confirmDeletePayment').dataset.id;
    fetch(`/payments/${id}`, {
        method: 'DELETE', headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(r => r.json())
        .then(() => window.location.reload())
        .catch(() => alert('Не удалось удалить оплату'));
});
