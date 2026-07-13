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
  import rawSvg from './nigeria_solar_vs_petrol_minify.svg?raw';

  let { mode = 'svg' } = $props();

  // --- coordinate space ----------------------------------------------------
  const VB_W = 155.99;          // original SVG width — the fs↔px anchor
  const VB_H = 410;             // original 389.37, extended for the source note
  const REF_WIDTH = 760;        // desktop width the fs px-vars are calibrated to
  const K = VB_W / REF_WIDTH;   // 0.2052 — user-units per px at the reference width

  // Chart art with its outer <svg> wrapper stripped, so it can be injected
  // straight into our own <svg> and share the coordinate system.
  const chartInner = rawSvg
    .replace(/^[\s\S]*?<svg[^>]*>/, '')
    .replace(/<\/svg>\s*$/, '');

  // Palette + type tokens resolve from the theme's :root on the live site;
  // fallbacks keep it rendering in isolation (local dev, this repo).
  const COBALT = 'var(--cr-cobalt, #003cff)';
  const SOIL   = 'var(--cr-soil, #805533)';
  const GREY   = 'var(--cr-50grey, #808080)';
  const INK    = 'var(--text-color, #000)';

  // Key y-lines pulled from the art: dashed gridlines + baseline axis.
  const Y_300 = 294.14, Y_200 = 320.16, Y_100 = 346.18, Y_0 = 372.33;
  // Year-band centres (ticks at 11.44 / 42.91 / 74.38 / 105.85 / 137.32).
  const YEAR = [27.18, 58.65, 90.12, 121.59];

  // --- text blocks (single source of truth for both renderers) -------------
  // Each block: an anchor point (x,y = baseline of the FIRST line), an anchor
  // (start|middle|end), a font-size var, a line-height in user-units, and
  // `lines` — each line an array of runs {t, w?(weight), c?(colour)}.
  // Positions are in viewBox units; nudge freely, there is no live preview.
  const B = {
    headline: {
      x: 2, y: 62, anchor: 'start', size: '--fs-4xl', lh: 9,
      lines: [[{ t: 'Oil for Sun', w: 600 }]],
    },
    deck: {
      x: 2, y: 73, anchor: 'start', size: '--fs-base', lh: 5,
      lines: [
        [{ t: 'Surges in Nigerian ' }, { t: 'solar imports', w: 600, c: COBALT }],
        [{ t: 'have tracked ' }, { t: 'petrol prices', w: 600, c: SOIL }],
      ],
    },
    callout: {
      x: 104.96, y: 15, anchor: 'start', size: '--fs-xs', lh: 4.4,
      lines: [
        [{ t: '1.4 GW', w: 600, c: COBALT, size: '--fs-2xl' }],
        [{ t: 'of Chinese solar panels and' }],
        [{ t: 'cells imported to ' }, { t: 'Nigeria', w: 600 }],
        [{ t: 'in March 2026' }],
      ],
    },
    source: {
      x: 2, y: 396, anchor: 'start', size: '--fs-3xs', lh: 5, color: GREY,
      lines: [
        [{ t: 'Data: Ember; Nigeria Petroleum Products Pricing Regulatory Agency' }],
        [{ t: 'Solar imports as three-month rolling average. Dots show monthly figures. Petrol price in US cents per liter.' }],
      ],
    },
  };

  // Axis tick labels are single-line, so generate them compactly.
  const leftAxis = [
    { x: 2, y: Y_300 - 2, anchor: 'start', size: '--fs-sm', lines: [[{ t: '300 MW', w: 600 }]] },
    { x: 2, y: Y_200 - 2, anchor: 'start', size: '--fs-sm', lines: [[{ t: '200' }]] },
    { x: 2, y: Y_100 - 2, anchor: 'start', size: '--fs-sm', lines: [[{ t: '100' }]] },
  ];
  const rightAxis = [
    { x: 154, y: Y_300 - 2, anchor: 'end', size: '--fs-sm', lines: [[{ t: '90¢', w: 600 }]] },
    { x: 154, y: Y_200 - 2, anchor: 'end', size: '--fs-sm', lines: [[{ t: '70' }]] },
    { x: 154, y: Y_100 - 2, anchor: 'end', size: '--fs-sm', lines: [[{ t: '50' }]] },
    { x: 154, y: Y_0 - 2,   anchor: 'end', size: '--fs-sm', lines: [[{ t: '30' }]] },
  ];
  const xAxis = ['2022', '’23', '’24', '’25'].map((t, i) => ({
    x: YEAR[i], y: 381, anchor: 'middle', size: '--fs-xs', lines: [[{ t }]],
  }));

  const blocks = [
    B.headline, B.deck, B.callout, B.source,
    ...leftAxis, ...rightAxis, ...xAxis,
  ];

  // --- helpers -------------------------------------------------------------
  const svgFont = (v) => `calc(var(${v}, 16px) * ${K})`;   // px-var -> user units
  const anchorToTextAlign = { start: 'left', middle: 'center', end: 'right' };
  // % coords for the HTML overlay.
  const pctX = (x) => (x / VB_W) * 100;
  const pctY = (y) => (y / VB_H) * 100;
  const translateForAnchor = { start: '0', middle: '-50%', end: '-100%' };
</script>

<div class="noil" class:noil--html={mode === 'html'}>
  <svg viewBox="0 0 {VB_W} {VB_H}" role="img"
       aria-label="Nigerian solar imports have tracked petrol prices, with a 1.4 GW spike in March 2026.">
    <!-- static chart art (bars, lines, gridlines, Iran War marker) -->
    {@html chartInner}

    {#if mode === 'svg'}
      {#each blocks as b}
        <text x={b.x} y={b.y}
              text-anchor={b.anchor}
              fill={b.color ?? INK}
              font-size={svgFont(b.size)}>
          {#each b.lines as line, li}
            <tspan x={b.x} dy={li === 0 ? 0 : (b.lh ?? 5)}
                   font-size={line[0]?.size ? svgFont(line[0].size) : null}>
              {#each line as run}<tspan
                  font-weight={run.w ?? null}
                  fill={run.c ?? null}>{run.t}</tspan>{/each}
            </tspan>
          {/each}
        </text>
      {/each}
    {/if}
  </svg>

  {#if mode === 'html'}
    <div class="noil-overlay" aria-hidden="true">
      {#each blocks as b}
        <div class="noil-block"
             style="
               left:{pctX(b.x)}%;
               top:{pctY(b.y)}%;
               transform:translate({translateForAnchor[b.anchor]}, -0.8em);
               text-align:{anchorToTextAlign[b.anchor]};
               color:{b.color ?? INK};
               font-size:var({b.size}, 16px);">
          {#each b.lines as line}
            <div class="noil-line"
                 style={line[0]?.size ? `font-size:var(${line[0].size}, 16px)` : null}>
              {#each line as run}<span
                  style="{run.w ? `font-weight:${run.w};` : ''}{run.c ? `color:${run.c};` : ''}"
                  >{run.t}</span>{/each}
            </div>
          {/each}
        </div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .noil {
    position: relative;
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    font-family: var(--primary-font, 'Innovator Grotesk', -apple-system, sans-serif);
    line-height: 1;
  }
  .noil svg {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
  }
  /* SVG-mode text inherits the family; weights come from the runs. */
  .noil svg text { font-family: inherit; }

  /* HTML overlay covers the SVG box exactly; % coords map 1:1 to the viewBox. */
  .noil-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }
  .noil-block {
    position: absolute;
    white-space: nowrap;
    line-height: 1.15;
  }
</style>
