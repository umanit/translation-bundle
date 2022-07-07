document.addEventListener('DOMContentLoaded', () => {
  // Adds the locale as a parameter to the edit route to know which version we want to edit/translate to
  document.querySelectorAll('[data-translate-to]').forEach(link => {
    const href = new URL(link.href);

    href.searchParams.append('locale', link.dataset.translateTo);

    link.href = href.toString();
  });
});