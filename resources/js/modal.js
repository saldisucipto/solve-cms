const modal = document.querySelector(".app-modal");
const modalTitle = document.querySelector(".modal-title");
const modalBody = document.querySelector(".modal-body");
const modalClose = document.querySelector(".modal-close");

export function closeModal() {
  modalClose;
  modal.classList.add("hidden");
}

if (modal) {
  modalClose.addEventListener("click", closeModal);
}

export function bukaModal() {
  modal.classList.remove("hidden");
}
