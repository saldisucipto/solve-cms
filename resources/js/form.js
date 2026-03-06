export function formSubmit(selector, callback) {
  const form = document.querySelector(selector);

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const data = new FormData(form);
    callback(data);
  });
}
