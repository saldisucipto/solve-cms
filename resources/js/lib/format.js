const STATUS_VARIANTS = {
    published: 'default',
    draft: 'secondary',
    pending: 'outline',
    scheduled: 'outline',
    approved: 'default',
    spam: 'destructive',
    rejected: 'destructive',
    active: 'default',
    inactive: 'secondary',
};

export function statusVariant(status) {
    return STATUS_VARIANTS[status] ?? 'outline';
}

export function statusLabel(status) {
    if (!status) {
        return '—';
    }

    return status.charAt(0).toUpperCase() + status.slice(1);
}

export function formatDate(value, locale = 'en') {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
}

export function formatDateTime(value, locale = 'en') {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}
