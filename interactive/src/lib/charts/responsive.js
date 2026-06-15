/**
 * Svelte action: report an element's content box whenever it resizes.
 *
 * Usage inside a component:
 *   <div use:resize={(rect) => (width = rect.width)}>…</div>
 *
 * Charts read `width` (and optionally height) from this and let D3 scales /
 * SVG viewBox react, so every visualisation is fluid by default.
 */
export function resize(node, callback) {
  const ro = new ResizeObserver((entries) => {
    const rect = entries[0]?.contentRect;
    if (rect) callback(rect);
  });
  ro.observe(node);
  return {
    destroy() {
      ro.disconnect();
    },
  };
}
