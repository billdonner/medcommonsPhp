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
 * 
 * This is the lighter version of the original Amazon FPS APIs. These APIs will
 * enable merchants/developers easily integrate with FPS.
 * This is packaged and provided along with the paynow and marketplace widgets.
 * 
 */

interface  Amazon_FPS_Interface 
{
    

            
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
    public function refund($request);


            
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
    public function settle($request);

}