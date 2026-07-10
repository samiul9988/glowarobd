# Objective
Implement a feature "Customer Group Wise Coupon Management" with existing coupon management feature using required skills and laravel-boost mcp

# Instructions
* Analyze the existing coupon management system in backend
* Add new coupon type "For Customer Group" in coupon create/edit form
* If select the new option show a select field with existing fields to select customer group (loaded from customer_groups table)
* Create a migration to add new column "group_id" in coupons table, then store the selected group_id into this column
* Analyze the existing coupon validation logic (web/api), and add new checking that if coupon is for customer group then only those users can use the coupon which group match the coupon group
* Analyze the assigned coupons routes, and add group coupons with existing assigned coupons logic, only if the users group matched with any coupon group

# Dos
* Use laravel-specialist skill and laravel boost mcp to implement the feature
* Use laravel best practices
* Maintain a clean, user-friendly and visually appealing interface
* Ensure consistency across the admin panel
* Run migrations like `php artisan migrate --path="file_name"` only for newly created migration

# Donts
* Only provide the required code and implementation files
* Do not run migrations like `php artisan migrate`
