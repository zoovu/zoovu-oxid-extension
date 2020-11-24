
function sxRangeFilterAction(values, handle, unencoded, tap, positions, noUiSlider) {
    // values: Current slider values (array);
    // handle: Handle that caused the event (number);
    // unencoded: Slider values without formatting (array);
    // tap: Event was caused by the user tapping the slider (boolean);
    // positions: Left offset of the handles (array);
    // noUiSlider: slider public Api (noUiSlider);

    var id = this.target.getAttribute('id');
    if (id.endsWith('Sidebar')) id = id.substr(0, id.length - 7);

    document.getElementsByName("attrfilter[" + id + "]")[0].value = values[0] + '___' + values[1];
    document.getElementById('filterList').submit();
}

// i was getting JS errors bs everything was called 2x, fixed it with this 
if (typeof sXStarted == 'undefined') {
    var sXStarted = false;
    startSx();
}

function startSx() {

    if (sXStarted) return;
    sXStarted = true;

    // make it work
    let elementsDone = [];
    if (typeof liTags == 'undefined') {
        let liTags = document.getElementsByTagName("li");
        for (var i = 0; i < liTags.length; i++) {

            let li = liTags[i];

            // check if li filter
            if (!li.parentNode.classList.contains('dropdown-menu') || li.parentNode.parentNode.querySelectorAll('input:not(.js-style)').length != 1) continue;

            // change filter label
            let filterButtonElement = li.parentNode.parentNode.querySelectorAll('button:not(.js-style-btn)')[0];
            if (filterButtonElement) {
                filterButtonElement.innerHTML = filterButtonElement.innerHTML.replace('###', ', ');
            }


            // get Filter and value
            let filterInputElement = li.parentNode.parentNode.querySelectorAll('input:not(.js-style)')[0];
            if (!filterInputElement) continue;
            let filterName = filterInputElement.getAttribute('name');

            var aTags = li.getElementsByTagName('a');
            if (aTags.length != 1) continue;
            let filterOptionElement = li.getElementsByTagName('a')[0]

            var dataSelectionId = filterOptionElement.getAttribute('data-selection-id');

            if (sxAttributeOptions[filterName] && sxAttributeOptions[filterName][dataSelectionId]) {

                filterOptionElement.setAttribute('data-selection-id', sxAttributeOptions[filterName][dataSelectionId]['value']);

                if (sxAttributeOptions[filterName][dataSelectionId]['count'] && sxAttributeOptions[filterName][dataSelectionId]['count'] >= 0) {
                    filterOptionElement.innerHTML = filterOptionElement.innerHTML + ' (' + sxAttributeOptions[filterName][dataSelectionId]['count'] + ')';
                }

                if (sxAttributeOptions[filterName][dataSelectionId]['active']) {
                    filterOptionElement.classList.add('selected');
                }
            }
            else {
                // if we land here, is the "bitte wählen" option that clears the filter!
                // i copy the word of the clear button, bc hopefully is translated
                if (document.querySelector("#resetFilter button")) {
                    const text = document.querySelector("#resetFilter button").innerText;
                    filterOptionElement.innerText = text;
                } else {
                    filterOptionElement.innerText = "Clear";
                }
                filterOptionElement.classList.add('resetFilter');
            }

            // set filter value
            let filterValue = filterInputElement.value;
            let filterValues = filterValue.split('###');

            if (!elementsDone[filterName]) {
                let newFilterValue = [];
                filterValues.forEach(fv => {
                    if (sxAttributeOptions[filterName] && sxAttributeOptions[filterName][fv]) {
                        newFilterValue.push(sxAttributeOptions[filterName][fv]['value']);
                    }
                });
                filterValue = newFilterValue.join('###');
                filterInputElement.setAttribute('value', filterValue);

                elementsDone[filterName] = true;
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
        // sometimes, looks like things are not loaded yet... and no one is that fast anyway
        setTimeout(function () {
            sidebarFiltersEvents();
            justStyleEvents();
        }, 2000);
    }
}

function sidebarFiltersEvents() {                                                        // this btn-filter is for sonepar
    let sidebarFilters = document.querySelectorAll(".sxFilterBoxSidebar .btn-group .btn, .sxFilterBoxSidebar .btn-filter .btn,.sxFilterBoxSidebar .sxRangeFilter label");
    for (var j = 0; j < sidebarFilters.length; j++) {
        sidebarFilters[j].addEventListener('click', function (event) {
            this.closest('.btn-group, .btn-filter, .sxRangeFilter').classList.toggle('sideclosed');
        })
    }

}

function justStyleEvents() {
    const styleInputsMin = document.querySelector(".js-style[data-input-type='min']");
    const styleInputsMax = document.querySelector(".js-style[data-input-type='max']");
    const styleBtn = document.querySelector(".js-style-btn");
    if (styleBtn) {
        styleBtn.addEventListener('click', function (event) {
            const filterName = this.closest('.sxRangeFilter').querySelector('.slider').getAttribute('id');
            if ((styleInputsMin.value < styleInputsMax.value) &&
                (styleInputsMin.value >= styleInputsMin.getAttribute('min')) &&
                (styleInputsMax.value <= styleInputsMax.getAttribute('max'))
            ) {
                document.getElementsByName("attrfilter[" + filterName + "]")[0].value = styleInputsMin.value + '___' + styleInputsMax.value;

            } else {
                document.getElementsByName("attrfilter[" + filterName + "]")[0].value = styleInputsMin.getAttribute('min') + '___' + styleInputsMax.getAttribute('max');
            }
            document.getElementById('filterList').submit();
        });
    }
}

function hasSomeParentTheClass(element, classname) {
    if (element.className && element.className.split(' ').indexOf(classname) >= 0) return true;
    return element.parentNode && hasSomeParentTheClass(element.parentNode, classname);
}

function getParentWithClass(element, classname) {
    if (element.className && element.className.split(' ').indexOf(classname) >= 0) return element;
    return element.parentNode && getParentWithClass(element.parentNode, classname);
}
