<template>
  <div class="doc-page">
    <header class="doc-header">
      <h1 class="doc-title">Documentação Técnica do Sistema Fábrica</h1>
      <p class="doc-subtitle">
        Referência rápida para onboarding e manutenção do projeto (frontend + backend).
      </p>
    </header>

    <main class="doc-content">
      <section>
        <h2>1. Visão Geral</h2>
        <p>
          O <strong>Sistema Fábrica</strong> é uma aplicação web voltada para o gerenciamento de pedidos,
          produção, manutenção e serviços, com foco em ambientes industriais. A arquitetura é separada em
          <strong>frontend (SPA)</strong> e <strong>backend (API REST)</strong>, permitindo escalabilidade
          e manutenção independente.
        </p>
      </section>

      <section>
        <h2>2. Arquitetura Geral</h2>

        <h3>2.1 Stack Tecnológica</h3>

        <div class="grid">
          <div class="card">
            <h4>Frontend</h4>
            <ul>
              <li>Vue.js 3 (Composition API)</li>
              <li>TypeScript</li>
              <li>Vue Router</li>
              <li>Pinia (gerenciamento de estado)</li>
              <li>Axios (consumo de API)</li>
              <li>Bootstrap 5 (UI)</li>
            </ul>
          </div>

          <div class="card">
            <h4>Backend</h4>
            <ul>
              <li>PHP 8+</li>
              <li>Laravel</li>
              <li>API RESTful</li>
              <li>MySQL</li>
              <li>Autenticação via token (Sanctum/JWT)</li>
            </ul>
          </div>
        </div>
      </section>

      <section>
        <h2>3. Estrutura do Projeto</h2>

        <h3>3.1 Frontend (Vue)</h3>
        <pre class="code"><code>src/
            ├─ assets/
            ├─ components/
            │   ├─ layout/
            │   ├─ modals/
            │   └─ ui/
            ├─ views/
            │   ├─ ordem-producao/
            │   ├─ ordem-manutencao/
            │   ├─ ordem-servico/
            │   └─ auth/
            ├─ router/
            ├─ stores/
            ├─ services/
            └─ types/</code></pre>

        <p class="note"><strong>Pontos importantes:</strong></p>
        <ul>
          <li><code>services/axios.ts</code>: configuração central de chamadas HTTP</li>
          <li><code>stores/session.ts</code>: dados de autenticação e usuário logado</li>
          <li><code>router/index.ts</code>: definição de rotas e permissões</li>
        </ul>

        <h3>3.2 Backend (Laravel)</h3>
        <pre class="code"><code>app/
            ├─ Http/Controllers/Api/
            ├─ Models/
            │   └─ Pedido/
            ├─ Services/
            ├─ Classes/
            ├─ Policies/
            └─ Providers/</code></pre>

        <p class="note"><strong>Pontos importantes:</strong></p>
        <ul>
          <li>Controllers seguem padrão REST</li>
          <li>Services concentram regras de negócio complexas</li>
          <li>Models usam relacionamentos Eloquent</li>
        </ul>
      </section>

      <section>
        <h2>4. Fluxo de Autenticação</h2>
        <ol>
          <li>Usuário realiza login via API</li>
          <li>Backend autentica usando <strong>Laravel Sanctum</strong></li>
          <li>Token de acesso é emitido para o cliente</li>
          <li>Token é armazenado no Pinia / LocalStorage</li>
          <li>Axios injeta o token no header <code>Authorization</code></li>
          <li>Rotas protegidas utilizam <code>auth:sanctum</code></li>
        </ol>

        <p class="note">
          <strong>Observação técnica:</strong>
          As permissões e roles são gerenciadas via
          <em>Spatie Laravel Permission</em> e devem utilizar o mesmo
          <code>guard_name</code> configurado no backend.
        </p>
      </section>

      <section>
        <h2>5. Módulos do Sistema</h2>

        <h3>5.1 Pedidos</h3>
        <ul>
          <li>Criação de pedidos</li>
          <li>Itens vinculados ao pedido</li>
          <li>Status e etapas de produção</li>
          <li>Aprovação PCP</li>
        </ul>

        <h3>5.2 Produção</h3>
        <ul>
          <li>Painel de acompanhamento</li>
          <li>Etapas sequenciais</li>
          <li>Bloqueio/desbloqueio por regras de negócio</li>
        </ul>

        <h3>5.3 Manutenção</h3>
        <ul>
          <li>Registro de ordens de manutenção</li>
          <li>Histórico</li>
          <li>Status operacional</li>
        </ul>

        <h3>5.4 Serviços</h3>
        <ul>
          <li>Ordens de serviço independentes</li>
          <li>Fluxo semelhante à manutenção</li>
        </ul>
      </section>

      <section>
        <h2>6. Regras de Negócio Críticas</h2>

        <div class="callout">
          <ul>
            <li>
              Ações só são liberadas após <strong>todos os itens</strong> atingirem a
              <strong>etapa 18</strong>.
            </li>
            <li>
              Após a liberação, a ação permanece ativa mesmo se alguns itens avançarem além da etapa 18.
            </li>
            <li>
              Permissões são controladas por perfil de usuário.
            </li>
          </ul>
        </div>
      </section>

      <section>
        <h2>7. Padrões de Código</h2>

        <h3>Frontend</h3>
        <ul>
          <li>Composition API (<code>&lt;script setup&gt;</code>)</li>
          <li>Tipagem obrigatória (TypeScript)</li>
          <li>Componentes reutilizáveis</li>
          <li><code>computed</code> para estados derivados</li>
        </ul>

        <h3>Backend</h3>
        <ul>
          <li>Controllers enxutos (sem regra de negócio)</li>
          <li>Regras concentradas em <strong>Services</strong></li>
          <li>Autenticação de API via <strong>Laravel Sanctum</strong></li>
          <li>Autorização via <strong>Spatie Laravel Permission</strong></li>
          <li>Uso de middleware <code>auth:sanctum</code>, <code>role</code> e <code>permission</code></li>
        </ul>
      </section>

      <section>
        <h2>8. Convenções de Commit</h2>
        <ul>
          <li><code>feat:</code> nova funcionalidade</li>
          <li><code>fix:</code> correção de bug</li>
          <li><code>refactor:</code> refatoração</li>
          <li><code>chore:</code> ajustes técnicos</li>
        </ul>
      </section>

      <section>
        <h2>9. Ambiente de Desenvolvimento</h2>

        <h3>Variáveis de Ambiente</h3>
        <ul>
          <li>Backend: <code>.env</code></li>
          <li>Frontend: <code>.env</code> ou <code>.env.local</code></li>
        </ul>

        <h3>Principais Bibliotecas (Backend)</h3>
        <ul>
          <li><strong>laravel/sanctum</strong>: autenticação via token</li>
          <li><strong>spatie/laravel-permission</strong>: roles e permissões</li>
          <li><strong>spatie/laravel-activitylog</strong>: auditoria de ações</li>
          <li><strong>ably/ably-php</strong>: comunicação em tempo real</li>
        </ul>

        <h3>Comandos Principais</h3>

        <h4>Frontend</h4>
        <pre class="code"><code>npm install
          npm run dev</code></pre>

        <h4>Backend</h4>
        <pre class="code"><code>composer install
          php artisan migrate
          php artisan db:seed
          php artisan permission:cache-reset
          php artisan serve</code></pre>
      </section>

      <section>
        <h2>10. Boas Práticas</h2>
        <ul>
          <li>Nunca acessar banco diretamente no frontend</li>
          <li>Validar dados no backend</li>
          <li>Manter tipagem forte no Vue</li>
          <li>Criar testes para regras críticas</li>
        </ul>
      </section>

      <section>
        <h2>11. Contato Técnico</h2>
        <p>
          Em caso de dúvidas sobre regras de negócio ou arquitetura, consulte o desenvolvedor responsável pelo sistema.
        </p>
      </section>

      <!-- ============================
           SEÇÕES NOVAS (FRONTEND)
           ============================ -->

      <section>
        <h2>12. Arquitetura do Frontend</h2>
        <ul>
          <li><strong>Aplicação SPA</strong> construída em Vue 3 + Vite + TypeScript.</li>
          <li><strong>Rotas</strong> e proteção de navegação via Vue Router (<code>meta.requiresAuth</code> /
            <code>meta.permission</code>).</li>
          <li><strong>Estado global</strong> via Pinia (sessão, usuário, permissões, etc.).</li>
          <li><strong>Comunicação com API</strong> via Axios, com injeção de token e interceptors centralizados.</li>
          <li><strong>UI</strong> baseada em Bootstrap 5 (componentização e layout).</li>
          <li><strong>Grids e listagens avançadas</strong> via AG Grid.</li>
          <li><strong>Gráficos</strong> via ECharts / Vue-ECharts.</li>
          <li><strong>Recurso realtime</strong> (notificações / presença / eventos) via Ably + Laravel Echo.</li>
        </ul>

        <p class="note">
          <strong>Arquivos-chave:</strong> <code>src/main.ts</code>, <code>src/router/index.ts</code>,
          <code>src/stores/session.ts</code>, <code>src/services/axios.ts</code> e componentes de layout
          (Sidebar/Layout).
        </p>
      </section>

      <section>
        <h2>13. Autenticação e Permissões no Frontend</h2>

        <h3>13.1 Fonte da verdade</h3>
        <ul>
          <li>A store de sessão (<code>stores/session.ts</code>) deve concentrar: token, dados do usuário e permissões.
          </li>
          <li>O Axios deve anexar o token nas requisições (header <code>Authorization</code>).</li>
        </ul>

        <h3>13.2 Controle de acesso</h3>
        <ul>
          <li><strong>Rotas</strong>: protegidas por <code>meta.requiresAuth</code> e, quando aplicável,
            <code>meta.permission</code>.</li>
          <li><strong>Menus</strong>: o Sidebar/Layout normalmente filtra itens conforme permissões do usuário.</li>
        </ul>

        <div class="callout">
          <ul>
            <li><strong>Regra prática:</strong> se a rota não abre, verificar primeiro o guard do Router.</li>
            <li><strong>Se a rota abre por URL, mas o menu não aparece:</strong> verificar o filtro do Sidebar/Layout.
            </li>
            <li><strong>Após alterar permissões:</strong> resetar cache do Spatie no backend e refazer login para
              recarregar o estado no Pinia.</li>
          </ul>
        </div>
      </section>

      <section>
        <h2>14. Padrão de Página e Fluxo de Dados</h2>

        <h3>14.1 Fluxo típico</h3>
        <ol>
          <li>View (ex.: Painel) é carregada via Router.</li>
          <li>A View dispara chamadas em <code>services/axios.ts</code> (ou serviço específico) para consumir a API.
          </li>
          <li>Dados podem ser armazenados em estado local (refs) ou no Pinia (quando compartilhados).</li>
          <li>Componentes de UI renderizam tabelas/grids (AG Grid), gráficos (ECharts) e ações.</li>
        </ol>

        <h3>14.2 Convenções recomendadas</h3>
        <ul>
          <li>Centralizar requests e interceptors no Axios (evita duplicidade e facilita manutenção).</li>
          <li>Manter checagens de permissão consistentes (Router + Sidebar usando a mesma “fonte da verdade” da store).
          </li>
          <li>Evitar lógica de negócio espalhada na UI; preferir funções utilitárias e serviços.</li>
        </ul>

        <p class="note">
          <strong>Exemplo de referência:</strong> uma view “Painel” costuma ser um bom guia para padrão de consumo de
          API, layout e estados.
        </p>
      </section>

      <section>
        <h2>15. Resumo de Permissões por Setor (Roles)</h2>

        <p class="note">
          <strong>Atenção:</strong> evitar atribuir a role <code>admin</code> por conveniência.
          O sistema usa permissões granulares (Spatie) e menus também são controlados por permissões.
        </p>

        <div class="callout">
          <ul>
            <li><strong>Roles existentes:</strong> <code>operador</code>, <code>PCP</code>,
              <code>qualidade_operacional</code>, <code>qualidade_inspecao</code>, <code>qualidade_liberacao</code>,
              <code>admin</code>.</li>
            <li><strong>Fonte de verdade:</strong> <code>Database\\Seeders\\RolePermissionSeeder</code>.</li>
            <li><strong>Guard:</strong> o seeder usa <code>guard_name = web</code>. A autenticação da API usa Sanctum;
              manter consistência de guard evita erros de permissão.</li>
          </ul>
        </div>

        <h3>15.1 operador (Produção / Setor)</h3>
        <ul>
          <li><strong>Visualização (setor):</strong> visualizar fila/detalhes do item do setor.</li>
          <li><strong>Ações (setor):</strong> operar produção, registrar MP, reprovar item, upload, checklist,
            parâmetros.</li>
          <li><strong>Menus mínimos:</strong> <code>menu geral</code>, <code>menu dashboard</code>.</li>
          <li><strong>Observação:</strong> por padrão não recebe <code>documentacao api</code> (comentado no seeder).
          </li>
        </ul>

        <h3>15.2 PCP</h3>
        <ul>
          <li><strong>Visão global:</strong> visualizar ordens, pedidos aguardando produção, dados completos do pedido,
            produção completa, progresso e datas de entrega.</li>
          <li><strong>Documentos:</strong> criar OP/OS/OM; editar/modificar OP; gerenciar setores na OS; criar máquinas;
            inserir produtos; modificar status OP/OS/OM.</li>
          <li><strong>Checklists/Relatórios:</strong> criar checklists (OP/OM) e ver relatórios (OP/OS/OM).</li>
          <li><strong>Menus:</strong> recebe <strong>todos</strong> os menus (<code>$permsMenu</code>), incluindo
            <code>documentacao api</code>.</li>
        </ul>

        <h3>15.3 qualidade_operacional (Qualidade com atuação operacional)</h3>
        <ul>
          <li><strong>Visão global:</strong> mesmo pacote de visualização do PCP/Qualidade.</li>
          <li><strong>Setor:</strong> mesmas permissões de setor do operador (view + action).</li>
          <li><strong>Ações de qualidade (parcial):</strong> <code>inspecionar op</code>, <code>modificar it</code>,
            <code>mudar upload</code>.</li>
          <li><strong>Menus:</strong> recebe <strong>todos</strong> os menus (<code>$permsMenu</code>).</li>
        </ul>

        <h3>15.4 qualidade_inspecao</h3>
        <ul>
          <li><strong>Visão global:</strong> mesmo pacote de visualização do PCP/Qualidade.</li>
          <li><strong>Ações de inspeção:</strong> <code>inspecionar op</code>, <code>retornar etapas</code>,
            <code>reprovar item qualidade</code>, <code>ver reprovados por semestre</code>.</li>
          <li><strong>Menus:</strong> recebe <strong>todos</strong> os menus (<code>$permsMenu</code>).</li>
        </ul>

        <h3>15.5 qualidade_liberacao</h3>
        <ul>
          <li><strong>Visão global:</strong> mesmo pacote de visualização do PCP/Qualidade.</li>
          <li><strong>Ações de liberação:</strong> <code>liberar esterilizacao</code>, <code>liberar estoque</code> e
            <code>retornar etapas</code>.</li>
          <li><strong>Menus:</strong> recebe <strong>todos</strong> os menus (<code>$permsMenu</code>).</li>
        </ul>

        <h3>15.6 admin</h3>
        <ul>
          <li><strong>Administração:</strong> gerenciar usuários, aprovar solicitações, ver usuários online.</li>
          <li><strong>Acesso total:</strong> recebe todos os pacotes (admin + visão global + docs + qualidade + setor +
            menus).</li>
          <li><strong>Uso recomendado:</strong> apenas para administradores do sistema (não usar como “role coringa”).
          </li>
        </ul>

        <h3>15.7 Operação segura (como atribuir role corretamente)</h3>
        <ol>
          <li>Defina a área do usuário (Produção/PCP/Qualidade/Admin).</li>
          <li>Atribua a role correspondente (evitar admin).</li>
          <li>Se um menu/rota não aparecer, validar: (a) permissão na role, (b) cache do Spatie, (c) login novamente
            para recarregar permissões no frontend.</li>
        </ol>
      </section>

    </main>
  </div>
