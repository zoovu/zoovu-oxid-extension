
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

                // set data-selection-id
                filterOptionElement.setAttribute('data-selection-id', sxAttributeOptions[filterName][dataSelectionId]['value']);

                // set active if active
                if (sxAttributeOptions[filterName][dataSelectionId]['active']) {
                    filterOptionElement.classList.add('selected');
                }

                // remove id from label
                filterOptionElement.innerHTML = filterOptionElement.innerHTML.replace(sxAttributeOptions[filterName][dataSelectionId]['id'], '');

                // add results count if active
                if (sxAttributeOptions[filterName][dataSelectionId]['count'] && sxAttributeOptions[filterName][dataSelectionId]['count'] >= 0) {
                    filterOptionElement.innerHTML = filterOptionElement.innerHTML + ' (' + sxAttributeOptions[filterName][dataSelectionId]['count'] + ')';
                }


                if (sxAttributeOptions[filterName][dataSelectionId]['isTreeNode']) {
                   
                    filterOptionElement.parentNode.parentNode.classList.add('showAll');
                    filterOptionElement.parentNode.parentNode.classList.add('categoryTree');

                    filterOptionElement.parentNode.setAttribute('style', sxAttributeOptions[filterName][dataSelectionId]['css']);
                    if (sxAttributeOptions[filterName][dataSelectionId]['isHidden']) {
                        filterOptionElement.parentNode.classList.add('hidden');
                    }
                    
                    filterOptionElement.parentNode.setAttribute('data-id', sxAttributeOptions[filterName][dataSelectionId]['id']);
                    filterOptionElement.parentNode.setAttribute('data-parent-id', sxAttributeOptions[filterName][dataSelectionId]['parentId']);
                    filterOptionElement.parentNode.setAttribute('data-parent-ids', sxAttributeOptions[filterName][dataSelectionId]['parentIds']);

                    if (sxAttributeOptions[filterName][dataSelectionId]['isParent']) {
                        filterOptionElement.setAttribute('data-is-parent', true);

                        if (sxAttributeOptions[filterName][dataSelectionId]['isFolded']) {
                            filterOptionElement.parentNode.classList.add("folded")
                        }
                        filterOptionElement.outerHTML = filterOptionElement.outerHTML + ' <span class="caret" onclick="return sxExpandCategory(this, \'' + sxAttributeOptions[filterName][dataSelectionId]['id'] + '\')"></span>';
                        //filterOptionElement.outerHTML = ' <span class="caret" onclick="return sxExpandCategory(this, \'' + sxAttributeOptions[filterName][dataSelectionId]['id'] + '\')"></span>' + filterOptionElement.outerHTML;

                    }
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
    var filterName;
    if (styleBtn) {
        styleBtn.addEventListener('click', function () {
            filterName = this.closest('.sxRangeFilter').querySelector('.slider').getAttribute('id');
            filterName = filterName.replace("Sidebar", "");

            if (document.getElementsByName("attrfilter[" + filterName + "]").length == 0) {
                console.warn("SEMKNOX SiteSearch360: filter input for '" + filterName + "' does not exist");
                return false;
            }

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

function sxExpandCategory(element, parentId) {

    if (parentId <= 0) return false;

    var folded = element.parentNode.classList.contains('folded');

    if (folded) {
        element.parentNode.classList.remove('folded');
    } else {
        element.parentNode.classList.add('folded');
    }

    document.querySelectorAll('#filterList .dropdown-menu li').forEach(function (node) {
        if (folded) {
            if (node.getAttribute("data-parent-id") && node.getAttribute("data-parent-id").indexOf(parentId) > -1) {
                node.classList.remove('hidden');
            }
        } else {
            if (node.getAttribute("data-parent-ids") && node.getAttribute("data-parent-ids").indexOf(parentId) > -1) {
                node.classList.add('hidden');
                node.classList.add('folded');
            }
        }
    });

    return false;

}

document.querySelectorAll('#filterList .dropdown-menu .caret').forEach(function (node) {

    node.addEventListener('click', function (e) {
        e.preventDefault();
        e.cancelBubble = true;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
    });

})