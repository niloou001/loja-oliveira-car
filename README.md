# Oliveira Car

Catálogo de veículos em PHP 8.4 + SQLite, com busca, filtros, favoritos no navegador e simulação de parcelas. O catálogo original é ilustrativo; confirme preços e condições antes de usar comercialmente.

## Desenvolvimento

Requisitos: PHP 8.4 com PDO SQLite (`pdo_sqlite`, pacote Debian `php8.4-sqlite3`), Python 3 para o build estático. Sem Composer ou npm para executar o site.

```sh
php tools/catalogo.php listar
php -S 127.0.0.1:8080 -t .
```

O banco é criado automaticamente a partir de `data/catalogo.json`, uma única vez, em `../.loja-oliveira-car/catalogo.sqlite`, fora da raiz pública. Para hospedagem PHP, configure `CATALOG_DB_PATH` com um caminho privado e persistente, gravável pelo PHP, faça backups e configure HTTPS. Não hospede o banco em pastas públicas. Não há endpoint de administração aberto.

## Gerenciar pelo chat

Peça, por exemplo: “mude o Ford GT para R$ 120 mil”, “marque o Camaro como reservado” ou “cadastre este carro”. O agente usa o terminal neste ambiente. Este chat não é uma conexão permanente com uma futura hospedagem externa; tal conexão precisa ser configurada separadamente.

```sh
php tools/catalogo.php listar
php tools/catalogo.php atualizar 0 '{"valor":120000}'
php tools/catalogo.php atualizar 1 '{"status":"reservado"}'
php tools/catalogo.php adicionar '{"nome":"Modelo","marca":"Marca","descricao":"Descrição","imagem":"https://exemplo.com/carro.jpg","valor":100000,"parcelamento_max_sj":12,"parcelamento_max_cj":36,"juros":1.99,"status":"disponivel"}'
php tools/catalogo.php historico
php tools/catalogo.php backup /caminho/privado/novo-backup.sqlite
```

IDs são estáveis, começando em 0 para preservar os links originais. `vendido` retira o carro do catálogo público sem apagar o registro; `remover ID` apaga o registro e grava histórico. Preços são armazenados em centavos e consultas usam parâmetros. O histórico registra o antes e o depois, mas não identifica usuários (a administração é por terminal).

### Persistir e publicar alterações

Após editar o banco, execute `php tools/catalogo.php exportar` para salvar o catálogo em `data/catalogo.json`. Revise e versione esse arquivo: é ele que permite restaurar o catálogo em uma nova máquina e gerar o site público. O banco e o histórico local não são enviados ao GitHub; faça backup privado para retê-los. A exportação não é automática, e salvar no banco sozinho não muda o Pages.

```sh
php tools/catalogo.php exportar
python3 tools/build.py
```

O build usa um banco temporário inicializado com o JSON versionado, gera `dist/`, converte links para HTML e não publica PHP, banco ou ferramentas de gestão. Publique o conteúdo de `dist/` na branch `gh-pages`, preservando os commits anteriores dessa branch. Configure Pages para `gh-pages` e `/(root)`.

**GitHub Pages é estático:** os visitantes acessam as páginas geradas. Alterações via chat exigem exportar, gerar e publicar novamente. Para consultas ou escritas no banco online em tempo real, é necessária hospedagem PHP com armazenamento persistente. Favoritos ficam apenas no navegador do visitante; não são cadastros de clientes.

## Contato

Defina o WhatsApp comercial público em `config/loja.php` (somente dígitos com código do país). Quando configurado, o detalhe exibe um link com mensagem sobre o veículo. Não existe número fictício ou formulário que simule envio.

## Verificações

```sh
python3 tests/catalogo_test.py
python3 tools/build.py
```

O teste usa banco temporário; não altera o catálogo real. As simulações mantêm juros simples do projeto original e exibem condições e limites; não representam aprovação de crédito.
