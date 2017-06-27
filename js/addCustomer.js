jQuery(document).ready(function(event) {

 jQuery('#add-customer-form').submit(ajaxSubmit);

 function ajaxSubmit(e) {
  e.preventDefault();
  //var ajaxurl = <wp_json_encode( admin_url( 'admin-ajax.php' ) ); //must echo it ?>;
  //var customerForm = jQuery(this).serializeObject();
  var customerForm = {};
  jQuery(this).serializeArray().map(function(x){customerForm[x.name] = x.value;}); 
  console.log(customerForm);
  console.log(AddCustAjax.ajaxurl);
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

/*function cat_ajax_get(catID) {
  var offset = 0;
  var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); //must echo it ?>;

  jQuery("a.ajax").parent().removeClass("current-tab");
  if (catID == null)
      jQuery("#category-menu li:first-child").addClass("current-tab");

  else
      jQuery("." + catID).parent().addClass("current-tab"); //adds class current to the category menu item being displayed so you can style it with css
  if ( jQuery(".current-tab").hasClass('main-cat') )
      offset = 3;

  jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {"action": "cat_posts", cat: catID , offset: offset},
      success: function(response) {
          if (response.length > 1)
              jQuery("#post-container").html(response);
          else {
              jQuery("#post-container").html("<h2>No content here yet - Try another tab!</h2>");
              jQuery(".load-more-posts-button").hide();
          }
          jQuery(".load-more-posts-button").html("Load more..");
          return false;
      }
  });
}

function cat_ajax_load_more() {
  var catID = jQuery(".current-tab").children().attr("class").split(' ')[0];
  var offset = jQuery("article").length;
  var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); //must echo it ?>;
  jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {"action": "more_posts_by_cat", cat: catID, offset: offset },
      success: function(response) {
          jQuery("#post-container").append(response);
          if(response.length < 1)  //no more posts to load
              jQuery(".load-more-posts-button").html("Nothing more to load..");
          return false;
      }

  });
}*/