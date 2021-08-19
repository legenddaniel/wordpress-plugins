window.addEventListener('load', function () {

    var table = document.querySelector('.buyer');

    // Initial min and max values
    var min = table.dataset.min,
        max = table.dataset.max;

    var add = table.querySelector('.plus');
    var remove = table.querySelector('.minus');
    var reset = table.querySelector('.reset');

    // Click to add new row
    add.addEventListener('click', function () {
        var tbody = table.tBodies[0];
        var rl = tbody.rows.length;
        var lastMin = tbody.querySelector(`#point-min-${rl - 1}`);
        var newThreshold = lastMin && lastMin.innerText > 1 ? lastMin.innerText - 1 : 0;

        // Add new row
        var frag = document.createDocumentFragment();
        var wrapper = document.createElement('tr');
        wrapper.innerHTML += `
        <tr>
            <td class="auction-points">
                <span id="point-min-${rl}">${min}</span>
                <span>-</span>
                <input type="number" step="1" value="${newThreshold}" id="point-max-${rl}" name="point-max-${rl}">
            </td>
            <td>
                <input type="number" min="0" step="1" id="fee-${rl}" name="fee-${rl}">
            </td>
        </tr>
        `;
        frag.appendChild(wrapper);
        tbody.appendChild(frag);
    });

    // Remove last row (minimun 1 row)
    remove.addEventListener('click', function () {
        var tbody = table.tBodies[0];
        var rl = tbody.rows.length;
        if (rl < 2) return;

        // Reset table if only 1 row left afterwards
        if (rl == 2) {
            return reset.click();
        }

        // Delete row and reset the last `min` 
        tbody.deleteRow(-1);
        var lastMin = tbody.querySelector(`#point-min-${rl - 2}`);
        if (lastMin) {
            lastMin.innerText = min;
        }
    })

    // Reset the table to 1 row
    reset.addEventListener('click', function () {
        var tbody = table.tBodies[0];
        tbody.innerHTML = `
        <tr>
            <td class="auction-points">
                <span id="point-min-0">${min}</span>
                <span>-</span>
                <input type="number" step="1" value="${max}" id="point-max-0" name="point-max-0">
            </td>
            <td>
                <input type="number" min="0" step="1" id="fee-0" name="fee-0">
            </td>
        </tr>
        `;
    })

    // Auto change the last `min` when changing `max`
    table.addEventListener('input', function (e) {
        var target = e.target;
        var rl = target.id.split('point-max-')[1];
        if (typeof rl === 'undefined') return;

        var v = target.value;
        if (!v) {
            target.value = min;
        }

        // Set last `min` and current `max`
        e.currentTarget.querySelector(`#point-min-${rl - 1}`).innerText = v ? v - -1 : min;
    })
})