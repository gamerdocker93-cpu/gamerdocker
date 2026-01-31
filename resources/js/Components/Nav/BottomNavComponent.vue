<template>
    <div class="flex sm:hidden">
        <div
            class="fixed z-40 w-full h-16 max-w-lg -translate-x-1/2 bg-white border border-gray-200 bottom-0 left-1/2 dark:bg-gray-800 dark:border-gray-800"
        >
            <div class="grid h-full max-w-lg grid-cols-5 mx-auto">

                <!-- HOME -->
                <RouterLink
                    to="/"
                    class="inline-flex flex-col items-center justify-center px-5 rounded-l-full hover:bg-gray-50 dark:hover:bg-gray-800 group"
                >
                    <i class="fa-duotone fa-house-chimney mb-1 text-xl"></i>
                    <span class="text-[12px]">{{ $t('Home') }}</span>
                </RouterLink>

                <!-- CASINO -->
                <RouterLink
                    to="/casinos"
                    class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group"
                >
                    <i class="fa-duotone fa-dice mb-1 text-xl"></i>
                    <span class="text-[12px]">{{ $t('Casino') }}</span>
                </RouterLink>

                <!-- DEPOSIT -->
                <RouterLink
                    to="/profile/deposit"
                    class="inline-flex items-center justify-center w-10 h-10 font-medium bg-green-700 rounded-full hover:bg-green-700 group focus:ring-4 focus:ring-green-300 focus:outline-none dark:focus:ring-blue-800"
                >
                    <i class="fa-solid fa-dollar-sign"></i>
                    <span class="sr-only">{{ $t('Deposit') }}</span>
                </RouterLink>

                <!-- AFFILIATE -->
                <RouterLink
                    to="/profile/affiliate"
                    class="inline-flex flex-col items-center justify-center px-5 rounded-r-full hover:bg-gray-50 dark:hover:bg-gray-800 group"
                >
                    <i class="fa-solid fa-users mb-1 text-xl"></i>
                    <span class="text-[12px]">Convidar</span>
                </RouterLink>

                <!-- WALLET -->
                <RouterLink
                    to="/profile/wallet"
                    class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group"
                >
                    <i class="fa-duotone fa-wallet mb-1 text-xl"></i>
                    <span class="text-[12px]">{{ $t('Wallet') }}</span>
                </RouterLink>

            </div>
        </div>
    </div>
</template>

<script>
import { RouterLink } from 'vue-router';

export default {
    components: {
        RouterLink,
    },
};
</script>

<style scoped>
</style>