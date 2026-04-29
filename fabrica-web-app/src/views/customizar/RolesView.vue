<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import Swal from 'sweetalert2'
import { usePermissionsStore } from '@/stores/rbacpermissionsStore'
import { useRolesStore } from '@/stores/rbacrolesStore'
import { useRolePermissionsStore } from '@/stores/rbacrolePermissionsStore'

const rolesStore = useRolesStore()
const permissionsStore = usePermissionsStore()
const rolePermStore = useRolePermissionsStore()

const newRole = ref('')
const newPerm = ref('')
const selectedRoleId = ref<number | null>(null)
const searchPerm = ref('')
const currentPage = ref(1)
const itemsPerPage = 10

const selectedRoleName = computed(() => {
  const role = rolesStore.roles.find((r) => r.id === selectedRoleId.value)
  return role?.name || ''
})


const filteredPermissions = computed(() => {
  return permissionsStore.permissions.filter((perm) =>
    perm.name.toLowerCase().includes(searchPerm.value.toLowerCase())
  )
})


const paginatedPermissions = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage
  return filteredPermissions.value.slice(startIndex, startIndex + itemsPerPage)
})


const totalPages = computed(() => {
  return Math.ceil(filteredPermissions.value.length / itemsPerPage)
})

const addRole = async () => {
  if (!newRole.value) return
  try {
    await rolesStore.createRole(newRole.value)
    newRole.value = ''
    Swal.fire({
      icon: 'success',
      title: 'Papel criado!',
      text: 'O papel foi criado com sucesso.',
      confirmButtonText: 'OK',
    })
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Houve um erro ao criar o papel. Tente novamente.',
      confirmButtonText: 'Tentar novamente',
    })
  }
}

const addPermission = async () => {
  if (!newPerm.value) return
  try {
    await permissionsStore.createPermission(newPerm.value)
    newPerm.value = ''
    Swal.fire({
      icon: 'success',
      title: 'Permissão criada!',
      text: 'A permissão foi criada com sucesso.',
      confirmButtonText: 'OK',
    })
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Houve um erro ao criar a permissão. Tente novamente.',
      confirmButtonText: 'Tentar novamente',
    })
  }
}

const togglePermission = async (roleId: number, permId: number) => {
  const role = rolePermStore.rolePermissions.find((r) => r.roleId === roleId)
  try {
    if (role && role.permissionIds.includes(permId)) {
      await rolePermStore.removePermission(roleId, permId)
      Swal.fire({
        icon: 'success',
        title: 'Permissão removida!',
        text: 'A permissão foi removida com sucesso.',
        confirmButtonText: 'OK',
      })
    } else {
      await rolePermStore.assignPermission(roleId, permId)
      Swal.fire({
        icon: 'success',
        title: 'Permissão atribuída!',
        text: 'A permissão foi atribuída com sucesso.',
        confirmButtonText: 'OK',
      })
    }
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Houve um erro ao atualizar as permissões. Tente novamente.',
      confirmButtonText: 'Tentar novamente',
    })
  }
}

const hasPermission = (roleId: number | null, permId: number) => {
  if (!roleId) return false
  const role = rolePermStore.rolePermissions.find((r) => r.roleId === roleId)
  return role ? role.permissionIds.includes(permId) : false
}

const confirmDelete = async (message: string, onConfirm: () => Promise<void> | void) => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar',
  })

  if (result.isConfirmed) {
    try {
      await onConfirm()
      Swal.fire('Excluído!', 'A ação foi concluída com sucesso.', 'success')
    } catch (error) {
      console.error(error)
      Swal.fire('Erro!', 'Houve um erro ao excluir o item. Tente novamente.', 'error')
    }
  }
}

const deleteRole = (roleId: number) =>
  confirmDelete('Este papel será excluído permanentemente.', () => rolesStore.deleteRole(roleId))

const deletePermission = (permId: number) =>
  confirmDelete('Essa permissão será excluída permanentemente.', () =>
    permissionsStore.deletePermission(permId),
  )

onMounted(() => {
  rolesStore.loadRoles()
  permissionsStore.loadPermissions()
  rolePermStore.loadRolePermissions()
})
</script>


