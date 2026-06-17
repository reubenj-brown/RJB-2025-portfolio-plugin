<script>
  import { onMount } from "svelte";
  // D3 submodules (not the full d3 bundle) + topojson.
  import { geoEquirectangular, geoPath } from "d3-geo";
  import { select } from "d3-selection";
  import { line as d3line } from "d3-shape";
  import { json as d3json } from "d3-fetch";
  import * as topojson from "topojson-client";
  // Voyage track + daily cargo/price data, imported as ES modules.
  import { SHIP_PATH } from "./data.js";
  import { CARGO_VALUES } from "./cargo-values.js";

  let root; // component's own DOM root (keeps lookups island-safe)

  // --- Narrative steps -----------------------------------------------------
  // Constant date rate: scroll progress maps linearly from T_START to T_END.
  // Each prose beat is anchored to its real date, so the active step always
  // matches the ticked date. Map cues advance by `stage` (cumulative).
  const STEPS = [
    {
      date: "2026-02-24",
      prose:
        "On February 24, the M/V Diamond Gas Sakura left Cameron, Louisiana, carrying enough fuel to power its destination city, Nagoya, for a week.",
    },
    {
      date: "2026-02-27",
      prose:
        "Energy markets were in a calm period entering spring, and the seven-week journey should have been uneventful.",
    },
    {
      date: "2026-02-28",
      prose:
        "But four days into the voyage, the U.S. and Israel launched a war on Iran. In retaliation, the Islamic Republic blockaded the Strait of Hormuz and cut off the world from 20 percent of its L.N.G.",
    },
    {
      date: "2026-03-04",
      prose: "The Diamond Gas Sakura’s cargo was suddenly a lot more valuable.",
    },
    {
      date: "2026-03-06",
      prose:
        "As the ship traced Brazil’s northern coast in early March, the L.N.G.’s owner Mitsubishi flipped the fuel to Taiwan’s national energy company.",
    },
    {
      date: "2026-04-04",
      prose:
        "While the terms of this sale were not made public, markets data suggest the total arbitrage could have been worth more than $50 million.",
    },
  ];

  onMount(() => {
    return initMap(root);
  });

  // --- helpers -------------------------------------------------------------
  const clamp = (v, lo, hi) => (v < lo ? lo : v > hi ? hi : v);
  const lerp = (a, b, t) => a + (b - a) * t;
  const smoothstep = (t) => t * t * (3 - 2 * t);

  function initMap(rootEl) {
    const scrolly = rootEl; // the tall scroll container
    const svg = rootEl.querySelector("#dgs-map");
    const WIDTH = 1000;
    const HEIGHT = 500;
    const FULL_VB = { x: 137, y: 0, w: 810, h: 500 };
    const VB_ASPECT = FULL_VB.w / FULL_VB.h;

    const T_START = new Date("2026-02-24T00:00:00").getTime();
    const T_END = new Date("2026-04-04T23:59:59").getTime();

    const reduceMotion =
      typeof matchMedia === "function" &&
      matchMedia("(prefers-reduced-motion: reduce)").matches;

    const projection = geoEquirectangular()
      .rotate([0, 0])
      .scale(160)
      .translate([WIDTH / 2, HEIGHT / 2]);

    const pathGen = geoPath(projection);

    // --- Project the full track, attach time + heading ---
    const shipProjected = SHIP_PATH.map(function (p) {
      const proj = projection([p.lon, p.lat]);
      return {
        x: proj[0],
        y: proj[1],
        t: new Date(p.date).getTime(),
        lon: p.lon,
        lat: p.lat,
      };
    });
    for (let h = 0; h < shipProjected.length; h++) {
      const curr = shipProjected[h];
      const next = shipProjected[Math.min(h + 1, shipProjected.length - 1)];
      curr.heading =
        (Math.atan2(next.x - curr.x, -(next.y - curr.y)) * 180) / Math.PI;
    }

    // Only draw/reveal the track up to the final narrative beat (Apr 4).
    const pathPts = shipProjected.filter((p) => p.t <= T_END);
    const shipBefore = SHIP_PATH.filter(
      (p) => new Date(p.date).getTime() <= T_END,
    );

    // Cumulative projected length so we can reveal the path by date.
    const cumLen = [0];
    for (let i = 1; i < pathPts.length; i++) {
      const dx = pathPts[i].x - pathPts[i - 1].x;
      const dy = pathPts[i].y - pathPts[i - 1].y;
      cumLen[i] = cumLen[i - 1] + Math.hypot(dx, dy);
    }
    const totalProj = cumLen[cumLen.length - 1] || 1;

    function lenFracAt(t) {
      if (!pathPts.length) return 0;
      if (t <= pathPts[0].t) return 0;
      if (t >= pathPts[pathPts.length - 1].t) return 1;
      let lo = 0,
        hi = pathPts.length - 1;
      while (lo < hi - 1) {
        const mid = (lo + hi) >>> 1;
        if (pathPts[mid].t <= t) lo = mid;
        else hi = mid;
      }
      const frac = (t - pathPts[lo].t) / (pathPts[hi].t - pathPts[lo].t || 1);
      return lerp(cumLen[lo], cumLen[hi], frac) / totalProj;
    }

    const shipByTime = shipProjected.slice().sort((a, b) => a.t - b.t);
    function interpolateShip(t) {
      if (!shipByTime.length) return null;
      if (t <= shipByTime[0].t) return shipByTime[0];
      if (t >= shipByTime[shipByTime.length - 1].t)
        return shipByTime[shipByTime.length - 1];
      let lo = 0,
        hi = shipByTime.length - 1;
      while (lo < hi - 1) {
        const mid = (lo + hi) >>> 1;
        if (shipByTime[mid].t <= t) lo = mid;
        else hi = mid;
      }
      const p0 = shipByTime[lo],
        p1 = shipByTime[hi];
      const frac = p1.t === p0.t ? 0 : (t - p0.t) / (p1.t - p0.t);
      return {
        x: p0.x + frac * (p1.x - p0.x),
        y: p0.y + frac * (p1.y - p0.y),
        heading: p0.heading,
      };
    }

    // --- Daily cargo value + JKM price (stepped, never interpolated) ---
    const cargoSorted = CARGO_VALUES.map((d) => ({
      t: new Date(d.date).getTime(),
      jkm: d.jkm_price,
      cargo: d.cargo_value_usd,
    })).sort((a, b) => a.t - b.t);

    function cargoIdxAt(t) {
      let idx = 0;
      for (let i = 0; i < cargoSorted.length; i++) {
        if (cargoSorted[i].t <= t) idx = i;
        else break;
      }
      return idx;
    }

    const usdFmt = new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      maximumFractionDigits: 0,
    });

    function formatDate(ms) {
      return new Date(ms).toLocaleDateString(undefined, {
        month: "long",
        day: "numeric",
      });
    }

    // --- Build path d once world geometry / projection is ready ---
    function buildPathD(points) {
      if (!Array.isArray(points) || points.length === 0) return "";
      const segments = [];
      let current = [];
      let prevLon = null;
      for (let i = 0; i < points.length; i++) {
        const p = points[i];
        if (prevLon !== null && Math.abs(p.lon - prevLon) > 180) {
          if (current.length) segments.push(current);
          current = [];
        }
        current.push([p.lon, p.lat]);
        prevLon = p.lon;
      }
      if (current.length) segments.push(current);
      const ln = d3line()
        .x((d) => projection(d)[0])
        .y((d) => projection(d)[1]);
      return segments.map(ln).join(" ");
    }

    const voyagePath = rootEl.querySelector("#dgs-voyage-path");
    voyagePath.setAttribute("d", buildPathD(shipBefore));
    const pathLen = voyagePath.getTotalLength();
    voyagePath.style.strokeDasharray = pathLen + "px";
    voyagePath.style.strokeDashoffset = pathLen + "px";

    // --- Load world basemap (CDN at runtime), tag countries by id ---
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
          .attr("data-cid", (d) => String(d.id))
          .attr("d", pathGen);

        select(svg)
          .select(".dgs-borders-layer")
          .append("path")
          .attr("class", "dgs-borders")
          .attr("d", pathGen(borders));
      })
      .catch(function (err) {
        console.error("Failed to load world basemap", err);
      });

    // --- Map annotations (leader line + label), positioned via projection ---
    const SVGNS = "http://www.w3.org/2000/svg";
    const annoLayer = rootEl.querySelector("#dgs-annotation-layer");

    const ANNOS = [
      { id: "cameron", ll: [-93.32, 29.8], label: "Cameron, La.", dx: -10, dy: -16, anchor: "end" },
      { id: "nagoya", ll: [136.91, 35.18], label: "Nagoya, Japan", dx: -12, dy: -14, anchor: "end" },
      { id: "hormuz", ll: [56.25, 26.57], label: "Strait of Hormuz", dx: 6, dy: 22, anchor: "start" },
      { id: "kaohsiung", ll: [120.31, 22.62], label: "Kaohsiung", dx: 10, dy: 18, anchor: "start" },
    ];

    // Each annotation is built anchor-relative (children around 0,0) and the
    // group is translated to the projected point. A per-frame scale is applied
    // around that anchor so labels/leaders stay a constant size on screen even
    // as the map zooms (see updateAnnoScale).
    const annoGroups = [];

    ANNOS.forEach((a) => {
      const [px, py] = projection(a.ll);
      const down = a.dy > 0;
      const g = document.createElementNS(SVGNS, "g");
      g.setAttribute("id", "dgs-anno-" + a.id);
      g.setAttribute("class", "dgs-anno");
      g.setAttribute("transform", `translate(${px.toFixed(2)},${py.toFixed(2)})`);

      const dot = document.createElementNS(SVGNS, "circle");
      dot.setAttribute("class", "dgs-anno-dot");
      dot.setAttribute("cx", "0");
      dot.setAttribute("cy", "0");
      dot.setAttribute("r", "1.4");
      g.appendChild(dot);

      const line = document.createElementNS(SVGNS, "line");
      line.setAttribute("class", "dgs-anno-leader");
      line.setAttribute("x1", "0");
      line.setAttribute("y1", "0");
      line.setAttribute("x2", a.dx.toFixed(2));
      line.setAttribute("y2", a.dy.toFixed(2));
      g.appendChild(line);

      const text = document.createElementNS(SVGNS, "text");
      text.setAttribute("class", "dgs-anno-label");
      text.setAttribute("x", a.dx.toFixed(2));
      // Downward leaders place the label below the line end; upward above it.
      text.setAttribute("y", (a.dy + (down ? 7 : -3)).toFixed(2));
      text.setAttribute("text-anchor", a.anchor);
      text.textContent = a.label;
      g.appendChild(text);

      annoLayer.appendChild(g);
      annoGroups.push({ el: g, ax: px, ay: py });
    });

    // Iran country label (no leader; sits over the highlighted country).
    {
      const [ix, iy] = projection([53.0, 32.0]);
      const g = document.createElementNS(SVGNS, "g");
      g.setAttribute("id", "dgs-anno-iran");
      g.setAttribute("class", "dgs-anno");
      g.setAttribute("transform", `translate(${ix.toFixed(2)},${iy.toFixed(2)})`);
      const text = document.createElementNS(SVGNS, "text");
      text.setAttribute("class", "dgs-anno-label dgs-anno-country");
      text.setAttribute("x", "0");
      text.setAttribute("y", "0");
      text.setAttribute("text-anchor", "middle");
      text.textContent = "IRAN";
      g.appendChild(text);
      annoLayer.appendChild(g);
      annoGroups.push({ el: g, ax: ix, ay: iy });
    }

    let lastAnnoScale = -1;
    function updateAnnoScale(s) {
      if (Math.abs(s - lastAnnoScale) < 0.002) return;
      lastAnnoScale = s;
      for (const a of annoGroups) {
        a.el.setAttribute(
          "transform",
          `translate(${a.ax.toFixed(2)},${a.ay.toFixed(2)}) scale(${s.toFixed(3)})`,
        );
      }
    }

    // --- Opening zoom box (tight on the Gulf / Cameron) ---
    const [camX, camY] = projection([-93.32, 29.8]);
    const z0w = 320;
    const z0h = z0w / VB_ASPECT;
    const START_VB = {
      x: camX - z0w * 0.3,
      y: camY - z0h * 0.5,
      w: z0w,
      h: z0h,
    };
    // Closing zoom box (tight on Taiwan + Japan), mirroring the opening one.
    const twP = projection([120.31, 22.62]);
    const jpP = projection([136.91, 35.18]);
    const eMinX = Math.min(twP[0], jpP[0]),
      eMaxX = Math.max(twP[0], jpP[0]);
    const eMinY = Math.min(twP[1], jpP[1]),
      eMaxY = Math.max(twP[1], jpP[1]);
    let eW = eMaxX - eMinX + 120;
    let eH = eW / VB_ASPECT;
    if (eH < eMaxY - eMinY + 80) {
      eH = eMaxY - eMinY + 80;
      eW = eH * VB_ASPECT;
    }
    const END_VB = {
      x: (eMinX + eMaxX) / 2 - eW / 2,
      y: (eMinY + eMaxY) / 2 - eH / 2,
      w: eW,
      h: eH,
    };

    const ZOOM_IN_END = 0.1; // opening Gulf zoom completes
    const ZOOM_OUT_START = 0.85; // closing Taiwan/Japan zoom begins
    let curVBWidth = FULL_VB.w; // tracked so markers/labels can counter-scale
    let lastVB = "";
    function blend(a, b, e) {
      return {
        x: lerp(a.x, b.x, e),
        y: lerp(a.y, b.y, e),
        w: lerp(a.w, b.w, e),
        h: lerp(a.h, b.h, e),
      };
    }
    function setVB(b) {
      curVBWidth = b.w;
      const s = `${b.x.toFixed(1)} ${b.y.toFixed(1)} ${b.w.toFixed(1)} ${b.h.toFixed(1)}`;
      if (s === lastVB) return;
      lastVB = s;
      svg.setAttribute("viewBox", s);
    }
    function applyZoom(p) {
      if (reduceMotion) {
        setVB(FULL_VB);
      } else if (p <= ZOOM_IN_END) {
        setVB(blend(START_VB, FULL_VB, smoothstep(clamp(p / ZOOM_IN_END, 0, 1))));
      } else if (p >= ZOOM_OUT_START) {
        const e = smoothstep(clamp((p - ZOOM_OUT_START) / (1 - ZOOM_OUT_START), 0, 1));
        setVB(blend(FULL_VB, END_VB, e));
      } else {
        setVB(FULL_VB);
      }
    }

    // --- HUD elements ---
    const dateEl = rootEl.querySelector("#dgs-date");
    const gasEl = rootEl.querySelector("#dgs-gas-value");
    const cargoEl = rootEl.querySelector("#dgs-cargo-value");
    const destEl = rootEl.querySelector("#dgs-dest-value");
    const shipMarker = rootEl.querySelector("#dgs-ship-marker");

    // --- Step timeline (date) vs scroll position -------------------------
    // The date advances at a near-constant rate, EXCEPT each inter-step
    // segment's scroll length is capped (SEG_CAP) so the long Mar 6 -> Apr 4
    // crossing no longer dominates the scroll. stepDateFracs drive the clock;
    // stepScrollFracs place the prose + map cues along the scroll.
    const stepDateFracs = STEPS.map(
      (s) => (new Date(s.date).getTime() - T_START) / (T_END - T_START),
    );
    const SEG_CAP = 0.18;
    const segW = [];
    for (let i = 0; i < stepDateFracs.length - 1; i++) {
      segW[i] = Math.min(stepDateFracs[i + 1] - stepDateFracs[i], SEG_CAP);
    }
    const segTotal = segW.reduce((a, b) => a + b, 0) || 1;
    const stepScrollFracs = [0];
    for (let i = 0; i < segW.length; i++) {
      stepScrollFracs[i + 1] = stepScrollFracs[i] + segW[i] / segTotal;
    }

    // Scroll progress -> date fraction (piecewise-linear through the steps).
    function dateFracAt(p) {
      if (p <= 0) return 0;
      if (p >= 1) return 1;
      let i = 0;
      while (i < stepScrollFracs.length - 1 && p > stepScrollFracs[i + 1]) i++;
      const lo = stepScrollFracs[i],
        hi = stepScrollFracs[i + 1];
      const local = hi === lo ? 0 : (p - lo) / (hi - lo);
      return lerp(stepDateFracs[i], stepDateFracs[i + 1], local);
    }

    const stepWindows = stepScrollFracs.map((f, i) => {
      let nearest = Infinity;
      if (i > 0) nearest = Math.min(nearest, f - stepScrollFracs[i - 1]);
      if (i < stepScrollFracs.length - 1)
        nearest = Math.min(nearest, stepScrollFracs[i + 1] - f);
      if (!isFinite(nearest)) nearest = 0.12;
      return Math.min(0.08, nearest * 0.5);
    });
    const stepEls = [...rootEl.querySelectorAll(".dgs-step")];

    function layoutSteps() {
      const H = scrolly.offsetHeight;
      const vh = window.innerHeight;
      const range = Math.max(H - vh, 1);
      stepEls.forEach((el, i) => {
        const centerY = stepScrollFracs[i] * range + vh / 2;
        el.style.top = centerY.toFixed(1) + "px";
      });
    }

    function stageAt(p) {
      let s = 1;
      for (let i = 0; i < stepScrollFracs.length; i++) {
        if (p >= stepScrollFracs[i] - 1e-6) s = i + 1;
      }
      return s;
    }

    function applyStage(stage) {
      const cl = rootEl.classList;
      cl.toggle("show-japan", stage >= 1 && stage < 5);
      cl.toggle("show-cameron", stage >= 1 && stage < 5);
      cl.toggle("show-nagoya", stage >= 1 && stage < 5);
      cl.toggle("hi-gas", stage >= 2 && stage < 4);
      cl.toggle("show-iran", stage === 3);
      cl.toggle("hi-cargo", stage >= 4);
      cl.toggle("show-taiwan", stage >= 5);
      cl.toggle("show-kaohsiung", stage >= 5);
      destEl.textContent = stage >= 5 ? "Kaohsiung, Taiwan" : "Nagoya, Japan";
    }

    function updateProse(p) {
      for (let i = 0; i < stepEls.length; i++) {
        const d = Math.abs(p - stepScrollFracs[i]);
        const w = stepWindows[i];
        const plateau = w * 0.45;
        let op;
        if (d <= plateau) op = 1;
        else if (d >= w) op = 0;
        else op = 1 - (d - plateau) / (w - plateau);
        stepEls[i].style.opacity = op.toFixed(3);
      }
    }

    // --- Scroll loop (rAF-throttled) ---
    let ticking = false;
    let lastDayStr = null;
    let lastCargoIdx = -1;
    let lastStage = -1;

    function update() {
      ticking = false;
      const rect = scrolly.getBoundingClientRect();
      const H = scrolly.offsetHeight;
      const vh = window.innerHeight;
      const range = Math.max(H - vh, 1);
      const p = clamp(-rect.top / range, 0, 1);

      const curMs = T_START + dateFracAt(p) * (T_END - T_START);

      // Zoom first, then size the markers/labels to the current zoom so they
      // stay constant on screen.
      applyZoom(p);
      const mScale = curVBWidth / FULL_VB.w;
      updateAnnoScale(mScale);

      // Path reveal.
      voyagePath.style.strokeDashoffset =
        (pathLen * (1 - lenFracAt(curMs))).toFixed(1) + "px";

      // Ship marker (counter-scaled so the chevron stays a fixed size).
      const sp = interpolateShip(curMs);
      if (sp) {
        shipMarker.setAttribute(
          "transform",
          `translate(${sp.x.toFixed(2)},${sp.y.toFixed(2)}) rotate(${sp.heading.toFixed(1)}) scale(${mScale.toFixed(3)})`,
        );
      }

      // Date (guard by day).
      const dayStr = new Date(curMs).toDateString();
      if (dayStr !== lastDayStr) {
        lastDayStr = dayStr;
        dateEl.textContent = formatDate(curMs);
      }

      // Readouts (guard by data row — stepped, no interpolation).
      const idx = cargoIdxAt(curMs);
      if (idx !== lastCargoIdx) {
        lastCargoIdx = idx;
        const row = cargoSorted[idx];
        gasEl.textContent = "$" + row.jkm.toFixed(2);
        cargoEl.textContent = usdFmt.format(Math.round(row.cargo));
      }

      // Stage cues (guard by stage).
      const stage = stageAt(p);
      if (stage !== lastStage) {
        lastStage = stage;
        applyStage(stage);
      }

      updateProse(p);
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    }
    function onResize() {
      layoutSteps();
      onScroll();
    }

    layoutSteps();
    update();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onResize);

    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onResize);
    };
  }
