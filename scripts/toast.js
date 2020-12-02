function creatToast(timeToShow, type, msg) 
{
    var element = document.getElementById("toast");
  
    element.classList.add("show");
    element.classList.add(type);
    element.innerHTML = msg;
  
    // After 3 seconds, remove the show class from DIV
    setTimeout(function()
    { 
        element.classList.remove("show");
        element.classList.remove(type);
        element.innerHTML = "";
    }, timeToShow);


}