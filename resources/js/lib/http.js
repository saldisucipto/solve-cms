/**
 * Small JSON client used by the interactive admin widgets (media picker, SEO
 * panel). Unlike a bare fetch it surfaces whatever the backend actually sent,
 * including Laravel's validation errors, so the interface never invents a
 * generic "something failed" message.
 */

export class HttpError extends Error {
    constructor(message, status, errors = {}) {
        super(message);
        this.name = 'HttpError';
        this.status = status;
        this.errors = errors ?? {};
    }

    /** Every backend message, including per-field validation errors. */
    messages() {
        const collected = Object.values(this.errors ?? {})
            .flat()
            .filter(Boolean);

        if (collected.length > 0) {
            return collected;
        }

        return [this.message];
    }
}

function fallbackMessage(status) {
    if (status === 401 || status === 419) {
        return 'Your session expired. Reload the page and try again.';
    }

    if (status === 403) {
        return 'You do not have permission to do that.';
    }

    if (status === 404) {
        return 'That resource no longer exists.';
    }

    if (status === 429) {
        return 'Too many requests. Please wait a moment.';
    }

    if (status >= 500) {
        return 'The server reported an error. Please try again.';
    }

    return 'The request could not be completed.';
}

export function csrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

export async function requestJson(url, options = {}) {
    const isFormData = options.body instanceof FormData;

    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
            ...(options.method && options.method !== 'GET'
                ? { 'X-CSRF-TOKEN': csrfToken() }
                : {}),
            ...options.headers,
        },
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        throw new HttpError(
            payload?.message || fallbackMessage(response.status),
            response.status,
            payload?.errors ?? {},
        );
    }

    return payload;
}
