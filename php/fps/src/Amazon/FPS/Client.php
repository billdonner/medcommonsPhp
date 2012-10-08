<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     Amazon_FPS
 *  @copyright   Copyright 2007 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2008-05-01
 */
/******************************************************************************* 
 *    __  _    _  ___ 
 *   (  )( \/\/ )/ __)
 *   /__\ \    / \__ \
 *  (_)(_) \/\/  (___/
 * 
 *  Amazon FPS PHP5 Library
 *  Generated: Thu Apr 24 02:05:45 PDT 2008
 * 
 */

/**
 *  @see Amazon_FPS_Interface
 */
require_once ('Amazon/FPS/Interface.php'); 

/**
 * 
 * This is the lighter version of the original Amazon FPS APIs. These APIs will
 * enable merchants/developers easily integrate with FPS.
 * This is packaged and provided along with the paynow and marketplace widgets.
 * 
 * Amazon_FPS_Client is an implementation of Amazon_FPS
 *
 */
class Amazon_FPS_Client implements Amazon_FPS_Interface
{

    const SERVICE_VERSION = '2008-05-01';

    /** @var string */
    private  $_awsAccessKeyId = null;
    
    /** @var string */
    private  $_awsSecretAccessKey = null;
    
    /** @var array */
    private  $_config = array ('ServiceURL' => 'https://fps.amazonaws.com/paynow', 
                               'UserAgent' => 'Amazon FPS PHP5 Library',
                               'SignatureVersion' => 1,
                               'ProxyHost' => null,
                               'ProxyPort' => -1,
                               'MaxErrorRetry' => 3       
                               );
   
    /**
     * Construct new Client
     * 
     * @param string $awsAccessKeyId AWS Access Key ID
     * @param string $awsSecretAccessKey AWS Secret Access Key
     * @param array $config configuration options. 
     * Valid configuration options are:
     * <ul>
     * <li>ServiceURL</li>
     * <li>UserAgent</li>
     * <li>SignatureVersion</li>
     * <li>TimesRetryOnError</li>
     * <li>ProxyHost</li>
     * <li>ProxyPort</li>
     * <li>MaxErrorRetry</li>
     * </ul>
     */
    public function __construct($awsAccessKeyId, $awsSecretAccessKey, $config = null)
    {
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->_awsAccessKeyId = $awsAccessKeyId;
        $this->_awsSecretAccessKey = $awsSecretAccessKey;
        if (!is_null($config)) $this->_config = array_merge($this->_config, $config);
    }

    // Public API ------------------------------------------------------------//


            
    /**
     * Refund 
     * 
     * This operation enables the merchant issue a complete/partial refund of the
     * original transaction. The refunded money goes into the customer's credit card
     * if the original payment instrument was credit card. Else it goes into the
     * customer's ABT balance.
     * If the original transaction is a marketplace transaction, by default the
     * Marketplace fee is not refunded. This can be overridden by the caller.
     * 
     *   
     * @see http://docs.amazonwebservices.com/FPS/2008-05-01/DG/Refund.html      
     * @param mixed $request array of parameters for Amazon_FPS_Model_Refund request or Amazon_FPS_Model_Refund object itself
     * @see Amazon_FPS_Model_Refund
     * @return Amazon_FPS_Model_RefundResponse Amazon_FPS_Model_RefundResponse
     *
     * @throws Amazon_FPS_Exception
     */
    public function refund($request) 
    {
        if (!$request instanceof Amazon_FPS_Model_Refund) {
            require_once ('Amazon/FPS/Model/Refund.php');
            $request = new Amazon_FPS_Model_Refund($request);
        }
        require_once ('Amazon/FPS/Model/RefundResponse.php');
        return Amazon_FPS_Model_RefundResponse::fromXML($this->_invoke($this->_convertRefund($request)));
    }


            
    /**
     * Settle 
     * 
     * This operation enables merchants to receive funds they had reserved on a
     * customer's Amazon Payments Account Balance/Credit Card. They can settle partial
     * or full amount authorized on the payment instrument using this operation.
     *   
     * @see http://docs.amazonwebservices.com/FPS/2008-05-01/DG/Settle.html      
     * @param mixed $request array of parameters for Amazon_FPS_Model_Settle request or Amazon_FPS_Model_Settle object itself
     * @see Amazon_FPS_Model_Settle
     * @return Amazon_FPS_Model_SettleResponse Amazon_FPS_Model_SettleResponse
     *
     * @throws Amazon_FPS_Exception
     */
    public function settle($request) 
    {
        if (!$request instanceof Amazon_FPS_Model_Settle) {
            require_once ('Amazon/FPS/Model/Settle.php');
            $request = new Amazon_FPS_Model_Settle($request);
        }
        require_once ('Amazon/FPS/Model/SettleResponse.php');
        return Amazon_FPS_Model_SettleResponse::fromXML($this->_invoke($this->_convertSettle($request)));
    }

        // Private API ------------------------------------------------------------//

