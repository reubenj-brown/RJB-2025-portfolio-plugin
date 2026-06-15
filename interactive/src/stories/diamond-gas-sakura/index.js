import { mount } from 'svelte';
import DiamondGasSakura from './DiamondGasSakura.svelte';

/**
 * Island entry. mount.js calls this with the placeholder element and the props
 * derived from its data-* attributes.
 */
export default function (target, props) {
  return mount(DiamondGasSakura, { target, props });
}
