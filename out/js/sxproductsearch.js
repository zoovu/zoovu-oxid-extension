
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