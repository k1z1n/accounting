// === TRANSFER ===
const editTransferModal = document.getElementById('modalEditTransferBackdrop');
const deleteTransferModal = document.getElementById('modalDeleteTransferBackdrop');

document.querySelectorAll('.edit-transfer-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id   = btn.dataset.id;
        document.getElementById('edit_transfer_id').value = id;
        document.getElementById('transferModalId').textContent = `#${id}`;

        document.getElementById('edit_transfer_from').value                 = btn.dataset.fromId;
        document.getElementById('edit_transfer_to').value                   = btn.dataset.toId;
        document.getElementById('edit_transfer_amount').value               = btn.dataset.amount;
        document.getElementById('edit_transfer_amount_currency').value      = btn.dataset.amountCurrency;
        document.getElementById('edit_transfer_commission').value           = btn.dataset.commission;
        document.getElementById('edit_transfer_commission_currency').value  = btn.dataset.commissionCurrency;

        editTransferModal.classList.remove('hidden');
    });
});

document.getElementById('cancelEditTransfer')?.addEventListener('click', () => {
    editTransferModal.classList.add('hidden');
});
document.getElementById('modalEditTransferClose')?.addEventListener('click', () => {
    editTransferModal.classList.add('hidden');
});

document.getElementById('editTransferForm').addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('edit_transfer_id').value;
    const data = {
        exchanger_from:          document.getElementById('edit_transfer_from').value,
        exchanger_to:            document.getElementById('edit_transfer_to').value,
        amount:                  document.getElementById('edit_transfer_amount').value,
        amount_currency_id:      document.getElementById('edit_transfer_amount_currency').value,
        commission:              document.getElementById('edit_transfer_commission').value,
        commission_currency_id:  document.getElementById('edit_transfer_commission_currency').value,
    };
    fetch(`/transfers/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(() => window.location.reload())
        .catch(() => alert('Не удалось обновить перевод'));
});

document.querySelectorAll('.delete-transfer-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('deleteTransferId').textContent = `#${btn.dataset.id}`;
        document.getElementById('confirmDeleteTransfer').dataset.id = btn.dataset.id;
        deleteTransferModal.classList.remove('hidden');
    });
});
document.getElementById('cancelDeleteTransfer')?.addEventListener('click', () => {
    deleteTransferModal.classList.add('hidden');
});
document.getElementById('modalDeleteTransferClose')?.addEventListener('click', () => {
    deleteTransferModal.classList.add('hidden');
});
document.getElementById('confirmDeleteTransfer').addEventListener('click', () => {
    const id = document.getElementById('confirmDeleteTransfer').dataset.id;
    fetch(`/transfers/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(r => r.json())
        .then(() => window.location.reload())
        .catch(() => alert('Не удалось удалить перевод'));
});
