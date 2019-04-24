# CPF e CNPJ para Contact Form 7
Plugin criado para inserir, gerenciar e validar CPF e CNPJ nos formulários criados com Contact Form 7, de forma fácil e otimizada para o usuário final.

## Descrição

Plugin criado para inserir, gerenciar e validar CPF e CNPJ nos formulários criados com Contact Form 7, de forma fácil e otimizada para o usuário final.

As tags podem ser adicionadas facilmente ao formulário, selecionando os botões "cpf" e "cnpj" disponíveis na criação/edição do formulário de contato.

Também há a opção de gerenciamento das mensagens de erro para ambos os campos, disponíveis na aba "Mensagens".

No front-end, os campos de CPF e CNPJ são apresentados com máscaras de campo e placeholder (caso esteja preenchido), limitando ao usuário digitar somente números. Na versão mobile, os campos são adicionados com o tipo "tel" (HTML5), também para esse fim.

## Instalação

Descompacte ou instale o plugin em sua pasta `/wp-content/plugins/`, ative e use.

## FAQ

### Como adicionar os campos no formulário?

Basta clicar na opção desejada "cpf" ou "cnpj", disponíveis na edição do formulário, se seguir os passos seguintes (campo obrigatório, valor padrão/placeholder, etc).

### Posso personalizar as mensagens de erro de cada campo?

Claro que sim. É só editar as mensagens padrões dos respectivos campos, na aba "Mensagens".

### A validação dos campos é somente no front-end?

Não, a validação toda se dá somente no back-end. No front-end ocorre somente a formatação do campo, através da máscara de cada um (000.000.000-00 para CPF's e 00.000.000/0000-00 para CNPJ's).

### Posso criar mais de um campo de CPF/CNPJ no mesmo formulário?

Sim, sem problemas quanto a isso. Você só precisa seguir o padrão do Contact Form 7, de não repetir o mesmo ID (nome) para os campos diferentes.

## Screenshots

1. Campos de CPF e CNPJ disponíveis para inserção no formulário
2. Gerenciamento das mensagens de erro para cada campo
3. Exemplo da validação do formulário no front-end
