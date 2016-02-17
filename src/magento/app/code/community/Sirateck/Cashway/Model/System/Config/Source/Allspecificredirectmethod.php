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
 * Source for allow all or specific redirect method
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Model_System_Config_Source_Allspecificredirectmethod
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('cashway')->__('All Enabled Methods')),
            array('value'=>1, 'label'=>Mage::helper('cashway')->__('Specific Methods')),
        );
    }
}