<template>
  <div class="container py-5">
    <div class="mb-5 text-center">
      <h3 class="fw-bold text-dark mb-1">
        <i class="bi bi-shield-lock me-2 text-success"></i> Gerenciamento de Acesso
      </h3>
      <p class="text-muted">Controle os papéis e permissões do sistema</p>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
          <div
            class="card-header border-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center"
          >
            <h5 class="fw-semibold text-secondary mb-2 mb-sm-0">Papéis</h5>
            <div class="input-group input-group-sm" style="max-width: 50%">
              <input
                v-model="newRole"
                class="form-control rounded-start"
                placeholder="Novo papel"
              />
              <button class="btn btn-outline-primary rounded-end" @click="addRole">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <table class="table table-hover table-borderless mb-0 align-middle">
              <thead class="bg-light text-muted">
                <tr>
                  <th>Papel</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="role in rolesStore.roles"
                  :key="role.id"
                  :class="{ 'bg-primary bg-opacity-10': selectedRoleId === role.id }"
                  class="transition"
                  @click="selectedRoleId = role.id"
                  style="cursor: pointer"
                >
                  <td>
                    <template v-if="role.editing">
                      <input
                        v-model="role.name"
                        class="form-control form-control-sm"
                        @blur="
                          () => {
                            rolesStore.updateRole(role.id, role.name)
                            role.editing = false
                          }
                        "
                      />
                    </template>
                    <template v-else>
                      {{ role.name }}
                    </template>
                  </td>
                  <td class="text-end text-nowrap">
                    <button
                      class="btn btn-sm btn-light me-1"
                      @click.stop="role.editing = true"
                      title="Editar"
                    >
                      <i class="bi bi-pencil text-primary"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-light"
                      @click.stop="deleteRole(role.id)"
                      title="Excluir"
                    >
                      <i class="bi bi-trash text-danger"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div
            class="card-header bg-white border-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center"
          >
            <h5 class="fw-semibold text-secondary mb-2 mb-sm-0">
              Permissões para:
              <span class="text-primary">{{ selectedRoleName || 'Selecione um papel' }}</span>
            </h5>
            <div v-if="selectedRoleId" class="input-group input-group-sm" style="max-width: 50%">
              <input
                v-model="newPerm"
                class="form-control rounded-start"
                placeholder="Nova permissão"
              />
              <button class="btn btn-outline-primary rounded-end" @click="addPermission">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <template v-if="selectedRoleId">
              <div class="input-group input-group-sm mb-3" style="max-width: 50%">
                <input
                  v-model="searchPerm"
                  class="form-control rounded-start"
                  placeholder="Filtrar permissões"
                />
              </div>
              <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered align-middle mb-0">
                  <thead class="bg-light text-muted">
                    <tr>
                      <th>Permissão</th>
                      <th>Status</th>
                      <th class="text-end">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="perm in paginatedPermissions" :key="perm.id">
                      <td>
                        <template v-if="perm.editing">
                          <input
                            v-model="perm.name"
                            class="form-control form-control-sm"
                            @blur="() => {
                              permissionsStore.updatePermission(perm.id, perm.name)
                              perm.editing = false
                            }"
                          />
                        </template>
                        <template v-else>
                          {{ perm.name }}
                        </template>
                      </td>
                      <td>
                        <span
                          class="badge px-3 py-2"
                          :class="hasPermission(selectedRoleId, perm.id) ? 'bg-success' : 'bg-secondary'"
                        >
                          {{ hasPermission(selectedRoleId, perm.id) ? 'Ativo' : 'Inativo' }}
                        </span>
                      </td>
                      <td class="text-end text-nowrap">
                        <button
                          class="btn btn-sm me-1"
                          :class="hasPermission(selectedRoleId, perm.id) ? 'btn-outline-danger' : 'btn-outline-success'"
                          @click="togglePermission(selectedRoleId, perm.id)"
                        >
                          <i :class="hasPermission(selectedRoleId, perm.id) ? 'bi bi-x-circle' : 'bi bi-check-circle'"></i>
                        </button>
                        <button class="btn btn-sm btn-light me-1" @click.stop="perm.editing = true">
                          <i class="bi bi-pencil text-primary"></i>
                        </button>
                        <button class="btn btn-sm btn-light" @click.stop="deletePermission(perm.id)">
                          <i class="bi bi-trash text-danger"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginação -->
              <div class="d-flex justify-content-center mt-3">
                <button
                  class="btn btn-outline-secondary"
                  :disabled="currentPage === 1"
                  @click="currentPage--"
                >
                  Anterior
                </button>
                <span class="mx-2">{{ currentPage }} / {{ totalPages }}</span>
                <button
                  class="btn btn-outline-secondary"
                  :disabled="currentPage === totalPages"
                  @click="currentPage++"
                >
                  Próximo
                </button>
              </div>
            </template>
            <template v-else>
              <div class="text-muted text-center py-4">
                <i class="bi bi-arrow-left-circle me-2"></i>
                Selecione um papel à esquerda para ver as permissões
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
