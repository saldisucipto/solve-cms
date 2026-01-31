import "@vite/client";
import "../css/app.css";
if (import.meta.hot) {
  import.meta.hot.accept(() => {
    window.location.reload();
  });
}
