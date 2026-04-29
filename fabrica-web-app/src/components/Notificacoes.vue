<template>
  <div>
    <!-- BOTÃO FLUTUANTE -->
    <div class="position-fixed end-0 bottom-0 m-3" style="z-index: 9999;">
      <button
        @click="toggle"
        class="btn btn-primary rounded-circle shadow position-relative d-flex justify-content-center align-items-center"
        style="width: 60px; height: 60px;"
      >
        <i class="bi bi-bell-fill fs-3"></i>

        <!-- BADGE -->
        <span
          v-if="totalNaoLidas > 0"
          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
        >
          {{ totalNaoLidas }}
        </span>
      </button>
    </div>

    <!-- PAINEL -->
    <div class="position-fixed painel-notif" :style="estilosPainel">
      <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary">
        <h6 class="text-secondary m-0">Notificações</h6>

        <button class="btn btn-sm btn-outline-secondary" @click="toggle">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="mt-3" style="overflow-y: auto; max-height: 260px;">
        <div
          v-for="(n, index) in notificacoes"
          :key="index"
          class="alert py-2 notif-card"
        >
          <div class="d-flex gap-2">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>

            <div class="d-flex flex-column">
              <div class="fw-bold mb-1">
                Pedido {{ n.dados.numero_pedido }} — Item {{ n.dados.item_codigo }}
              </div>

              <small><strong>Produto:</strong> {{ n.dados.item_nome }}</small>
              <small><strong>Etapa reprovada:</strong> {{ n.dados.etapa }}</small>
              <small><strong>Responsável:</strong> {{ n.dados.resp }}</small>
              <small><strong>Observação:</strong> {{ n.dados.obs || 'Nenhuma' }}</small>
              <small class="text-muted mt-1">{{ n.dados.data }}</small>
            </div>
          </div>

          <button class="btn btn-sm btn-link text-primary ps-0" @click="irParaPedido(n)">
            Ver pedido →
          </button>
        </div>

        <p v-if="notificacoes.length === 0" class="text-center text-secondary">
          Nenhuma notificação
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { createAblyClient } from "@/services/ably";

const router = useRouter();

const aberto = ref(false);
const notificacoes = ref([]);

// ------------------------------
// PERSISTÊNCIA LOCAL
// ------------------------------
function salvarLocal() {
  localStorage.setItem("notificacoes", JSON.stringify(notificacoes.value));
}

function carregarLocal() {
  const salvo = localStorage.getItem("notificacoes");
  if (salvo) {
    const parsed = JSON.parse(salvo);
    notificacoes.value = parsed.map(n => ({
      ...n,
      lida: false 
    }));
  }
}

// ------------------------------
// ABRIR / FECHAR PAINEL
// ------------------------------
const toggle = () => {
  aberto.value = !aberto.value;
};

// ------------------------------
// NÚMERO DE NÃO LIDAS (BADGE)
// ------------------------------
const totalNaoLidas = computed(() =>
  notificacoes.value.filter(n => !n.lida).length
);

// ------------------------------
// IR PARA O PEDIDO (SEM APAGAR NOTIFICAÇÃO)
// ------------------------------
function irParaPedido(n) {
  // Marca como lida, mas não remove
  n.lida = true;
  salvarLocal();

  router.push({
    name: "pedido-producao",
    state: { id: n.dados.pedido_id },
  });
}

// ------------------------------
// ESTILOS DO PAINEL
// ------------------------------
const estilosPainel = computed(() => ({
  bottom: aberto.value ? "95px" : "30px",
  right: "30px",
  opacity: aberto.value ? "1" : "0",
  pointerEvents: aberto.value ? "auto" : "none",
  transform: aberto.value ? "scale(1)" : "scale(0.9)",
  transition: "all .25s ease",
  width: "32em",
  maxHeight: "350px",
  zIndex: "9998",
  padding: "15px",
  borderRadius: "20px",
  backdropFilter: "blur(55px)",
  background: "rgba(255, 255, 255, 0.10)",
  border: "1px solid rgba(255, 255, 255, 0.3)",
  boxShadow: "0 8px 25px rgba(0,0,0,0.25)",
}));

// ------------------------------
// ABLY
// ------------------------------
onMounted(() => {
  carregarLocal();

  const client = createAblyClient();
  if (!client) return;

  const canal = client.channels.get("kanban");

  canal.subscribe("pedido-reprovado", (msg) => {
    const d = msg.data;

    const nova = {
      lida: false,
      dados: {
        pedido_id: d.pedido_id,
        numero_pedido: d.numero_pedido,
        item_codigo: d.item_codigo,
        item_nome: d.item_nome,
        etapa: d.etapa_reprovada,
        resp: d.reprovado_por_nome,
        obs: d.observacao,
        data: d.data,
      },
    };

    notificacoes.value.unshift(nova);
    salvarLocal();
  });
});
</script>

<style scoped>
.notif-card {
  border-radius: 12px !important;
  padding: 12px 14px !important;
  border-left: 5px solid #f1c40f;
  background: rgba(255, 255, 200, 0.25);
  backdrop-filter: blur(4px);
}

.notif-card small {
  line-height: 1.4;
}
</style>
