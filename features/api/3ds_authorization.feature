Feature: A payment authorization request is made against the Worldpay payment gateway

  Notes: the encryptedData value in the follow scenario is representative of the below test card details:
  - card number: 444333322221111
  - cvc: 123
  - expiry month: 5
  - expiry year: 2025
  - card holder name: 3D - this value is going to always trigger a test 3DS response from the API

  Scenario: Payment authorization with 3DS reply
    When I authorize the following payment
      | merchantCode     | SESSIONECOM                                                            |
      | orderCode        | 42796904y                                                              |
      | description      | some description                                                       |
      | currencyCode     | GBP                                                                    |
      | value            | 15                                                                     |
      | encryptedData    | trigger-3ds                                                            |
      | address1         | 4                                                                      |
      | address2         | Braford Gardens                                                        |
      | address3         | Shenley Brook End                                                      |
      | postalCode       | MK137QJ                                                                |
      | city             | Milton Keynes                                                          |
      | state            | Buckingamshire                                                         |
      | countryCode      | GB                                                                     |
      | shopperIPAddress | 123.123.123.123                                                        |
      | sessionId        | 0215ui8ib1                                                             |
      | email            | lpanainte+test@inviqa.com                                              |
      | acceptHeader     | text/html                                                              |
      | userAgentHeader  | Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) |
    Then I should receive a 3d secure response
    And the response should reference the "42796904y" order code
    And the response should reference a valid "paRequest" value
    And the response should reference the following issuerURL: "https://secure-test.worldpay.com/jsp/test/shopper/ThreeDResponseSimulator.jsp?orderCode=42796904y"

  Scenario: 3D Secure payment authorisation completion
    When the authorization for the following payment is completed
      | merchantCode | SESSIONECOM                                                                                                                                                                                                                                                                                                                                                                                                                                              |
      | orderCode    | reiss-9mar-2                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | paResponse   | eJx9UsFuwjAMve8rqt4haaGjIGPUqSD1AEKjk3atWqtEoi1LWsT+fklga7dp8yXx87P97ARW1+rkXEgq0dRL1xtz16E6bwpRl0v3Jd2MQneFkB4lUXygvJOEsCWlspIcUeiMwOfB3A+92dTzXIR99Ezqd2SsRFlToQn3XqhbjX1gn64uKvNjVrcIWf72lOzQ6w3YHYOKZBIPQ8ZGPe8WB9ZX23fmprTsqygwif60JTDDgCJrCX3uhXzC547HF8FkMZkBszicTbmoajpd2w84BzZEQC9I6v29Y+g/AvvygK7npiaTA+zrDqwXt492yAd2G8egkL4itKL6IWq6MM0tDqrN2k5hEq93abJJ1jGwOwR5drng9t+pLQUoF8gDrU6fNis6lY0U7bEymr8DwIwmZt8a4aCfVjeTxOzi7d8wlOGfefgAZo27+w== |
      | sessionId    | sessionliviu                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | cookie       | machine=0aa20016;path=/                                                                                                                                                                                                                                                                                                                                                                                                                                  |
    Then I should receive an authorised response
    And the response should be successful
    And the response should reference the "reiss-9mar-2" order code

  Scenario: Failed 3D Secure payment authorisation completion
    When the authorization for the following payment is completed
      | merchantCode | SESSIONECOM             |
      | orderCode    | reiss-9mar-2            |
      | paResponse   | trigger-an-error        |
      | sessionId    | sessionliviu            |
      | cookie       | machine=0aa20016;path=/ |
    Then I should receive an authorised response
    And the response should not be successful
    And the response error message should be "An internal CSE service error has occurred."
    And the response error code should be "5"
