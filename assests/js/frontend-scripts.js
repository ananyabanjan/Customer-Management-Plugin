// Frontend Scripts for Custom Customer Management Plugin

jQuery(document).ready(function($) {
    var perPage = 10; // Matches backend default;

    function loadCustomers(page = 1, search = '') {
        $.post(ccm_ajax.ajax_url, {
            action: 'ccm_search_customers',
            page: page,
            per_page: perPage,
            search: search
        }, function(response) {
            // response: { customers: [...], total_pages: X, current_page: Y, total_count: Z }
            $('#customers-container').html(
                '<table class="customer-table">' +
                    '<thead>' +
                        '<tr>' +
                            '<th>Name</th>' +
                            '<th>Email</th>' +
                            '<th>Age</th>' +
                            '<th>Phone</th>' +
                            '<th>Gender</th>' +
                            '<th>CR Number</th>' +
                            '<th>Address</th>' +
                            '<th>City</th>' +
                            '<th>Country</th>' +
                            '<th>Status</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody id="customer-tbody"></tbody>' +
                '</table>'
            );
            response.customers.forEach(function(customer) {
                $('#customer-tbody').append(
                    '<tr>' +
                        '<td>' + customer.name + '</td>' +
                        '<td>' + customer.email + '</td>' +
                        '<td>' + customer.age + '</td>' +
                        '<td>' + customer.phone + '</td>' +
                        '<td>' + customer.gender + '</td>' +
                        '<td>' + customer.cr_number + '</td>' +
                        '<td>' + customer.address + '</td>' +
                        '<td>' + customer.city + '</td>' +
                        '<td>' + customer.country + '</td>' +
                        '<td>' + customer.status + '</td>' +
                    '</tr>'
                );
            });

            // Generate pagination only if total_pages > 1
            generatePagination(response.total_pages, response.current_page, search);
        });
    }

    function generatePagination(totalPages, currentPage, search) {
        $('#pagination').html('');
        if (totalPages > 1) {
            var paginationHtml = '';
            if (currentPage > 1) {
                paginationHtml += '<button data-page="' + (currentPage - 1) + '">Previous</button>';
            }
            for (var i = 1; i <= totalPages; i++) {
                paginationHtml += '<button data-page="' + i + '"' + (i === currentPage ? ' disabled' : '') + '>' + i + '</button>';
            }
            if (currentPage < totalPages) {
                paginationHtml += '<button data-page="' + (currentPage + 1) + '">Next</button>';
            }
            $('#pagination').html(paginationHtml);

            // Bind click events
            $('#pagination button:not(:disabled)').on('click', function() {
                var page = $(this).data('page');
                loadCustomers(page, search);
            });
        }
    }

    loadCustomers();
    $('#search-customers').on('input', function() {
        loadCustomers(1, $(this).val());
    });
});