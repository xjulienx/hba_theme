(function ($) {
  $(document).ready(function() {
    // only apply this to node and comment and new user registration forms
    var forms = $(".node-form>div>div>#edit-submit,#comment-form>div>div>#edit-submit,#user-register-form>div>div>#edit-submit");

    // insert the saving div now to cache it for better performance and to show the loading image
    $('<div id="saving"><p class="saving">Saving&hellip;</p></div>').insertAfter(forms);

    forms.click(function() {
      $(this).siblings("input:submit").hide();
      $(this).hide();
      $("#saving").show();

      var notice = function() {
        $('<p id="saving-notice">Not saving? Wait a few seconds, reload this page, and try again. Every now and then the internet hiccups too :-)</p>').appendTo("#saving").fadeIn();
     };

      // append notice if form saving isn't work, perhaps a timeout issue
      setTimeout(notice, 24000);
    });
   });

})(jQuery)
