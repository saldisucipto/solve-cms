import "@vite/client";
import "../css/app.css";
import "./icons.js";
if (import.meta.hot) {
  import.meta.hot.accept(() => {
    window.location.reload();
  });
}
