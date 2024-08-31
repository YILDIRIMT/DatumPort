$(document).ready(function()
{
   function DataCheck()
   {
   $.ajax({
       url: "api/main_page_api.php",
       type:"POST",
       success: function (result)
       {
      document.getElementById("p1").innerHTML=result;
   }});
    }
    DataCheck();

   setInterval(DataCheck,7000);
});