    /**
     * Invoke request and return response
     */
    private function _invoke(array $parameters)
    {
        $actionName = $parameters["Action"];
        $response = array();
        $responseBody = null;
        $statusCode = 200;

        /* Submit the request and read response body */
        try {
        
            /* Add required request parameters */
            $parameters = $this->_addRequiredParameters($parameters);

            $shouldRetry = true;
            $retries = 0;
            do {
                try {
                        $response = $this->_httpPost($parameters);
                        if ($response['Status'] === 200) {
                            $shouldRetry = false;
                        } else {
                            if ($response['Status'] === 500 || $response['Status'] === 503) {
                                $shouldRetry = true;
                                $this->_pauseOnRetry(++$retries, $response['Status']);
                            } else {    
                                throw $this->_reportAnyErrors($response['ResponseBody'], $response['Status']);
                            }
                       }     
                /* Rethrow on deserializer error */
                } catch (Exception $e) {
                    require_once ('Amazon/FPS/Exception.php');
                    if ($e instanceof Amazon_FPS_Exception) {
                        throw $e;
                    } else {
                        require_once ('Amazon/FPS/Exception.php');
                        throw new Amazon_FPS_Exception(array('Exception' => $e, 'Message' => $e->getMessage()));   
                    }
                }

            } while ($shouldRetry);

        } catch (Amazon_FPS_Exception $se) {
            throw $se;
        } catch (Exception $t) {
            throw new Amazon_FPS_Exception(array('Exception' => $t, 'Message' => $t->getMessage()));
        }

	echo($response['ResponseBody']);
        return $response['ResponseBody'];
    }

