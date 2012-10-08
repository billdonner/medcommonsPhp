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
 * Amazon_FPS_Model_Settle
 * 
 * Properties:
 * <ul>
 * 
 * <li>TransactionId: string</li>
 * <li>SettleAmount: Amazon_FPS_Model_Amount</li>
 *
 * </ul>
 */ 
class Amazon_FPS_Model_Settle extends Amazon_FPS_Model
{


    /**
     * Construct new Amazon_FPS_Model_Settle
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>TransactionId: string</li>
     * <li>SettleAmount: Amazon_FPS_Model_Amount</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'TransactionId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SettleAmount' => array('FieldValue' => null, 'FieldType' => 'Amazon_FPS_Model_Amount'),
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
     * @return Amazon_FPS_Model_Settle instance
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
     * Gets the value of the SettleAmount.
     * 
     * @return Amount SettleAmount
     */
    public function getSettleAmount() 
    {
        return $this->_fields['SettleAmount']['FieldValue'];
    }

    /**
     * Sets the value of the SettleAmount.
     * 
     * @param Amount SettleAmount
     * @return void
     */
    public function setSettleAmount($value) 
    {
        $this->_fields['SettleAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SettleAmount  and returns this instance
     * 
     * @param Amount $value SettleAmount
     * @return Amazon_FPS_Model_Settle instance
     */
    public function withSettleAmount($value)
    {
        $this->setSettleAmount($value);
        return $this;
    }


    /**
     * Checks if SettleAmount  is set
     * 
     * @return bool true if SettleAmount property is set
     */
    public function isSetSettleAmount()
    {
        return !is_null($this->_fields['SettleAmount']['FieldValue']);

    }




}
