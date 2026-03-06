// export function openModal(id) {
//   document.getElementById(id).classList.remove("hidden");
// }

// export function closedModal(id) {
//   document.getElementById(id).classList.add("hidden");
// }

const modal = document.getElementById("app-modal");
const modalTitle = document.getElementById("modal-title");
const modalBody = document.getElementById("modal-body");
const modalClose = document.getElementById("modal-close");

export function openModal({ title = "", content = "" }) {
  modalTitle.innerText = title;

  if (typeof content === "string") {
    // console.info(content);
    modalBody.innerHTML = content;
  } else {
    modalBody.innerHTML = "";
    modalBody.appendChild(content);
  }

  modal.classList.remove("hidden");
}

export function closeModal() {
  modal.classList.add("hidden");
}

modalClose.addEventListener("click", closeModal);
