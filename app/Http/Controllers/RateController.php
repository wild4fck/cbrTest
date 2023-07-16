<?php

namespace App\Http\Controllers;

use Rate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ComparisonWithYesterdayRequest;

class RateController extends Controller
{
    /**
     * @param \App\Http\Requests\ComparisonWithYesterdayRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getComparisonWithYesterday(ComparisonWithYesterdayRequest $request): JsonResponse
    {
        try {
            $comparison = Rate::getComparisonWithYesterday(
                $request->date('date'),
                $request->currency_code,
                $request->base_currency_code,
            );
            
            return $this->successResponse($comparison, 'Rate comparison with yesterday');
        } catch (Exception $exception) {
            Log::error("Failed get Comparison With Yesterday. {$exception->getMessage()}", [
                'data' => $request->all(),
                'exception' => $exception
            ]);
            return $this->failureResponse($exception->getMessage());
        }
    }
}
