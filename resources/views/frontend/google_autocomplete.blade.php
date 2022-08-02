<!-- Google Map -->
<script
src="https://maps.google.com/maps/api/js?key={{ env('MAP_API_KEY') }}&amp;libraries=places&amp;callback=initAutocomplete"
type="text/javascript">
</script>
<script>
    google.maps.event.addDomListener(window, 'load', initialize);
    function initialize() {
    var input = document.getElementById('autocomplete');
    console.log('input', input);
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener('place_changed', function() {
    var place = autocomplete.getPlace();
    console.log('completeAddress - ', place);
    console.log(place.geometry['location'].lat());
    console.log(place.geometry['location'].lng());
    var countryName = null;
    $.map(place.address_components, function(vale, key){
        console.log('values', vale);
        if(vale.types[0] === 'country'){
            console.log('valename', vale.long_name);
            countryName = vale.long_name;
        }
    })
    $('#autocomplete').after('<input type="hidden" class="form-control mb-3" id="latitude" name="latitude" readonly="" value="'+place.geometry['location'].lat()+'">')
    $('#autocomplete').after('<input type="hidden" class="form-control mb-3" id="country_name" name="country_name" readonly="" value="'+countryName+'">')
    $('#autocomplete').after('<input type="hidden" class="form-control mb-3" id="longitude" name="longitude" readonly="" value="'+place.geometry['location'].lng()+'">')
    // $('#latitude').val(place.geometry['location'].lat());
    // $('#longitude').val(place.geometry['location'].lng());
    });
    }
</script>
