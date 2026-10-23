document.querySelectorAll("form[data-confirm]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    const message = form.getAttribute("data-confirm");
    if (message && !window.confirm(message)) event.preventDefault();
  });
});

document.querySelectorAll("form[data-auth-form], form[data-password-reset]").forEach((form) => {
  const status = form.querySelector(".admin-form-status");
  const isPasswordReset = form.matches("[data-password-reset]");
  const accessTokenField = form.querySelector('input[name="access_token"]');
  if (isPasswordReset && accessTokenField instanceof HTMLInputElement) {
    const token = new URLSearchParams(window.location.hash.slice(1)).get("access_token");
    if (token) {
      accessTokenField.value = token;
      window.history.replaceState(null, "", window.location.pathname);
    }
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!(status instanceof HTMLElement)) return;

    const values = new FormData(form);
    if (isPasswordReset) {
      const password = String(values.get("password") || "");
      const confirmation = String(values.get("password_confirmation") || "");
      if (password !== confirmation) {
        status.textContent = "The passwords do not match.";
        status.dataset.state = "error";
        return;
      }
      if (!String(values.get("access_token") || "")) {
        status.textContent = "Open the secure reset link from your email first.";
        status.dataset.state = "error";
        return;
      }
      values.delete("password_confirmation");
    }

    const button = form.querySelector('button[type="submit"]');
    if (button instanceof HTMLButtonElement) button.disabled = true;
    status.textContent = "Please wait…";
    status.dataset.state = "pending";
    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: values,
        headers: { Accept: "application/json" },
      });
      const result = await response.json();
      if (!result || typeof result.message !== "string") {
        throw new Error("The server returned an invalid response.");
      }
      status.textContent = result.message;
      status.dataset.state = response.ok ? "success" : "error";
      if (response.ok && isPasswordReset) {
        const loginLink = document.createElement("a");
        loginLink.href = "/admin/login";
        loginLink.textContent = "Sign in";
        status.append(" ", loginLink);
      }
      if (response.ok && !isPasswordReset) form.reset();
    } catch (error) {
      if (!(error instanceof Error)) throw error;
      status.textContent = "This request could not be completed. Please try again.";
      status.dataset.state = "error";
    } finally {
      if (button instanceof HTMLButtonElement) button.disabled = false;
    }
  });
});
