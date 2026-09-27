(() => {
  const mobileBreakpoint = window.matchMedia("(max-width: 767.98px)");
  const selects = document.querySelectorAll(".admin-mobile-select");

  function initializeMobileSelect(select, index) {
    const wrapper = document.createElement("div");
    const trigger = document.createElement("button");
    const menu = document.createElement("div");
    const menuId = `mobile-select-menu-${index}`;

    wrapper.className = "mobile-select-control";
    trigger.type = "button";
    trigger.className = "mobile-select-trigger";
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");
    trigger.setAttribute("aria-controls", menuId);

    menu.id = menuId;
    menu.className = "mobile-select-menu";
    menu.setAttribute("role", "listbox");

    for (const option of select.options) {
      const item = document.createElement("button");
      item.type = "button";
      item.className = "mobile-select-option";
      item.setAttribute("role", "option");
      item.textContent = option.textContent.trim();
      item.addEventListener("click", () => {
        select.value = option.value;
        select.dispatchEvent(new Event("change", { bubbles: true }));
        closeMenu();
      });
      menu.append(item);
    }

    function syncSelection() {
      const selectedOption = select.options[select.selectedIndex];
      trigger.textContent = selectedOption
        ? selectedOption.textContent.trim()
        : "Pilih";
      menu
        .querySelectorAll(".mobile-select-option")
        .forEach((item, itemIndex) => {
          const selected = itemIndex === select.selectedIndex;
          item.classList.toggle("selected", selected);
          item.setAttribute("aria-selected", String(selected));
        });
    }

    function closeMenu() {
      wrapper.classList.remove("is-open");
      trigger.setAttribute("aria-expanded", "false");
    }

    trigger.addEventListener("click", () => {
      const isOpen = wrapper.classList.toggle("is-open");
      trigger.setAttribute("aria-expanded", String(isOpen));
    });

    document.addEventListener("click", (event) => {
      if (!wrapper.contains(event.target)) closeMenu();
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") closeMenu();
    });
    select.addEventListener("change", syncSelection);

    wrapper.append(trigger, menu);
    select.insertAdjacentElement("afterend", wrapper);
    select.classList.toggle(
      "admin-mobile-native-select",
      mobileBreakpoint.matches,
    );
    syncSelection();
  }

  selects.forEach(initializeMobileSelect);
  mobileBreakpoint.addEventListener("change", () => {
    selects.forEach((select) => {
      select.classList.toggle(
        "admin-mobile-native-select",
        mobileBreakpoint.matches,
      );
    });
  });
})();
