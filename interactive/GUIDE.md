# A Plain-Language Guide to the Interactive Viz System

> ## ⚡ Quick start — preview live in VS Code
> Open a terminal (**Terminal → New Terminal**) and run:
> ```bash
> cd "/Users/reubenj.brown/RJB-2025-portfolio-plugin/interactive"
> npm run dev
> ```
> Then **Cmd-click** the `http://localhost:5173/` link (or **Cmd+Shift+P → "Simple
> Browser: Show"** to view inside VS Code). Edit any `.svelte` file and **save** —
> the preview updates instantly. Press **Ctrl+C** in the terminal to stop.
>
> First time on a new machine only: run `npm install` once before `npm run dev`.

---


You already know PHP, CSS, HTML, and vanilla JS. This system adds one genuinely
new idea — a **build step** — and a few tools that depend on it. This guide
explains what each piece is, why it exists, and how you'll actually use it,
always by comparing it to things you already do.

---

## 1. The one big new concept: a "build step"

Everywhere else on your site, the file you edit *is* the file the browser runs.
You save `stories-section.css`, WordPress serves `stories-section.css`. Done.

This folder is different. Here you write code in a **convenient format for
humans** (`.svelte` files), and a tool called **Vite** translates it into a
**plain `.js` file the browser understands**. That translation is the "build
step."

```
   You edit            Vite translates           Browser runs
  src/*.svelte    ─────────────────────────▶    dist/rjb-viz.js
  (human-friendly)        npm run build          (machine-friendly)
```

**Why bother?** Because Svelte and the chart tools let you describe a complex,
interactive chart in a fraction of the code that hand-written JS would take. The
price is that a browser can't read `.svelte` directly — it has to be compiled
first. That compile is the only real new habit you're taking on.

**The mental model:** `src/` is your kitchen. `dist/` is the plated dish. You
cook in `src/`; you serve from `dist/`. You never edit `dist/` by hand, the same
way you'd never edit the combined CSS bundle by hand — it's generated.

---

## 2. Why this lives in the plugin, not the theme

Your theme is the *chrome*: headers, footers, page layout. Your plugin is the
*content*: the shortcodes that drop sections into pages. A chart embedded in a
story is content, so it belongs with the other shortcodes. Bonus: if you ever
change themes, your charts come along for the ride.

---

## 3. The folder map, in plain terms

```
interactive/
├── package.json        "Shopping list" of tools this project needs
├── vite.config.js      Settings for the translator (Vite)
├── index.html          A private test page, just for you (never goes live)
│
├── src/                ◀── YOUR KITCHEN. You work here.
│   ├── mount.js            The "switchboard" (explained below)
│   ├── lib/                Shared toolbox — reused by every chart
│   │   ├── data/load.js        Loads a spreadsheet/JSON file
│   │   ├── charts/responsive.js  Makes charts resize with the screen
│   │   └── scroll/scroller.js    Engine for scroll-driven stories (later)
│   ├── viz/                One folder per chart
│   │   └── solar-output/       The example chart I built
│   └── stories/            One folder per scrollytelling piece (later)
│
└── dist/               ◀── THE PLATED DISH. Generated. Don't touch.
```

The split that matters most: **`lib/` is shared plumbing** you write once and
reuse; **`viz/` and `stories/` are the individual pieces** you'll keep adding.
It's the same instinct as `base-sections.css` (shared) vs. each section's own
CSS file (specific).

---

## 4. What each key file does

### `package.json` — the shopping list
Lists the tools the project depends on (Svelte, the D3 chart helpers, Vite).
When you run `npm install`, it reads this list and downloads them into a
`node_modules/` folder. You rarely open this file; you just need to know it's
the manifest.

> **`node_modules/` is huge and disposable.** It's downloaded tooling, not your
> work. It's deliberately *not* saved to GitHub (`.gitignore` excludes it).
> Anyone can recreate it with `npm install`.

### `mount.js` — the switchboard
This is the clever bit that makes everything efficient. When a page loads, this
tiny script (2 KB) wakes up and:
1. Looks for any placeholder `<div data-viz="…">` on the page (your shortcode
   prints these).
2. **Waits** until that div is about to scroll into view.
3. *Then* downloads the actual chart code and draws it.

So a page only loads the charts it actually shows, and only when the reader
reaches them. That's why your homepage stays fast — the same priority as your
recent performance work.

### `lib/` — the shared toolbox
- **`data/load.js`** — give it a URL to a `.json` or `.csv`, it hands back clean
  rows of data. (Think of it as one reusable "fetch + parse" helper so no chart
  reinvents it.)
- **`charts/responsive.js`** — the one-liner that makes a chart redraw itself
  when the window resizes, so charts look right on mobile.
- **`scroll/scroller.js`** — the foundation for scrollytelling (a graphic that
  changes as the reader scrolls past paragraphs). It's stubbed in and ready for
  when you build your first one.

### `viz/solar-output/` — an example chart (and your template)
Two files, and every future chart follows the same two-file pattern:
- **`index.js`** — a 3-line "plug" that connects the chart to the switchboard.
  You'll copy this almost verbatim each time.
- **`SolarOutput.svelte`** — the actual chart. This is where the real work is.

---

## 5. What a `.svelte` file actually is

A `.svelte` file is the natural evolution of something you already do. Today, a
section of your site is split across three files: a PHP/HTML template, a CSS
file, and maybe a JS file. A Svelte file puts all three **in one file**, in
three labelled sections:

```svelte
<script>
  // The JS: load data, do the maths
</script>

<figure>
  <!-- The HTML: the markup, with {curly braces} for live data -->
</figure>

<style>
  /* The CSS: scoped to THIS component only — can't leak out */
</style>
```

Two things make it powerful:

1. **`{curly braces}` insert live data into the HTML** — like PHP's `<?php echo
   $value; ?>`, but it updates automatically when the data changes.
2. **The `<style>` is automatically scoped** — a `.tick` class here can't
   accidentally clash with a `.tick` anywhere else on the site. (This is a nice
   exception to the global-CSS-reuse rule: chart styles are deliberately
   self-contained so a chart is a sealed unit you can drop anywhere.)

In `SolarOutput.svelte`, the `<script>` loads the solar data and computes the
12-month average; the HTML draws the SVG lines; the `<style>` colours them
(using your `--highlight-color` green). Open it — it reads more like plain
HTML/CSS/JS than you'd expect.

---

## 6. What "D3" is for

D3 is a maths library for charts. It does *not* draw anything — it does the
**calculations** that drawing requires: "given solar values from 400 to 1000,
and a chart 360 pixels tall, what's the Y-position of each point?" You let D3 do
that maths, and let Svelte do the drawing. We import only the small pieces of D3
we need (scales, line shapes) rather than the whole library, to keep things
light.

---

## 7. How you'll actually build a new component

Here's the full loop. The first time will feel unfamiliar; by the third it's
muscle memory.

### One-time setup (per computer)
```bash
cd interactive
npm install          # downloads the tools. Takes a few seconds.
```

### Each work session
```bash
cd interactive
npm run dev          # starts a live preview at http://localhost:5173
```
Open that URL in your browser. You'll see `index.html` — your private test page
with the example chart on it. **As you edit and save a `.svelte` file, the
browser updates instantly.** This is your sandbox; it never affects the live
site.

### Building a brand-new chart, step by step
1. **Copy the example folder:** duplicate `src/viz/solar-output/` and rename it,
   e.g. `src/viz/rainfall/`.
2. **Rename the component file** inside it (e.g. `Rainfall.svelte`) and update
   the one `import` line in `index.js` to point at the new name.
3. **Edit the `.svelte` file** to make your chart. Reuse `lib/` for loading data
   and responsiveness — don't rewrite those.
4. **Preview it:** add a line to `index.html` so it shows in your sandbox:
   ```html
   <div class="rjb-viz" data-viz="rainfall" data-src="/src/viz/rainfall/sample-data.json"></div>
   ```
   Save, and it appears in the live preview.
5. **When happy, build it:**
   ```bash
   npm run build       # regenerates dist/
   ```
6. **Save your work to GitHub** (source *and* the generated `dist/`):
   ```bash
   cd ..
   git add interactive
   git commit -m "viz: add rainfall chart"
   git push
   ```
7. **Use it on the real site** — drop the shortcode into any page or story:
   ```
   [reuben_viz id="rainfall" src="https://reubenjbrown.com/wp-content/uploads/.../rainfall.json" title="Annual rainfall"]
   ```

That's it. The shortcode is the only part that touches WordPress, and it works
exactly like every other shortcode you already use.

---

## 8. The three commands you need to remember

| Command | When | Plain meaning |
|---|---|---|
| `npm install` | once per computer | "Download the tools." |
| `npm run dev` | start of each session | "Open my live sandbox." |
| `npm run build` | before committing | "Plate the dish for serving." |

If you only memorise one rule: **edit in `src/`, run `npm run build`, commit
everything including `dist/`.**

---

## 9. Why each part is important (the short version)

- **Build step / Vite** — lets you write powerful charts in little code; the
  cost is one `build` command before deploying.
- **Living in the plugin** — charts are content, and stay with you across theme
  changes.
- **`mount.js` switchboard + lazy loading** — keeps pages fast; only loads a
  chart when the reader reaches it.
- **`lib/` shared toolbox** — write data-loading, resizing, and scroll logic
  once; reuse forever. (Same DRY instinct as `base-sections.css`.)
- **One folder per chart** — each visualisation is a self-contained unit you can
  add, remove, or reuse without touching the others.
- **`.svelte` files** — HTML + CSS + JS in one scoped, reusable component.
- **Committing `dist/`** — your host has no build tools, so you ship the
  finished `.js`, the same way you ship finished CSS.

---

*Questions to ask me anytime: "walk me through SolarOutput.svelte line by
line," "help me start a new chart for X data," or "set up my first
scrollytelling piece."*
