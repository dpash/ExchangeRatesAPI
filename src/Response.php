<?php

namespace BenMajor\ExchangeRatesAPI;

final class Response
{
    # The actual Guzzle response:
    private \GuzzleHttp\Psr7\Response $response;
    
    # Core response:
    private array $headers;
    private string $bodyRaw;
    private mixed $body;
    
    # Properties:
    private int $statusCode;
    private string $timestamp;
    private string $baseCurrency;
    
    private array $rates = [ ];
    
    function __construct( \GuzzleHttp\Psr7\Response $response = null )
    {
        $this->response = $response;
        
        $this->headers    = $response->getHeaders();
        $this->bodyRaw    = $response->getBody()->getContents();
        $this->body       = json_decode( $this->bodyRaw );
        
        # Set our properties:
        $this->statusCode   = $response->getStatusCode();
        $this->timestamp    = date('c');
        $this->baseCurrency = $this->body->base;
        $this->rates        = $this->body->rates;
    }
    
    /****************************/
    /*                          */
    /*         GETTERS          */
    /*                          */
    /****************************/
    
    # Get the status code:
    public function getStatusCode(): int
    {
        return (int) $this->statusCode;
    }
    
    # Get the timestamp of the request:
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }
    
    # Get the base currency:
    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }
    
    # Get the exchange rates:
    public function getRates(): mixed
    {
        # Convert the rates to a key / value array:
        return json_decode( json_encode($this->rates), true );
    }
    
    # Return a specific rate:
    public function getRate( string $code = null ): float | null
    {
        $rates = $this->getRates();
        
        # If there's only one rate, and the code is null, return the first one:
        if( count($rates) == 1 && $code == null )
        {
            return reset( $rates );
        }
        
        if( $this->body->rates->{$code} )
        {
            return $this->body->rates->{$code};
        }
        
        return null;
    }
    
    # Convert the response to JSON:
    public function toJSON(): bool|string
    {
        return json_encode([
            'statusCode'   => $this->getStatusCode(),
            'timestamp'    => $this->getTimestamp(),
            'baseCurrency' => $this->getBaseCurrency(),
            'rates'        => $this->getRates()
        ]);
    }

    /**
     * @return \GuzzleHttp\Psr7\Response|null
     */
    public function getResponse(): ?\GuzzleHttp\Psr7\Response
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}