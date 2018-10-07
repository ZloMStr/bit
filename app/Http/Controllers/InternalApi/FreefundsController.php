<?php

namespace App\Http\Controllers\InternalApi;

use App\Models\Freefunds;
use App\Models\Instrument;
use App\Services\MarketApi\MarketApiInterface;
use App\Services\MarketApi\MarketFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FreefundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'operation' => 'required',
            'amount' => 'required|min:0',
            'instrumentId' => 'required|integer',
        ]);

        $user = $request->user();
        $amount = bcadd((double)$request->get('amount'), 0, 8);
        $instrument = Instrument::findOrFail((int)$request->get('instrumentId'));

        try {
            DB::beginTransaction();

            /* @var $freefunds Freefunds */
            $freefunds = Freefunds::lockForUpdate()->firstOrNew([
                'user_id' => $user->id,
                'instrument_id' => $instrument->id,
            ], [
                'user_id' => $user->id,
                'instrument_id' => $instrument->id,
            ]);

            switch ($request->get('operation')) {
                case 'replenish':
                    /* @var $market MarketApiInterface */
                    $market = MarketFactory::build($user, $instrument->market);
                    if (! $market->isInsufficientFunds($instrument, $amount)) {
                        $freefunds->replenish($amount);
                    }
                    break;

                case 'withdraw':
                    if (! $freefunds->isInsufficientFunds($amount)) {
                        $freefunds->withdraw($amount);
                    }
                    break;

                default:
                    throw new \Exception('Operation not found.');
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            return new JsonResponse($exception->getMessage(), 422);
        }

        return new JsonResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
