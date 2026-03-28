<?php
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:../error.php");
    exit();
}
session_start();

// The do_pass_reveal() function in modules_funcs.php already handles
// password generation and email sending. We just need to display the result.

if(isset($_POST['reveal'])) {
  $store = do_pass_reveal();
  
  if(isset($store['success']) && !empty($store['success'])) {
      // Display the success message from do_pass_reveal
      echo '<p class="success">' . htmlspecialchars($store['success'][0]) . '</p>';
  } else {
      show_messages($store);
  }
}
?>
<form class='form' method='post' autocomplete="off">
<table class="table_reg">

                    <tbody>
                      <tr>
                        <td width="30%"><?php echo phrase_username?></td>
                        <td align='left' colspan='3'><input id="username" type='text' name='account' maxlength='10' class='button' autocomplete="off"><span class='error' id='txtHint'></span></td>
                      </tr>
                      <tr>
                        <td width="30%">&nbsp;&nbsp;<?php echo phrase_email?></td>
                        <td align='left'colspan='3' width="30%">
                        <input type='email' id="email" name='email' maxlength='40' class='button' autocomplete="off">
                        <span id='txtMail'></span>
                        </td>
						</tr>
                        <tr>
                        <td width="30%">&nbsp;&nbsp;<?php echo phrase_code_check?></td><td>
                       <td align='left'colspan='1' width="30%"><input type="text" class="lanyu" onkeypress="return num(event, this)" name="verify" maxlength="5" autocomplete="off"/></td>
					   <td style="text-align:left;"><img src="js/verify/verify.php?<?php echo time(); ?>" onclick="this.src='js/verify/verify.php?'+Math.random();" style="cursor:pointer;" title="Click to refresh"/></td>
                      </tr>
                      <tr>                       
                        <td colspan="5" style="text-align: center;">
                        <hr></br>
                    
                             <input type='submit' class="button" value='<?php echo phrase_pass_reveal?>' name="reveal">
							<input type="reset" name="Reset" value="<?php echo phrase_reset?>" class="button"></td>
					 
                      </tr>
                    </tbody>
					
					
       </form>
					</table>
