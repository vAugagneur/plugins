# Change Log for CashWay Magento Module

## 2015-10-27 - 0.2.0

 * Add redirection system to cashway when last payment method failed
   - Configuration for activation added
   - Validation and Redirection are in [Observer](app/code/community/Sirateck/Cashway/Model/Observer.php)
   - When a payment transaction failed, the user is redirect to order rewiew page for pay directly with cashway
   - A payment failed event is send to Cashway
   
 ### DEV Notes
   
   * Can be improved design of review page

## 2015-10-23 - 0.1.0, dev version

 * New CashWay module with basic functionality:
   - Basic configuration.
   - Evaluate Transaction in frontend and hide cashway is service is not available
   - Send transaction and transaction confirm in one statement (just for now, I hope)
   - Receive paid notification in [IpnController](app/code/community/Sirateck/Cashway/controllers/IpnController.php)
   - When is paid, the order is invoiced