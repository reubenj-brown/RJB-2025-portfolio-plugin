/**
 * RJB interactive runtime — island loader.
 *
 * Scans the page for [data-viz] placeholders (printed by the [reuben_viz]
 * shortcode) and lazily mounts the matching Svelte island when it scrolls near
 * the viewport. Each island lives in src/viz/<id>/ or src/stories/<id>/ and
 * default-exports a mount(target, props) function.
 *
 * This is the only script WordPress enqueues. Island code and its CSS ship as
 * separate chunks that are fetched on demand, so a page downloads only the
 * visualisations it actually contains.
 */

// Vite turns these globs into a map of module path -> lazy import().
const modules = {
  ...import.meta.glob('./viz/*/index.js'),
  ...import.meta.glob('./stories/*/index.js'),
};

// Index islands by folder name, which is the id used in data-viz="...".
const registry = {};
for (const [path, loader] of Object.entries(modules)) {
  const match = path.match(/\/(?:viz|stories)\/([^/]+)\/index\.js$/);
  if (match) registry[match[1]] = loader;
}

// Turn data-* attributes into component props. dataset keys are already
// camelCased (data-foo-bar -> fooBar).
function propsFor(el) {
  const props = { ...el.dataset };
  delete props.viz; // the island id, not a prop
  delete props.vizMounted; // internal guard flag
  return props;
}

async function hydrate(el) {
  if (el.dataset.vizMounted) return;
  el.dataset.vizMounted = '1';

  const id = el.dataset.viz;
  const loader = registry[id];
  if (!loader) {
    console.warn(`[rjb-viz] no island registered for id "${id}"`);
    return;
  }

  try {
    const mod = await loader();
    mod.default(el, propsFor(el));
  } catch (err) {
    console.error(`[rjb-viz] failed to mount "${id}"`, err);
  }
}

function init() {
  const nodes = [...document.querySelectorAll('[data-viz]')];
  if (!nodes.length) return;

  // No IntersectionObserver -> just mount everything immediately.
  if (!('IntersectionObserver' in window)) {
    nodes.forEach(hydrate);
    return;
  }

  // Otherwise defer each island until it nears the viewport.
  const io = new IntersectionObserver(
    (entries, obs) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          obs.unobserve(entry.target);
          hydrate(entry.target);
        }
      }
    },
    { rootMargin: '200px' }
  );
  nodes.forEach((n) => io.observe(n));
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
