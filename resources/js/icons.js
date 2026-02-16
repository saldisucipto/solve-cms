document.querySelectorAll(".si").forEach((icon) => {
  const name = [...icon.classList].find((c) => c.startsWith("si-"));
  if (!name) return;

  icon.innerHTML = `
    <svg>
      <use href="/icons/solve-icons.svg#${name}"></use>
    </svg>
  `;
});
