#!/usr/bin/env python3
"""Regenerate reviewed checksums, counts and per-country coverage after data edits."""
import hashlib
import json
from collections import Counter
from pathlib import Path

root = Path(__file__).resolve().parents[1]
data = root / 'resources/json'
files = {}
records = {}
for file in sorted(data.glob('*.json')):
    contents = file.read_bytes()
    records[file.stem] = json.loads(contents)
    files[file.name] = {'records': len(records[file.stem]), 'sha256': hashlib.sha256(contents).hexdigest()}
states = Counter(record['country_code'] for record in records['states'])
cities = Counter(record['country_code'] for record in records['cities'])
coverage = {country['iso2']: {'states': states[country['iso2']], 'cities': cities[country['iso2']]} for country in sorted(records['countries'], key=lambda c: c['iso2'])}
manifest = {'schema_version': 1, 'sources': '../docs/DATA_SOURCES.md', 'files': files, 'coverage': coverage}
(root / 'resources/data-manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + '\n')
