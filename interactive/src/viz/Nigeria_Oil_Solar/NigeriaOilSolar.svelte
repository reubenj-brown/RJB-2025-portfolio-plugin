<script>
  // The static chart art (bars, lines, gridlines, shaded bands) is a paths-only
  // SVG with no <text>. Every label is drawn on top as an absolutely-positioned
  // HTML overlay, mapped onto the chart by % of its viewBox. Font sizes are
  // fixed px (the theme's --fs-* tokens), tunable per breakpoint via --noil-scale.
  import rawSvg from "./nigeria_solar_vs_petrol_minify.svg?raw";

  // `body` is optional HTML (enclosed shortcode content) shown in the top-left
  // blank; it wraps naturally within its region.
  let { body = "" } = $props();

  // --- coordinate space ----------------------------------------------------
  const VB_W = 155.99; // original SVG width
  const VB_H = 385; // bottom bound; matches the original SVG art (source note fits within)
  const VB_Y0 = 5; // crop the empty margin above the 1.4 GW line/dot (top ≈7)
  const REF_WIDTH = 760; // desktop width the fs px-vars are calibrated to

  // Chart art with its outer <svg> wrapper stripped, injected into our own <svg>.
  const chartInner = rawSvg
    .replace(/^[\s\S]*?<svg[^>]*>/, "")
    .replace(/<\/svg>\s*$/, "");

  // Palette + type tokens resolve from the theme's :root on the live site;
  // fallbacks keep it rendering in isolation (local dev, this repo).
  const COBALT = "var(--cr-cobalt, #003cff)";
  const SOIL = "var(--cr-soil, #805533)";
  const GREY = "var(--cr-50grey, #808080)";
  const INK = "var(--text-color, #000)";

  // Key y-lines pulled from the art: dashed gridlines + baseline axis.
  const Y_300 = 294.14,
    Y_200 = 320.16,
    Y_100 = 346.18,
    Y_0 = 372.33;
  // Year-band centres (ticks at 11.44 / 42.91 / 74.38 / 105.85 / 137.32).
  const YEAR = [27.18, 58.65, 90.12, 121.59];

  // --- text blocks (single source of truth for the overlay) ----------------
  // Each block: an anchor point (x,y = baseline of the FIRST line), an anchor
  // (start|middle|end), a font-size var, a line-height in viewBox units, and
  // `lines` — each line an array of runs {t, w?(weight), c?(colour)}.
  // Positions are in viewBox units.
  const B = {
    headline: {
      x: 0,
      y: 268,
      anchor: "start",
      size: "--fs-6xl",
      lh: 5,
      lines: [[{ t: "Oil for Sun", w: 600 }]],
    },
    deck: {
      x: 0,
      y: 275,
      anchor: "start",
      size: "--fs-base",
      lh: 4,
      lines: [
        [
          { t: "Surges in Nigerian " },
          { t: "solar imports", w: 600, c: COBALT },
        ],
        [{ t: "have tracked " }, { t: "petrol prices", w: 600, c: SOIL }],
      ],
    },
    callout: {
      x: 104.96,
      y: 15,
      anchor: "start",
      size: "--fs-sm",
      lh: 3.5,
      lines: [
        [{ t: "1.4 GW", w: 600, c: COBALT, size: "--fs-2xl" }],
        [{ t: "of Chinese solar" }],
        [{ t: "panels and cells" }],
        [{ t: "imported to " }, { t: "Nigeria", w: 600 }],
        [{ t: "in March 2026" }],
      ],
    },
    source: {
      x: 78,
      y: 382.5,
      anchor: "middle",
      size: "--fs-xs",
      lh: 3.2,
      wrap: true, // wrap within `w` so it can't overflow on mobile
      w: 150,
      color: GREY,
      lines: [
        [
          {
            t: "Data: Ember; Nigeria Petroleum Products Pricing Regulatory Agency",
          },
        ],
        [
          {
            t: "Solar imports as three-month rolling average. Dots show monthly figures. Petrol price in US cents per liter.",
          },
        ],
      ],
    },
  };

  // Axis tick labels are single-line, so generate them compactly.
  const leftAxis = [
    {
      x: 0,
      y: Y_300 - 2,
      anchor: "start",
      size: "--fs-sm",
      lines: [[{ t: "300 MW", w: 600 }]],
    },
    {
      x: 0,
      y: Y_200 - 2,
      anchor: "start",
      size: "--fs-sm",
      lines: [[{ t: "200" }]],
    },
    {
      x: 0,
      y: Y_100 - 2,
      anchor: "start",
      size: "--fs-sm",
      lines: [[{ t: "100" }]],
    },
  ];
  const rightAxis = [
    {
      x: 154.5,
      y: Y_300 - 2,
      anchor: "end",
      size: "--fs-sm",
      lines: [[{ t: "90¢", w: 600 }]],
    },
    {
      x: 154.5,
      y: Y_200 - 2,
      anchor: "end",
      size: "--fs-sm",
      lines: [[{ t: "70" }]],
    },
    {
      x: 154.5,
      y: Y_100 - 2,
      anchor: "end",
      size: "--fs-sm",
      lines: [[{ t: "50" }]],
    },
    {
      x: 154.5,
      y: Y_0 - 2,
      anchor: "end",
      size: "--fs-sm",
      lines: [[{ t: "30" }]],
    },
  ];
  const xAxis = ["2022", "’23", "’24", "’25"].map((t, i) => ({
    x: YEAR[i],
    y: 377,
    anchor: "middle",
    size: "--fs-sm",
    lines: [[{ t }]],
  }));

  // Rotated labels (90° CCW) centred on the two shaded bands. x/y is the band
  // centre. Band 1: x 54.71–81.3 (centre 68). Band 2: x 141.48–143.95 (centre 142.7).
  // Both bands span y 294.14–372.2 (centre 333).
  const bands = [
    {
      x: 53.2,
      y: 316,
      anchor: "middle",
      rotate: -90,
      size: "--fs-xs",
      color: GREY,
      lines: [[{ t: "Removal of fuel subsidies" }]],
    },
    {
      x: 140.2,
      y: 358,
      anchor: "middle",
      rotate: -90,
      size: "--fs-xs",
      color: GREY,
      lines: [[{ t: "Iran War" }]],
    },
  ];

  const blocks = [
    B.headline,
    B.deck,
    B.callout,
    B.source,
    ...leftAxis,
    ...rightAxis,
    ...xAxis,
    ...bands,
  ];

  // Body-copy region in the top-left blank: x/y = top-left corner, w/h in
  // viewBox units. Sits above the headline (y≈268) and left of the callout
  // (x≈105). Text flows/wraps within this width.
  const BODY = { x: 0, y: 7, w: 98, h: 240, size: "--fs-base" };

  // --- helpers -------------------------------------------------------------
  // --noil-scale (default 1, overridden per breakpoint in <style>) multiplies
  // every font-size, so type can be tuned per screen size from one knob.
  const htmlFont = (v) => `calc(var(${v}, 16px) * var(--noil-scale, 1))`;
  const anchorToTextAlign = { start: "left", middle: "center", end: "right" };
  // % coords for the overlay, mapping viewBox units onto the rendered box.
  const pctX = (x) => (x / VB_W) * 100;
  const pctY = (y) => ((y - VB_Y0) / (VB_H - VB_Y0)) * 100;
  const translateForAnchor = { start: "0", middle: "-50%", end: "-100%" };
  // Rotated blocks centre on their point; plain blocks anchor by baseline.
  const htmlTransform = (b) =>
    b.rotate
      ? `translate(-50%, -50%) rotate(${b.rotate}deg)`
      : `translate(${translateForAnchor[b.anchor]}, -0.8em)`;
  // lh (viewBox units) -> the px baseline-gap it represents at the reference
  // width, applied as line spacing in the overlay.
  const lineGapPx = (b) => (b.lh ?? 5) * (REF_WIDTH / VB_W);
