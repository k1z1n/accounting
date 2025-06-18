// === SALECRYPT ===
const editSaleCryptModal = document.getElementById('modalEditSaleCryptBackdrop');
const deleteSaleCryptModal = document.getElementById('modalDeleteSaleCryptBackdrop');

document.querySelectorAll('.edit-salecrypt-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('edit_salecrypt_id').value = id;
        document.getElementById('saleCryptModalId').textContent = `#${id}`;
        document.getElementById('edit_salecrypt_exchanger').value = btn.dataset.exchangerId;
        document.getElementById('edit_salecrypt_sale_amount').value = btn.dataset.saleAmount;
        document.getElementById('edit_salecrypt_sale_currency').value = btn.dataset.saleCurrencyId;
        document.getElementById('edit_salecrypt_fixed_amount').value = btn.dataset.fixedAmount;
        document.getElementById('edit_salecrypt_fixed_currency').value = btn.dataset.fixedCurrencyId;

        editSaleCryptModal.classList.remove('hidden');
    });
});

document.getElementById('cancelEditSaleCrypt').addEventListener('click', () => {
    editSaleCryptModal.classList.add('hidden');
});
document.getElementById('modalEditSaleCryptClose').addEventListener('click', () => {
    editSaleCryptModal.classList.add('hidden');
});

document.getElementById('editSaleCryptForm').addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('edit_salecrypt_id').value;
    const data = {
        exchanger_id: document.getElementById('edit_salecrypt_exchanger').value,
        sale_amount: document.getElementById('edit_salecrypt_sale_amount').value,
        sale_currency_id: document.getElementById('edit_salecrypt_sale_currency').value,
        fixed_amount: document.getElementById('edit_salecrypt_fixed_amount').value,
        fixed_currency_id: document.getElementById('edit_salecrypt_fixed_currency').value,
    };
    fetch(`/sale-crypts/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
        .then(r => r.json()).then(() => location.reload())
        .catch(() => alert('Не удалось сохранить'));
});

document.querySelectorAll('.delete-salecrypt-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('deleteSaleCryptId').textContent = `#${btn.dataset.id}`;
        document.getElementById('confirmDeleteSaleCrypt').dataset.id = btn.dataset.id;
        deleteSaleCryptModal.classList.remove('hidden');
    });
});

document.getElementById('cancelDeleteSaleCrypt').addEventListener('click', () => {
    deleteSaleCryptModal.classList.add('hidden');
});
document.getElementById('modalDeleteSaleCryptClose').addEventListener('click', () => {
    deleteSaleCryptModal.classList.add('hidden');
});

document.getElementById('confirmDeleteSaleCrypt').addEventListener('click', () => {
    const id = document.getElementById('confirmDeleteSaleCrypt').dataset.id;
    fetch(`/sale-crypts/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(r => r.json()).then(() => location.reload())
        .catch(() => alert('Не удалось удалить'));
});
