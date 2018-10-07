<template>
    <div class="row">
        <div class="col-lg-4">
            <replenish-freefunds></replenish-freefunds>
            <withdraw-freefunds></withdraw-freefunds>
        </div>
        <div class="col-lg-4">
            <transactions-freefunds></transactions-freefunds>
        </div>
        <div class="col-lg-4">

        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                isRequestData: true,
            }
        },

        mounted() {
            this.$store.dispatch('getMarkets')
            this.$store.dispatch('getInstruments')
            this.$store.dispatch('getTransactions')
            this.isRequestData = false;
        },

        computed: {
            markets() {
                return this.$store.getters.markets
            },
            instruments() {
                return this.$store.getters.instruments
            },
            transactions() {
                return this.$store.getters.transactions
            },
        },

        methods: {

        },
    }
</script>