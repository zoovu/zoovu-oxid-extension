
function sxRangeFilterAction(values, handle, unencoded, tap, positions, noUiSlider) {
    // values: Current slider values (array);
    // handle: Handle that caused the event (number);
    // unencoded: Slider values without formatting (array);
    // tap: Event was caused by the user tapping the slider (boolean);
    // positions: Left offset of the handles (array);
    // noUiSlider: slider public Api (noUiSlider);

    document.getElementsByName("attrfilter[" + this.target.getAttribute('id') + "]")[0].value = values[0] + '___' + values[1];
    document.getElementById('filterList').submit();
}


// make multiselect filter work
let aTags = document.getElementsByTagName("a"); // not nice, but makes it work with many browsers
for (var filterName in sxMultiselectFilter) {

    sxMultiselectFilter[filterName].forEach(function (option) {

        // make it visible
        for (var i = 0; i < aTags.length; i++) {
            if (aTags[i].textContent == option) {
                aTags[i].classList.add("selected");
                break;
            }
        }

    })
};

// make it work
let liTags = document.getElementsByTagName("li");
for (var i = 0; i < liTags.length; i++) {

    if (!liTags[i].parentNode.classList.contains('dropdown-menu')) continue;

    let li = liTags[i];
    let currentInput = li.parentNode.parentNode.getElementsByTagName('input')[0];
    if (!currentInput) continue;

    let currentButton = li.parentNode.parentNode.getElementsByTagName('button')[0];
    if (currentButton) {
        currentButton.innerHTML = currentButton.innerHTML.replace('###', ', ');
    }

    li.addEventListener('click', function (event) {

        var currentDataSelectionId = li.firstChild.getAttribute('data-selection-id');

        if (currentInput.value.indexOf(currentDataSelectionId) > -1) {
            li.firstChild.setAttribute('data-selection-id', currentInput.value.replace(currentDataSelectionId, ''));
        } else {
            if (currentInput.value.length > 0) currentInput.value = currentInput.value + '###';

            li.firstChild.setAttribute('data-selection-id', currentInput.value + currentDataSelectionId);
        }
    })
}



/*
if ( $oFilterList.length )
{
    $oFilterList.find( '.dropdown-menu li' ).click( function ()
        {
            var $this = $( this );
            $this.parent().prev().val( $this.children().first().data( 'selection-id' ) );
            $this.closest( 'form' ).submit();
        }
    );
}
*/