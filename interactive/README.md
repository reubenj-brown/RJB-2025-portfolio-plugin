# RJB Interactive

Svelte + D3 data-visualisation **islands** for the portfolio. Each visualisation
is an independent component that hydrates a placeholder `<div>` printed by the
`[reuben_viz]` shortcode. Pages download only the islands they actually contain.

## Why a build step lives here

Svelte must be compiled, so this folder is the one place in the plugin with a
Node/Vite toolchain. The compiled output in `dist/` is **committed** — the host
(Hostinger) has no Node, and WordPress enqueues `dist/rjb-viz.js` directly.

```
interactive/
├── package.json / vite.config.js / svelte.config.js
├── index.html              # dev harness (Vite only; not shipped)
├── src/
│   ├── mount.js            # runtime: finds [data-viz] nodes, lazy-mounts islands
│   ├── lib/                # shared framework layer (reused by every island)
│   │   ├── data/load.js    #   fetch + parse JSON/CSV/TSV (d3-dsv)
│   │   ├── charts/responsive.js  # use:resize action for fluid charts
│   │   └── scroll/scroller.js     # scrollytelling step manager (foundation)
│   ├── viz/<id>/           # individual charts — index.js + <Name>.svelte
│   └── stories/<id>/       # scrollytelling compositions (built on lib/scroll)
└── dist/                   # COMMITTED build output, enqueued by WordPress
```

## Develop

```bash
cd interactive
npm install          # first time only
npm run dev          # http://localhost:5173 — live preview via index.html
```

Add a `.rjb-viz` div to `index.html` to preview any island locally.

## Build & deploy

```bash
npm run build        # writes dist/
cd ..
git add interactive/dist interactive/src   # commit source AND compiled output
git commit -m "viz: …" && git push
```

Then deploy the plugin as usual. `dist/rjb-viz.js` is cache-busted by the
plugin via `filemtime()`, so a fresh build is picked up automatically.

## Add a new visualisation

1. `src/viz/<id>/index.js` — default-export `mount(target, props)` (copy
   `solar-output/index.js`).
2. `src/viz/<id>/<Name>.svelte` — the component. Read inputs from `$props()`;
   they map to the shortcode's `data-*` attributes.
3. `npm run build`, commit `dist/`.
4. Embed: `[reuben_viz id="<id>" src="https://…/data.json" title="…"]`.

Reuse `lib/` for data loading, responsiveness, and scroll — don't reinvent
those per chart. Import D3 by submodule (`d3-scale`, not `d3`) to keep bundles
small.
