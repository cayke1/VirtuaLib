## Erro: "Não foi possível registrar o empréstimo no momento."

Breve e direto: o servidor respondeu com a mensagem genérica porque o banco rejeitou a operação.

- Causa: tentamos gravar valores em português (ex.: `Emprestado`, `Devolvido`) no campo `status` da tabela `Borrows`, mas o ENUM do banco foi definido em inglês: `('borrowed','returned','late')`. O MySQL não adivinha sinônimos, ele só aceita os valores exatos.
- O que foi feito: `BorrowModel.php` foi ajustado para usar os valores do enum (`'borrowed'` ao criar; `'returned'` ao devolver; consultas usam `'borrowed'/'late'`).

Teste rápido:

1. Reinicie o servidor/contêiner ou o processo PHP para carregar a alteração.
2. Faça a requisição POST para `/borrow/{id}` (usar a UI é suficiente) — deve retornar JSON com `success: true`.
3. Se ainda falhar, verifique os logs do PHP (error_log) — a mensagem de erro do PDO foi registrada lá com detalhes.

Opções alternativas (se preferir guardar tudo em português):

- Alterar o ENUM no banco para aceitar os rótulos em português e migrar os valores existentes (mais invasivo).
- Ou manter o padrão atual (recomendado) e garantir que todas as gravações usem os valores do enum.

Observação final: sim, o MySQL é literal — e agora o código também.
