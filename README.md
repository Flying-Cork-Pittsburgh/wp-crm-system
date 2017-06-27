# CRM Plugin
A customer resource management plugin for WordPress. 
## Installation
Clone or unzip to your plugins folder and activate through wp-admin 
### Use
Few things:
- Customers can be created via the wp-admin backend or via form submission through shortcode
- To use shortcode submission to embed in posts/pages, use [customer_form]
- [customer_form] takes parameters to make it more customizable:
	- Labels for input fields can be adjust via the following attributes:
		- name, phone, email, budget, message
		- EX: [customer_form name="Enter name:"]
	- Maxlength's for input fields can be added for each field via the following attributes:
		- name-max-length, phone-max-length, email-max-length, budget-max-length, message-max-length
		- EX: [customer_form name-max-length="30"]
	- For the message field, the rows and columns can be set via the following attributes:
		- message-rows, message-cols
		- EX: [customer_form message-rows="5" message-cols="100"]