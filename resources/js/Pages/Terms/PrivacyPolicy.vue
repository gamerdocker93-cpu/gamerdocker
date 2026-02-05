<template>
  <BaseLayout>
    <div class="md:w-4/6 2xl:w-4/6 m-auto px-4 md:px-0 py-6">
      <h1 class="text-2xl md:text-3xl font-bold text-white mb-4">
        {{ $t('Privacy Policy') }}
      </h1>

      <div class="text-gray-300 leading-relaxed space-y-4">
        <p>
          {{ $t('Privacy Policy') }} — {{ $t('Software Name') }}
        </p>

        <p>
          Este é um texto padrão temporário. Você pode editar depois no arquivo:
          <strong>resources/js/Pages/Terms/PrivacyPolicy.vue</strong>
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">1. Coleta de dados</h2>
        <p>
          Coletamos informações fornecidas no cadastro (nome, e-mail/telefone, CPF quando exigido)
          e dados técnicos básicos (IP, navegador, dispositivo) para fins de segurança e funcionamento do serviço.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">2. Uso de dados</h2>
        <p>
          Utilizamos os dados para autenticação, prevenção a fraudes, suporte, processamento de pagamentos e
          melhoria da experiência.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">3. Compartilhamento</h2>
        <p>
          Podemos compartilhar dados com provedores essenciais (ex.: gateway de pagamento, serviços de e-mail),
          sempre que necessário para operar a plataforma.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">4. Cookies</h2>
        <p>
          Usamos cookies e armazenamento local para manter sessão, preferências e configurações de interface.
        </p>

        <h2 class="text-xl font-semibold text-white mt-6">5. Contato</h2>
        <p>
          Para dúvidas, entre em contato pelo suporte dentro da plataforma.
        </p>
      </div>
    </div>
  </BaseLayout>
</template>

<script>
import BaseLayout from "@/Layouts/BaseLayout.vue";

export default {
  name: "PrivacyPolicy",
  components: { BaseLayout },
};
</script>

<style scoped>
/* vazio mesmo */
</style>