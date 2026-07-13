import { mount } from 'svelte';
import NigeriaOilSolar from './NigeriaOilSolar.svelte';

/**
 * Island entry. mount.js calls this with the placeholder element and the props
 * derived from its data-* attributes (e.g. data-mode="html" -> { mode }).
 */
export default function (target, props) {
  return mount(NigeriaOilSolar, { target, props });
}
