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
 * Amazon_FPS_Model_RefundResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>TransactionStatus: string</li>
 * <li>TransactionId: string</li>
 * <li>PendingReason: string</li>
 *
 * </ul>
 */ 
class Amazon_FPS_Model_RefundResult extends Amazon_FPS_Model
{


    /**
     * Construct new Amazon_FPS_Model_RefundResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>TransactionStatus: string</li>
     * <li>TransactionId: string</li>
     * <li>PendingReason: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'TransactionStatus' => array('FieldValue' => null, 'FieldType' => 'string'),
        'TransactionId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'PendingReason' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the TransactionStatus property.
     * 
     * @return string TransactionStatus
     */
    public function getTransactionStatus() 
    {
        return $this->_fields['TransactionStatus']['FieldValue'];
    }

    /**
     * Sets the value of the TransactionStatus property.
     * 
     * @param string TransactionStatus
     * @return this instance
     */
    public function setTransactionStatus($value) 
    {
        $this->_fields['TransactionStatus']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TransactionStatus and returns this instance
     * 
     * @param string $value TransactionStatus
     * @return Amazon_FPS_Model_RefundResult instance
     */
    public function withTransactionStatus($value)
    {
        $this->setTransactionStatus($value);
        return $this;
    }


    /**
     * Checks if TransactionStatus is set
     * 
     * @return bool true if TransactionStatus  is set
     */
    public function isSetTransactionStatus()
    {
        return !is_null($this->_fields['TransactionStatus']['FieldValue']);
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
     * @return Amazon_FPS_Model_RefundResult instance
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
     * Gets the value of the PendingReason property.
     * 
     * @return string PendingReason
     */
    public function getPendingReason() 
    {
        return $this->_fields['PendingReason']['FieldValue'];
    }

    /**
     * Sets the value of the PendingReason property.
     * 
     * @param string PendingReason
     * @return this instance
     */
    public function setPendingReason($value) 
    {
        $this->_fields['PendingReason']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the PendingReason and returns this instance
     * 
     * @param string $value PendingReason
     * @return Amazon_FPS_Model_RefundResult instance
     */
    public function withPendingReason($value)
    {
        $this->setPendingReason($value);
        return $this;
    }


    /**
     * Checks if PendingReason is set
     * 
     * @return bool true if PendingReason  is set
     */
    public function isSetPendingReason()
    {
        return !is_null($this->_fields['PendingReason']['FieldValue']);
    }




}