import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

/**
 * Surfaces backend error responses for Inertia visits. Without this a 419, 403
 * or 500 during navigation fails silently in the console and the user sees
 * nothing happen.
 */
function messageFrom(error) {
    const response = error?.response;

    if (response?.data?.message) {
        return String(response.data.message);
    }

    const validationMessages = Object.values(
        response?.data?.errors ?? {},
    ).flat();

    if (validationMessages.length > 0) {
        return String(validationMessages[0]);
    }

    if (response?.status === 419) {
        return 'Your session expired. Reload the page and sign in again.';
    }

    if (response?.status === 403) {
        return 'You do not have permission to do that.';
    }

    if (response?.status === 429) {
        return 'Too many requests. Please wait a moment.';
    }

    if (response?.status >= 500) {
        return 'The server reported an error. Please try again.';
    }

    return error?.message ? String(error.message) : 'The request failed.';
}

export function initializeErrorHandling() {
    router.on('httpException', (event) => {
        const detail = event.detail ?? {};

        // 409 is used by Inertia for version mismatches, which it already handles.
        if (detail.response?.status === 409) {
            return;
        }

        toast.error(messageFrom(detail));
    });

    router.on('networkError', (event) => {
        toast.error('Network error. Check your connection and try again.', {
            description: event?.detail?.message,
        });
    });

    router.on('error', (event) => {
        const errors = event?.detail?.errors ?? {};

        Object.values(errors)
            .flat()
            .slice(0, 1)
            .forEach((message) => toast.error(String(message)));
    });
}
