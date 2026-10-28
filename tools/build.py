#!/usr/bin/env python3
"""Gera o Pages a partir do catálogo versionado, usando um SQLite temporário."""
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile
from html.parser import HTMLParser
from urllib.parse import unquote, urlparse

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'dist'
PHP = os.environ.get('PHP_CLI', 'php')


def build():
    catalog = json.loads((ROOT / 'data/catalogo.json').read_text())
    ids = [row['id'] for row in catalog]
    if len(ids) != len(set(ids)) or any(type(i) is not int or i < 0 for i in ids):
        raise ValueError('IDs devem ser inteiros únicos e não negativos.')
    # Só limpa a pasta de saída conhecida, nunca a raiz do checkout.
    if OUT.is_symlink():
        raise ValueError('dist não pode ser um link simbólico.')
    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir()
    with tempfile.TemporaryDirectory(prefix='oliveira-build-') as temp:
        runtime = dict(os.environ, CATALOG_DB_PATH=str(Path(temp) / 'catalogo.sqlite'))

        def render(template, product_id=None):
            code = 'chdir(' + json.dumps(str(ROOT)) + ');'
            if product_id is not None:
                code += '$_GET["id"]=' + str(product_id) + ';'
            code += 'include ' + json.dumps(template) + ';'
            result = subprocess.run([PHP, '-d', 'display_errors=stderr', '-r', code], env=runtime, capture_output=True, text=True, check=True)
            if result.stderr.strip():
                raise RuntimeError(result.stderr)
            html = result.stdout.replace('index.php', 'index.html')
            html = re.sub(r'detalhe-produto\.php\?id=(\d+)', r'produto-\1.html', html)
            if '<?php' in html:
                raise ValueError('PHP não renderizado na saída.')
            return html

        (OUT / 'index.html').write_text(render('index.php'))
        for row in catalog:
            if row['status'] != 'vendido':
                (OUT / f'produto-{row["id"]}.html').write_text(render('detalhe-produto.php', row['id']))
        (OUT / '404.html').write_text(render('detalhe-produto.php', -1))
    for folder in ['css', 'js', 'img', 'fontes']:
        shutil.copytree(ROOT / folder, OUT / folder)
    (OUT / '.nojekyll').touch()

    class CheckLinks(HTMLParser):
        def handle_starttag(self, tag, attrs):
            for key, value in attrs:
                if key not in ('src', 'href') or not value or value.startswith('#'):
                    continue
                parsed = urlparse(value)
                if parsed.scheme or parsed.netloc:
                    continue
                path = unquote(parsed.path)
                if not (OUT / path).is_file():
                    raise ValueError('Link local quebrado: ' + value)

    for page in OUT.glob('*.html'):
        CheckLinks().feed(page.read_text())
    if list(OUT.rglob('*.php')) or list(OUT.rglob('*.sqlite')):
        raise ValueError('Arquivo privado na saída pública.')
    print(f'Build OK: {len(list(OUT.glob("*.html")))} páginas, links locais verificados; nenhum banco ou PHP publicado.')


if __name__ == '__main__':
    build()
