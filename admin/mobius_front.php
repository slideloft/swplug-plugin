<?php
global $wpdb;
$options = get_option('woocommerce_mobius_gateway_settings');
$api_key = $options['app_apikey'];
$app_uid = $options['app_uid'];

//Set Your Nonce
$ajax_nonce = wp_create_nonce( "get_balance_secure" );
?>
<div class="container">
  <div class="row">
   <div class="col-md-12 played-games">
    <h4 class="section-title"><?php echo esc_html( 'Pay with Mobius' ); ?></h4>
    <form class="form-group">
      <input class="form-control" type="text" name="mobius_email" id="mobius_email" placeholder="Enter mobius email" style="width: 100%;" />
      <table>
      <tr>
        <td style="vertical-align: middle;"><img src='<?php echo plugins_url( 'images/MOBI.png', dirname(__FILE__) )   ?>' id="currency_logo" /></td>
        <td><div id="mobius_content"><?php echo esc_html( 'Price to be pay ' ); ?><br><strong><?php echo esc_html( 'MOBI : ' ); ?><span id="mobius_dollar_price"></span></strong></div></td>
      </tr>
      </table>
      <input class="form-control" type="hidden" name="api_key" id="api_key" value="<?php echo $api_key ?>" />
      <input class="form-control" type="hidden" name="app_uid" id="app_uid" value="<?php echo $app_uid ?>" />
      
      <input type="hidden" name="mobius_charge" id="mobius_charge">
    </form>
  </div>
</div>
</div>
<script type="text/javascript">
  
    jQuery.ajaxQ = (function(){
    var id = 0, Q = {};

    jQuery(document).ajaxSend(function(e, jqx){
      jqx._id = ++id;
      Q[jqx._id] = jqx;
    });
    jQuery(document).ajaxComplete(function(e, jqx){
      delete Q[jqx._id];
    });

    return {
      abortAll: function(){
        var r = [];
        jQuery.each(Q, function(i, jqx){
          r.push(jqx._id);
          jqx.abort();
        });
        return r;
      }
    };

  })();

  jQuery(document).ready(function()
  {

    
    var checkout_btn = "[name='woocommerce_checkout_place_order']";
    //var checkout_btn = "input[name='woocommerce_checkout_place_order']";
    jQuery(checkout_btn).on('click',function()
    {
      
     var selected_payment_method = jQuery('input[name=payment_method]:checked').val();
     

     if(selected_payment_method == "mobius_gateway") {

         var previous_text = jQuery(this).val();
         jQuery(checkout_btn).val("Please wait ...");
         var mobius_email = jQuery("#mobius_email").val();
         var api_key = jQuery("#api_key").val();
         var app_uid = jQuery("#app_uid").val();
         var woocommerce_total_amount = jQuery('.order-total .woocommerce-Price-amount').html();
         var woocomerce_products_total =parseFloat(woocommerce_total_amount.replace(/[^0-9\.]/g, ''), 10);
         var mobius_charge = jQuery('#mobius_charge').val();
         var stellar_products_total = woocomerce_products_total / mobius_charge;
         stellar_products_total = stellar_products_total.toFixed(2);
         var app_credit_charge_per_each_use = stellar_products_total;

         if(mobius_email=='') {
          alert("Please enter mobius email.");
          jQuery(checkout_btn).val(previous_text);
          return false;
        }

        var app_credit_charge_per_game =  app_credit_charge_per_each_use;

        jQuery.ajax({
          url : myplugin.ajax_url,
          data : {action:"swplug_get_balance",email:mobius_email,api_key:api_key,app_uid:app_uid,security: '<?php echo $ajax_nonce; ?>'},
          success : function(response){
           
           response = JSON.parse(response);
           console.log(response);
           var num_credits = response.num_credits;
           if(num_credits < app_credit_charge_per_game)
           {
                jQuery.ajax({
                url : myplugin.ajax_url,
                data : {action:"swplug_delete_last_mobius_order",security: '<?php echo $ajax_nonce; ?>'},
                success : function(response){
                  alert("You have not enough credits to mobius account. Please add credits to our mobius app to play game.Please visit https://mobius.network/store/.Note : Credits require to pay is "+app_credit_charge_per_game + ' MOBI');
                  jQuery.ajaxQ.abortAll();
                  window.location.href='<?php //echo site_url('shop'); ?>';
                }
                });
          }
          else
          {
            jQuery.ajax({
             url : myplugin.ajax_url,
             data : {action:"swplug_use_credits",email:mobius_email,api_key:api_key,app_uid:app_uid,num_credits:app_credit_charge_per_game,security: '<?php echo $ajax_nonce; ?>'},
             success : function(response){
              console.log(response);
              response = JSON.parse(response);
              var remaining_num_credits = response.num_credits;
              if(response.success == true)
              {
               alert('Mobius credits have been successfully charged.');
             }
           },
           error : function(error) {
            console.log(error);
          }
        });
          }
          jQuery(checkout_btn).val(previous_text);
        },
        error : function(error) {
         console.log(error);
         jQuery(checkout_btn).val(previous_text);
       }
     });

        return true;
  }
  });


    jQuery.ajax({
             type: "GET",
             url: "https://api.stellarterm.com/v1/ticker.json",
             success: function(result)
             {
              jQuery.each(result.assets, function(index,value){
              
                if(value.code == 'MOBI'){
                   var mobi_price_USD = value.price_USD;
                   var woocommerce_total_amount = jQuery('.order-total .woocommerce-Price-amount').html();
                   var woocomerce_products_total =parseFloat(woocommerce_total_amount.replace(/[^0-9\.]/g, ''), 10);
                   var mobius_charge = mobi_price_USD;
                   var stellar_products_total = woocomerce_products_total / mobius_charge;
                   stellar_products_total = stellar_products_total.toFixed(2);
                  jQuery('#mobius_charge').val(mobi_price_USD);  
                  jQuery('#mobius_dollar_price').html(stellar_products_total);
                
                }
             });

             }
             }); 


  }); 

</script>
