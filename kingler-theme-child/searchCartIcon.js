
  
window.onload = function addIcon() {

  
      
         var shopContentWrap2 = document.getElementById("product-search-field-0");
        var shopButton2 = document.createElement("BUTTON");


  		shopButton2.setAttribute("class", "search_button  icon-search");
        shopButton2.setAttribute("type", "submit");

      
        // divContainer.innerHTML = "before look";
        // document.body.appendChild(divContainer);


       shopContentWrap2.parentNode.insertBefore(shopButton2, shopContentWrap2.nextSibling);
      
  
}