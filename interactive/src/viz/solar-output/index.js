import { mount } from 'svelte';
import SolarOutput from './SolarOutput.svelte';

/**
 * Island entry. mount.js calls this with the placeholder element and the props
 * derived from its data-* attributes.
 */
export default function (target, props) {
  return mount(SolarOutput, { target, props });
}
