export async function http(url, options = {}) {
  const res = await fetch(url, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    ...options,
  });

  return await res.json();
}
