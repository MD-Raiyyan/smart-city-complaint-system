# Design System Specification: Civic Clarity

## 1. Overview & Creative North Star
**The Creative North Star: "The Digital Architect"**

The design system moves beyond the cold, bureaucratic standard of typical government portals. Instead, it adopts the persona of a "Digital Architect"—an experience that feels structured yet transparent, authoritative yet breathing. 

To achieve a high-end editorial feel, we depart from the "box-within-a-box" layout. We utilize **intentional asymmetry**, where large typographic headlines anchor the eye, and content "floats" on varying tonal planes. By prioritizing negative space and removing harsh structural lines, we create a sense of calm and efficiency essential for a Smart City environment.

---

## 2. Colors & Surface Philosophy
The palette is rooted in a "Clean White" foundation, using the `primary` (#005bbf) for institutional trust and `tertiary` (#006d2c) for civic success and sustainability.

### The "No-Line" Rule
**Borders are prohibited for sectioning.** To separate content, designers must use background shifts. A section might transition from `surface` to `surface-container-low` to signify a change in context. This creates a sophisticated, seamless flow that feels like a singular, unified document rather than a collection of disparate widgets.

### Surface Hierarchy & Nesting
Depth is built through "Tonal Stacking." Instead of shadows being the primary driver of hierarchy, use the container tiers:
*   **Base Layer:** `surface` (#f8f9fa)
*   **Sectioning:** `surface-container-low` (#f3f4f5) for large secondary areas.
*   **Actionable Cards:** `surface-container-lowest` (#ffffff) to provide a "pop" of clean white against the slightly darker background.
*   **Active Overlays:** `surface-container-highest` (#e1e3e4) for temporary states like drawer menus.

### The "Glass & Gradient" Rule
To add "soul" to the professional blue:
*   **Hero Areas:** Use a subtle linear gradient from `primary_container` (#1a73e8) to `primary` (#005bbf) at a 135-degree angle.
*   **Glassmorphism:** For floating navigation bars or map overlays, use `surface` at 80% opacity with a `24px` backdrop-blur. This keeps the Smart City context (like maps or data) visible even when navigating menus.

---

## 3. Typography
We utilize **Inter** for its mathematical precision and high legibility. The scale is designed to feel editorial—large displays for impact and compact labels for data-heavy civic dashboards.

*   **Display (lg/md/sm):** Used for "Hero" moments and major city metrics. These should be set with a slightly tighter letter-spacing (-0.02em) to feel premium.
*   **Headline (lg/md/sm):** Your primary navigational anchors. Use `headline-lg` for page titles to establish immediate authority.
*   **Title (lg/md/sm):** Dedicated to card headings and section titles.
*   **Body (lg/md/sm):** Always use `on_surface_variant` (#414754) for long-form text to reduce eye strain, reserving `on_surface` (#191c1d) for titles.
*   **Labels:** Specifically for metadata and small buttons. Use `label-md` in all-caps with +0.05em tracking for a "technical/data" aesthetic.

---

## 4. Elevation & Depth
In this design system, light and air replace heavy ink.

*   **The Layering Principle:** A `surface-container-lowest` card sitting on a `surface-container-low` background creates a natural "lift" of roughly 2dp without a single drop shadow. Use this as the default state.
*   **Ambient Shadows:** For interactive elements (like a button on hover), use an extra-diffused shadow:
    *   *Offset:* 0px 8px | *Blur:* 24px | *Color:* `on_surface` at 6% opacity.
    *   This mimics natural light and prevents the UI from looking "dirty."
*   **The "Ghost Border" Fallback:** In high-density data tables where boundaries are essential, use `outline_variant` (#c1c6d6) at **15% opacity**. It should be felt, not seen.

---

## 5. Components

### Buttons
*   **Primary:** Background: `primary_container`. Text: `on_primary`. 
    *   *Detail:* Apply an 8px radius (`DEFAULT`). On hover, transition the background to `primary` and apply the **Ambient Shadow**.
*   **Secondary:** Background: `transparent`. Border: `Ghost Border` (outline-variant at 20%).
*   **Tertiary (Success):** Use `tertiary_container` (#008939) for "Success" actions (e.g., "Payment Complete," "Permit Approved").

### Cards & Lists
*   **Forbid Dividers:** Never use a horizontal line to separate list items. Use 16px or 24px of vertical padding and a background shift (`surface-container-low` on hover) to define rows.
*   **Rounding:** All cards must use the `DEFAULT` (8px) radius. For high-end "Feature" cards, the `lg` (16px) radius may be used to create a softer, more modern look.

### Input Fields
*   **Stateful Design:** Default state should use `surface_container_highest` as a subtle background fill with no border. On focus, animate a 2px bottom-border using `primary`. This "underline-only" focus state feels more sophisticated than a full-box outline.

### Smart City Specifics: "The Metric Badge"
*   Use `secondary_container` (#b2c9fe) with `label-md` text. These small, rounded chips should be used to display real-time data status (e.g., "Live," "Scheduled," "Optimized").

---

## 6. Do's and Don'ts

### Do
*   **Do** use asymmetrical margins. For example, a wider left margin for text content than the right margin creates an editorial, high-end feel.
*   **Do** use `tertiary` (#006d2c) sparingly as a "Success" accent. It should feel like a reward for the user.
*   **Do** prioritize white space. If you think there is enough space, add 8px more.

### Don't
*   **Don't** use 1px solid #CCCCCC borders. This is the quickest way to make a professional system look like a generic template.
*   **Don't** use pure black (#000000) for text. Use `on_surface` (#191c1d) to maintain a soft, premium contrast.
*   **Don't** use standard "Drop Shadows." Only use the **Ambient Shadow** spec to ensure the UI feels light and integrated with the background.