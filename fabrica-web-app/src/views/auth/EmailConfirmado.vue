<template>
  <div class="email-page d-flex justify-content-center align-items-center">
    <div class="confirmation-box text-center p-5">
      <img src="/icons/icon-192x192.png" alt="Logo" />

      <div>
       <h1>E-mail confirmado com sucesso! ✅</h1>
        <p class="text-white">Agora você já pode acessar sua conta.</p>
      </div>

      <router-link to="/login" class="btn btn-primary mt-4 px-4 py-2">
        Ir para o Login
      </router-link>
    </div>
  </div>
</template>

<style scoped>
img {
  width: 96px;
  margin-bottom: 20px;
}
.email-page {
  height: 100vh;
  width: 100vw;
  background: #006b3e;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo {
  border-radius: 10%;
  padding: 10px;
}

.btn-primary {
  background-color: #007a5a;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #005c43;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
