<script>
  import { onMount } from "svelte";
  // D3 submodules (not the full d3 bundle) + topojson, replacing the original
  // CDN <script> tags.
  import { geoEquirectangular, geoPath } from "d3-geo";
  import { select } from "d3-selection";
  import { line as d3line } from "d3-shape";
  import { json as d3json } from "d3-fetch";
  import * as topojson from "topojson-client";
  // Voyage + cargo data, now imported as ES modules instead of global scripts.
  import { SHIP_PATH } from "./data.js";
  import { CARGO_VALUES } from "./cargo-values.js";

  let root; // the component's own DOM root (keeps lookups island-safe)

  onMount(() => {
    initMap(root);
  });

  // --- Original voyage-map logic, scoped to `rootEl` -----------------------
  // Ported near-verbatim from the standalone map.html. Changes: d3.X -> imported
  // submodules; document.getElementById -> rootEl.querySelector so multiple
  // instances never collide.
  function initMap(rootEl) {
    const svg = rootEl.querySelector("#dgs-map");
    const WIDTH = 1000;
    const HEIGHT = 500;
    const VIEWBOX_X = 137;
    const VIEWBOX_W = 810;
    const APRIL5 = new Date("2026-04-05T00:00:00").getTime();

    const projection = geoEquirectangular()
      .rotate([0, 0])
      .scale(160)
      .translate([WIDTH / 2, HEIGHT / 2]);

    const pathGen = geoPath(projection);

    const shipProjected = SHIP_PATH.map(function (p) {
      const proj = projection([p.lon, p.lat]);
      return {
        x: proj[0],
        y: proj[1],
        t: new Date(p.date).getTime(),
        date: p.date,
        destination: p.destination,
        lon: p.lon,
        lat: p.lat,
      };
    });

    for (var h = 0; h < shipProjected.length; h++) {
      var curr = shipProjected[h];
      var next = shipProjected[Math.min(h + 1, shipProjected.length - 1)];
      var dx = next.x - curr.x;
      var dy = next.y - curr.y;
      curr.heading = (Math.atan2(dx, -dy) * 180) / Math.PI;
    }

    var april5Idx = shipProjected.length;
    for (var i = 0; i < shipProjected.length; i++) {
      if (shipProjected[i].t >= APRIL5) {
        april5Idx = i;
        break;
      }
    }

    const shipBefore = SHIP_PATH.slice(
      0,
      Math.min(april5Idx + 1, SHIP_PATH.length),
    );

    var shipXMin = Infinity,
      shipXMax = -Infinity;
    shipProjected.forEach(function (p) {
      if (p.x < shipXMin) shipXMin = p.x;
      if (p.x > shipXMax) shipXMax = p.x;
    });

    const shipXAtApril5 =
      april5Idx < shipProjected.length ? shipProjected[april5Idx].x : shipXMax;

    // --- Load world basemap (still fetched from CDN at runtime) ---
    d3json("https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json")
      .then(function (world) {
        const countries = topojson.feature(world, world.objects.countries);
        const borders = topojson.mesh(world, world.objects.countries);

        select(svg)
          .select(".dgs-land-layer")
          .selectAll("path")
          .data(countries.features)
          .enter()
          .append("path")
          .attr("class", "dgs-land")
          .attr("d", pathGen);

        select(svg)
          .select(".dgs-borders-layer")
          .append("path")
          .attr("class", "dgs-borders")
          .attr("d", pathGen(borders));

        drawShipPaths();
      })
      .catch(function (err) {
        console.error("Failed to load world basemap", err);
        drawShipPaths();
      });

    function buildPathD(points) {
      if (!Array.isArray(points) || points.length === 0) return "";
      const segments = [];
      var current = [];
      var prevLon = null;
      for (var i = 0; i < points.length; i++) {
        var p = points[i];
        if (prevLon !== null && Math.abs(p.lon - prevLon) > 180) {
          if (current.length) segments.push(current);
          current = [];
        }
        current.push([p.lon, p.lat]);
        prevLon = p.lon;
      }
      if (current.length) segments.push(current);

      var line = d3line()
        .x(function (d) {
          return projection(d)[0];
        })
        .y(function (d) {
          return projection(d)[1];
        });
      return segments.map(line).join(" ");
    }

    function drawShipPaths() {
      rootEl
        .querySelector("#dgs-voyage-path-before")
        .setAttribute("d", buildPathD(shipBefore));
    }

    // --- Price chart (embedded in main SVG) ---
    const CHART_Y_TOP = 400;
    const CHART_Y_BOTTOM = 485;
    const CHART_PAD = 8;

    const t0 = shipProjected.length ? shipProjected[0].t : 0;

    var allValues = (typeof CARGO_VALUES !== "undefined" ? CARGO_VALUES : [])
      .map(function (p) {
        return {
          t: new Date(p.date).getTime(),
          price: p.cargo_value_usd,
          jkm: p.jkm_price,
          mt: p.remaining_mt,
        };
      })
      .sort(function (a, b) {
        return a.t - b.t;
      });
    var chartPrices = allValues.filter(function (p) {
      return p.t >= t0 && p.t <= APRIL5;
    });

    var priceMin = 0,
      priceMax = 1;
    if (chartPrices.length) {
      priceMin = Infinity;
      priceMax = -Infinity;
      chartPrices.forEach(function (p) {
        if (p.price < priceMin) priceMin = p.price;
        if (p.price > priceMax) priceMax = p.price;
      });
      if (priceMin === priceMax) {
        priceMin -= 0.5;
        priceMax += 0.5;
      }
    }

    function chartY(price) {
      var frac = (price - priceMin) / (priceMax - priceMin);
      return (
        CHART_Y_BOTTOM -
        CHART_PAD -
        frac * (CHART_Y_BOTTOM - CHART_Y_TOP - 2 * CHART_PAD)
      );
    }

    function formatCargoValue(usd) {
      if (usd === null || usd === undefined || !isFinite(usd)) return "—";
      if (Math.abs(usd) >= 1e9) return "$" + (usd / 1e9).toFixed(2) + "B";
      if (Math.abs(usd) >= 1e6) return "$" + (usd / 1e6).toFixed(1) + "M";
      if (Math.abs(usd) >= 1e3) return "$" + (usd / 1e3).toFixed(0) + "K";
      return "$" + usd.toFixed(0);
    }

    function chartX(t) {
      if (APRIL5 === t0) return shipXMin;
      return shipXMin + ((t - t0) / (APRIL5 - t0)) * (shipXAtApril5 - shipXMin);
    }

    var chartPricePoints = chartPrices.map(function (p) {
      return { x: chartX(p.t), y: chartY(p.price), price: p.price, t: p.t };
    });

    (function drawChartPath() {
      if (!chartPricePoints.length) return;
      var d = chartPricePoints
        .map(function (p, i) {
          return (i === 0 ? "M" : "L") + p.x.toFixed(2) + "," + p.y.toFixed(2);
        })
        .join(" ");
      rootEl.querySelector("#dgs-chart-path").setAttribute("d", d);
    })();

    // --- Y-axis (JKM gas price) -------------------------------------------
    // The plotted line is cargo value, but cargo value = jkm_price * mt * 52,
    // so we can label the same vertical scale in JKM ($/MMBtu). The cargo mass
    // (mt) drifts ~4% over the voyage from boil-off, so we convert ticks using
    // the window's average mass — close enough for a readable axis.
    function niceTicks(lo, hi, count) {
      var span = hi - lo;
      if (!(span > 0)) return [];
      var rawStep = span / count;
      var mag = Math.pow(10, Math.floor(Math.log10(rawStep)));
      var norm = rawStep / mag;
      var cands = [1, 2, 2.5, 5, 10];
      var step = 10;
      for (var i = 0; i < cands.length; i++) {
        if (cands[i] >= norm) {
          step = cands[i];
          break;
        }
      }
      step *= mag;
      var ticks = [];
      for (
        var v = Math.ceil(lo / step) * step;
        v <= hi + step * 1e-9;
        v += step
      ) {
        ticks.push(Math.round(v / step) * step);
      }
      return ticks;
    }

    (function drawChartAxis() {
      if (!chartPrices.length) return;
      var axisG = rootEl.querySelector("#dgs-chart-axis");
      if (!axisG) return;

      var avgMt = 0;
      chartPrices.forEach(function (p) {
        avgMt += p.mt;
      });
      avgMt /= chartPrices.length;
      var perJkm = avgMt * 52; // cargo USD per $1/MMBtu of JKM

      var ticks = niceTicks(priceMin / perJkm, priceMax / perJkm, 5);
      var SVGNS = "http://www.w3.org/2000/svg";

      ticks.forEach(function (v) {
        var y = chartY(v * perJkm);
        if (y < CHART_Y_TOP || y > CHART_Y_BOTTOM) return;

        var gridLine = document.createElementNS(SVGNS, "line");
        gridLine.setAttribute("class", "dgs-axis-grid");
        gridLine.setAttribute("x1", shipXMin.toFixed(2));
        gridLine.setAttribute("x2", shipXAtApril5.toFixed(2));
        gridLine.setAttribute("y1", y.toFixed(2));
        gridLine.setAttribute("y2", y.toFixed(2));
        axisG.appendChild(gridLine);

        var label = document.createElementNS(SVGNS, "text");
        label.setAttribute("class", "dgs-axis-label");
        label.setAttribute("x", (shipXMin + 3).toFixed(2));
        label.setAttribute("y", (y - 3).toFixed(2));
        label.textContent = "$" + (v % 1 === 0 ? v.toFixed(0) : v.toFixed(1));
        axisG.appendChild(label);
      });

      var title = document.createElementNS(SVGNS, "text");
      title.setAttribute("class", "dgs-axis-title");
      title.setAttribute("x", (shipXMin + 3).toFixed(2));
      title.setAttribute("y", (CHART_Y_TOP - 4).toFixed(2));
      title.textContent = "JKM gas price, $/MMBtu";
      axisG.appendChild(title);
    })();

    // --- Hover interaction ---
    var priceValueEl = rootEl.querySelector("#dgs-price-value");
    var locationValueEl = rootEl.querySelector("#dgs-location-value");
    var dateLabelEl = rootEl.querySelector("#dgs-date-label");
    var hoverMarker = rootEl.querySelector("#dgs-hover-marker");
    var chartHoverMarker = rootEl.querySelector("#dgs-chart-hover-marker");

    var shipByTime = shipProjected.slice().sort(function (a, b) {
      return a.t - b.t;
    });

    function formatDate(iso) {
      var d = new Date(iso);
      return d.toLocaleDateString(undefined, { month: "long", day: "numeric" });
    }

    function interpolatePrice(t) {
      if (!chartPrices.length) return null;
      if (t <= chartPrices[0].t) return chartPrices[0].price;
      if (t >= chartPrices[chartPrices.length - 1].t)
        return chartPrices[chartPrices.length - 1].price;
      var lo = 0,
        hi = chartPrices.length - 1;
      while (lo < hi - 1) {
        var mid = (lo + hi) >>> 1;
        if (chartPrices[mid].t <= t) lo = mid;
        else hi = mid;
      }
      var p0 = chartPrices[lo],
        p1 = chartPrices[hi];
      var frac = (t - p0.t) / (p1.t - p0.t);
      return p0.price + frac * (p1.price - p0.price);
    }

    function interpolateShip(t) {
      if (!shipByTime.length) return null;
      if (t <= shipByTime[0].t) return shipByTime[0];
      if (t >= shipByTime[shipByTime.length - 1].t)
        return shipByTime[shipByTime.length - 1];
      var lo = 0,
        hi = shipByTime.length - 1;
      while (lo < hi - 1) {
        var mid = (lo + hi) >>> 1;
        if (shipByTime[mid].t <= t) lo = mid;
        else hi = mid;
      }
      var p0 = shipByTime[lo],
        p1 = shipByTime[hi];
      var frac = p1.t === p0.t ? 0 : (t - p0.t) / (p1.t - p0.t);
      return {
        x: p0.x + frac * (p1.x - p0.x),
        y: p0.y + frac * (p1.y - p0.y),
        heading: p0.heading,
        destination: frac < 0.5 ? p0.destination : p1.destination,
        t: t,
      };
    }

    function mouseXToTime(mouseX) {
      if (shipXAtApril5 === shipXMin) return t0;
      return (
        t0 + ((mouseX - shipXMin) / (shipXAtApril5 - shipXMin)) * (APRIL5 - t0)
      );
    }

    function handleMove(evt) {
      if (!shipByTime.length) return;

      var svgRect = svg.getBoundingClientRect();
      var mouseX =
        VIEWBOX_X + ((evt.clientX - svgRect.left) / svgRect.width) * VIEWBOX_W;

      if (mouseX < shipXMin || mouseX > shipXAtApril5) {
        handleLeave();
        return;
      }

      var t = mouseXToTime(mouseX);
      var price = interpolatePrice(t);
      var shipPt = interpolateShip(t);

      locationValueEl.textContent = (shipPt && shipPt.destination) || "—";
      dateLabelEl.textContent = formatDate(new Date(t).toISOString());
      priceValueEl.textContent = formatCargoValue(price);

      if (shipPt) {
        hoverMarker.setAttribute(
          "transform",
          "translate(" +
            shipPt.x.toFixed(2) +
            "," +
            shipPt.y.toFixed(2) +
            ") rotate(" +
            shipPt.heading.toFixed(1) +
            ")",
        );
        hoverMarker.style.display = "";
      } else {
        hoverMarker.style.display = "none";
      }

      if (price !== null) {
        chartHoverMarker.setAttribute("cx", mouseX.toFixed(2));
        chartHoverMarker.setAttribute("cy", chartY(price).toFixed(2));
        chartHoverMarker.style.display = "";
      } else {
        chartHoverMarker.style.display = "none";
      }
    }

    function handleLeave() {
      hoverMarker.style.display = "none";
      chartHoverMarker.style.display = "none";
      dateLabelEl.textContent = "";
      priceValueEl.textContent = "—";
      locationValueEl.textContent = "—";
    }

    svg.addEventListener("mousemove", handleMove);
    svg.addEventListener("mouseleave", handleLeave);

    // --- SCROLL STEPS GO HERE (next phase) -------------------------------
    // When we add the 3–6 narrative steps, import createScroller from
    // ../../lib/scroll/scroller.js, wrap the prose steps in a sibling element,
    // and on each onStep(index) drive the ship/chart to a fixed timestamp
    // (reuse interpolateShip / interpolatePrice above instead of the mouse).
  }
