
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


// make it work
let liTags = document.getElementsByTagName("li");
for (var i = 0; i < liTags.length; i++) {

    let li = liTags[i];

    // check if li filter
    if (!li.parentNode.classList.contains('dropdown-menu') || li.parentNode.parentNode.getElementsByTagName('input').length != 1) continue;

    // change filter label
    let filterButtonElement = li.parentNode.parentNode.getElementsByTagName('button')[0];
    if (filterButtonElement) {
        filterButtonElement.innerHTML = filterButtonElement.innerHTML.replace('###', ', ');
    }


    // get Filter and value
    let filterInputElement = li.parentNode.parentNode.getElementsByTagName('input')[0];
    if (!filterInputElement) continue;
    let filterValue = filterInputElement.value;
    let filterName = filterInputElement.getAttribute('name');
    let filterOptionElement = li.firstChild;

    if (sxAttributeOptions[filterName][filterOptionElement.getAttribute('data-selection-id')]) {

        var dataSelectionId = filterOptionElement.getAttribute('data-selection-id');

        filterOptionElement.setAttribute('data-selection-id', sxAttributeOptions[filterName][dataSelectionId]['value']);

        if (sxAttributeOptions[filterName][dataSelectionId]['active']) {
            filterOptionElement.classList.add('selected');
        }
    }

    li.addEventListener('click', function (event) {

        var dataSelectionId = filterOptionElement.getAttribute('data-selection-id');

        if (dataSelectionId.length == 0) filterValue = '';

        if (filterValue.indexOf(dataSelectionId) > -1) {
            filterOptionElement.setAttribute('data-selection-id', filterValue.replace(dataSelectionId, ''));
        } else {
            if (filterValue.length > 0) filterValue = filterValue + '###';

            filterOptionElement.setAttribute('data-selection-id', filterValue + dataSelectionId);
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