const form = document.querySelector("#contact-form");
const serviceSelect = document.querySelector("#service");
const requestedService = new URLSearchParams(window.location.search).get("service");

if (
  serviceSelect
  && requestedService
  && Array.from(serviceSelect.options).some((option) => option.value === requestedService)
) {
  serviceSelect.value = requestedService;
}

const status = document.querySelector("#form-status");
const submitButton = form?.querySelector('button[type="submit"]');

if (
  form instanceof HTMLFormElement
  && form.dataset.enabled === "true"
  && submitButton instanceof HTMLButtonElement
) {
  submitButton.disabled = false;
}

const messageField = form?.querySelector("#message");
if (messageField instanceof HTMLTextAreaElement && status instanceof HTMLElement) {
  messageField.addEventListener("invalid", () => {
    if (messageField.validity.tooShort) {
      status.textContent = "Please enter at least 10 characters in your message so we can understand your enquiry.";
      status.dataset.state = "error";
    }
  }, true);

  messageField.addEventListener("input", () => {
    if (!messageField.validity.tooShort && status.dataset.state === "error") {
      status.textContent = "";
      delete status.dataset.state;
    }
  });
}

form?.addEventListener("submit", async (event) => {
  event.preventDefault();

  if (!(status instanceof HTMLElement) || !(submitButton instanceof HTMLButtonElement)) {
    throw new Error("Contact feedback controls are missing.");
  }

  submitButton.disabled = true;
  form.setAttribute("aria-busy", "true");
  status.textContent = "Sending your enquiry…";
  status.dataset.state = "pending";

  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { Accept: "application/json" },
    });
    const contentType = response.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
      throw new Error("Unexpected contact service response.");
    }

    const result = await response.json();
    if (!result || typeof result.message !== "string" || !result.message.trim()) {
      throw new Error("Contact service returned no usable message.");
    }
    status.textContent = result.message;
    status.dataset.state = response.ok ? "success" : "error";
    if (response.ok) form.reset();
  } catch (error) {
    if (!(error instanceof Error)) throw error;
    status.textContent = "Your enquiry could not be confirmed. Please call 045 254212 or email info@acctechs.ie.";
    status.dataset.state = "error";
  } finally {
    submitButton.disabled = false;
    form.setAttribute("aria-busy", "false");
    status.focus();
  }
});
