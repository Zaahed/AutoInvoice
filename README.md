# AutoInvoice

### Installation
To install this package with composer run the following commands inside your Magento 2 directory:\
`composer require zaahed/module-auto-invoice`\
`bin/magento module:enable Zaahed_AutoInvoice`

Check your environment mode `bin/magento deploy:mode:show` and follow one of the two instructions below.


**For production environments:**\
`bin/magento maintenance:mode enable`\
`bin/magento setup:di:compile`\
`bin/magento cache:clean config`\
`bin/magento maintenance:mode disable`

**For developer environments:**\
Remove the contents of the `generated/` folder and run:
`bin/magento cache:clean config`

### How to use
1. Browse to your Magento 2 admin.
2. Go to Stores > Configuration > Sales > Sales.
3. Expand Auto Invoice and pick 'Yes' for the Enable field.
4. Select your desired payment method(s) and click Save in the right upper corner.

Invoices should be automatically created after placing an order with the selected payment methods.

### Why certain choices were made

A plugin is created for OrderManagementInterface::place because this class has the `@api` tag and is used by the frontend, backend and Web API. The `@api` ensures compatibility with future Magento 2 versions until the next major release.





