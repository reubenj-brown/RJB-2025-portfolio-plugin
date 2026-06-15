/**
 * Scrollytelling step manager (foundation).
 *
 * Wraps IntersectionObserver to fire onStep(index, element) as each step of a
 * scrolly container crosses the active band (a thin horizontal slice centred in
 * the viewport by default). Narrative pieces in src/stories/<id>/ build on top
 * of this — a sticky graphic that updates as the reader scrolls through prose
 * steps.
 *
 * Deliberately minimal for now; extend (progress within a step, enter/exit
 * direction, etc.) when the first scrollytelling piece needs it.
 *
 * @param {Element} container
 * @param {Object}  opts
 * @param {string}  [opts.stepSelector='.step']
 * @param {(index:number, el:Element) => void} [opts.onStep]
 * @param {string}  [opts.rootMargin='-50% 0px -50% 0px']  active band
 * @returns {{ destroy(): void }}
 */
export function createScroller(
  container,
  { stepSelector = '.step', onStep, rootMargin = '-50% 0px -50% 0px' } = {}
) {
  const steps = [...container.querySelectorAll(stepSelector)];

  const io = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          onStep?.(steps.indexOf(entry.target), entry.target);
        }
      }
    },
    { rootMargin, threshold: 0 }
  );

  steps.forEach((step) => io.observe(step));

  return {
    destroy() {
      io.disconnect();
    },
  };
}
