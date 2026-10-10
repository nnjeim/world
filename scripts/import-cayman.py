#!/usr/bin/env python3
"""Import only GeoNames Cayman populated places with a known district.
Usage: python3 scripts/import-cayman.py /path/to/KY.zip (or a KY.tsv snapshot)
Sources and licensing: docs/DATA_SOURCES.md. Review the diff before committing.
"""
import json
import sys
import zipfile
from pathlib import Path

root = Path(__file__).resolve().parents[1]
path = root / 'resources/json'
states = json.loads((path / 'states.json').read_text())
cities = json.loads((path / 'cities.json').read_text())
if any(state['country_code'] == 'KY' for state in states):
    raise SystemExit('Cayman data already exists; review updates against stable source IDs manually.')
source = Path(sys.argv[1])
contents = zipfile.ZipFile(source).read('KY.txt').decode() if source.suffix == '.zip' else source.read_text()
records = [line.split('\t') for line in contents.splitlines()]
codes = {'10346796': 'GT', '10375968': 'WB', '10375969': 'BT', '10375970': 'NS', '10375971': 'EE', '10375972': 'SI'}
new_states = []
next_id = max(state['id'] for state in states) + 1
for row in sorted((r for r in records if r[7] == 'ADM1'), key=lambda r: int(r[0])):
    if row[0] not in codes:
        raise SystemExit('Unexpected Cayman district; review the source before importing.')
    new_states.append(dict(id=next_id, name='Sister Islands' if row[0] == '10375972' else row[1],
        country_id=41, country_code='KY', country_name='Cayman Islands', state_code=codes[row[0]],
        type='district', latitude=f'{float(row[4]):.8f}', longitude=f'{float(row[5]):.8f}', geonameId=int(row[0])))
    next_id += 1
if len(new_states) != 6:
    raise SystemExit('Expected the six districts documented in the Cayman census.')
by_geoname = {str(state['geonameId']): state for state in new_states}
new_cities = []
next_id = max(city['id'] for city in cities) + 1
for row in sorted((r for r in records if r[7] in ('PPL', 'PPLA', 'PPLC') and r[10] in codes), key=lambda r: int(r[0])):
    state = by_geoname[row[10]]
    new_cities.append(dict(id=next_id, name=row[1], state_id=state['id'], state_code=state['state_code'],
        state_name=state['name'], country_id=41, country_code='KY', country_name='Cayman Islands',
        latitude=f'{float(row[4]):.8f}', longitude=f'{float(row[5]):.8f}', wikiDataId=None, geonameId=int(row[0])))
    next_id += 1
for module, additions in [('states', new_states), ('cities', new_cities)]:
    file = path / (module + '.json')
    text = file.read_text().rstrip()
    suffix = json.dumps(additions, indent=4, ensure_ascii=False)[1:-1].strip('\n')
    file.write_text(text[:-1].rstrip() + ',\n' + suffix + '\n]\n')
print(f'Added {len(new_states)} districts and {len(new_cities)} populated places.')
