$(document).ready(function(){
// ---------------------------------------------- //
// ----- DEFINITION OF VARIABLES TO BE USED ----- //
// ---------------------------------------------- //
    let // User List variables
        paginationUnderline = $('.paginationUnderline'),
        paginationContainer = $('#paginationContainer'),
        logoutLink = $('#logoutLink'),
        neededPaginationNumbers,
        currentActivatedPagination = 1,
        idOfRequestedPagination,
        requestedPagination,
        // AJAX variables
        ajaxResponse,
        numberOfIdentifiedUsers,
        userAccountsBasicInformation,
        n;


// ---------------------------- //
// ----- LINKS DEFINITION ----- //
// ---------------------------- //
    logoutLink.attr("href", APP_URL + URL_CloseSession);


// ------------------------ //
// ----- STYLES FIXES ----- //
// ------------------------ //
    paginationUnderline.css({
        'background': '#F5F5F5'
    });
    $('#p1Underline').css({
        'background': '#EC8439'
    });


// ---------------------------------- //
// ----- USERLIST MODAL EFFECTS ----- //
// ---------------------------------- //
    logoutLink.parent().parent().on('click', function(e){
        e.preventDefault();
        window.location = APP_URL + URL_CloseSession;
    });


// ---------------------------------- //
// ----- USERLIST TABLE PROCESS ----- //
// ---------------------------------- //
    /**
     * This code is used to update the Userlist table when the Pagination is used by the user.
     *
     * @return void
     *
     * @author Miranda Meza César
     * DATE January 15, 2020
     */
    paginationContainer.children().on('click', function(){
        idOfRequestedPagination = $(this).find('.paginationUnderline').attr('id');
        if (idOfRequestedPagination.match('[0-5]')==null) {
            // Next pagination has been selected
            if (numberOfIdentifiedUsers > ((currentActivatedPagination)*5)) {
                // There is a further pagination available
                requestedPagination = parseInt(currentActivatedPagination) + 1;

                // Hide the pagination underline of the actual selected value
                $('#p'+currentActivatedPagination+'Underline').css({
                    'background': '#F5F5F5'
                });
                $('#nextUnderline').css({
                    'background': '#F5F5F5'
                });

                // Show the pagination underline of the desired selected value
                if (requestedPagination<6) {
                    $('#p'+requestedPagination+'Underline').css({
                        'background': '#EC8439'
                    });
                } else {
                    $('#nextUnderline').css({
                        'background': '#EC8439'
                    });
                }

                // Update the Userlist table with the accordingly requested pagination number
                updateUserlistTable(userAccountsBasicInformation, numberOfIdentifiedUsers, requestedPagination);
                currentActivatedPagination = requestedPagination;
            } // Otherwise, there isnt a further pagination
        } else {
            // A numeric pagination has been selected
            requestedPagination = idOfRequestedPagination.match('[0-5]')[0];

            // Hide the pagination underline of the actual selected value
            $('#p'+currentActivatedPagination+'Underline').css({
                'background': '#F5F5F5'
            });
            $('#nextUnderline').css({
                'background': '#F5F5F5'
            });

            // Show the pagination underline of the desired selected value
            $('#p'+requestedPagination+'Underline').css({
                'background': '#EC8439'
            });

            // Update the Userlist table with the accordingly requested pagination number
            updateUserlistTable(userAccountsBasicInformation, numberOfIdentifiedUsers, requestedPagination);
            currentActivatedPagination = requestedPagination;
        }
    });

    /**
     * This code is used to update the Userlist table when the "Userlist/index" web-page is visited.
     *
     * @return void
     *
     * @author Miranda Meza César
     * DATE January 15, 2020
     */
    $.post(
        '/Userlist/ajaxGetUserlistTableData',
        {
        },
        function (response) {
            ajaxResponse = JSON.parse(response);
            if (ajaxResponse['status'] == 202) {
                // User logged in successfully
                numberOfIdentifiedUsers = ajaxResponse['data'][0]; // numberOfIdentifiedUsers = total number of user accounts
                userAccountsBasicInformation = ajaxResponse['data'][1]; // userAccountsBasicInformation["specific user account from 0 up to n total user accounts"]."username / first_name / last_name attributes"

                // UPDATE THE USERLIST TABLE WITH THE DATA RECEIVED THROUGH THE JSON
                updateUserlistTable(userAccountsBasicInformation, numberOfIdentifiedUsers, 1);
            } else {
                // No user accounts were detected in the database
                alert(ajaxResponse['message']);
            }
        }
    );


// -------------------------------------------- //
// ----- FUNCTIONS USED FOR THIS .JS FILE ----- //
// -------------------------------------------- //
    /**
     * updateUserlistTable(uABI = "object that contains the basic information of all the user accounts arranged in an array",
     *                     nOIU = "total number of user accounts",
     *                     desiredPagination = "desired pagination to display in the table")
     *
     * This function is in charge of updating the displayed content of the Userlist table.
     *
     * @return void
     *
     * @author Miranda Meza César
     * DATE January 15, 2020
     */
    function updateUserlistTable(uABI, nOIU, desiredPagination) {
        let totalNumberOfUsersToDisplay = 0;
        if ((5 * desiredPagination) < nOIU) {
            totalNumberOfUsersToDisplay = 5;
        } else {
            totalNumberOfUsersToDisplay = nOIU - (5 * (desiredPagination - 1));
        }

        // Update Userlist table with the currently existing user accounts
        for (n = 0; n < totalNumberOfUsersToDisplay; n++) {
            $('#checkRow' + (n + 1)).css({
                'display': 'inline'
            });
            $('#usernameRow' + (n + 1)).empty();
            $('#usernameRow' + (n + 1)).append(uABI[n + (5 * (desiredPagination - 1))].username);
            $('#fullName' + (n + 1)).empty();
            $('#fullName' + (n + 1)).append(uABI[n + (5 * (desiredPagination - 1))].first_name + ' ' + uABI[n + (5 * (desiredPagination - 1))].last_name);
            $('#extraText1Row' + (n + 1)).css({
                'display': 'inline'
            });
            $('#extraText2Row' + (n + 1)).css({
                'display': 'inline'
            });
        }
        // Hide text and checks from rows that wont have information on them (out of user accounts)
        for (n = totalNumberOfUsersToDisplay; n < 5; n++) {
            $('#checkRow' + (n + 1)).css({
                'display': 'none'
            });
            $('#usernameRow' + (n + 1)).empty();
            $('#fullName' + (n + 1)).empty();
            $('#extraText1Row' + (n + 1)).css({
                'display': 'none'
            });
            $('#extraText2Row' + (n + 1)).css({
                'display': 'none'
            });
        }

        // Hide the pagination numbers that arent going to be used
        neededPaginationNumbers = Math.ceil(nOIU / 5);
        if (neededPaginationNumbers < 6) {
            for (n=neededPaginationNumbers; n < 6; n++) {
                $('#p' + (n + 1) + 'Underline').parent().css({
                    'display': 'none'
                });
            }
        }
        if (nOIU < 5) {
            $('#nextUnderline').parent().css({
                'display': 'none'
            });
        }
    }

});
