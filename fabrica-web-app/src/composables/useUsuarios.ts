import { ref } from 'vue';
import api from '@/services/axios.ts';

interface Permissao {
  id: number;
  nome: string;
}

interface Usuario {
  id: number;
  name: string;
  email: string;
  status: string;
  setor_id?: number | string;
  setor?: { id: number; nome: string };
  permissoes?: Permissao[];
}

const usuarios = ref<Usuario[]>([]);
const perfil = ref<Usuario | null>(null);

const carregarUsuarios = async () => {
  try {
    const response = await api.get('/api/usuarios');
    usuarios.value = response.data.users;
  } catch (error: unknown) {
    console.error('Erro ao carregar usuários:', (error as any).response?.data || (error as any).message);
  }
};

const carregarUsuarioPorId = async (id: number) => {
  try {
    const response = await api.get(`/api/usuarios/${id}`);
    return response.data.usuario;
  } catch (error: unknown) {
    console.error('Erro ao carregar usuário:', (error as any).response?.data || (error as any).message);
    return null;
  }
};

const carregarPerfil = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await api.get('/api/perfil', {
      headers: { Authorization: `Bearer ${token}` }
    });
    perfil.value = response.data;
    return perfil.value;
  } catch (error: unknown) {
    console.error('Erro ao carregar perfil:', (error as any).response?.data || (error as any).message);
    return null;
  }
};

const atualizarUsuario = async (id: number, payload: Partial<Usuario>) => {
  try {
    await api.put(`/api/usuarios/${id}`, payload);
    const index = usuarios.value.findIndex(u => u.id === id);
    if (index !== -1) usuarios.value[index] = { ...usuarios.value[index], ...payload };
  } catch (error: unknown) {
    console.error('Erro ao atualizar usuário:', (error as any).response?.data || (error as any).message);
  }
};

const excluirUsuario = async (id: number) => {
  try {
    await api.delete(`/api/usuarios/${id}`);
    usuarios.value = usuarios.value.filter(u => u.id !== id);
  } catch (error: unknown) {
    console.error('Erro ao excluir usuário:', (error as any).response?.data || (error as any).message);
  }
};

export function useUsuarios() {
  return {
    usuarios,
    perfil,
    carregarUsuarios,
    carregarUsuarioPorId,
    carregarPerfil,
    atualizarUsuario,
    excluirUsuario,
  };
}
