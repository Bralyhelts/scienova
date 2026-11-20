document.addEventListener('click', function(event) {

    const button = event.target.closest('[data-loading]');

    if (!button) return;

    const form = button.closest('form');

    if (!form) return;

    if (!form.reportValidity()) return;

    event.preventDefault();

    button.disabled = true;

    button.textContent =
        button.dataset.loading || 'Please wait...';

    setTimeout(function() {

        form.submit();

    }, 5000);

});
