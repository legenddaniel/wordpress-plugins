window.addEventListener('load', function () {

    var table = document.querySelector('.buyer');
    var min = table.dataset.min,
        max = table.dataset.max;

    var add = table.querySelector('.plus');

    // Click to add new row
    add.addEventListener('click', function () {
        var tbody = table.tBodies[0];

        var rl = tbody.rows.length;
        var lastMin = table.querySelector(`#point-min-${rl - 1}`);
        var newThreshold = lastMin.innerText > 1 ? lastMin.innerText - 1 : 0;
        var html = `
        <tr>
            <td class="auction-points">
                <span id="point-min-${rl}">${min}</span>
                <span>-</span>
                <input type="number" min="${min}" max="${newThreshold}" step="1" value="${newThreshold}" id="point-max-${rl}">
            </td>
            <td>
                <input type="number" min="0" step="1" id="fee-${rl}">
            </td>
        </tr>
        `;
        tbody.innerHTML += html;
    });

    // Auto change the last `min` when changing `max`
    table.addEventListener('input', function (e) {
        var target = e.target;
        var rl = target.id.split('point-max-')[1];
        if (typeof rl === 'undefined') return;

        var v = target.value;
        if (!v) {
            target.value = min;
        }
        e.currentTarget.querySelector(`#point-min-${rl - 1}`).innerText = v ? v - -1 : min;
    })
})