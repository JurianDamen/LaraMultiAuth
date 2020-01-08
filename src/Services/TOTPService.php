<?php

namespace RSpeekenbrink\LaraMultiAuth\Services;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use InvalidArgumentException;
use Base32\Base32;
use RSpeekenbrink\LaraMultiAuth\LaraMultiAuth;

class TOTPService
{
    /**
     * The length of generated codes, 6 is standard
     *
     * @var integer
     */
    protected static $codeLength = 6;

    /**
     * Generates new secret token.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param integer $secretLength >=16 && <=128
     * @return string
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function generateSecret($secretLength = 16)
    {
        // Valid secret lengths are 80 to 640 bits
        if ($secretLength < 16 || $secretLength > 128) {
            throw new InvalidArgumentException('Invalid secret length provided. Length should be between 16 and 128.');
        }

        return substr(Base32::encode(random_bytes($secretLength)), 0, $secretLength);
    }

    /**
     * Calculate the code, with given secret and point of time.
     *
     * @param string $secret
     * @param integer|null $timeSlice
     * @return string
     */
    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice == null) {
            $timeSlice = floor(time() / 30);
        }

        $secretKey = Base32::decode($secret);

        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);

        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretKey, true);

        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;

        // Grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpack binary value
        $value = unpack('N', $hashpart)[1];

        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, static::getCodeLength());

        return str_pad($value % $modulo, static::getCodeLength(), '0', STR_PAD_LEFT);
    }

    /**
     * Get the code length
     *
     * @return integer
     */
    public static function getCodeLength()
    {
        return static::$codeLength;
    }

    /**
     * Set the code length, should be >=6.
     *
     * @param integer $length Length =>6
     * @return static
     * @throws InvalidArgumentException
     */
    public static function setCodeLength($length)
    {
        if ($length < 6) {
            throw new InvalidArgumentException('Code length should be 6 or greater');
        }

        static::$codeLength = $length;

        return new static;
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec
     * ago to $discrepancy*30sec from now.
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $currentTimeSlice time slice if we want use other that time()
     * @return bool
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != static::getCodeLength()) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = static::getCode($secret, $currentTimeSlice + $i);

            if (static::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A timing safe equals comparison
     * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
     *
     * @param string $safeString The internal (safe) value to be checked
     * @param string $userString The user submitted (unsafe) value
     * @return bool True if the two strings are identical
     */
    private static function timingSafeEquals($safeString, $userString)
    {
        return hash_equals($safeString, $userString);
    }

    /**
     * Get an inline QRCode representing a scannable otpauth URL. This function requires a compatible
     * qr generation package to be installed or an exception will be thrown.
     *
     * @param string $title
     * @param string $holder
     * @param string $secret
     * @param int $size Default 200
     * @param string $encoding Default UTF-8
     * @return string
     * @throws Exception
     */
    public static function getInlineQRCode(
        string $title,
        string $holder,
        string $secret,
        int $size = 200,
        string $encoding = 'utf-8'
    ) {
        if (!LaraMultiAuth::checkIfQRGenerationIsAvailable()) {
            throw new Exception('Inline QR Code Generation Requires The "bacon/bacon-qr-code" package');
        }

        $renderer = new ImageRenderer(
            (new RendererStyle($size))->withSize($size),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);

        $data = $writer->writeString(
            static::getOTPAuthUrl($title, $holder, $secret),
            $encoding
        );

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Generate a otpauth URL to import secret keys directly into totp supported applications.
     *
     * @param string $title
     * @param string $holder
     * @param string $secret
     * @return string
     */
    private static function getOTPAuthUrl(string $title, string $holder, string $secret)
    {
        return 'otpauth://totp/' .
            rawurlencode($title) .
            ':' .
            rawurlencode($holder) .
            '?secret=' .
            $secret .
            '&issuer=' .
            rawurlencode($title) .
            '';
    }
}
