import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest

SOURCE = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_CLI', 'php')


class CatalogoTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='oliveira-test-')
        self.root = Path(self.temp.name) / 'site'
        shutil.copytree(SOURCE, self.root, ignore=shutil.ignore_patterns('.git', 'dist', '__pycache__'))
        self.env = dict(os.environ, CATALOG_DB_PATH=str(Path(self.temp.name) / 'private/catalogo.sqlite'))

    def tearDown(self):
        self.temp.cleanup()

    def command(self, *args, ok=True):
        result = subprocess.run([PHP, str(self.root / 'tools/catalogo.php'), *args], env=self.env, capture_output=True, text=True)
        self.assertEqual(result.returncode, 0 if ok else 1, result.stderr)
        return result

    def rows(self):
        return json.loads(self.command('listar').stdout)

    def test_update_survives_restart_and_export_restores(self):
        self.assertEqual(len(self.rows()), 5)
        self.command('atualizar', '0', '{"valor":120000.99,"status":"reservado"}')
        row = self.rows()[0]
        self.assertEqual(row['valor'], 120000.99)
        self.assertEqual(row['status'], 'reservado')
        self.command('exportar')
        seed = json.loads((self.root / 'data/catalogo.json').read_text())
        self.assertEqual(seed[0]['valor'], 120000.99)
        self.assertNotIn('atualizado_em', seed[0])
        self.env['CATALOG_DB_PATH'] = str(Path(self.temp.name) / 'restored.sqlite')
        self.assertEqual(self.rows()[0]['valor'], 120000.99)
        self.assertEqual([r['id'] for r in self.rows()], [0, 1, 2, 3, 4])

    def test_add_remove_stable_id_and_audit(self):
        product = self.rows()[0]
        product['nome'] = "Carro d'Oliveira <teste>"
        product.pop('id'); product.pop('atualizado_em')
        created = json.loads(self.command('adicionar', json.dumps(product)).stdout)
        self.assertEqual(created['id'], 5)
        self.command('remover', '1')
        self.assertEqual([r['id'] for r in self.rows()], [0, 2, 3, 4, 5])
        self.assertEqual(self.rows()[-1]['nome'], product['nome'])
        history = json.loads(self.command('historico').stdout)
        self.assertEqual([r['acao'] for r in history], ['remover', 'adicionar'])
        self.assertEqual(json.loads(history[0]['dados'])['antes']['nome'], 'Camaro Preto')

    def test_invalid_values_do_not_mutate_database(self):
        before = self.rows()
        for patch in [{'valor': -1}, {'valor': 0}, {'juros': 101}, {'parcelamento_max_sj': 0}, {'parcelamento_max_cj': 1}, {'parcelamento_max_sj': 2.5}, {'imagem': 'javascript:alert(1)'}, {'status': 'desconhecido'}, {'id': 42}, {'nome': ''}]:
            self.command('atualizar', '0', json.dumps(patch), ok=False)
        self.command('atualizar', '999', '{"valor":1}', ok=False)
        self.command('atualizar', '0 OR 1=1', '{"valor":1}', ok=False)
        self.assertEqual(self.rows(), before)
        self.assertEqual(json.loads(self.command('historico').stdout), [])

    def test_empty_catalog_is_not_reseeded(self):
        for row in self.rows():
            self.command('remover', str(row['id']))
        self.assertEqual(self.rows(), [])
        self.assertEqual(self.rows(), [])

    def test_sold_hidden_and_build_uses_exported_seed(self):
        self.command('atualizar', '0', '{"status":"vendido"}')
        self.command('atualizar', '1', '{"nome":"<script>alert(1)</script>"}')
        self.command('exportar')
        result = subprocess.run(['python3', str(self.root / 'tools/build.py')], env=self.env, text=True, capture_output=True)
        self.assertEqual(result.returncode, 0, result.stderr)
        out = self.root / 'dist'
        self.assertFalse((out / 'produto-0.html').exists())
        self.assertTrue((out / 'produto-1.html').exists())
        self.assertIn('&lt;script&gt;alert(1)&lt;/script&gt;', (out / 'index.html').read_text())
        self.assertNotIn('<script>alert(1)</script>', (out / 'index.html').read_text())
        self.assertFalse(list(out.rglob('*.sqlite')))
        self.assertFalse(list(out.rglob('*.php')))

    def test_backup_can_be_restored(self):
        self.command('atualizar', '0', '{"valor":123456}')
        backup = Path(self.temp.name) / 'backup.sqlite'
        self.command('backup', str(backup))
        self.command('backup', str(backup), ok=False)
        self.env['CATALOG_DB_PATH'] = str(backup)
        self.assertEqual(self.rows()[0]['valor'], 123456)


if __name__ == '__main__':
    unittest.main(verbosity=2)