    /**
     * Look for additional error strings in the response and return formatted exception
     */
    private function _reportAnyErrors($responseBody, $status, Exception $e =  null)
    {
        $ex = null;
        if (!is_null($responseBody) && strpos($responseBody, '<') === 0) {
            if (preg_match('@<RequestID>(.*)</RequestID>.*<Error><Code>(.*)</Code><Message>(.*)</Message></Error>.*(<Error>)?@mi',
                $responseBody, $errorMatcherOne)) {
                                
                $requestId = $errorMatcherOne[1];
                $code = $errorMatcherOne[2];
                $message = $errorMatcherOne[3];

                require_once ('Amazon/FPS/Exception.php');
                $ex = new Amazon_FPS_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code, 
                                                           'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody));

            } elseif (preg_match('@<Error><Code>(.*)</Code><Message>(.*)</Message></Error>.*(<Error>)?.*<RequestID>(.*)</RequestID>@mi',
                $responseBody, $errorMatcherTwo)) {
                                
                $code = $errorMatcherTwo[1];  
                $message = $errorMatcherTwo[2];  
                $requestId = $errorMatcherTwo[4];   
                require_once ('Amazon/FPS/Exception.php');
                $ex = new Amazon_FPS_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code, 
                                                              'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody));
            } elseif (preg_match('@<Error><Type>(.*)</Type><Code>(.*)</Code><Message>(.*)</Message>.*</Error>.*(<Error>)?.*<RequestId>(.*)</RequestId>@mi',
                $responseBody, $errorMatcherThree)) {
                
                $type = $errorMatcherThree[1];
                $code = $errorMatcherThree[2];  
                $message = $errorMatcherThree[3];  
                $requestId = $errorMatcherThree[5];   
                require_once ('Amazon/FPS/Exception.php');
                $ex = new Amazon_FPS_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code, 
                                                              'ErrorType' => $type, 'RequestId' => $requestId, 'XML' => $responseBody));
            
            } else {
                require_once ('Amazon/FPS/Exception.php');
                $ex = new Amazon_FPS_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status));
            }
        } else {
            require_once ('Amazon/FPS/Exception.php');
            $ex = new Amazon_FPS_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status));
        }
        return $ex;
    }



    /**
     * Perform HTTP post with exponential retries on error 500 and 503
     * 
     */
    private function _httpPost(array $parameters) 
    {
        $query = $this->_getParametersAsString($parameters);
	$url   = parse_url ($this->_config['ServiceURL']);
        $post  = "POST " . $url['path'] .  " HTTP/1.0\r\n";
        $post .= "Host: " . $url['host'] . "\r\n";
        $post .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
        $post .= "Content-Length: " . strlen($query) . "\r\n";
        $post .= "User-Agent: " . $this->_config['UserAgent'] . "\r\n";
        $post .= "\r\n";
        $post .= $query;
	$port = "";
        $scheme = '';
        
        switch ($url['scheme']) {
            case 'https':
		    $scheme = 'ssl://';
		    $port  = 443;
                break;
            default:
		    $scheme = '';
		    $port  = 80;
	}
              
        $response = '';
        if ($socket = @fsockopen($scheme . $url['host'], $port, $errno, $errstr, 10)) {
  
            fwrite($socket, $post);

            while (!feof($socket)) {
                $response .= fgets($socket, 1160);
            }
            fclose($socket);
        
            list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
            $other = preg_split("/\r\n|\n|\r/", $other);
            list($protocol, $code, $text) = explode(' ', trim(array_shift($other)), 3);
        } else {
            throw new Exception ("Unable to establish connection to host " . $url['host'] . " $errstr");
        }
        return array ('Status' => (int)$code, 'ResponseBody' => $responseBody);
    }

    /**
     * Exponential sleep on failed request
     * @param retries current retry
     * @throws Amazon_FPS_Exception if maximum number of retries has been reached
     */
    private function _pauseOnRetry($retries, $status)
    {
        if ($retries <= $this->_config['MaxErrorRetry']) {
            $delay = (int) (pow(4, $retries) * 100000) ;
            usleep($delay);
        } else {
            require_once ('Amazon/FPS/Exception.php');
            throw new Amazon_FPS_Exception (array ('Message' => "Maximum number of retry attempts reached :  $retries", 'StatusCode' => $status));
        }
    }

    /**
     * Add authentication related and version parameters
     */
    private function _addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId'] = $this->_awsAccessKeyId;
        $parameters['Timestamp'] = $this->_getFormattedTimestamp();
        $parameters['Version'] = self::SERVICE_VERSION;      
        $parameters['SignatureVersion'] = $this->_config['SignatureVersion']; 
        $parameters['Signature'] = $this->_signParameters($parameters, $this->_awsSecretAccessKey); 
        
        return $parameters;
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . urlencode($value);
        }
        return implode('&', $queryParameters);
    }  


    /**
      * Computes RFC 2104-compliant HMAC signature for request parameters
      * Implements AWS Signature, as per following spec:
      *
      * If Signature Version is 0, it signs concatenated Action and Timestamp
      *
      * If Signature Version is 1, it performs the following:
      *
      * Sorts all  parameters (including SignatureVersion and excluding Signature,
      * the value of which is being created), ignoring case.
      *
      * Iterate over the sorted list and append the parameter name (in original case)
      * and then its value. It will not URL-encode the parameter values before
      * constructing this string. There are no separators.
      */
    private function _signParameters(array $parameters, $key)
    {
        $signatureVersion = $parameters['SignatureVersion'];
        $data = '';

        if (0 === $signatureVersion) {
            $data .=  $parameters['Action'] .  $parameters['Timestamp'];
        } elseif (1 === $signatureVersion) {
            uksort($parameters, 'strcasecmp');
            unset ($parameters['Signature']);
                
            foreach ($parameters as $parameterName => $parameterValue) {
                $data .= $parameterName . $parameterValue;
            }
        } else {
            throw new Exception("Invalid Signature Version specified");
        }
        return $this->_sign($data, $key);
    }


    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $key)
    {
        return base64_encode (
            pack("H*", sha1((str_pad($key, 64, chr(0x00))
            ^(str_repeat(chr(0x5c), 64))) .
            pack("H*", sha1((str_pad($key, 64, chr(0x00))
            ^(str_repeat(chr(0x36), 64))) . $data))))
        );
    }


    /**
     * Formats date as ISO 8601 timestamp
     */
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }

        
    /**
     * Convert Refund to name value pairs
     */
    private function _convertRefund($request) {
        
        $parameters = array();
        $parameters['Action'] = 'Refund';
        if ($request->isSetTransactionId()) {
            $parameters['TransactionId'] =  $request->getTransactionId();
        }
        if ($request->isSetRefundTransactionReference()) {
            $parameters['RefundTransactionReference'] =  $request->getRefundTransactionReference();
        }
        if ($request->isSetTransactionDescription()) {
            $parameters['TransactionDescription'] =  $request->getTransactionDescription();
        }
        if ($request->isSetRefundAmount()) {
            $refundAmount = $request->getRefundAmount();
            if ($refundAmount->isSetCurrencyCode()) {
                $parameters['RefundAmount' . '.' . 'CurrencyCode'] =  $refundAmount->getCurrencyCode();
            }
            if ($refundAmount->isSetValue()) {
                $parameters['RefundAmount' . '.' . 'Value'] =  $refundAmount->getValue();
            }
        }
        if ($request->isSetMarketplaceRefundPolicy()) {
            $parameters['MarketplaceRefundPolicy'] =  $request->getMarketplaceRefundPolicy();
        }

        return $parameters;
    }
        
                        
    /**
     * Convert Settle to name value pairs
     */
    private function _convertSettle($request) {
        
        $parameters = array();
        $parameters['Action'] = 'Settle';
        if ($request->isSetTransactionId()) {
            $parameters['TransactionId'] =  $request->getTransactionId();
        }
        if ($request->isSetSettleAmount()) {
            $settleAmount = $request->getSettleAmount();
            if ($settleAmount->isSetCurrencyCode()) {
                $parameters['SettleAmount' . '.' . 'CurrencyCode'] =  $settleAmount->getCurrencyCode();
            }
            if ($settleAmount->isSetValue()) {
                $parameters['SettleAmount' . '.' . 'Value'] =  $settleAmount->getValue();
            }
        }

        return $parameters;
    }

}
