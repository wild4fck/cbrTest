<?php

namespace App\Services\RateService\Clients;

use Carbon\Carbon;
use RuntimeException;
use SimpleXMLElement;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\RateService\RateService;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class CbrClient extends BaseRateClientAbstract
{
    private const DAILY_REQUEST = 'XML_daily.asp';
    private const VAL_FULL_REQUEST = 'XML_valFull.asp';
    private const DYNAMIC_REQUEST = 'XML_dynamic.asp';
    
    /** @inheritDoc */
    public function getRate(Carbon $date, string $currencyCode, string $baseCurrencyCode): float
    {
        $dateString = $date->format('d.m.Y');
        $cacheKey = "rate_list:$dateString";
        
        //Поиск в кэше (если пусто - запрашиваем)
        $body = Cache::get($cacheKey);
        
        if (!$body) {
            try {
                //Запрос котировок валют на дату
                $response = $this->getRequest(self::DAILY_REQUEST, [
                    'date_req' => $dateString,
                ]);
                $body = (string)$response->getBody();
                //Сохранение в кэш
                Cache::put($cacheKey, $body);
            } catch (GuzzleException $e) {
                throw new RuntimeException('Request failed: ' . $e->getMessage());
            }
        }
        
        return $this->getRateFromXml($body, $currencyCode, $baseCurrencyCode);
    }
    
    
    /**
     * Возвращает 181 день, нулевой день используется для подсчета разницы первого с предыдущим
     * @inheritDoc
     */
    public function getHalfYearRates(string $currencyCharCode): array
    {
        $today = Carbon::today();
        $firstDate = (clone $today)->subDays(181);
        $todayFormat = $today->format('d.m.Y');
        $firstDateFormat = $firstDate->format('d.m.Y');
        
        $cacheKey = "half_year_rates_list:$todayFormat:$firstDateFormat:$currencyCharCode";
        
        $body = Cache::get($cacheKey);
        
        if (!$body) {
            $currencyCode = $this->getCurrencyCode($currencyCharCode);
            try {
                $response = $this->getRequest(self::DYNAMIC_REQUEST, [
                    'date_req1' => $firstDateFormat,
                    'date_req2' => $todayFormat,
                    'VAL_NM_RQ' => $currencyCode,
                ]);
        
                $body = (string)$response->getBody();
                Cache::put($cacheKey, $body);
            } catch (GuzzleException $e) {
                throw new RuntimeException('Request failed: ' . $e->getMessage());
            }
        }
        $res = [];
        foreach (simplexml_load_string($body)->Record as $record) {
            $res[] = [
                'date' => (string)$record->attributes()->Date,
                'value' => $this->xmlObjectValueToFloat($record->Value)
            ];
        }
    
        return $res;
    }
    
    /**
     * @return string
     */
    protected function getCurrencyCodes(): string
    {
        $cacheKey = 'currency_codes';
        
        $currencyCodesXmlString = Cache::get($cacheKey);
        
        if ($currencyCodesXmlString) {
            return $currencyCodesXmlString;
        }
        
        try {
            $response = $this->getRequest(self::VAL_FULL_REQUEST);
            $body = (string)$response->getBody();
            
            Cache::put($cacheKey, $body);
            
            return $body;
        } catch (GuzzleException $e) {
            throw new RuntimeException('Request failed: ' . $e->getMessage());
        }
    }
    
    /**
     * @param string $charCode
     *
     * @return string
     */
    protected function getCurrencyCode(string $charCode): string
    {
        $currencyCodesXmlString = $this->getCurrencyCodes();
        $item = simplexml_load_string($currencyCodesXmlString)->xpath("Item[ISO_Char_Code='$charCode']");
        
        if (empty($item)) {
            throw new RuntimeException("Currency '$charCode' not found.");
        }
        
        return trim((string)$item[0]->ParentCode);
    }
    
    /** @inheritDoc */
    protected function getBaseUrl(): string
    {
        return 'https://www.cbr.ru/scripts/';
    }
    
    /**
     * @param bool|\SimpleXMLElement $value
     *
     * @return null|float
     */
    private function xmlObjectValueToFloat(bool|SimpleXMLElement $value): ?float
    {
        return $value ? (float)str_replace(['.', ','], ['', '.'], (string)$value) : null;
    }
    
    /**
     * Получение и расчет курса из ответа ЦБРФ
     *
     * @param string $body
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     *
     * @return float|int
     */
    private function getRateFromXml(string $body, string $currencyCode, string $baseCurrencyCode): float|int
    {
        $xml = simplexml_load_string($body);
        $valute = $xml->xpath("Valute[CharCode='$currencyCode']");
        if (empty($valute)) {
            throw new RuntimeException('Rate not found.');
        }
        
        $valuteValue = $this->xmlObjectValueToFloat($valute[0]->Value);
        
        //Если базовая - Рубль, возвращаем курс, иначе высчитываем через рубль
        if ($baseCurrencyCode === RateService::DEFAULT_VALUTA_CODE) {
            return $valuteValue;
        }
        
        $baseValute = $xml->xpath("Valute[CharCode='$baseCurrencyCode']");
        if (empty($baseValute)) {
            throw new RuntimeException('Rate not found.');
        }
        
        return $valuteValue / $this->xmlObjectValueToFloat($baseValute[0]->Value);
    }
}
