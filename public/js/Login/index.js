$(document).ready(function(){
// ---------------------------------------------- //
// ----- DEFINITION OF VARIABLES TO BE USED ----- //
// ---------------------------------------------- //
        // styles variables
    let loginSideImage_image = $('.loginSideImage-container img'),
        login_container = $('.login-container'),
        isProcess = false,
        loginFormContainer = $('#loginFormContainer'),
        //login modal variables
        usernameLoginInput = $('#usernameLoginInput'),
        passwordLoginInput = $('#passwordLoginInput'),
        rememberMeCheckbox = $('#rememberMeCheckbox'),
        loginButton = $('#loginButton'),
        //process modal variables
        processContainer = $('#processContainer'),
        processImageContainer = $('#processImageContainer'),
        processTitleContainer = $('#processTitleContainer'),
        processMessageListContainer = $('#processMessageListContainer'),
        processButton = $('#processButton'),
        processButtonText = $('#processButton span'),
        errorHtml = '',
        ajaxResponse;


// ---------------------------- //
// ----- LINKS DEFINITION ----- //
// ---------------------------- //


// ------------------------ //
// ----- STYLES FIXES ----- //
// ------------------------ //
    // get "loginSideImage-container" widths and implement it on "mainLoginForm-container"
    login_container.css({
        'width':loginSideImage_image.width()*2.2
    });
    $(window).on('resize', function() {
        login_container.css({
            'width':loginSideImage_image.width()*2.2
        });
    });

// ---------------------------------- //
// ----- GENERAL MODALS EFFECTS ----- //
// ---------------------------------- //
    loginButton.on('mouseenter', function(e){
        $("button").css({
            'background': '#C8723A'
        });
    });
    loginButton.on('mouseleave', function(e){
        $("button").css({
            'background': '#EC8439'
        });
    });
    processButton.on('mouseenter', function(e){
        processButton.css({
            'background': '#BA3438',
            'transition': 'all 0.5s ease'
        });
    });
    processButton.on('mouseleave', function(e){
        processButton.css({
            'background': '#ED3237',
            'transition': 'all 0.5s ease'
        });
    });
    $(document).on("keydown", function(event) {
        if(event.which==13) {
            if (isProcess == true){
                processButton.trigger("click");
            }
        }
    });

// ------------------------------- //
// ----- LOGIN MODAL EFFECTS ----- //
// ------------------------------- //


// ------------------------------- //
// ----- LOGIN MODAL PROCESS ----- //
// ------------------------------- //
    loginButton.on('click', function(e) {
        e.preventDefault();
        loginButton.on('mouseenter', function(e){
            $("button").css({
                'color': '#FFFFFF',
                'background': '#EC8439',
                'border': '1px solid #E0E0E0',
                'box-shadow': 'none',
                '-moz-box-shadow': 'none',
                '-webkit-box-shadow': 'none'
            });
        });
        loginButton.on('mouseleave', function(e){
            $("button").css({
                'color': '#000000',
                'background': '#E0E0E0',
                'border': '1px solid #E0E0E0',
                'box-shadow': '8px 5px 4px rgba(184,184,184,0.5)',
                '-moz-box-shadow': '8px 5px 4px rgba(184,184,184,0.5)',
                '-webkit-box-shadow': '8px 5px 4px rgba(184,184,184,0.5)'
            });
        });
        login();
    });


// -------------------------------------------- //
// ----- FUNCTIONS USED FOR THIS .JS FILE ----- //
// -------------------------------------------- //
    /**
     * This function is in charge of executing the validation process and also of executing the
     * corresponding process for the login of a user by sending to a php controller the data of
     * the login form.
     *
     * @return void
     *
     * @author Miranda Meza César
     * DATE January 13, 2020
     */
    function login() {
        processMessageListContainer.empty();
        processMessageListContainer.css({'display':'none'});
        errorHtml = "";
        if (usernameLoginInput.val() == '' || usernameLoginInput.val() == null) {
            processMessageListContainer.css({'display':'block'});
            errorHtml += '<li>Please enter your username</li>';
        }
        if (passwordLoginInput.val() == '' || passwordLoginInput.val() == null) {
            processMessageListContainer.css({'display':'block'});
            errorHtml += '<li>Please enter your password</li>';
        }
        if (errorHtml == "") {
            $.post(
                '/Home/ajaxLogin',
                {
                    usernameLoginInput:usernameLoginInput.val(),
                    passwordLoginInput:passwordLoginInput.val(),
                    rememberMeCheckbox:rememberMeCheckbox.is(":checked")
                },
                function (response) {
                    ajaxResponse = JSON.parse(response.match('\{(\"[^"]+\"\:[^,}]+[,}])+')[0]); //Esto hace un match perfecto a codigo JSONs (excepto cuando se usan comas dentro de strings)
                    if (ajaxResponse.status == 202) {
                        //User logged in successfully
                        setTimeout(function(){
                            window.location = APP_URL + URL_Userlist;
                        }, 2000);
                    } else {
                        // Give current functionality to processButton
                        processButton.on('click', function(e){
                            e.preventDefault();
                            processContainer.fadeOut(500).css({
                                'display': 'none'
                            }).delay(500);
                            loginFormContainer.fadeIn(500).css({
                                'display': 'flex'
                            }).delay(500);
                            isProcess = false;
                        });
                        isProcess = true;
                        //modal de error
                        loginFormContainer.fadeOut(500).css({
                            'display': 'none'
                        }).delay(500);
                        processTitleContainer.children().empty();
                        processTitleContainer.children().append("ERROR!");
                        processImageContainer.empty();
                        processImageContainer.append('<img src="/img/Login/icons8-error-64.png" alt="">').css({
                            'display': 'flex',
                            'flex-direction': 'row',
                            'flex-wrap': 'nowrap',
                            'justify-content': 'center'
                        });
                        processMessageListContainer.append(ajaxResponse.message);
                        processMessageListContainer.css({
                            'display': 'block',
                            'padding': '0',
                            'font-size': '150%'
                        });
                        // Enable got it button
                        processButton.css({
                            'display': 'inline-block',
                            'background': 'rgb(232, 72, 73)'
                        });
                        processButtonText.empty();
                        processButtonText.append("GOT IT");
                        processContainer.fadeIn(500).css({
                            'display': 'flex'
                        }).delay(500);

                        // We return log in button default styles
                        loginButton.on('mouseenter', function(e){
                            $("button").css({
                                'color': '#FFFDFE',
                                'background': '#C8723A'
                            });
                        });
                        loginButton.on('mouseleave', function(e){
                            $("button").css({
                                'background': '#EC8439'
                            });
                        });
                    }
                }
            );
        } else {
            // Give current functionality to processButton
            processButton.on('click', function(e){
                e.preventDefault();
                processContainer.fadeOut(500).css({
                    'display': 'none'
                }).delay(500);
                loginFormContainer.fadeIn(500).css({
                    'display': 'flex'
                }).delay(500);
                isProcess = false;
            });
            isProcess = true;
            //modal de error
            loginFormContainer.fadeOut(500).css({
                'display': 'none'
            }).delay(500);
            processTitleContainer.children().empty();
            processTitleContainer.children().append("ERROR!");
            processImageContainer.empty();
            processImageContainer.append('<img src="/img/Login/icons8-error-64.png" alt="">').css({
                'display': 'flex',
                'flex-direction': 'row',
                'flex-wrap': 'nowrap',
                'justify-content': 'center'
            });
            processMessageListContainer.append(errorHtml);
            // Enable got it button
            processMessageListContainer.css({
                'display': 'block',
                'padding': '0',
                'font-size': '150%'
            });
            processButton.css({
                'display': 'inline-block',
                'background': 'rgb(232, 72, 73)'
            });
            processButtonText.empty();
            processButtonText.append("GOT IT");
            processContainer.fadeIn(500).css({
                'display': 'flex'
            }).delay(500);

            // We return log in button default styles
            loginButton.on('mouseenter', function(e){
                $("button").css({
                    'color': '#FFFDFE',
                    'background': '#C8723A'
                });
            });
            loginButton.on('mouseleave', function(e){
                $("button").css({
                    'background': '#EC8439'
                });
            });
        }
    }

});
