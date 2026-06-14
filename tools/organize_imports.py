#!/usr/bin/env python3
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
IGNORED_DIRS = {'vendor', 'storage', 'docker'}

use_re = re.compile(r'^\s*use\s+[^;]+;\s*$')

def process_file(path: Path) -> bool:
    changed = False
    text = path.read_text(encoding='utf-8')
    lines = text.splitlines()
    out_lines = []
    i = 0
    n = len(lines)
    while i < n:
        line = lines[i]
        if use_re.match(line):
            # collect contiguous use block
            start = i
            block = []
            while i < n and use_re.match(lines[i]):
                block.append(lines[i].strip())
                i += 1
            # dedupe and sort
            unique = sorted(dict.fromkeys(block))
            if unique != block:
                changed = True
            out_lines.extend(unique)
            continue
        else:
            out_lines.append(line)
            i += 1
    if changed:
        path.write_text('\n'.join(out_lines) + '\n', encoding='utf-8')
    return changed


def should_skip(p: Path) -> bool:
    parts = set(p.parts)
    return any(d in parts for d in IGNORED_DIRS)


if __name__ == '__main__':
    php_files = list(ROOT.rglob('*.php'))
    modified = []
    for f in php_files:
        if should_skip(f):
            continue
        try:
            if process_file(f):
                modified.append(str(f.relative_to(ROOT)))
        except Exception as e:
            print(f'Error processing {f}: {e}')
    if modified:
        print('Modified files:')
        for m in modified:
            print(m)
    else:
        print('No changes made.')
