symfony server:start
php bin/console make:controller
php bin/console make:entity
php bin/console make:form
php bin/console  doctrine:fixtures:load
php bin/console  debug:router
php bin/console   doctrine:migrations:migrate 
php bin/console tailwind:build --watch
php bin/console doctrine:schema:update --force 
php bin/console cache:clear

 