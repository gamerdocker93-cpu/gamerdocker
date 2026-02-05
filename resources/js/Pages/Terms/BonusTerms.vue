<template>
  <BaseLayout>
    <div class="md:w-4/6 2xl:w-4/6 m-auto px-4 md:px-0 py-6">
      <h1 class="text-2xl md:text-3xl font-bold text-white mb-4">
        {{ $t('Bonus Terms') }}
      </h1>

      <div class="text-gray-300 leading-relaxed space-y-4">

        <p>
          Estes são os Termos de Bônus da plataforma {{ $t('Software Name') }}.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">1. Elegibilidade</h2>
        <p>
          Os bônus são destinados apenas a usuários registrados e verificados.
          Cada usuário pode usufruir das promoções conforme as regras vigentes.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">2. Uso do Bônus</h2>
        <p>
          Valores de bônus devem ser utilizados exclusivamente em apostas ou jogos
          permitidos na plataforma.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">3. Requisitos de Rollover</h2>
        <p>
          Antes de solicitar saque, o usuário deve cumprir o requisito de apostas
          (rollover) definido para cada promoção.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">4. Cancelamento</h2>
        <p>
          A plataforma se reserva o direito de cancelar bônus em caso de fraude,
          abuso ou violação dos termos.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">5. Alterações</h2>
        <p>
          Os termos podem ser alterados a qualquer momento, sem aviso prévio.
        </p>

      </div>
    </div>
  </BaseLayout>
</template>

<script>
import BaseLayout from "@/Layouts/BaseLayout.vue";

export default {
  name: "BonusTerms",
  components: { BaseLayout },
};
</script>

<style scoped>
/* vazio */
</style>