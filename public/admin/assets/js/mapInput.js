// console.log(JSON.parse(localStorage.getItem('city')));
// var CityArray=JSON.parse(localStorage.getItem('city')) || [];
var CityArray=[];
// displayCityCheckbox();
function initialize() {
        localStorage.removeItem('city');
    $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
    const locationInputs = document.getElementsByClassName("map-input");

    const autocompletes = [];
    const geocoder = new google.maps.Geocoder;
    for (let i = 0; i < locationInputs.length; i++) {

        const input = locationInputs[i];
        const fieldKey = input.id.replace("-input", "");
        const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';

        const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
        const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;

        const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
            center: {lat: latitude, lng: longitude},
            zoom: 13
        });
        const marker = new google.maps.Marker({
            map: map,
            position: {lat: latitude, lng: longitude},
        });

        marker.setVisible(isEdit);

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.key = fieldKey;
        autocompletes.push({input: input, map: map, marker: marker, autocomplete: autocomplete});
    }

    for (let i = 0; i < autocompletes.length; i++) {
        const input = autocompletes[i].input;
        const autocomplete = autocompletes[i].autocomplete;
        const map = autocompletes[i].map;
        const marker = autocompletes[i].marker;

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            marker.setVisible(false);
            const place = autocomplete.getPlace();
            // console.log('city- '+JSON.stringify(place));
            // console.log("dfd "+place.geometry.location);
            var lat_long = JSON.stringify(place.geometry.location);
               var lat_long= JSON.parse(lat_long);
               console.log("Sdfsdf "+ JSON.stringify(place.formatted_address));
            var setcity={};
            setcity.checked = true;
            setcity.address = JSON.stringify(place.formatted_address);            
            setcity.latitude = lat_long.lat;
            setcity.longitude = lat_long.lng;                        
            setcity.name=place.address_components[0].long_name;
            console.log('city ob '+setcity.latitude);
            CityArray.push(setcity);

            // CityArray.push(place.address_components[0].long_name);
            window.localStorage.setItem("city", JSON.stringify(CityArray));
            displayCityCheckbox();
            geocoder.geocode({'placeId': place.place_id}, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    setLocationCoordinates(autocomplete.key, lat, lng);
                }
            });

            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                input.value = "";
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

        });
    }
}

function displayCityCheckbox(){
    HTML = "";
    optionsArray = localStorage.getItem('city');

    // console.log('array '+ JSON.parse(optionsArray));
    optionsArray=  JSON.parse(optionsArray);   
    Object.values(optionsArray).forEach(entry=>{
          console.log('sdfe '+entry.checked, entry.name);
          var check_checked = null;
          check_checked =(entry.checked == true)?'checked':null;
            HTML += "<label for='id_"+entry.name+"'>"+entry.name+ " <input type=checkbox value=" + entry.name + " id='id_" + entry.name + "' "+check_checked+" name=city[] onclick=checkbox(this);><input type=hidden name=locations["+entry.name+"][address] value="+entry.address+"><input type=hidden name=locations["+entry.name+"][latitude] value="+entry.latitude+"><input type=hidden name=locations["+entry.name+"][longitude] value="+entry.longitude+"><input type=hidden name=locations["+entry.name+"][check] value="+entry.checked+"></label>";          
    });
    // for (var i = 0; i < optionsArray.length; i++) {

    //   HTML += "<label for='id_"+optionsArray[i]+"'>"+optionsArray[i]+ " <input type=checkbox name=text value=" + optionsArray[i] + " id='id_" + optionsArray[i] + "' onclick=checkbox(this);></label>";
    // }
    document.getElementById("display_city").innerHTML = HTML;    
}
function checkbox(obj){
    var id = obj.id;

    console.log('id '+obj.checked);
        if (obj.checked == true)
        {
            // console.log(document.getElementById(id).checked);
            // document.getElementById(id).checked = false;
            // $('#'+id).prop('checked',false);
             document.getElementById(id).checked = true;
             // document.getElementById(id).checked = true;             
            // $('#'+id).removeAttr('checked');
        }
        if(obj.checked == false)
        {
            // console.log(document.getElementById(id).checked);
            document.getElementById(id).checked = false;
        }

}
function setLocationCoordinates(key, lat, lng) {
    const latitudeField = document.getElementById(key + "-" + "latitude");
    const longitudeField = document.getElementById(key + "-" + "longitude");
    latitudeField.value = lat;
    longitudeField.value = lng;
}