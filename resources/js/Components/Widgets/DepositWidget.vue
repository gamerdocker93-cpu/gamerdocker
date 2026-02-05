<template>
    <div class="block">
        <!-- Seleção do gateway -->
        <div v-if="(paymentType == null || paymentType === '') && wallet && setting">
            <div>
                <ul>
                    <li
                        v-if="setting.sharkpay_is_enable"
                        @click="setPaymentMethod('pix', 'sharkpay')"
                        class="bg-white dark:bg-gray-900 cursor-pointer flex justify-between hover:bg-green-700/20 px-4 py-3 mb-3"
                    >
                        <div class="flex items-center gap-4">
                            <img :src="`/assets/images/pix.png`" alt="" width="100" />
                            <p>SHARKPAY</p>
                        </div>
                        <div class="flex justify-center items-center text-gray-500 gap-4">
                            <i class="fa-solid fa-chevron-right ml-2"></i>
                        </div>
                    </li>

                    <li
                        v-if="setting.digitopay_is_enable"
                        @click="setPaymentMethod('pix', 'digitopay')"
                        class="bg-white dark:bg-gray-900 cursor-pointer flex justify-between hover:bg-green-700/20 px-4 py-3 mb-3"
                    >
                        <div class="flex items-center gap-4">
                            <img :src="`/assets/images/pix.png`" alt="" width="100" />
                            <p>DIGITOPAY</p>
                        </div>
                        <div class="flex justify-center items-center text-gray-500 gap-4">
                            <i class="fa-solid fa-chevron-right ml-2"></i>
                        </div>
                    </li>
                </ul>

                <div v-if="!setting.sharkpay_is_enable && !setting.digitopay_is_enable" class="text-gray-500">
                    Nenhum gateway de depósito está habilitado no painel admin.
                </div>
            </div>
        </div>

        <!-- DIGITOPAY -->
        <div
            v-if="paymentType === 'pix' && paymentGateway === 'digitopay' && setting && setting.digitopay_is_enable"
            class="relative"
        >
            <div v-if="showPixQRCode && wallet" class="flex flex-col">
                <div class="w-full p-4 bg-white dark:bg-gray-700 rounded mb-3">
                    <div class="flex justify-between">
                        <h2 class="text-lg">
                            Falta pouco! Escaneie o código QR pelo seu app de pagamentos ou Internet Banking
                        </h2>
                        <div class="text-4xl">
                            <i class="fa-regular fa-circle-dollar"></i>
                        </div>
                    </div>
                </div>

                <div class="w-full p-4">
                    <p class="font-bold">
                        Valor do Pix a pagar: {{ state.currencyFormat(parseFloat(deposit.amount), wallet.currency) }}
                    </p>

                    <div class="p-3 flex justify-center items-center">
                        <QRCodeVue3 :value="qrcodecopypast" />
                    </div>

                    <p class="font-bold">Código válido por 23 horas.</p>

                    <div class="mt-4">
                        <p class="mb-3">Se preferir, você pode pagá-lo copiando e colando o código abaixo:</p>
                        <input id="pixcopiaecola" type="text" class="input w-full" v-model="qrcodecopypast" />
                    </div>

                    <div class="mt-5 w-full flex items-center justify-center">
                        <button @click.prevent="copyQRCode" type="button" class="ui-button-blue w-full">
                            <span class="uppercase font-semibold text-sm">{{ $t("Copy code") }}</span>
                        </button>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="ui-button-blue w-full" @click.prevent="resetPayment()">
                            Voltar
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!showPixQRCode">
                <div v-if="setting != null && wallet != null && isLoading === false" class="flex flex-col w-full">
                    <form action="" @submit.prevent="submitQRCode">
                        <div class="flex items-center justify-between">
                            <p class="text-gray-500">{{ $t("Deposit Currency") }}</p>
                            <button type="button" class="flex justify-center items-center mr-3 pt-1">
                                <div>{{ wallet.currency }}</div>
                                <div class="mr-2 ml-2">
                                    <img :src="`/assets/images/coin/BRL.png`" alt="" width="32" />
                                </div>
                                <div class="ml-2 text-sm">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </button>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-gray-500">{{ $t("Payment methods") }}</p>
                            <div class="w-full flex items-center justify-between bg-white dark:bg-gray-900 rounded p-2">
                                <div class="flex w-full items-center">
                                    <img :src="`/assets/images/pix.png`" alt="" width="100" />
                                    <span class="ml-3">PIX</span>
                                </div>
                                <div class="w-8">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="mb-2 text-gray-500">
                                {{ state.currencyFormat(parseFloat(setting.min_deposit), wallet.currency) }} -
                                {{ state.currencyFormat(parseFloat(setting.max_deposit), wallet.currency) }}
                            </p>
                            <div class="w-full flex items-center justify-between bg-white dark:bg-gray-900 rounded py-1">
                                <div class="flex w-full">
                                    <input
                                        type="text"
                                        v-model="deposit.amount"
                                        class="appearance-none border border-gray-300 rounded-md bg-transparent border-none w-full"
                                        :min="setting.min_deposit"
                                        :max="setting.max_deposit"
                                        :placeholder="$t('Enter the value here')"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-gray-500">
                            <p>
                                {{ $t("Get an extra bonus") }}
                                <strong class="text-white font-bold">{{ setting.initial_bonus }}%</strong>
                                {{ $t("on a minimum deposit of") }}
                                <strong class="text-white font-bold">
                                    {{ state.currencyFormat(parseFloat(setting.min_deposit), wallet.currency) }}
                                </strong>
                            </p>
                        </div>

                        <div class="mt-4">
                            <label class="inline-flex items-center mb-5 cursor-pointer">
                                <input type="checkbox" v-model="deposit.accept_bonus" value="" class="sr-only peer" />
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                                ></div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Aceitar Bônus</span>
                            </label>
                        </div>

                        <div class="mt-5 item-selected">
                            <div
                                @click.prevent="setAmount(parseFloat(setting.min_deposit))"
                                class="item"
                                :class="{ active: selectedAmount === parseFloat(setting.min_deposit) }"
                            >
                                <button type="button">
                                    {{ state.currencyFormat(parseFloat(setting.min_deposit), wallet.currency) }}
                                </button>
                                <div v-if="selectedAmount === parseFloat(setting.min_deposit)" class="ratio">
                                    +{{ setting.initial_bonus }}%
                                </div>
                                <img
                                    v-if="selectedAmount === parseFloat(setting.min_deposit)"
                                    class="img-check"
                                    :src="`/assets/images/check.webp`"
                                    alt=""
                                />
                            </div>

                            <div
                                @click.prevent="setAmount(50.0)"
                                class="item"
                                :class="{ active: selectedAmount === 50.0 }"
                            >
                                <button type="button">{{ wallet.symbol }} 50,00</button>
                                <div v-if="selectedAmount === 50.0" class="ratio">+{{ setting.initial_bonus }}%</div>
                                <img
                                    v-if="selectedAmount === 50.0"
                                    class="img-check"
                                    :src="`/assets/images/check.webp`"
                                    alt=""
                                />
                            </div>

                            <div
                                @click.prevent="setAmount(200.0)"
                                class="item"
                                :class="{ active: selectedAmount === 200.0 }"
                            >
                                <button type="button">{{ wallet.symbol }} 200,00</button>
                                <div v-if="selectedAmount === 200.0" class="ratio">+{{ setting.initial_bonus }}%</div>
                                <img
                                    v-if="selectedAmount === 200.0"
                                    class="img-check"
                                    :src="`/assets/images/check.webp`"
                                    alt=""
                                />
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="text-gray-500">CPF/CNPJ</p>
                            <input
                                type="text"
                                v-model="deposit.cpf"
                                v-maska
                                data-maska="[
                                    '###.###.###-##',
                                    '##.###.###/####-##'
                                ]"
                                class="mt-2 border-none text-gray-600 placeholder:text-gray-300 dark:text-gray-200 dark:placeholder:text-gray-500 w-full bg-white dark:bg-gray-900 font-sans transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-75 px-2 text-sm leading-5 rounded py-3"
                                placeholder="Digite o CPF"
                                required
                            />
                        </div>

                        <div class="mt-5 w-full flex items-center justify-center">
                            <button type="submit" class="ui-button-blue w-full">
                                <span class="uppercase font-semibold text-sm">{{ $t("Deposit") }}</span>
                            </button>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="ui-button-blue w-full" @click.prevent="resetPayment()">
                                Voltar
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="isLoading" role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2">
                    <svg
                        aria-hidden="true"
                        class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-green-600"
                        viewBox="0 0 100 101"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"
                        />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"
                        />
                    </svg>
                    <span class="sr-only">{{ $t("Loading") }}...</span>
                </div>
            </div>
        </div>

        <!-- SHARKPAY -->
        <div
            v-if="paymentType === 'pix' && paymentGateway === 'sharkpay' && setting && setting.sharkpay_is_enable"
            class="relative"
        >
            <div v-if="showPixQRCode && wallet" class="flex flex-col">
                <div class="w-full p-4 bg-white dark:bg-gray-700 rounded mb-3">
                    <div class="flex justify-between">
                        <h2 class="text-lg">
                            Falta pouco! Escaneie o código QR pelo seu app de pagamentos ou Internet Banking
                        </h2>
                        <div class="text-4xl">
                            <i class="fa-regular fa-circle-dollar"></i>
                        </div>
                    </div>
                </div>

                <div class="w-full p-4">
                    <p class="font-bold">
                        Valor do Pix a pagar: {{ state.currencyFormat(parseFloat(deposit.amount), wallet.currency) }}
                    </p>

                    <div class="p-3 flex justify-center items-center">
                        <QRCodeVue3 :value="qrcodecopypast" />
                    </div>

                    <p class="font-bold">Código válido por 23 horas.</p>

                    <div class="mt-4">
                        <p class="mb-3">Se preferir, você pode pagá-lo copiando e colando o código abaixo:</p>
                        <input id="pixcopiaecola" type="text" class="input w-full" v-model="qrcodecopypast" />
                    </div>

                    <div class="mt-5 w-full flex items-center justify-center">
                        <button @click.prevent="copyQRCode" type="button" class="ui-button-blue w-full">
                            <span class="uppercase font-semibold text-sm">{{ $t("Copy code") }}</span>
                        </button>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="ui-button-blue w-full" @click.prevent="resetPayment()">
                            Voltar
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!showPixQRCode">
                <div v-if="wallet != null && setting != null && isLoading === false" class="flex flex-col w-full">
                    <form action="" @submit.prevent="submitQRCode">
                        <div class="flex items-center justify-between">
                            <p class="text-gray-500">{{ $t("Deposit Currency") }}</p>
                            <button type="button" class="flex justify-center items-center mr-3 pt-1">
                                <div>{{ wallet.currency }}</div>
                                <div class="mr-2 ml-2">
                                    <img :src="`/assets/images/coin/BRL.png`" alt="" width="32" />
                                </div>
                                <div class="ml-2 text-sm">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </button>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-gray-500">{{ $t("Payment methods") }}</p>
                            <div class="w-full flex items-center justify-between bg-white dark:bg-gray-900 rounded p-2">
                                <div class="flex w-full items-center">
                                    <img :src="`/assets/images/pix.png`" alt="" width="100" />
                                    <span class="ml-3">PIX</span>
                                </div>
                                <div class="w-8">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="mb-2 text-gray-500">
                                {{ state.currencyFormat(parseFloat(setting.min_deposit), wallet.currency) }} -
                                {{ state.currencyFormat(parseFloat(setting.max_deposit), wallet.currency) }}
                            </p>
                            <div class="w-full flex items-center justify-between bg-white dark:bg-gray-900 rounded py-1">
                                <div class="flex w-full">
                                    <input
                                        type="text"
                                        v-model="deposit.amount"
                                        class="appearance-none border border-gray-300 rounded-md bg-transparent border-none w-full"
                                        :min="setting.min_deposit"
                                        :max="setting.max_deposit"
                                        :placeholder="$t('Enter the value here')"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="text-gray-500">CPF/CNPJ</p>
                            <input
                                type="text"
                                v-model="deposit.cpf"
                                v-maska
                                data-maska="[
                                    '###.###.###-##',
                                    '##.###.###/####-##'
                                ]"
                                class="mt-2 border-none text-gray-600 placeholder:text-gray-300 dark:text-gray-200 dark:placeholder:text-gray-500 w-full bg-white dark:bg-gray-900 font-sans transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-75 px-2 text-sm leading-5 rounded py-3"
                                placeholder="Digite o CPF"
                                required
                            />
                        </div>

                        <div class="mt-5 w-full flex items-center justify-center">
                            <button type="submit" class="ui-button-blue w-full">
                                <span class="uppercase font-semibold text-sm">{{ $t("Deposit") }}</span>
                            </button>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="ui-button-blue w-full" @click.prevent="resetPayment()">
                                Voltar
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="isLoading" role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2">
                    <svg
                        aria-hidden="true"
                        class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-green-600"
                        viewBox="0 0 100 101"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"
                        />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"
                        />
                    </svg>
                    <span class="sr-only">{{ $t("Loading") }}...</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useToast } from "vue-toastification";
