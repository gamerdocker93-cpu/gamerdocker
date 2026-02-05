<template>
  <BaseLayout>
    <div class="md:w-4/6 2xl:w-4/6 m-auto px-4 md:px-0 py-6">
      <h1 class="text-2xl md:text-3xl font-bold text-white mb-4">
        {{ $t('Welcome Bonus') }}
      </h1>

      <div class="text-gray-300 leading-relaxed space-y-4">

        <p>
          Bem-vindo ao bônus de boas-vindas da plataforma {{ $t('Software Name') }}.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">1. Bônus Inicial</h2>
        <p>
          Todo novo usuário pode receber um bônus especial no primeiro depósito,
          conforme a promoção vigente.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">2. Condições</h2>
        <p>
          O bônus está sujeito a requisitos mínimos de apostas antes de permitir saque.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">3. Prazo</h2>
        <p>
          O bônus deve ser utilizado dentro do prazo informado na promoção.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">4. Uso Indevido</h2>
        <p>
          Tentativas de exploração, múltiplas contas ou fraude resultarão no
          cancelamento do bônus.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">5. Disposições Finais</h2>
        <p>
          A plataforma pode alterar ou encerrar o bônus a qualquer momento.
        </p>

      </div>
    </div>
  </BaseLayout>
</template>

<script>
import BaseLayout from "@/Layouts/BaseLayout.vue";

export default {
  name: "WelcomeBonus",
  components: { BaseLayout },
};
</script>

<style scoped>
/* vazio */
</style>