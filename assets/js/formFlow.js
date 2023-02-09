let options = [
    "App 1",
    "App 2",
    "App 3",
    "App 4"
];

let container = document.querySelector('#formFlow');

function addFlowItem() {
    
    let selectContainer = document.createElement("select");
    selectContainer.classList.add("custom-select");
    selectContainer.classList.add("mb-3");

    for (let option of options) {
        let optionEl = document.createElement("option");
        optionEl.text = option;
        optionEl.value = option;
        selectContainer.appendChild(optionEl);
    }

    container.appendChild(selectContainer);
}