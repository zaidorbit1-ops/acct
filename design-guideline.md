# Design Guidelines

Use this guide when making future visual changes to the ACCTS & TECHS website.

## Keep the existing design language

- Keep the Manrope typeface, current font sizes, and established blue palette. Do not introduce a new font or resize type as part of a visual enhancement unless specifically requested.
- Reuse the existing `.button` and `.button-outline` styles and the colors already defined in `assets/css/buttons.css`.
- Keep homepage-only styling scoped to `body.page-home` in `assets/css/home.css`. Do not unintentionally change the other PHP pages.
- Prefer the existing SVG icons and CSS artwork. Do not add an external icon or animation library for decorative effects.

## Preserve section spacing

- Do not add margins or increase vertical padding between homepage sections.
- The original section spacing is intentional. Preserve the existing `.section` spacing instead of adding new per-section padding overrides.
- Keep spacing consistent at desktop and mobile sizes; check the section boundaries at both widths before finishing.

## Hover and interaction treatment

- Keep the hover treatment on the “Professional support for your business” service cards.
- Other homepage card groups may use restrained, consistent feedback: a small upward lift, a cool-blue border/shadow, and a short blue accent reveal. Keep transitions smooth and subtle; avoid exaggerated 3D, rotating cards, or unrelated decoration.
- Industry cards may gently zoom their photos. Approach cards may lightly lift their step icon. Keep the card’s typography and dimensions unchanged.
- Buttons and related-support links should look like the site’s reusable blue/light-blue buttons and have a clear hover and keyboard-focus state.

## Finder popups

- “What would you like to move forward?” uses one native `<dialog>` per topic. Keep each trigger’s `aria-controls` matched to its unique dialog ID and keep each dialog labelled by its topic heading.
- Each dialog must remain independently openable and closable, including by its close control and Escape key. Keep the recommended service actions and related-support links inside the popup.
- Show the six service topics in three columns on desktop, two on tablet, and one on mobile. Use the existing topic-specific inline SVG icons and a simple right arrow for the “View matching support” action; do not use a cross or a numbered badge on the card.
- Keep the popup compact and structured: icon and topic title, primary action links, then related-support buttons. The cross icon belongs only on the popup’s close control.
- Do not add decorative sequence numbers to the finder cards.

## Scroll effects and accessibility

- Home, About, and service-page sections/cards reveal whenever they enter the viewport and hide/reset when they leave, so the animation replays in both scroll directions. Keep the effect understated with a small vertical offset and a 620 ms opacity/position transition.
- Smooth scrolling and reveal effects must respect `prefers-reduced-motion`. If JavaScript or `IntersectionObserver` is unavailable, content must remain visible.
- Preserve visible keyboard focus and use semantic controls for interactions.

## Numbering

- Do not add `1`, `2`, `3`, `4` or other decorative sequence numbers to headings, cards, icons, process steps, or other sections on any page.

## Where to make changes

- Homepage markup and copy: `data/pages.json` (the `home` record).
- Homepage presentation and responsive states: `assets/css/home.css`.
- Homepage popup, scroll-reveal, and shared navigation behavior: `assets/js/site.js`.
- Shared button styles: `assets/css/buttons.css`.

After a visual change, check the homepage at desktop and mobile widths, confirm the section spacing and image loading, and test card hover/focus, popup open/close, and reduced-motion behavior.
