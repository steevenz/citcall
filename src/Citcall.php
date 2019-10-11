<?php
/**
 * This file is part of the Citcall Package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace Steevenz;

// ------------------------------------------------------------------------

use O2System\Curl;
use O2System\Kernel\Http\Message\Uri;
use O2System\Spl\Traits\Collectors\ConfigCollectorTrait;
use O2System\Spl\Traits\Collectors\ErrorCollectorTrait;

/**
 * Class Citcall
 */
class Citcall
{
    use ConfigCollectorTrait;
    use ErrorCollectorTrait;

    /**
     * Citcall::$response
     *
     * Citcall original response.
     *
     * @access  protected
     * @type    mixed
     */
    protected $response;

    // ------------------------------------------------------------------------

    /**
     * Citcall::__construct
     *
     * @param array $config
     *
     * @access  public
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'apiUrl'   => 'https://gateway.citcall.com/',
            'version'  => 'v3',
            'appName'  => null,
            'userId'   => null,
            'senderId' => null,
            'apiKey'   => null,
            'retry'    => 0,
        ];

        $this->setConfig(array_merge($defaultConfig, $config));
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::setApiUrl
     *
     * Set Citcall API Url.
     *
     * @param string $serverIp Citcall API Url.
     *
     * @access  public
     * @return  static
     */
    public function setApiUrl($apiUrl)
    {
        $this->setConfig('apiUrl', $serverIp);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::setUserId
     *
     * Set Citcall User Id.
     *
     * @param string $userId Citcall User id
     *
     * @access  public
     * @return  static
     */
    public function setUserId($userId)
    {
        $this->setConfig('userId', $userId);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::setUserId
     *
     * Set Citcall User Id.
     *
     * @param string $userId Citcall Sender ID
     *
     * @access  public
     * @return  static
     */
    public function setSenderId($senderId)
    {
        $this->setConfig('senderId', $senderId);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::setApiKey
     *
     * Set Citcall API Key.
     *
     * @param string $apiKey Citcall API Key
     *
     * @access  public
     * @return  static
     */
    public function setApiKey($apiKey)
    {
        $this->setConfig('apiKey', $apiKey);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::request
     *
     * Call API request.
     *
     * @param string $path
     * @param array  $params
     * @param string $type
     *
     * @access  protected
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    protected function request($path, $params = [], $type = 'GET')
    {
        // default params
        if (empty($this->config[ 'apiUrl' ])) {
            throw new \InvalidArgumentException('Citcall: API Url is not set!');
        }

        if (empty($this->config[ 'userId' ])) {
            throw new \InvalidArgumentException('Citcall: User ID is not set');
        }

        if (empty($this->config[ 'apiKey' ])) {
            throw new \InvalidArgumentException('Citcall: API Key is not set');
        }

        $uri = (new Uri($this->config[ 'apiUrl' ]))
            ->addPath($this->config[ 'version' ])
            ->addPath($path);

        $request = new Curl\Request();
        $request->setHeaders([
            'Authorization' => base64_encode($this->config[ 'userId' ] . ':' . $this->config[ 'apiKey' ]),
        ]);
        $request->setConnectionTimeout(500);

        if ($this->config[ 'retry' ] > 0 and $this->config[ 'retry' ] <= 20) {
            $params[ 'limit_try' ] = $this->config[ 'retry' ]; // default 5
        }

        if ($this->response = $request->setUri($uri)->post($params, true)) {
            if (false !== ($error = $this->response->getError())) {
                $this->addError($error->code, $error->message);
            } elseif ($body = $this->response->getBody()) {
                return $body;
            }
        }

        return false;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::buildSendPackageData
     *
     * @param array $data
     *
     * @return array|bool
     */
    protected function validateMsisdn($msisdn)
    {
        if (preg_match('/^(62[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            $msisdn = '0' . substr($msisdn, 2);
        } elseif (preg_match('/^(\+62[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            $msisdn = '0' . substr($msisdn, 3);
        }

        if (preg_match('/^(0[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            return trim($msisdn);
        }

        return false;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::send
     *
     * Send SMS
     *
     * @param string $msisdn  MSISDN Number
     * @param string $message Message
     *
     * @access  public
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function send($msisdn, $message)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Citcall: Invalid MSISDN Number');
        }

        $senderId = empty($this->config[ 'senderId' ]) ? $this->config[ 'userId' ] : $this->config[ 'senderId' ];

        return $this->request('sms', [
            'senderid' => $senderId,
            'msisdn'   => $msisdn,
            'text'     => $message,
        ], 'POST');
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::call
     *
     * Async Missed Call
     *
     * @param string $msisdn  MSISDN Number
     * @param string $gateway Gateway Number
     * @param bool   $async   Asyncronous call
     *
     * @access  public
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function call($msisdn, $gateway = 1, $async = false)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Citcall: Invalid MSISDN Number');
        }

        if ( ! is_int($gateway) or $gateway > 5 or $gateway < 0) {
            throw new \InvalidArgumentException('Citcall: Invalid Gateway Number');
        }

        $path = $async === true ? 'asynccall' : 'call';

        return $this->request($path, [
            'msisdn'  => $msisdn,
            'gateway' => (int)$gateway,
        ], 'POST');
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::sendOtp
     *
     * Send SMS
     *
     * @param string $msisdn  MSISDN Number
     * @param string $token   OTP Token Code
     * @param int    $expires Expires time in seconds
     *
     * @access  public
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function sendOtp($msisdn, $token, $expires = 0)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Citcall: Invalid MSISDN Number');
        }

        $params[ 'msisdn' ] = $msisdn;
        $params[ 'senderid' ] = empty($this->config[ 'senderId' ]) ? $this->config[ 'userId' ] : $this->config[ 'senderId' ];

        $token = trim($token);
        if (strlen($token) < 4) {
            throw new \InvalidArgumentException('Citcall: OTP Code minimum length is 5 digit');
        } elseif (strlen($token) > 8) {
            throw new \InvalidArgumentException('Citcall: OTP Code maximum length is 8 digit');
        }

        $params[ 'token' ] = $token;
        $params[ 'text' ] = $params[ 'token' ] . ' is your ' . $this->config[ 'appName' ] . 'OTP code.';

        if ($expires > 0) {
            $params[ 'valid_time' ] = $expires;
        }

        return $this->request('smsotp', $params, 'POST');
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::verifyOtp
     *
     * @param string $trxId
     * @param string $msisdn
     * @param string $token
     */
    public function verifyOtp($trxId, $msisdn, $token)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Citcall: Invalid MSISDN Number');
        }

        $token = trim($token);

        return $this->request('verify', [
            'trxid'  => $trxId,
            'msisdn' => $msisdn,
            'token'  => $token,
        ], 'POST');
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::getResponse
     *
     * Get original response object.
     *
     * @access  public
     * @return  \O2System\Curl\Response|bool Returns FALSE if failed.
     */
    public function getResponse()
    {
        return $this->response;
    }
    // ------------------------------------------------------------------------

    /**
     * Citcall::getCallback
     *
     * Get callback json response from Citcall
     *
     * @return \O2System\Curl\Response\SimpleJSONElement
     */
    public function getCallback()
    {
        $result = new Curl\Response\SimpleJSONElement([
            'rc'  => 0,
            'msg' => 'invalid callback response',
        ]);

        if ($response = file_get_contents('php://input')) {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $result = new Curl\Response\SimpleJSONElement($data);
            }
        }

        return $result;
    }
}