<?php

namespace RSpeekenbrink\LaraMultiAuth\Tests\Services;

use InvalidArgumentException;
use RSpeekenbrink\LaraMultiAuth\Services\TOTPService;
use PHPUnit\Framework\TestCase;

class TOTPServiceTest extends TestCase
{
    public function test_service_can_be_instantiated()
    {
        $reflectionClass = new \ReflectionClass(TOTPService::class);

        $this->assertTrue($reflectionClass->isInstantiable());
        $this->assertEquals('RSpeekenbrink\LaraMultiAuth\Services', $reflectionClass->getNamespaceName());
    }

    public function test_generate_secret_defaults_to_sixteen_characters()
    {
        $this->assertEquals(16, strlen(TOTPService::generateSecret()));
    }

    public function test_generate_secret_length_can_be_specified()
    {
        for ($secretLength = 16; $secretLength < 128; ++$secretLength) {
            $secret = TOTPService::generateSecret($secretLength);
            $this->assertEquals($secretLength, strlen($secret));
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_exception_thrown_when_secret_length_is_under_sixteen()
    {
        TOTPService::generateSecret(15);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_exception_thrown_when_secret_length_is_above_hunderdtwentyeight()
    {
        TOTPService::generateSecret(129);
    }

    public function test_get_code_returns_correct_values()
    {
        $codes = array(
            array(
                'secret' => 'LIQFKJGYW44QSUWKOJDYVHZYSU',
                'timeslice' => '0',
                'code' => '086090'
            ),
            array(
                'secret' => 'LIQFKJGYW44QSUWKOJDYVHZYSU',
                'timeslice' => '1385909245',
                'code' => '314258'
            ),
            array(
                'secret' => 'LIQFKJGYW44QSUWKOJDYVHZYSU',
                'timeslice' => '1378934578',
                'code' => '797496'
            ),
        );

        foreach ($codes as $code) {
            $generatedCode = TOTPService::getCode($code['secret'], $code['timeslice']);
            $this->assertEquals($code['code'], $generatedCode);
        }
    }

    public function test_verify_code_returns_false_on_invalid_code()
    {
        $secret = 'LIQFKJGYW44QSUWKOJDYVHZYSU';
        $code = '123456';
        $result = TOTPService::verifyCode($secret, $code);
        $this->assertFalse($result);
    }

    public function test_verify_code_returns_true_on_correct_code()
    {
        $secret = 'LIQFKJGYW44QSUWKOJDYVHZYSU';
        $code = TOTPService::getCode($secret);
        $result = TOTPService::verifyCode($secret, $code);
        $this->assertTrue($result);
    }

    public function test_verify_code_returns_false_when_code_does_not_match_code_length()
    {
        $secret = 'LIQFKJGYW44QSUWKOJDYVHZYSU';
        $code = '1234567';
        $result = TOTPService::verifyCode($secret, $code);
        $this->assertFalse($result);
    }

    public function test_verify_code_verifies_correctly_with_leading_zero()
    {
        $secret = 'LIQFKJGYW44QSUWKOJDYVHZYSU';
        $code = TOTPService::getCode($secret);
        $result = TOTPService::verifyCode($secret, $code);
        $this->assertTrue($result);

        $code = '0' . $code;
        $result = TOTPService::verifyCode($secret, $code);
        $this->assertFalse($result);
    }

    public function test_code_length_can_be_set()
    {
        $codeLength = 10;
        TOTPService::setCodeLength($codeLength);
        $this->assertEquals(TOTPService::getCodeLength(), $codeLength);

        TOTPService::setCodeLength(6);
        $this->assertEquals(TOTPService::getCodeLength(), 6);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_exception_thrown_when_trying_to_set_code_length_under_six()
    {
        TOTPService::setCodeLength(5);
    }
}
