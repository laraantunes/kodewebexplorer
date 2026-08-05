# KodeWeb Explorer

O **KodeWeb Explorer** é uma ferramenta focada e extremamente leve para navegação e gerenciamento de arquivos. Faz parte do ecossistema KodeWeb (ao lado do KodeWeb IDE e KodeWeb Lite), desenvolvido inteiramente em PHP, HTML, CSS e JavaScript sem dependências externas pesadas, garantindo o máximo de performance.

## 🚀 Funcionalidades

- **Gerenciador de Arquivos Simples e Direto**: Crie, edite, exclua, mova e renomeie arquivos do seu servidor ou computador local através de uma interface web clean.
- **Upload e Download Rápidos**: Faça upload de arquivos com suporte a *drag-and-drop* e faça download de pastas inteiras como `.zip`.
- **Navegação Visual Intuitiva**: Explore as pastas do seu sistema com uma hierarquia limpa.
- **Editor de Texto Integrado**: Realize pequenas edições de código diretamente pelo navegador.
- **Leveza Absoluta**: Não utiliza Composer nem extensões pesadas do PHP. 
- **Auto-Updater**: Atualização integrada com as *releases* do repositório no GitHub para manter a ferramenta sempre na última versão com apenas um clique!

## 🛠️ Instalação

### Instalação Padrão
1. Faça o download da última *release* na [página de lançamentos](https://github.com/laraantunes/kodewebexplorer/releases) (ou clone o repositório).
2. Extraia os arquivos no diretório do seu servidor web (ex: Apache, Nginx) que tenha suporte a PHP (recomendado PHP 8.0+).
3. Acesse o diretório no navegador (ex: `http://localhost/kodeweb-explorer`).
4. Na primeira execução, crie o seu usuário mestre de acesso.

### Rodando com Docker (Recomendado)
O KodeWeb Explorer já está preparado para rodar perfeitamente em container Docker:
1. Abra o terminal na raiz do projeto.
2. Execute o comando:
   ```bash
   docker-compose up -d --build
   ```
3. Acesse via `http://localhost:8082`.
4. **Acessando arquivos locais:** O arquivo `docker-compose.yml` utiliza a variável `KODEWEB_DOCKER_WORKSPACE_ROOT` definida no arquivo `.env` para saber qual pasta do seu computador ele deve exibir dentro da pasta `/workspace` do container. Edite o arquivo `.env` para apontar para a sua pasta ou unidade (ex: `KODEWEB_DOCKER_WORKSPACE_ROOT="D:\"`).

## 🔒 Segurança

Sempre proteja o seu acesso! Como a ferramenta gerencia arquivos do servidor hospedeiro, mantenha sua senha forte e evite expor o KodeWeb Explorer publicamente sem proteções extras caso esteja rodando em um servidor real.

## 📄 Licença

Criado e mantido por [Laralabs](https://laralabs.dev).
