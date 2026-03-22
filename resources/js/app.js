import "@vite/client";
import "../css/app.css";
import "./icons.js";
import "./form.js";
import "./http.js";
import "./modal.js";
import Swal from "sweetalert2";
import { bukaModal, closeModal } from "./modal.js";

window.swall = Swal;

window.$modal = {
  open_modal: bukaModal,
  close: closeModal,
};

if (import.meta.hot) {
  import.meta.hot.accept(() => {
    window.location.reload();
  });
}
