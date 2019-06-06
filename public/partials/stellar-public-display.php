<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://medium.com/swplug
 * @since      1.0.0
 *
 * @package    SWPLUG-Plus
 * @subpackage SWPLUG-Plus/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
    
    function swplug_getEnabledCurrencies($settings) {
      $enabledCurrencies = array();
      foreach ($settings as $key => $value) {
        if($key!='enabled' && $value == 'yes')
          $enabledCurrencies[] = $key;
      }
      return $enabledCurrencies;
    }

    function swplug_getCurrencyCode($currency) {
      $pos = strpos($currency, "(");
      return trim(substr($currency, 0,$pos));
    }

    $str_1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str_2 = 'aBcEeFgHiJkLmNoPqRstUvWxYz0123456789';
    $memo = substr(str_shuffle($str_1),0, 1) . substr(str_shuffle($str_2),0, 11);
    

    /*
    * choose random wallet address
    */
    $stellar_setting = get_option('woocommerce_stellar_gateway_settings');
    
    $stellar_wallet_address = $stellar_setting['wallet_address'];

    $stellar_owner_email = $stellar_setting['owner_email'];

    $enabledCurrencies = swplug_getEnabledCurrencies($stellar_setting);

    $default_currency = $stellar_setting['default_currency'];

    $wallet_address_array = explode(',', $stellar_wallet_address);
    $k = array_rand($wallet_address_array);
    $random_wallet_address = $wallet_address_array[$k];
    
    /*
    * dynamic memo timing
    */
    $memo_timing = $stellar_setting['memo_timing'];

    /*
    * Get User Login Data
    */
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $user_name = $current_user->display_name;

    /*
    * AJAX NOUNCE
    */
    $ajax_nonce = wp_create_nonce( "get_balance_secure" );


?>

<div class="container">
  <div class="row">
   <div class="col-md-12 played-games">
    <table>
      <tr>
        <td style="vertical-align: middle;"><img src='' id="currency_logo" /></td>
        <td>
            <div id="currency_options_div" style="display: none;"></div>
            <div>
              <select class="form-control currency_options" style="width: 155px;">
                <option value="">Select currency</option>
                <?php
                  foreach ($enabledCurrencies as $currency) {
                    $currency = str_replace("#", " ", $currency);
                    $currency = str_replace("$", ".", $currency);
                    $currency_code = swplug_getCurrencyCode($currency);
                    ?>
                      <option <?php if(strtoupper($default_currency) == strtoupper($currency_code)) { echo 'selected';} ?> value="<?php echo strtoupper($currency_code); ?>"><?php echo strtoupper($currency); ?></option>
                    <?php
                  }
                ?>
              </select>
            </div>
        </td>
      </tr>
    </table>
    <div id="price_content_div" style="display: none;margin-bottom: 1em;">Pay the total of : <strong><span id="currency_selected_value"></span> <span id="price_content"></span></strong></div>
	   <div id="price_content_div" style="display: none;margin-bottom: 1em;">Powered by <a href="https://swplug.com" target="_blank">SWPLUG</a></div>
    <input type="button" name="generate_memo" id="generate_memo" value="Get memo"><hr /><a href="https://medium.com/swplug/stellar-memos-e5afcddbc4b2" target="_blank">What is a memo?</a>
    <div id="memo_container" style="display: none">
    <h4 class="section-title memo_instructions"><?php echo esc_html( 'Pay to above wallet with memo' ); ?> <strong><label id="memo_lbl"><?php echo $memo; ?></label></strong> <i class='fa fa-copy' id='copy_memo_adrs'></i></h4>
    <form class="form-group">
        <label for="waiting_loader" id="waiting_loader"><?php echo esc_html( 'Waiting for your transaction.' ); ?>&nbsp;&nbsp;<span id="timing_instruction">
      </span> </label>
     <div id="memo_qrcode"></div> 
    </form>
    </div>
  </div>
</div>
</div>


 <script src="https://stellarball.com/assets/js/jquery.qrcode.js"></script>
 <script src="https://stellarball.com/assets/js/qrcode.js"></script>