</script>

<div class="dgs-map-container" bind:this={root}>
  <div class="dgs-map-figure">
    <div class="dgs-date-label" id="dgs-date-label"></div>
    <svg
      id="dgs-map"
      viewBox="137 0 810 500"
      preserveAspectRatio="xMidYMid meet"
      aria-label="Diamond Gas Sakura voyage map"
    >
      <rect class="dgs-ocean" x="137" y="0" width="810" height="500" />
      <g class="dgs-land-layer"></g>
      <g class="dgs-borders-layer"></g>
      <path class="dgs-path" id="dgs-voyage-path-before" d="" />
      <g class="dgs-chart-axis" id="dgs-chart-axis"></g>
      <g class="dgs-chart-layer">
        <path class="dgs-chart-line" id="dgs-chart-path" d="" />
      </g>
      <g class="dgs-hover-layer">
        <polygon
          class="dgs-ship-arrow"
          id="dgs-hover-marker"
          points="0,-8 5,6 0,3 -5,6"
          style="display:none"
        />
        <circle
          class="dgs-chart-hover-dot"
          id="dgs-chart-hover-marker"
          r="5"
          cx="0"
          cy="0"
          style="display:none"
        />
      </g>
    </svg>
  </div>

  <div class="dgs-readout">
    <div class="dgs-readout-cell dgs-left">
      <div class="dgs-readout-item">
        <div class="dgs-readout-label">Origin</div>
        <div class="dgs-readout-value">Cameron, La.</div>
      </div>
      <div class="dgs-readout-item">
        <div class="dgs-readout-label">Cargo cost</div>
        <div class="dgs-readout-value">$23.7M</div>
      </div>
    </div>
    <div class="dgs-readout-cell dgs-right">
      <div class="dgs-readout-item">
        <div class="dgs-readout-label">Cargo value</div>
        <div class="dgs-readout-value" id="dgs-price-value">—</div>
      </div>
      <div class="dgs-readout-item">
        <div class="dgs-readout-label">Destination</div>
        <div class="dgs-readout-value" id="dgs-location-value">—</div>
      </div>
    </div>
  </div>
  <div class="dgs-source">
    Source: Kpler; BloombergNEF; S&amp;P Global Energy
  </div>