import HttpApi from "@/Services/HttpApi.js";
import QRCodeVue3 from "qrcode-vue3";
import { useAuthStore } from "@/Stores/Auth.js";
import { StripeCheckout } from "@vue-stripe/vue-stripe";
import { useSettingStore } from "@/Stores/SettingStore.js";

export default {
    props: ["showMobile", "title", "isFull"],
    components: { QRCodeVue3, StripeCheckout },
    data() {
        return {
            isLoading: false,

            setting: null,
            wallet: null,

            deposit: {
                amount: "",
                cpf: "",
                gateway: "",
                accept_bonus: true,
                paymentType: "",
            },

            selectedAmount: 0,
            showPixQRCode: false,
            qrcodecopypast: "",
            idTransaction: "",
            intervalId: null,

            // começa vazio para o usuário escolher o gateway
            paymentType: null,
            paymentGateway: null,

            // stripe (mantido)
            elementsOptions: { appearance: {} },
            confirmParams: { return_url: null },
            successURL: null,
            cancelURL: null,
            amount: null,
            currency: null,
            publishableKey: null,
            sessionId: null,
        };
    },
    computed: {
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
    },
    beforeUnmount() {
        if (this.intervalId) clearInterval(this.intervalId);
    },
    methods: {
        resetPayment() {
            this.showPixQRCode = false;
            this.qrcodecopypast = "";
            this.idTransaction = "";
            if (this.intervalId) clearInterval(this.intervalId);
            this.intervalId = null;

            this.paymentType = null;
            this.paymentGateway = null;
        },
        setPaymentMethod(type, gateway) {
            this.paymentType = type;
            this.paymentGateway = gateway;
        },
        submitQRCode() {
            const _toast = useToast();

            if (!this.deposit.amount) {
                _toast.error(this.$t("You need to enter a value"));
                return;
            }
            if (!this.deposit.cpf) {
                _toast.error(this.$t("Do you need to enter your CPF or CNPJ"));
                return;
            }

            if (parseFloat(this.deposit.amount) < parseFloat(this.setting.min_deposit)) {
                _toast.error("O valor mínimo de depósito é de " + this.setting.min_deposit);
                return;
            }
            if (parseFloat(this.deposit.amount) > parseFloat(this.setting.max_deposit)) {
                _toast.error("O valor máximo de depósito é de " + this.setting.max_deposit);
                return;
            }

            this.deposit.paymentType = this.paymentType;
            this.deposit.gateway = this.paymentGateway;

            this.isLoading = true;

            HttpApi.post("wallet/deposit/payment", this.deposit)
                .then((response) => {
                    this.showPixQRCode = true;
                    this.isLoading = false;

                    this.idTransaction = response.data.idTransaction;
                    this.qrcodecopypast = response.data.qrcode;

                    this.intervalId = setInterval(() => {
                        this.checkTransactions(this.idTransaction);
                    }, 5000);
                })
                .catch((error) => {
                    try {
                        Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                            _toast.error(`${value}`);
                        });
                    } catch (e) {
                        _toast.error("Erro ao gerar pagamento.");
                    }
                    this.showPixQRCode = false;
                    this.isLoading = false;
                });
        },
        checkTransactions(idTransaction) {
            const _toast = useToast();

            if (!this.paymentGateway) return;

            HttpApi.post(this.paymentGateway + "/consult-status-transaction", { idTransaction })
                .then(() => {
                    _toast.success("Pedido concluído com sucesso");
                    if (this.intervalId) clearInterval(this.intervalId);
                    this.intervalId = null;
                })
                .catch(() => {
                    // silêncio
                });
        },
        copyQRCode() {
            const _toast = useToast();
            const inputElement = document.getElementById("pixcopiaecola");
            if (!inputElement) return;

            inputElement.select();
            inputElement.setSelectionRange(0, 99999);
            document.execCommand("copy");
            _toast.success("Pix Copiado com sucesso");
        },
        setAmount(amount) {
            this.deposit.amount = amount;
            this.selectedAmount = amount;
        },
        getWallet() {
            const _toast = useToast();

            HttpApi.get("profile/wallet")
                .then((response) => {
                    this.wallet = response.data.wallet;
                    this.currency = response.data.wallet.currency;
                })
                .catch((error) => {
                    try {
                        Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                            _toast.error(`${value}`);
                        });
                    } catch (e) {
                        _toast.error("Erro ao carregar carteira.");
                    }
                });
        },
        getSetting() {
            const settingStore = useSettingStore();
            const settingData = settingStore.setting;

            if (settingData) {
                this.setting = settingData;
                this.amount = settingData.max_deposit;
            }
        },
    },
    created() {
        if (this.isAuthenticated) {
            this.getWallet();
            this.getSetting();
        }
    },
};
</script>

<style scoped></style>
