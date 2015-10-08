# Test scenarios for CashWay PrestaShop payment module

 * nécessite Ruby, Bundler; run `bundle install`
 * renommer `.env.sample` en `.env` et mettre les bonnes valeurs
 * run `bundle exec rspec spec/`

##

 * install_module :
   * **supprime** le module s'il est déjà installé
   * installe le module à partir d'une archive fournie
     (TODO, le faire à partir de addons.p.c)
   * configure le module

 * client_use :
   * utilise un compte existant
   * passe commande d'un item pris au hasard
   * choisit l'option de paiement CashWay
   * TODO


## TODO

 * finir process d'achat
 * après achat, vérifier l'état de la commande vue de l'API
 * après achat, vérifier l'état de la commande vue de l'admin
