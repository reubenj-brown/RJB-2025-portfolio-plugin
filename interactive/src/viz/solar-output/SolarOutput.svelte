<script>
  import { scaleUtc, scaleLinear } from 'd3-scale';
  import { line, curveMonotoneX } from 'd3-shape';
  import { extent, max } from 'd3-array';
  import { utcParse, utcFormat } from 'd3-time-format';
  import { loadData } from '../../lib/data/load.js';
  import { resize } from '../../lib/charts/responsive.js';

  // Props arrive from the [reuben_viz] data-* attributes via mount.js.
  let { src = '', title = 'Solar output' } = $props();

  let rows = $state([]);
  let error = $state(null);
  let width = $state(640);

  const height = 360;
  const margin = { top: 24, right: 20, bottom: 32, left: 48 };

  const parseMonth = utcParse('%Y-%m');
  const fmtAxis = utcFormat("%b ’%y");

  // Load + normalise into [{ date: Date, value: number }] sorted by date.
  // Accepts a few likely column names so the same island works across datasets.
  $effect(() => {
    if (!src) return;
    let alive = true;
    loadData(src)
      .then((data) => {
        if (!alive) return;
        rows = data
          .map((d) => ({
            date: d.date instanceof Date ? d.date : parseMonth(d.month ?? d.date),
            value: +(d.value ?? d.mma ?? d.y),
          }))
          .filter((d) => d.date && Number.isFinite(d.value))
          .sort((a, b) => a.date - b.date);
      })
      .catch((e) => (error = e.message));
    return () => {
      alive = false;
    };
  });

  // 12-month moving average, computed client-side — the "data compiling" step.
  let series = $derived.by(() => {
    const out = [];
    for (let i = 0; i < rows.length; i++) {
      if (i < 11) continue; // need a full 12-month window
      const window = rows.slice(i - 11, i + 1);
      out.push({
        date: rows[i].date,
        value: window.reduce((sum, d) => sum + d.value, 0) / 12,
      });
    }
    return out;
  });

  let x = $derived(
    scaleUtc()
      .domain(extent(rows, (d) => d.date))
      .range([margin.left, width - margin.right])
  );
  let y = $derived(
    scaleLinear()
      .domain([0, max(rows, (d) => d.value) || 1])
      .nice()
      .range([height - margin.bottom, margin.top])
  );

  let monthlyPath = $derived(
    line()
      .x((d) => x(d.date))
      .y((d) => y(d.value))(rows)
  );
  let mmaPath = $derived(
    line()
      .curve(curveMonotoneX)
      .x((d) => x(d.date))
      .y((d) => y(d.value))(series)
  );
</script>

<figure class="solar-output" use:resize={(r) => (width = r.width)}>
  <figcaption class="solar-output__title">{title}</figcaption>

  {#if error}
    <p class="solar-output__msg solar-output__msg--error">Couldn’t load data: {error}</p>
  {:else if !rows.length}
    <p class="solar-output__msg">Loading…</p>
  {:else}
    <svg viewBox={`0 0 ${width} ${height}`} role="img" aria-label={title}>
      <!-- y gridlines + labels -->
      {#each y.ticks(5) as t}
        <g class="tick tick--y" transform={`translate(0,${y(t)})`}>
          <line x1={margin.left} x2={width - margin.right} />
          <text x={margin.left - 8} dy="0.32em">{t}</text>
        </g>
      {/each}

      <!-- x labels -->
      {#each x.ticks(6) as t}
        <text class="tick--x" x={x(t)} y={height - margin.bottom + 20}>{fmtAxis(t)}</text>
      {/each}

      <path class="series series--monthly" d={monthlyPath} />
      <path class="series series--mma" d={mmaPath} />
    </svg>

    <p class="solar-output__legend">
      <span class="swatch swatch--monthly"></span> Monthly
      <span class="swatch swatch--mma"></span> 12-month average
    </p>
  {/if}
</figure>

<style>
  .solar-output {
    margin: 0;
    width: 100%;
    font-family: var(--primary-font, system-ui, sans-serif);
    color: #222;
  }
  .solar-output__title {
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  svg {
    width: 100%;
    height: auto;
    display: block;
    overflow: visible;
  }
  .tick--y line {
    stroke: #e6e6e6;
    stroke-width: 1;
  }
  .tick--y text {
    fill: #777;
    font-size: 11px;
    text-anchor: end;
  }
  .tick--x {
    fill: #777;
    font-size: 11px;
    text-anchor: middle;
  }
  .series {
    fill: none;
  }
  .series--monthly {
    stroke: #cfcfcf;
    stroke-width: 1;
  }
  .series--mma {
    stroke: var(--highlight-color, #39e58f);
    stroke-width: 2.5;
  }
  .solar-output__legend {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: #555;
    margin-top: 0.5rem;
  }
  .swatch {
    display: inline-block;
    width: 14px;
    height: 3px;
    border-radius: 2px;
  }
  .swatch--monthly {
    background: #cfcfcf;
  }
  .swatch--mma {
    background: var(--highlight-color, #39e58f);
    margin-left: 0.6rem;
  }
  .solar-output__msg {
    color: #777;
    font-style: italic;
  }
  .solar-output__msg--error {
    color: #b00;
    font-style: normal;
  }
</style>
