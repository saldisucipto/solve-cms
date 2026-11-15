import { afterEach, describe, expect, it, vi } from 'vitest';
import { HttpError, requestJson } from '@/lib/http';

function jsonResponse(body, status = 200) {
    return {
        ok: status >= 200 && status < 300,
        status,
        json: async () => body,
    };
}

afterEach(() => {
    vi.restoreAllMocks();
});

describe('requestJson error handling', () => {
    it('returns the parsed payload on success', async () => {
        global.fetch = vi.fn(async () => jsonResponse({ items: [1, 2] }));

        await expect(requestJson('/admin/media/library')).resolves.toEqual({
            items: [1, 2],
        });
    });

    it('surfaces the backend message on a validation error', async () => {
        global.fetch = vi.fn(async () =>
            jsonResponse(
                {
                    message: 'The file field must be a file of type: jpg, png.',
                    errors: {
                        file: [
                            'The file field must be a file of type: jpg, png.',
                        ],
                    },
                },
                422,
            ),
        );

        const error = await requestJson('/admin/media', {
            method: 'POST',
            body: new FormData(),
        }).catch((e) => e);

        expect(error).toBeInstanceOf(HttpError);
        expect(error.status).toBe(422);
        expect(error.message).toContain('must be a file of type');
        expect(error.messages()).toContain(
            'The file field must be a file of type: jpg, png.',
        );
    });

    it('falls back to a helpful message when the body has none', async () => {
        global.fetch = vi.fn(async () => ({
            ok: false,
            status: 419,
            json: async () => {
                throw new Error('not json');
            },
        }));

        const error = await requestJson('/admin/posts').catch((e) => e);

        expect(error).toBeInstanceOf(HttpError);
        expect(error.message).toContain('session expired');
    });

    it('reports server errors', async () => {
        global.fetch = vi.fn(async () =>
            jsonResponse({ message: 'Server exploded' }, 500),
        );

        const error = await requestJson('/admin/posts').catch((e) => e);

        expect(error.status).toBe(500);
        expect(error.message).toBe('Server exploded');
    });

    it('sends the CSRF token for mutating requests', async () => {
        document.head.innerHTML =
            '<meta name="csrf-token" content="token-123">';
        global.fetch = vi.fn(async () => jsonResponse({ ok: true }));

        await requestJson('/admin/media/library', {
            method: 'POST',
            body: new FormData(),
        });

        const [, options] = global.fetch.mock.calls[0];

        expect(options.headers['X-CSRF-TOKEN']).toBe('token-123');
        expect(options.headers['Content-Type']).toBeUndefined();
    });
});