</template>

<script setup lang="ts">
// Doc.vue é um componente estático de documentação.
// Se desejar transformar em página navegável, registre uma rota apontando para este componente.
</script>

<style scoped>
.doc-page {
  padding: 16px;
  max-width: 1100px;
  margin: 0 auto;
  line-height: 1.55;
}

.doc-header {
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.02);
  margin-bottom: 16px;
}

.doc-title {
  margin: 0 0 8px 0;
  font-size: 22px;
  font-weight: 700;
}

.doc-subtitle {
  margin: 0;
  opacity: 0.8;
}

.doc-content section {
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  margin-bottom: 12px;
  background: #fff;
}

.doc-content h2 {
  margin: 0 0 12px 0;
  font-size: 18px;
  font-weight: 700;
}

.doc-content h3 {
  margin: 14px 0 8px 0;
  font-size: 15px;
  font-weight: 700;
}

.doc-content h4 {
  margin: 12px 0 6px 0;
  font-size: 14px;
  font-weight: 700;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 10px;
}

.card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  padding: 12px;
  background: rgba(0, 0, 0, 0.02);
}

.code {
  padding: 12px;
  border-radius: 10px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  overflow: auto;
  background: rgba(0, 0, 0, 0.03);
  margin: 8px 0 12px 0;
}

.note {
  margin: 10px 0 6px 0;
}

.callout {
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  background: rgba(0, 0, 0, 0.03);
}

@media (max-width: 900px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