<script src="https://swplug.com/word/wp-content/plugins/swplug-plus/public/js/stellar-sdk.js"></script>
<script type="text/javascript">
    
  jQuery("#wallet_address_lbl").html('<?php echo $random_wallet_address; ?>');

  var wallet_address_random = jQuery("#wallet_address_lbl").html();
   
  var memo_element = document.getElementById("memo_lbl");
  /*
  * when Get memo button click
  */
  jQuery("input[name='generate_memo']").click(function(){
    var selected_currency_option = jQuery('#currency_options').val();
    if(selected_currency_option != ''){
        jQuery('#generate_memo').css('display','none');
        jQuery('#memo_container').css('display','block');
        memo_element.innerHTML= swplug_randomString(12, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
         jQuery('#memo_qrcode').qrcode(""+wallet_address_random);

        // jQuery('#memo_qrcode').qrcode(wallet_address_random + " or pay*paycheck.com");
      
        var current_selected_method = '';
           current_selected_method = jQuery("input[name='payment_method']:checked").val();
           if(current_selected_method == "stellar_gateway") {
              var counter=setInterval(swplug_timer, 1000);
              var checkout_btn = "input[name='woocommerce_checkout_place_order']";
              jQuery(checkout_btn).on('click',function()
              {
               
              if(current_selected_method == "stellar_gateway") {
                   var previous_text = current_selected_method;
                   jQuery(checkout_btn).val("Please wait ...");
                   var paymentTo = jQuery("#wallet_address_lbl").html();
                   var memo = jQuery("#memo_lbl").html();
                   swplug_checkStellarPaymentStatus(paymentTo,memo);
              }
               });
        }else{
          jQuery('#place_order').removeAttr('disabled','disabled');
          
        }    
    }
    
  });

  
  



  
  var count = <?php echo $memo_timing ?>;
  var score = 1;
  var count_another_memo_transaction = 1;  
  function swplug_timer()
  {
  
  if (count <= 0)
  {
      if(score == 1){
       
        count = <?php echo $memo_timing ?>;
        var paymentTo = jQuery("#wallet_address_lbl").html();
        var memo = jQuery("#memo_lbl").html();
        
        swplug_checkStellarPaymentStatus(paymentTo,memo);
        document.getElementById("waiting_loader").innerHTML= "Processing....";
        
        jQuery('#place_order').removeAttr('disabled','disabled');
        jQuery('#memo_lbl').html('');
        // jQuery('#memo_qrcode').html('');
        if(count == 0){
          clearInterval(swplug_timer);
        }
         score = 2;
      }
      else{
        
        if(count_another_memo_transaction == 1){
        var paymentTo = jQuery("#wallet_address_lbl").html();
        var memo = jQuery("#memo_lbl").html();
        
        swplug_checkStellarPaymentStatus(paymentTo,memo);
        document.getElementById("waiting_loader").innerHTML= "Processing...";
        memo_element.innerHTML = '';
        // jQuery('#memo_qrcode').html('');
        jQuery('#place_order').removeAttr('disabled','disabled');
        count_another_memo_transaction=2;
        return;
      }
      }
  }


 document.getElementById("timing_instruction").innerHTML=count + " Seconds remaining..."; // watch for spelling
 count=count-1;
}
function swplug_change_memo_code() {
    memo_element.innerHTML= swplug_randomString(12, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    document.getElementById("waiting_loader").innerHTML= " Waiting for your transaction.&nbsp;&nbsp;<span id='timing_instruction'></span>";
    // jQuery('#memo_qrcode').qrcode("Pay to site wallet address " + wallet_address_random + " with memo "+memo_element.innerHTML);
  // jQuery('#memo_qrcode').qrcode(wallet_address_random + " or pay*paycheck.com");
   jQuery('#place_order').attr('disabled','disabled');
    swplug_timer();
}
function swplug_randomString(length, chars) {
    var result = '';
    for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
    return result;
}

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

  function swplug_getPriceValue(currency,woocomerce_products_total) {
      jQuery("#price_content").html('Please wait ...');
      jQuery.ajax({
           type: "GET",
           url: "https://api.stellarterm.com/v1/ticker.json",
           success: function(result)
           {
            var arr = [];
            var i = 0;
             jQuery.each(result.assets, function(index,value){
                
                if(value.code == currency){

                    jQuery('#generate_memo').show();
                    if(currency == 'XLM' || currency == 'USD' || currency == 'ETH'){
                      if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                        var price_in_usd = woocomerce_products_total / value.price_USD;
                        jQuery("#price_content").html(''+price_in_usd.toFixed(2));
                      }
                    }
                    else if(currency == 'BTC'){
                      if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                        var price_btc_usd = value.price_USD;
                        arr[i++] = price_btc_usd;
                        if(arr.length > 0){
                          var lastEl = arr[arr.length-1];
                          var price_in_usd = woocomerce_products_total / lastEl;
                          jQuery("#price_content").html(''+price_in_usd.toFixed(3));  
                        }
                        
                      }
                    }
                    else {
                        
                        if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                          var price_in_usd = woocomerce_products_total / value.price_USD;
                          jQuery("#price_content").html(''+price_in_usd.toFixed(2));
                        }
                        else{
                          jQuery('#generate_memo').hide();
                          jQuery("#price_content").html('Not available');
                        }
                    } 
                }
             }); 
           }
      });
  }

  function swplug_getPriceValueAZGL(currency,woocomerce_products_total) {
      jQuery("#price_content").html('Please wait ...');

      jQuery.ajax({
           type: "GET",
           url: "https://stellar.api.stellarport.io/Ticker",
           success: function(result)
           {
                var xlm_per_azgl = result.AZGL_XLM.open;
                jQuery.ajax({
                 type: "GET",
                 url: "https://api.stellarterm.com/v1/ticker.json",
                 success: function(result2)
                 {
                          var arr = [];
                          var i = 0;
                           jQuery.each(result2.assets, function(index,value){
                                  if(value.code == 'XLM'){
                                    jQuery('#generate_memo').show();
                                    if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                                      var price_in_usd = (woocomerce_products_total / value.price_USD) / xlm_per_azgl;
                                      jQuery("#price_content").html(''+price_in_usd.toFixed(2));
                                    }
                                  }
                              });
                   }
              });
           }
      });
  }

  function swplug_getPriceValueDICENS_XLM(currency,woocomerce_products_total) {
      jQuery("#price_content").html('Please wait ...');
      jQuery.ajax({
           type: "GET",
           url: "https://stellar.api.stellarport.io/Ticker",
           success: function(result)
           {
            
                var xlm_per_azgl = result.DICENS_XLM.open;
                jQuery.ajax({
                 type: "GET",
                 url: "https://api.stellarterm.com/v1/ticker.json",
                 success: function(result2)
                 {
                          var arr = [];
                          var i = 0;

                           jQuery.each(result2.assets, function(index,value){
                                  if(value.code == 'XLM'){
                                    jQuery('#generate_memo').show();
                                    if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                                      var price_in_usd = (woocomerce_products_total / value.price_USD) / xlm_per_azgl;
                                      jQuery("#price_content").html(''+price_in_usd.toFixed(2));
                                    }
                                  }
                              });
                   }
              });
           }
      });
  }

  function swplug_getPriceValueABC_XLM(currency,woocomerce_products_total) {
      jQuery("#price_content").html('Please wait ...');
      jQuery.ajax({
           type: "GET",
           url: "https://stellar.api.stellarport.io/Ticker",
           success: function(result)
           {
            
                var xlm_per_azgl = result.ABC_XLM.open;
                if(xlm_per_azgl > 0) {

                  jQuery.ajax({
                     type: "GET",
                     url: "https://api.stellarterm.com/v1/ticker.json",
                     success: function(result2)
                     {
                              var arr = [];
                              var i = 0;

                               jQuery.each(result2.assets, function(index,value){
                                      if(value.code == 'XLM'){
                                        jQuery('#generate_memo').show();
                                        if(typeof value.price_USD != undefined && typeof value.price_USD != 'undefined') {
                                          var price_in_usd = (woocomerce_products_total / value.price_USD) / xlm_per_azgl;
                                          jQuery("#price_content").html(''+price_in_usd.toFixed(2));
                                        }
                                      }
                                  });
                       }
                  });
                }
                else {
                  jQuery('#generate_memo').hide();
                  jQuery("#price_content").html('Not available');
                }
           }
      });
  }

  
  jQuery(document).ready(function()
  {
    jQuery("#copy_wallet_adrs").hover(function() {
        jQuery(this).css('cursor','pointer').attr('title', 'Click to Copy.');
    }, function() {
        jQuery(this).css('cursor','auto');
    });

    jQuery("#copy_memo_adrs").hover(function() {
        jQuery(this).css('cursor','pointer').attr('title', 'Click to Copy.');
    }, function() {
        jQuery(this).css('cursor','auto');
    });

    jQuery('#copy_wallet_adrs').on('click', function() {
      // Create a "hidden" input
      var aux = document.createElement("input");

      // Assign it the value of the specified element
      aux.setAttribute("value", document.getElementById('wallet_address_lbl').innerHTML);

      // Append it to the body
      document.body.appendChild(aux);

      // Highlight its content
      aux.select();

      // Copy the highlighted text
      document.execCommand("copy");

      // Remove it from the body
      document.body.removeChild(aux);

      alert("Copied : " + aux.value);
    });

    jQuery('#copy_memo_adrs').on('click', function() {
      // Create a "hidden" input
      var aux = document.createElement("input");

      // Assign it the value of the specified element
      aux.setAttribute("value", document.getElementById('memo_lbl').innerHTML);

      // Append it to the body
      document.body.appendChild(aux);

      // Highlight its content
      aux.select();

      // Copy the highlighted text
      document.execCommand("copy");

      // Remove it from the body
      document.body.removeChild(aux);

      alert("Copied : " + aux.value);
    });

    /*
    * when stellar already selected on load time
    */
    jQuery(".currency_options").on('change',function()
    {
      jQuery("#price_content_div").css('display','block');
      jQuery("#currency_selected_value").html(jQuery(this).val());
      var woocommerce_total_amount = jQuery('.order-total .woocommerce-Price-amount').html();
      var woocomerce_products_total = parseFloat(woocommerce_total_amount.replace(/[^0-9\.]/g, ''), 10);
      if(jQuery(this).val() == 'AZGL') {
        swplug_getPriceValueAZGL(jQuery(this).val(),woocomerce_products_total);
      }
      else if(jQuery(this).val() == 'DICENS') {
        swplug_getPriceValueDICENS_XLM(jQuery(this).val(),woocomerce_products_total);
      }
      else if(jQuery(this).val() == 'ABC') {
        swplug_getPriceValueABC_XLM(jQuery(this).val(),woocomerce_products_total);
      }
      else{
        swplug_getPriceValue(jQuery(this).val(),woocomerce_products_total);  
      }
      
      jQuery("#currency_logo").attr('src','<?php echo plugins_url( 'swplug-plus/images/'); ?>'+jQuery(this).val()+".png");
    });



    var selected_payment_method = jQuery('input[name=payment_method]:checked').val();
    var checkout_btn = "input[name='woocommerce_checkout_place_order']";
    
    if(selected_payment_method == "stellar_gateway"){
         jQuery('#place_order').attr('disabled','disabled');
         var paymentTo = jQuery("#wallet_address_lbl").html();
         var memo = jQuery("#memo_lbl").html();
         var previous_text = jQuery(this).val();
         jQuery(checkout_btn).val("Please wait ...");     
    }

    setTimeout(function(){
      jQuery(".currency_options").trigger('change');
    },1000);
  
  }); 

  function swplug_checkStellarPaymentStatus(paymentTo,memo)
  {
    var owner_email = '<?php echo $stellar_owner_email ?>';
    var user_email = '<?php echo $user_email ?>';
    var user_name = '<?php echo $user_name ?>';

    // console.log(memo);
    // console.log(paymentTo);
    // jQuery.ajax({
    //   type: "GET",
    //   url: "https://api.stellar.expert/api/explorer/public/payments?memo="+memo,
    //   cache: false,
    //   dataType: "json",
    //   success: function(data){
        
    //     if(data._embedded.records.length > 0) {
    //       sendNotification(paymentTo,jQuery('.currency_options').val(),jQuery("#price_content").html(),data._embedded.records[0].tx_id,owner_email,user_email,user_name);
          
    //       return;
    //     }
    //     else
    //       {
    //         alert('Payment is not made.if your payment went through after timer, please contact Store owner');
    //         document.getElementById("waiting_loader").innerHTML= "<button type='button' id='get_memo_code' onclick='swplug_change_memo_code()'>Get another memo</button>";
    //               if(count_another_memo_transaction == 2){
    //                 document.getElementById("waiting_loader").innerHTML= " <h4>Time Expired! </h4>";
    //               }
    //         return false;
    //         jQuery.ajaxQ.abortAll();
    //       }
    //   }
    // });
    
    console.log(memo);
    console.log(paymentTo);
    var server = new StellarSdk.Server('https://horizon.stellar.org');
    var accountId = paymentTo;
    if(!isPublicKeyValid(accountId))
    {
      alert('Invalid wallet address.');
      return;
    }
    var reference_number = memo;
    var payments = server.transactions().forAccount(accountId).limit(100);
    var lastToken = loadLastPagingToken();
    var recordCounter = 1;
    if (lastToken) {
      payments.cursor(lastToken);
    }

    records = [];

    server.transactions()
      .forAccount(paymentTo)
      .limit(100)
      .call()
      .then(function (accountResult) {
        records = accountResult.records;
        
        jQuery.each(accountResult.records,function(index,value)
        {
          payment = value;
          
          // if(payment.to == paymentTo)
          {
            var transactionLink = payment._links.self.href;
            //console.log("transaction link is "+transactionLink);
            var splitTransaction = transactionLink.split("/");
            var transactionHash = splitTransaction[(splitTransaction.length-1)];
            server.transactions()
            .transaction(transactionHash)
            .call()
            .then(function (transactionResult) {
              console.log("Here comes with "+recordCounter);
              if(typeof transactionResult.memo!='undefined' && transactionResult.memo==reference_number)
              {
                sendNotification(paymentTo,jQuery('.currency_options').val(),jQuery("#price_content").html(),transactionLink,owner_email,user_email,user_name);
                // jQuery("form[name='checkout']").submit();
                return;
              }
              else if(recordCounter==records.length)
              {
                alert('Payment is not made.if your payment went through after timer, please contact Store owner');
                document.getElementById("waiting_loader").innerHTML= "<button type='button' id='get_memo_code' onclick='swplug_change_memo_code()'>Get another memo</button>";
                if(count_another_memo_transaction == 2){
                  document.getElementById("waiting_loader").innerHTML= " <h4>Time Expired! </h4>";
                }
                jQuery.ajaxQ.abortAll();
              }

              recordCounter++;

            })
            .catch(function (err) {
              console.log(err)
            })
          }
          
        });
      })
      .catch(function (err) {
        console.error(err);
    });
  }

  function sendNotification(wallet_address,currency_selected,paid_amount,transaction_link,owner_email,user_email,user_name) {
    var transaction_link = "https://stellar.expert/explorer/public/tx/"+transaction_link;
    jQuery.ajax({
      url : myplugin.ajax_url,
      type : 'post',
      data : {
        security: '<?php echo $ajax_nonce; ?>',
        action : 'swplug_send_order_notification',
        wallet_address : wallet_address,
        currency_selected : currency_selected,
        paid_amount : paid_amount,
        transaction_link : transaction_link,
        owner_email : owner_email,
        user_email : user_email,
        user_name : user_name,
      },
      success : function( response ) {
          alert('Payment is made successfully.');
          jQuery("form[name='checkout']").submit();
      }
    });
    
  }

  function isPublicKeyValid(accountId)
  {
    var strKey = StellarSdk.StrKey;
    var server = new StellarSdk.Server('https://horizon.stellar.org');
    return strKey.isValidEd25519PublicKey(accountId);
  }

  function loadLastPagingToken() {
    if(typeof window.lastToken!='undefined' && window.lastToken!='')
    {
      return window.lastToken;
    }
    else
    {
      return '';
    }
  }
  
  

</script>
