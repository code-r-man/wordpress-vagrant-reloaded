window.addEventListener('load', function () {
    var btnSearch = document.getElementById('btn-search-everywhere');
    var textSearch = document.getElementById('text-search-everywhere');
    var checkIgnoreCase = document.getElementById('check-search-everywhere-ignore-case');
    if (!btnSearch) {
        return;
    }
    btnSearch.addEventListener('click', function () {
        var searchPhrase = textSearch.value;
        if (searchPhrase.trim().length === 0) {
            return;
        }
        if (W3Ex.abemodule.getGridItem().getData().length > 0) {
            var selected_rows = [];
            W3Ex.abemodule.getGridItem().getData().forEach(function(row, i) {
                Object.keys(row).forEach(function(cell) {
                    if (typeof row[cell] !== 'string') {
                        return;
                    }

                    var searchResultFound = false;

                    if (checkIgnoreCase.checked) {
                        console.log('case ignored');
                        searchResultFound = row[cell].toLowerCase().includes(searchPhrase.toLowerCase());
                    } else {
                        console.log('case NOT ignored');
                        searchResultFound = row[cell].includes(searchPhrase);
                    }

                    if (searchResultFound && selected_rows.indexOf(i) === -1) {
                        selected_rows.push(i);
                    }
                });
            });
            W3Ex.abemodule.getGridItem().setSelectedRows(selected_rows);
            $("#selectdialog").dialog('close');
        }
    })
});
