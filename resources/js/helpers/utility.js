function filterObjsByColumns(paginatedData, columns) {
    if (paginatedData?.data) {
        let newArr = paginatedData.data.map(function (item) {
            let newItem = {}
            columns.forEach(function (column) {
                if (item.hasOwnProperty(column.data)) {
                    newItem[column.data] = item[column.data]
                }
            });
            return newItem
        });
        return newArr
    }
    return []
}

/**
 *
 * @param stringNum
 * @param format decimal | currency
 * @returns {string}
 */
function toCurrency(stringNum, format = 'decimal') {
    stringNum = parseFloat(stringNum)
    return new Intl.NumberFormat('en-PH', {style: format, currency: 'PHP', minimumFractionDigits: 2}).format(stringNum?.toFixed(2))
}

function closeModal(modalName) {
    var myModalEl = document.getElementById(modalName)
    var modal = bootstrap.Modal.getInstance(myModalEl)
    modal.hide()
}

function openModal(modalName) {
    var myModalEl = document.getElementById(modalName)
    var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)
    modal.show()
    return modal
}

export {filterObjsByColumns, toCurrency, closeModal, openModal}