</div>

<style>
  .dgs-map-container {
    width: 100%;
    max-width: 100vw;
    margin: 0 auto;
    font-family: var(
      --primary-font,
      system-ui,
      -apple-system,
      "Segoe UI",
      Helvetica,
      Arial,
      sans-serif
    );
  }

  .dgs-map-figure {
    position: relative;
    width: 100%;
  }

  .dgs-map-figure svg {
    display: block;
    width: 100%;
    height: auto;
    cursor: crosshair;
  }

  .dgs-ocean {
    fill: #f5f9fc;
  }

  /* .dgs-land and .dgs-borders are added to the SVG by D3 at runtime, so
     Svelte can't see them in the markup. Scope through the static container
     with :global() so the styles survive compilation and still apply only
     inside this component. */
  .dgs-map-container :global(.dgs-land) {
    fill: #ffffff;
    stroke: none;
  }

  .dgs-map-container :global(.dgs-borders) {
    fill: none;
    stroke: #808080;
    stroke-opacity: 0.5;
    stroke-width: 0.5px;
    vector-effect: non-scaling-stroke;
  }

  .dgs-path {
    fill: none;
    stroke: #ff193b;
    stroke-width: 1.8px;
    stroke-linejoin: round;
    stroke-linecap: round;
    vector-effect: non-scaling-stroke;
  }

  .dgs-ship-arrow {
    fill: #ff193b;
    stroke: #ffffff;
    stroke-width: 1px;
    stroke-linejoin: round;
  }

  .dgs-chart-hover-dot {
    fill: #ff193b;
    stroke: #ffffff;
    stroke-width: 1.5px;
  }

  .dgs-chart-line {
    fill: none;
    stroke: #ff193b;
    stroke-width: 1.8px;
    stroke-linejoin: round;
    stroke-linecap: round;
    vector-effect: non-scaling-stroke;
  }

  /* Axis gridlines + labels are created by JS at runtime, so scope them with
     :global() to survive Svelte's compile-time CSS pruning. */
  .dgs-map-container :global(.dgs-axis-grid) {
    stroke: #d8dee3;
    stroke-width: 1px;
    stroke-dasharray: 2 3;
    vector-effect: non-scaling-stroke;
  }

  .dgs-map-container :global(.dgs-axis-label),
  .dgs-map-container :global(.dgs-axis-title) {
    fill: #808080;
    font-size: 10px;
    font-weight: 600;
  }

  .dgs-map-container :global(.dgs-axis-title) {
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .dgs-date-label {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 36px;
    font-weight: 600;
    color: #111;
    line-height: 1.1;
    pointer-events: none;
    z-index: 10;
  }

  .dgs-readout {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0 16px;
    margin-top: 0px;
    gap: 24px;
  }

  .dgs-readout-cell {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .dgs-readout-cell.dgs-right {
    text-align: right;
  }
  .dgs-readout-item {
    display: block;
  }

  .dgs-readout-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #808080;
    margin-bottom: 4px;
  }

  .dgs-readout-value {
    font-size: 36px;
    font-weight: 600;
    color: #111;
    line-height: 1.1;
  }

  .dgs-source {
    text-align: center;
    font-size: 11px;
    color: #999;
    padding: 12px 8px 8px;
  }
</style>
