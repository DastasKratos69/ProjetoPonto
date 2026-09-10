# Backlog de Desenvolvimento — Sistema PWA de Controle de Ponto e Banco de Horas

Este documento contém a divisão técnica em tarefas e subtarefas para a implementação completa do sistema de controle de ponto e banco de horas PWA, com base nas especificações (`SPEC_PRINCIPAL.md`) e modelo de dados (`SCHEMA.sql`).

---

## 📋 Resumo das Fases e Módulos

- **Módulo 1:** Infraestrutura Base & Backend Supabase
- **Módulo 2:** Interface do Usuário (UI/UX) & Estrutura PWA Base
- **Módulo 3:** Módulo Biométrico Touchless (Reconhecimento Facial)
- **Módulo 4:** Contingência por ID & Tratamento de Erros
- **Módulo 5:** Arquitetura Offline-First & Sincronização em Segundo Plano
- **Módulo 6:** Regras de Negócio do Banco de Horas & Integração com RH

---

## 🟩 Tarefas Concluídas
- [x] **[INFRA-01]** Definição da especificação técnica principal (`SPEC_PRINCIPAL.md`).
- [x] **[INFRA-02]** Modelagem do esquema de banco de dados PostgreSQL / Supabase (`SCHEMA.sql`).
- [x] **[PLAN-01]** Análise de requisitos e elaboração do detalhamento do backlog (`backlog.md`).

---

## 🔲 Tarefas em Aberto

### Módulo 1: Infraestrutura Base & Backend Supabase
- [ ] **[SUPA-01] Execução e validação do SCHEMA.sql no Supabase**
  - [ ] Criar tabelas (`users`, `terminals`, `time_entries`, `time_bank`).
  - [ ] Aplicar tipos customizados (`user_status`, `user_role`, `entry_method`).
  - [ ] Configurar políticas de segurança RLS (Row Level Security).
- [ ] **[SUPA-02] Módulo de Conexão Supabase JS SDK (CDN)**
  - [ ] Inicializar o cliente oficial da CDN Supabase em JavaScript Vanilla.
  - [ ] Criar wrappers para chamadas REST (inserção de ponto, consulta de usuário, consulta de banco de horas).
- [ ] **[SUPA-03] Seeds e Dados MOC de Teste**
  - [ ] Criar scripts de carga inicial para terminais (`TERM-01`, `TERM-02`).
  - [ ] Inserir usuários de teste (funcionário padrão, gêmeos com exceção, funcionário em saldo negativo -20h, funcionário bloqueado).

---

### Módulo 2: Interface do Usuário (UI/UX) & Estrutura PWA Base
- [ ] **[UI-01] Layout Base HTML5 & CSS3**
  - [ ] Implementar design limpo e moderno com fundo claro (`#ffffff` / `#f8fafc`).
  - [ ] Otimizar layout responsivo focado em Tablets e Desktop.
- [ ] **[UI-02] Integração Lucide Icons (CDN)**
  - [ ] Carregar biblioteca SVG do Lucide Icons via CDN.
  - [ ] Garantir ausência total de emojis na interface e nos fontes.
- [ ] **[PWA-01] Configuração de PWA & Service Worker Base**
  - [ ] Criar arquivo `manifest.json` para instalação como App PWA.
  - [ ] Implementar Service Worker inicial com estratégia de cache para assets estáticos.

---

### Módulo 3: Reconhecimento Facial Touchless (Biometria)
- [ ] **[BIO-01] Integração da Biblioteca Facial (CDN)**
  - [ ] Importar `face-api.js` / `human.js` via CDN.
  - [ ] Carregar os modelos neurais pré-treinados para detecção e vetorização de faces (128d).
- [ ] **[BIO-02] Captura de Vídeo Touchless**
  - [ ] Integrar stream de vídeo do terminal com `navigator.mediaDevices.getUserMedia`.
  - [ ] Implementar identificação contínua com tempo de resposta < 500ms.
- [ ] **[BIO-03] Fluxo de Contingência para Falha Facial**
  - [ ] **1ª Falha:** Exibir alerta por 5 segundos (*"Ajuste sua posição e remova acessórios (óculos/boné)"*).
  - [ ] **2ª Falha:** Redirecionar automaticamente para a tela de contingência manual por ID.
- [ ] **[BIO-04] Tratamento de Exceção para Irmãos Gêmeos**
  - [ ] Verificar flag `is_twin_exception`.
  - [ ] Redirecionar colaboradores sinalizados para identificação por ID + validação manual do RH.

---

### Módulo 4: Tela de Contingência Manual & Regras de ID
- [ ] **[ID-01] Interface do Teclado Numérico / Campo de ID**
  - [ ] Criar interface tátil para digitação do `employee_code`.
- [ ] **[ID-02] Confirmação Visual do Colaborador**
  - [ ] Exibir foto e nome cadastrado por 2 segundos após a digitação do ID para confirmação do próprio colaborador.
- [ ] **[ID-03] Bloqueio por Tentativas Incorretas de ID**
  - [ ] Contabilizar falhas consecutivas de ID.
  - [ ] Acionar temporizador de bloqueio por **3 minutos** após 3 tentativas incorretas consecutivas.

---

### Módulo 5: Resiliência Offline-First & Sincronização
- [ ] **[OFF-01] Persistência em IndexedDB**
  - [ ] Criar banco de dados local no navegador via IndexedDB.
  - [ ] Armazenar os registros de marcação (`time_entries`) e fotos locais quando sem conexão com a internet.
- [ ] **[OFF-02] Indicador Discreto de Estado Sync**
  - [ ] Exibir badge/ícone amarelo discreto indicando o número de batidas pendentes (Ex: *"Offline: 3 pendentes"*).
  - [ ] Garantir que o fluxo do usuário ocorra sem travamentos ou banners invasivos.
- [ ] **[OFF-03] Sincronização em Segundo Plano (Background Sync)**
  - [ ] Escutar reconexão com a rede no Service Worker.
  - [ ] Enviar batidas armazenadas no IndexedDB para a API Supabase assincronamente e atualizar o badge.

---

### Módulo 6: Regras de Negócio do Banco de Horas & Integração com RH
- [ ] **[BIZ-01] Trava Antiduplicidade Inter-Terminais (5 Minutos)**
  - [ ] Impedir novo registro do mesmo colaborador dentro de **5 minutos** após uma marcação bem-sucedida, em qualquer terminal.
- [ ] **[BIZ-02] Tolerância de Horários**
  - [ ] Aplicar margem de tolerância de até 10 minutos na chegada e saída.
- [ ] **[BIZ-03] Recálculo do Banco de Horas & Alerta de Déficit (-20h)**
  - [ ] Calcular saldo mensal do funcionário (`balance_minutes`).
  - [ ] Disparar aviso para auditoria do RH quando o saldo for igual ou inferior a **-20 horas (-1200 minutos)** (`requires_rh_review = true`).
- [ ] **[BIZ-04] Bloqueio de Registro por Decisão do RH**
  - [ ] Bloquear tentativas de batida de usuários com `status = 'BLOQUEADO_RH'`.
  - [ ] Exibir mensagem: *"Acesso não liberado. Por favor, dirija-se ao Departamento de Recursos Humanos."*
