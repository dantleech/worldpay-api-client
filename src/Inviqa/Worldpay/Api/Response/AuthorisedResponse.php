<?php

namespace Inviqa\Worldpay\Api\Response;

use Inviqa\Worldpay\Api\Client\HttpResponse;
use Inviqa\Worldpay\Api\Response\PaymentService\Reply\OrderStatus\OrderCode;
use SimpleXMLElement;

class AuthorisedResponse
{
    /**
     * @var string
     */
    private $rawXml;

    /**
     * @var SimpleXMLElement
     */
    private $response;

    /**
     * @var string
     */
    private $machineCookie;

    /**
     * @var bool
     */
    private $successful;

    /**
     * @var OrderCode
     */
    private $orderCode;

    /**
     * @var array
     */
    private $cardDetails = [];

    /**
     * @var string
     */
    private $requestXml;

    public function __construct(HttpResponse $httpResponse, string $requestXml)
    {
        $this->rawXml = $httpResponse->content();
        $this->response = new SimpleXMLElement($this->rawXml, LIBXML_NOCDATA);
        $this->machineCookie = $httpResponse->cookie();
        $this->successful = $this->nodeValue("lastEvent") === "AUTHORISED";
        $this->orderCode = new OrderCode($this->nodeAttributeValue("orderStatus", "orderCode"));
        $this->setCardDetails();
        $this->requestXml = $requestXml;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function isError(): bool
    {
        return !empty($this->nodeValue('error'));
    }

    public function rawXml()
    {
        return $this->rawXml;
    }

    public function rawRequestXml()
    {
        return $this->requestXml;
    }

    public function orderCode()
    {
        return $this->orderCode;
    }

    public function errorCode()
    {
        return $this->nodeAttributeValue("error", "code");
    }

    public function errorMessage()
    {
        return trim($this->nodeValue('error'));
    }

    public function is3DSecure(): bool
    {
        return $this->hasNode('request3DSecure');
    }

    public function is3DSFlexChallengeRequired(): bool
    {
        return $this->hasNode('challengeRequired');
    }

    public function paRequestValue(): string
    {
        return $this->nodeValue('paRequest');
    }

    public function issuerURL(): string
    {
        return trim($this->nodeValue('issuerURL'));
    }

    public function machineCookie()
    {
        return $this->machineCookie;
    }

    public function cardDetails()
    {
        return $this->cardDetails;
    }

    public function payloadValue(): string
    {
        return $this->nodeValue('payload');
    }

    public function transactionId3DSValue(): string
    {
        return $this->nodeValue('transactionId3DS');
    }

    public function acsURLValue(): string
    {
        return $this->nodeValue('acsURL');
    }

    private function nodeValue(string $nodeName): string
    {
        $node = $this->findNodeByName($nodeName);
        if ($node) {
            return trim($node);
        }

        return '';
    }

    private function nodeAttributeValue(string $nodeName, string $attributeName): string
    {
        $node = $this->findNodeByName($nodeName);
        if ($node) {
            return (string)$node[$attributeName];
        }

        return '';
    }

    private function hasNode(string $nodeName): bool
    {
        return count($this->response->xpath("//$nodeName")) > 0;
    }


    private function setCardDetails(): void
    {
        if ($this->nodeValue('cardNumber')) {
            $this->cardDetails = [
                'creditCard' => [
                    'type' => $this->nodeValue('paymentMethod'),
                    "cardholderName" => $this->nodeValue('cardHolderName'),
                    'number' => $this->nodeValue('cardNumber'),
                ],
            ];
        }

        if ($this->hasNode('paymentMethodDetail')) {
            $this->cardDetails = [
                'creditCard' => [
                    'type' => $this->nodeValue('paymentMethod'),
                    'cardholderName' => $this->nodeValue('cardHolderName'),
                    'number' => $this->nodeAttributeValue('card', 'number'),
                    'expiryMonth' => $this->nodeAttributeValue('date', 'month'),
                    'expiryYear' => $this->nodeAttributeValue('date', 'year'),
                ],
            ];
        }
    }

    /**
     * @param string $nodeName
     *
     * @return null|SimpleXMLElement
     */
    private function findNodeByName(string $nodeName): ?SimpleXMLElement
    {
        $matchedNodes = $this->response->xpath("//$nodeName");
        $node = reset($matchedNodes);

        return is_object($node) ? $node : null;
    }
}
