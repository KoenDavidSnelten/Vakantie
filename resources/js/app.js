import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Voorkomt dubbel indienen: na een submit gaat de knop op slot en toont hij
 * "Bezig…". Het uitschakelen gebeurt bewust ná de submit (setTimeout 0), anders
 * stuurt de browser de waarde van de knop zelf niet mee.
 *
 * Formulieren die hun submit zelf afvangen (@submit.prevent) slaan we over, en
 * na een terugnavigatie uit de bfcache zetten we alles weer aan.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented || form.dataset.noSubmitLock !== undefined) {
        return;
    }

    setTimeout(() => {
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((button) => {
            if (button.disabled) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('opacity-60', 'cursor-wait');

            if (button.tagName === 'BUTTON' && button.dataset.busyLabel !== undefined) {
                button.dataset.originalLabel = button.innerHTML;
                button.innerHTML = button.dataset.busyLabel || 'Bezig&hellip;';
            }
        });
    }, 0);
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[aria-busy="true"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.classList.remove('opacity-60', 'cursor-wait');

        if (button.dataset.originalLabel !== undefined) {
            button.innerHTML = button.dataset.originalLabel;
            delete button.dataset.originalLabel;
        }
    });
});
