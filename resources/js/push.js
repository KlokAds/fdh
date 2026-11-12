/*
 * Admin inquiry alerts via the browser's free Web Push service.
 * Bound to the [data-push] card on the Notifications page.
 */
function urlBase64ToUint8Array(base64) {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const raw = atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function post(url, body) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    if (!response.ok) {
        throw new Error(`Request failed (${response.status})`);
    }
}

export function bindPushCard() {
    const card = document.querySelector('[data-push]');
    if (!card || card.dataset.bound) {
        return;
    }
    card.dataset.bound = '1';

    const status = card.querySelector('[data-push-status]');
    const enableBtn = card.querySelector('[data-push-enable]');
    const disableBtn = card.querySelector('[data-push-disable]');
    if (!status) {
        return;
    }

    const setStatus = (text, tone) => {
        status.textContent = text;
        status.className = `badge ${tone === 'ok' ? 'badge-ok' : 'badge-warn'}`;
    };

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        setStatus(window.isSecureContext ? 'This browser does not support notifications. On iPhone, add the panel to your Home Screen first.' : 'Notifications need HTTPS (they will work on the live site).', 'warn');
        return;
    }

    const registrationPromise = navigator.serviceWorker.register(card.dataset.worker);

    const refresh = async () => {
        const registration = await registrationPromise;
        const subscription = await registration.pushManager.getSubscription();
        if (Notification.permission === 'denied') {
            setStatus('Blocked in browser settings — allow notifications for this site, then reload.', 'warn');
            enableBtn.hidden = true;
            disableBtn.hidden = true;
        } else if (subscription) {
            setStatus('On — this device will get inquiry alerts', 'ok');
            enableBtn.hidden = true;
            disableBtn.hidden = false;
        } else {
            setStatus('Off on this device', 'warn');
            enableBtn.hidden = false;
            disableBtn.hidden = true;
        }
    };

    enableBtn.addEventListener('click', async () => {
        enableBtn.disabled = true;
        try {
            if ((await Notification.requestPermission()) !== 'granted') {
                await refresh();
                return;
            }
            const registration = await registrationPromise;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(card.dataset.publicKey),
            });
            const json = subscription.toJSON();
            await post(card.dataset.subscribe, {
                endpoint: json.endpoint,
                keys: json.keys,
                contentEncoding: (PushManager.supportedContentEncodings || ['aes128gcm'])[0],
            });
            window.Livewire?.dispatch('push-changed');
        } catch (error) {
            setStatus(`Could not turn on notifications: ${error.message}`, 'warn');
        } finally {
            enableBtn.disabled = false;
            await refresh().catch(() => {});
        }
    });

    disableBtn.addEventListener('click', async () => {
        const registration = await registrationPromise;
        const subscription = await registration.pushManager.getSubscription();
        if (subscription) {
            await post(card.dataset.unsubscribe, { endpoint: subscription.endpoint }).catch(() => {});
            await subscription.unsubscribe();
        }
        window.Livewire?.dispatch('push-changed');
        await refresh();
    });

    refresh().catch((error) => setStatus(`Notifications unavailable: ${error.message}`, 'warn'));
}
