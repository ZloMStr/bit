require('./bootstrap');

window.Vue = require('vue');

Vue.component('spinner-component', require('./components/SpinnerComponent.vue'));
Vue.component('main-freefunds', require('./components/dashboard-freefunds/MainFreefunds.vue'));
Vue.component('replenish-freefunds', require('./components/dashboard-freefunds/ReplenishFreefunds.vue'));
Vue.component('withdraw-freefunds', require('./components/dashboard-freefunds/WithdrawFreefunds.vue'));
Vue.component('transactions-freefunds', require('./components/dashboard-freefunds/TransactionsFreefunds.vue'));

import Vuex from 'vuex'
Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        markets: [],
        instruments: [],
        transactions: [],
    },

    getters: {
        markets: state => { return state.markets },
        instruments: state => { return state.instruments },
        transactions: state => { return state.transactions },
    },

    actions: {
        getMarkets (context) {
            axios.get('/api/internal/markets')
                .then((response) => {
                    context.commit('setMarkets', response.data)
                })
        },
        getInstruments (context) {
            axios.get('/api/internal/instruments')
                .then((response) => {
                    context.commit('setInstruments', response.data)
                })
        },
        getTransactions (context) {
            axios.get('/api/internal/freefunds-transactions')
                .then((response) => {
                    context.commit('setTransactions', response.data)
                })
        },
    },

    mutations: {
        setMarkets (state, markets) {
            state.markets = markets
        },
        setInstruments (state, instruments) {
            state.instruments = instruments
        },
        setTransactions (state, transactions) {
            state.transactions = transactions
        },
    }
})

const app = new Vue({
    el: '#app',
    store,
});
