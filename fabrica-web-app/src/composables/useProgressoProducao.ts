import { ref } from "vue";
import api from "@/services/axios";

// ---------------------------------------------------------------------------
// CHAMADA PRINCIPAL DO PROGRESSO
// ---------------------------------------------------------------------------
export function useProgressoProducao() {
  const pedido = ref<any>(null);
  const loading = ref(false);

  async function carregarPedido(pedidoId: number) {
    loading.value = true;
    try {
      const { data } = await api.get(`/api/producao/${pedidoId}`);
      pedido.value = data;
    } finally {
      loading.value = false;
    }
  }

  // -------------------------------------------------------------------------
  // FORMATAÇÃO DE STATUS
  // -------------------------------------------------------------------------
  function actionToStatus(acao?: string, tipo_decisao?: string | null) {
    if (!acao) return "pendente";
    const a = acao.toUpperCase();

    if (a === "FINALIZACAO") return "concluida";
    if (a === "INICIO" || a === "INÍCIO") return "em_andamento";
    if (a === "PAUSA") return "pausada";
    if (a === "REPROVACAO") return "reprovada";

    if (a === "DECISAO") {
      if (tipo_decisao === "RETRABALHO") return "retrabalho";
      if (tipo_decisao === "REFUGO") return "refugo";
    }

    return "pendente";
  }

  // -------------------------------------------------------------------------
  // FORMATAÇÃO DE DATAS
  // -------------------------------------------------------------------------
  function formatDisplay(value?: string | null) {
    if (!value) return "--";
    const dt = new Date(value);
    if (isNaN(dt.getTime())) return "--";
    return dt.toLocaleDateString("pt-BR");
  }

  // -------------------------------------------------------------------------
  // PROGRESSO TOTAL (já vindo da API, mas fallback simples)
  // -------------------------------------------------------------------------
  function calcularProgressoTotal(itens: any[]) {
    if (!itens?.length) return 0;
    const soma = itens.reduce((s, i) => s + (i.progresso ?? 0), 0);
    return Math.round(soma / itens.length);
  }

  return {
    pedido,
    loading,
    carregarPedido,
    actionToStatus,
    formatDisplay,
    calcularProgressoTotal,
  };
}
