jQuery(document).ready(function(){

  jQuery("button#BlogsNetwork_copykey").click(function(e){

    e.preventDefault()

    jQuery("input#token").select();

    document.execCommand("copy");

  })

});