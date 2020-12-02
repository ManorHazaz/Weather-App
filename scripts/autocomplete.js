window.addEventListener('load', (event) => {

    document.getElementById("search-form").addEventListener('submit', event => {
        var str = document.getElementById("search-city").value;
        var str = str.trim();
        if(str)
        {
            keyEnterPress();
        }
        else
        {
            event.preventDefault();
            creatToast(3000, "error", "please enter valid location");

        }

    });

    document.getElementById("search-city").addEventListener("keyup", event => {
            var str = document.getElementById("search-city").value;

            if( event.key == "ArrowDown" || event.key == "ArrowUp" || event.key == "Enter")
            {
                if((document.getElementById("auto-field").innerHTML).trim() != "")
                {
                    var options = document.getElementsByClassName("auto-option");
                    if(event.key == "ArrowDown")
                    {
                        keyArrowDownPress(options);
                    }

                    if(event.key == "ArrowUp")
                    {
                        keyArrowUpPress(options);
                    }
                }
            }
            else
            {
                if(str){
                    readTextFile("scripts/cities.json", function(text){
                        var cities = JSON.parse(text);
                        
                        str = str.trim();
                        var capitalizedStr = titleCase(str);
                        searchInJson(capitalizedStr,cities);
                    });
                }
            }
        });
});

// search in json and build autocomplete field
function searchInJson(str,cities)
{   
    var autoCompleteCities = [];
    document.getElementById("auto-field").innerHTML = "";
    document.getElementById("search-city").classList.remove("search-input-autocomplete")
    cities.forEach(city => {
        if( city.startsWith(str) )
        {
            autoCompleteCities.push(city);
        }
    });

    var counter = 0 ;
    if(autoCompleteCities.length > 30)
    {
        return;
    }
    else
    {
        document.getElementById("search-city").classList.add("search-input-autocomplete")
        autoCompleteCities.forEach(autoCompleteCity => {

            if( counter == 0 )
            {
                document.getElementById("auto-field").innerHTML = document.getElementById("auto-field").innerHTML + "<li class='auto-option focus'>" + autoCompleteCity + "</li>";
            }
            else
            {
                document.getElementById("auto-field").innerHTML = document.getElementById("auto-field").innerHTML + "<li class='auto-option'>" + autoCompleteCity + "</li>";
            }

            counter++;
        });
    }
}

// moving one option down in the autocomplete options
function keyArrowDownPress( options )
{
    for (let i = 0; i < options.length; i++)
    {
        if(options[i].classList.contains("focus"))
        {
            options[i].classList.remove("focus");
            if((i+1) >= options.length)
            {
                options[0].classList.add("focus")
            }
            else
            {
                options[i+1].classList.add("focus")
            }
            break;
        }
    }
}

// moving one option up in the autocomplete options
function keyArrowUpPress( options )
{
    for (let i = 0; i < options.length; i++)
    {
        if(options[i].classList.contains("focus"))
        {
            options[i].classList.remove("focus");
            if((i-1) < 0 )
            {
                options[options.length - 1].classList.add("focus")
            }
            else
            {
                options[i-1].classList.add("focus")
            }
            break;
        }
    }
}

// inserting the chosen state to the search area
function keyEnterPress()
{
    var chosen = document.getElementsByClassName("focus")[0].innerHTML;
    document.getElementById("search-city").value = chosen;
}


// upper case all the string
function titleCase(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
    }
    // Directly return the joined string
    return splitStr.join(' '); 
 }

//  reading the json file
function readTextFile(file, callback) {
    var rawFile = new XMLHttpRequest();
    rawFile.overrideMimeType("application/json");
    rawFile.open("GET", file, true);
    rawFile.onreadystatechange = function() {
        if (rawFile.readyState === 4 && rawFile.status == "200") {
            callback(rawFile.responseText);
        }
    }
    rawFile.send(null);
}