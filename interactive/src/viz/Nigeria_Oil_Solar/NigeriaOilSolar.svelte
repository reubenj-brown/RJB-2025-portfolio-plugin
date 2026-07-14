<script>
  // Static chart art lives in the SVG (paths only — no <text>). Every label is
  // drawn here, on top of the art, in the SVG's own 155.99-wide coordinate
  // space. Two rendering modes let us compare type behaviour on the live site:
  //
  //   mode="svg"  — labels are SVG <text>. They sit in the chart's coordinate
  //                 space and SCALE with the chart. font-size is written as
  //                 calc(var(--fs-*) * K) so a label hits its true px size at
  //                 the REF_WIDTH reference width (760px desktop) and scales
  //                 proportionally at other widths.
  //   mode="html" — labels are an HTML overlay positioned by %. font-size is
  //                 var(--fs-*) in fixed px, so type stays a constant pixel
  //                 size no matter how wide the chart renders. Alignment to
  //                 chart features is approximate (no shared coordinate space).
  //
  // Switch per-embed with [reuben_viz id="Nigeria_Oil_Solar" mode="html"].
  import rawSvg from "./nigeria_solar_vs_petrol_minify.svg?raw";

  // `body` is optional HTML (enclosed shortcode content) shown in the top-left
  // blank; it wraps naturally (foreignObject in svg mode, an overlay in html).
  let { mode = "svg", body = "" } = $props();

  // --- coordinate space ----------------------------------------------------
  const VB_W = 155.99; // original SVG width — the fs↔px anchor
  const VB_H = 385; // matches the original SVG art bounds (source note fits within)
  const REF_WIDTH = 760; // desktop width the fs px-vars are calibrated to
  const K = VB_W / REF_WIDTH; // 0.2052 — user-units per px at the reference width

  // Chart art with its outer <svg> wrapper stripped, so it can be injected
  // straight into our own <svg> and share the coordinate system.
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

  // --- text blocks (single source of truth for both renderers) -------------
  // Each block: an anchor point (x,y = baseline of the FIRST line), an anchor
  // (start|middle|end), a font-size var, a line-height in user-units, and
  // `lines` — each line an array of runs {t, w?(weight), c?(colour)}.
  // Positions are in viewBox units; nudge freely, there is no live preview.
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
      size: "--fs-xs",
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
      size: "--fs-2xs",
      lh: 3.2,
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
    size: "--fs-xs",
    lines: [[{ t }]],
  }));

  // Rotated labels (90° CCW) centred on the two shaded bands. x/y is the band
  // centre; `rotate` + `db:central` centre the text on that point.
  // Band 1: x 54.71–81.3 (centre 68). Band 2: x 141.48–143.95 (centre 142.7).
  // Both bands span y 294.14–372.2 (centre 333).
  const bands = [
    {
      x: 53.2,
      y: 316,
      anchor: "middle",
      db: "central",
      rotate: -90,
      size: "--fs-xs",
      color: GREY,
      lines: [[{ t: "Removal of fuel subsidies" }]],
    },
    {
      x: 140.2,
      y: 358,
      anchor: "middle",
      db: "central",
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
  const BODY = { x: 2, y: 18, w: 98, h: 240, size: "--fs-base" };

  // --- helpers -------------------------------------------------------------
  const svgFont = (v) => `calc(var(${v}, 16px) * ${K})`; // px-var -> user units
  const anchorToTextAlign = { start: "left", middle: "center", end: "right" };
  // % coords for the HTML overlay.
  const pctX = (x) => (x / VB_W) * 100;
  const pctY = (y) => (y / VB_H) * 100;
  const translateForAnchor = { start: "0", middle: "-50%", end: "-100%" };
  // Rotated blocks centre on their point; plain blocks anchor by baseline.
  const htmlTransform = (b) =>
    b.rotate
      ? `translate(-50%, -50%) rotate(${b.rotate}deg)`
      : `translate(${translateForAnchor[b.anchor]}, -0.8em)`;
  // lh is in viewBox units; convert to the px baseline-gap it represents at the
  // reference width so the HTML overlay matches SVG mode's line spacing.
  const lineGapPx = (b) => (b.lh ?? 5) * (REF_WIDTH / VB_W);
</script>

<div class="noil" class:noil--html={mode === "html"}>
  <svg
    viewBox="0 0 {VB_W} {VB_H}"
    role="img"
    aria-label="Nigerian solar imports have tracked petrol prices, with a 1.4 GW spike in March 2026."
  >
    <!-- static chart art (bars, lines, gridlines, Iran War marker) -->
    {@html chartInner}

    {#if mode === "svg"}
      {#each blocks as b}
        <text
          x={b.x}
          y={b.y}
          text-anchor={b.anchor}
          dominant-baseline={b.db ?? null}
          transform={b.rotate ? `rotate(${b.rotate} ${b.x} ${b.y})` : null}
          fill={b.color ?? INK}
          font-size={svgFont(b.size)}
        >
          {#each b.lines as line, li}
            <tspan
              x={b.x}
              dy={li === 0 ? 0 : (b.lh ?? 5)}
              font-size={line[0]?.size ? svgFont(line[0].size) : null}
            >
              {#each line as run}<tspan
                  font-weight={run.w ?? null}
                  fill={run.c ?? null}>{run.t}</tspan
                >{/each}
            </tspan>
          {/each}
        </text>
      {/each}

      {#if body}
        <foreignObject x={BODY.x} y={BODY.y} width={BODY.w} height={BODY.h}>
          <div
            xmlns="http://www.w3.org/1999/xhtml"
            class="noil-body"
            style="font-size:calc(var({BODY.size}, 16px) * {K}); color:{INK};"
          >
            {@html body}
          </div>
        </foreignObject>
      {/if}
    {/if}
  </svg>

  {#if mode === "html"}
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
               font-size:var({b.size}, 16px);"
        >
          {#each b.lines as line, li}
            <div
              class="noil-line"
              style="{li
                ? `margin-top:calc(${lineGapPx(b)}px - 1em);`
                : ''}{line[0]?.size
                ? `font-size:var(${line[0].size}, 16px);`
                : ''}"
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
        style="left:{pctX(BODY.x)}%; top:{pctY(BODY.y)}%; width:{(BODY.w /
          VB_W) *
          100}%; font-size:var({BODY.size}, 16px); color:{INK};"
      >
        {@html body}
      </div>
    {/if}
  {/if}
</div>

<style>
  .noil {
    position: relative;
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
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
  /* SVG-mode text inherits the family; weights come from the runs. */
  .noil svg text {
    font-family: inherit;
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

  /* Body copy: wraps within its region. In svg mode it lives in a
     foreignObject (scales); in html mode it's an absolutely-placed overlay. */
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
</style>
