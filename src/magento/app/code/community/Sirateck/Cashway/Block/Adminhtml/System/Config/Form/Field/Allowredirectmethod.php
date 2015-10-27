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
 * Frontend type for select specific redirect method in admin configuration
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Block_Adminhtml_System_Config_Form_Field_Allowredirectmethod extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	
	/**
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$javaScript = "
            <script type=\"text/javascript\">
                Event.observe('{$element->getId()}', 'change', function(){
                    redirect_method=$('{$element->getId()}').value;
                    $('{$this->_getRedirectMethodElementId($element)}').disabled = (!redirect_method || redirect_method!=1);
                });
            </script>";
		
		$element->setData('after_element_html',$javaScript.$element->getAfterElementHtml());
		
		$this->toggleDisabled($element);
	
		return parent::_getElementHtml($element);
	}

    public function toggleDisabled($element)
    {
        if(!$element->getValue() || $element->getValue()!=1) {

            $element->getForm()->getElement($this->_getRedirectMethodElementId($element))->setDisabled('disabled');
        }
        return parent::getHtml();
    }

    protected function _getRedirectMethodElementId($element)
    {
        return substr($element->getId(), 0, strrpos($element->getId(), 'redirectspecific')) . 'redirectmethod';
    }

}
