import { mount } from 'svelte';
import NigeriaOilSolar from './NigeriaOilSolar.svelte';

/**
 * Island entry. mount.js calls this with the placeholder element and the props
 * derived from its data-* attributes (e.g. data-mode="html" -> { mode }).
 *
 * Enclosed shortcode content arrives as an inert <template.rjb-viz-body> child;
 * read its HTML into the `body` prop, then clear the target before mounting.
 */
export default function (target, props) {
  const tpl = target.querySelector('.rjb-viz-body');
  const body = tpl ? tpl.innerHTML.trim() : '';
  target.innerHTML = '';
  return mount(NigeriaOilSolar, { target, props: { ...props, body } });
}
