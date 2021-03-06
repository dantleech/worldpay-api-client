<?php

namespace spec\Inviqa\Worldpay\Notification\Response;

use Inviqa\Worldpay\Notification\Response\NotificationResponse;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotificationResponseSpec extends ObjectBehavior
{
    const CAPTURED_NOTIFICATION = '
<paymentService version="1.4" merchantCode="SESSIONECOM">
  <notify>
    <orderStatusEvent orderCode="123456-1234"> <!--The orderCode you supplied in the order-->
      <payment>
        <paymentMethod>VISA-SSL</paymentMethod>
          <amount value="1000" currencyCode="EUR" exponent="2" debitCreditIndicator="credit"/>
        <lastEvent>CAPTURED</lastEvent>
        <riskScore value="0"/>
      </payment>
      <journal journalType="CAPTURED" sent="n"></journal>
    </orderStatusEvent>
  </notify>
</paymentService>
';

    const SENT_FOR_REFUND_NOTIFICATION = '
<paymentService version="1.4" merchantCode="SESSIONECOM"> 
  <notify>
    <orderStatusEvent orderCode="123456">
      <payment>
        <paymentMethod>VISA-SSL</paymentMethod>
          <amount value="1000" currencyCode="GBP" exponent="2" debitCreditIndicator="credit"/>
        <lastEvent>SENT_FOR_REFUND</lastEvent>
        <reference>{"notifyClient":true,"returnNumber":"RN0000000"}</reference>
        <balance accountType="IN_PROCESS_CAPTURED">
          <amount value="1000" currencyCode="GBP" exponent="2" debitCreditIndicator="credit"/>
        </balance>
        <cardNumber>5255********2490</cardNumber>
        <riskScore value="0"/>
      </payment>
      <journal journalType="SENT_FOR_REFUND" sent="n">
          <bookingDate>
            <date dayOfMonth="01" month="01" year="2020"/>
          </bookingDate>
          <accountTx accountType="SETTLED_BIBIT_NET" batchId="10">
            <amount value="900" currencyCode="GBP" exponent="2" debitCreditIndicator="debit"/>
          </accountTx>
          <accountTx accountType="IN_PROCESS_CAPTURED" batchId="17">
            <amount value="900" currencyCode="GBP" exponent="2" debitCreditIndicator="credit"/>
          </accountTx> 
          <journalReference type="refund" reference=""/>
      </journal> 
    </orderStatusEvent>
  </notify>
</paymentService>
';

    function it_returns_true_if_a_captured_event_is_available()
    {
        $this->beConstructedFromRawNotification(self::CAPTURED_NOTIFICATION);

        $this->isSuccessful()->shouldBe(true);
    }

    function it_returns_the_order_code()
    {
        $this->beConstructedFromRawNotification(self::CAPTURED_NOTIFICATION);

        $this->orderCode()->shouldBe("123456-1234");
    }

    function it_returns_true_when_the_last_event_is_captured()
    {
        $this->beConstructedFromRawNotification(self::CAPTURED_NOTIFICATION);

        $this->isCaptured()->shouldBe(true);
    }

    function it_returns_true_if_a_refunded_event_is_available()
    {
        $this->beConstructedFromRawNotification(self::SENT_FOR_REFUND_NOTIFICATION);

        $this->isSuccessful()->shouldBe(true);
    }

    function it_returns_true_when_the_last_event_is_refunded()
    {
        $this->beConstructedFromRawNotification(self::SENT_FOR_REFUND_NOTIFICATION);

        $this->isRefunded()->shouldBe(true);
    }

    function it_returns_the_refund_value_from_the_journal_node_in_the_notification()
    {
        $this->beConstructedFromRawNotification(self::SENT_FOR_REFUND_NOTIFICATION);

        $this->refundValue()->shouldBe(900);
    }

    function it_returns_the_reference_from_reference_node_in_the_notification()
    {
        $this->beConstructedFromRawNotification(self::SENT_FOR_REFUND_NOTIFICATION);

        $this->reference()->shouldBe('{"notifyClient":true,"returnNumber":"RN0000000"}');
    }
}
