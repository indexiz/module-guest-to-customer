# Indexiz_GuestToCustomer Module

The `Indexiz_GuestToCustomer` module is designed to automatically convert guest customers into registered customers in your Magento store. This helps enhance customer engagement and streamline the registration process.

## Key Features

- **Automatic Guest to Customer Conversion:** The module runs on a scheduled CRON job that automatically converts guest customers into registered customers based on your settings.

- **Customizable CRON Job Schedule:** You can configure the CRON job to run at a specific time, determining when the guest-to-customer conversion will take place.

- **Batch Conversion:** Set a batch size to define how many guest customers should be converted into registered customers during each CRON job execution.

- **Welcome Email Configuration:** You can enable or disable the sending of a welcome email to new customers when they are created from guest accounts.

- **Customer Group Assignment:** Choose which customer group should be assigned to newly registered customers that are created from guest accounts.

- **Order Assignment:** Automatically assign any past orders made by the guest customers to their newly created customer account based on their email address.

## Admin Configuration

To configure the module, follow these steps in the Magento Admin Panel:

1. **CRON Job Settings:**
    - Set the CRON job schedule to define when it will run and convert guest customers into registered customers.

2. **Batch Size:**
    - Specify how many guest customers should be converted at once when the CRON job runs.

3. **Welcome Email:**
    - Enable or disable the welcome email that will be sent to customers when they are converted from guest to registered status.

4. **Customer Group Assignment:**
    - Select the customer group to which new registered customers will be assigned upon conversion.

5. **Order Auto-Assignment:**
    - Enable the feature to automatically assign orders placed by guest customers to their newly created registered customer account based on their email address.

## Installation

1. Upload the module files to the `/app/code` directory in your Magento installation.

2. Run the following commands to enable the module and update the Magento database:

   ```bash
   php bin/magento module:enable Indexiz_GuestToCustomer
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy
