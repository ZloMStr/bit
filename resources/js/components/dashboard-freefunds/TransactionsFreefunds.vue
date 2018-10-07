<template>
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>Free Funds Transactions</h5>
            <div class="ibox-tools"></div>
        </div>
        <div class="ibox-content"
             v-bind:class="{'sk-loading': $parent.isRequestData || isRequestData}">
            <spinner-component></spinner-component>

            <ul class="list-group clear-list m-t">
                    <li class="list-group-item" v-for="transaction in $parent.transactions" :key="transaction.id">
                        <span class="float-right">
                            <span v-bind:class="{
                                    'text-navy': transaction.type === 1,
                                    'text-danger': transaction.type === 2}">
                                <span v-if="transaction.type === 1">+</span><span v-if="transaction.type === 2">-</span>{{ transaction.amount }} <b>{{ getInstrumentTickerById(transaction.instrumentId) }}</b>
                            </span>
                            <span class=" label label-primary"
                                  v-bind:class="{
                                    'label-warning': transaction.status === 1,
                                    'label-primary': transaction.status === 2,
                                    'label-danger': transaction.status === 3,}">
                                <i class="fa"
                                   v-bind:class="{
                                    'fa-clock-o': transaction.status === 1,
                                    'fa-check-square-o': transaction.status === 2,
                                    'fa-times': transaction.status === 3,}"></i>
                            </span>
                        </span>

                        <i class="fa fa-calendar"></i> {{ transaction.created }}, {{ getMarketNameById(transaction.market) }}
                    </li>
                </ul>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                isRequestData: false,
            }
        },

        mounted() {

        },

        methods: {
            getInstrumentTickerById(id) {
                let items = this.$parent.instruments;
                for (let i in items) {
                    if (items[i].id === id) {
                        return items[i].ticker;
                    }
                }
                return 'N/A';
            },
            getMarketNameById(id) {
                let items = this.$parent.markets;
                for (let i in items) {
                    if (items[i].id === id) {
                        return items[i].name;
                    }
                }
                return 'N/A';
            }
        },
    }
</script>