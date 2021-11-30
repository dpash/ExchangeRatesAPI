<?PHP

/**
 * A simple SDK wrapper for the ExchangeRatesAPI (https://exchangeratesapi.io/)
 * Written by Ben Major (https://github.com/benmajor)
 *
 * @link      https://github.com/benmajor/PHP-ExchangeRatesAPI
 * @copyright Copyright (c) 2019 Ben Major
 * @license   https://github.com/benmajor/PHP-ExchangeRatesAPI/blob/master/LICENSE (MIT License)
 */

namespace BenMajor\ExchangeRatesAPI;

use \GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class ExchangeRatesAPI
{
    # Default API URL:
    const API_URL_SSL = 'https://api.exchangerate.host/';
    
    # Free plan API URL:
    const API_URL_NON_SSL = 'http://api.exchangerate.host/';

    # Regular Expression for the date:
    const DATE_REGEX = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';

    # Regular Expression for the currency:
    const CURRENCY_REGEX = '/^[A-Z]{3}$/';

    # Fetch date
    private string $fetchDate;

    # Date from which to request historic rates:
    private string | null $dateFrom;
    
    # Date to which to request historic rates:
    private string | null  $dateTo;
    
    # The base currency (default is EUR):
    private string $baseCurrency;
    
    # Exchange rates to fetch
    private array $rates = [ ];
    
    # Contains our Guzzle client:
    private Client $client;
    
    # The URL of the API:
    private string $apiURL = self::API_URL_SSL;
    
    # Supported currencies:
    private array $_currencies = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD',
        'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF',
        'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP',
        'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY',
        'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK',
        'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP',
        'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ',
        'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS',
        'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD',
        'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD',
        'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL',
        'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT',
        'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN',
        'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB',
        'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON',
        'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK',
        'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP',
        'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD',
        'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF',
        'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR',
        'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL',
        ];
    
    # Error messages:
    private array $_errors = [
        'format.invalid_date'          => 'The specified date is invalid. Please use ISO 8601 notation (e.g. YYYY-MM-DD).',
        'format.invalid_currency_code' => 'The specified currency code (%s) is invalid. Please use ISO 4217 notation (e.g. EUR).',
        'format.unsupported_currency'  => 'The specified currency code (%s) is not currently supported.',
        'format.invalid_amount'        => 'Conversion amount must be specified as a numeric value.',
        'format.invalid_rounding'      => 'Rounding precision must be specified as a numeric value.'
    ];
    
    # ExchangeRatesAPI Access Key:
    private string $access_key;
    
    function __construct( string $access_key = null, bool $use_ssl = true )
    {
        $this->setAccessKey($access_key);
        $this->setUseSSL($use_ssl);
        
        $this->client = new Client([ 'base_uri' => $this->apiURL ]);
    }
    
    /****************************/
    /*                          */
    /*         GETTERS          */
    /*                          */
    /****************************/

    /** Get the fetch date date:
     * @return string|null
     */
    public function getFetchDate(): ?string
    {
        return $this->dateFrom;
    }

    # Get the "from" date:
    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }
    
    # Get the "to" date:
    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }
    
    # Get the supported currencies:
    public function getSupportedCurrencies( string $concat = null ): array|string
    {
        return (is_null($concat)) ? $this->_currencies : implode($concat, $this->_currencies);
    }
    
    # Get the specified base currency:
    public function getBaseCurrency(): string
    {
        return (is_null($this->baseCurrency)) ? 'EUR' : $this->baseCurrency;
    }
    
    # Get the rates:
    public function getRates( string $concat = null ): array|string
    {
        return (! is_null($concat) ) ? implode($concat, $this->rates) : $this->rates;
    }
    
    # Get access key:
    public function getAccessKey(): string
    {
        return $this->access_key;
    }
    
    # Get boolean flag for SSL usage:
    public function getUseSSL(): bool
    {
        return ($this->apiURL == self::API_URL_SSL);
    }
    
    /****************************/
    /*                          */
    /*  SETTERS / DATA METHODS  */
    /*                          */
    /****************************/

    /** set the fetch date
     * @throws Exception
     */
    public function setFetchDate(string $date ): ExchangeRatesAPI
    {
        if(!$this->validateDateFormat($date)) {
            throw new Exception($this->_errors['format.invalid_date']);
        }

        $this->fetchDate = $date;

        # Return object to preserve method-chaining:
        return $this;
    }

    /** Add a date-from:
     * @throws Exception
     */
    public function addDateFrom(string $from ): ExchangeRatesAPI
    {
        if(!$this->validateDateFormat($from)) {
            throw new Exception($this->_errors['format.invalid_date']);
        }

        $this->dateFrom = $from;

        # Return object to preserve method-chaining:
        return $this;
    }
    
    # Remove a date-from:
    public function removeDateFrom(): ExchangeRatesAPI
    {
        $this->dateFrom = null;
        
        # Return object to preserve method-chaining:
        return $this;
    }

    /** Add a date-to:
     * @throws Exception
     */
    public function addDateTo(string $to ): ExchangeRatesAPI
    {
        if(!$this->validateDateFormat($to)) {
            throw new Exception($this->_errors['format.invalid_date']);
        }

        $this->dateTo = $to;

        # Return object to preserve method-chaining:
        return $this;
    }
    
    # Remove the date-to:
    public function removeDateTo(): ExchangeRatesAPI
    {
        $this->dateTo = null;
        
        # Return object to preserve method-chaining:
        return $this;
    }

    /** Check if a currency code is in the supported range:
     * @throws Exception
     */
    public function currencyIsSupported(string $code ): bool
    {
        $currencyCode = $this->sanitizeCurrencyCode($code);
        
        if( ! $this->validateCurrencyCodeFormat($currencyCode) )
        {
            throw new Exception( sprintf($this->_errors['format.invalid_currency_code'], $currencyCode) );
        }
        
        return in_array( $currencyCode, $this->_currencies );
    }

    /** Set the base currency:
     * @throws Exception
     */
    public function setBaseCurrency(string $currency ): ExchangeRatesAPI
    {
        # Sanitize the code:
        $currencyCode = $this->sanitizeCurrencyCode($currency);
        
        # Is it valid?
        $this->verifyCurrencyCode( $currencyCode );
        
        $this->baseCurrency = $currencyCode;
        
        # Return object to preserve method-chaining:
        return $this;
    }

    /** Add multiple currencies at once
     * @throws Exception
     */
    public function addRates(array $currencies ): ExchangeRatesAPI
    {
        foreach ($currencies as $currency)
        {
            $this->addRate($currency);
        }
        return $this;
    }

    /** Add a currency to the returned rates:
     * @throws Exception
     */
    public function addRate(string $currency ): ExchangeRatesAPI
    {
        # Sanitize the code:
        $currencyCode = $this->sanitizeCurrencyCode($currency);
        
        $this->verifyCurrencyCode($currencyCode);
        
        $this->rates[] = $currencyCode;
        
        # Return object to preserve method-chaining:
        return $this;
    }

    /** Remove multiple currencies at once
     * @throws Exception
     */
    public function removeRates(array $currencies ): ExchangeRatesAPI
    {
        foreach ($currencies as $currency)
        {
            $this->removeRate($currency);
        }
        return $this;
    }

    /** Remove a currency from the returned rates:
     * @throws Exception
     */
    public function removeRate(string $currency ): ExchangeRatesAPI
    {
        # Sanitize the code:
        $currencyCode = $this->sanitizeCurrencyCode($currency);
        
        # Verify it's valid:
        $this->verifyCurrencyCode($currencyCode);
        
        $newRates = [ ];
        
        # Loop over the rates and check them against the currency to remove:
        foreach( $this->getRates() as $rate )
        {
            if( $rate != $currencyCode )
            {
                $newRates[] = $rate;
            }
        }
        
        # Copy the temp array to the rates:
        $this->rates = $newRates;
        
        # Return object to preserve method chaining:
        return $this;
    }
    
    # Set access key:
    public function setAccessKey( string $access_key = null ): ExchangeRatesAPI
    {
        $this->access_key = $access_key;
        
        # Return object to preserve method chaining:
        return $this;
    }

    # Set SSL flag and API URL:
    public function setUseSSL( bool $use_ssl = true ): ExchangeRatesAPI
    {
        if ( $use_ssl )
        {
            $this->apiURL = self::API_URL_SSL;
        }
        else
        {
            $this->apiURL = self::API_URL_NON_SSL;
        }
        
        return $this;
    }

    /****************************/
    /*                          */
    /*   API FUNCTION CALLS     */
    /*                          */
    /****************************/

    /** Static function to quickly make a conversion:
     * @throws Exception|GuzzleException
     */
    public function convert(string $to, float $amount, int $rounding = 2 ): float
    {
        $currencyTo = $this->sanitizeCurrencyCode($to);
        
        # Check it's an allowed currency:
        $this->verifyCurrencyCode($to);
        
        if( !is_numeric($amount) )
        {
            throw new Exception( $this->_errors['format.invalid_amount'] );
        }
        
        if( ! is_numeric($rounding) )
        {
            throw new Exception( $this->_errors['format.invalid_rounding'] );
        }
        
        # Now get the response:
        $rate = $this->addRate($currencyTo)->fetch()->getRate();
        
        return round(
            ($amount * $rate),
            $rounding
        );
    }

    /** Send off the request:
     * @throws GuzzleException
     * @throws Exception
     */
    public function fetch(bool $returnJSON = false, bool $parseJSON = true ): Response | false | string
    {
        # Build the URL:
        $params = [ ];
        
        # Set access key if available:
        if ( !is_null($this->getAccessKey()) )
        {
            $params['access_key'] = $this->getAccessKey();
        }

        # Set the relevant endpoint:
        if( is_null($this->dateFrom) )
        {
            $endpoint = is_null($this->fetchDate) ? 'latest' : $this->fetchDate;
        }
        else
        {
            $endpoint = 'history';
        }
        
        # Add dateFrom if specified:
        if( ! is_null($this->getDateFrom()) )
        {
            $params['start_at'] = $this->getDateFrom();
        }
        
        # Add a dateTo:
        if( ! is_null($this->getDateTo()) )
        {
            $params['end_at'] = $this->getDateTo();
        }
        
        # Set the base currency:
        if( ! is_null($this->getBaseCurrency()) )
        {
            $params['base'] = $this->getBaseCurrency();
        }
        
        # Are there any rates set?
        if( count($this->rates) > 0 )
        {
            $params['symbols'] = $this->getRates(',');
        }
        
        # Begin the sending:
        try
        {
            $guzzleResponse = $this->client->request('GET', $endpoint, [ 'query' => $params ]);
            
            $response = new Response( $guzzleResponse );
            
            if(!$returnJSON) {
                return $response;
            }

            $json = $response->toJSON();

            if (!$parseJSON) {
                return $json;
            }

            return json_decode($json);

        }
        catch( \Exception $e )
        {
            throw new Exception( $e->getMessage() );
        }
    }
    
    /****************************/
    /*                          */
    /*  INTERNAL VERIFICTATION  */
    /*                          */
    /****************************/
    
    # Validate a date is in the correct format:
    private function validateDateFormat( string $date = null ): bool|int
    {
        if(is_null($date)) {
            return false;
        }
        return preg_match(self::DATE_REGEX, $date);

    }
    
    # Validate a currency code is in the correct format:
    private function validateCurrencyCodeFormat( string $code = null ): bool
    {
        if(is_null($code)) {
            return false;
        }

        # Is the string longer than 3 characters?
        if (strlen($code) != 3) {
            return false;
        }

        # Does it contain non-alphabetical characters?
        return (bool)preg_match(self::CURRENCY_REGEX, $code);


    }

    /** Runs tests to verify a currency code:
     * @throws Exception
     */
    private function verifyCurrencyCode(string $code ): void
    {
        $currencyCode = $this->sanitizeCurrencyCode($code);
        
        # Is the currency code in ISO 4217 format?
        if( ! $this->validateCurrencyCodeFormat($currencyCode) )
        {
            throw new Exception( sprintf($this->_errors['format.invalid_currency_code'], $currencyCode) );
        }
        
        # Is it a supported currency?
        if( ! $this->currencyIsSupported($currencyCode) )
        {
            throw new Exception( sprintf($this->_errors['format.unsupported_currency'], $currencyCode) );
        }
    }
    
    # Sanitize a currency code:
    private function sanitizeCurrencyCode( string $code ): string
    {
        return trim(
            strtoupper( $code )
        );
    }
}
