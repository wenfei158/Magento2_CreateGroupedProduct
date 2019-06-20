# Magento2_CreateGroupedProduct
Create grouped product for performance test of magento2

step 1 add repository to composer.json

composer config repositories.magneto2_creategroupedproduct vcs https://github.com/wenfei158/Magento2_CreateGroupedProduct.git


step 2 get the source code by composer

composer require magento2/creategroupedproduct:dev-master


step 3 enable the new module

bin/magento module:enable Magento2_CreateGroupedProduct


step 4 add the new module to magento

bin/magento setup:upgrade


step 5 generate 1 million simple products

bin/magento setup:perf:generate-fixtures ./app/code/Magento2/CreateGroupedProduct/profiles/one_million_small_attribute.xml


step 6 generate 100 thousands grouped products

bin/magento magento2:create_group 2047 one_million


step 7 rebuild indexer

bin/magento index:reindex


step 8 flush cache

bin/magento cache:flush

