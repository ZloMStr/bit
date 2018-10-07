<template>
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>Free Funds Release
                <small></small>
            </h5>
            <div class="ibox-tools"></div>
        </div>
        <div class="ibox-content"
             v-bind:class="{'sk-loading': $parent.isRequestData || isRequestData}">
            <spinner-component></spinner-component>
            <div class="row">

                <div class="col-sm-6 b-r">
                    <form role="form">
                        <div class="form-group">
                            <label>Market:</label>
                            <select class="form-control" v-model="config.marketId" v-on:change="reset()">
                                <option v-for="market in $parent.markets" :key="market.id"
                                        v-bind:value="market.id">
                                    {{ market.name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount:</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-right" placeholder="0,00" v-model="config.amount"/>
                                <div class="input-group-append">
                                    <button class="btn btn-default dropdown-toggle no-margins" data-toggle="dropdown"
                                            aria-expanded="false">
                                        <img :src="'/img/assets/' + config.instrument.icon" width="18"/> {{config.instrument.ticker }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li v-for="instrument in $parent.instruments" :key="instrument.id"
                                            v-if="instrument.market === config.marketId"
                                            @click="config.instrument = instrument">
                                            <a><img :src="'/img/assets/' + instrument.icon" class="pull-left m-r-sm"/>
                                                {{ instrument.ticker}}, {{ instrument.name }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <small>Available: -,-- <b>N/A</b></small>
                        </div>
                    </form>
                    <div>
                        <button class="btn btn-sm btn-primary pull-right m-t-n-xs" @click="withdraw()"><b>Release</b></button>
                    </div>
                </div>

                <div class="col-sm-6">
                    <p class="text-center"><i class="fa fa-unlock big-icon"></i></p>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {notify} from '../../notify.js'

    export default {
        data: function () {
            return {
                isRequestData: false,

                instrumentDefault: {
                    id: 0,
                    ticker: 'N/A',
                    icon: 'coin.png',
                },

                config: {
                    marketId: 0,
                    instrument: {},
                    amount: null,
                },
            }
        },

        mounted() {
            this.reset();
        },

        methods: {
            reset() {
                this.config.instrument = this.instrumentDefault;
            },

            withdraw() {
                this.isRequestData = true;

                axios
                    .patch('/api/internal/freefunds/0', {
                        operation: 'withdraw',
                        instrumentId: this.config.instrument.id,
                        amount: this.config.amount,
                    })
                    .then(() => {
                        this.$store.dispatch('getTransactions')
                        notify.success('Release funds successfully!')
                    })
                    .catch(({response}) => notify.error(response))
                    .finally(() => {
                        this.isRequestData = false;
                    });
            },
        },
    }
</script>