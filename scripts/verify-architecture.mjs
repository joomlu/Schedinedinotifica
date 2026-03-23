#!/usr/bin/env node
/**
 * Verifica mínima de arquitectura.
 * - Asegura che il build sia stato generato.
 * - Blinda il componente GEO (immutabile): se cambia, la verifica fallisce.
 */
import { createHash } from 'crypto';
import { existsSync, readFileSync } from 'fs';
import { resolve } from 'path';

const manifest = resolve(process.cwd(), 'public', 'build', '.vite', 'manifest.json');
if (!existsSync(manifest)) {
  console.error('Manifest Vite no encontrado en public/build/.vite/manifest.json');
  process.exit(1);
}

const geoHashesFile = resolve(process.cwd(), 'scripts', 'geo-immutable.hashes.json');
if (!existsSync(geoHashesFile)) {
  console.error('Archivo de hashes GEO no encontrado: scripts/geo-immutable.hashes.json');
  process.exit(1);
}

const expected = JSON.parse(readFileSync(geoHashesFile, 'utf8'));
const failures = [];

for (const [relativePath, expectedHash] of Object.entries(expected)) {
  const absPath = resolve(process.cwd(), relativePath);
  if (!existsSync(absPath)) {
    failures.push(`${relativePath}: archivo faltante`);
    continue;
  }
  const content = readFileSync(absPath);
  const actualHash = createHash('sha256').update(content).digest('hex');
  if (actualHash !== expectedHash) {
    failures.push(`${relativePath}: hash inesperado`);
  }
}

if (failures.length) {
  console.error('GEO IMMUTABILE: cambi rilevati in file blindati.');
  failures.forEach((msg) => console.error(`- ${msg}`));
  process.exit(1);
}

console.log('verify-architecture: OK (manifest presente, GEO immutabile integro)');
process.exit(0);
