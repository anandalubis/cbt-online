(() => {
  const storageKey = "cbt-portal-theme";
  const root = document.documentElement;

  function readPreference() {
    try {
      const saved = localStorage.getItem(storageKey);
      if (saved === "light" || saved === "dark") return saved;
    } catch (error) {
      // Storage can be unavailable in private browsing; keep the session usable.
    }
    return window.matchMedia?.("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  }

  function applyTheme(theme) {
    root.dataset.theme = theme;
    root.setAttribute("data-bs-theme", theme);

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      const isDark = theme === "dark";
      const label = isDark ? "Beralih ke mode terang" : "Beralih ke mode gelap";
      const icon = button.querySelector("i");
      const text = button.querySelector("[data-theme-label]");

      button.setAttribute("aria-label", label);
      button.setAttribute("title", label);
      button.setAttribute("aria-pressed", String(isDark));
      if (icon)
        icon.className = `bi ${isDark ? "bi-sun-fill" : "bi-moon-stars-fill"}`;
      if (text) text.textContent = isDark ? "Mode terang" : "Mode gelap";
    });
  }

  applyTheme(readPreference());

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-theme-toggle]");
    if (!button) return;

    const nextTheme = root.dataset.theme === "dark" ? "light" : "dark";
    try {
      localStorage.setItem(storageKey, nextTheme);
    } catch (error) {
      // The selected theme still applies for this page if storage is unavailable.
    }
    applyTheme(nextTheme);
  });
})();
