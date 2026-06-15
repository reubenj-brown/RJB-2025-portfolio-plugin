import { csvParse, tsvParse, autoType } from 'd3-dsv';

/**
 * Fetch a dataset by URL and return an array of row objects.
 *
 * - .json  -> parsed as-is
 * - .tsv   -> d3-dsv tsvParse with autoType
 * - .csv   -> d3-dsv csvParse with autoType (and the default for anything else)
 *
 * autoType coerces obviously-numeric and ISO-date columns to Number / Date so
 * downstream charts don't have to. Ambiguous strings (e.g. "2024-01") are left
 * alone for the caller to parse.
 */
export async function loadData(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`fetch ${url} -> ${res.status}`);

  if (/\.json(\?|$)/.test(url)) return res.json();

  const text = await res.text();
  if (/\.tsv(\?|$)/.test(url)) return tsvParse(text, autoType);
  return csvParse(text, autoType);
}
