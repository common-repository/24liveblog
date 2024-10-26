(function (window) {
    const lb24UserData = {
        email: '',
        password: '',
    }

    function lb24Login() {
        const loginLoading = document.getElementById("lb24-login-loading");
        const loginErrorMessage = document.getElementById("lb24-wp-login-error-message");
        loginErrorMessage.innerHTML = '';
        loginLoading.style.visibility = 'visible';
        let formData = new FormData();
        formData.append("email", lb24UserData.email);
        formData.append("password", lb24UserData.password);
        window.fetch( lb24WpData.getLoginUrl, {
            method: 'POST',
            body: formData
        }).then((response) => {
            const res = response.json();
            return res;
        }).then(res => {
            if (res['err_code'] === 100) {
                loginLoading.style.visibility = 'hidden';
                const data = {
                    _ajax_nonce: lb24WpData.getNonce,
                    action: 'update_lb24_token',
                    user_id: lb24WpData.getWpUserId,
                    user_token: res['data']['token'],
                    user_uid: res['data']['uid'],
                    user_refresh_token: res['data']['refresh_token'],
                    user_uname: res['data']['uname']
                };
                jQuery.post(ajaxurl, data, function (response) {
                    window.location.reload();
                });
            } else if (res['err_code'] === 204) {
                loginLoading.style.visibility = 'hidden';
                loginErrorMessage.innerHTML = 'Password error';
            } else if (res['err_code'] === 205) {
                loginLoading.style.visibility = 'hidden';
                loginErrorMessage.innerHTML = 'User account does not exist';
            } else {
                loginLoading.style.visibility = 'hidden';
                loginErrorMessage.innerHTML = 'Login failed';
            }
        });
    }

    function lb24GetEmailInputValue(event) {
        lb24UserData.email = event.target.value
    };

    function lb24GetPasswordInputValue(event) {
        lb24UserData.password = event.target.value
    };

    function lb24Logout() {
        const data = {
            _ajax_nonce: lb24WpData.getNonce,
            action: 'update_lb24_token',
            user_id: lb24WpData.getWpUserId,
            user_token: '',
            user_uid: '',
            user_refresh_token: '',
            user_uname: ''
        };
        jQuery.post(ajaxurl, data, function (response) {
            window.location.reload();
        });
    }

    window.lb24WpFunc = {
        lb24Login: lb24Login,
        lb24Logout: lb24Logout,
        lb24GetEmailInputValue: lb24GetEmailInputValue,
        lb24GetPasswordInputValue: lb24GetPasswordInputValue,
    }
})(window)