</script>

<div class="noil">
  <svg
    viewBox="0 {VB_Y0} {VB_W} {VB_H - VB_Y0}"
    role="img"
    aria-label="Nigerian solar imports have tracked petrol prices, with a 1.4 GW spike in March 2026."
  >
    <!-- static chart art (bars, lines, gridlines, shaded bands) -->
    {@html chartInner}
  </svg>

  <div class="noil-overlay" aria-hidden="true">
    {#each blocks as b}
      <div
        class="noil-block"
        style="
             left:{pctX(b.x)}%;
             top:{pctY(b.y)}%;
             transform:{htmlTransform(b)};
             text-align:{anchorToTextAlign[b.anchor]};
             color:{b.color ?? INK};
             font-size:{htmlFont(b.size)};
             {b.wrap ? `white-space:normal; width:${(b.w / VB_W) * 100}%;` : ''}"
      >
        {#each b.lines as line, li}
          <div
            class="noil-line"
            style="{li
              ? `margin-top:calc(${lineGapPx(b)}px - 1em);`
              : ''}{line[0]?.size
              ? `font-size:${htmlFont(line[0].size)};`
              : ''}{b.wrap ? 'line-height:1.35;' : ''}"
          >
            {#each line as run}<span
                style="{run.w ? `font-weight:${run.w};` : ''}{run.c
                  ? `color:${run.c};`
                  : ''}">{run.t}</span
              >{/each}
          </div>
        {/each}
      </div>
    {/each}
  </div>

  {#if body}
    <div
      class="noil-body noil-body--overlay"
      style="left:{pctX(BODY.x)}%; top:{pctY(BODY.y)}%; width:{(BODY.w / VB_W) *
        100}%; font-size:{htmlFont(BODY.size)}; color:{INK};"
    >
      {@html body}
    </div>
  {/if}
</div>

<style>
  .noil {
    position: relative;
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    /* Multiplies every font-size; override per breakpoint below. */
    --noil-scale: 1;
    /* Never let an overlay label push the page past 100vw on mobile. */
    overflow-x: clip;
    font-family: var(
      --primary-font,
      "Innovator Grotesk",
      -apple-system,
      sans-serif
    );
    line-height: 1;
  }
  .noil svg {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
  }

  /* HTML overlay covers the SVG box exactly; % coords map 1:1 to the viewBox. */
  .noil-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }
  .noil-block {
    position: absolute;
    white-space: nowrap;
  }
  /* line-height:1 so per-line margin-top is the exact baseline gap. */
  .noil-line {
    line-height: 1;
  }

  /* Body copy: an absolutely-placed overlay that wraps within its region. */
  .noil-body {
    line-height: 1.4;
  }
  .noil-body--overlay {
    position: absolute;
  }
  .noil-body :global(p) {
    margin: 0 0 0.7em;
  }
  .noil-body :global(p:last-child) {
    margin-bottom: 0;
  }

  /* Responsive type: one knob per breakpoint scales all font-sizes together.
     Tune to taste (site breakpoints are 768px / 480px). <1 = smaller, >1 =
     larger — text is fixed px, so shrinking a little on small screens keeps it
     in proportion with the chart. */
  @media (max-width: 768px) {
    .noil {
      --noil-scale: 0.9;
    }
  }
  @media (max-width: 480px) {
    .noil {
      --noil-scale: 0.8;
    }
  }
</style>
