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
 * Account Response Object
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */

/**
 * @method string getName()
 * @method string getEmail()
 * @method string getApiUser()
 * @method string getPhone()
 * @method string getAddress()
 * @method string getCompany()
 * @method int getZipcode()
 * @method string getCity()
 * @method string getCountry()
 * @method string getSiren()
 * @method string getVat()
 * @method string getIban()
 * @method string getBic()
 * @method date getCreatedAt()
 * @method date getUpdatedAt()
 * @method float getBalance()
 * @method string getStatus() // Account status: new,pending_verification,active,blocked,closed
 * @method string getReason()
 * @method array getTodo()
 * @method array getOrders()
 */
class Sirateck_Cashway_Model_Api_Response_Account extends Sirateck_Cashway_Model_Api_Response_Abstract
{
	
}