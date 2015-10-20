<?php
/**
 *  CASHWAY
 *
 *  Copyright 2015 CashWay
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 * @category   Sirateck
 * @package    Sirateck_Cashway
 * @copyright  Copyright 2015 CashWay
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
 */


/**
 * Order Response Object
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */

/**
 * @method string getBarcode()
 * @method string getShopOrderId()
 * @method string getStatus()
 * @method string getCreatedAt()
 * @method string getExpiresAt()
 * @method float getOrderTotal()
 * @method float getCustomerPayment()
 * @method float getCustomerFee()
 * @method float getShopFee()
 */
class Sirateck_Cashway_Model_Api_Response_Order extends Sirateck_Cashway_Model_Api_Response_Abstract
{
	
}