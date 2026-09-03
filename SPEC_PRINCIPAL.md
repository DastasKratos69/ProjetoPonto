# SPEC_PRINCIPAL.md — Sistema PWA de Controle de Ponto e Banco de Horas

Este documento especifica os requisitos técnicos, arquitetura de software, fluxos de contingência e diretrizes de execução autônoma para o agente de IA **Google Jules**.

---

## 1. Instruções Especiais para Agente de IA (Google Jules)

- **Confirmação Prévia:** Não codifique se houver dúvidas em relação ao escopo ou aos requisitos técnicos. Solicite esclarecimentos antes de prosseguir.
- **Divisão de Tarefas:** Sempre divida programações extensas em tarefas e subtarefas incrementais.
- **Gestão de Histórico:** Mantenha e atualize continuamente o arquivo `backlog.md` no repositório GitHub, registrando todas as funcionalidades implementadas, ajustadas, alteradas ou pendentes.
- **Uso do Supabase:** Conexão e manipulação do banco de dados exclusivamente via cliente JS/API REST oficial da CDN Supabase.

---

## 2. Visão Geral do Projeto & Stack Tecnológica

| Componente | Tecnologia / Especificação |
| :--- | :--- |
| **Agente de Execução** | Google Jules |
| **Hospedagem & Repositório** | GitHub (GitHub Pages / Actions para deploy) |
| **Backend / Banco de Dados** | Supabase (PostgreSQL + REST API) |
| **Frontend Base** | HTML5, CSS3, JavaScript Vanilla (Sem frameworks como React, Vue ou Angular) |
| **Bibliotecas Permitidas** | - **Supabase JS SDK (CDN):** Comunicação com o backend.<br>- **Lucide Icons (CDN):** Biblioteca de ícones (sem uso de emojis na UI).<br>- **face-api.js / human.js (CDN):** Processamento e extração de vetores faciais touchless. |
| **PWA / Storage Local** | Service Workers + IndexedDB (Arquitetura Offline-First) |

---

## 3. Diretrizes de Interface e Experiência do Usuário (UI/UX)

- **Layout:** Design limpo, profissional, fundo estritamente branco (`#ffffff` ou `#f8fafc`), otimizado exclusivamente para visualização em tablets e computadores de mesa (desktop).
- **Proibição de Emojis:** A interface de usuário deve utilizar exclusivamente a biblioteca de ícones (Lucide Icons) em SVG. Emojis são proibidos no código-fonte e na interface.
- **Uso Restrito de APIs Nativas:** Acessar APIs nativas do navegador (Ex: `navigator.mediaDevices.getUserMedia`) somente quando estritamente necessário para captura de imagem via câmera.

---

## 4. Arquitetura e Regras de Negócio

### 4.1. Fluxo de Identificação Biométrica e Contingência
1. **Captura Touchless:** O colaborador posiciona-se em frente ao terminal e inicia o reconhecimento facial. O algoritmo deve extrair o vetor e comparar com a base local/remota em tempo inferior a 500ms.
2. **1ª Falha Facial:** Caso ocorra falha de leitura (acessórios, iluminação), o terminal exibe um alerta de 5 segundos: *"Ajuste sua posição e remova acessórios (óculos/boné)"*.
3. **2ª Falha Facial:** Se a segunda tentativa falhar, o sistema altera automaticamente a tela para a contingência manual via **Digitação de ID**.
4. **Exceção para Irmãos Gêmeos / Bloqueio Manual:** Colaboradores cadastrados como exceção são direcionados para autenticação por ID + validação no painel do RH.
5. **Proteção Contra Erros no ID:**
   - Ao digitar o ID, o sistema exibe o nome e a foto cadastrada por 2 segundos para confirmação do próprio colaborador.
   - Se ocorrerem 3 tentativas incorretas de ID seguidas, o terminal entra em estado de bloqueio por **3 minutos**.

### 4.2. Resiliência Offline-First & Sincronização
- **Armazenamento Local:** Em caso de perda de conexão à internet, os registros de ponto e fotos são armazenados imediatamente no banco local do navegador (**IndexedDB**).
- **Indicador Discreto de Estado:** Exibição de um pequeno badge/ícone amarelo discreto na tela indicando o número de registros pendentes de envio (Ex: *"Offline: 3 pendentes"*). A interface e o fluxo do usuário não devem travar ou apresentar banners invasivos.
- **Sincronização em Segundo Plano:** O Service Worker monitora o evento de reconexão e realiza o upload assíncrono dos dados locais para o Supabase (**Background Sync**).

---

## 5. Regras do Banco de Horas e Bloqueio pelo RH

- **Tolerância Padrão:** Tolerância de até 10 minutos na chegada ou saída sem aplicação de penalidades automáticas.
- **Trava Antiduplicidade Inter-Terminais:** Após uma marcação realizada com sucesso, o colaborador fica impossibilitado de efetuar novo registro em qualquer um dos dois terminais durante um intervalo de **5 minutos**.
- **Monitoramento de Déficit Crítico:** O backend recalcula o saldo do banco de horas continuamente. Ao atingir o limite negativo de **-20 horas no mês**, um gatilho envia um alerta para a fila de auditoria do RH.
- **Bloqueio de Acesso no Terminal:** Caso o RH confirme a ausência de justificativas pendentes para o saldo de -20h, o status do funcionário passa para `BLOQUEADO_RH`.
  - No terminal da fábrica, a tentativa de registro é recusada com a mensagem: *"Acesso não liberado. Por favor, dirija-se ao Departamento de Recursos Humanos."*

---

## 6. Estrutura Proposta do Arquivo `backlog.md`

```markdown
# Backlog de Desenvolvimento do Projeto

## Tarefas em Aberto
- [ ] Configuração inicial do repositório GitHub e estrutura de pastas.
- [ ] Implementação da conexão REST/API com o Supabase usando HTML/JS Vanilla.
- [ ] Criação do layout limpo PWA (fundo branco, Lucide Icons) para telas de Tablet/Desktop.
- [ ] Desenvolvimento do Service Worker e persistência em IndexedDB (Offline-First).
- [ ] Implementação do módulo de captura e validação facial com face-api.js.
- [ ] Construção da tela de contingência por ID e tratamento de bloqueio (3 tentativas / 3 min).
- [ ] Implementação da trava antiduplicidade de 5 minutos entre terminais.
- [ ] Construção da regra de saldo de banco de horas (-20h) e sinalização de bloqueio pelo RH.

## Tarefas Concluídas
- [x] Definição do documento de especificação técnica (SPEC_PRINCIPAL.md).
- [x] Criação da estrutura de modelo de dados SQL para o Supabase (SCHEMA.sql).
```
