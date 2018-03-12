jQuery(document).ready(function(event) {

 jQuery('#add-customer-form').submit(ajaxSubmit);

 function ajaxSubmit(e) {
  e.preventDefault();
  var customerForm = {};
  jQuery(this).serializeArray().map(function(x){customerForm[x.name] = x.value;}); 
  jQuery.ajax({
    action:  'add_customer',
    type:    "POST",
    url:     AddCustAjax.ajaxurl,
    data:    {"action": "add_customer", customer_data : customerForm},
    success: function(data) {
       jQuery("#add-customer-results").html(data);
       return false;
    }
  });
 }
});