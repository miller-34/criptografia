document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Mostrar' : 'Ocultar';
    });
});

document.querySelectorAll('[data-copy], [data-copy-input]').forEach((button) => {
    button.addEventListener('click', async () => {
        const targetId = button.dataset.copy || button.dataset.copyInput;
        const target = document.getElementById(targetId);
        const value = button.dataset.copyInput ? target?.value : target?.textContent;
        if (!value) return;

        await navigator.clipboard.writeText(value);
        const originalLabel = button.textContent;
        button.textContent = 'Copiado!';
        window.setTimeout(() => { button.textContent = originalLabel; }, 1400);
    });
});

document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Excluir esta credencial permanentemente?')) {
            event.preventDefault();
        }
    });
});