</script>

<div class="dgs-scrolly" bind:this={root}>
  <div class="dgs-sticky">
    <div class="dgs-graphic">
      <svg
        id="dgs-map"
        viewBox="137 0 810 500"
        preserveAspectRatio="xMidYMid meet"
        aria-label="Diamond Gas Sakura voyage map"
      >
        <rect class="dgs-ocean" x="137" y="0" width="810" height="500" />
        <g class="dgs-land-layer"></g>
        <g class="dgs-borders-layer"></g>
        <path class="dgs-path" id="dgs-voyage-path" d="" />
        <g id="dgs-annotation-layer"></g>
        <g class="dgs-ship-layer">
          <polygon
            class="dgs-ship-arrow"
            id="dgs-ship-marker"
            points="0,-8 5,6 0,3 -5,6"
          />
        </g>
      </svg>
    </div>

    <div class="dgs-hud">
      <div class="dgs-date" id="dgs-date">February 24</div>
      <div class="dgs-readouts">
        <div class="dgs-readout" id="dgs-gas">
          <div class="dgs-readout-label">Asia gas price</div>
          <div class="dgs-readout-value dgs-num" id="dgs-gas-value">—</div>
        </div>
        <div class="dgs-readout" id="dgs-cargo">
          <div class="dgs-readout-label">Cargo value</div>
          <div class="dgs-readout-value dgs-num" id="dgs-cargo-value">—</div>
        </div>
        <div class="dgs-readout" id="dgs-dest">
          <div class="dgs-readout-label">Destination</div>
          <div class="dgs-readout-value" id="dgs-dest-value">Nagoya, Japan</div>
        </div>
      </div>
    </div>
  </div>

  <div class="dgs-steps">
    {#each STEPS as step, i}
      <div class="dgs-step" data-stage={i + 1}>
        <p class="dgs-step-inner">{step.prose}</p>
      </div>
    {/each}
  </div>
</div>

<style>
  .dgs-scrolly {
    position: relative;
    width: 100vw;
    margin-left: calc(50% - 50vw);
    height: 400vh;

    font-family: var(
      --primary-font,
      system-ui,
      -apple-system,
      "Segoe UI",
      Helvetica,
      Arial,
      sans-serif
    );

    /* Mode-aware tokens (light defaults). */
    --dgs-ocean: var(--cr-lavender, #f5f9fc);
    --dgs-land: #ffffff;
    --dgs-border: var(--cr-50grey, #808080);
    --dgs-line: var(--cr-cherry, #ff193b);
    --dgs-sakura: var(--cr-sakura, #ffccd4);
    --dgs-marker-stroke: #ffffff;
    --dgs-text: var(--text-color, #111);
    --dgs-muted: var(--cr-50grey, #808080);
    --dgs-step-bg: rgba(255, 255, 255, 0.86);
    --dgs-panel-bg: rgba(255, 255, 255, 0.92);
  }

  @media (prefers-color-scheme: dark) {
    .dgs-scrolly {
      --dgs-ocean: var(--cr-navy, #0a2066);
      --dgs-land: #000000;
      --dgs-border: var(--cr-75grey, #bfbfbf);
      --dgs-text: var(--text-color, #ffffff);
      --dgs-muted: var(--cr-75grey, #bfbfbf);
      --dgs-step-bg: rgba(8, 12, 28, 0.72);
      --dgs-panel-bg: rgba(8, 12, 28, 0.92);
    }
  }

  /* --- Sticky graphic --- */
  .dgs-sticky {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow: hidden;
  }

  .dgs-graphic {
    position: absolute;
    inset: 0;
    background: var(--dgs-ocean);
  }

  .dgs-graphic svg {
    display: block;
    width: 100%;
    height: 100%;
  }

  .dgs-ocean {
    fill: var(--dgs-ocean);
  }

  /* .dgs-land / .dgs-borders are added by D3 at runtime; scope with :global(). */
  .dgs-scrolly :global(.dgs-land) {
    fill: var(--dgs-land);
    stroke: none;
    transition: fill 0.5s ease;
  }

  .dgs-scrolly :global(.dgs-borders) {
    fill: none;
    stroke: var(--dgs-border);
    stroke-opacity: 0.5;
    stroke-width: 0.5px;
    vector-effect: non-scaling-stroke;
  }

  /* Country highlights, toggled by stage classes on the root. The stage
     classes (show-*, hi-*) are added at runtime by JS and never appear in the
     static markup, so Svelte would prune any partly-scoped selector that
     references them — these must be fully :global(). */
  :global(.dgs-scrolly.show-japan .dgs-land[data-cid="392"]),
  :global(.dgs-scrolly.show-iran .dgs-land[data-cid="364"]),
  :global(.dgs-scrolly.show-taiwan .dgs-land[data-cid="158"]) {
    fill: var(--dgs-sakura);
  }

  .dgs-path {
    fill: none;
    stroke: var(--dgs-line);
    /* User-unit width (no non-scaling-stroke): the dash-offset reveal measures
       in user units to match getTotalLength(); non-scaling-stroke would put the
       dash pattern in screen space and break the reveal. */
    stroke-width: 1.4px;
    stroke-linejoin: round;
    stroke-linecap: round;
  }

  .dgs-ship-arrow {
    fill: var(--dgs-line);
    stroke: var(--dgs-marker-stroke);
    stroke-width: 1px;
    stroke-linejoin: round;
  }

  /* --- Annotations (built by JS, so scope with :global()) --- */
  .dgs-scrolly :global(.dgs-anno) {
    opacity: 0;
    transition: opacity 0.5s ease;
    pointer-events: none;
  }
  :global(.dgs-scrolly.show-cameron #dgs-anno-cameron),
  :global(.dgs-scrolly.show-nagoya #dgs-anno-nagoya),
  :global(.dgs-scrolly.show-iran #dgs-anno-iran),
  :global(.dgs-scrolly.show-iran #dgs-anno-hormuz),
  :global(.dgs-scrolly.show-kaohsiung #dgs-anno-kaohsiung) {
    opacity: 1;
  }

  .dgs-scrolly :global(.dgs-anno-dot) {
    fill: var(--dgs-text);
  }
  .dgs-scrolly :global(.dgs-anno-leader) {
    stroke: var(--dgs-text);
    stroke-width: 0.75px;
    vector-effect: non-scaling-stroke;
  }
  .dgs-scrolly :global(.dgs-anno-label) {
    fill: var(--dgs-text);
    font-family: var(--primary-font, system-ui, sans-serif);
    font-size: 7px;
    font-weight: 600;
  }
  .dgs-scrolly :global(.dgs-anno-country) {
    font-size: 8px;
    letter-spacing: 0.12em;
  }

  /* --- HUD overlay --- */
  .dgs-hud {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
  }

  .dgs-date {
    position: absolute;
    top: 24px;
    left: 50%;
    transform: translateX(-50%);
    font-size: clamp(28px, 5vw, 56px);
    font-weight: 600;
    color: var(--dgs-text);
    line-height: 1.1;
    font-feature-settings: "tnum";
    -webkit-font-feature-settings: "tnum";
    -moz-font-feature-settings: "tnum";
  }

  .dgs-readouts {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
    padding: 0 28px;
  }

  .dgs-readout:nth-child(2) {
    text-align: center;
  }
  .dgs-readout:last-child {
    text-align: right;
  }

  .dgs-readout-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--dgs-muted);
    margin-bottom: 4px;
  }

  .dgs-readout-value {
    font-size: clamp(22px, 3vw, 36px);
    font-weight: 600;
    color: var(--dgs-text);
    line-height: 1.1;
    transition: color 0.4s ease;
  }

  .dgs-num {
    font-feature-settings: "tnum";
    -webkit-font-feature-settings: "tnum";
    -moz-font-feature-settings: "tnum";
  }

  :global(.dgs-scrolly.hi-gas #dgs-gas-value),
  :global(.dgs-scrolly.hi-cargo #dgs-cargo-value) {
    color: var(--cr-cherry, #ff193b);
  }

  /* --- Prose steps --- */
  .dgs-steps {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
  }

  .dgs-step {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: min(640px, 86vw);
    text-align: center;
    opacity: 0;
  }

  .dgs-step-inner {
    margin: 0;
    display: inline-block;
    background: var(--dgs-step-bg);
    color: var(--dgs-text);
    padding: 18px 22px;
    border-radius: 10px;
    font-size: clamp(18px, 2.4vw, 24px);
    line-height: 1.45;
    -webkit-backdrop-filter: blur(4px);
    backdrop-filter: blur(4px);
  }

  /* --- Mobile: map top half, readouts bottom-half panel --- */
  @media (max-width: 640px) {
    .dgs-graphic {
      inset: 0 0 auto 0;
      height: 50vh;
    }
    .dgs-date {
      top: 12px;
      font-size: 28px;
    }
    .dgs-readouts {
      top: 50vh;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 18px 20px;
      gap: 16px;
      flex-direction: column;
      justify-content: center;
      align-items: stretch;
      background: var(--dgs-panel-bg);
    }
    .dgs-readout,
    .dgs-readout:nth-child(2),
    .dgs-readout:last-child {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      text-align: left;
    }
    .dgs-readout-label {
      margin-bottom: 0;
    }
    .dgs-readout-value {
      font-size: 24px;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .dgs-scrolly :global(.dgs-land),
    .dgs-scrolly :global(.dgs-anno),
    .dgs-readout-value {
      transition: none;
    }
  }
</style>
