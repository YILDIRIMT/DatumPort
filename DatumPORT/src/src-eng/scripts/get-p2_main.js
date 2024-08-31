   $(document).ready(function()
   {
      function DataCheck()
      {
      $.ajax({
          url: "api/main_page_api_vd.php",
          type:"POST",
          success: function (result)
          {
         document.getElementById("p2").innerHTML=result;
      }});
       }
       DataCheck();

      setInterval(DataCheck,7000);
  });
