# Trabalhar neste catálogo

- Use o checkout existente. O ambiente já é isolado; não crie worktrees sem pedido.
- PHP local nesta máquina: `/workspace/.tools/bin/php`. Ative `export PATH="/workspace/.tools/bin:$PATH"` se necessário.
- Catálogo público em `data/catalogo.json`; SQLite privado é criado uma vez fora da raiz pública. Comandos em `tools/catalogo.php`. Não publique bancos nem backups.
- Ao receber alterações do catálogo por chat, inspecione o estado atual, aplique só o que foi pedido, faça backup privado antes de operações destrutivas, exporte para o JSON e revise o diff. Registros vendidos devem ser marcados como `vendido` em vez de removidos quando o pedido permitir.
- Para atualizar o site: teste, execute `python3 tools/build.py` e envie `dist/` para `gh-pages`. Não basta mudar SQLite ou main para atualizar Pages. Preserve o histórico de gh-pages. Confira `origin` antes do push: o repositório foi renomeado para `niloou001/loja-oliveira-car`.
- Não descreva o banco local como banco conectado ao site no GitHub Pages. É um catálogo estático gerado a partir do banco. Para banco online é preciso hospedagem PHP e acesso específico.
- Não invente contato, estoque real, histórico de revisões ou condições comerciais. Mantenha os avisos de simulação ilustrativa.
- Verificações: `python3 tests/catalogo_test.py`, `python3 tools/build.py` e teste no navegador se alterar a interface. Evite alterar o catálogo real durante testes: use `CATALOG_DB_PATH` temporário.
