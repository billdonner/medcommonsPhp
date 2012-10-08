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
 *  @see Amazon_FPS_Model
 */
require_once ('Amazon/FPS/Model.php');  

    

/**
 * Amazon_FPS_Model_Refund
 * 
 * Properties:
 * <ul>
 * 
 * <li>TransactionId: string</li>
 * <li>RefundTransactionReference: string</li>
 * <li>TransactionDescription: string</li>
 * <li>RefundAmount: Amazon_FPS_Model_Amount</li>
 * <li>MarketplaceRefundPolicy: string</li>
 *
 * </ul>
 */ 
class Amazon_FPS_Model_Refund extends Amazon_FPS_Model
{


    /**
     * Construct new Amazon_FPS_Model_Refund
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>TransactionId: string</li>
     * <li>RefundTransactionReference: string</li>
     * <li>TransactionDescription: string</li>
     * <li>RefundAmount: Amazon_FPS_Model_Amount</li>
     * <li>MarketplaceRefundPolicy: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'TransactionId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'RefundTransactionReference' => array('FieldValue' => null, 'FieldType' => 'string'),
        'TransactionDescription' => array('FieldValue' => null, 'FieldType' => 'string'),
        'RefundAmount' => array('FieldValue' => null, 'FieldType' => 'Amazon_FPS_Model_Amount'),
        'MarketplaceRefundPolicy' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the TransactionId property.
     * 
     * @return string TransactionId
     */
    public function getTransactionId() 
    {
        return $this->_fields['TransactionId']['FieldValue'];
    }

    /**
     * Sets the value of the TransactionId property.
     * 
     * @param string TransactionId
     * @return this instance
     */
    public function setTransactionId($value) 
    {
        $this->_fields['TransactionId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TransactionId and returns this instance
     * 
     * @param string $value TransactionId
     * @return Amazon_FPS_Model_Refund instance
     */
    public function withTransactionId($value)
    {
        $this->setTransactionId($value);
        return $this;
    }


    /**
     * Checks if TransactionId is set
     * 
     * @return bool true if TransactionId  is set
     */
    public function isSetTransactionId()
    {
        return !is_null($this->_fields['TransactionId']['FieldValue']);
    }

    /**
     * Gets the value of the RefundTransactionReference property.
     * 
     * @return string RefundTransactionReference
     */
    public function getRefundTransactionReference() 
    {
        return $this->_fields['RefundTransactionReference']['FieldValue'];
    }

    /**
     * Sets the value of the RefundTransactionReference property.
     * 
     * @param string RefundTransactionReference
     * @return this instance
     */
    public function setRefundTransactionReference($value) 
    {
        $this->_fields['RefundTransactionReference']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the RefundTransactionReference and returns this instance
     * 
     * @param string $value RefundTransactionReference
     * @return Amazon_FPS_Model_Refund instance
     */
    public function withRefundTransactionReference($value)
    {
        $this->setRefundTransactionReference($value);
        return $this;
    }


    /**
     * Checks if RefundTransactionReference is set
     * 
     * @return bool true if RefundTransactionReference  is set
     */
    public function isSetRefundTransactionReference()
    {
        return !is_null($this->_fields['RefundTransactionReference']['FieldValue']);
    }

    /**
     * Gets the value of the TransactionDescription property.
     * 
     * @return string TransactionDescription
     */
    public function getTransactionDescription() 
    {
        return $this->_fields['TransactionDescription']['FieldValue'];
    }

    /**
     * Sets the value of the TransactionDescription property.
     * 
     * @param string TransactionDescription
     * @return this instance
     */
    public function setTransactionDescription($value) 
    {
        $this->_fields['TransactionDescription']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TransactionDescription and returns this instance
     * 
     * @param string $value TransactionDescription
     * @return Amazon_FPS_Model_Refund instance
     */
    public function withTransactionDescription($value)
    {
        $this->setTransactionDescription($value);
        return $this;
    }


    /**
     * Checks if TransactionDescription is set
     * 
     * @return bool true if TransactionDescription  is set
     */
    public function isSetTransactionDescription()
    {
        return !is_null($this->_fields['TransactionDescription']['FieldValue']);
    }

    /**
     * Gets the value of the RefundAmount.
     * 
     * @return Amount RefundAmount
     */
    public function getRefundAmount() 
    {
        return $this->_fields['RefundAmount']['FieldValue'];
    }

    /**
     * Sets the value of the RefundAmount.
     * 
     * @param Amount RefundAmount
     * @return void
     */
    public function setRefundAmount($value) 
    {
        $this->_fields['RefundAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundAmount  and returns this instance
     * 
     * @param Amount $value RefundAmount
     * @return Amazon_FPS_Model_Refund instance
     */
    public function withRefundAmount($value)
    {
        $this->setRefundAmount($value);
        return $this;
    }


    /**
     * Checks if RefundAmount  is set
     * 
     * @return bool true if RefundAmount property is set
     */
    public function isSetRefundAmount()
    {
        return !is_null($this->_fields['RefundAmount']['FieldValue']);

    }

    /**
     * Gets the value of the MarketplaceRefundPolicy property.
     * 
     * @return string MarketplaceRefundPolicy
     */
    public function getMarketplaceRefundPolicy() 
    {
        return $this->_fields['MarketplaceRefundPolicy']['FieldValue'];
    }

    /**
     * Sets the value of the MarketplaceRefundPolicy property.
     * 
     * @param string MarketplaceRefundPolicy
     * @return this instance
     */
    public function setMarketplaceRefundPolicy($value) 
    {
        $this->_fields['MarketplaceRefundPolicy']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the MarketplaceRefundPolicy and returns this instance
     * 
     * @param string $value MarketplaceRefundPolicy
     * @return Amazon_FPS_Model_Refund instance
     */
    public function withMarketplaceRefundPolicy($value)
    {
        $this->setMarketplaceRefundPolicy($value);
        return $this;
    }


    /**
     * Checks if MarketplaceRefundPolicy is set
     * 
     * @return bool true if MarketplaceRefundPolicy  is set
     */
    public function isSetMarketplaceRefundPolicy()
    {
        return !is_null($this->_fields['MarketplaceRefundPolicy']['FieldValue']);
    }




}