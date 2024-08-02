# Indexiz_GuestToCustomer module

This module is helpful when you want your store to convert guest customers to registered customer automatically.

### Admin Configuration:
* You need to set CRON Job time to in admin configuration at which time CRON Job will run and convert guests into 
registered customers.
* You can set batch size at once how many guests will be converted to customers when a CRON Job runs.
* You can Enable/Disable Welcome Email sent to customer while creating customers from guests.
* You can set which customer group should be assigned to newly created customers from guests.
* You can Enable auto assign orders based on email to those customers which are being converted from guests, 
if those guests have placed any order.
